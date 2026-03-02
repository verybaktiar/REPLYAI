<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\QuickReply;
use Illuminate\Support\Facades\Validator;

class QuickReplyController extends Controller
{
    /**
     * Display a listing of quick replies (full page view)
     */
    public function index()
    {
        $quickReplies = QuickReply::forUser(auth()->id())
            ->latest()
            ->get()
            ->groupBy('category');

        $categories = QuickReply::forUser(auth()->id())
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return view('pages.settings.quick_replies', compact('quickReplies', 'categories'));
    }

    /**
     * Fetch quick replies for AJAX/Component use
     */
    public function fetch(): JsonResponse
    {
        $quickReplies = QuickReply::forUser(auth()->id())
            ->active()
            ->select('id', 'shortcut', 'message', 'category', 'usage_count')
            ->orderBy('category')
            ->orderBy('shortcut')
            ->get()
            ->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => $quickReplies
        ]);
    }

    /**
     * Get all categories for the current user
     */
    public function categories(): JsonResponse
    {
        $categories = QuickReply::forUser(auth()->id())
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created quick reply
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shortcut' => 'required|string|max:50|unique:quick_replies,shortcut,NULL,id,user_id,' . auth()->id(),
            'message' => 'required|string|max:5000',
            'category' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ], [
            'shortcut.unique' => 'Shortcut ini sudah digunakan. Pilih shortcut lain.',
            'shortcut.required' => 'Shortcut wajib diisi.',
            'message.required' => 'Pesan wajib diisi.',
            'shortcut.max' => 'Shortcut maksimal 50 karakter.',
            'message.max' => 'Pesan maksimal 5000 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $quickReply = QuickReply::create([
            'user_id' => auth()->id(),
            'shortcut' => $request->shortcut,
            'message' => $request->message,
            'category' => $request->category ?: 'Umum',
            'is_active' => $request->input('is_active', true),
            'usage_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quick Reply berhasil ditambahkan',
            'data' => $quickReply
        ]);
    }

    /**
     * Update the specified quick reply
     */
    public function update(Request $request, QuickReply $quickReply): JsonResponse
    {
        // Ensure user can only update their own quick replies
        if ($quickReply->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'shortcut' => 'required|string|max:50|unique:quick_replies,shortcut,' . $quickReply->id . ',id,user_id,' . auth()->id(),
            'message' => 'required|string|max:5000',
            'category' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ], [
            'shortcut.unique' => 'Shortcut ini sudah digunakan. Pilih shortcut lain.',
            'shortcut.required' => 'Shortcut wajib diisi.',
            'message.required' => 'Pesan wajib diisi.',
            'shortcut.max' => 'Shortcut maksimal 50 karakter.',
            'message.max' => 'Pesan maksimal 5000 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $quickReply->update([
            'shortcut' => $request->shortcut,
            'message' => $request->message,
            'category' => $request->category ?: 'Umum',
            'is_active' => $request->input('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quick Reply berhasil diperbarui',
            'data' => $quickReply
        ]);
    }

    /**
     * Remove the specified quick reply
     */
    public function destroy(QuickReply $quickReply): JsonResponse
    {
        // Ensure user can only delete their own quick replies
        if ($quickReply->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $quickReply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quick Reply berhasil dihapus'
        ]);
    }

    /**
     * Increment usage count for a quick reply
     */
    public function trackUsage(QuickReply $quickReply): JsonResponse
    {
        if ($quickReply->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $quickReply->increment('usage_count');

        return response()->json([
            'success' => true,
            'data' => $quickReply
        ]);
    }

    /**
     * Search quick replies by keyword
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $quickReplies = QuickReply::forUser(auth()->id())
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('shortcut', 'like', "%{$query}%")
                  ->orWhere('message', 'like', "%{$query}%");
            })
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $quickReplies
        ]);
    }
}
