<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UsageTrackingService;
use Illuminate\Support\Facades\Log;

/**
 * Command: Reset Penggunaan Bulanan
 * 
 * Command ini mereset counter penggunaan bulanan.
 * Harus dijalankan setiap tanggal 1 jam 00:00 via cron job.
 * 
 * Yang direset:
 * - Jumlah pesan AI (ai_messages)
 * - Jumlah broadcast (broadcasts)
 * 
 * Yang TIDAK direset (lifetime):
 * - Kontak
 * - Storage KB
 * - Jumlah device dll
 * 
 * Cara menjalankan:
 * php artisan usage:reset-monthly
 * 
 * Cron (jalankan setiap tanggal 1):
 * 0 0 1 * * cd /path/to/project && php artisan usage:reset-monthly
 */
class ResetMonthlyUsage extends Command
{
    /**
     * Nama dan signature command
     * 
     * @var string
     */
    protected $signature = 'usage:reset-monthly';

    /**
     * Deskripsi command
     * 
     * @var string
     */
    protected $description = 'Reset counter penggunaan bulanan (AI messages, broadcasts)';

    protected UsageTrackingService $tracker;

    public function __construct(UsageTrackingService $tracker)
    {
        parent::__construct();
        $this->tracker = $tracker;
    }

    /**
     * Jalankan command
     */
    public function handle(): int
    {
        $this->info('🔄 Mereset penggunaan bulanan...');
        $this->newLine();

        $count = $this->tracker->resetMonthlyCounters();

        $this->info("📊 Jumlah record periode lalu: {$count}");
        $this->info("   (Record baru dengan counter 0 akan dibuat otomatis)");

        // Log
        Log::info('Monthly usage reset completed', [
            'old_records' => $count,
            'month' => now()->format('Y-m'),
        ]);

        $this->newLine();
        $this->info('✅ Reset penggunaan bulanan selesai!');

        return Command::SUCCESS;
    }
}
