<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Mail\PendingPaymentReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Command: Send Pending Payment Reminders
 * 
 * Kirim email reminder ke user yang punya pembayaran pending
 * dan akan expired dalam waktu dekat (default: 4 jam)
 * 
 * Usage:
 *   php artisan payments:send-reminders
 *   php artisan payments:send-reminders --hours=2
 * 
 * Recommended: Jalankan setiap jam via scheduler
 */
class SendPendingPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:send-reminders 
                            {--hours=4 : Hours before expiration to send reminder}
                            {--dry-run : Show what would be sent without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for pending payments nearing expiration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        
        $this->info("🔍 Looking for pending payments expiring in {$hours} hours...");
        
        // Cari pembayaran yang akan expired dalam X jam
        // dan belum pernah dikirim reminder (atau reminder terakhir sudah lama)
        $expiringSoon = Payment::where('status', Payment::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addHours($hours))
            ->where(function($query) {
                // Belum pernah dikirim reminder ATAU reminder terakhir > 12 jam yang lalu
                $query->whereNull('reminder_sent_at')
                      ->orWhere('reminder_sent_at', '<=', now()->subHours(12));
            })
            ->with(['user', 'plan'])
            ->get();
        
        if ($expiringSoon->isEmpty()) {
            $this->info('No pending payments need reminders.');
            return 0;
        }
        
        $this->info("Found {$expiringSoon->count()} payment(s) needing reminders.");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent');
        }
        
        $sent = 0;
        $failed = 0;
        
        foreach ($expiringSoon as $payment) {
            $user = $payment->user;
            
            if (!$user || !$user->email) {
                $this->warn("Skipping payment {$payment->invoice_number} - no user email");
                $failed++;
                continue;
            }
            
            $timeLeft = $payment->expires_at->diffForHumans();
            
            $this->info("Processing: {$payment->invoice_number} for {$user->email} (expires {$timeLeft})");
            
            if (!$dryRun) {
                try {
                    Mail::to($user->email)
                        ->queue(new PendingPaymentReminderMail($payment));
                    
                    // Update reminder_sent_at
                    $payment->update(['reminder_sent_at' => now()]);
                    
                    $sent++;
                    
                    Log::info('Pending payment reminder queued', [
                        'payment_id' => $payment->id,
                        'invoice' => $payment->invoice_number,
                        'user_id' => $user->id,
                        'expires_at' => $payment->expires_at,
                    ]);
                    
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Failed to send payment reminder', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("Failed: {$e->getMessage()}");
                }
            } else {
                $sent++;
            }
        }
        
        if ($dryRun) {
            $this->info("\n✅ Would send {$sent} reminder(s) (dry run)");
        } else {
            $this->info("\n✅ Sent {$sent} reminder(s), {$failed} failed");
        }
        
        return 0;
    }
}
