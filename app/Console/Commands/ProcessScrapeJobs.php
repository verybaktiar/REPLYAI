<?php

namespace App\Console\Commands;

use App\Models\KbScrapeJob;
use App\Services\Scraper\WebScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScrapeJobs extends Command
{
    protected $signature = 'scrape:process 
                            {--once : Process only one job and exit}
                            {--job-id= : Process specific job ID}';

    protected $description = 'Process pending KB scrape jobs';

    public function handle(WebScraperService $scraper): int
    {
        // Process specific job if ID provided
        if ($jobId = $this->option('job-id')) {
            $job = KbScrapeJob::where('job_id', $jobId)->first();
            
            if (!$job) {
                $this->error("Job {$jobId} not found");
                return self::FAILURE;
            }

            $this->info("Processing job: {$jobId}");
            $scraper->processJob($job);
            $this->info("Job completed with status: {$job->fresh()->status}");
            
            return self::SUCCESS;
        }

        // Process pending jobs
        do {
            $job = KbScrapeJob::getNextPending();

            if (!$job) {
                $this->info('No pending jobs found');
                break;
            }

            $this->info("Processing job: {$job->job_id} for URL: {$job->url}");

            try {
                $scraper->processJob($job);
                $this->info("✓ Job completed: {$job->fresh()->status}");
            } catch (\Exception $e) {
                $this->error("✗ Job failed: {$e->getMessage()}");
                Log::error('Scrape job failed', [
                    'job_id' => $job->job_id,
                    'error' => $e->getMessage()
                ]);
            }

            // Sleep briefly to prevent CPU spinning
            sleep(1);

        } while (!$this->option('once'));

        return self::SUCCESS;
    }
}
