<?php

namespace App\Http\Controllers;

use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaConversation;
use App\Models\WaConversationNote;
use App\Models\Tag;
use App\Models\WhatsAppDevice;
use App\Models\AiTrainingExample;
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
                
                // MANUALLY apply user_id filter in subquery because it's a raw table query
                if (auth()->guard('web')->check()) {
                    $subQuery->where('user_id', auth()->guard('web')->id());
                }
                
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
                    'stop_autofollowup' => (bool)($waConversation?->stop_autofollowup ?? false),
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
                    'rated' => AiTrainingExample::where('message_id', $msg->id)->value('rating'),
                ];
            });

        return response()->json($messages);
    }

    /**
     * AI Pro: Get summary of the conversation
     */
    public function getSummary(string $phone): JsonResponse
    {
        $messages = WaMessage::where('phone_number', $phone)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->map(function($msg) {
                return [
                    'role' => $msg->direction === 'incoming' ? 'user' : 'assistant',
                    'content' => $msg->message
                ];
            })
            ->values()
            ->toArray();

        $aiService = app(\App\Services\AiAnswerService::class);
        $summary = $aiService->generateSummary($messages);

        return response()->json([
            'summary' => $summary ?: 'Gagal merangkum percakapan.'
        ]);
    }

    /**
     * AI Pro: Get smart quick reply suggestions
     */
    public function getSuggestions(string $phone): JsonResponse
    {
        $messages = WaMessage::where('phone_number', $phone)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->reverse()
            ->map(function($msg) {
                return [
                    'role' => $msg->direction === 'incoming' ? 'user' : 'assistant',
                    'content' => $msg->message
                ];
            })
            ->values()
            ->toArray();

        $aiService = app(\App\Services\AiAnswerService::class);
        $suggestions = $aiService->generateSuggestions($messages);

        return response()->json([
            'suggestions' => $suggestions
        ]);
    }
    /**
     * AI Pro: Rate a message for style training
     */
    public function rateMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message_id' => 'required|exists:wa_messages,id',
            'rating' => 'required|in:good,bad',
        ]);

        $msg = WaMessage::findOrFail($request->message_id);
        
        // SAFETY CHECK: Only train from chats with > 7 days history to prevent poisoning
        $firstMsg = WaMessage::where('phone_number', $msg->phone_number)->oldest()->first();
        if (!$firstMsg || $firstMsg->created_at->gt(now()->subDays(7))) {
            return response()->json([
                'success' => false, 
                'message' => 'Latihan AI hanya diizinkan untuk customer dengan riwayat > 7 hari (Anti-Poisoning).'
            ], 403);
        }

        // Find previous user message for context
        $previousUserMsg = WaMessage::where('phone_number', $msg->phone_number)
            ->where('id', '<', $msg->id)
            ->where('direction', 'incoming')
            ->orderBy('id', 'desc')
            ->first();

        $aiService = app(\App\Services\AiAnswerService::class);
        $userQuery = $previousUserMsg?->message ?? '(Unknown)';
        $assistantResponse = $msg->bot_reply ?: $msg->message;

        // Create or update training example with PII scrubbing
        AiTrainingExample::updateOrCreate(
            ['message_id' => $msg->id],
            [
                'user_id' => auth()->id(),
                'business_profile_id' => WhatsAppDevice::where('session_id', $msg->session_id)->first()?->business_profile_id,
                'user_query' => $aiService->scrubPII($userQuery),
                'assistant_response' => $aiService->scrubPII($assistantResponse),
                'rating' => $request->rating,
                'is_approved' => false, // Always requires admin approval
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Toggle auto-follow up for a specific conversation
     */
    public function toggleFollowup(string $phone): JsonResponse
    {
        $conv = WaConversation::where('phone_number', $phone)->firstOrFail();
        $conv->stop_autofollowup = !$conv->stop_autofollowup;
        $conv->save();

        return response()->json([
            'success' => true,
            'stop_autofollowup' => $conv->stop_autofollowup,
            'message' => $conv->stop_autofollowup ? 'Auto-Follow Up dinonaktifkan untuk chat ini.' : 'Auto-Follow Up diaktifkan kembali.'
        ]);
    }

    /**
     * CRM: Get notes for conversation
     */
    public function getNotes(string $phone): JsonResponse
    {
        $conversation = WaConversation::where('phone_number', $phone)->firstOrFail();
        
        $notes = $conversation->notes()
            ->with('author:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'content' => $note->content,
                    'author_name' => $note->author->name ?? 'System',
                    'created_at' => $note->created_at->diffForHumans(),
                    'timestamp' => $note->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json($notes);
    }

    /**
     * CRM: Store a new note
     */
    public function storeNote(Request $request, string $phone): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $conversation = WaConversation::firstOrCreate(
            ['phone_number' => $phone],
            ['user_id' => auth()->id()] // Ensure user_id is set if creating new
        );

        $note = $conversation->notes()->create([
            'content' => $request->content,
            'user_id' => auth()->id(),
            'is_internal' => true,
        ]);

        return response()->json([
            'success' => true,
            'note' => [
                'id' => $note->id,
                'content' => $note->content,
                'author_name' => auth()->user()->name,
                'created_at' => 'Just now',
                'timestamp' => now()->format('d M Y H:i'),
            ]
        ]);
    }

    /**
     * CRM: Get tags for conversation
     */
    public function getTags(string $phone): JsonResponse
    {
        $conversation = WaConversation::where('phone_number', $phone)->first();
        
        if (!$conversation) {
            return response()->json([]);
        }

        return response()->json($conversation->tags);
    }

    /**
     * CRM: Attach tag to conversation
     */
    public function attachTag(Request $request, string $phone): JsonResponse
    {
        $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $conversation = WaConversation::firstOrCreate(
            ['phone_number' => $phone],
            ['user_id' => auth()->id()]
        );

        $conversation->tags()->syncWithoutDetaching([$request->tag_id]);

        return response()->json(['success' => true]);
    }

    /**
     * CRM: Detach tag from conversation
     */
    public function detachTag(Request $request, string $phone): JsonResponse
    {
        $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $conversation = WaConversation::where('phone_number', $phone)->firstOrFail();
        $conversation->tags()->detach($request->tag_id);

        return response()->json(['success' => true]);
    }

    /**
     * CRM: Get all available tags for the user
     */
    public function getAvailableTags(): JsonResponse
    {
        $tags = Tag::orderBy('name')->get();
        return response()->json($tags);
    }
}

