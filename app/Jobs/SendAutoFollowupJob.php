<?php

namespace App\Jobs;

use App\Models\BusinessProfile;
use App\Models\WaConversation;
use App\Models\WhatsAppDevice;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAutoFollowupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $waService): void
    {
        // SMART TIMING: Only send between 09:00 and 21:00
        $hour = now()->hour;
        $isWeekend = now()->isWeekend();

        if ($isWeekend) {
            Log::info("It's weekend. Skipping auto-followups to maintain professional boundaries.");
            return;
        }

        if ($hour < 9 || $hour >= 21) {
            Log::info("Outside of smart timing window ($hour:00). Stalling follow-ups.");
            return;
        }

        $cutoff = now()->subHours(24);

        $conversations = WaConversation::where('status', WaConversation::STATUS_BOT_ACTIVE)
            ->where('session_status', '!=', WaConversation::SESSION_CLOSED)
            ->where('stop_autofollowup', false) // Check for manual stop
            ->where('followup_count', '<', 2) // ANTI-SPAM: Max 2 follow-ups
            ->whereNotNull('last_user_reply_at')
            ->where('last_user_reply_at', '<=', $cutoff)
            ->where(function($q) {
                // If never sent, OR sent > 24h ago
                $q->whereNull('followup_sent_at')
                  ->orWhere('followup_sent_at', '<=', now()->subHours(24));
            })
            ->get();

        if ($conversations->isEmpty()) {
            return;
        }

        foreach ($conversations as $conv) {
            try {
                // Get user's business profile
                $profile = BusinessProfile::where('user_id', $conv->user_id)->first();
                
                if (!$profile || !$profile->enable_autofollowup) {
                    continue;
                }

                // CONTEXT CHECK: Skip if last message from user indicates closure
                $lastMsg = \App\Models\WaMessage::where('phone_number', $conv->phone_number)
                    ->where('direction', 'incoming')
                    ->latest()
                    ->first();
                
                if ($lastMsg) {
                    $closedKeywords = ['terima kasih', 'makasih', 'thanks', 'ok', 'oke', 'sip', 'sudah transfer', 'done'];
                    foreach ($closedKeywords as $kw) {
                        if (\Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($lastMsg->message), $kw)) {
                            Log::info("Skipping follow-up for {$conv->phone_number} - detected closed keyword: '{$kw}'");
                            $conv->update(['session_status' => WaConversation::SESSION_CLOSED]);
                            continue 2;
                        }
                    }
                }

                $followupMsg = $profile->followup_message ?: "Halo kak! 👋 Apakah masih ada yang bisa kami bantu? Kami masih setia menunggu pertanyaan kakak ya 😊";
                
                // Variasi pesan untuk follow-up ke-2
                if ($conv->followup_count == 1) {
                    $followupMsg = "Halo kak, sekadar mengingatkan jika ada pertanyaan tentang layanan kami, silakan balas ya. Kami siap membantu. 😊";
                }

                // Find a connected WhatsApp device for this user
                $device = WhatsAppDevice::where('user_id', $conv->user_id)
                    ->where('status', 'connected')
                    ->first();

                if (!$device) {
                    continue;
                }

                // Send message
                $jid = $conv->phone_number . '@s.whatsapp.net';
                $waService->sendMessage($device->session_id, $jid, $followupMsg);

                // Update conversation tracker
                $conv->increment('followup_count');
                $conv->update([
                    'followup_sent_at' => now(),
                    'session_status' => WaConversation::SESSION_FOLLOWUP_SENT
                ]);

                Log::info("Auto-Followup #{$conv->followup_count} sent to {$conv->phone_number} for user ID: {$conv->user_id}");

            } catch (\Exception $e) {
                Log::error("Error in AutoFollowup for conv ID {$conv->id}: " . $e->getMessage());
            }
        }
    }
}
