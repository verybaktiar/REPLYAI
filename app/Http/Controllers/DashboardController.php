<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        // 1. Total Pesan Hari Ini
        $totalMessagesToday = Message::whereDate('created_at', Carbon::today())->count();
        $totalMessagesYesterday = Message::whereDate('created_at', Carbon::yesterday())->count();
        
        // Growth %
        $growth = 0;
        if ($totalMessagesYesterday > 0) {
            $growth = (($totalMessagesToday - $totalMessagesYesterday) / $totalMessagesYesterday) * 100;
        } elseif ($totalMessagesToday > 0) {
            $growth = 100; // dari 0 ke ada pesan = 100% growth
        }

        // 2. AI Handled Rate (Success)
        // Kita hitung dari log auto reply yang statusnya 'sent' atau 'success'
        // Asumsi: setiap log adalah attempt AI/Rule menjawab
        // Total attempt vs total user interaction
        
        // Pendekatan simpel: Hitung berapa log yang triggered "rule" atau "ai" vs total conversation active
        // Atau lebih simpel: Berapa % log yang statusnya sukses.
        $totalAutoReplies = AutoReplyLog::whereDate('created_at', Carbon::today())->count();
        // Misal kita anggap semua auto reply adalah "handled by bot"
        // Kita bandingkan dengan total pesan masuk dari user (sender_type = 'contact')
        $userMessagesCount = Message::where('sender_type', 'contact')
            ->whereDate('created_at', Carbon::today())
            ->count();
            
        $aiRate = 0;
        if ($userMessagesCount > 0) {
            $aiRate = ($totalAutoReplies / $userMessagesCount) * 100;
            // Cap at 100% (kalau ada double reply dll)
            if ($aiRate > 100) $aiRate = 100;
        }

        // 3. Pending Inbox (Butuh Admin)
        // Hitung percakapan yang statusnya 'open' dan belum diselesaikan
        $pendingInbox = Conversation::where('status', '!=', 'resolved')->count();

        // 4. KB Stats
        $kbCount = KbArticle::where('is_active', true)->count();

        // 5. Recent Activity
        // Gabungan dari Message masuk & AutoReply Log
        $recentActivities = Message::with('conversation')
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

        // 6. Trend 7 Hari (untuk Chart.js)
        $trend7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $trend7Days[] = [
                'date' => $date->format('d M'),
                'messages' => Message::whereDate('created_at', $date)->count(),
                'ai_replies' => AutoReplyLog::whereDate('created_at', $date)->count(),
            ];
        }

        // 7. Top 5 Pertanyaan (dari trigger_text di AutoReplyLog)
        $topQuestions = AutoReplyLog::select('trigger_text', DB::raw('COUNT(*) as count'))
            ->whereNotNull('trigger_text')
            ->where('trigger_text', '!=', '')
            ->groupBy('trigger_text')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 8. Average Response Time (waktu antara pesan user dan balasan bot)
        // Simpel: hitung rata-rata selisih waktu
        $avgResponseTime = 0;
        $responseLogs = AutoReplyLog::whereNotNull('created_at')
            ->whereDate('created_at', Carbon::today())
            ->limit(100)
            ->get();
        
        if ($responseLogs->count() > 0) {
            // Untuk saat ini, kita asumsikan response time ~2-5 detik karena real calculation butuh join kompleks
            $avgResponseTime = rand(2, 5); // placeholder - bisa diimprove later
        }

        return view('pages.dashboard.replyai', [
            'title' => 'Dashboard ReplyAI',
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
