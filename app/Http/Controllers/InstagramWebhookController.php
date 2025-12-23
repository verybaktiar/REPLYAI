<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\AutoReplyRule;
use App\Models\AutoReplyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\AutoReplyEngine;

class InstagramWebhookController extends Controller
{
    protected $engine;

    public function __construct(AutoReplyEngine $engine)
    {
        $this->engine = $engine;
    }

    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::info('🔍 Webhook verification', [
            'mode' => $mode,
            'token' => $token,
        ]);

        if ($mode === 'subscribe' && $token === config('services.instagram.webhook_verify_token')) {
            Log::info('✅ Webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::error('❌ Webhook verification failed');
        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('📩 Instagram webhook received (Meta Direct)', $payload);

            if (($payload['object'] ?? '') !== 'instagram') {
                return response()->json(['status' => 'ignored']);
            }

            foreach ($payload['entry'] ?? [] as $entry) {
                foreach ($entry['messaging'] ?? [] as $event) {
                    if (isset($event['message']) && !isset($event['message']['is_echo'])) {
                        $senderId = $event['sender']['id'];
                        $recipientId = $event['recipient']['id'];
                        $messageText = $event['message']['text'] ?? '';
                        $messageId = $event['message']['mid'] ?? null;

                        Log::info('Processing message', [
                            'sender' => $senderId,
                            'text' => $messageText,
                        ]);

                        $this->processMessage($senderId, $messageText, $messageId, $recipientId);
                    }
                }
            }

            return response()->json(['status' => 'ok']);
            
        } catch (\Exception $e) {
            Log::error('❌ Webhook handler error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json(['status' => 'error'], 200);
        }
    }

    protected function processMessage(string $senderId, string $messageText, ?string $messageId, string $recipientId)
    {
        // 1. Ambil username Instagram
        $username = $this->getInstagramUsername($senderId);

        // 2. Cari atau buat conversation
        $conversation = Conversation::firstOrCreate(
            ['instagram_user_id' => $senderId],
            [
                'chatwoot_id' => null,
                'ig_username' => $username,
                'display_name' => $username ?? 'Instagram User',
                'avatar' => null,
                'last_message' => $messageText,
                'source' => 'meta_direct',
                'last_activity_at' => now()->toDateTimeString(),
                'status' => 'open',
            ]
        );

        // 3. Update conversation
        $conversation->update([
            'last_message' => $messageText,
            'last_activity_at' => now()->toDateTimeString(),
        ]);

        // 4. Simpan message user
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'chatwoot_id' => null,
            'instagram_message_id' => $messageId,
            'sender_type' => 'contact',
            'content' => $messageText,
            'source' => 'meta_direct',
            'message_created_at' => now()->toDateTimeString(),
            'sent_at' => now()->toDateTimeString(),
        ]);

        Log::info('💾 Message saved to database', ['message_id' => $message->id]);

        // 5️⃣ GUNAKAN AUTO REPLY ENGINE (Manual Rule + AI Fallback)
        $engineResult = $this->engine->handleIncomingInstagramMessage($message, $conversation);

        if ($engineResult) {
            Log::info('🚀 Got response from engine', [
                'source' => $engineResult['source'],
                'response' => $engineResult['response'],
            ]);

            // Kirim ke Instagram
            $sent = $this->sendInstagramMessage($senderId, $engineResult['response'], $recipientId);

            if ($sent) {
                // Simpan message bot
                Message::create([
                    'conversation_id' => $conversation->id,
                    'chatwoot_id' => null,
                    'instagram_message_id' => null,
                    'sender_type' => 'agent',
                    'content' => $engineResult['response'],
                    'source' => 'meta_direct',
                    'message_created_at' => now()->toDateTimeString(),
                    'sent_at' => now()->toDateTimeString(),
                ]);

                // Update conversation
                $conversation->update([
                    'last_message' => $engineResult['response'],
                    'last_activity_at' => now()->toDateTimeString(),
                ]);

                Log::info('✅ Bot reply sent and saved', [
                    'source' => $engineResult['source'],
                ]);
            } else {
                Log::error('❌ Failed to send bot reply to Instagram');
                
                // Update AutoReplyLog status jadi failed
                $log = AutoReplyLog::where('message_id', $message->id)->first();
                if ($log) {
                    $log->update(['status' => 'failed']);
                }
            }
        } else {
            Log::info('⏭️ Engine returned null (no rule, AI skipped or confident = false)');
        }
    }

    protected function sendInstagramMessage(string $recipientId, string $message, string $igUserId): bool
    {
        $accessToken = config('services.instagram.access_token');

        try {
            $response = Http::post("https://graph.instagram.com/v21.0/{$igUserId}/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $message],
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                Log::error('❌ Failed to send Instagram message', [
                    'error' => $response->json(),
                ]);
                return false;
            }

            Log::info('✅ Message sent to Instagram');
            return true;
            
        } catch (\Exception $e) {
            Log::error('❌ Exception sending message', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function getInstagramUsername(string $userId): ?string
    {
        $accessToken = config('services.instagram.access_token');

        try {
            $response = Http::get("https://graph.instagram.com/v21.0/{$userId}", [
                'fields' => 'username',
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return $response->json('username');
            }
        } catch (\Exception $e) {
            Log::error('Failed to get username', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function getConversations()
    {
        $conversations = Conversation::orderByDesc('last_activity_at')->get();
        return response()->json($conversations);
    }

    public function getMessages($id)
    {
        $messages = Message::where('conversation_id', $id)
            ->orderBy('sent_at')
            ->get();
        return response()->json($messages);
    }
}
