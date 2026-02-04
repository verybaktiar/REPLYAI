<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $sessionId,
        protected string $to,
        protected string $message,
        protected ?string $mediaUrl = null,
        protected ?string $mediaType = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $waService): void
    {
        try {
            Log::info("Job Processing: Sending WhatsApp message to {$this->to} via {$this->sessionId}");
            
            $waService->sendMessage(
                $this->sessionId,
                $this->to,
                $this->message,
                $this->mediaUrl,
                $this->mediaType
            );
        } catch (\Exception $e) {
            Log::error("Job Failed: Sending WhatsApp message to {$this->to}. Error: " . $e->getMessage());
            // Retry logic is handled by Laravel Queue configuration, but we can throw to trigger it
            throw $e;
        }
    }
}
