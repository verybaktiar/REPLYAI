<?php

namespace App\Console\Commands;

use App\Models\KbScrapeJob;
use Illuminate\Console\Command;

class CleanupScrapeData extends Command
{
    protected $signature = 'scrape:cleanup 
                            {--hours=24 : Hours to keep raw HTML data}';

    protected $description = 'Cleanup old scrape job raw HTML data';

    public function handle(): int
    {
        $hours = $this->option('hours');
        $cutoff = now()->subHours($hours);

        // Clear raw_html from old completed/failed jobs
        $count = KbScrapeJob::whereNotNull('raw_html')
            ->where('created_at', '<', $cutoff)
            ->update(['raw_html' => null]);

        $this->info("Cleaned up raw HTML from {$count} old scrape jobs.");

        // Delete very old jobs (30 days)
        $oldJobs = KbScrapeJob::where('created_at', '<', now()->subDays(30))->delete();
        $this->info("Deleted {$oldJobs} jobs older than 30 days.");

        return self::SUCCESS;
    }
}
