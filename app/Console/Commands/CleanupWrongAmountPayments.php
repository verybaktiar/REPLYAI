<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command: Cleanup Wrong Amount Payments
 * 
 * Membersihkan payments dengan amount yang tidak sesuai durasi
 * (untuk fixing data lama sebelum bug fix)
 * 
 * Usage:
 *   php artisan payments:cleanup-wrong-amount --dry-run
 *   php artisan payments:cleanup-wrong-amount --force
 */
class CleanupWrongAmountPayments extends Command
{
    protected $signature = 'payments:cleanup-wrong-amount 
                            {--dry-run : Show what would be fixed without fixing}
                            {--force : Skip confirmation}';

    protected $description = 'Fix payments with wrong amount for their duration';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Checking for payments with wrong amounts...');

        // Cari payments pending dengan amount tidak sesuai
        $wrongPayments = Payment::where('status', Payment::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->with('plan')
            ->get()
            ->filter(function ($payment) {
                if (!$payment->plan) return false;
                
                $expectedAmount = $payment->duration_months == 12 
                    ? $payment->plan->price_yearly 
                    : $payment->plan->price_monthly;
                
                return $payment->amount !== $expectedAmount;
            });

        if ($wrongPayments->isEmpty()) {
            $this->info('✅ No wrong amount payments found.');
            return 0;
        }

        $this->warn("Found {$wrongPayments->count()} payment(s) with wrong amount:");
        
        foreach ($wrongPayments as $payment) {
            $expectedAmount = $payment->duration_months == 12 
                ? $payment->plan->price_yearly 
                : $payment->plan->price_monthly;
            
            $this->line("");
            $this->line("  Invoice: {$payment->invoice_number}");
            $this->line("  Plan: {$payment->plan->name}");
            $this->line("  Duration: {$payment->duration_months} bulan");
            $this->line("  Current Amount: Rp " . number_format($payment->amount, 0, ',', '.'));
            $this->line("  Expected Amount: Rp " . number_format($expectedAmount, 0, ',', '.'));
        }

        if ($dryRun) {
            $this->line("");
            $this->warn('DRY RUN - No changes made.');
            return 0;
        }

        if (!$force) {
            $this->line("");
            if (!$this->confirm('Do you want to CANCEL these wrong payments?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $count = 0;
        foreach ($wrongPayments as $payment) {
            $payment->update([
                'status' => 'failed',
                'admin_notes' => 'Auto-cancelled: Wrong amount for duration',
            ]);
            
            Log::info('Wrong amount payment cancelled', [
                'payment_id' => $payment->id,
                'invoice' => $payment->invoice_number,
            ]);
            
            $count++;
        }

        $this->info("✅ {$count} payment(s) have been cancelled.");
        $this->info("Users can now create new payments with correct amounts.");

        return 0;
    }
}
