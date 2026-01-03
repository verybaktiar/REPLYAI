<?php

namespace App\Jobs;

use App\Models\WaBroadcastTarget;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $target;
    public $timeout = 120; // 2 minutes timeout per job

    /**
     * Create a new job instance.
     */
    public function __construct(WaBroadcastTarget $target)
    {
        $this->target = $target;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $waService): void
    {
        $target = $this->target;
        $broadcast = $target->broadcast;

        // Skip if already handled
        if ($target->status === 'sent') {
            return;
        }

        try {
            Log::info("Processing broadcast target: {$target->phone_number} for broadcast ID: {$broadcast->id}");

            // Prepare media if exists
            $mediaUrl = null;
            $mediaType = null;

            if ($broadcast->media_path) {
                $mediaUrl = asset('storage/' . $broadcast->media_path);
                
                // Determine media type based on extension
                $mime = Storage::disk('public')->mimeType($broadcast->media_path);
                if (str_contains($mime, 'image')) {
                    $mediaType = 'image';
                } elseif (str_contains($mime, 'video')) {
                    $mediaType = 'video';
                } else {
                    $mediaType = 'document';
                }
            }

            // Send via WhatsAppService
            $response = $waService->sendMessage(
                $target->phone_number,
                $broadcast->message ?? '',
                $mediaUrl,
                $mediaType
            );

            if (isset($response['error'])) {
                throw new \Exception($response['error']);
            }

            // Update status to sent
            $target->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Add random delay to prevent ban (1-5 seconds)
            sleep(rand(1, 5));

        } catch (\Exception $e) {
            Log::error("Failed to send broadcast to {$target->phone_number}: " . $e->getMessage());
            
            $target->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
