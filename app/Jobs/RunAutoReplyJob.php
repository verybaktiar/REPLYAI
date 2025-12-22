<?php

namespace App\Jobs;

use App\Services\AutoReplyEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAutoReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;          // retry max 3x
    public int $backoff = 10;       // jeda retry 10 detik
    public int $timeout = 120;      // max 2 menit per job

    public function handle(AutoReplyEngine $engine): void
    {
        try {
            $report = $engine->runForAllConversations();
            Log::info('[RunAutoReplyJob] done', $report);
        } catch (\Throwable $e) {
            Log::error('[RunAutoReplyJob] failed: '.$e->getMessage());
            throw $e; // biar queue retry
        }
    }
}
