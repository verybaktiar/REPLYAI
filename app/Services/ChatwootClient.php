<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Conversation;
use App\Models\Message;

class ChatwootClient
{
    protected string $baseUrl;
    protected string $accountId;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('services.chatwoot.base_url'), '/');
        $this->accountId = config('services.chatwoot.account_id');
        $this->token     = config('services.chatwoot.api_token');
    }

    protected function client()
    {
        return Http::withHeaders([
            'api_access_token' => $this->token,
            'Content-Type' => 'application/json',
        ]);
    }

    // =========================
    // RAW CHATWOOT METHODS
    // =========================
    public function getConversations(): array
    {
        $url = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations";
        return $this->client()->get($url)->json() ?? [];
    }

    public function getMessages(int $conversationId): array
    {
        $url = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages";
        return $this->client()->get($url)->json() ?? [];
    }

    // =========================
    // NORMALIZED CONVERSATIONS
    // (dipakai UI / sync)
    // =========================
    public function getConversationsNormalized(): array
    {
        $res = $this->getConversations();

        $raw = $res['data'] ?? $res['payload'] ?? $res ?? [];
        if (isset($raw['payload']) && is_array($raw['payload'])) {
            $raw = $raw['payload'];
        }

        $out = [];
        foreach ($raw as $c) {
            $id = data_get($c, 'id');
            if (!$id) continue;

            $contact = data_get($c, 'meta.contact')
                ?? data_get($c, 'contact')
                ?? [];

            $igUsername =
                data_get($contact, 'additional_attributes.social_instagram_user_name')
                ?? data_get($contact, 'additional_attributes.social_profiles.instagram');

            $displayName = $igUsername
                ? '@' . $igUsername
                : (data_get($contact, 'name') ?? 'User');

            $avatar = data_get($contact, 'thumbnail');

            $lastMsg =
                data_get($c, 'last_non_activity_message.content')
                ?? data_get($c, 'messages.0.content')
                ?? '';

            $lastAt =
                data_get($c, 'last_activity_at')
                ?? data_get($c, 'created_at')
                ?? null;

            $out[] = [
                'id'            => $id,
                'display_name'  => $displayName,
                'avatar'        => $avatar,
                'last_message'  => $lastMsg,
                'last_at'       => $lastAt,
            ];
        }

        return $out;
    }

    // =========================
    // ✅ NORMALIZED MESSAGES
    // (buat kompatibilitas SyncChatwoot lama)
    // =========================
    public function getMessagesNormalized(int $conversationId): array
    {
        $messagesResponse = $this->getMessages($conversationId);

        $messages = $messagesResponse['payload'] ?? [];
        usort($messages, fn($a,$b) => ($a['created_at'] ?? 0) <=> ($b['created_at'] ?? 0));

        $meta    = $messagesResponse['meta'] ?? [];
        $contact = data_get($meta, 'contact')
            ?? data_get($meta, 'contact.payload.0')
            ?? null;

        return [
            'messages' => $messages,
            'contact'  => $contact,
        ];
    }

    // =========================
    // SEND MESSAGE (REPLY)
    // =========================
    public function sendMessage(int $conversationId, string $content): array
    {
        $url = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages";

        return $this->client()->post($url, [
            "content" => $content,
            "message_type" => "outgoing",
            "private" => false,
        ])->json() ?? [];
    }

    // =========================
    // SYNC 1 CONVERSATION TO DB
    // =========================
    public function syncOneConversationToDb(int $conversationId): void
    {
        $convs = $this->getConversationsNormalized();
        $conv = collect($convs)->firstWhere('id', $conversationId);
        if (!$conv) return;

        $localConv = Conversation::updateOrCreate(
            ['chatwoot_id' => $conv['id']],
            [
                'ig_username'       => ltrim($conv['display_name'] ?? '', '@'),
                'display_name'      => $conv['display_name'] ?? 'User',
                'avatar'            => $conv['avatar'] ?? null,
                'last_message'      => $conv['last_message'] ?? null,
                'last_activity_at'  => $conv['last_at'] ?? null,
            ]
        );

        $messagesData = $this->getMessages($conversationId);
        $messages = $messagesData['payload'] ?? [];

        foreach ($messages as $m) {
            $chatwootMsgId = data_get($m, 'id');
            if (!$chatwootMsgId) continue;

            Message::updateOrCreate(
                ['chatwoot_id' => $chatwootMsgId],
                [
                    'conversation_id'    => $localConv->id,
                    'sender_type'        => data_get($m, 'sender_type') ?? 'unknown',
                    'content'            => data_get($m, 'content') ?? '',
                    'message_created_at' => data_get($m, 'created_at') ?? time(),
                ]
            );
        }
    }
    
}
