<?php

namespace App\Http\Controllers;

use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaConversation;
use App\Models\WhatsAppDevice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class WhatsAppInboxController extends Controller
{
    /**
     * Display the inbox page
     */
    public function index(): View
    {
        $session = WaSession::getDefault();
        $devices = WhatsAppDevice::where('status', 'connected')
            ->orderBy('device_name')
            ->get(['id', 'session_id', 'device_name', 'phone_number']);
        
        return view('pages.whatsapp.inbox', [
            'title' => 'WhatsApp Inbox',
            'takeoverTimeout' => $session->takeover_timeout_minutes ?? 60,
            'idleWarning' => $session->idle_warning_minutes ?? 30,
            'devices' => $devices,
        ]);
    }

    /**
     * Get list of conversations
     */
    public function getConversations(Request $request): JsonResponse
    {
        $deviceFilter = $request->query('device');
        
        // Subquery to get the latest valid push_name for each phone number
        $latestNameQuery = WaMessage::select('push_name')
            ->whereColumn('phone_number', 'wa_messages.phone_number')
            ->whereNotNull('push_name')
            ->where('push_name', '!=', '')
            ->where('push_name', '!=', 'Unknown') // Exclude literal "Unknown"
            ->where('push_name', '!=', 'unknown')
            ->orderBy('id', 'desc')
            ->limit(1);

        // Build base query - simplified filters
        $query = WaMessage::select('phone_number', 'push_name', 'remote_jid', 'created_at', 'message', 'status', 'session_id')
            ->addSelect(['display_name' => $latestNameQuery]) // Add dynamic column
            ->where('remote_jid', 'not like', '%@g.us')
            ->where('remote_jid', 'not like', '%@newsletter')
            ->where('remote_jid', 'not like', '%@broadcast')
            // Only check that phone_number is numeric and reasonable length
            ->whereRaw('LENGTH(phone_number) >= 10')
            ->whereRaw('LENGTH(phone_number) <= 15')
            ->whereRaw("phone_number REGEXP '^[0-9]+$'"); // Simple: only digits
        
        // Apply device filter if specified
        if ($deviceFilter) {
            $query->where('session_id', $deviceFilter);
        }

        // Get unique phone numbers from messages, ordered by latest message
        $conversations = $query->whereIn('id', function($subQuery) use ($deviceFilter) {
                $subQuery->selectRaw('MAX(id)')
                    ->from('wa_messages')
                    ->where('remote_jid', 'not like', '%@g.us')
                    ->where('remote_jid', 'not like', '%@newsletter')
                    ->where('remote_jid', 'not like', '%@broadcast')
                    ->whereRaw('LENGTH(phone_number) >= 10')
                    ->whereRaw('LENGTH(phone_number) <= 15')
                    ->whereRaw("phone_number REGEXP '^[0-9]+$'");
                
                if ($deviceFilter) {
                    $subQuery->where('session_id', $deviceFilter);
                }
                
                $subQuery->groupBy('phone_number');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($msg) {
                // Format phone number
                $formattedPhone = '+' . $msg->phone_number;
                
                // Prioritize the subquery result (display_name), then the row's push_name
                $displayName = $msg->display_name ?? $msg->push_name;
                
                // Allow "Unknown" ONLY if we fail to find anything else, but prefer formatted phone over "Unknown"
                if (empty($displayName) || 
                    $displayName == $msg->phone_number || 
                    str_contains($displayName, 'whatsapp.net') ||
                    strtolower($displayName) === 'unknown') {
                    $displayName = $formattedPhone;
                }

                // Get conversation status
                $waConversation = WaConversation::where('phone_number', $msg->phone_number)->first();
                $status = $waConversation?->status ?? 'bot_active';
                $remainingMinutes = $waConversation?->remaining_minutes;
                $idleMinutes = $waConversation?->idle_minutes;

                // Get device info
                $device = WhatsAppDevice::where('session_id', $msg->session_id)->first();

                return [
                    'phone_number' => $msg->phone_number,
                    'formatted_phone' => $formattedPhone,
                    'name' => $displayName,
                    'original_name' => $msg->push_name,
                    'last_message' => $msg->message,
                    'last_message_time' => $msg->created_at->diffForHumans(),
                    'timestamp' => $msg->created_at->timestamp,
                    'unread' => 0,
                    'avatar' => null,
                    // Takeover status fields
                    'status' => $status,
                    'assigned_cs' => $waConversation?->assigned_cs,
                    'remaining_minutes' => $remainingMinutes,
                    'idle_minutes' => $idleMinutes,
                    // Device info
                    'session_id' => $msg->session_id,
                    'device_name' => $device?->device_name ?? 'Unknown Device',
                    'device_color' => $device?->color ?? '#888888',
                ];
            });

        return response()->json($conversations);
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages(string $phone): JsonResponse
    {
        $messages = WaMessage::where('phone_number', $phone)
            ->orderBy('created_at', 'asc') // Oldest first for chat view
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'direction' => $msg->direction,
                    'message' => $msg->message,
                    'type' => $msg->message_type,
                    'status' => $msg->status,
                    'time' => $msg->created_at->format('H:i'),
                    'full_time' => $msg->created_at->format('d M Y H:i'),
                    'is_bot_reply' => !empty($msg->bot_reply),
                    'bot_reply' => $msg->bot_reply,
                    'session_id' => $msg->session_id,
                ];
            });

        return response()->json($messages);
    }
}

