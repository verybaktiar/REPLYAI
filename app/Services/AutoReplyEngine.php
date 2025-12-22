<?php

namespace App\Services;

use App\Models\AutoReplyRule;
use App\Models\AutoReplyLog;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Str;

class AutoReplyEngine
{
    public function __construct(
        protected ChatwootClient $chatwoot,
        protected AiAnswerService $ai // ✅ AI fallback
    ) {}

    /**
     * Jalankan bot untuk semua conversation.
     */
    public function runForAllConversations(): array
    {
        // Ambil latest CONTACT message per conversation yang BELUM ada AutoReplyLog
        $latestUnprocessedMessages = Message::query()
            ->select('messages.*')
            ->where('sender_type', 'contact')
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                  ->from('auto_reply_logs')
                  ->whereColumn('auto_reply_logs.message_id', 'messages.id');
            })
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(m2.id)')
                  ->from('messages as m2')
                  ->whereColumn('m2.conversation_id', 'messages.conversation_id')
                  ->where('m2.sender_type', 'contact')
                  // ✅ IMPORTANT: MAX id dari yang BELUM punya log
                  ->whereNotExists(function ($q2) {
                      $q2->selectRaw(1)
                          ->from('auto_reply_logs as l2')
                          ->whereColumn('l2.message_id', 'm2.id');
                  })
                  ->groupBy('m2.conversation_id');
            })
            ->orderByDesc('message_created_at')
            ->limit(200)
            ->get();
    
        $conversationIds = $latestUnprocessedMessages
            ->pluck('conversation_id')
            ->unique()
            ->values();
    
        $conversations = Conversation::whereIn('id', $conversationIds)
            ->orderByDesc('updated_at')
            ->get();
    
        $report = [
            'checked_conversations' => 0,
            'checked_messages' => 0,
            'replied' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];
    
        foreach ($conversations as $conv) {
            $report['checked_conversations']++;
            $r = $this->runForConversation($conv);
            foreach ($r as $k => $v) $report[$k] += $v;
        }
    
        return $report;
    }
    

    /**
     * Untuk dipakai ulang di tempat lain.
     */
    public function matchRule(?string $text, ?Conversation $conversation = null): ?AutoReplyRule
    {
        return $this->findMatchingRule($text);
    }

    /**
     * Jalankan bot untuk 1 conversation.
     */
    public function runForConversation(Conversation $conv): array
    {
        $report = [
            'checked_messages' => 0,
            'replied' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        /**
         * ✅ BURST / DEBOUNCE:
         * Ambil beberapa pesan contact terbaru dalam window pendek
         * lalu gabung jadi 1 pertanyaan.
         */
        $latestMsgs = Message::where('conversation_id', $conv->id)
            ->where('sender_type', 'contact')
            ->orderBy('message_created_at', 'desc')
            ->limit(5)
            ->get();

        if ($latestMsgs->isEmpty()) {
            return $report;
        }

        $latestContactMsg = $latestMsgs->first();

        $report['checked_messages']++;

        $debounceSeconds = (int) config('ai.debounce_seconds', 90);

        $burstMsgs = $latestMsgs->filter(function ($m) use ($latestContactMsg, $debounceSeconds) {
            return abs(($latestContactMsg->message_created_at ?? 0) - ($m->message_created_at ?? 0)) <= $debounceSeconds;
        });

        $messageText = $burstMsgs
            ->sortBy('message_created_at')
            ->pluck('content')
            ->filter()
            ->implode("\n");

        if (trim($messageText) === '') {
            $report['skipped']++;
            return $report;
        }

        // ✅ cek sudah diproses berdasarkan message lokal ATAU chatwoot_id
        $alreadyProcessed = AutoReplyLog::where('message_id', $latestContactMsg->id)
            ->orWhereIn('message_id', function ($q) use ($latestContactMsg) {
                if ($latestContactMsg->chatwoot_id) {
                    $q->select('id')
                        ->from('messages')
                        ->where('chatwoot_id', $latestContactMsg->chatwoot_id);
                } else {
                    $q->selectRaw('0');
                }
            })
            ->exists();

       // ✅ guard: abaikan log child burst
        $lastLog = AutoReplyLog::where('conversation_id', $conv->id)
        ->where('status', '!=', 'skipped_burst') // ✅ INI PENTING
        ->orderByDesc('id')
        ->first();

        if ($lastLog) {
            $lastMsgTime = optional($lastLog->message)->message_created_at ?? 0;
            if (($latestContactMsg->message_created_at ?? 0) <= $lastMsgTime) {
                $report['skipped']++;
                return $report;
            }
        }

        if ($alreadyProcessed) {
            // ✅ penting: tetap log supaya message ini dianggap processed
            AutoReplyLog::create([
                'conversation_id' => $conv->id,
                'message_id' => $latestContactMsg->id,
                'rule_id' => null,
                'trigger_text' => $messageText,
                'response_text' => null,
                'status' => 'skipped_duplicate',
                'ai_used' => false,
                'response_source' => 'manual',
            ]);
            
        
            $report['skipped']++;
            return $report;
        }
        

        // 1) Cari rule manual
        $rule = $this->findMatchingRule($messageText);

        if ($rule) {
            try {
                $this->chatwoot->sendMessage(
                    (int) $conv->chatwoot_id,
                    $rule->response_text
                );

                AutoReplyLog::create([
                    'conversation_id' => $conv->id,
                    'message_id' => $latestContactMsg->id,
                    'rule_id' => $rule->id,
                    'trigger_text' => $messageText,
                    'response_text' => $rule->response_text,
                    'status' => 'sent',
                    'ai_used' => false,
                    'response_source' => 'manual',
                ]);

                // ✅ tandai pesan burst lain sebagai sudah diproses
                $this->logBurstChildren(
                    $conv,
                    $burstMsgs,
                    $latestContactMsg,
                    $rule->id,
                    'manual',
                    false
                );

                $report['replied']++;
                return $report;

            } catch (\Throwable $e) {
                AutoReplyLog::create([
                    'conversation_id' => $conv->id,
                    'message_id' => $latestContactMsg->id,
                    'rule_id' => $rule->id,
                    'trigger_text' => $messageText,
                    'response_text' => $rule->response_text,
                    'status' => 'failed',
                    'ai_used' => false,
                    'response_source' => 'manual',
                    'error_message' => $e->getMessage(),
                ]);

                // ✅ tandai burst lain biar gak diproses ulang
                $this->logBurstChildren(
                    $conv,
                    $burstMsgs,
                    $latestContactMsg,
                    $rule->id,
                    'manual',
                    false
                );

                $report['failed']++;
                return $report;
            }
        }

        // 2) Rule manual gak ada → AI fallback dari KB

        // ✅ COOLDOWN CHECK
        if ($this->isAiCooldownActive($conv->id)) {
            AutoReplyLog::create([
                'conversation_id' => $conv->id,
                'message_id' => $latestContactMsg->id,
                'rule_id' => null,
                'trigger_text' => $messageText,
                'response_text' => null,
                'status' => 'skipped_ai_cooldown',
                'ai_used' => true,
                'response_source' => 'ai',
            ]);

            // ✅ tandai burst lain
            $this->logBurstChildren(
                $conv,
                $burstMsgs,
                $latestContactMsg,
                null,
                'ai',
                true
            );

            $report['skipped']++;
            return $report;
        }

        try {
            $aiRes = $this->ai->answerFromKb($messageText);

            if (!$aiRes || empty($aiRes['answer'])) {
                AutoReplyLog::create([
                    'conversation_id' => $conv->id,
                    'message_id' => $latestContactMsg->id,
                    'rule_id' => null,
                    'trigger_text' => $messageText,
                    'response_text' => null,
                    'status' => 'skipped',
                    'ai_used' => true,
                    'response_source' => 'ai',
                    'ai_confidence' => $aiRes['confidence'] ?? 0,
                    'ai_sources' => $aiRes['sources'] ?? null,
                ]);

                // ✅ tandai burst lain
                $this->logBurstChildren(
                    $conv,
                    $burstMsgs,
                    $latestContactMsg,
                    null,
                    'ai',
                    true
                );

                $report['skipped']++;
                return $report;
            }

            $confidence = (float) ($aiRes['confidence'] ?? 0);

            // AI confident → kirim
            $this->chatwoot->sendMessage(
                (int) $conv->chatwoot_id,
                $aiRes['answer']
            );

            AutoReplyLog::create([
                'conversation_id' => $conv->id,
                'message_id' => $latestContactMsg->id,
                'rule_id' => null,
                'trigger_text' => $messageText,
                'response_text' => $aiRes['answer'],
                'status' => 'sent_ai',
                'ai_used' => true,
                'response_source' => 'ai',
                'ai_confidence' => $confidence,
                'ai_sources' => $aiRes['sources'] ?? null,
            ]);

            // ✅ tandai burst lain
            $this->logBurstChildren(
                $conv,
                $burstMsgs,
                $latestContactMsg,
                null,
                'ai',
                true
            );

            $report['replied']++;

        } catch (\Throwable $e) {
            AutoReplyLog::create([
                'conversation_id' => $conv->id,
                'message_id' => $latestContactMsg->id,
                'rule_id' => null,
                'trigger_text' => $messageText,
                'response_text' => null,
                'status' => 'failed_ai',
                'ai_used' => true,
                'response_source' => 'ai',
                'error_message' => $e->getMessage(),
            ]);

            // ✅ tandai burst lain
            $this->logBurstChildren(
                $conv,
                $burstMsgs,
                $latestContactMsg,
                null,
                'ai',
                true
            );

            $report['failed']++;
        }

        return $report;
    }

    /**
     * ✅ Tandai pesan burst selain message utama sebagai sudah diproses.
     */
    protected function logBurstChildren(
        Conversation $conv,
        $burstMsgs,
        Message $latestContactMsg,
        ?int $ruleId,
        string $source,
        bool $aiUsed
    ): void {
        foreach ($burstMsgs as $bm) {
            if ($bm->id === $latestContactMsg->id) continue;

            AutoReplyLog::create([
                'conversation_id' => $conv->id,
                'message_id' => $bm->id,
                'rule_id' => $ruleId,
                'trigger_text' => $bm->content,
                'response_text' => null,
                'status' => 'skipped_burst',
                'ai_used' => $aiUsed,
                'response_source' => $source,
            ]);
        }
    }

    /**
     * Cari rule match berdasarkan keyword sederhana.
     */
    protected function findMatchingRule(?string $text): ?AutoReplyRule
    {
        if (!$text) return null;

        $rules = AutoReplyRule::where('is_active', true)
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->get();

        foreach ($rules as $rule) {
            if ($this->isRuleMatch($text, $rule)) {
                return $rule;
            }
        }

        return null;
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

    /**
     * ✅ Cooldown AI supaya tidak spam reply dalam window tertentu
     */
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
