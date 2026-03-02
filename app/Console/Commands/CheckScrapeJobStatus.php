<?php

namespace App\Console\Commands;

use App\Models\KbScrapeJob;
use Illuminate\Console\Command;

class CheckScrapeJobStatus extends Command
{
    protected $signature = 'scrape:status {jobId}';

    protected $description = 'Check status of a scrape job';

    public function handle(): int
    {
        $jobId = $this->argument('jobId');
        $job = KbScrapeJob::where('job_id', $jobId)->first();

        if (!$job) {
            $this->error("Job {$jobId} not found");
            return self::FAILURE;
        }

        $this->info("Job ID: {$job->job_id}");
        $this->info("Status: {$job->status}");
        $this->info("Progress: {$job->progress_percent}%");
        $this->info("Step: {$job->progress_step}");
        $this->info("URL: {$job->url}");

        if ($job->status === 'completed') {
            $this->newLine();
            $this->info("=== EXTRACTED DATA ===");
            
            $data = $job->scraped_data;
            $entities = $job->extracted_entities;
            
            $this->info("Title: " . ($data['title'] ?? 'N/A'));
            $this->info("Business: " . ($data['businessName'] ?? 'N/A'));
            $this->info("Pricing packages: " . ($entities['pricingCount'] ?? 0));
            $this->info("FAQ items: " . ($entities['faqCount'] ?? 0));
            
            if (!empty($data['pricing'])) {
                $this->newLine();
                $this->info("=== PRICING ===");
                foreach ($data['pricing'] as $pkg) {
                    $this->info("- {$pkg['name']}: {$pkg['price']}");
                }
            }
        }

        if ($job->status === 'failed') {
            $this->newLine();
            $this->error("Error: {$job->error_message}");
        }

        return self::SUCCESS;
    }
}
