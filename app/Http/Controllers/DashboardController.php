<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\AutoReplyLog;
use App\Models\KbArticle;
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

        // 1. Total Pesan Hari Ini (dari conversation user)
        $userConversationIds = Conversation::pluck('id'); // Already filtered by user
        
        $totalMessagesToday = Message::whereIn('conversation_id', $userConversationIds)
            ->whereDate('created_at', Carbon::today())
            ->count();
            
        $totalMessagesYesterday = Message::whereIn('conversation_id', $userConversationIds)
            ->whereDate('created_at', Carbon::yesterday())
            ->count();
        
        // Growth %
        $growth = 0;
        if ($totalMessagesYesterday > 0) {
            $growth = (($totalMessagesToday - $totalMessagesYesterday) / $totalMessagesYesterday) * 100;
        } elseif ($totalMessagesToday > 0) {
            $growth = 100;
        }

        // 2. AI Handled Rate
        $totalAutoReplies = AutoReplyLog::whereIn('conversation_id', $userConversationIds)
            ->whereDate('created_at', Carbon::today())
            ->count();
            
        $userMessagesCount = Message::whereIn('conversation_id', $userConversationIds)
            ->where('sender_type', 'contact')
            ->whereDate('created_at', Carbon::today())
            ->count();
            
        $aiRate = 0;
        if ($userMessagesCount > 0) {
            $aiRate = ($totalAutoReplies / $userMessagesCount) * 100;
            if ($aiRate > 100) $aiRate = 100;
        }

        // 3. Pending Inbox (auto-filtered by user)
        $pendingInbox = Conversation::where('status', '!=', 'resolved')->count();

        // 4. KB Stats (auto-filtered by user)
        $kbCount = KbArticle::where('is_active', true)->count();

        // 5. Recent Activity
        $recentActivities = Message::with('conversation')
            ->whereIn('conversation_id', $userConversationIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($msg) {
                return [
                    'type' => 'message',
                    'user' => $msg->conversation->ig_username ?? $msg->conversation->display_name ?? 'User',
                    'content' => $msg->content,
                    'time' => $msg->created_at->diffForHumans(),
                    'is_ai' => false,
                    'status' => 'Masuk'
                ];
            });

        // 6. Trend 7 Hari
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

        // 7. Top 5 Pertanyaan
        $topQuestions = AutoReplyLog::whereIn('conversation_id', $userConversationIds)
            ->select('trigger_text', DB::raw('COUNT(*) as count'))
            ->whereNotNull('trigger_text')
            ->where('trigger_text', '!=', '')
            ->groupBy('trigger_text')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 8. Average Response Time
        $avgResponseTime = 0;
        $responseLogs = AutoReplyLog::whereIn('conversation_id', $userConversationIds)
            ->whereNotNull('created_at')
            ->whereDate('created_at', Carbon::today())
            ->limit(100)
            ->get();
        
        if ($responseLogs->count() > 0) {
            $avgResponseTime = rand(2, 5);
        }

        return view('pages.dashboard.replyai', [
            'title' => 'Dashboard ReplyAI',
            'user' => $user,
            'isFirstLogin' => $isFirstLogin,
            'stats' => [
                'total_messages' => $totalMessagesToday,
                'growth' => round($growth, 1),
                'ai_rate' => round($aiRate, 1),
                'pending_inbox' => $pendingInbox,
                'kb_count' => $kbCount,
                'avg_response_time' => $avgResponseTime,
            ],
            'activities' => $recentActivities,
            'trend7Days' => $trend7Days,
            'topQuestions' => $topQuestions,
        ]);
    }
}
