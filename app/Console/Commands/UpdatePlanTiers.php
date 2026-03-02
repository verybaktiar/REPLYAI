<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plan;

class UpdatePlanTiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:update-tiers {--dry-run : Show changes without applying}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update plan tiers based on pricing (UMKM < 200k, Business 200k-500k, Enterprise > 500k)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $plans = Plan::all();
        
        $this->info('Updating plan tiers...');
        $this->table(['Plan', 'Price', 'Old Tier', 'New Tier'], []);
        
        foreach ($plans as $plan) {
            $oldTier = $plan->tier;
            
            // Logic: Based on monthly price
            if ($plan->price_monthly >= 500000) {
                $newTier = 'enterprise';
            } elseif ($plan->price_monthly >= 200000) {
                $newTier = 'business';
            } else {
                $newTier = 'umkm';
            }
            
            // Override based on name/slug (common patterns)
            $slug = strtolower($plan->slug);
            if (str_contains($slug, 'enterprise') || str_contains($slug, 'vip')) {
                $newTier = 'enterprise';
            } elseif (str_contains($slug, 'business') || str_contains($slug, 'pro')) {
                $newTier = 'business';
            } elseif (str_contains($slug, 'starter') || str_contains($slug, 'basic') || str_contains($slug, 'free') || str_contains($slug, 'gratis')) {
                $newTier = 'umkm';
            }
            
            $this->info(sprintf(
                "%s | Rp %s | %s | %s",
                $plan->name,
                number_format($plan->price_monthly),
                $oldTier ?? 'null',
                $newTier
            ));
            
            if (!$this->option('dry-run')) {
                $plan->update(['tier' => $newTier]);
            }
        }
        
        if ($this->option('dry-run')) {
            $this->warn('This was a dry run. No changes were made.');
            $this->info('Run without --dry-run to apply changes.');
        } else {
            $this->info('Plan tiers updated successfully!');
        }
        
        return Command::SUCCESS;
    }
}
