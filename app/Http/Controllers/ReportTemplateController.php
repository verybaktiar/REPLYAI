<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportTemplateController extends Controller
{
    /**
     * Display a listing of report templates.
     */
    public function index()
    {
        $user = Auth::user();

        $templates = ReportTemplate::where('user_id', $user->id)
            ->orWhere('is_shared', true)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();
        
        $stats = [
            'total' => $templates->count(),
            'default' => $templates->where('is_default', true)->count(),
            'shared' => $templates->where('is_shared', true)->count(),
            'own' => $templates->where('user_id', $user->id)->count(),
        ];

        return view('pages.reports.templates.index', [
            'templates' => $templates,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created report template.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'metrics' => ['required', 'array', 'min:1'],
            'metrics.*' => ['string', 'in:total_conversations,total_messages,bot_resolution_rate,avg_response_time,first_response_time,escalation_rate,csrf_score,sentiment_positive,sentiment_negative,sentiment_neutral,peak_hours,agent_performance'],
            'filters' => ['required', 'array'],
            'filters.platform' => ['required', 'string', 'in:all,whatsapp,instagram,web'],
            'filters.date_range' => ['required', 'integer', 'min:1', 'max:365'],
            'chart_type' => ['required', 'string', 'in:line,bar,pie,heatmap,mixed'],
            'is_default' => ['boolean'],
            'is_shared' => ['boolean'],
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
        $validated['is_default'] = $validated['is_default'] ?? false;
        $validated['is_shared'] = $validated['is_shared'] ?? false;

        // If setting as default, unset other defaults
        if ($validated['is_default']) {
            ReportTemplate::where('user_id', $user->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $template = ReportTemplate::create($validated);

        ActivityLogService::logCreated(
            $template,
            "Membuat report template: {$template->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Report template created successfully',
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'metrics' => $template->metrics,
                'filters' => $template->filters,
                'chart_type' => $template->chart_type,
                'is_default' => $template->is_default,
                'is_shared' => $template->is_shared,
                'is_owner' => true,
                'created_at' => $template->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Update the specified report template.
     */
    public function update(Request $request, ReportTemplate $reportTemplate)
    {
        $user = Auth::user();

        // Authorization check
        if ($reportTemplate->user_id !== $user->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'metrics' => ['sometimes', 'required', 'array', 'min:1'],
            'metrics.*' => ['string', 'in:total_conversations,total_messages,bot_resolution_rate,avg_response_time,first_response_time,escalation_rate,csrf_score,sentiment_positive,sentiment_negative,sentiment_neutral,peak_hours,agent_performance'],
            'filters' => ['sometimes', 'required', 'array'],
            'filters.platform' => ['required_with:filters', 'string', 'in:all,whatsapp,instagram,web'],
            'filters.date_range' => ['required_with:filters', 'integer', 'min:1', 'max:365'],
            'chart_type' => ['sometimes', 'required', 'string', 'in:line,bar,pie,heatmap,mixed'],
            'is_default' => ['boolean'],
            'is_shared' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // If setting as default, unset other defaults first
        if (isset($validated['is_default']) && $validated['is_default']) {
            ReportTemplate::where('user_id', $user->id)
                ->where('is_default', true)
                ->where('id', '!=', $reportTemplate->id)
                ->update(['is_default' => false]);
        }

        $reportTemplate->update($validated);

        ActivityLogService::logUpdated(
            $reportTemplate,
            "Memperbarui report template: {$reportTemplate->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Report template updated successfully',
            'template' => [
                'id' => $reportTemplate->id,
                'name' => $reportTemplate->name,
                'description' => $reportTemplate->description,
                'metrics' => $reportTemplate->metrics,
                'filters' => $reportTemplate->filters,
                'chart_type' => $reportTemplate->chart_type,
                'is_default' => $reportTemplate->is_default,
                'is_shared' => $reportTemplate->is_shared,
                'is_owner' => true,
                'updated_at' => $reportTemplate->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Remove the specified report template.
     */
    public function destroy(ReportTemplate $reportTemplate)
    {
        $user = Auth::user();

        // Authorization check
        if ($reportTemplate->user_id !== $user->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Prevent deleting default template
        if ($reportTemplate->is_default) {
            return response()->json([
                'ok' => false,
                'message' => 'Cannot delete default template. Set another template as default first.',
            ], 422);
        }

        $name = $reportTemplate->name;
        $id = $reportTemplate->id;

        $reportTemplate->delete();

        ActivityLogService::logDeleted(
            $reportTemplate,
            "Menghapus report template: {$name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Report template deleted successfully',
            'id' => $id,
        ]);
    }

    /**
     * Set template as default.
     */
    public function setDefault(ReportTemplate $reportTemplate)
    {
        $user = Auth::user();

        // Authorization check
        if ($reportTemplate->user_id !== $user->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Unset current default
        ReportTemplate::where('user_id', $user->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        // Set new default
        $reportTemplate->update(['is_default' => true]);

        ActivityLogService::logUpdated(
            $reportTemplate,
            "Set default template: {$reportTemplate->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Default template set successfully',
            'template' => [
                'id' => $reportTemplate->id,
                'name' => $reportTemplate->name,
                'is_default' => true,
            ],
        ]);
    }

    /**
     * Duplicate an existing template.
     */
    public function duplicate(ReportTemplate $reportTemplate)
    {
        $user = Auth::user();

        // Allow duplicating shared templates or own templates
        if ($reportTemplate->user_id !== $user->id && !$reportTemplate->is_shared) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $newTemplate = ReportTemplate::create([
            'user_id' => $user->id,
            'name' => $reportTemplate->name . ' (Copy)',
            'description' => $reportTemplate->description,
            'metrics' => $reportTemplate->metrics,
            'filters' => $reportTemplate->filters,
            'chart_type' => $reportTemplate->chart_type,
            'is_default' => false,
            'is_shared' => false,
        ]);

        ActivityLogService::logCreated(
            $newTemplate,
            "Duplikat report template dari: {$reportTemplate->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Template duplicated successfully',
            'template' => [
                'id' => $newTemplate->id,
                'name' => $newTemplate->name,
                'description' => $newTemplate->description,
                'metrics' => $newTemplate->metrics,
                'filters' => $newTemplate->filters,
                'chart_type' => $newTemplate->chart_type,
                'is_default' => false,
                'is_shared' => false,
                'is_owner' => true,
                'created_at' => $newTemplate->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Get available metrics for templates.
     */
    public function getAvailableMetrics(): array
    {
        return [
            [
                'value' => 'total_conversations',
                'label' => 'Total Conversations',
                'category' => 'general',
                'icon' => 'chat',
            ],
            [
                'value' => 'total_messages',
                'label' => 'Total Messages',
                'category' => 'general',
                'icon' => 'message',
            ],
            [
                'value' => 'bot_resolution_rate',
                'label' => 'Bot Resolution Rate',
                'category' => 'performance',
                'icon' => 'smart_toy',
            ],
            [
                'value' => 'avg_response_time',
                'label' => 'Average Response Time',
                'category' => 'performance',
                'icon' => 'timer',
            ],
            [
                'value' => 'first_response_time',
                'label' => 'First Response Time',
                'category' => 'performance',
                'icon' => 'schedule',
            ],
            [
                'value' => 'escalation_rate',
                'label' => 'Escalation Rate',
                'category' => 'performance',
                'icon' => 'trending_up',
            ],
            [
                'value' => 'csrf_score',
                'label' => 'CSAT Score',
                'category' => 'quality',
                'icon' => 'star',
            ],
            [
                'value' => 'sentiment_positive',
                'label' => 'Positive Sentiment',
                'category' => 'sentiment',
                'icon' => 'sentiment_satisfied',
            ],
            [
                'value' => 'sentiment_negative',
                'label' => 'Negative Sentiment',
                'category' => 'sentiment',
                'icon' => 'sentiment_dissatisfied',
            ],
            [
                'value' => 'sentiment_neutral',
                'label' => 'Neutral Sentiment',
                'category' => 'sentiment',
                'icon' => 'sentiment_neutral',
            ],
            [
                'value' => 'peak_hours',
                'label' => 'Peak Hours',
                'category' => 'analytics',
                'icon' => 'access_time',
            ],
            [
                'value' => 'agent_performance',
                'label' => 'Agent Performance',
                'category' => 'analytics',
                'icon' => 'people',
            ],
        ];
    }

    /**
     * Get available chart types.
     */
    public function getChartTypes(): array
    {
        return [
            ['value' => 'line', 'label' => 'Line Chart', 'icon' => 'show_chart'],
            ['value' => 'bar', 'label' => 'Bar Chart', 'icon' => 'bar_chart'],
            ['value' => 'pie', 'label' => 'Pie Chart', 'icon' => 'pie_chart'],
            ['value' => 'heatmap', 'label' => 'Heatmap', 'icon' => 'grid_on'],
            ['value' => 'mixed', 'label' => 'Mixed Charts', 'icon' => 'multiline_chart'],
        ];
    }
}
