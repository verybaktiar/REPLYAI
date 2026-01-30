<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\WaMessage;
use App\Models\WaConversation;
use App\Models\AutoReplyLog;
use App\Models\UsageRecord; // Added this line
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        // Get filter parameters
        $platform = $request->get('platform', 'all');
        $dateRange = $request->get('range', '30'); // days
        $startDate = Carbon::now()->subDays((int)$dateRange)->startOfDay();
        
        // === 1. Usage Metric from UsageRecord ===
        // AI Messages
        $aiMessagesUsed = UsageRecord::getUsage($user->id, UsageRecord::FEATURE_AI_MESSAGES);
        $plan = $user->getPlan();
        $aiMessagesLimit = $plan?->limits['ai_messages_monthly'] ?? 500;
        
        // Broadcasts
        $broadcastsUsed = UsageRecord::getUsage($user->id, UsageRecord::FEATURE_BROADCASTS);
        $broadcastsLimit = $plan?->limits['broadcasts_monthly'] ?? 5;
        
        // Contacts
        $totalContacts = WaConversation::count(); // Automatically scoped by user_id
        $contactsLimit = $plan?->limits['contacts'] ?? 1000;

        // === 2. Message Statistics ===
        $waQuery = WaMessage::where('created_at', '>=', $startDate);
        $totalMessages = (clone $waQuery)->count();
        $incomingCount = (clone $waQuery)->where('direction', 'incoming')->count();
        $outgoingCount = (clone $waQuery)->where('direction', 'outgoing')->count();
        
        // Bot resolution rate
        $resolvedByBot = (clone $waQuery)->where('direction', 'incoming')
            ->whereNotNull('bot_reply')
            ->where('bot_reply', '!=', '')
            ->count();
        $resolutionRate = $incomingCount > 0 ? round(($resolvedByBot / $incomingCount) * 100, 1) : 0;
        
        // Human handoff
        $handoffCount = WaConversation::where('status', WaConversation::STATUS_AGENT_HANDLING)->count();
        $handoffRate = $totalContacts > 0 ? round(($handoffCount / $totalContacts) * 100, 1) : 0;
        
        // Average response time
        $avgResponseTime = $this->calculateAvgResponseTime('whatsapp', $startDate);

        // === 3. Chart Data ===
        $dailyVolume = $this->getDailyVolume($platform, 7);
        
        // Recent activity
        $recentLogs = $this->getRecentActivity($platform, 10);

        return view('pages.analytics.index', [
            // Summary Cards
            'aiMessagesUsed' => $aiMessagesUsed,
            'aiMessagesLimit' => $aiMessagesLimit,
            'totalContacts' => $totalContacts,
            'contactsLimit' => $contactsLimit,
            'broadcastsUsed' => $broadcastsUsed,
            'broadcastsLimit' => $broadcastsLimit,
            
            // Detailed Stats
            'totalMessages' => $totalMessages,
            'incomingCount' => $incomingCount,
            'outgoingCount' => $outgoingCount,
            'resolutionRate' => $resolutionRate,
            'handoffRate' => $handoffRate,
            'avgResponseTime' => $avgResponseTime,
            
            // Filters & Extras
            'currentPlatform' => $platform,
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

