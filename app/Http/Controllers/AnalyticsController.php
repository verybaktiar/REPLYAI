<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\WaMessage;
use App\Models\WaConversation;
use App\Models\AutoReplyLog;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $platform = $request->get('platform', 'all');
        $bot = $request->get('bot', 'all');
        $dateRange = $request->get('range', '30'); // days
        
        $startDate = Carbon::now()->subDays((int)$dateRange)->startOfDay();
        
        // === WhatsApp Statistics ===
        $waQuery = WaMessage::where('created_at', '>=', $startDate);
        
        // Unique conversations (distinct phone numbers)
        $waConversations = (clone $waQuery)->distinct('phone_number')->count('phone_number');
        
        // Total messages
        $waTotalMessages = (clone $waQuery)->count();
        $waIncoming = (clone $waQuery)->where('direction', 'incoming')->count();
        $waOutgoing = (clone $waQuery)->where('direction', 'outgoing')->count();
        
        // Bot resolution rate - messages that got bot replies
        $waWithBotReply = (clone $waQuery)->where('direction', 'incoming')->whereNotNull('bot_reply')->where('bot_reply', '!=', '')->count();
        $waBotResolutionRate = $waIncoming > 0 ? round(($waWithBotReply / $waIncoming) * 100, 1) : 0;
        
        // Human handoff (CS takeover)
        $waHandoff = WaConversation::where('status', 'agent_handling')->count();
        $waHandoffRate = $waConversations > 0 ? round(($waHandoff / $waConversations) * 100, 1) : 0;
        
        // Average response time (estimate based on consecutive messages)
        $avgResponseTime = $this->calculateAvgResponseTime('whatsapp', $startDate);
        
        // === Instagram Statistics ===
        $igConversations = Conversation::where('created_at', '>=', $startDate)->count();
        $igTotalMessages = 0; // TODO: Add Instagram message count if available
        
        // Instagram logs for resolution
        $igLogs = AutoReplyLog::where('created_at', '>=', $startDate)->whereNotNull('conversation_id');
        $igTotalLogs = (clone $igLogs)->count();
        $igResolved = (clone $igLogs)->whereIn('status', ['sent', 'sent_ai', 'success'])->count();
        $igResolutionRate = $igTotalLogs > 0 ? round(($igResolved / $igTotalLogs) * 100, 1) : 0;
        
        // === Combined or Filtered Stats ===
        if ($platform === 'whatsapp') {
            $totalConversations = $waConversations;
            $totalMessages = $waTotalMessages;
            $resolutionRate = $waBotResolutionRate;
            $handoffRate = $waHandoffRate;
        } elseif ($platform === 'instagram') {
            $totalConversations = $igConversations;
            $totalMessages = $igTotalMessages;
            $resolutionRate = $igResolutionRate;
            $handoffRate = 0;
        } else {
            $totalConversations = $waConversations + $igConversations;
            $totalMessages = $waTotalMessages + $igTotalMessages;
            // Weighted average for resolution
            $totalIncoming = $waIncoming + $igTotalLogs;
            $totalResolved = $waWithBotReply + $igResolved;
            $resolutionRate = $totalIncoming > 0 ? round(($totalResolved / $totalIncoming) * 100, 1) : 0;
            $handoffRate = $waHandoffRate;
        }
        
        // Platform split for chart
        $totalSources = $waConversations + $igConversations;
        $waPercentage = $totalSources > 0 ? round(($waConversations / $totalSources) * 100) : 50;
        $igPercentage = $totalSources > 0 ? round(($igConversations / $totalSources) * 100) : 50;

        // Daily conversation volume (last 7 days)
        $dailyVolume = $this->getDailyVolume($platform, 7);
        
        // Recent activity logs
        $recentLogs = $this->getRecentActivity($platform, 10);

        return view('pages.analytics.index', [
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'resolutionRate' => $resolutionRate,
            'handoffRate' => $handoffRate,
            'avgResponseTime' => $avgResponseTime,
            'waPercentage' => $waPercentage,
            'igPercentage' => $igPercentage,
            'whatsappCount' => $waConversations,
            'instagramCount' => $igConversations,
            'waMessages' => $waTotalMessages,
            'waIncoming' => $waIncoming,
            'waOutgoing' => $waOutgoing,
            'currentPlatform' => $platform,
            'currentBot' => $bot,
            'dateRange' => $dateRange,
            'recentLogs' => $recentLogs,
            'dailyVolume' => $dailyVolume,
        ]);
    }

    /**
     * Calculate average response time
     */
    private function calculateAvgResponseTime(string $platform, Carbon $startDate): float
    {
        if ($platform === 'whatsapp' || $platform === 'all') {
            // Get pairs of incoming followed by outgoing messages
            $messages = WaMessage::where('created_at', '>=', $startDate)
                ->orderBy('phone_number')
                ->orderBy('created_at')
                ->get(['phone_number', 'direction', 'created_at']);
            
            $responseTimes = [];
            $lastIncoming = [];
            
            foreach ($messages as $msg) {
                if ($msg->direction === 'incoming') {
                    $lastIncoming[$msg->phone_number] = $msg->created_at;
                } elseif ($msg->direction === 'outgoing' && isset($lastIncoming[$msg->phone_number])) {
                    $diff = abs($msg->created_at->diffInSeconds($lastIncoming[$msg->phone_number]));
                    if ($diff < 300) { // Only count if response within 5 minutes
                        $responseTimes[] = $diff;
                    }
                    unset($lastIncoming[$msg->phone_number]);
                }
            }
            
            if (count($responseTimes) > 0) {
                return round(array_sum($responseTimes) / count($responseTimes), 1);
            }
        }
        
        return 0;
    }

    /**
     * Get daily conversation volume
     */
    private function getDailyVolume(string $platform, int $days): array
    {
        $volumes = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();
            
            $waCount = 0;
            $igCount = 0;
            
            if ($platform !== 'instagram') {
                $waCount = WaMessage::where('direction', 'incoming')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();
            }
            
            if ($platform !== 'whatsapp') {
                $igCount = AutoReplyLog::whereNotNull('conversation_id')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();
            }
            
            $volumes[] = [
                'date' => $date->format('M d'),
                'day' => $date->format('D'),
                'whatsapp' => $waCount,
                'instagram' => $igCount,
                'total' => $waCount + $igCount,
            ];
        }
        
        return $volumes;
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(string $platform, int $limit): array
    {
        $activities = [];
        
        // WhatsApp recent messages
        if ($platform !== 'instagram') {
            $waMessages = WaMessage::where('direction', 'incoming')
                ->latest()
                ->limit($limit)
                ->get();
            
            foreach ($waMessages as $msg) {
                $activities[] = [
                    'time' => $msg->created_at,
                    'platform' => 'whatsapp',
                    'phone' => $msg->phone_number,
                    'name' => $msg->push_name ?? 'Unknown',
                    'message' => \Str::limit($msg->message, 50),
                    'has_reply' => !empty($msg->bot_reply),
                    'status' => !empty($msg->bot_reply) ? 'resolved' : 'pending',
                ];
            }
        }
        
        // Instagram recent logs
        if ($platform !== 'whatsapp') {
            $igLogs = AutoReplyLog::with('conversation')
                ->whereNotNull('conversation_id')
                ->latest()
                ->limit($limit)
                ->get();
            
            foreach ($igLogs as $log) {
                $activities[] = [
                    'time' => $log->created_at,
                    'platform' => 'instagram',
                    'phone' => $log->conversation?->instagram_user_id ?? '-',
                    'name' => $log->conversation?->username ?? 'Unknown',
                    'message' => \Str::limit($log->trigger_text, 50),
                    'has_reply' => in_array($log->status, ['sent', 'sent_ai', 'success']),
                    'status' => $log->status,
                ];
            }
        }
        
        // Sort by time and limit
        usort($activities, fn($a, $b) => $b['time'] <=> $a['time']);
        
        return array_slice($activities, 0, $limit);
    }

    /**
     * Export analytics data as CSV
     */
    public function export(Request $request)
    {
        $platform = $request->get('platform', 'all');
        
        // Prepare data
        $waConversations = WaMessage::distinct('phone_number')->count('phone_number');
        $waTotalMessages = WaMessage::count();
        $waIncoming = WaMessage::where('direction', 'incoming')->count();
        $waWithReply = WaMessage::where('direction', 'incoming')->whereNotNull('bot_reply')->where('bot_reply', '!=', '')->count();
        
        $igConversations = Conversation::count();
        $igLogs = AutoReplyLog::whereNotNull('conversation_id')->count();
        $igResolved = AutoReplyLog::whereNotNull('conversation_id')->whereIn('status', ['sent', 'sent_ai', 'success'])->count();
        
        // CSV content
        $csvContent = "Metrik,Nilai\n";
        $csvContent .= "Tanggal Export," . now()->format('Y-m-d H:i:s') . "\n";
        $csvContent .= "\n=== WhatsApp ===\n";
        $csvContent .= "Total Conversations,{$waConversations}\n";
        $csvContent .= "Total Messages,{$waTotalMessages}\n";
        $csvContent .= "Incoming Messages,{$waIncoming}\n";
        $csvContent .= "Bot Replies,{$waWithReply}\n";
        $csvContent .= "Bot Resolution Rate," . ($waIncoming > 0 ? round(($waWithReply / $waIncoming) * 100, 1) : 0) . "%\n";
        $csvContent .= "\n=== Instagram ===\n";
        $csvContent .= "Total Conversations,{$igConversations}\n";
        $csvContent .= "Total Logs,{$igLogs}\n";
        $csvContent .= "Resolved,{$igResolved}\n";
        $csvContent .= "Resolution Rate," . ($igLogs > 0 ? round(($igResolved / $igLogs) * 100, 1) : 0) . "%\n";
        $csvContent .= "\n--- Recent WhatsApp Activity ---\n";
        $csvContent .= "Timestamp,Phone,Name,Message,Bot Reply\n";
        
        $messages = WaMessage::where('direction', 'incoming')->latest()->take(50)->get();
        foreach ($messages as $msg) {
            $message = str_replace(["\n", "\r", ","], " ", $msg->message ?? '-');
            $name = str_replace(["\n", "\r", ","], " ", $msg->push_name ?? '-');
            $hasReply = !empty($msg->bot_reply) ? 'Yes' : 'No';
            $csvContent .= "{$msg->created_at},{$msg->phone_number},{$name},{$message},{$hasReply}\n";
        }

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics_report_' . date('Y-m-d') . '.csv"',
        ]);
    }

}

