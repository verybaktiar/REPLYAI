<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

/**
 * Command: Cleanup Expired Payments
 * 
 * FIX-007: Membersihkan pembayaran yang sudah expired
 * 
 * Usage:
 *   php artisan payments:cleanup-expired
 *   php artisan payments:cleanup-expired --force (tanpa konfirmasi)
 * 
 * Recommended: Jalankan setiap jam via scheduler
 */
class CleanupExpiredPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:cleanup-expired {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired pending payments';

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for expired payments...');

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to cleanup expired payments?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $count = $this->paymentService->cleanupExpiredPayments();

        if ($count > 0) {
            $this->info("✅ {$count} expired payments have been cleaned up.");
        } else {
            $this->info('No expired payments found.');
        }

        return 0;
    }
}
