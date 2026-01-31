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

        // Multi-tenant: Data sudah auto-filter via Global Scope
        // Conversation, KbArticle, AutoReplyRule sudah filter by user
        $userConversationIds = Conversation::pluck('id');

        // --- SECTION 1: ONBOARDING CHECKLIST CALCULATION ---
        $onboarding = [
            'account_created' => true, // 1. Always true if logged in
            'wa_connected' => WhatsAppDevice::where('user_id', $userId)->where('status', 'connected')->exists(), // 2
            'kb_added' => KbArticle::exists(), // 3 (Filtered by User Global Scope)
            'chat_tested' => Message::whereIn('conversation_id', $userConversationIds)->exists(), // 4
            'ai_active' => AutoReplyRule::exists() || $user->csat_enabled, // 5
        ];

        $completedSteps = collect($onboarding)->filter()->count();
        $setupProgress = ($completedSteps / 5) * 100;

        // --- SECTION 2: STATS OVERVIEW ---
        // 1. TOTAL MESSAGES (Incoming)
        $totalMessagesToday = Message::whereIn('conversation_id', $userConversationIds)
            ->whereDate('created_at', Carbon::today())
            ->count();
            
        $totalMessagesYesterday = Message::whereIn('conversation_id', $userConversationIds)
            ->whereDate('created_at', Carbon::yesterday())
            ->count();
        
        $msgTrend = 0;
        if ($totalMessagesYesterday > 0) {
            $msgTrend = round((($totalMessagesToday - $totalMessagesYesterday) / $totalMessagesYesterday) * 100);
        } elseif ($totalMessagesToday > 0) {
            $msgTrend = 100;
        }

        // 2. AI RESPONSES
        $totalAutoReplies = AutoReplyLog::whereIn('conversation_id', $userConversationIds)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        $totalYesterdayAutoReplies = AutoReplyLog::whereIn('conversation_id', $userConversationIds)
            ->whereDate('created_at', Carbon::yesterday())
            ->count();

        $aiTrend = 0;
        if ($totalYesterdayAutoReplies > 0) {
            $aiTrend = round((($totalAutoReplies - $totalYesterdayAutoReplies) / $totalYesterdayAutoReplies) * 100);
        } elseif ($totalAutoReplies > 0) {
            $aiTrend = 100;
        }

        // 3. PENDING REPLIES
        $pendingInbox = Conversation::where('status', '!=', 'resolved')->count();

        // 4. KNOWLEDGE BASE
        $kbCount = KbArticle::count();

        // --- SECTION 3: ANALYTICS CHART (7 Days) ---
        $trend7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trend7Days[] = [
                'date' => $date->format('d M'),
                'messages' => Message::whereIn('conversation_id', $userConversationIds)
                    ->whereDate('created_at', $date)
                    ->count(),
                'ai_replies' => AutoReplyLog::whereIn('conversation_id', $userConversationIds)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        // Recent Activity
        $recentActivities = Message::with('conversation')
            ->whereIn('conversation_id', $userConversationIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($msg) {
                return [
                    'text' => ($msg->sender_type == 'contact' ? 'Pesan dari ' : 'Balasan ke ') . ($msg->conversation->ig_username ?? $msg->conversation->display_name ?? 'User'),
                    'time' => $msg->created_at->diffForHumans(),
                    'type' => $msg->sender_type
                ];
            });

        // Forecast Days (Legacy logic preserved)
        $avgDailyMsg = Message::whereIn('conversation_id', $userConversationIds)
            ->where('sender_type', '!=', 'contact')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count() / 7;
        
        $plan = $user->getPlan();
        $limit = $plan->features['ai_messages'] ?? 100;
        $used = Message::whereIn('conversation_id', $userConversationIds)
            ->where('sender_type', '!=', 'contact')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
            
        $daysLeft = $limit > $used && $avgDailyMsg > 0 ? floor(($limit - $used) / $avgDailyMsg) : 0;

        return view('pages.dashboard.replyai', [
            'title' => 'Dashboard ReplyAI',
            'user' => $user,
            'setup_progress' => $setupProgress,
            'onboarding' => $onboarding,
            'isFirstLogin' => $isFirstLogin,
            'stats' => [
                'total_messages' => $totalMessagesToday,
                'msg_trend' => $msgTrend,
                'ai_responses' => $totalAutoReplies,
                'ai_trend' => $aiTrend,
                'pending_replies' => $pendingInbox,
                'kb_articles' => $kbCount,
                'forecast_days' => $daysLeft,
            ],
            'activities' => $recentActivities,
            'trend7Days' => $trend7Days,
        ]);
    }
}
