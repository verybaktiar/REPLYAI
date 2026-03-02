<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MessageTemplateController extends Controller
{
    /**
     * Template categories
     */
    private array $categories = [
        'welcome' => 'Welcome Messages',
        'notification' => 'Notifications',
        'reminder' => 'Reminders',
        'support' => 'Support',
        'marketing' => 'Marketing',
        'system' => 'System',
        'other' => 'Other',
    ];

    /**
     * Available template variables
     */
    private array $availableVariables = [
        'user' => [
            '{{name}}' => 'User full name',
            '{{email}}' => 'User email',
            '{{phone}}' => 'User phone number',
        ],
        'order' => [
            '{{order_id}}' => 'Order/Invoice ID',
            '{{amount}}' => 'Payment amount',
            '{{plan_name}}' => 'Subscription plan name',
            '{{expiry_date}}' => 'Subscription expiry date',
        ],
        'system' => [
            '{{app_name}}' => 'Application name',
            '{{support_email}}' => 'Support email',
            '{{current_date}}' => 'Current date',
            '{{current_time}}' => 'Current time',
        ],
    ];

    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $query = MessageTemplate::query();

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name or content
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('category')->orderBy('name')->paginate(20);

        return view('admin.templates.index', [
            'templates' => $templates,
            'categories' => $this->categories,
            'availableVariables' => $this->availableVariables,
        ]);
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        return view('admin.templates.create', [
            'categories' => $this->categories,
            'availableVariables' => $this->availableVariables,
        ]);
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:message_templates,name',
            'content' => 'required|string|max:5000',
            'category' => ['required', Rule::in(array_keys($this->categories))],
            'variables' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Parse variables from content (extract {{variable}} patterns)
        $variables = $this->extractVariables($validated['content']);
        if (!empty($validated['variables'])) {
            $additionalVars = array_map('trim', explode(',', $validated['variables']));
            $variables = array_unique(array_merge($variables, $additionalVars));
        }

        $template = MessageTemplate::create([
            'name' => $validated['name'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'variables' => json_encode($variables),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'create_template',
            "Create message template: {$template->name}",
            ['template_id' => $template->id, 'name' => $template->name, 'category' => $template->category],
            $template
        );

        return redirect()->route('admin.templates.index')
            ->with('success', "Message template '{$template->name}' created successfully!");
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(MessageTemplate $template)
    {
        $template->variables = json_decode($template->variables, true) ?? [];
        
        return view('admin.templates.edit', [
            'template' => $template,
            'categories' => $this->categories,
            'availableVariables' => $this->availableVariables,
        ]);
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, MessageTemplate $template)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('message_templates')->ignore($template->id)],
            'content' => 'required|string|max:5000',
            'category' => ['required', Rule::in(array_keys($this->categories))],
            'variables' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $oldData = $template->toArray();

        // Parse variables from content
        $variables = $this->extractVariables($validated['content']);
        if (!empty($validated['variables'])) {
            $additionalVars = array_map('trim', explode(',', $validated['variables']));
            $variables = array_unique(array_merge($variables, $additionalVars));
        }

        $template->update([
            'name' => $validated['name'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'variables' => json_encode($variables),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'update_template',
            "Update message template: {$template->name}",
            [
                'template_id' => $template->id,
                'old_data' => $oldData,
                'new_data' => $template->fresh()->toArray(),
            ],
            $template
        );

        return redirect()->route('admin.templates.index')
            ->with('success', "Message template '{$template->name}' updated successfully!");
    }

    /**
     * Remove the specified template
     */
    public function destroy(MessageTemplate $template)
    {
        $name = $template->name;

        // Log activity before deletion
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'delete_template',
            "Delete message template: {$name}",
            ['template_id' => $template->id, 'name' => $name]
        );

        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', "Message template '{$name}' deleted successfully!");
    }

    /**
     * Extract variables from template content
     */
    private function extractVariables(string $content): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Preview template with sample data
     */
    public function preview(Request $request, MessageTemplate $template)
    {
        $sampleData = $request->input('sample_data', []);
        
        $content = $template->content;
        
        // Replace variables with sample data
        foreach ($sampleData as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }
        
        // Replace any remaining variables with placeholder
        $content = preg_replace('/\{\{([^}]+)\}\}/', '[{$1}]', $content);

        return response()->json([
            'success' => true,
            'preview' => $content,
        ]);
    }

    /**
     * Get templates by category (API)
     */
    public function getByCategory(string $category)
    {
        $templates = MessageTemplate::where('category', $category)
            ->where('is_active', true)
            ->get(['id', 'name', 'content', 'variables']);

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }
}
