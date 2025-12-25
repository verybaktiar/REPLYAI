<?php

namespace App\Services;

use App\Models\AutoReplyRule;
use App\Models\AutoReplyLog;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AutoReplyEngine
{
    public function __construct(
        protected ChatwootClient $chatwoot,
        protected AiAnswerService $ai
    ) {}

    public function handleIncomingInstagramMessage(Message $message, Conversation $conversation): ?array
    {
        $rawText = trim((string) ($message->content ?? ''));
        if ($rawText === '') return null;

        Log::info('🤖 Processing Instagram message', [
            'message_id' => $message->id,
            'raw_text' => $rawText,
        ]);

        // Cek sudah diproses?
        if (AutoReplyLog::where('message_id', $message->id)->exists()) {
            Log::info('⏭️ Message already processed');
            return null;
        }

        // 1️⃣ MANUAL RULE: pakai RAW user text (tanpa merge)
        $rule = $this->findMatchingRule($rawText);

        if ($rule) {
            Log::info('✅ Found manual rule', ['rule_id' => $rule->id]);

            AutoReplyLog::create([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'rule_id' => $rule->id,
                'trigger_text' => $rawText,
                'response_text' => $rule->response_text,
                'status' => 'sent',
                'response_source' => 'manual',
                'ai_used' => false,
            ]);

            return [
                'response' => $rule->response_text,
                'rule_id' => $rule->id,
                'source' => 'manual',
                'ai_used' => false,
            ];
        }

        // 2️⃣ Kalau manual gak ketemu → SIAPKAN TEKS UNTUK AI (boleh merge konteks)
        $aiText = $rawText;

        // ✅ merge hanya untuk AI, dan hanya kalau user pesan pendek
        if (mb_strlen($rawText) <= 25) {
            $prev = Message::where('conversation_id', $conversation->id)
                ->where('id', '<', $message->id)
                ->orderByDesc('id')
                ->first();

            // merge hanya kalau sebelumnya agent/bot DAN itu pertanyaan follow-up
            if ($prev && $prev->sender_type === 'agent' && $prev->content) {
                $prevLower = Str::lower($prev->content);

                $isFollowupQuestion =
                    Str::contains($prevLower, 'sebutkan') ||
                    Str::contains($prevLower, 'pilih') ||
                    Str::contains($prevLower, 'mau yang mana') ||
                    Str::contains($prevLower, 'nama poli') ||
                    Str::contains($prevLower, 'spesialis') ||
                    Str::contains($prevLower, 'yang mana ya');

                if ($isFollowupQuestion) {
                    $aiText = trim($prev->content . "\n" . $rawText);
                    Log::info('🧠 Context merged (AI only)', ['ai_text' => $aiText]);
                }
            }
        }

        // 3️⃣ AI COOLDOWN
        if ($this->isAiCooldownActive($conversation->id)) {
            AutoReplyLog::create([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'rule_id' => null,
                'trigger_text' => $aiText,
                'response_text' => null,
                'status' => 'skipped_ai_cooldown',
                'response_source' => 'ai',
                'ai_used' => true,
            ]);
            return null;
        }

        // 4️⃣ AI ANSWER
        try {
            $aiResult = $this->ai->answerFromKb($aiText);

            if (!$aiResult || empty($aiResult['answer'])) {
                Log::info('📚 AI no answer found');

                AutoReplyLog::create([
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                    'rule_id' => null,
                    'trigger_text' => $aiText,
                    'response_text' => null,
                    'status' => 'skipped',
                    'response_source' => 'ai',
                    'ai_used' => true,
                    'ai_confidence' => $aiResult['confidence'] ?? 0,
                ]);

                return null;
            }

            AutoReplyLog::create([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'rule_id' => null,
                'trigger_text' => $aiText,
                'response_text' => $aiResult['answer'],
                'status' => 'sent_ai',
                'response_source' => 'ai',
                'ai_used' => true,
                'ai_confidence' => (float)($aiResult['confidence'] ?? 0),
            ]);

            return [
                'response' => $aiResult['answer'],
                'rule_id' => null,
                'source' => 'ai',
                'ai_used' => true,
                'ai_confidence' => (float)($aiResult['confidence'] ?? 0),
            ];
        } catch (\Throwable $e) {
            Log::error('❌ AI error', ['error' => $e->getMessage()]);

            AutoReplyLog::create([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'rule_id' => null,
                'trigger_text' => $aiText,
                'response_text' => null,
                'status' => 'failed_ai',
                'response_source' => 'ai',
                'ai_used' => true,
                'error_message' => $e->getMessage(),
            ]);

            return null;
        }
    }


    /**
     * ✅ Jangan merge kalau pesan itu salam/basa-basi.
     * Merge hanya untuk jawaban singkat yang biasanya balasan pilihan.
     */
    protected function shouldTryMergeContext(string $messageText): bool
    {
        $t = Str::lower(trim($messageText));

        // greeting / smalltalk → jangan merge
        $greetings = ['halo', 'hai', 'hi', 'pagi', 'siang', 'sore', 'malam', 'assalam', 'permisi', 'test', 'tes'];
        if (in_array($t, $greetings, true)) return false;

        // kalau pendek banget & berpotensi jawaban pilihan → boleh merge
        if (mb_strlen($t) > 25) return false;

        // contoh jawaban pilihan (poli anak, gigi, umum, angka 1/2/3)
        $looksLikeChoice =
            preg_match('/^\d+$/', $t) ||
            Str::contains($t, 'poli') ||
            Str::contains($t, 'anak') ||
            Str::contains($t, 'gigi') ||
            Str::contains($t, 'umum') ||
            Str::contains($t, 'igd') ||
            Str::contains($t, 'spesialis') ||
            Str::contains($t, 'promo');

        return (bool) $looksLikeChoice;
    }

    // ===================== RULE MATCHER (FULL) =====================

    protected function findMatchingRule(?string $text): ?AutoReplyRule
    {
        $text = trim((string) $text);
        if ($text === '') return null;

        $rules = AutoReplyRule::where('is_active', true)
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->get();

        // 1) kumpulkan semua kandidat yang match
        $matched = [];
        foreach ($rules as $rule) {
            if ($this->isRuleMatch($text, $rule)) {
                $matched[] = $rule;
            }
        }

        if (count($matched) === 0) return null;
        if (count($matched) === 1) return $matched[0];

        // 2) kandidat > 1 → AI pilih 1 (opsi C)
        $pickedId = $this->ai->pickBestRuleId($text, $matched);

        if ($pickedId) {
            foreach ($matched as $r) {
                if ((int) $r->id === (int) $pickedId) {
                    Log::info('🤖 AI picked rule', ['rule_id' => $pickedId]);
                    return $r;
                }
            }
        }

        // 3) fallback: priority tertinggi
        return collect($matched)->sortByDesc('priority')->first();
    }

    private function isRuleMatch(string $message, $rule): bool
    {
        $rawTrigger = $rule->trigger_keyword ?? '';
        if (trim($rawTrigger) === '') return false;

        $triggers = collect(explode('|', $rawTrigger))
            ->map(fn($t) => trim($t))
            ->filter();

        if ($triggers->isEmpty()) return false;

        $type = $rule->match_type ?? 'contains';

        foreach ($triggers as $trigger) {
            if ($this->matchByType($message, $trigger, $type)) {
                return true;
            }
        }

        return false;
    }

    private function matchByType(string $message, string $trigger, string $type): bool
    {
        $msgLower = Str::lower($message);
        $trgLower = Str::lower($trigger);

        return match ($type) {
            'exact' => trim($msgLower) === trim($trgLower),
            'regex' => $this->safeRegexMatch($trigger, $message),
            default => Str::contains($msgLower, $trgLower),
        };
    }

    private function safeRegexMatch(string $pattern, string $message): bool
    {
        try {
            return (bool) preg_match('/' . $pattern . '/i', $message);
        } catch (\Throwable $e) {
            return false;
        }
    }
    protected function isAiCooldownActive(int $conversationId): bool
    {
        $cooldownMinutes = (int) config('ai.ai_cooldown_minutes', 3);

        if ($cooldownMinutes <= 0) return false;

        $lastAiSent = AutoReplyLog::where('conversation_id', $conversationId)
            ->where('response_source', 'ai')
            ->whereIn('status', ['sent_ai'])
            ->orderByDesc('id')
            ->first();

        if (!$lastAiSent) return false;

        return $lastAiSent->created_at->diffInMinutes(now()) < $cooldownMinutes;
    }

}
