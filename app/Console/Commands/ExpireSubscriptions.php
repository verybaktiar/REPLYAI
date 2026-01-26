<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Deactivate expired subscriptions and move them to grace period or expired status';

    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        // Move expired subscriptions to grace period (7 days grace)
        $expiredCount = Subscription::where('status', 'active')
            ->where('expires_at', '<', now())
            ->whereNull('grace_period_ends_at')
            ->update([
                'status' => 'grace_period',
                'grace_period_ends_at' => now()->addDays(7),
            ]);

        $this->info("Moved {$expiredCount} subscriptions to grace period");

        // Fully expire subscriptions past grace period
        $fullyExpiredCount = Subscription::where('status', 'grace_period')
            ->where('grace_period_ends_at', '<', now())
            ->update([
                'status' => 'expired',
            ]);

        $this->info("Fully expired {$fullyExpiredCount} subscriptions");

        Log::info('Subscription expiry check completed', [
            'grace_period' => $expiredCount,
            'fully_expired' => $fullyExpiredCount,
        ]);

        return 0;
    }
}
