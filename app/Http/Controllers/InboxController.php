<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InboxController extends Controller
{
    // tampilkan inbox dari DB lokal
    public function index(Request $request)
    {
        $conversations = Conversation::orderByDesc('last_activity_at')->get();
        
        // Ambil ID dari query atau pakai conversation terbaru
        $selectedId = $request->query('conversation_id');
        if (!$selectedId && $conversations->isNotEmpty()) {
            $selectedId = $conversations->first()->id;
        }
        
        $selectedConversation = $selectedId ? Conversation::find($selectedId) : null;
        
        return view('pages.inbox.index', [
            'title' => 'Inbox Instagram',
            'conversations' => $conversations,
            'selectedId' => $selectedId,
            'messages' => $selectedConversation?->messages()->orderBy('message_created_at')->get() ?? collect(),
            'contact' => $selectedConversation ? [
                'name' => $selectedConversation->display_name,
                'avatar' => $selectedConversation->avatar,
                'ig_username' => $selectedConversation->ig_username,
            ] : null,
        ]);
    }

    // kirim balasan manual via Meta API
    public function send(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'content' => 'required|string',
        ]);

        $conversationId = (int) $request->conversation_id;
        $content = $request->content;

        $conversation = Conversation::findOrFail($conversationId);

        // Kirim pesan via Meta Graph API
        $sent = $this->sendInstagramMessage(
            $conversation->instagram_user_id,
            $content
        );

        if ($sent) {
            // Simpan pesan ke database
            Message::create([
                'conversation_id' => $conversation->id,
                'chatwoot_id' => null,
                'instagram_message_id' => null,
                'sender_type' => 'agent',
                'content' => $content,
                'source' => 'meta_direct',
                'message_created_at' => now()->toDateTimeString(),
                'sent_at' => now()->toDateTimeString(),
            ]);

            // Update conversation
            $conversation->update([
                'last_message' => $content,
                'last_activity_at' => now()->toDateTimeString(),
                'status' => 'agent_handling',
                'agent_replied_at' => now(),
            ]);

            return redirect()
                ->route('inbox', ['conversation_id' => $conversationId])
                ->with('success', 'Pesan terkirim.');
        }

        return redirect()
            ->route('inbox', ['conversation_id' => $conversationId])
            ->with('error', 'Gagal mengirim pesan. Cek log untuk detail.');
    }

    /**
     * Kirim pesan ke Instagram via Meta Graph API
     */
    protected function sendInstagramMessage(string $recipientId, string $message): bool
    {
        $accessToken = config('services.instagram.access_token');
        $igUserId = config('services.instagram.instagram_user_id'); // Instagram Business Account ID

        try {
            $response = Http::acceptJson()->asJson()->post(
                "https://graph.instagram.com/v21.0/{$igUserId}/messages",
                [
                    'recipient' => ['id' => $recipientId],
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

            Log::info('✅ Agent message sent to Instagram', ['to' => $recipientId]);
            return true;
        } catch (\Throwable $e) {
            Log::error('❌ Exception sending message', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Cek apakah ada pesan baru (polling dari database)
     */
    public function hasNew(Request $request)
    {
        $conversationId = (int) $request->query('conversation_id');
        $since = (int) $request->query('since', 0);

        if (!$conversationId) {
            return response()->json(['has_new' => false]);
        }

        // Cek pesan terbaru dari database
        $latestMessage = Message::where('conversation_id', $conversationId)
            ->orderByDesc('message_created_at')
            ->first();

        $latestTs = $latestMessage 
            ? strtotime($latestMessage->message_created_at) 
            : 0;

        return response()->json([
            'has_new' => $latestTs > $since,
            'latest' => $latestTs,
        ]);
    }

    /**
     * Kembalikan conversation ke Bot handling
     */
    public function handbackToBot($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $conversation->update([
            'status' => 'bot_handling',
            'agent_replied_at' => null,
        ]);

        return redirect()
            ->route('inbox', ['conversation_id' => $conversationId])
            ->with('success', 'Percakapan dikembalikan ke Bot.');
    }
}
