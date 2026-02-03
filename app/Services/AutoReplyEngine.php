<?php

namespace App\Services;

use App\Models\AutoReplyRule;
use App\Models\AutoReplyLog;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AutoReplyEngine
{
    public function __construct(
        protected AiAnswerService $ai,
        protected ReplyTemplate $tpl
    ) {}

    /**
     * Simulasi respons bot tanpa menyimpan ke database.
     * Digunakan oleh Bot Simulator.
     */
    public function simulateMessage(string $messageText): array
    {
        $rawText = trim($messageText);
        if ($rawText === '') {
            return ['response' => '', 'source' => 'empty', 'ai_used' => false];
        }

        // 1) menu/help
        $lower = Str::lower($rawText);
        if (in_array($lower, ['bantuan', 'menu', 'help'], true)) {
            return [
                'response' => $this->tpl->menu(),
                'source' => 'menu',
                'ai_used' => false,
            ];
        }

        // 2) Manual Rule
        $rule = $this->findMatchingRule($rawText);
        if ($rule) {
            return [
                'response' => $this->tpl->appendFooter((string) $rule->response_text),
                'source' => 'manual',
                'ai_used' => false,
                'rule_id' => $rule->id,
            ];
        }

        // 3) AI
        if (!env('AI_REPLY_ENABLED', true)) {
            return [
                'response' => '🤷 AI dinonaktifkan. Pesan akan diteruskan ke CS.',
                'source' => 'fallback',
                'ai_used' => false,
            ];
        }

        try {
            $aiResult = $this->ai->answerFromKb($rawText);

            if (!$aiResult || empty($aiResult['answer'])) {
                return [
                    'response' => env('DEFAULT_FALLBACK_TEXT', 'Mohon maaf, pertanyaan Anda akan kami teruskan ke CS.'),
                    'source' => 'fallback',
                    'ai_used' => true,
                    'ai_confidence' => $aiResult['confidence'] ?? 0,
                ];
            }

            $title = $this->tpl->titleFromIntent($rawText);
            $resp = $this->tpl->wrap($title, $this->limitReply($aiResult['answer']));

            return [
                'response' => $resp,
                'source' => 'ai',
                'ai_used' => true,
                'ai_confidence' => (float)($aiResult['confidence'] ?? 0),
            ];
        } catch (\Throwable $e) {
            return [
                'response' => '❌ Error AI: ' . $e->getMessage(),
                'source' => 'error',
                'ai_used' => true,
            ];
        }
    }

    /**
     * Proses semua percakapan yang statusnya 'bot_handling'.
     * Dipanggil oleh RunAutoReplyJob via Scheduler.
     */
    public function runForAllConversations(): array
    {
        // Ambil semua percakapan aktif yang sedang ditangani bot
        // Anda bisa sesuaikan filter 'updated_at' agar tidak memproses percakapan purba
        $conversations = Conversation::where('status', 'bot_handling')
            ->where('updated_at', '>=', now()->subHours(24)) 
            ->get();

        $stats = [
            'total' => $conversations->count(),
            'processed' => 0,
            'errors' => 0,
        ];

        foreach ($conversations as $conversation) {
            try {
                // Ambil pesan terakhir dari user yang BELUM dibalas
                // Logika sederhana: ambil pesan terakhir, cek apakah dari user
                // dan belum ada log auto-reply untuk message_id tersebut.
                $lastMessage = Message::where('conversation_id', $conversation->id)
                    ->orderByDesc('id')
                    ->first();

                if (!$lastMessage) continue;

                // Cek apakah pesan terakhir dari user/contact
                if (!in_array($lastMessage->sender_type, ['user', 'contact'])) continue;

                // Cek apakah sudah diproses sebelumnya
                $alreadyProcessed = AutoReplyLog::where('message_id', $lastMessage->id)->exists();
                if ($alreadyProcessed) continue;

                // Proses pesan ini
                $this->handleIncomingInstagramMessage($lastMessage, $conversation);
                $stats['processed']++;

            } catch (\Throwable $e) {
                Log::error("AutoReplyEngine Error [ConvID: {$conversation->id}]: " . $e->getMessage());
                $stats['errors']++;
            }
        }

        return $stats;
    }

    public function handleIncomingInstagramMessage(Message $message, Conversation $conversation): ?array
    {
        // ✅ jangan respon pesan agent/bot/echo
        $senderType = (string) ($message->sender_type ?? '');
        if (!in_array($senderType, ['user', 'contact'], true)) {
            return null;
        }

        // ✅ HANDOFF CHECK: Cek apakah bot boleh balas untuk conversation ini
        $status = $conversation->status ?? 'bot_handling';
        
        // Jika agent_handling, cek timeout (4 jam)
        if ($status === 'agent_handling') {
            $agentRepliedAt = $conversation->agent_replied_at;
            $hoursSinceAgent = $agentRepliedAt 
                ? now()->diffInHours($agentRepliedAt) 
                : 999; // Anggap sudah lama
            
            if ($hoursSinceAgent >= 4) {
                // Timeout tercapai, kembalikan ke bot
                $conversation->update(['status' => 'bot_handling']);
                Log::info('🤖 Handoff timeout: conversation returned to bot', ['conv_id' => $conversation->id]);
            } else {
                // Masih dalam masa agent handling, bot diam
                Log::info('🤫 Bot silent: agent still handling', ['conv_id' => $conversation->id]);
                return null;
            }
        }
        
        // Jika escalated, bot tetap diam (menunggu CS respond)
        if ($status === 'escalated') {
            // Cek new session (24 jam)
            $lastActivity = $conversation->last_activity_at;
            $hoursSinceLast = $lastActivity 
                ? now()->diffInHours(\Carbon\Carbon::createFromTimestamp($lastActivity)) 
                : 999;
            
            if ($hoursSinceLast >= 24) {
                // Session baru, reset ke bot_handling
                $conversation->update(['status' => 'bot_handling']);
                Log::info('🆕 New session: conversation returned to bot', ['conv_id' => $conversation->id]);
            } else {
                Log::info('🤫 Bot silent: waiting for CS', ['conv_id' => $conversation->id]);
                return null;
            }
        }

        $rawText = trim((string) ($message->content ?? ''));
        if ($rawText === '') return null;

        Log::info('🤖 Processing Instagram message', [
            'message_id' => $message->id,
            'sender_type' => $senderType,
            'raw_text' => $rawText,
        ]);

        // anti double-process
        if (AutoReplyLog::where('message_id', $message->id)->exists()) {
            Log::info('⏭️ Message already processed');
            return null;
        }

        // 0) menu/help selalu ditangani
        $lower = Str::lower($rawText);
        if (in_array($lower, ['bantuan', 'menu', 'help'], true)) {
            $resp = $this->tpl->menu();

            AutoReplyLog::create([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'rule_id' => null,
                'trigger_text' => $rawText,
                'response_text' => $resp,
                'status' => 'sent_menu',
                'response_source' => 'menu',
                'ai_used' => false,
                'user_id' => $conversation->user_id,
            ]);

            return [
                'response' => $resp,
                'rule_id' => null,
                'source' => 'menu',
                'ai_used' => false,
            ];
        }

        // 1) MANUAL RULE
        $rule = $this->findMatchingRule($rawText);
        if ($rule) {
            Log::info('✅ Found manual rule', ['rule_id' => $rule->id]);

            $resp = $this->tpl->appendFooter((string) $rule->response_text);

            AutoReplyLog::create([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'rule_id' => $rule->id,
                'trigger_text' => $rawText,
                'response_text' => $resp,
                'status' => 'sent',
                'response_source' => 'manual',
                'ai_used' => false,
                'user_id' => $conversation->user_id,
            ]);

            return [
                'response' => $resp,
                'rule_id' => $rule->id,
                'source' => 'manual',
                'ai_used' => false,
            ];
        }

        // 2) AI text (merge context jika cocok)
        $aiText = $rawText;

        if ($this->shouldTryMergeContext($rawText)) {
            $prev = Message::where('conversation_id', $conversation->id)
                ->where('id', '<', $message->id)
                ->orderByDesc('id')
                ->first();

            if ($prev && in_array($prev->sender_type, ['agent', 'bot'], true) && $prev->content) {
                $prevLower = Str::lower($prev->content);

                // ❌ Jangan merge jika prev adalah menu bantuan (terlalu panjang)
                $isMenuOrTooLong = 
                    Str::contains($prevLower, 'menu bantuan') ||
                    Str::contains($prevLower, 'ketik *bantuan*') ||
                    mb_strlen($prev->content) > 300;

                $isFollowupQuestion =
                    Str::contains($prevLower, 'sebutkan') ||
                    Str::contains($prevLower, 'pilih') ||
                    Str::contains($prevLower, 'mau yang mana') ||
                    Str::contains($prevLower, 'nama poli') ||
                    Str::contains($prevLower, 'spesialis') ||
                    Str::contains($prevLower, 'yang mana ya');

                if ($isFollowupQuestion && !$isMenuOrTooLong) {
                    $aiText = trim($prev->content . "\n" . $rawText);
                    Log::info('🧠 Context merged (AI only)', ['ai_text' => $aiText]);
                }
            }
        }

        // 3) AI enable?
        if (!env('AI_REPLY_ENABLED', true)) {
            return $this->fallbackToCS($conversation, $message, $aiText);
        }

        // 4) AI cooldown (✅ jangan diam) - skip jika conversation_id null (simulator)
        if ($conversation->id && $this->isAiCooldownActive($conversation->id)) {
            $resp = $this->tpl->cooldown();

            AutoReplyLog::create([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'rule_id' => null,
                'trigger_text' => $aiText,
                'response_text' => $resp,
                'status' => 'sent_ai_cooldown',
                'response_source' => 'ai',
                'ai_used' => true,
                'user_id' => $conversation->user_id,
            ]);

            return [
                'response' => $resp,
                'rule_id' => null,
                'source' => 'ai_cooldown',
                'ai_used' => false,
            ];
        }

        // 5) AI answer
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
                    'user_id' => $conversation->user_id,
                ]);

                return $this->fallbackToCS($conversation, $message, $aiText);
            }

            $this->markAiCooldown($conversation->id);

            $title = $this->tpl->titleFromIntent($aiText);
            $resp = $this->tpl->wrap($title, $this->limitReply($aiResult['answer']));

            AutoReplyLog::create([
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'rule_id' => null,
                'trigger_text' => $aiText,
                'response_text' => $resp,
                'status' => 'sent_ai',
                'response_source' => 'ai',
                'ai_used' => true,
                'ai_confidence' => (float)($aiResult['confidence'] ?? 0),
                'user_id' => $conversation->user_id,
            ]);

            return [
                'response' => $resp,
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
                'user_id' => $conversation->user_id,
            ]);

            return $this->fallbackToCS($conversation, $message, $aiText);
        }
    }

    protected function limitReply(string $text): string
    {
        $max = (int) env('AI_REPLY_MAX_CHARS', 600);
        $text = trim($text);
        if ($max > 0 && mb_strlen($text) > $max) {
            $text = mb_substr($text, 0, $max - 3) . '...';
        }
        return $text;
    }

    protected function fallbackToCS(Conversation $conversation, Message $message, string $triggerText): ?array
    {
        if (!env('DEFAULT_FALLBACK_ENABLED', true)) return null;

        $fallback = trim((string) env('DEFAULT_FALLBACK_TEXT', ''));
        if ($fallback === '') return null;

        $resp = $this->tpl->wrap("📩 Kami teruskan ke CS", $fallback);

        AutoReplyLog::create([
            'conversation_id' => $conversation->id,
            'message_id' => $message->id,
            'rule_id' => null,
            'trigger_text' => $triggerText,
            'response_text' => $resp,
            'status' => 'sent_fallback',
            'response_source' => 'fallback',
            'ai_used' => false,
            'user_id' => $conversation->user_id,
        ]);

        return [
            'response' => $resp,
            'rule_id' => null,
            'source' => 'fallback',
            'ai_used' => false,
        ];
    }

    protected function isAiCooldownActive(int $conversationId): bool
    {
        $minutes = (int) env('AI_COOLDOWN_MINUTES', 1);
        if ($minutes <= 0) return false;

        return Cache::has("ai_cooldown:conv:{$conversationId}");
    }

    protected function markAiCooldown(int $conversationId): void
    {
        $minutes = (int) env('AI_COOLDOWN_MINUTES', 1);
        if ($minutes <= 0) return;

        Cache::put("ai_cooldown:conv:{$conversationId}", 1, now()->addMinutes($minutes));
    }

    protected function shouldTryMergeContext(string $messageText): bool
    {
        $t = Str::lower(trim($messageText));
        $greetings = ['halo','hai','hi','pagi','siang','sore','malam','assalam','permisi','test','tes'];

        if (in_array($t, $greetings, true)) return false;
        if (mb_strlen($t) > 25) return false;

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

    protected function findMatchingRule(?string $text): ?AutoReplyRule
    {
        $text = trim((string) $text);
        if ($text === '') return null;

        $rules = AutoReplyRule::where('is_active', true)
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->get();

        $matched = [];
        foreach ($rules as $rule) {
            if ($this->isRuleMatch($text, $rule)) $matched[] = $rule;
        }

        if (count($matched) === 0) return null;
        if (count($matched) === 1) return $matched[0];

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
            if ($this->matchByType($message, $trigger, $type)) return true;
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
}
