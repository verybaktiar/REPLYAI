<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\RunAutoReplyJob;

class AutoReplyQueueCommand extends Command
{
    protected $signature = 'autoreply:run';
    protected $description = 'Dispatch AutoReplyEngine to queue';

    public function handle(): int
    {
        RunAutoReplyJob::dispatch();
        $this->info('RunAutoReplyJob dispatched.');
        return Command::SUCCESS;
    }
}
