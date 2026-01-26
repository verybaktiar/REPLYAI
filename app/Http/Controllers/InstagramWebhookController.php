<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\AutoReplyLog;
use App\Models\InstagramAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\AutoReplyEngine;
use App\Services\ReplyTemplate;
class InstagramWebhookController extends Controller
{
    protected AutoReplyEngine $engine;
    protected ReplyTemplate $tpl;

        // di constructor inject ReplyTemplate $tpl
    public function __construct(AutoReplyEngine $engine, ReplyTemplate $tpl)
    {
        $this->engine = $engine;
        $this->tpl = $tpl;
    }
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.instagram.webhook_verify_token')) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

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

                    // only real incoming message (not echo)
                    if (!isset($event['message']) || isset($event['message']['is_echo'])) {
                        continue;
                    }

                    $senderId    = (string) ($event['sender']['id'] ?? '');
                    $recipientId = (string) ($event['recipient']['id'] ?? ''); // ig user id
                    $messageText = (string) ($event['message']['text'] ?? '');
                    $mid         = $event['message']['mid'] ?? null;

                    if ($senderId === '' || $recipientId === '' || trim($messageText) === '') continue;

                    // ✅ Skip jika mid sudah pernah diproses (anti duplikat)
                    if ($mid && Message::where('instagram_message_id', $mid)->exists()) {
                        Log::info('⏭️ Skipping duplicate message', ['mid' => $mid]);
                        continue;
                    }

                    Log::info('Processing message', [
                        'sender' => $senderId,
                        'text' => $messageText,
                        'mid' => $mid,
                    ]);

                    $this->processMessage($senderId, $messageText, $mid, $recipientId);
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('❌ Webhook handler error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['status' => 'error'], 200);
        }
    }

    protected function processMessage(string $senderId, string $messageText, ?string $messageId, string $igUserId)
    {
        // ✅ Multi-tenancy: Find which user owns this Instagram account
        $igAccount = InstagramAccount::findByInstagramId($igUserId);
        
        // Log warning only if account not found (important for troubleshooting)
        if (!$igAccount) {
            Log::warning('Instagram webhook: Account not found for recipient', [
                'instagram_user_id' => $igUserId,
            ]);
        }
        
        $userId = $igAccount?->user_id;
        $igAccountId = $igAccount?->id;
        $accessToken = $igAccount?->access_token ?? config('services.instagram.access_token');
        
        if (!$accessToken) {
            Log::warning('Instagram: No access token found for recipient', ['igUserId' => $igUserId]);
            return;
        }

        $userInfo = $this->getInstagramUserInfo($senderId, $accessToken);

        $username = $userInfo['username'] ?? null;
        $name     = $userInfo['name'] ?? null;
        $avatar   = $userInfo['profile_pic'] ?? null;

        // ✅ Include user_id AND instagram_account_id for proper multi-tenancy
        $conversation = Conversation::firstOrCreate(
            [
                'instagram_user_id' => $senderId, 
                'instagram_account_id' => $igAccountId  // ✅ Link ke akun IG spesifik
            ],
            [
                'user_id' => $userId,
                'chatwoot_id' => null,
                'ig_username' => $username,
                'display_name' => $name ?? $username ?? 'Instagram User',
                'avatar' => $avatar,
                'last_message' => $messageText,
                'source' => 'meta_direct',
                'last_activity_at' => now()->toDateTimeString(),
                'status' => 'open',
            ]
        );

        $conversation->update([
            'ig_username' => $username ?? $conversation->ig_username,
            'display_name' => $name ?? $username ?? $conversation->display_name,
            'avatar' => $avatar ?? $conversation->avatar,
            'last_message' => $messageText,
            'last_activity_at' => now()->toDateTimeString(),
            // ✅ Always link to user and IG account if we found one
            'user_id' => $userId ?? $conversation->user_id,
            'instagram_account_id' => $igAccountId ?? $conversation->instagram_account_id,
        ]);
        
        // Store access token for later use in this request
        $this->currentAccessToken = $accessToken;

                // ✅ Welcome 1x per conversation
        if (!$conversation->has_sent_welcome) {
            $welcome = $this->tpl->welcome();

            $sentWelcome = $this->sendInstagramMessage($senderId, $welcome, $igUserId);
            if ($sentWelcome) {
                // simpan message bot welcome
                Message::create([
                    'conversation_id' => $conversation->id,
                    'chatwoot_id' => null,
                    'instagram_message_id' => null,
                    'sender_type' => 'agent',
                    'content' => $welcome,
                    'source' => 'meta_direct',
                    'message_created_at' => now()->toDateTimeString(),
                    'sent_at' => now()->toDateTimeString(),
                ]);

                // tandai sudah welcome
                $conversation->update([
                    'has_sent_welcome' => true,
                    'last_message' => $welcome,
                    'last_activity_at' => now()->toDateTimeString(),
                ]);
            }
        }


        // ✅ sender_type harus 'user' supaya engine jalan
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'chatwoot_id' => null,
            'instagram_message_id' => $messageId,
            'sender_type' => 'user',
            'content' => $messageText,
            'source' => 'meta_direct',
            'message_created_at' => now()->toDateTimeString(),
            'sent_at' => now()->toDateTimeString(),
        ]);

        Log::info('💾 Message saved to database', ['message_id' => $message->id]);

        $engineResult = $this->engine->handleIncomingInstagramMessage($message, $conversation);

        if (!$engineResult) {
            Log::info('⏭️ Engine returned null (no rule / AI skipped)');
            return;
        }

        $replyText = (string) $engineResult['response'];
        Log::info('🚀 Got response from engine', [
            'source' => $engineResult['source'],
            'response' => $replyText,
        ]);

        $sent = $this->sendInstagramMessage($senderId, $replyText, $igUserId);

        if ($sent) {
            Message::create([
                'conversation_id' => $conversation->id,
                'chatwoot_id' => null,
                'instagram_message_id' => null,
                'sender_type' => 'agent',
                'content' => $replyText,
                'source' => 'meta_direct',
                'message_created_at' => now()->toDateTimeString(),
                'sent_at' => now()->toDateTimeString(),
            ]);

            $conversation->update([
                'last_message' => $replyText,
                'last_activity_at' => now()->toDateTimeString(),
            ]);

            Log::info('✅ Bot reply sent and saved', ['source' => $engineResult['source']]);
        } else {
            Log::error('❌ Failed to send bot reply to Instagram');

            $log = AutoReplyLog::where('message_id', $message->id)->first();
            if ($log) $log->update(['status' => 'failed']);
        }
    }

    /**
     * Membersihkan markdown formatting dari pesan untuk Instagram
     * Instagram DM tidak mendukung markdown, jadi *bold* akan tampil sebagai *text*
     */
    protected function stripMarkdown(string $text): string
    {
        // Hapus bold markers: *text* -> text
        $text = preg_replace('/\*([^\*]+)\*/', '$1', $text);
        
        // Hapus italic/underscore markers: _text_ -> text (jika diperlukan)
        $text = preg_replace('/_([^_]+)_/', '$1', $text);
        
        return $text;
    }

    // Property to store current request's access token
    protected ?string $currentAccessToken = null;

    protected function sendInstagramMessage(string $toUserId, string $message, string $igUserId, ?string $accessToken = null): bool
    {
        // ✅ Use provided token, stored token, or fallback to global config
        $accessToken = $accessToken ?? $this->currentAccessToken ?? config('services.instagram.access_token');
        
        // Bersihkan markdown sebelum kirim ke Instagram
        $message = $this->stripMarkdown($message);

        try {
            $response = Http::acceptJson()->asJson()->post(
                "https://graph.instagram.com/v21.0/{$igUserId}/messages",
                [
                    'recipient' => ['id' => $toUserId],
                    'message' => ['text' => $message],
                    'access_token' => $accessToken,
                ]
            );

            if ($response->failed()) {
                Log::error('❌ Failed to send Instagram message', [
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);
                return false;
            }

            Log::info('✅ Message sent to Instagram', ['to' => $toUserId]);
            return true;
        } catch (\Throwable $e) {
            Log::error('❌ Exception sending message', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function getInstagramUserInfo(string $userId, ?string $accessToken = null): array
    {
        // ✅ Use provided token or fallback to global config
        $accessToken = $accessToken ?? $this->currentAccessToken ?? config('services.instagram.access_token');

        try {
            $response = Http::acceptJson()->get("https://graph.instagram.com/v21.0/{$userId}", [
                'fields' => 'name,username,profile_pic',
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('✅ Instagram User Profile API success', $data);
                return $data;
            }

            Log::error('❌ Failed to get Instagram user info', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Exception getting Instagram user info', [
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }
}
