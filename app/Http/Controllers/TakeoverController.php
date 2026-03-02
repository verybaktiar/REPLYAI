<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WaSession;
use App\Models\TakeoverLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class TakeoverController
 * 
 * Controller untuk mengelola takeover conversation.
 * Security: Semua method melakukan authorization check untuk memastikan
 * user hanya bisa mengakses conversation miliknya sendiri.
 */
class TakeoverController extends Controller
{
    /**
     * =========================================================================
     * WHATSAPP TAKEOVER
     * =========================================================================
     */

    /**
     * Take over a WhatsApp conversation
     * 
     * @param string $phone Nomor telepon conversation
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function takeoverWa(string $phone): JsonResponse
    {
        try {
            // SECURITY: Authorization check - hanya bisa akses conversation milik sendiri
            $conversation = WaConversation::where('phone_number', $phone)
                ->where('user_id', auth()->id())
                ->firstOrFail();
            
            // Check apakah sudah di-takeover
            if (!$conversation->isBotActive()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversation already taken over',
                ], 400);
            }

            $conversation->takeover(auth()->user()->name ?? 'Admin');

            // Log the action
            TakeoverLog::logTakeover(
                TakeoverLog::PLATFORM_WHATSAPP,
                $phone,
                $conversation->display_name,
                auth()->user()->name ?? 'Admin',
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Conversation taken over successfully',
                'status' => $conversation->status,
            ]);

        } catch (ModelNotFoundException $e) {
            // SECURITY: Return 404 (not 403) untuk menghindari information disclosure
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }
    }

    /**
     * Hand back a WhatsApp conversation to bot
     * 
     * @param string $phone Nomor telepon conversation
     * @return JsonResponse
     */
    public function handbackWa(string $phone): JsonResponse
    {
        try {
            // SECURITY: Authorization check
            $conversation = WaConversation::where('phone_number', $phone)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            if ($conversation->isBotActive()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Bot is already active',
                ], 400);
            }

            $idleMinutes = $conversation->idle_minutes;
            $customerName = $conversation->display_name;
            
            $conversation->handback();

            // Log the action
            TakeoverLog::logHandback(
                TakeoverLog::PLATFORM_WHATSAPP,
                $phone,
                $customerName,
                auth()->user()->name ?? 'Admin',
                $idleMinutes,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Conversation handed back to bot',
                'status' => 'bot_active',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }
    }

    /**
     * Get WhatsApp conversation status
     * 
     * @param string $phone Nomor telepon conversation
     * @return JsonResponse
     */
    public function getWaConversationStatus(string $phone): JsonResponse
    {
        try {
            // SECURITY: Authorization check
            $conversation = WaConversation::where('phone_number', $phone)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            return response()->json([
                'status' => $conversation->status,
                'assigned_cs' => $conversation->assigned_cs,
                'takeover_at' => $conversation->takeover_at?->toIso8601String(),
                'last_cs_reply_at' => $conversation->last_cs_reply_at?->toIso8601String(),
                'remaining_minutes' => $conversation->remaining_minutes,
                'idle_minutes' => $conversation->idle_minutes,
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'bot_active',
                'assigned_cs' => null,
                'remaining_minutes' => null,
            ]);
        }
    }

    /**
     * =========================================================================
     * INSTAGRAM TAKEOVER
     * =========================================================================
     */

    /**
     * Take over an Instagram conversation
     * 
     * @param int $id ID conversation
     * @return JsonResponse
     */
    public function takeoverIg(int $id): JsonResponse
    {
        try {
            // SECURITY: Authorization check
            $conversation = Conversation::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            if ($conversation->status === 'agent_handling') {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversation already taken over',
                ], 400);
            }

            $conversation->update([
                'status' => 'agent_handling',
                'assigned_cs' => auth()->user()->name ?? 'Admin',
                'takeover_at' => now(),
                'agent_replied_at' => now(),
            ]);

            // Log the action
            TakeoverLog::logTakeover(
                TakeoverLog::PLATFORM_INSTAGRAM,
                (string) $id,
                $conversation->display_name,
                auth()->user()->name ?? 'Admin',
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Conversation taken over successfully',
                'status' => 'agent_handling',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }
    }

    /**
     * Hand back an Instagram conversation to bot
     * 
     * @param int $id ID conversation
     * @return JsonResponse
     */
    public function handbackIg(int $id): JsonResponse
    {
        try {
            // SECURITY: Authorization check
            $conversation = Conversation::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $idleMinutes = $conversation->agent_replied_at 
                ? now()->diffInMinutes($conversation->agent_replied_at) 
                : null;
            $customerName = $conversation->display_name;

            $conversation->update([
                'status' => 'bot_handling',
                'assigned_cs' => null,
                'takeover_at' => null,
                'agent_replied_at' => null,
            ]);

            // Log the action
            TakeoverLog::logHandback(
                TakeoverLog::PLATFORM_INSTAGRAM,
                (string) $id,
                $customerName,
                auth()->user()->name ?? 'Admin',
                $idleMinutes,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Conversation handed back to bot',
                'status' => 'bot_handling',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }
    }

    /**
     * =========================================================================
     * SETTINGS & LOGS
     * =========================================================================
     */

    /**
     * Get takeover logs
     * SECURITY: Filter berdasarkan user_id untuk mencegah data leakage
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLogs(Request $request): JsonResponse
    {
        $query = TakeoverLog::where('user_id', auth()->id()) // SECURITY: Filter by user
            ->orderBy('created_at', 'desc');

        // Filter by platform
        if ($request->has('platform') && $request->platform !== 'all') {
            $query->where('platform', $request->platform);
        }

        // Filter by action
        if ($request->has('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->limit(100)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'platform' => $log->platform_label,
                'customer_name' => $log->customer_name ?? $log->conversation_id,
                'action' => $log->action_label,
                'actor' => $log->actor,
                'idle_duration' => $log->idle_duration_minutes 
                    ? $log->idle_duration_minutes . ' menit' 
                    : '-',
                'created_at' => $log->created_at->format('d/m/Y H:i'),
                'time_ago' => $log->created_at->diffForHumans(),
            ];
        });

        return response()->json($logs);
    }

    /**
     * Get takeover logs page
     * 
     * @return View
     */
    public function logsPage(): View
    {
        return view('pages.takeover-logs', [
            'title' => 'Takeover Activity Logs',
        ]);
    }

    /**
     * Update takeover settings
     * SECURITY: Pastikan user hanya bisa update settings miliknya sendiri
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'takeover_timeout_minutes' => 'nullable|integer|min:15|max:1440',
            'idle_warning_minutes' => 'nullable|integer|min:5|max:120',
            'session_idle_timeout_minutes' => 'nullable|integer|min:1|max:240',
            'session_followup_timeout_minutes' => 'nullable|integer|min:1|max:120',
        ]);

        // SECURITY: Ambil session milik user yang sedang login
        $session = WaSession::where('user_id', auth()->id())->first();
        
        if (!$session) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found',
            ], 404);
        }
        
        $updateData = [];
        if ($request->has('takeover_timeout_minutes')) {
            $updateData['takeover_timeout_minutes'] = $request->takeover_timeout_minutes;
        }
        if ($request->has('idle_warning_minutes')) {
            $updateData['idle_warning_minutes'] = $request->idle_warning_minutes;
        }
        if ($request->has('session_idle_timeout_minutes')) {
            $updateData['session_idle_timeout_minutes'] = $request->session_idle_timeout_minutes;
        }
        if ($request->has('session_followup_timeout_minutes')) {
            $updateData['session_followup_timeout_minutes'] = $request->session_followup_timeout_minutes;
        }
        
        $session->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Get current takeover settings
     * 
     * @return JsonResponse
     */
    public function getSettings(): JsonResponse
    {
        // SECURITY: Ambil session milik user yang sedang login
        $session = WaSession::where('user_id', auth()->id())->first();

        if (!$session) {
            return response()->json([
                'takeover_timeout_minutes' => 60,
                'idle_warning_minutes' => 30,
            ]);
        }

        return response()->json([
            'takeover_timeout_minutes' => $session->takeover_timeout_minutes ?? 60,
            'idle_warning_minutes' => $session->idle_warning_minutes ?? 30,
        ]);
    }
}
