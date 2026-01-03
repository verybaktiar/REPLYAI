<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\WaMessage;
use App\Models\AutoReplyLog;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $platform = $request->get('platform', 'all');
        $bot = $request->get('bot', 'all');
        
        // 1. Total Conversations - include WhatsApp
        $waConversations = WaMessage::distinct('phone_number')->count('phone_number');
        $igConversations = Conversation::count();
        
        // Apply platform filter
        if ($platform === 'whatsapp') {
            $totalConversations = $waConversations;
        } elseif ($platform === 'instagram') {
            $totalConversations = $igConversations;
        } else {
            $totalConversations = $waConversations + $igConversations;
        }
        
        // 2. Stats from Logs
        $logsQuery = AutoReplyLog::query();
        
        // Apply platform filter to logs if possible
        if ($platform === 'whatsapp') {
            // WhatsApp logs don't have conversation_id typically, so we might skip
            $logsQuery->whereNull('conversation_id');
        } elseif ($platform === 'instagram') {
            $logsQuery->whereNotNull('conversation_id');
        }
        
        $totalLogs = $logsQuery->count();
        
        // Resolution Rate (Resolved by Bot)
        $resolvedLogs = (clone $logsQuery)->whereIn('status', ['sent', 'sent_ai', 'success'])->count();
        $resolutionRate = $totalLogs > 0 ? round(($resolvedLogs / $totalLogs) * 100, 1) : 0;
        
        // Human Handoff
        $handoffCount = (clone $logsQuery)->whereIn('status', ['fallback', 'sent_fallback', 'no_match'])->count();
        $handoffRate = $totalLogs > 0 ? round(($handoffCount / $totalLogs) * 100, 1) : 0;
        
        // 3. Platform Split
        $totalSources = $waConversations + $igConversations;
        $waPercentage = $totalSources > 0 ? round(($waConversations / $totalSources) * 100) : 50;
        $igPercentage = $totalSources > 0 ? round(($igConversations / $totalSources) * 100) : 50;

        // Recent logs for table
        $recentLogsQuery = AutoReplyLog::with('conversation')->latest();
        if ($platform === 'instagram') {
            $recentLogsQuery->whereNotNull('conversation_id');
        }
        
        return view('pages.analytics.index', [
            'totalConversations' => $totalConversations,
            'resolutionRate' => $resolutionRate,
            'handoffRate' => $handoffRate,
            'waPercentage' => $waPercentage,
            'igPercentage' => $igPercentage,
            'whatsappCount' => $waConversations,
            'instagramCount' => $igConversations,
            'currentPlatform' => $platform,
            'currentBot' => $bot,
            'recentLogs' => $recentLogsQuery->take(10)->get()
        ]);
    }

    /**
     * Export analytics data as CSV
     */
    public function export(Request $request)
    {
        $platform = $request->get('platform', 'all');
        
        // Prepare data
        $waConversations = WaMessage::distinct('phone_number')->count('phone_number');
        $igConversations = Conversation::count();
        $totalLogs = AutoReplyLog::count();
        $resolvedLogs = AutoReplyLog::whereIn('status', ['sent', 'sent_ai', 'success'])->count();
        $handoffCount = AutoReplyLog::whereIn('status', ['fallback', 'sent_fallback', 'no_match'])->count();
        
        // CSV content
        $csvContent = "Metrik,Nilai\n";
        $csvContent .= "Tanggal Export," . now()->format('Y-m-d H:i:s') . "\n";
        $csvContent .= "Total Conversations WhatsApp,{$waConversations}\n";
        $csvContent .= "Total Conversations Instagram,{$igConversations}\n";
        $csvContent .= "Total Conversations," . ($waConversations + $igConversations) . "\n";
        $csvContent .= "Bot Resolution Rate," . ($totalLogs > 0 ? round(($resolvedLogs / $totalLogs) * 100, 1) : 0) . "%\n";
        $csvContent .= "Human Handoff Rate," . ($totalLogs > 0 ? round(($handoffCount / $totalLogs) * 100, 1) : 0) . "%\n";
        $csvContent .= "\n--- Recent Logs ---\n";
        $csvContent .= "Timestamp,Trigger,Platform,Status\n";
        
        $logs = AutoReplyLog::with('conversation')->latest()->take(50)->get();
        foreach ($logs as $log) {
            $platform = $log->conversation ? 'Instagram' : 'WhatsApp';
            $trigger = str_replace(["\n", "\r", ","], " ", $log->trigger_text ?? '-');
            $csvContent .= "{$log->created_at},{$trigger},{$platform},{$log->status}\n";
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics_report_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
