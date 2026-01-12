<?php

namespace App\Console\Commands;

use App\Services\SequenceService;
use Illuminate\Console\Command;

/**
 * ProcessSequenceSteps
 * 
 * Command untuk memproses enrollment sequence yang sudah waktunya.
 * Dijalankan oleh scheduler setiap menit.
 * 
 * Cara kerja:
 * 1. Cari semua enrollment dengan status 'active' dan next_run_at <= now()
 * 2. Untuk setiap enrollment, kirim pesan sesuai step saat ini
 * 3. Pindahkan ke step berikutnya dan jadwalkan next_run_at
 * 4. Jika tidak ada step berikutnya, tandai sebagai completed
 */
class ProcessSequenceSteps extends Command
{
    /**
     * Nama dan signature command
     */
    protected $signature = 'sequence:process';

    /**
     * Deskripsi command
     */
    protected $description = 'Proses langkah-langkah sequence yang sudah waktunya dijalankan';

    /**
     * Jalankan command
     */
    public function handle(SequenceService $sequenceService): int
    {
        $this->info('🚀 Mulai memproses sequence enrollments...');

        $processed = $sequenceService->processReadyEnrollments();

        if ($processed > 0) {
            $this->info("✅ Berhasil memproses {$processed} enrollment");
        } else {
            $this->info('ℹ️  Tidak ada enrollment yang perlu diproses saat ini');
        }

        return Command::SUCCESS;
    }
}
