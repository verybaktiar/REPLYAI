<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\AutoReplyLog;
use App\Models\KbArticle;
use App\Models\WaMessage;
use App\Models\WhatsAppDevice;
use App\Models\AutoReplyRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        
        // Cek apakah ini first time login (untuk welcome popup)
        $isFirstLogin = !session()->has('welcomed_' . $userId);
        if ($isFirstLogin) {
            session(['welcomed_' . $userId => true]);
        }

        // --- SECTION 1: ONBOARDING CHECKLIST CALCULATION ---
        // (Tidak di-cache karena status bisa berubah cepat saat onboarding)
        // Multi-tenant: Data sudah auto-filter via Global Scope
        // Conversation, KbArticle, AutoReplyRule sudah filter by user
        $userConversationIds = Conversation::pluck('id');

        // Fetch all devices for this user (Scoped by UserTenantScope)
        $waDevices = WhatsAppDevice::all();
        
        // Robust check: Status is 'connected' OR user has sent/received messages
        $waConnected = $waDevices->contains(fn($device) => $device->isConnected()) || 
                       WaMessage::exists(); // Fallback if status sync is delayed

        $onboarding = [
            'account_created' => true, // 1. Always true if logged in
            'wa_connected' => $waConnected,
            'kb_added' => KbArticle::exists(), // 3 (Filtered by User Global Scope)
            'chat_tested' => Message::whereIn('conversation_id', $userConversationIds)->exists() || 
                             WaMessage::where('user_id', $userId)->exists(), // 4 - Include WhatsApp
            'ai_active' => AutoReplyRule::exists() || $user->csat_enabled, // 5
        ];

        $completedSteps = collect($onboarding)->filter()->count();
        $setupProgress = ($completedSteps / 5) * 100;

        // --- CACHED STATS CALCULATION (5 Minutes) ---
        $cacheKey = "dashboard_stats_{$userId}";
        $stats = Cache::remember($cacheKey, 300, function () use ($userConversationIds, $user) {
            
            // --- SECTION 2: STATS OVERVIEW ---
            // Include both Instagram (Message) and WhatsApp (WaMessage)
            
            // 1. TOTAL MESSAGES (Incoming) - Instagram
            $igMessagesToday = Message::whereIn('conversation_id', $userConversationIds)
                ->whereDate('created_at', Carbon::today())
                ->count();
            
            // WhatsApp messages
            $waMessagesToday = WaMessage::where('user_id', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->count();
            
            $totalMessagesToday = $igMessagesToday + $waMessagesToday;
            
            // Yesterday
            $igMessagesYesterday = Message::whereIn('conversation_id', $userConversationIds)
                ->whereDate('created_at', Carbon::yesterday())
                ->count();
            $waMessagesYesterday = WaMessage::where('user_id', $user->id)
                ->whereDate('created_at', Carbon::yesterday())
                ->count();
            $totalMessagesYesterday = $igMessagesYesterday + $waMessagesYesterday;
            
            $msgTrend = 0;
            if ($totalMessagesYesterday > 0) {
                $msgTrend = round((($totalMessagesToday - $totalMessagesYesterday) / $totalMessagesYesterday) * 100);
            } elseif ($totalMessagesToday > 0) {
                $msgTrend = 100;
            }

            // 2. AI RESPONSES - Include WhatsApp auto-replies
            $igAutoReplies = AutoReplyLog::whereIn('conversation_id', $userConversationIds)
                ->whereDate('created_at', Carbon::today())
                ->count();
            
            // Count WhatsApp AI replies (messages with bot_reply not null)
            $waAutoReplies = WaMessage::where('user_id', $user->id)
                ->whereNotNull('bot_reply')
                ->whereDate('created_at', Carbon::today())
                ->count();
            
            $totalAutoReplies = $igAutoReplies + $waAutoReplies;
            
            // Yesterday AI replies
            $igAutoRepliesYesterday = AutoReplyLog::whereIn('conversation_id', $userConversationIds)
                ->whereDate('created_at', Carbon::yesterday())
                ->count();
            $waAutoRepliesYesterday = WaMessage::where('user_id', $user->id)
                ->whereNotNull('bot_reply')
                ->whereDate('created_at', Carbon::yesterday())
                ->count();
            $totalYesterdayAutoReplies = $igAutoRepliesYesterday + $waAutoRepliesYesterday;

            $aiTrend = 0;
            if ($totalYesterdayAutoReplies > 0) {
                $aiTrend = round((($totalAutoReplies - $totalYesterdayAutoReplies) / $totalYesterdayAutoReplies) * 100);
            } elseif ($totalAutoReplies > 0) {
                $aiTrend = 100;
            }

            // --- AI RATE ---
            $igUserMessages = Message::whereIn('conversation_id', $userConversationIds)
                ->where('sender_type', 'contact')
                ->whereDate('created_at', Carbon::today())
                ->count();
            $waUserMessages = WaMessage::where('user_id', $user->id)
                ->where('direction', 'incoming')
                ->whereDate('created_at', Carbon::today())
                ->count();
            $userMessagesCount = $igUserMessages + $waUserMessages;
                
            $aiRate = 0;
            if ($userMessagesCount > 0) {
                $aiRate = round(($totalAutoReplies / $userMessagesCount) * 100);
            }

            // --- PENDING REPLIES (WhatsApp unread) ---
            // WhatsApp: status != 'read' for incoming messages
            $waPending = WaMessage::where('user_id', $user->id)
                ->where('direction', 'incoming')
                ->where('status', '!=', 'read')
                ->count();
            // Instagram: tidak ada kolom is_read, gunakan waPending saja
            $pendingReplies = $waPending;

            // --- KB ARTICLES ---
            $kbArticles = KbArticle::where('user_id', $user->id)
                ->where('is_active', true)
                ->count();

            // --- FORECAST (Estimasi kuota habis) ---
            $dailyRate = $totalMessagesToday > 0 ? $totalMessagesToday : 1;
            $quota = 10000;
            $used = $totalMessagesToday;
            $remaining = $quota - $used;
            $daysLeft = round($remaining / $dailyRate);

            return [
                'total_messages' => $totalMessagesToday,
                'msg_trend' => $msgTrend,
                'ai_responses' => $totalAutoReplies,
                'ai_trend' => $aiTrend,
                'ai_rate' => $aiRate,
                'forecast_days' => $daysLeft,
                'pending_replies' => $pendingReplies,
                'kb_articles' => $kbArticles,
            ];
        });

        // --- SECTION 3: RECENT ACTIVITIES (Live Feed) ---
        // Not cached because it needs to be real-time
        $activities = Message::whereIn('conversation_id', $userConversationIds)
            ->latest()
            ->take(5)
            ->with('conversation') // Eager load conversation
            ->get()
            ->map(function ($msg) {
                return [
                    'type' => $msg->sender_type, // 'contact' or 'user' (bot)
                    'text' => $msg->body,
                    'time' => $msg->created_at->diffForHumans(),
                    'contact' => $msg->conversation->phone_number ?? 'Unknown'
                ];
            });

        // --- SECTION 4: CHART DATA (7 Days Trend) ---
        // Cached for 1 hour as historical data doesn't change often
        $chartCacheKey = "dashboard_chart_{$userId}";
        $trend7Days = Cache::remember($chartCacheKey, 3600, function () use ($userConversationIds, $userId) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                // Instagram messages
                $igMsgCount = Message::whereIn('conversation_id', $userConversationIds)
                    ->whereDate('created_at', $date)
                    ->count();
                // WhatsApp messages
                $waMsgCount = WaMessage::where('user_id', $userId)
                    ->whereDate('created_at', $date)
                    ->count();
                $msgCount = $igMsgCount + $waMsgCount;
                
                // Instagram AI replies
                $igAiCount = AutoReplyLog::whereIn('conversation_id', $userConversationIds)
                    ->whereDate('created_at', $date)
                    ->count();
                // WhatsApp AI replies (messages with bot_reply)
                $waAiCount = WaMessage::where('user_id', $userId)
                    ->whereNotNull('bot_reply')
                    ->whereDate('created_at', $date)
                    ->count();
                $aiCount = $igAiCount + $waAiCount;
                
                $data[] = [
                    'date' => $date->format('d M'),
                    'messages' => $msgCount,
                    'ai_replies' => $aiCount
                ];
            }
            return $data;
        });

        return view('pages.dashboard.replyai', compact(
            'onboarding', 
            'setupProgress', 
            'stats', 
            'activities', 
            'trend7Days', 
            'isFirstLogin'
        ));
    }

    public function roadmap()
    {
        return view('pages.dashboard.roadmap');
    }
}
