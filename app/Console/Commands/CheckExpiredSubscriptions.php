<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionService;
use App\Models\Subscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Command: Cek Langganan Expired
 * 
 * Command ini menjalankan pengecekan langganan yang expired.
 * Harus dijalankan setiap hari via cron job.
 * 
 * Yang dilakukan:
 * 1. Tandai langganan yang baru expired (masuk grace period)
 * 2. Kunci langganan yang grace period-nya habis
 * 3. Downgrade trial yang expired ke paket gratis
 * 4. Kirim email reminder untuk yang akan expired
 * 
 * Cara menjalankan:
 * php artisan subscription:check-expired
 * 
 * Cron (jalankan setiap hari jam 00:01):
 * 1 0 * * * cd /path/to/project && php artisan subscription:check-expired
 */
class CheckExpiredSubscriptions extends Command
{
    /**
     * Nama dan signature command
     * 
     * @var string
     */
    protected $signature = 'subscription:check-expired 
                            {--send-reminders : Kirim email reminder juga}';

    /**
     * Deskripsi command
     * 
     * @var string
     */
    protected $description = 'Cek dan proses langganan yang expired serta kirim reminder';

    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Jalankan command
     */
    public function handle(): int
    {
        $this->info('🔄 Memulai pengecekan langganan...');
        $this->newLine();

        // 1. Proses expired subscriptions
        $stats = $this->subscriptionService->processExpiringSubscriptions();

        $this->info('📊 Hasil Proses:');
        $this->table(
            ['Status', 'Jumlah'],
            [
                ['Masuk Grace Period', $stats['grace_period']],
                ['Trial Expired', $stats['expired']],
                ['Dikunci (Locked)', $stats['locked']],
            ]
        );

        // Log hasil
        Log::info('Subscription check completed', $stats);

        // 2. Kirim reminder jika diminta
        if ($this->option('send-reminders')) {
            $this->sendReminders();
        }

        $this->newLine();
        $this->info('✅ Pengecekan langganan selesai!');

        return Command::SUCCESS;
    }

    /**
     * Kirim email reminder untuk langganan yang akan expired
     */
    private function sendReminders(): void
    {
        $this->info('📧 Mengirim reminder...');

        // Reminder 7 hari sebelum expired
        $expiringIn7Days = $this->subscriptionService->getExpiringIn(7);
        $this->info("   - Akan expired dalam 7 hari: {$expiringIn7Days->count()} user");

        foreach ($expiringIn7Days as $subscription) {
            $this->sendReminderEmail($subscription, 7);
        }

        // Reminder 3 hari sebelum expired
        $expiringIn3Days = $this->subscriptionService->getExpiringIn(3);
        $this->info("   - Akan expired dalam 3 hari: {$expiringIn3Days->count()} user");

        foreach ($expiringIn3Days as $subscription) {
            $this->sendReminderEmail($subscription, 3);
        }

        // Reminder 1 hari sebelum expired
        $expiringIn1Day = $this->subscriptionService->getExpiringIn(1);
        $this->info("   - Akan expired dalam 1 hari: {$expiringIn1Day->count()} user");

        foreach ($expiringIn1Day as $subscription) {
            $this->sendReminderEmail($subscription, 1);
        }
    }

    /**
     * Kirim email reminder ke user
     * 
     * @param Subscription $subscription
     * @param int $daysLeft
     */
    private function sendReminderEmail(Subscription $subscription, int $daysLeft): void
    {
        try {
            $user = $subscription->user;
            $plan = $subscription->plan;

            // TODO: Implement proper Mailable
            // Mail::to($user->email)->send(new SubscriptionExpiringMail($subscription, $daysLeft));

            // Untuk sementara, log saja
            Log::info("Reminder email sent", [
                'user_id' => $user->id,
                'email' => $user->email,
                'plan' => $plan->name,
                'days_left' => $daysLeft,
                'expires_at' => $subscription->expires_at->toDateString(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send reminder email", [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
