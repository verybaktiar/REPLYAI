<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Response;
use Illuminate\Mail\Attachment;
use Exception;

/**
 * Service untuk generate dan export laporan dalam berbagai format
 * Mendukung: PDF, Excel, CSV
 */
class ReportExportService
{
    /**
     * Format yang didukung
     */
    protected array $supportedFormats = ['pdf', 'excel', 'csv'];

    /**
     * Direktori penyimpanan laporan
     */
    protected string $storagePath = 'reports';

    /**
     * Main method untuk generate laporan
     *
     * @param array $reportData Data laporan yang akan digenerate
     * @param string $format Format export: 'pdf', 'excel', 'csv'
     * @param string $type Tipe laporan: 'analytics', 'ai_performance', 'csat', 'conversation_quality'
     * @return array|Response Array dengan file path atau Response download
     * @throws Exception
     */
    public function generateReport(array $reportData, string $format, string $type = 'analytics'): array|Response
    {
        try {
            // Validasi format
            if (!in_array(strtolower($format), $this->supportedFormats, true)) {
                throw new Exception("Format '{$format}' tidak didukung. Format yang tersedia: " . implode(', ', $this->supportedFormats));
            }

            $format = strtolower($format);
            $filename = $this->getReportFilename($type, $format);
            $fullPath = storage_path("app/{$this->storagePath}/" . $filename);

            // Ensure directory exists
            $this->ensureDirectoryExists();

            Log::info('Generating report', [
                'type' => $type,
                'format' => $format,
                'filename' => $filename,
            ]);

            switch ($format) {
                case 'pdf':
                    $content = $this->generatePDF($reportData, $type);
                    file_put_contents($fullPath, $content);
                    break;

                case 'excel':
                    $content = $this->generateExcel($reportData, $type);
                    file_put_contents($fullPath, $content);
                    break;

                case 'csv':
                    $content = $this->generateCSV($reportData);
                    file_put_contents($fullPath, $content);
                    break;

                default:
                    throw new Exception("Format tidak dikenali: {$format}");
            }

            ActivityLogService::log(
                'report.generated',
                "Laporan {$type} berhasil digenerate dalam format {$format}",
                null,
                [
                    'type' => $type,
                    'format' => $format,
                    'filename' => $filename,
                    'size' => filesize($fullPath),
                ],
                'success'
            );

            return [
                'success' => true,
                'file_path' => $fullPath,
                'filename' => $filename,
                'format' => $format,
                'type' => $type,
                'size' => filesize($fullPath),
                'download_url' => route('reports.download', ['filename' => $filename]),
            ];
        } catch (Exception $e) {
            Log::error('Failed to generate report', [
                'type' => $type,
                'format' => $format,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            ActivityLogService::error(
                'report.failed',
                "Gagal generate laporan: {$e->getMessage()}",
                null,
                [
                    'type' => $type,
                    'format' => $format,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Generate PDF report
     *
     * @param array $data Data untuk laporan
     * @param string $template Template yang digunakan
     * @return string PDF content
     * @throws Exception
     */
    public function generatePDF(array $data, string $template): string
    {
        try {
            $html = $this->createReportView($template, $data);

            // Cek apakah DOMPDF tersedia
            if (class_exists('Dompdf\Dompdf')) {
                return $this->generatePDFWithDompdf($html);
            }

            // Cek apakah Browsershot tersedia
            if (class_exists('Spatie\Browsershot\Browsershot')) {
                return $this->generatePDFWithBrowsershot($html);
            }

            // Fallback: return HTML dengan header PDF
            Log::warning('No PDF library found, using HTML fallback with PDF headers');
            return $this->generatePDFWithFallback($html);
        } catch (Exception $e) {
            Log::error('PDF generation failed', [
                'template' => $template,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Gagal generate PDF: {$e->getMessage()}");
        }
    }

    /**
     * Generate PDF menggunakan DOMPDF
     *
     * @param string $html HTML content
     * @return string PDF content
     */
    protected function generatePDFWithDompdf(string $html): string
    {
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Generate PDF menggunakan Browsershot
     *
     * @param string $html HTML content
     * @return string PDF content
     */
    protected function generatePDFWithBrowsershot(string $html): string
    {
        return \Spatie\Browsershot\Browsershot::html($html)
            ->format('A4')
            ->pdf();
    }

    /**
     * Fallback PDF generation - returns HTML with PDF headers suggestion
     *
     * @param string $html HTML content
     * @return string
     */
    protected function generatePDFWithFallback(string $html): string
    {
        // Tambahkan CSS print-friendly
        $pdfStyles = '
        <style>
            @media print {
                body { font-family: Arial, sans-serif; }
                .no-print { display: none; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f4f4f4; }
            }
        </style>';

        return str_replace('</head>', $pdfStyles . '</head>', $html);
    }

    /**
     * Generate Excel report
     *
     * @param array $data Data untuk laporan
     * @param string $template Template yang digunakan
     * @return string Excel content
     * @throws Exception
     */
    public function generateExcel(array $data, string $template): string
    {
        try {
            // Cek apakah maatwebsite/excel tersedia
            if (class_exists('Maatwebsite\Excel\Facades\Excel')) {
                return $this->generateExcelWithMaatwebsite($data, $template);
            }

            // Cek apakah Spout tersedia
            if (class_exists('Box\Spout\Writer\Common\Creator\WriterEntityFactory')) {
                return $this->generateExcelWithSpout($data, $template);
            }

            // Fallback: Generate simple XLS (HTML table with Excel headers)
            Log::warning('No Excel library found, using HTML table fallback');
            return $this->generateExcelWithFallback($data, $template);
        } catch (Exception $e) {
            Log::error('Excel generation failed', [
                'template' => $template,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Gagal generate Excel: {$e->getMessage()}");
        }
    }

    /**
     * Generate Excel menggunakan Maatwebsite Excel
     *
     * @param array $data Data untuk laporan
     * @param string $template Template yang digunakan
     * @return string Excel content
     */
    protected function generateExcelWithMaatwebsite(array $data, string $template): string
    {
        // Implementasi sederhana untuk Maatwebsite Excel
        // Note: Perlu dibuat class Export terpisah untuk fitur lengkap
        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected array $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }
        };

        $tempPath = tempnam(sys_get_temp_dir(), 'excel_');
        \Maatwebsite\Excel\Facades\Excel::store($export, $tempPath);
        $content = file_get_contents($tempPath);
        unlink($tempPath);

        return $content;
    }

    /**
     * Generate Excel menggunakan Spout
     *
     * @param array $data Data untuk laporan
     * @param string $template Template yang digunakan
     * @return string Excel content
     */
    protected function generateExcelWithSpout(array $data, string $template): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'excel_') . '.xlsx';
        $writer = \Box\Spout\Writer\Common\Creator\WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($tempPath);

        // Header
        if (!empty($data)) {
            $headers = array_keys($data[0]);
            $headerRow = \Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($headers);
            $writer->addRow($headerRow);
        }

        // Data rows
        foreach ($data as $row) {
            $rowData = \Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($row);
            $writer->addRow($rowData);
        }

        $writer->close();
        $content = file_get_contents($tempPath);
        unlink($tempPath);

        return $content;
    }

    /**
     * Fallback Excel generation menggunakan HTML table
     *
     * @param array $data Data untuk laporan
     * @param string $template Template yang digunakan
     * @return string Excel content (HTML table)
     */
    protected function generateExcelWithFallback(array $data, string $template): string
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
        $html .= '<table border="1">';

        // Header
        if (!empty($data)) {
            $html .= '<tr>';
            foreach (array_keys($data[0]) as $header) {
                $html .= '<th style="background-color: #f4f4f4; font-weight: bold;">' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr>';
        }

        // Data rows
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return $html;
    }

    /**
     * Generate CSV report
     *
     * @param array $data Data untuk laporan
     * @return string CSV content
     * @throws Exception
     */
    public function generateCSV(array $data): string
    {
        try {
            if (empty($data)) {
                return '';
            }

            $output = fopen('php://temp', 'r+');

            // Write BOM for UTF-8
            fwrite($output, "\xEF\xBB\xBF");

            // Header
            fputcsv($output, array_keys($data[0]));

            // Data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }

            rewind($output);
            $csv = stream_get_contents($output);
            fclose($output);

            return $csv;
        } catch (Exception $e) {
            Log::error('CSV generation failed', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Gagal generate CSV: {$e->getMessage()}");
        }
    }

    /**
     * Create HTML view untuk laporan
     *
     * @param string $type Tipe laporan: 'analytics', 'ai_performance', 'csat', 'conversation_quality'
     * @param array $data Data laporan
     * @return string HTML string
     * @throws Exception
     */
    public function createReportView(string $type, array $data): string
    {
        $validTypes = ['analytics', 'ai_performance', 'csat', 'conversation_quality'];

        if (!in_array($type, $validTypes, true)) {
            throw new Exception("Tipe laporan '{$type}' tidak didukung. Tipe yang tersedia: " . implode(', ', $validTypes));
        }

        $viewName = "reports.{$type}";

        // Cek apakah view Blade tersedia
        if (View::exists($viewName)) {
            return View::make($viewName, $data)->render();
        }

        // Fallback: generate HTML manual
        return $this->createReportViewFallback($type, $data);
    }

    /**
     * Fallback HTML view generator
     *
     * @param string $type Tipe laporan
     * @param array $data Data laporan
     * @return string HTML string
     */
    protected function createReportViewFallback(string $type, array $data): string
    {
        $titles = [
            'analytics' => 'Laporan Analitik',
            'ai_performance' => 'Laporan Performa AI',
            'csat' => 'Laporan Kepuasan Pelanggan (CSAT)',
            'conversation_quality' => 'Laporan Kualitas Percakapan',
        ];

        $title = $titles[$type] ?? 'Laporan';
        $generatedAt = now()->format('d F Y H:i:s');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            padding-bottom: 20px;
            border-bottom: 3px solid #4a90d9;
        }
        .header h1 { 
            color: #2c3e50; 
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header .meta {
            color: #666;
            font-size: 11px;
        }
        .summary { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 30px; 
            gap: 15px;
        }
        .summary-box { 
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        .summary-box .value {
            font-size: 28px;
            font-weight: bold;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            background: white;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        tr:hover {
            background-color: #e3f2fd;
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 10px;
            color: #666;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$title}</h1>
        <div class="meta">Generated on: {$generatedAt}</div>
    </div>
HTML;

        // Summary boxes jika tersedia
        if (isset($data['summary'])) {
            $html .= '<div class="summary">';
            foreach ($data['summary'] as $label => $value) {
                $html .= <<<HTML
                <div class="summary-box">
                    <h3>{$label}</h3>
                    <div class="value">{$value}</div>
                </div>
HTML;
            }
            $html .= '</div>';
        }

        // Data table
        if (isset($data['items']) && !empty($data['items'])) {
            $html .= '<table>';
            
            // Table headers
            $html .= '<thead><tr>';
            foreach (array_keys($data['items'][0]) as $header) {
                $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
            }
            $html .= '</tr></thead>';

            // Table body
            $html .= '<tbody>';
            foreach ($data['items'] as $row) {
                $html .= '<tr>';
                foreach ($row as $key => $value) {
                    $cellValue = htmlspecialchars((string)$value);
                    
                    // Format status badges
                    if ($key === 'status' || $key === 'category') {
                        $badgeClass = match(strtolower((string)$value)) {
                            'success', 'completed', 'resolved', 'high' => 'badge-success',
                            'pending', 'waiting', 'medium' => 'badge-warning',
                            'failed', 'error', 'cancelled', 'low' => 'badge-danger',
                            default => 'badge-info',
                        };
                        $cellValue = '<span class="badge ' . $badgeClass . '">' . $cellValue . '</span>';
                    }
                    
                    $html .= '<td>' . $cellValue . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        // Charts section jika tersedia
        if (isset($data['charts'])) {
            $html .= '<div style="margin-top: 30px;">';
            foreach ($data['charts'] as $chartTitle => $chartData) {
                $html .= '<h3 style="margin-bottom: 15px; color: #2c3e50;">' . htmlspecialchars($chartTitle) . '</h3>';
                $html .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">';
                
                // Simple bar chart representation
                $maxValue = max($chartData);
                foreach ($chartData as $label => $value) {
                    $percentage = $maxValue > 0 ? ($value / $maxValue) * 100 : 0;
                    $html .= <<<HTML
                    <div style="margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>{$label}</span>
                            <span>{$value}</span>
                        </div>
                        <div style="background: #e9ecef; border-radius: 4px; height: 20px;">
                            <div style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); 
                                        height: 100%; border-radius: 4px; width: {$percentage}%;"></div>
                        </div>
                    </div>
HTML;
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= <<<HTML
    <div class="footer">
        <p>Generated by REPLYAI Reporting System</p>
        <p>&copy; {$this->getYear()} REPLYAI. All rights reserved.</p>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Generate nama file laporan dengan timestamp
     *
     * @param string $type Tipe laporan
     * @param string $format Format file
     * @return string Filename
     */
    public function getReportFilename(string $type, string $format): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $extension = match(strtolower($format)) {
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            'csv' => 'csv',
            default => $format,
        };

        return "{$type}_report_{$timestamp}.{$extension}";
    }

    /**
     * Kirim email dengan lampiran laporan
     *
     * @param array $report Data laporan dari generateReport()
     * @param string $email Alamat email tujuan
     * @param string $subject Subjek email
     * @return bool
     * @throws Exception
     */
    public function sendEmail(array $report, string $email, string $subject): bool
    {
        try {
            if (empty($report['file_path']) || !file_exists($report['file_path'])) {
                throw new Exception('File laporan tidak ditemukan');
            }

            $filename = $report['filename'];
            $type = $report['type'] ?? 'report';
            $format = $report['format'] ?? 'pdf';

            Log::info('Sending report email', [
                'email' => $email,
                'subject' => $subject,
                'filename' => $filename,
            ]);

            Mail::raw(
                "Berikut adalah laporan {$type} Anda dalam format {$format}.",
                function ($message) use ($email, $subject, $report, $filename) {
                    $message->to($email)
                        ->subject($subject)
                        ->attach($report['file_path'], [
                            'as' => $filename,
                            'mime' => $this->getMimeType($report['format']),
                        ]);
                }
            );

            ActivityLogService::success(
                'report.email_sent',
                "Laporan berhasil dikirim ke {$email}",
                null,
                [
                    'email' => $email,
                    'subject' => $subject,
                    'filename' => $filename,
                ]
            );

            return true;
        } catch (Exception $e) {
            Log::error('Failed to send report email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            ActivityLogService::error(
                'report.email_failed',
                "Gagal mengirim laporan: {$e->getMessage()}",
                null,
                [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]
            );

            throw new Exception("Gagal mengirim email: {$e->getMessage()}");
        }
    }

    /**
     * Download response untuk laporan
     *
     * @param string $filePath Path file
     * @param string $filename Nama file download
     * @return Response
     */
    public function download(string $filePath, string $filename): Response
    {
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        $format = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeType = $this->getMimeType($format);

        return response(file_get_contents($filePath), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get MIME type berdasarkan format
     *
     * @param string $format
     * @return string
     */
    protected function getMimeType(string $format): string
    {
        return match(strtolower($format)) {
            'pdf' => 'application/pdf',
            'excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            default => 'application/octet-stream',
        };
    }

    /**
     * Ensure storage directory exists
     *
     * @return void
     */
    protected function ensureDirectoryExists(): void
    {
        $path = storage_path("app/{$this->storagePath}");
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Get current year
     *
     * @return string
     */
    protected function getYear(): string
    {
        return date('Y');
    }

    /**
     * Get supported formats
     *
     * @return array
     */
    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }

    /**
     * Clean old reports
     *
     * @param int $days Hapus laporan yang lebih dari X hari
     * @return int Jumlah file yang dihapus
     */
    public function cleanOldReports(int $days = 30): int
    {
        $path = storage_path("app/{$this->storagePath}");
        $deleted = 0;
        $threshold = now()->subDays($days)->getTimestamp();

        if (!is_dir($path)) {
            return 0;
        }

        $files = glob("{$path}/*");
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $threshold) {
                unlink($file);
                $deleted++;
            }
        }

        Log::info('Cleaned old reports', [
            'deleted' => $deleted,
            'threshold_days' => $days,
        ]);

        return $deleted;
    }
}
