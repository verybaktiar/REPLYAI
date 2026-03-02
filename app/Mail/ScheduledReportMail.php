<?php

namespace App\Mail;

use App\Models\ScheduledReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduledReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ScheduledReport $report;
    public string $filePath;
    public string $fileName;

    public function __construct(ScheduledReport $report, string $filePath, string $fileName)
    {
        $this->report = $report;
        $this->filePath = $filePath;
        $this->fileName = $fileName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Laporan Terjadwal: {$this->report->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.scheduled-report',
            with: [
                'reportName' => $this->report->name,
                'reportType' => $this->getReportTypeLabel(),
                'frequency' => $this->report->frequency,
                'generatedAt' => now()->format('d M Y H:i'),
            ]
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->fileName)
                ->withMime($this->getMimeType()),
        ];
    }

    private function getReportTypeLabel(): string
    {
        $labels = [
            'analytics' => 'Analitik',
            'ai_performance' => 'Performa AI',
            'csat' => 'Kepuasan Pelanggan',
            'conversation_quality' => 'Kualitas Percakapan',
        ];
        
        return $labels[$this->report->report_type] ?? 'Umum';
    }

    private function getMimeType(): string
    {
        $mimes = [
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
        ];
        
        return $mimes[$this->report->format] ?? 'application/octet-stream';
    }
}
