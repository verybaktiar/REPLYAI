<?php

namespace App\Notifications;

use App\Models\ScheduledReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportGenerationFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public ScheduledReport $report;
    public string $error;

    public function __construct(ScheduledReport $report, string $error)
    {
        $this->report = $report;
        $this->error = $error;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Gagal Generate Laporan')
            ->line("Pembuatan laporan terjadwal '{$this->report->name}' gagal.")
            ->line('Error: ' . $this->error)
            ->line('Silakan cek pengaturan laporan Anda.')
            ->action('Kelola Laporan', url('/reports/scheduled'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'report_name' => $this->report->name,
            'error' => $this->error,
            'type' => 'report_generation_failed',
        ];
    }
}
