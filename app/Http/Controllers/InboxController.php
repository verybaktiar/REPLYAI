<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\InstagramAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InboxController extends Controller
{
    // tampilkan inbox dari DB lokal - HANYA milik akun IG yang aktif
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Guard against unauthenticated access
        if (!$user) {
            return redirect()->route('login');
        }
        
        // ✅ Get the currently active Instagram account for this user
        $activeIgAccount = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
        
        // Check if user has connected Instagram
        $hasInstagramAccount = $activeIgAccount !== null;
        
        // ✅ Multi-tenancy: Hanya tampilkan conversation milik akun IG yang aktif
        $conversations = collect();
        if ($activeIgAccount) {
            $conversations = Conversation::where('instagram_account_id', $activeIgAccount->id)
                ->orderByDesc('last_activity_at')
                ->get();
        }
        
        // ✅ Hanya pilih conversation jika ada di query parameter
        // Tidak auto-select conversation pertama
        $selectedId = $request->query('conversation_id');
        
        // Pastikan selected conversation milik akun IG ini
        $selectedConversation = null;
        if ($selectedId && $activeIgAccount) {
            $selectedConversation = Conversation::where('id', $selectedId)
                ->where('instagram_account_id', $activeIgAccount->id)
                ->first();
        }
        
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
            'hasInstagramAccount' => $hasInstagramAccount,
            'instagramAccount' => $activeIgAccount,
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
        $result = $this->sendInstagramMessage(
            $conversation->instagram_user_id,
            $content
        );

        if ($result['success']) {
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

        // Error handling dengan pesan spesifik
        $errorMessage = $result['error_message'] ?? 'Gagal mengirim pesan. Cek log untuk detail.';
        
        return redirect()
            ->route('inbox', ['conversation_id' => $conversationId])
            ->with('error', $errorMessage);
    }

    /**
     * Kirim pesan ke Instagram via Meta Graph API
     * Menggunakan token dari database berdasarkan user yang login
     * @return array{success: bool, error_message?: string}
     */
    protected function sendInstagramMessage(string $recipientId, string $message): array
    {
        // ✅ Ambil token dari database berdasarkan user yang login
        $user = Auth::user();
        $igAccount = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$igAccount) {
            Log::error('❌ User tidak memiliki akun Instagram yang terhubung', ['user_id' => $user->id]);
            return [
                'success' => false, 
                'error_message' => 'Akun Instagram belum terhubung. Silakan hubungkan di Pengaturan Instagram.'
            ];
        }

        // Cek apakah token expired
        if ($igAccount->isTokenExpired()) {
            Log::error('❌ Token Instagram sudah expired', ['user_id' => $user->id]);
            return [
                'success' => false, 
                'error_message' => '🔑 Token Instagram sudah kadaluarsa. Silakan hubungkan ulang di Pengaturan Instagram.'
            ];
        }

        $accessToken = $igAccount->access_token;
        $igUserId = $igAccount->instagram_user_id;

        // 🔍 Debug: Log konfigurasi
        Log::info('📤 Attempting to send Instagram message', [
            'recipient_id' => $recipientId,
            'ig_user_id' => $igUserId,
            'user_id' => $user->id,
            'has_access_token' => !empty($accessToken),
            'message_length' => strlen($message),
        ]);

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
                $errorData = $response->json();
                $errorSubcode = $errorData['error']['error_subcode'] ?? null;
                $metaMessage = $errorData['error']['message'] ?? 'Unknown error';

                Log::error('❌ Failed to send Instagram message', [
                    'status' => $response->status(),
                    'error' => $errorData,
                    'recipient_id' => $recipientId,
                    'ig_user_id' => $igUserId,
                ]);

                // Error spesifik untuk jendela 24 jam
                if ($errorSubcode === 2534022) {
                    return [
                        'success' => false, 
                        'error_message' => '⏰ Jendela 24 jam sudah berakhir. User harus mengirim pesan baru terlebih dahulu agar Anda bisa membalas.'
                    ];
                }

                // Error token expired
                if ($response->status() === 401 || $errorSubcode === 463) {
                    return [
                        'success' => false, 
                        'error_message' => '🔑 Access Token Instagram sudah kadaluarsa. Silakan perbarui di pengaturan.'
                    ];
                }

                return ['success' => false, 'error_message' => "Instagram API Error: {$metaMessage}"];
            }

            Log::info('✅ Agent message sent to Instagram', ['to' => $recipientId]);
            return ['success' => true];
        } catch (\Throwable $e) {
            Log::error('❌ Exception sending Instagram message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'error_message' => 'Terjadi kesalahan sistem. Cek log untuk detail.'];
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
     * Check for new messages (polling endpoint for frontend)
     */
    public function checkNew(Request $request)
    {
        $conversationId = (int) $request->query('conversation_id');
        $since = (int) $request->query('since', 0); // since = message count

        if (!$conversationId) {
            return response()->json(['has_new' => false]);
        }

        // Count current messages
        $currentCount = Message::where('conversation_id', $conversationId)->count();
        
        // Get latest user message for preview
        $latestUserMsg = Message::where('conversation_id', $conversationId)
            ->where('sender_type', 'user')
            ->orderByDesc('created_at')
            ->first();

        $hasNew = $currentCount > $since;

        return response()->json([
            'has_new' => $hasNew,
            'count' => $currentCount,
            'preview' => $hasNew && $latestUserMsg ? substr($latestUserMsg->content, 0, 50) . '...' : null,
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
