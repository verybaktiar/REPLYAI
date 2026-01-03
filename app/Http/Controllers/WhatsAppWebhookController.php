<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Services\AutoReplyEngine;
use App\Models\WaMessage;
use App\Models\WaConversation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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

        // Get or create conversation and track user reply time
        $waConversation = WaConversation::firstOrCreate(
            ['phone_number' => $message->phone_number],
            ['display_name' => $message->push_name, 'session_status' => WaConversation::SESSION_ACTIVE]
        );
        
        // Update last user reply time
        $waConversation->update(['last_user_reply_at' => now()]);

        // Check if user is responding to session follow-up
        $lowerMessage = strtolower(trim($message->message));
        if ($waConversation->session_status === WaConversation::SESSION_FOLLOWUP_SENT) {
            if (in_array($lowerMessage, ['tidak', 'no', 'sudah', 'selesai', 'cukup', 'ok', 'oke', 'thanks', 'terima kasih'])) {
                // User says they're done - close session with nice message
                $closeMessage = "Baik kak, terima kasih sudah menghubungi RS PKU Muhammadiyah Surakarta 🙏\n\n" .
                               "Semoga informasinya bermanfaat! Jangan ragu untuk chat lagi jika ada pertanyaan 💚";
                $this->waService->sendMessage($message->remote_jid, $closeMessage);
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
        if ($this->waService->isAutoReplyEnabled() && !$isAgentHandling) {
            $this->processAutoReply($message);
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

        // QR code is handled client-side via polling
        // This webhook is just for logging/notification purposes
        Log::info('WhatsApp QR Generated');

        return response()->json(['success' => true]);
    }

    /**
     * Process auto-reply for incoming message using WhatsApp-specific AI
     */
    protected function processAutoReply(WaMessage $message): void
    {
        try {
            // Use WhatsApp-specific AI method for smarter, more conversational responses
            $aiService = app(\App\Services\AiAnswerService::class);
            $aiResult = $aiService->answerWhatsApp($message->message);
            
            if ($aiResult && !empty($aiResult['answer'])) {
                $reply = $aiResult['answer'];
                
                // Send the reply via WhatsApp using remote_jid 
                // This properly handles @lid format from linked devices
                $sendResult = $this->waService->sendMessage(
                    $message->remote_jid, // Use JID directly instead of phone_number
                    $reply
                );

                if ($sendResult['success']) {
                    // Update the original message with bot reply
                    $message->update(['bot_reply' => $reply]);
                    
                    Log::info('WhatsApp Auto-Reply Sent', [
                        'to' => $message->phone_number,
                        'original' => $message->message,
                        'reply' => $reply,
                        'source' => $aiResult['source'] ?? 'ai',
                        'confidence' => $aiResult['confidence'] ?? 0,
                    ]);
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
