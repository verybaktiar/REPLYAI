<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebWidget;
use App\Models\WebConversation;
use App\Models\WebMessage;
use App\Services\AutoReplyEngine;
use App\Services\AiAnswerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WebChatController extends Controller
{
    protected AutoReplyEngine $engine;
    protected AiAnswerService $ai;

    public function __construct(AutoReplyEngine $engine, AiAnswerService $ai)
    {
        $this->engine = $engine;
        $this->ai = $ai;
    }

    /**
     * Get widget configuration by API key
     * GET /api/web/widget/{api_key}
     */
    public function getWidget(string $apiKey): JsonResponse
    {
        $widget = WebWidget::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$widget) {
            return response()->json([
                'success' => false,
                'error' => 'Widget not found or inactive'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'widget' => [
                'name' => $widget->name,
                'welcome_message' => $widget->welcome_message,
                'bot_name' => $widget->bot_name,
                'bot_avatar' => $widget->bot_avatar,
                'primary_color' => $widget->primary_color,
                'position' => $widget->position,
                'settings' => $widget->settings,
            ]
        ]);
    }

    /**
     * Send a message from the widget
     * POST /api/web/chat
     */
    public function sendMessage(Request $request): JsonResponse
    {

            Log::info('WebChat: Incoming message request', $request->all());

            $validated = $request->validate([
                'api_key' => 'required|string',
                'visitor_id' => 'required|string',
                'message' => 'required|string|max:2000',
                'visitor_name' => 'nullable|string|max:100',
                'visitor_email' => 'nullable|email|max:100',
                'page_url' => 'nullable|string|max:500',
            ]);

            // Find widget
            $widget = WebWidget::where('api_key', $validated['api_key'])
                ->where('is_active', true)
                ->first();

            if (!$widget) {
                return response()->json([
                    'success' => false,
                    'error' => 'Widget not found or inactive'
                ], 404);
            }

            // Find or create conversation
            $conversation = WebConversation::firstOrCreate(
                [
                    'widget_id' => $widget->id,
                    'visitor_id' => $validated['visitor_id'],
                ],
                [
                    'visitor_name' => $validated['visitor_name'] ?? null,
                    'visitor_email' => $validated['visitor_email'] ?? null,
                    'visitor_ip' => $request->ip(),
                    'visitor_user_agent' => $request->userAgent(),
                    'page_url' => $validated['page_url'] ?? null,
                    'status' => 'bot',
                    'user_id' => $widget->user_id,
                ]
            );

            // Update visitor info if provided
            if (!empty($validated['visitor_name']) && empty($conversation->visitor_name)) {
                $conversation->visitor_name = $validated['visitor_name'];
            }
            if (!empty($validated['visitor_email']) && empty($conversation->visitor_email)) {
                $conversation->visitor_email = $validated['visitor_email'];
            }
            $conversation->last_activity_at = now();
            $conversation->last_message = Str::limit($validated['message'], 100);
            $conversation->save();

            // Save visitor message
            $visitorMessage = WebMessage::create([
                'web_conversation_id' => $conversation->id,
                'sender_type' => 'visitor',
                'content' => $validated['message'],
                'user_id' => $widget->user_id,
            ]);

            // Generate AI response if handled by bot
            $botResponse = null;
            if ($conversation->status === 'bot') {
                try {
                    Log::info('WebChat: Generating AI response', ['msg' => $validated['message']]);
                    $botResponse = $this->generateAiResponse($validated['message'], $conversation, $widget);
                    Log::info('WebChat: AI response generated', ['response' => $botResponse]);
                    
                    // Save bot message
                    WebMessage::create([
                        'web_conversation_id' => $conversation->id,
                        'sender_type' => 'bot',
                        'content' => $botResponse,
                        'user_id' => $widget->user_id,
                    ]);

                    // Update last message
                    $conversation->last_message = Str::limit($botResponse, 100);
                    $conversation->save();
                } catch (\Exception $e) {
                    Log::error('WebChat: AI error: ' . $e->getMessage());
                    Log::error($e->getTraceAsString());
                    $botResponse = 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.';
                }
            }

            return response()->json([
                'success' => true,
                'message_id' => $visitorMessage->id,
                'bot_response' => $botResponse,
                'conversation_status' => $conversation->status,
            ]);
    }

    /**
     * Get conversation history
     * GET /api/web/conversation/{visitor_id}
     */
    public function getConversation(Request $request, string $visitorId): JsonResponse
    {
        $apiKey = $request->query('api_key');
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => 'API key required'
            ], 400);
        }

        $widget = WebWidget::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$widget) {
            return response()->json([
                'success' => false,
                'error' => 'Widget not found or inactive'
            ], 404);
        }

        $conversation = WebConversation::where('widget_id', $widget->id)
            ->where('visitor_id', $visitorId)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => true,
                'messages' => [],
                'conversation' => null,
            ]);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'visitor_name' => $conversation->visitor_name,
            ],
        ]);
    }

    /**
     * Update visitor information
     * POST /api/web/visitor
     */
    public function updateVisitor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_key' => 'required|string',
            'visitor_id' => 'required|string',
            'visitor_name' => 'nullable|string|max:100',
            'visitor_email' => 'nullable|email|max:100',
        ]);

        $widget = WebWidget::where('api_key', $validated['api_key'])
            ->where('is_active', true)
            ->first();

        if (!$widget) {
            return response()->json([
                'success' => false,
                'error' => 'Widget not found or inactive'
            ], 404);
        }

        $conversation = WebConversation::where('widget_id', $widget->id)
            ->where('visitor_id', $validated['visitor_id'])
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation not found'
            ], 404);
        }

        if (!empty($validated['visitor_name'])) {
            $conversation->visitor_name = $validated['visitor_name'];
        }
        if (!empty($validated['visitor_email'])) {
            $conversation->visitor_email = $validated['visitor_email'];
        }
        $conversation->save();

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'visitor_name' => $conversation->visitor_name,
                'visitor_email' => $conversation->visitor_email,
            ],
        ]);
    }

    /**
     * Generate AI response using existing AiAnswerService
     */
    protected function generateAiResponse(string $message, WebConversation $conversation, WebWidget $widget): string
    {
        // Get recent messages for context
        $recentMessages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'role' => $msg->sender_type === 'visitor' ? 'user' : 'assistant',
                    'content' => $msg->content,
                ];
            })
            ->values()
            ->toArray();

        // Use AI service to generate response
        try {
            $response = $this->ai->answerWithContext($message, $recentMessages);
            return $response ?? 'Maaf, saya tidak bisa menjawab pertanyaan tersebut saat ini.';
        } catch (\Exception $e) {
            Log::error('Web chat AI error: ' . $e->getMessage());
            return 'Maaf, terjadi kesalahan saat memproses pesan Anda. Silakan coba lagi.';
        }
    }

    /**
     * Poll for new messages (for real-time updates)
     * GET /api/web/poll
     */
    public function poll(Request $request): JsonResponse
    {
        $apiKey = $request->query('api_key');
        $visitorId = $request->query('visitor_id');
        $lastId = $request->query('last_message_id', 0);

        if (!$apiKey || !$visitorId) {
            return response()->json([
                'success' => false,
                'error' => 'API key and visitor ID required'
            ], 400);
        }

        $widget = WebWidget::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$widget) {
            return response()->json([
                'success' => false,
                'error' => 'Widget not found'
            ], 404);
        }

        $conversation = WebConversation::where('widget_id', $widget->id)
            ->where('visitor_id', $visitorId)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => true,
                'messages' => [],
            ]);
        }

        $newMessages = $conversation->messages()
            ->where('id', '>', $lastId)
            ->where('sender_type', '!=', 'visitor') // Only get bot/agent messages
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $newMessages,
            'conversation_status' => $conversation->status,
        ]);
    }
}
