<?php

namespace App\Console\Commands;

use App\Models\WaConversation;
use App\Models\WaSession;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WaSessionTimeoutCommand extends Command
{
    protected $signature = 'wa:session-timeout';
    protected $description = 'Check WhatsApp conversations for session timeout and send follow-up messages';

    public function handle(WhatsAppService $waService): int
    {
        $session = WaSession::getDefault();
        
        // Get timeout settings
        $idleTimeout = $session->session_idle_timeout_minutes ?? 30;
        $followupTimeout = $session->session_followup_timeout_minutes ?? 15;

        $this->info("Checking session timeouts (idle: {$idleTimeout}min, followup: {$followupTimeout}min)");

        // Get all active conversations that need processing
        $conversations = WaConversation::whereIn('session_status', [
            WaConversation::SESSION_ACTIVE,
            WaConversation::SESSION_FOLLOWUP_SENT,
        ])->get();

        $followupsSent = 0;
        $sessionsClosed = 0;

        foreach ($conversations as $conv) {
            // Skip if no user reply recorded yet
            if (!$conv->last_user_reply_at) {
                $this->line("  Skipping {$conv->phone_number}: no last_user_reply_at");
                continue;
            }

            $minutesSinceLastReply = now()->diffInMinutes($conv->last_user_reply_at);
            
            Log::info('Session Timeout Debug', [
                'phone' => $conv->phone_number,
                'now' => now()->toDateTimeString(),
                'last_reply' => $conv->last_user_reply_at?->toDateTimeString(),
                'minutes' => $minutesSinceLastReply,
                'timeout' => $idleTimeout,
                'status' => $conv->session_status,
                'should_trigger' => $minutesSinceLastReply >= $idleTimeout,
            ]);

            // Case 1: Active session that is idle - send follow-up
            if ($conv->session_status === WaConversation::SESSION_ACTIVE) {
                if ($minutesSinceLastReply >= $idleTimeout) {
                    $this->sendFollowup($waService, $conv);
                    $followupsSent++;
                }
            }

            // Case 2: Follow-up sent but no response - close session
            if ($conv->session_status === WaConversation::SESSION_FOLLOWUP_SENT) {
                $minutesSinceFollowup = $conv->followup_sent_at 
                    ? now()->diffInMinutes($conv->followup_sent_at) 
                    : 999;

                if ($minutesSinceFollowup >= $followupTimeout) {
                    $this->closeSession($waService, $conv);
                    $sessionsClosed++;
                }
            }
        }

        $this->info("Done! Follow-ups sent: {$followupsSent}, Sessions closed: {$sessionsClosed}");
        
        Log::info('WA Session Timeout Check', [
            'followups_sent' => $followupsSent,
            'sessions_closed' => $sessionsClosed,
        ]);

        return self::SUCCESS;
    }

    /**
     * Send follow-up message to idle user
     */
    protected function sendFollowup(WhatsAppService $waService, WaConversation $conv): void
    {
        $message = "Halo kak! 👋 Apakah masih ada yang bisa saya bantu?\n\n" .
                   "Jika sudah selesai, balas *tidak* ya.\n" .
                   "Jika masih butuh bantuan, silakan sampaikan pertanyaannya 😊";

        // Get the remote_jid from the latest message
        $lastMessage = $conv->messages()->latest()->first();
        $jid = $lastMessage?->remote_jid ?? $conv->phone_number . '@s.whatsapp.net';

        $result = $waService->sendMessage($jid, $message);

        if ($result['success']) {
            $conv->update([
                'session_status' => WaConversation::SESSION_FOLLOWUP_SENT,
                'followup_sent_at' => now(),
            ]);

            Log::info('WA Session Follow-up Sent', [
                'phone' => $conv->phone_number,
            ]);

            $this->line("  → Follow-up sent to {$conv->phone_number}");
        } else {
            Log::error('WA Session Follow-up Failed', [
                'phone' => $conv->phone_number,
                'error' => $result['error'] ?? 'Unknown',
            ]);
        }
    }

    /**
     * Close session after timeout
     */
    protected function closeSession(WhatsAppService $waService, WaConversation $conv): void
    {
        $message = "Terima kasih sudah menghubungi RS PKU Muhammadiyah Surakarta 🙏\n\n" .
                   "Jika ada pertanyaan lagi, jangan ragu untuk chat kami ya!\n" .
                   "Semoga sehat selalu! 💚";

        // Get the remote_jid from the latest message
        $lastMessage = $conv->messages()->latest()->first();
        $jid = $lastMessage?->remote_jid ?? $conv->phone_number . '@s.whatsapp.net';

        $result = $waService->sendMessage($jid, $message);

        $conv->update([
            'session_status' => WaConversation::SESSION_CLOSED,
        ]);

        Log::info('WA Session Closed', [
            'phone' => $conv->phone_number,
            'sent_closing' => $result['success'],
        ]);

        $this->line("  → Session closed for {$conv->phone_number}");
    }
}
