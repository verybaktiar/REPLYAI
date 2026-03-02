<?php

namespace App\Services;

use App\Models\ScheduledReport;
use App\Mail\ScheduledReportMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReportNotificationService
{
    /**
     * Send scheduled report notification
     */
    public function sendReport(ScheduledReport $report, string $filePath, string $fileName): bool
    {
        try {
            $emails = explode(',', $report->email_to);
            $emails = array_map('trim', $emails);
            
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($email)->send(new ScheduledReportMail($report, $filePath, $fileName));
                }
            }
            
            // Log success
            ActivityLogService::log(
                'scheduled_report_sent',
                "Scheduled report '{$report->name}' sent to {$report->email_to}",
                $report,
                $report->user_id
            );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send scheduled report', [
                'report_id' => $report->id,
                'error' => $e->getMessage()
            ]);
            
            ActivityLogService::log(
                'scheduled_report_failed',
                "Failed to send scheduled report '{$report->name}': {$e->getMessage()}",
                $report,
                $report->user_id,
                'error'
            );
            
            return false;
        }
    }
    
    /**
     * Send report generation failure notification
     */
    public function sendFailureNotification(ScheduledReport $report, string $error): void
    {
        try {
            // Notify user about failure
            $report->user->notify(new \App\Notifications\ReportGenerationFailed($report, $error));
        } catch (\Exception $e) {
            Log::error('Failed to send failure notification', [
                'report_id' => $report->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
