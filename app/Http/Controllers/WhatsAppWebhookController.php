<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Services\AutoReplyEngine;
use App\Models\WaMessage;
use App\Models\WaConversation;
use App\Models\WhatsAppDevice;
use App\Events\NewWhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected WhatsAppService $waService,
        protected AutoReplyEngine $autoReply
    ) {}

    /**
     * Handle incoming message from WhatsApp
     */
    public function handleMessage(Request $request): JsonResponse
    {
        // Verify webhook key
        if (!$this->verifyWebhookKey($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->input('data');
        
        if (!$data) {
            return response()->json(['error' => 'No data provided'], 400);
        }

        Log::info('WhatsApp Incoming Message', $data);

        // Save message to database
        $message = $this->waService->handleIncomingMessage($data);
        
        // Broadcast event for real-time updates
        broadcast(new NewWhatsAppMessage($message));
        
        // Get Session ID from webhook data
        $sessionId = $data['sessionId'] ?? null;

        if (!$sessionId) {
            // Fallback: Find first connected device if session ID missing
            $device = WhatsAppDevice::where('status', 'connected')->first();
            $sessionId = $device->session_id ?? 'default';
        }

        // Get or create conversation and track user reply time
        $waConversation = WaConversation::firstOrCreate(
            ['phone_number' => $message->phone_number, 'user_id' => $message->user_id],
            ['display_name' => $message->push_name, 'session_status' => WaConversation::SESSION_ACTIVE]
        );
        
        // Update display name if it changed (and is valid) to keep Inbox fresh
        if (!empty($message->push_name) && 
            $message->push_name !== 'Unknown' && 
            $message->push_name !== $message->phone_number &&
            $waConversation->display_name !== $message->push_name) {
            $waConversation->update(['display_name' => $message->push_name]);
        }
        
        // Update last user reply time AND last message preview
        $waConversation->update([
            'last_user_reply_at' => now(),
            'last_message' => $message->message, // Update preview with incoming message
            'last_message_at' => now(),
        ]);

        // Check if user is responding to session follow-up
        $lowerMessage = strtolower(trim($message->message));
        if ($waConversation->session_status === WaConversation::SESSION_FOLLOWUP_SENT) {
            if (in_array($lowerMessage, ['tidak', 'no', 'sudah', 'selesai', 'cukup', 'ok', 'oke', 'thanks', 'terima kasih'])) {
                // User says they're done - close session with nice message
                $closeMessage = "Baik kak, terima kasih sudah menghubungi RS PKU Muhammadiyah Surakarta 🙏\n\n" .
                               "Semoga informasinya bermanfaat! Jangan ragu untuk chat lagi jika ada pertanyaan 💚";
                $this->waService->sendMessage($sessionId, $message->remote_jid, $closeMessage);
                $waConversation->update(['session_status' => WaConversation::SESSION_CLOSED]);
                Log::info('WA Session Closed by User', ['phone' => $message->phone_number]);
                return response()->json(['success' => true]);
            } else {
                // User has more questions - reactivate session
                $waConversation->update(['session_status' => WaConversation::SESSION_ACTIVE, 'followup_sent_at' => null]);
            }
        }

        // Reactivate closed sessions if user chats again
        if ($waConversation->session_status === WaConversation::SESSION_CLOSED) {
            $waConversation->update(['session_status' => WaConversation::SESSION_ACTIVE, 'followup_sent_at' => null]);
        }

        // Check if conversation is being handled by CS (agent)
        $isAgentHandling = $waConversation && !$waConversation->isBotActive();

        // Check if auto-reply is enabled AND not being handled by CS
        if ($this->waService->isAutoReplyEnabled($sessionId) && !$isAgentHandling) {
            // Rate Limiting: Prevent spam/loops (Max 15 messages per minute per user)
            $rateLimitKey = "wa_autoreply:{$sessionId}:{$message->phone_number}";

            if (RateLimiter::tooManyAttempts($rateLimitKey, 15)) {
                Log::warning('WhatsApp Auto-Reply Rate Limit Exceeded', [
                    'phone' => $message->phone_number,
                    'session' => $sessionId
                ]);
                // Stop processing to save AI costs and prevent loops
            } else {
                RateLimiter::hit($rateLimitKey, 60); // Decay in 60 seconds
                $this->processAutoReply($message, $sessionId);
            }
        } elseif ($isAgentHandling) {
            Log::info('WhatsApp Auto-Reply Skipped - Agent Handling', [
                'phone' => $message->phone_number,
                'assigned_cs' => $waConversation->assigned_cs,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle status updates from WhatsApp service
     */
    public function handleStatus(Request $request): JsonResponse
    {
        // Verify webhook key
        if (!$this->verifyWebhookKey($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->input('data');
        
        if (!$data) {
            return response()->json(['error' => 'No data provided'], 400);
        }

        Log::info('WhatsApp Status Update', $data);

        $this->waService->handleStatusUpdate($data);

        return response()->json(['success' => true]);
    }

    /**
     * Handle QR code webhook
     */
    public function handleQr(Request $request): JsonResponse
    {
        // Verify webhook key
        if (!$this->verifyWebhookKey($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->input('data');
        if ($data && isset($data['sessionId'])) {
            Log::info('WhatsApp QR Generated', ['session' => $data['sessionId']]);
            
            // Update device status to scanning
            WhatsAppDevice::where('session_id', $data['sessionId'])->update([
                'status' => 'scanning'
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Process auto-reply for incoming message using WhatsApp-specific AI
     */
    protected function processAutoReply(WaMessage $message, string $sessionId): void
    {
        try {
            // 1. DEDUPLICATION CHECK: If message already has a bot reply, skip!
            if (!empty($message->bot_reply)) {
                Log::info('WhatsApp Auto-Reply Skipped - Already Replied', ['message_id' => $message->id]);
                return;
            }

            // Fetch device to get its assigned business profile
            $device = WhatsAppDevice::with('businessProfile')
                ->where('session_id', $sessionId)
                ->first();
            
            Log::info('Debug AutoReply Context', [
                'session_id' => $sessionId,
                'device_id' => $device?->id,
                'device_user_id' => $device?->user_id,
                'message_from' => $message->phone_number
            ]);

            $businessProfile = $device?->businessProfile;

            // Fallback: If device has no profile assigned, try to find one for the device's owner
            if (!$businessProfile && $device && $device->user_id) {
                 $businessProfile = \App\Models\BusinessProfile::withoutGlobalScopes()
                    ->where('user_id', $device->user_id)
                    ->where('is_active', true)
                    ->first();
                 
                 if ($businessProfile) {
                     Log::info('Found BusinessProfile via UserID fallback', ['user_id' => $device->user_id, 'profile_id' => $businessProfile->id]);
                 }
            }
            
            // Ambil history percakapan dari database (6 pesan terakhir)
            $recentMessages = WaMessage::where('remote_jid', $message->remote_jid)
                ->where('id', '!=', $message->id) // Exclude pesan saat ini
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get()
                ->reverse() // Urutkan dari lama ke baru
                ->values();
            
            // Format history untuk AI
            $conversationHistory = [];
            foreach ($recentMessages as $msg) {
                $role = $msg->is_from_me ? 'assistant' : 'user';
                $content = $msg->is_from_me ? ($msg->bot_reply ?? $msg->message) : $msg->message;
                
                if (!empty($content)) {
                    $conversationHistory[] = [
                        'role' => $role,
                        'content' => $content,
                    ];
                }
            }
            
            Log::info('WhatsApp Conversation History', [
                'remote_jid' => $message->remote_jid,
                'history_count' => count($conversationHistory),
            ]);
            
            // Use WhatsApp-specific AI method for smarter, more conversational responses
            $aiService = app(\App\Services\AiAnswerService::class);
            // Explicitly pass user_id from device to ensure correct tenant isolation for KB search
            $aiResult = $aiService->answerWhatsApp(
                $message->message, 
                $conversationHistory, 
                $businessProfile,
                $device?->user_id
            );

            Log::info('Debug AI Result', [
                'has_answer' => !empty($aiResult['answer']),
                'source' => $aiResult['source'] ?? 'unknown',
                'user_id_used' => $device?->user_id
            ]);
            
            if ($aiResult && !empty($aiResult['answer'])) {
                $reply = $aiResult['answer'];
                $imageUrl = $aiResult['image_url'] ?? null;
                
                // Send the reply via WhatsApp (with image if available)
                $sendResult = $this->waService->sendMessage(
                    $sessionId,
                    $message->remote_jid,
                    $reply,
                    $imageUrl,
                    $imageUrl ? 'image' : null
                );

                if ($sendResult['success']) {
                    // Update the original message with bot reply
                    $message->update(['bot_reply' => $reply]);
                    
                    // UPDATE CONVERSATION PREVIEW WITH BOT REPLY
                    WaConversation::where('phone_number', $message->phone_number)
                        ->where('user_id', $message->user_id)
                        ->update([
                            'last_message' => "CS: " . Str::limit($reply, 50), // Prefix with CS: for clarity
                            'last_message_at' => now(),
                        ]);

                    Log::info('WhatsApp Auto-Reply Sent', [
                        'to' => $message->phone_number,
                        'original' => $message->message,
                        'reply' => $reply,
                        'source' => $aiResult['source'] ?? 'ai',
                        'confidence' => $aiResult['confidence'] ?? 0,
                    ]);

                    // ALERT SYSTEM: Notify Admin if sentiment is frustrated
                    $notifSettings = $businessProfile->notification_settings;
                    if (($aiResult['sentiment'] ?? '') === 'frustrated' && 
                        $businessProfile->admin_phone && 
                        ($notifSettings['notify_frustrated'] ?? false)) {
                        
                        $alertMsg = "⚠️ *PERINGATAN SENTIMEN NEGATIF*\n\n" .
                                    "Customer: *{$message->push_name}* (+{$message->phone_number})\n" .
                                    "Pesan: _\"{$message->message}\"_\n\n" .
                                    "Mohon segera cek dashboard untuk bantuan manual.";
                                    
                        $this->waService->sendMessage(
                            $sessionId,
                            $businessProfile->admin_phone . '@s.whatsapp.net',
                            $alertMsg
                        );

                        Log::info('WhatsApp Admin Alert Sent', ['to' => $businessProfile->admin_phone]);
                    }

                    // Track AI Message Usage
                    $tracker = app(\App\Services\UsageTrackingService::class);
                    $tracker->track($businessProfile->user_id, \App\Models\UsageRecord::FEATURE_AI_MESSAGES);
                } else {
                    Log::error('WhatsApp Auto-Reply Send Failed', [
                        'to' => $message->remote_jid,
                        'error' => $sendResult['error'] ?? 'Unknown error',
                    ]);
                }
            } else {
                Log::info('WhatsApp No Auto-Reply Generated', [
                    'message' => $message->message,
                    'result' => $aiResult,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Auto-Reply Error: ' . $e->getMessage());
        }
    }

    /**
     * Verify webhook key from Node.js service
     */
    protected function verifyWebhookKey(Request $request): bool
    {
        $key = $request->header('X-WA-Service-Key');
        $expectedKey = config('services.whatsapp.webhook_key', 'replyai-wa-secret');
        
        return $key === $expectedKey;
    }
}
