<?php

namespace App\Http\Controllers;

use App\Models\ScheduledReport;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduledReportController extends Controller
{
    /**
     * Display a listing of scheduled reports.
     */
    public function index()
    {
        $user = Auth::user();
        
        $reports = ScheduledReport::where('user_id', $user->id)
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get();
        
        $stats = [
            'total' => $reports->count(),
            'active' => $reports->where('is_active', true)->count(),
            'inactive' => $reports->where('is_active', false)->count(),
            'sent' => $reports->whereNotNull('last_sent_at')->count(),
        ];

        return view('pages.reports.scheduled.index', [
            'reports' => $reports,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created scheduled report.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'report_type' => ['required', 'string', 'in:analytics,ai_performance,csat,conversation_quality'],
            'frequency' => ['required', 'string', 'in:daily,weekly,monthly'],
            'day_of_week' => ['required_if:frequency,weekly', 'nullable', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'day_of_month' => ['required_if:frequency,monthly', 'nullable', 'integer', 'min:1', 'max:31'],
            'send_time' => ['required', 'date_format:H:i'],
            'email_to' => ['required', 'string', 'email'],
            'format' => ['required', 'string', 'in:pdf,excel,csv'],
            'filters' => ['nullable', 'array'],
            'filters.platform' => ['nullable', 'string', 'in:all,whatsapp,instagram,web'],
            'filters.date_range' => ['nullable', 'integer', 'min:1', 'max:365'],
            'filters.metrics' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['user_id'] = $user->id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Calculate next_send_at based on frequency
        $validated['next_send_at'] = $this->calculateNextSendAt($validated);

        $report = ScheduledReport::create($validated);

        ActivityLogService::logCreated(
            $report,
            "Membuat scheduled report: {$report->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Scheduled report created successfully',
            'report' => [
                'id' => $report->id,
                'name' => $report->name,
                'report_type' => $report->report_type,
                'frequency' => $report->frequency,
                'day_of_week' => $report->day_of_week,
                'day_of_month' => $report->day_of_month,
                'send_time' => $report->send_time,
                'email_to' => $report->email_to,
                'format' => $report->format,
                'filters' => $report->filters,
                'is_active' => $report->is_active,
                'next_send_at' => $report->next_send_at?->format('Y-m-d H:i:s'),
                'created_at' => $report->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Update the specified scheduled report.
     */
    public function update(Request $request, ScheduledReport $scheduledReport)
    {
        $user = Auth::user();

        // Authorization check
        if ($scheduledReport->user_id !== $user->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'report_type' => ['sometimes', 'required', 'string', 'in:analytics,ai_performance,csat,conversation_quality'],
            'frequency' => ['sometimes', 'required', 'string', 'in:daily,weekly,monthly'],
            'day_of_week' => ['required_if:frequency,weekly', 'nullable', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'day_of_month' => ['required_if:frequency,monthly', 'nullable', 'integer', 'min:1', 'max:31'],
            'send_time' => ['sometimes', 'required', 'date_format:H:i'],
            'email_to' => ['sometimes', 'required', 'string', 'email'],
            'format' => ['sometimes', 'required', 'string', 'in:pdf,excel,csv'],
            'filters' => ['nullable', 'array'],
            'filters.platform' => ['nullable', 'string', 'in:all,whatsapp,instagram,web'],
            'filters.date_range' => ['nullable', 'integer', 'min:1', 'max:365'],
            'filters.metrics' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Recalculate next_send_at if frequency-related fields changed
        if (isset($validated['frequency']) || isset($validated['day_of_week']) || 
            isset($validated['day_of_month']) || isset($validated['send_time'])) {
            $validated['next_send_at'] = $this->calculateNextSendAt(array_merge(
                $scheduledReport->toArray(),
                $validated
            ));
        }

        $scheduledReport->update($validated);

        ActivityLogService::logUpdated(
            $scheduledReport,
            "Memperbarui scheduled report: {$scheduledReport->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Scheduled report updated successfully',
            'report' => [
                'id' => $scheduledReport->id,
                'name' => $scheduledReport->name,
                'report_type' => $scheduledReport->report_type,
                'frequency' => $scheduledReport->frequency,
                'day_of_week' => $scheduledReport->day_of_week,
                'day_of_month' => $scheduledReport->day_of_month,
                'send_time' => $scheduledReport->send_time,
                'email_to' => $scheduledReport->email_to,
                'format' => $scheduledReport->format,
                'filters' => $scheduledReport->filters,
                'is_active' => $scheduledReport->is_active,
                'next_send_at' => $scheduledReport->next_send_at?->format('Y-m-d H:i:s'),
                'updated_at' => $scheduledReport->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Remove the specified scheduled report.
     */
    public function destroy(ScheduledReport $scheduledReport)
    {
        $user = Auth::user();

        // Authorization check
        if ($scheduledReport->user_id !== $user->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $name = $scheduledReport->name;
        $id = $scheduledReport->id;

        $scheduledReport->delete();

        ActivityLogService::logDeleted(
            $scheduledReport,
            "Menghapus scheduled report: {$name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Scheduled report deleted successfully',
            'id' => $id,
        ]);
    }

    /**
     * Toggle the active status of a scheduled report.
     */
    public function toggleStatus(ScheduledReport $scheduledReport)
    {
        $user = Auth::user();

        // Authorization check
        if ($scheduledReport->user_id !== $user->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $scheduledReport->is_active = !$scheduledReport->is_active;

        // Recalculate next_send_at when enabling
        if ($scheduledReport->is_active) {
            $scheduledReport->next_send_at = $this->calculateNextSendAt($scheduledReport->toArray());
        }

        $scheduledReport->save();

        $status = $scheduledReport->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLogService::logUpdated(
            $scheduledReport,
            "Scheduled report {$status}: {$scheduledReport->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => "Scheduled report {$status}",
            'report' => [
                'id' => $scheduledReport->id,
                'is_active' => $scheduledReport->is_active,
                'next_send_at' => $scheduledReport->next_send_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Calculate the next send at datetime based on frequency.
     */
    protected function calculateNextSendAt(array $data): ?Carbon
    {
        $frequency = $data['frequency'] ?? 'weekly';
        $sendTime = $data['send_time'] ?? '09:00';
        $now = Carbon::now();

        // Parse send time
        [$hour, $minute] = explode(':', $sendTime);

        switch ($frequency) {
            case 'daily':
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                if ($next->lessThanOrEqualTo($now)) {
                    $next->addDay();
                }
                return $next;

            case 'weekly':
                $dayOfWeek = $data['day_of_week'] ?? 'monday';
                $daysMap = [
                    'sunday' => 0,
                    'monday' => 1,
                    'tuesday' => 2,
                    'wednesday' => 3,
                    'thursday' => 4,
                    'friday' => 5,
                    'saturday' => 6,
                ];
                $targetDay = $daysMap[strtolower($dayOfWeek)] ?? 1;
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                $currentDay = $next->dayOfWeek;

                if ($currentDay === $targetDay && $next->greaterThan($now)) {
                    return $next;
                }

                $daysUntil = ($targetDay - $currentDay + 7) % 7;
                if ($daysUntil === 0) {
                    $daysUntil = 7;
                }

                return $next->addDays($daysUntil);

            case 'monthly':
                $dayOfMonth = $data['day_of_month'] ?? 1;
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                $targetDay = min($dayOfMonth, $next->daysInMonth);

                if ($next->day === $targetDay && $next->greaterThan($now)) {
                    return $next;
                }

                if ($next->day < $targetDay) {
                    $next->setDay($targetDay);
                } else {
                    $next->addMonth()->setDay(min($dayOfMonth, $next->daysInMonth));
                }

                return $next;

            default:
                return null;
        }
    }

    /**
     * Manually trigger a report to be sent now.
     */
    public function sendNow(ScheduledReport $scheduledReport)
    {
        $user = Auth::user();

        // Authorization check
        if ($scheduledReport->user_id !== $user->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // TODO: Dispatch job to generate and send report
        // ReportGeneratorJob::dispatch($scheduledReport);

        $scheduledReport->update([
            'last_sent_at' => now(),
            'next_send_at' => $this->calculateNextSendAt($scheduledReport->toArray()),
        ]);

        ActivityLogService::logUpdated(
            $scheduledReport,
            "Manual trigger scheduled report: {$scheduledReport->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Report generation queued',
        ]);
    }
}
