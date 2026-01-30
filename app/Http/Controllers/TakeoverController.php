<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WaSession;
use App\Models\TakeoverLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TakeoverController extends Controller
{
    // =========================================
    // WHATSAPP TAKEOVER
    // =========================================

    /**
     * Take over a WhatsApp conversation
     */
    public function takeoverWa(string $phone): JsonResponse
    {
        $conversation = WaConversation::getOrCreate($phone);
        
        if (!$conversation->isBotActive()) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation already taken over',
            ], 400);
        }

        $conversation->takeover('Admin'); // TODO: Get actual CS name from auth

        // Log the action
        TakeoverLog::logTakeover(
            TakeoverLog::PLATFORM_WHATSAPP,
            $phone,
            $conversation->display_name,
            'Admin',
            $conversation->user_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversation taken over successfully',
            'status' => $conversation->status,
        ]);
    }

    /**
     * Hand back a WhatsApp conversation to bot
     */
    public function handbackWa(string $phone): JsonResponse
    {
        $conversation = WaConversation::where('phone_number', $phone)->first();
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }

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
            'Admin',
            $idleMinutes,
            $conversation->user_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversation handed back to bot',
            'status' => 'bot_active',
        ]);
    }

    /**
     * Get WhatsApp conversation status
     */
    public function getWaConversationStatus(string $phone): JsonResponse
    {
        $conversation = WaConversation::where('phone_number', $phone)->first();
        
        if (!$conversation) {
            return response()->json([
                'status' => 'bot_active',
                'assigned_cs' => null,
                'remaining_minutes' => null,
            ]);
        }

        return response()->json([
            'status' => $conversation->status,
            'assigned_cs' => $conversation->assigned_cs,
            'takeover_at' => $conversation->takeover_at?->toIso8601String(),
            'last_cs_reply_at' => $conversation->last_cs_reply_at?->toIso8601String(),
            'remaining_minutes' => $conversation->remaining_minutes,
            'idle_minutes' => $conversation->idle_minutes,
        ]);
    }

    // =========================================
    // INSTAGRAM TAKEOVER
    // =========================================

    /**
     * Take over an Instagram conversation
     */
    public function takeoverIg(int $id): JsonResponse
    {
        $conversation = Conversation::find($id);
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }

        if ($conversation->status === 'agent_handling') {
            return response()->json([
                'success' => false,
                'error' => 'Conversation already taken over',
            ], 400);
        }

        $conversation->update([
            'status' => 'agent_handling',
            'assigned_cs' => 'Admin', // TODO: Get actual CS name
            'takeover_at' => now(),
            'agent_replied_at' => now(),
        ]);

        // Log the action
        TakeoverLog::logTakeover(
            TakeoverLog::PLATFORM_INSTAGRAM,
            (string) $id,
            $conversation->display_name,
            'Admin',
            $conversation->user_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversation taken over successfully',
            'status' => 'agent_handling',
        ]);
    }

    /**
     * Hand back an Instagram conversation to bot
     */
    public function handbackIg(int $id): JsonResponse
    {
        $conversation = Conversation::find($id);
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found',
            ], 404);
        }

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
            'Admin',
            $idleMinutes,
            $conversation->user_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversation handed back to bot',
            'status' => 'bot_handling',
        ]);
    }

    // =========================================
    // SETTINGS & LOGS
    // =========================================

    /**
     * Get takeover logs
     */
    public function getLogs(Request $request): JsonResponse
    {
        $query = TakeoverLog::orderBy('created_at', 'desc');

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
     */
    public function logsPage(): View
    {
        return view('pages.takeover-logs', [
            'title' => 'Takeover Activity Logs',
        ]);
    }

    /**
     * Update takeover settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate([
            'takeover_timeout_minutes' => 'nullable|integer|min:15|max:1440',
            'idle_warning_minutes' => 'nullable|integer|min:5|max:120',
            'session_idle_timeout_minutes' => 'nullable|integer|min:1|max:240',
            'session_followup_timeout_minutes' => 'nullable|integer|min:1|max:120',
        ]);

        $session = WaSession::getDefault();
        
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
     */
    public function getSettings(): JsonResponse
    {
        $session = WaSession::getDefault();

        return response()->json([
            'takeover_timeout_minutes' => $session->takeover_timeout_minutes ?? 60,
            'idle_warning_minutes' => $session->idle_warning_minutes ?? 30,
        ]);
    }
}
