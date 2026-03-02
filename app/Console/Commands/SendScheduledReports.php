<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Services\ReportExportService;
use App\Services\ReportNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SendScheduledReports
 * 
 * Command untuk memproses dan mengirim laporan terjadwal.
 * Dijalankan oleh scheduler setiap jam.
 * 
 * Cara kerja:
 * 1. Cari semua laporan terjadwal yang aktif dan waktu kirimnya sudah tercapai
 * 2. Generate file laporan (PDF/Excel/CSV) sesuai format yang dipilih
 * 3. Kirim email dengan lampiran laporan
 * 4. Update last_sent_at dan next_send_at
 * 
 * Usage:
 *   php artisan reports:send-scheduled
 */
class SendScheduledReports extends Command
{
    /**
     * Nama dan signature command
     *
     * @var string
     */
    protected $signature = 'reports:send-scheduled 
                            {--dry-run : Tampilkan laporan yang akan dikirim tanpa mengirimnya}
                            {--report-id= : Kirim laporan tertentu saja}';

    /**
     * Deskripsi command
     *
     * @var string
     */
    protected $description = 'Kirim laporan terjadwal yang sudah waktunya dikirim';

    /**
     * @var ReportExportService
     */
    protected $exportService;

    /**
     * @var ReportNotificationService
     */
    protected $notificationService;

    /**
     * Constructor
     */
    public function __construct(
        ReportExportService $exportService,
        ReportNotificationService $notificationService
    ) {
        parent::__construct();
        $this->exportService = $exportService;
        $this->notificationService = $notificationService;
    }

    /**
     * Jalankan command
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $reportId = $this->option('report-id');

        $this->info('🚀 Memulai proses pengiriman laporan terjadwal...');

        // Query laporan yang perlu dikirim
        $query = ScheduledReport::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_send_at')
                    ->orWhere('next_send_at', '<=', now());
            });

        // Jika ada report-id yang spesifik
        if ($reportId) {
            $query->where('id', $reportId);
            $this->info("📋 Mencari laporan ID: {$reportId}");
        }

        $reports = $query->with('user', 'template')->get();

        if ($reports->isEmpty()) {
            $this->info('ℹ️  Tidak ada laporan yang perlu dikirim saat ini.');
            return Command::SUCCESS;
        }

        $this->info("📊 Ditemukan {$reports->count()} laporan untuk diproses.");

        if ($dryRun) {
            $this->showDryRunTable($reports);
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($reports as $report) {
            try {
                $this->processReport($report);
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
                $this->error("❌ Gagal memproses laporan #{$report->id}: {$e->getMessage()}");
                Log::error('Scheduled report failed', [
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->newLine();
        $this->info("✅ Proses selesai:");
        $this->info("   - Berhasil: {$successCount}");
        $this->info("   - Gagal: {$failCount}");

        return $failCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Proses satu laporan terjadwal
     */
    protected function processReport(ScheduledReport $report): void
    {
        $this->info("📧 Memproses laporan: {$report->name} (User: {$report->user->name})");

        // Generate laporan sesuai format
        $filePath = $this->generateReportFile($report);

        // Kirim notifikasi email
        $this->notificationService->sendScheduledReport(
            $report,
            $filePath
        );

        // Update jadwal
        $this->updateSchedule($report);

        // Log aktivitas
        Log::info('Scheduled report sent', [
            'report_id' => $report->id,
            'user_id' => $report->user_id,
            'format' => $report->format,
        ]);

        $this->info("   ✅ Laporan berhasil dikirim ke {$report->recipient_email}");
    }

    /**
     * Generate file laporan sesuai format
     */
    protected function generateReportFile(ScheduledReport $report): string
    {
        $format = $report->format ?? 'pdf';
        $dateRange = $this->calculateDateRange($report);

        return match ($format) {
            'pdf' => $this->exportService->generatePdf(
                $report,
                $dateRange['start'],
                $dateRange['end']
            ),
            'excel' => $this->exportService->generateExcel(
                $report,
                $dateRange['start'],
                $dateRange['end']
            ),
            'csv' => $this->exportService->generateCsv(
                $report,
                $dateRange['start'],
                $dateRange['end']
            ),
            default => throw new \InvalidArgumentException("Format tidak didukung: {$format}"),
        };
    }

    /**
     * Hitung rentang tanggal berdasarkan periode laporan
     */
    protected function calculateDateRange(ScheduledReport $report): array
    {
        $end = now();

        $start = match ($report->period) {
            'daily' => $end->copy()->subDay(),
            'weekly' => $end->copy()->subWeek(),
            'monthly' => $end->copy()->subMonth(),
            'quarterly' => $end->copy()->subQuarter(),
            'custom' => $report->custom_start_date ?? $end->copy()->subMonth(),
            default => $end->copy()->subDay(),
        };

        return [
            'start' => $start->startOfDay(),
            'end' => $end->endOfDay(),
        ];
    }

    /**
     * Update last_sent_at dan hitung next_send_at berikutnya
     */
    protected function updateSchedule(ScheduledReport $report): void
    {
        $now = now();
        $nextSendAt = $this->calculateNextSendAt($report, $now);

        $report->update([
            'last_sent_at' => $now,
            'next_send_at' => $nextSendAt,
            'last_sent_status' => 'success',
        ]);
    }

    /**
     * Hitung waktu kirim berikutnya
     */
    protected function calculateNextSendAt(ScheduledReport $report, Carbon $from): Carbon
    {
        $sendTime = $report->send_time ?? '09:00';
        [$hour, $minute] = explode(':', $sendTime);

        $next = match ($report->frequency) {
            'hourly' => $from->copy()->addHour(),
            'daily' => $from->copy()->addDay()->setTime((int) $hour, (int) $minute),
            'weekly' => $from->copy()->addWeek()->setTime((int) $hour, (int) $minute),
            'monthly' => $from->copy()->addMonth()->setTime((int) $hour, (int) $minute),
            'custom' => $this->calculateCustomNextSend($report, $from),
            default => $from->copy()->addDay()->setTime((int) $hour, (int) $minute),
        };

        return $next;
    }

    /**
     * Hitung waktu kirim untuk jadwal custom
     */
    protected function calculateCustomNextSend(ScheduledReport $report, Carbon $from): Carbon
    {
        $days = $report->custom_days ?? [1]; // Default: Senin
        $sendTime = $report->send_time ?? '09:00';
        [$hour, $minute] = explode(':', $sendTime);

        $next = $from->copy()->addDay()->setTime((int) $hour, (int) $minute);

        // Cari hari berikutnya yang valid
        while (!in_array($next->dayOfWeek, $days)) {
            $next->addDay();
        }

        return $next;
    }

    /**
     * Tampilkan tabel dry-run
     */
    protected function showDryRunTable($reports): void
    {
        $this->newLine();
        $this->warn('🔍 DRY RUN MODE - Tidak ada email yang akan dikirim');
        $this->newLine();

        $rows = $reports->map(function ($report) {
            return [
                $report->id,
                $report->name,
                $report->user->name,
                $report->frequency,
                $report->format,
                $report->recipient_email,
                $report->next_send_at?->format('Y-m-d H:i') ?? 'Now',
            ];
        });

        $this->table(
            ['ID', 'Name', 'User', 'Frequency', 'Format', 'Recipient', 'Next Send'],
            $rows
        );
    }
}
