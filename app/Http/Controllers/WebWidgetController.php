<?php

namespace App\Http\Controllers;

use App\Models\WebWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebWidgetController extends Controller
{
    /**
     * Display a listing of widgets.
     */
    public function index()
    {
        $widgets = WebWidget::withCount('conversations')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.web-widget.index', [
            'title' => 'Web Chat Widget',
            'widgets' => $widgets,
        ]);
    }

    /**
     * Show the form for creating a new widget.
     */
    public function create()
    {
        return view('pages.web-widget.create', [
            'title' => 'Buat Widget Baru',
        ]);
    }

    /**
     * Store a newly created widget.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'domain' => 'nullable|string|max:255',
            'welcome_message' => 'nullable|string|max:500',
            'bot_name' => 'nullable|string|max:50',
            'primary_color' => 'nullable|string|max:20',
            'position' => 'nullable|in:bottom-right,bottom-left',
        ]);

        $widget = WebWidget::create([
            'name' => $validated['name'],
            'domain' => $validated['domain'] ?? null,
            'welcome_message' => $validated['welcome_message'] ?? 'Halo! Ada yang bisa kami bantu?',
            'bot_name' => $validated['bot_name'] ?? 'Bot ReplyAI',
            'primary_color' => $validated['primary_color'] ?? '#4F46E5',
            'position' => $validated['position'] ?? 'bottom-right',
            'is_active' => true,
        ]);

        return redirect()->route('web-widgets.index')
            ->with('success', 'Widget berhasil dibuat! API Key: ' . $widget->api_key);
    }

    /**
     * Show the form for editing the specified widget.
     */
    public function edit(WebWidget $webWidget)
    {
        return view('pages.web-widget.edit', [
            'title' => 'Edit Widget',
            'widget' => $webWidget,
        ]);
    }

    /**
     * Update the specified widget.
     */
    public function update(Request $request, WebWidget $webWidget)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'domain' => 'nullable|string|max:255',
            'welcome_message' => 'nullable|string|max:500',
            'bot_name' => 'nullable|string|max:50',
            'primary_color' => 'nullable|string|max:20',
            'position' => 'nullable|in:bottom-right,bottom-left',
            'is_active' => 'boolean',
        ]);

        $webWidget->update([
            'name' => $validated['name'],
            'domain' => $validated['domain'] ?? null,
            'welcome_message' => $validated['welcome_message'] ?? $webWidget->welcome_message,
            'bot_name' => $validated['bot_name'] ?? $webWidget->bot_name,
            'primary_color' => $validated['primary_color'] ?? $webWidget->primary_color,
            'position' => $validated['position'] ?? $webWidget->position,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('web-widgets.index')
            ->with('success', 'Widget berhasil diupdate!');
    }

    /**
     * Remove the specified widget.
     */
    public function destroy(WebWidget $webWidget)
    {
        $webWidget->delete();

        return redirect()->route('web-widgets.index')
            ->with('success', 'Widget berhasil dihapus!');
    }

    /**
     * Toggle widget active status (AJAX).
     */
    public function toggle(WebWidget $webWidget)
    {
        $webWidget->update([
            'is_active' => !$webWidget->is_active,
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $webWidget->is_active,
        ]);
    }

    /**
     * Regenerate API key (AJAX).
     */
    public function regenerateKey(WebWidget $webWidget)
    {
        $webWidget->update([
            'api_key' => 'rw_' . Str::random(32),
        ]);

        return response()->json([
            'success' => true,
            'api_key' => $webWidget->api_key,
            'embed_code' => $webWidget->embed_code,
        ]);
    }

    /**
     * Get embed code for widget.
     */
    public function getEmbedCode(WebWidget $webWidget)
    {
        return response()->json([
            'success' => true,
            'embed_code' => $webWidget->embed_code,
            'api_key' => $webWidget->api_key,
        ]);
    }
}
