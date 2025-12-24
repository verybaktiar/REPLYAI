<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatwootClient;
use App\Models\Conversation;
use App\Models\Message;
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

    // kirim balasan manual (kamu sudah punya ini sebelumnya, disatuin biar rapih)
    public function send(Request $request, ChatwootClient $chatwoot)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'content' => 'required|string',
        ]);

        $conversationId = (int) $request->conversation_id;
        $content = $request->content;

        // kirim ke Chatwoot (endpoint send message)
        $chatwoot->sendMessage($conversationId, $content);

        // OPTIONAL: langsung sync conversation ini biar DB update
        $chatwoot->syncOneConversationToDb($conversationId);

        return redirect()
            ->route('inbox', ['conversation_id' => $conversationId])
            ->with('success', 'Pesan terkirim.');
    }

    /**
     * Endpoint polling:
     * - frontend kirim conversation_id + since (timestamp pesan terakhir yang sedang tampil)
     * - backend cek ke Chatwoot apakah ada pesan lebih baru
     * - kalau ada => sync ke DB & balikin has_new=true
     */
    public function hasNew(Request $request, ChatwootClient $chatwoot)
    {
        $conversationId = (int) $request->query('conversation_id');
        $since = (int) $request->query('since', 0);

        if (!$conversationId) {
            return response()->json(['has_new' => false]);
        }

        $raw = $chatwoot->getMessages($conversationId);
        $payload = $raw['payload'] ?? [];

        $latestTs = 0;
        foreach ($payload as $m) {
            $ts = (int) ($m['created_at'] ?? 0);
            if ($ts > $latestTs) $latestTs = $ts;
        }

        if ($latestTs > $since) {
            // ada pesan baru -> sync 1 conversation
            $chatwoot->syncOneConversationToDb($conversationId);

            return response()->json([
                'has_new' => true,
                'latest' => $latestTs,
            ]);
        }

        return response()->json([
            'has_new' => false,
            'latest' => $latestTs,
        ]);
    }
}
