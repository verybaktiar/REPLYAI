<?php

namespace App\Http\Controllers;

use App\Services\AiSuggestionService;
use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WebConversation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AiReplyController extends Controller
{
    protected AiSuggestionService $aiService;

    public function __construct(AiSuggestionService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Generate AI reply suggestions based on conversation context
     *
     * POST /api/ai/suggest-replies
     */
    public function suggest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|integer',
            'conversation_type' => 'required|string|in:instagram,whatsapp,web',
            'message_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = $this->getConversation(
                $request->input('conversation_type'),
                $request->input('conversation_id')
            );

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ], 404);
            }

            // Verify user has access to this conversation
            if (!$this->userCanAccessConversation($conversation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            // Get last message for context
            $lastMessage = $conversation->messages()
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$lastMessage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No messages found in conversation',
                ], 404);
            }

            // Generate suggestions
            $suggestions = $this->aiService->generateReplySuggestions($conversation, $lastMessage);

            // Analyze sentiment of last message
            $sentiment = $this->aiService->analyzeSentiment($lastMessage->content ?? '');

            // Detect intent
            $intent = $this->aiService->detectIntent($lastMessage->content ?? '');

            Log::info('AI Reply Suggestions generated', [
                'conversation_id' => $conversation->id,
                'conversation_type' => $request->input('conversation_type'),
                'suggestions_count' => count($suggestions),
                'sentiment' => $sentiment['sentiment'],
                'intent' => $intent['intent'],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'suggestions' => $suggestions,
                    'sentiment' => $sentiment,
                    'intent' => $intent,
                    'message_id' => $lastMessage->id,
                ],
                'meta' => [
                    'generated_at' => now()->toIso8601String(),
                    'cache_expires_in' => 300, // 5 minutes
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('AI Reply Suggestions error', [
                'error' => $e->getMessage(),
                'conversation_id' => $request->input('conversation_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate suggestions',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Analyze sentiment of a message
     *
     * POST /api/ai/analyze-sentiment
     */
    public function analyzeSentiment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:5000',
            'conversation_id' => 'nullable|integer',
            'conversation_type' => 'nullable|string|in:instagram,whatsapp,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $text = $request->input('text');
            $sentiment = $this->aiService->analyzeSentiment($text);

            // Optionally update conversation sentiment
            if ($request->has('conversation_id') && $request->has('conversation_type')) {
                $this->updateConversationSentiment(
                    $request->input('conversation_type'),
                    $request->input('conversation_id'),
                    $sentiment
                );
            }

            return response()->json([
                'success' => true,
                'data' => $sentiment,
            ]);

        } catch (\Throwable $e) {
            Log::error('AI Sentiment Analysis error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze sentiment',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Summarize a conversation
     *
     * POST /api/ai/summarize
     */
    public function summarizeConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|integer',
            'conversation_type' => 'required|string|in:instagram,whatsapp,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = $this->getConversation(
                $request->input('conversation_type'),
                $request->input('conversation_id')
            );

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ], 404);
            }

            if (!$this->userCanAccessConversation($conversation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            // Get messages for summary
            $messages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($msg) {
                    return [
                        'id' => $msg->id,
                        'sender_type' => $msg->sender_type,
                        'content' => $msg->content,
                        'created_at' => $msg->created_at,
                    ];
                })
                ->toArray();

            if (empty($messages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No messages to summarize',
                ], 404);
            }

            $summary = $this->aiService->summarizeConversation($messages);

            return response()->json([
                'success' => true,
                'data' => $summary,
                'meta' => [
                    'message_count' => count($messages),
                    'generated_at' => now()->toIso8601String(),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('AI Summarize error', [
                'error' => $e->getMessage(),
                'conversation_id' => $request->input('conversation_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to summarize conversation',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Detect intent from a message
     *
     * POST /api/ai/detect-intent
     */
    public function detectIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:5000',
            'conversation_id' => 'nullable|integer',
            'conversation_type' => 'nullable|string|in:instagram,whatsapp,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $text = $request->input('text');
            $intent = $this->aiService->detectIntent($text);

            // Optionally update conversation with intent
            if ($request->has('conversation_id') && $request->has('conversation_type')) {
                $this->updateConversationIntent(
                    $request->input('conversation_type'),
                    $request->input('conversation_id'),
                    $intent
                );
            }

            // Get intent-based suggestions
            $suggestions = $this->getIntentBasedSuggestions($intent['intent']);

            return response()->json([
                'success' => true,
                'data' => [
                    'intent' => $intent,
                    'suggested_responses' => $suggestions,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('AI Intent Detection error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to detect intent',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get conversation by type and ID
     */
    protected function getConversation(string $type, int $id): ?object
    {
        return match ($type) {
            'instagram' => Conversation::find($id),
            'whatsapp' => WaConversation::find($id),
            'web' => WebConversation::find($id),
            default => null,
        };
    }

    /**
     * Check if user can access conversation
     */
    protected function userCanAccessConversation($conversation): bool
    {
        $userId = auth()->id();

        if (!$userId) {
            return false;
        }

        // Check if conversation belongs to user
        if (isset($conversation->user_id)) {
            return $conversation->user_id === $userId;
        }

        // For Instagram conversations, check via instagram_account
        if (method_exists($conversation, 'instagramAccount')) {
            return $conversation->instagramAccount?->user_id === $userId;
        }

        return false;
    }

    /**
     * Update conversation sentiment
     */
    protected function updateConversationSentiment(string $type, int $id, array $sentiment): void
    {
        try {
            $conversation = $this->getConversation($type, $id);
            if ($conversation) {
                $conversation->update([
                    'ai_sentiment' => $sentiment['sentiment'],
                    'ai_sentiment_score' => $sentiment['score'],
                    'ai_analyzed_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to update conversation sentiment', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update conversation intent
     */
    protected function updateConversationIntent(string $type, int $id, array $intent): void
    {
        try {
            $conversation = $this->getConversation($type, $id);
            if ($conversation) {
                $conversation->update([
                    'ai_intent' => $intent['intent'],
                    'ai_intent_confidence' => $intent['confidence'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to update conversation intent', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get suggested responses based on intent
     */
    protected function getIntentBasedSuggestions(string $intent): array
    {
        $suggestions = [
            'complaint' => [
                'Mohon maaf atas ketidaknyamanannya. Bisa dijelaskan detail masalahnya?',
                'Kami sangat menyesal mendengar hal ini. Segera kami bantu perbaiki.',
            ],
            'inquiry' => [
                'Baik, saya akan bantu jelaskan. Untuk informasi lengkapnya...',
                'Tentu, silakan ditanyakan. Ada yang bisa saya bantu?',
            ],
            'purchase' => [
                'Terima kasih minatnya! Untuk pemesanan bisa melalui...',
                'Baik kak, saya bantu proses ordernya ya.',
            ],
            'support' => [
                'Saya siap membantu. Bisa dijelaskan masalahnya?',
                'Tentu, kami akan bantu step by step.',
            ],
            'feedback' => [
                'Terima kasih atas feedbacknya! Sangat berarti untuk kami.',
                'Kami menghargai masukannya. Terima kasih!',
            ],
            'greeting' => [
                'Halo! Selamat datang. Ada yang bisa dibantu?',
                'Hai! Senang bisa membantu Anda hari ini.',
            ],
            'urgent' => [
                'Baik, saya prioritaskan penanganannya.',
                'Segera saya bantu. Mohon tunggu sebentar.',
            ],
            'cancellation' => [
                'Baik, saya bantu proses pembatalannya.',
                'Mohon maaf, saya akan bantu cek status ordernya.',
            ],
            'general' => [
                'Baik kak, terima kasih infonya.',
                'Oke, saya catat ya kak.',
            ],
        ];

        return $suggestions[$intent] ?? $suggestions['general'];
    }
}
