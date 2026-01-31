<?php

namespace App\Jobs;

use App\Models\BusinessProfile;
use App\Models\WaMessage;
use App\Models\WhatsAppDevice;
use App\Services\AiAnswerService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDailyAdminSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AiAnswerService $aiService, WhatsAppService $waService): void
    {
        Log::info('🚀 Starting Daily Admin Summary Job');

        // Get all profiles with daily summary enabled
        $profiles = BusinessProfile::where('enable_daily_summary', true)
            ->whereNotNull('admin_phone')
            ->get();

        foreach ($profiles as $profile) {
            try {
                // Get today's messages for this user/profile
                $messages = WaMessage::where('user_id', $profile->user_id)
                    ->whereDate('created_at', today())
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($messages->isEmpty()) {
                    Log::info("No messages for profile ID: {$profile->id} today.");
                    continue;
                }

                // Generate AI Summary
                $summary = $aiService->generateDailySummary($messages, $profile);

                if (!$summary) {
                    Log::error("Failed to generate AI summary for profile ID: {$profile->id}");
                    continue;
                }

                // Find a connected WhatsApp device for this user to send the report
                $device = WhatsAppDevice::where('user_id', $profile->user_id)
                    ->where('status', 'connected')
                    ->first();

                if (!$device) {
                    Log::warning("No connected WhatsApp device found for user ID: {$profile->user_id} to send daily summary.");
                    continue;
                }

                // Format the message
                $reportMsg = "📊 *REKAP CHAT HARIAN - " . today()->format('d M Y') . "*\n\n" .
                             $summary . "\n\n" .
                             "--- _Laporan Otomatis AiPro_ ---";

                // Send via WhatsApp
                $waService->sendMessage(
                    $device->session_id,
                    $profile->admin_phone . '@s.whatsapp.net',
                    $reportMsg
                );

                Log::info("Daily summary sent to admin (+{$profile->admin_phone}) for profile ID: {$profile->id}");

            } catch (\Exception $e) {
                Log::error("Error processing daily summary for profile ID: {$profile->id}: " . $e->getMessage());
            }
        }
    }
}
