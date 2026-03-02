<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\InstagramAccount;
use App\Models\WaMessage;
use App\Models\WaConversation;
use App\Models\WhatsAppDevice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Unified Inbox Controller
 * Merges WhatsApp and Instagram conversations into a single interface
 */
class UnifiedInboxController extends Controller
{
    /**
     * Display the unified inbox page
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Get connected Instagram accounts
        $instagramAccounts = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();
        
        // Get connected WhatsApp devices
        $whatsappDevices = WhatsAppDevice::where('user_id', $user->id)
            ->where('status', 'connected')
            ->orderBy('device_name')
            ->get(['id', 'session_id', 'device_name', 'phone_number', 'color']);
        
        return view('pages.chat.unified-inbox', [
            'title' => 'Unified Inbox',
            'hasInstagram' => $instagramAccounts->isNotEmpty(),
            'hasWhatsApp' => $whatsappDevices->isNotEmpty(),
            'instagramAccounts' => $instagramAccounts,
            'whatsappDevices' => $whatsappDevices,
        ]);
    }

    /**
     * Get combined conversations from both WhatsApp and Instagram
     */
    public function getConversations(Request $request): JsonResponse
    {
        $platform = $request->query('platform', 'all'); // all, whatsapp, instagram
        $search = $request->query('search', '');
        
        $conversations = collect();
        
        // Fetch Instagram conversations
        if (in_array($platform, ['all', 'instagram'])) {
            $igConversations = $this->getInstagramConversations($search);
            $conversations = $conversations->merge($igConversations);
        }
        
        // Fetch WhatsApp conversations
        if (in_array($platform, ['all', 'whatsapp'])) {
            $waConversations = $this->getWhatsAppConversations($search);
            $conversations = $conversations->merge($waConversations);
        }
        
        // Sort by last_message_at descending
        $sorted = $conversations->sortByDesc('last_message_at')->values();
        
        return response()->json($sorted);
    }

    /**
     * Get Instagram conversations
     */
    protected function getInstagramConversations(string $search = ''): array
    {
        $user = Auth::user();
        
        // Get active Instagram account IDs for this user
        $igAccountIds = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        
        if (empty($igAccountIds)) {
            return [];
        }
        
        $query = Conversation::whereIn('instagram_account_id', $igAccountIds)
            ->with('instagramAccount:id,username,profile_picture_url');
        
        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('ig_username', 'like', "%{$search}%")
                  ->orWhere('last_message', 'like', "%{$search}%");
            });
        }
        
        $conversations = $query->orderByDesc('last_activity_at')
            ->get()
            ->map(function ($conv) {
                // Calculate unread count (messages not replied by agent)
                $unreadCount = Message::where('conversation_id', $conv->id)
                    ->where('sender_type', 'contact')
                    ->where('created_at', '>', $conv->agent_replied_at ?? '1970-01-01')
                    ->count();
                
                return [
                    'id' => $conv->id,
                    'platform' => 'instagram',
                    'platform_icon' => 'instagram',
                    'platform_color' => '#E4405F',
                    'identifier' => $conv->instagram_user_id,
                    'name' => $conv->display_name ?? $conv->ig_username ?? 'Unknown',
                    'username' => $conv->ig_username,
                    'avatar' => $conv->avatar,
                    'last_message' => $conv->last_message ?? 'No messages yet',
                    'last_message_at' => $conv->last_activity_at,
                    'last_message_time' => $conv->last_activity_at 
                        ? $this->formatTimestamp($conv->last_activity_at) 
                        : '-',
                    'unread_count' => $unreadCount,
                    'status' => $conv->status ?? 'open',
                    'instagram_account' => [
                        'id' => $conv->instagramAccount?->id,
                        'username' => $conv->instagramAccount?->username,
                    ],
                ];
            })
            ->toArray();
        
        return $conversations;
    }

    /**
     * Get WhatsApp conversations
     */
    protected function getWhatsAppConversations(string $search = ''): array
    {
        $user = Auth::user();
        
        // Get device session IDs for this user
        $sessionIds = WhatsAppDevice::where('user_id', $user->id)
            ->pluck('session_id')
            ->toArray();
        
        if (empty($sessionIds)) {
            return [];
        }
        
        // Subquery to get the latest valid push_name for each phone number
        $latestNameQuery = WaMessage::from('wa_messages as wm_sub')
            ->select('wm_sub.push_name')
            ->whereColumn('wm_sub.phone_number', 'wa_messages.phone_number')
            ->whereNotNull('wm_sub.push_name')
            ->where('wm_sub.push_name', '!=', '')
            ->where('wm_sub.push_name', '!=', 'Unknown')
            ->where('wm_sub.push_name', '!=', 'unknown')
            ->orderBy('wm_sub.id', 'desc')
            ->limit(1);
        
        // Build base query
        $query = WaMessage::select('phone_number', 'push_name', 'remote_jid', 'created_at', 'message', 'status', 'session_id')
            ->addSelect(['display_name' => $latestNameQuery])
            ->where('remote_jid', 'not like', '%@g.us')
            ->where('remote_jid', 'not like', '%@newsletter')
            ->where('remote_jid', 'not like', '%@broadcast')
            ->whereRaw('LENGTH(phone_number) >= 10')
            ->whereRaw('LENGTH(phone_number) <= 15')
            ->whereRaw("phone_number REGEXP '^[0-9]+$'")
            ->whereIn('session_id', $sessionIds);
        
        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('push_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        $conversations = $query->whereIn('id', function($subQuery) use ($sessionIds, $search) {
                $subQuery->selectRaw('MAX(id)')
                    ->from('wa_messages')
                    ->where('remote_jid', 'not like', '%@g.us')
                    ->where('remote_jid', 'not like', '%@newsletter')
                    ->where('remote_jid', 'not like', '%@broadcast')
                    ->whereRaw('LENGTH(phone_number) >= 10')
                    ->whereRaw('LENGTH(phone_number) <= 15')
                    ->whereRaw("phone_number REGEXP '^[0-9]+$")
                    ->whereIn('session_id', $sessionIds);
                
                if ($search) {
                    $subQuery->where(function ($q) use ($search) {
                        $q->where('push_name', 'like', "%{$search}%")
                          ->orWhere('phone_number', 'like', "%{$search}%")
                          ->orWhere('message', 'like', "%{$search}%");
                    });
                }
                
                $subQuery->groupBy('phone_number');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($msg) {
                $formattedPhone = '+' . $msg->phone_number;
                $displayName = $msg->display_name ?? $msg->push_name;
                
                if (empty($displayName) || 
                    $displayName == $msg->phone_number || 
                    str_contains($displayName, 'whatsapp.net') ||
                    strtolower($displayName) === 'unknown') {
                    $displayName = $formattedPhone;
                }
                
                // Get conversation status
                $waConversation = WaConversation::where('phone_number', $msg->phone_number)->first();
                $device = WhatsAppDevice::where('session_id', $msg->session_id)->first();
                
                return [
                    'id' => $msg->phone_number,
                    'platform' => 'whatsapp',
                    'platform_icon' => 'whatsapp',
                    'platform_color' => '#25D366',
                    'identifier' => $msg->phone_number,
                    'name' => $displayName,
                    'phone_number' => $msg->phone_number,
                    'formatted_phone' => $formattedPhone,
                    'avatar' => null,
                    'last_message' => $msg->message,
                    'last_message_at' => $msg->created_at->timestamp,
                    'last_message_time' => $msg->created_at->diffForHumans(),
                    'unread_count' => 0, // WhatsApp doesn't track unread in current schema
                    'status' => $waConversation?->status ?? 'bot_active',
                    'session_id' => $msg->session_id,
                    'device' => [
                        'name' => $device?->device_name ?? 'Unknown',
                        'color' => $device?->color ?? '#888888',
                    ],
                ];
            })
            ->toArray();
        
        return $conversations;
    }

    /**
     * Search across both platforms
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        return $this->getConversations($request);
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages(Request $request, string $platform, string $identifier): JsonResponse
    {
        if ($platform === 'whatsapp') {
            return $this->getWhatsAppMessages($identifier);
        } elseif ($platform === 'instagram') {
            return $this->getInstagramMessages((int) $identifier);
        }
        
        return response()->json(['error' => 'Invalid platform'], 400);
    }

    /**
     * Get conversation identifier helper
     */
    public function getConversation(Request $request, string $platform, string $identifier): JsonResponse
    {
        return $this->getConversationDetails($request, $platform, $identifier);
    }

    /**
     * Get WhatsApp messages
     */
    protected function getWhatsAppMessages(string $phone): JsonResponse
    {
        $messages = WaMessage::where('phone_number', $phone)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'platform' => 'whatsapp',
                    'direction' => $msg->direction,
                    'is_from_me' => $msg->direction === 'outgoing',
                    'content' => $msg->message,
                    'message' => $msg->message,
                    'type' => $msg->message_type,
                    'status' => $msg->status,
                    'time' => $msg->created_at->format('H:i'),
                    'full_time' => $msg->created_at->format('d M Y H:i'),
                    'timestamp' => $msg->created_at->timestamp,
                    'is_bot_reply' => !empty($msg->bot_reply),
                    'bot_reply' => $msg->bot_reply,
                ];
            });
        
        return response()->json($messages);
    }

    /**
     * Get Instagram messages
     */
    protected function getInstagramMessages(int $conversationId): JsonResponse
    {
        $conversation = Conversation::find($conversationId);
        
        if (!$conversation) {
            return response()->json([]);
        }
        
        $messages = Message::where('conversation_id', $conversationId)
            ->orderBy('message_created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'platform' => 'instagram',
                    'direction' => $msg->sender_type === 'agent' ? 'outgoing' : 'incoming',
                    'is_from_me' => $msg->sender_type === 'agent',
                    'content' => $msg->content,
                    'message' => $msg->content,
                    'type' => 'text',
                    'status' => 'read',
                    'time' => $msg->message_created_at 
                        ? date('H:i', is_numeric($msg->message_created_at) ? $msg->message_created_at : strtotime($msg->message_created_at))
                        : '-',
                    'full_time' => $msg->message_created_at
                        ? date('d M Y H:i', is_numeric($msg->message_created_at) ? $msg->message_created_at : strtotime($msg->message_created_at))
                        : '-',
                    'timestamp' => is_numeric($msg->message_created_at) ? $msg->message_created_at : strtotime($msg->message_created_at),
                    'is_bot_reply' => $msg->is_replied_by_bot ?? false,
                    'sender_type' => $msg->sender_type,
                ];
            });
        
        return response()->json($messages);
    }

    /**
     * Send message to a conversation
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|in:whatsapp,instagram',
            'identifier' => 'required|string',
            'message' => 'required|string',
        ]);
        
        $platform = $request->input('platform');
        $identifier = $request->input('identifier');
        $message = $request->input('message');
        
        if ($platform === 'whatsapp') {
            return $this->sendWhatsAppMessage($identifier, $message, $request);
        } elseif ($platform === 'instagram') {
            return $this->sendInstagramMessage((int) $identifier, $message);
        }
        
        return response()->json(['success' => false, 'error' => 'Invalid platform'], 400);
    }

    /**
     * Send WhatsApp message
     */
    protected function sendWhatsAppMessage(string $phone, string $message, Request $request): JsonResponse
    {
        try {
            // Use existing WhatsAppController logic
            $waController = app(WhatsAppController::class);
            
            // Create a new request for the send method
            $sendRequest = new Request([
                'phone' => $phone,
                'message' => $message,
            ]);
            $sendRequest->setUserResolver($request->getUserResolver());
            
            $response = $waController->send($sendRequest);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getContent(), true);
                return response()->json($data);
            }
            
            return response()->json(['success' => false, 'error' => 'Failed to send message'], 500);
        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp message', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send Instagram message directly via Meta API
     */
    protected function sendInstagramMessage(int $conversationId, string $message): JsonResponse
    {
        try {
            $user = Auth::user();
            $conversation = Conversation::findOrFail($conversationId);
            
            // Get Instagram account
            $igAccount = InstagramAccount::where('id', $conversation->instagram_account_id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
            
            if (!$igAccount) {
                return response()->json(['success' => false, 'error' => 'Instagram account not found'], 404);
            }
            
            // Send via Meta Graph API
            $response = Http::acceptJson()->asJson()->post(
                "https://graph.instagram.com/v21.0/{$igAccount->instagram_user_id}/messages",
                [
                    'recipient' => ['id' => $conversation->instagram_user_id],
                    'message' => ['text' => $message],
                    'access_token' => $igAccount->access_token,
                ]
            );
            
            if ($response->successful()) {
                // Save message to database
                Message::create([
                    'conversation_id' => $conversationId,
                    'sender_type' => 'agent',
                    'content' => $message,
                    'source' => 'meta_direct',
                    'message_created_at' => now()->toDateTimeString(),
                    'sent_at' => now()->toDateTimeString(),
                ]);
                
                // Update conversation
                $conversation->update([
                    'last_message' => $message,
                    'last_activity_at' => now()->timestamp,
                    'status' => 'agent_handling',
                    'agent_replied_at' => now(),
                ]);
                
                return response()->json(['success' => true]);
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Failed to send message';
            
            return response()->json(['success' => false, 'error' => $errorMessage], 500);
        } catch (\Exception $e) {
            Log::error('Error sending Instagram message', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unread counts per platform
     */
    public function getUnreadCounts(): JsonResponse
    {
        $user = Auth::user();
        
        $counts = [
            'whatsapp' => 0,
            'instagram' => 0,
            'total' => 0,
        ];
        
        // Count Instagram unread
        $igAccountIds = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        
        if (!empty($igAccountIds)) {
            $igConversations = Conversation::whereIn('instagram_account_id', $igAccountIds)
                ->select('id', 'agent_replied_at')
                ->get();
            
            foreach ($igConversations as $conv) {
                $unread = Message::where('conversation_id', $conv->id)
                    ->where('sender_type', 'contact')
                    ->where('created_at', '>', $conv->agent_replied_at ?? '1970-01-01')
                    ->count();
                $counts['instagram'] += $unread;
            }
        }
        
        // WhatsApp unread count (using conversation status as proxy)
        $sessionIds = WhatsAppDevice::where('user_id', $user->id)
            ->pluck('session_id')
            ->toArray();
        
        if (!empty($sessionIds)) {
            // Count conversations with agent_handling or needs_attention status
            $counts['whatsapp'] = WaConversation::whereIn('status', ['agent_handling', 'needs_attention', 'idle'])
                ->whereHas('messages', function ($q) use ($sessionIds) {
                    $q->whereIn('session_id', $sessionIds);
                })
                ->count();
        }
        
        $counts['total'] = $counts['whatsapp'] + $counts['instagram'];
        
        return response()->json($counts);
    }

    /**
     * Get conversation details
     */
    public function getConversationDetails(Request $request, string $platform, string $identifier): JsonResponse
    {
        if ($platform === 'whatsapp') {
            $waConversation = WaConversation::where('phone_number', $identifier)->first();
            $device = WhatsAppDevice::whereHas('messages', function ($q) use ($identifier) {
                $q->where('phone_number', $identifier);
            })->first();
            
            return response()->json([
                'platform' => 'whatsapp',
                'identifier' => $identifier,
                'status' => $waConversation?->status ?? 'bot_active',
                'assigned_cs' => $waConversation?->assigned_cs,
                'stop_autofollowup' => $waConversation?->stop_autofollowup ?? false,
                'device' => $device ? [
                    'name' => $device->device_name,
                    'color' => $device->color,
                ] : null,
            ]);
        } elseif ($platform === 'instagram') {
            $conversation = Conversation::find($identifier);
            
            if (!$conversation) {
                return response()->json(['error' => 'Conversation not found'], 404);
            }
            
            return response()->json([
                'platform' => 'instagram',
                'identifier' => $identifier,
                'status' => $conversation->status,
                'instagram_user_id' => $conversation->instagram_user_id,
                'instagram_account' => [
                    'username' => $conversation->instagramAccount?->username,
                ],
            ]);
        }
        
        return response()->json(['error' => 'Invalid platform'], 400);
    }

    /**
     * Format timestamp to human readable
     */
    protected function formatTimestamp($timestamp): string
    {
        if (!$timestamp) {
            return '-';
        }
        
        $time = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . 'm ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . 'h ago';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . 'd ago';
        } else {
            return date('M j', $time);
        }
    }
}
