<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\AutoReplyLog;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Total Conversations
        $totalConversations = Conversation::count();
        
        // 2. Stats from Logs
        $logs = AutoReplyLog::query();
        $totalLogs = $logs->count();
        
        // Resolution Rate (Resolved by Bot)
        // Asumsi: status 'success' dianggap resolved oleh bot, 'handoff' atau 'failed' tidak.
        // Jika tidak ada data log, default 0
        $resolvedLogs = AutoReplyLog::where('status', 'success')->count();
        $resolutionRate = $totalLogs > 0 ? round(($resolvedLogs / $totalLogs) * 100, 1) : 0;
        
        // Human Handoff (Status 'fallback' atau 'no_match' seringkali butuh handoff, atau status eksplisit 'handoff')
        // Disini kita pakai 'fallback' dan 'no_match' sebagai proxy 'Human Handoff' potential
        $handoffCount = AutoReplyLog::whereIn('status', ['fallback', 'no_match'])->count();
        $handoffRate = $totalLogs > 0 ? round(($handoffCount / $totalLogs) * 100, 1) : 0;
        
        // 3. Platform Split (Based on Conversation source/platform)
        // Asumsi kolom 'source' ada di Conversation, atau kita ambil dari log
        $whatsappCount = Conversation::where('source', 'like', '%whatsapp%')->count();
        $instagramCount = Conversation::where('source', 'like', '%instagram%')->count();
        $totalSources = $whatsappCount + $instagramCount;
        
        $waPercentage = $totalSources > 0 ? round(($whatsappCount / $totalSources) * 100) : 0;
        $igPercentage = $totalSources > 0 ? round(($instagramCount / $totalSources) * 100) : 0;

        return view('pages.analytics.index', [
            'totalConversations' => $totalConversations,
            'resolutionRate' => $resolutionRate,
            'handoffRate' => $handoffRate,
            'waPercentage' => $waPercentage,
            'igPercentage' => $igPercentage,
            'whatsappCount' => $whatsappCount,
            'instagramCount' => $instagramCount,
            // Pass recent logs for the table
            'recentLogs' => AutoReplyLog::with('conversation')->latest()->take(5)->get()
        ]);
    }
}
