<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Subscription;
use App\Models\EmailLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExpiryReminders extends Command
{
    protected $signature = 'subscriptions:send-expiry-reminders';
    protected $description = 'Send reminder emails to users whose subscriptions are expiring soon';

    public function handle()
    {
        $this->info('Checking for expiring subscriptions...');

        // Reminder 7 days before
        $this->sendReminders(7);
        
        // Reminder 3 days before
        $this->sendReminders(3);
        
        // Reminder 1 day before
        $this->sendReminders(1);

        $this->info('Done!');
        return 0;
    }

    protected function sendReminders(int $days)
    {
        $targetDate = now()->addDays($days)->startOfDay();
        
        $subscriptions = Subscription::where('status', 'active')
            ->whereDate('expires_at', $targetDate)
            ->with(['user', 'plan'])
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions expiring in {$days} days");

        foreach ($subscriptions as $subscription) {
            if (!$subscription->user || !$subscription->user->email) {
                continue;
            }

            // Check if we already sent reminder today
            $alreadySent = EmailLog::where('user_id', $subscription->user_id)
                ->where('template', 'subscription_expiring_' . $days)
                ->whereDate('created_at', today())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            try {
                Mail::to($subscription->user->email)
                    ->queue(new SubscriptionExpiringMail($subscription, $days));

                EmailLog::log(
                    $subscription->user_id,
                    $subscription->user->email,
                    "Langganan akan berakhir dalam {$days} hari",
                    'subscription_expiring_' . $days
                );

                $this->info("Sent reminder to: {$subscription->user->email}");

            } catch (\Exception $e) {
                $this->error("Failed to send to {$subscription->user->email}: {$e->getMessage()}");
            }
        }
    }
}
