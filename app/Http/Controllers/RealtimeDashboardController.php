<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WebConversation;
use App\Models\Message;
use App\Models\WaMessage;
use App\Models\WebMessage;
use App\Models\CsatRating;
use App\Models\User;
use App\Events\DashboardStatsUpdated;
use App\Events\AgentStatusChanged;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RealtimeDashboardController extends Controller
{
    /**
     * Display the realtime dashboard page.
     */
    public function index()
    {
        return view('pages.reports.realtime.index');
    }

    /**
     * Get count of active conversations across all platforms.
     */
    public function getActiveConversations()
    {
        $user = Auth::user();
        $cacheKey = "active_conversations_{$user->id}";

        $stats = Cache::remember($cacheKey, 30, function () use ($user) {
            // WhatsApp active conversations
            $waActive = WaConversation::where('user_id', $user->id)
                ->whereIn('status', [WaConversation::STATUS_BOT_ACTIVE, WaConversation::STATUS_AGENT_HANDLING])
                ->count();

            // Instagram active conversations
            $igActive = Conversation::where('user_id', $user->id)
                ->whereIn('status', ['open', 'bot_handling', 'agent_handling'])
                ->count();

            // Web widget active conversations
            $webActive = WebConversation::where('user_id', $user->id)
                ->whereIn('status', ['active', 'bot', 'cs', 'escalated'])
                ->count();

            return [
                'whatsapp' => $waActive,
                'instagram' => $igActive,
                'web' => $webActive,
                'total' => $waActive + $igActive + $webActive,
                'timestamp' => now()->toIso8601String(),
            ];
        });

        return response()->json([
            'ok' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Get queue length statistics.
     */
    public function getQueueLength()
    {
        $user = Auth::user();
        $cacheKey = "queue_stats_{$user->id}";

        $stats = Cache::remember($cacheKey, 30, function () use ($user) {
            // WhatsApp queue - waiting for agent
            $waWaiting = WaConversation::where('user_id', $user->id)
                ->where('status', WaConversation::STATUS_BOT_ACTIVE)
                ->where(function ($query) {
                    $query->whereNull('last_user_reply_at')
                        ->orWhere('last_user_reply_at', '>=', now()->subMinutes(5));
                })
                ->count();

            $waAgentHandling = WaConversation::where('user_id', $user->id)
                ->where('status', WaConversation::STATUS_AGENT_HANDLING)
                ->count();

            // Instagram queue
            $igWaiting = Conversation::where('user_id', $user->id)
                ->where('status', 'open')
                ->count();

            $igAgentHandling = Conversation::where('user_id', $user->id)
                ->where('status', 'agent_handling')
                ->count();

            // Web queue
            $webWaiting = WebConversation::where('user_id', $user->id)
                ->where('status', 'bot')
                ->count();

            $webAgentHandling = WebConversation::where('user_id', $user->id)
                ->whereIn('status', ['cs', 'escalated'])
                ->count();

            // Average wait time calculation (simplified)
            $avgWaitTime = $this->calculateAverageWaitTime($user->id);

            return [
                'waiting' => [
                    'whatsapp' => $waWaiting,
                    'instagram' => $igWaiting,
                    'web' => $webWaiting,
                    'total' => $waWaiting + $igWaiting + $webWaiting,
                ],
                'agent_handling' => [
                    'whatsapp' => $waAgentHandling,
                    'instagram' => $igAgentHandling,
                    'web' => $webAgentHandling,
                    'total' => $waAgentHandling + $igAgentHandling + $webAgentHandling,
                ],
                'avg_wait_time_seconds' => $avgWaitTime,
                'timestamp' => now()->toIso8601String(),
            ];
        });

        return response()->json([
            'ok' => true,
            'queue' => $stats,
        ]);
    }

    /**
     * Get agent online/offline status.
     */
    public function getAgentStatus()
    {
        $user = Auth::user();

        // For single user accounts, return the current user's status
        // For team accounts, this would query team members
        $agents = Cache::remember("agent_status_{$user->id}", 60, function () use ($user) {
            // Check recent activity to determine online status
            $lastActivity = Cache::get("user_last_activity_{$user->id}");
            $isOnline = $lastActivity && Carbon::parse($lastActivity)->diffInMinutes(now()) < 5;

            // Get assigned conversations count
            $assignedCount = WaConversation::where('user_id', $user->id)
                ->where('status', WaConversation::STATUS_AGENT_HANDLING)
                ->count();

            $igAssignedCount = Conversation::where('user_id', $user->id)
                ->where('status', 'agent_handling')
                ->count();

            return [
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $isOnline ? 'online' : 'offline',
                    'last_seen' => $lastActivity ?? now()->toIso8601String(),
                    'assigned_conversations' => $assignedCount + $igAssignedCount,
                    'is_current_user' => true,
                ],
            ];
        });

        return response()->json([
            'ok' => true,
            'agents' => $agents,
            'summary' => [
                'online' => collect($agents)->where('status', 'online')->count(),
                'offline' => collect($agents)->where('status', 'offline')->count(),
                'total' => count($agents),
            ],
        ]);
    }

    /**
     * Update agent status (online/offline).
     */
    public function updateAgentStatus(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'status' => ['required', 'string', 'in:online,offline,busy,away'],
        ]);

        $status = $request->input('status');

        // Update last activity
        Cache::put("user_last_activity_{$user->id}", now()->toIso8601String(), 300);

        // Store current status
        Cache::put("user_status_{$user->id}", $status, 3600);

        // Broadcast status change
        broadcast(new AgentStatusChanged($user->id, $status))->toOthers();

        ActivityLogService::log(
            'agent_status_changed',
            "Status agent diubah menjadi: {$status}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Status updated',
            'status' => $status,
        ]);
    }

    /**
     * Get real-time sentiment trend.
     */
    public function getSentimentTrend()
    {
        $user = Auth::user();
        $cacheKey = "sentiment_trend_{$user->id}";

        $trend = Cache::remember($cacheKey, 60, function () use ($user) {
            $timeRanges = [
                'last_1h' => now()->subHour(),
                'last_6h' => now()->subHours(6),
                'last_24h' => now()->subDay(),
            ];

            $result = [];

            foreach ($timeRanges as $label => $since) {
                // WhatsApp sentiment
                $waSentiment = WaConversation::where('user_id', $user->id)
                    ->whereNotNull('ai_sentiment')
                    ->where('ai_analyzed_at', '>=', $since)
                    ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                    ->groupBy('ai_sentiment')
                    ->pluck('count', 'ai_sentiment')
                    ->toArray();

                // Instagram sentiment
                $igSentiment = Conversation::where('user_id', $user->id)
                    ->whereNotNull('ai_sentiment')
                    ->where('ai_analyzed_at', '>=', $since)
                    ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                    ->groupBy('ai_sentiment')
                    ->pluck('count', 'ai_sentiment')
                    ->toArray();

                // Combine
                $combined = [
                    'positive' => ($waSentiment['positive'] ?? 0) + ($igSentiment['positive'] ?? 0),
                    'neutral' => ($waSentiment['neutral'] ?? 0) + ($igSentiment['neutral'] ?? 0),
                    'negative' => ($waSentiment['negative'] ?? 0) + ($igSentiment['negative'] ?? 0),
                ];

                $total = array_sum($combined);

                $result[$label] = [
                    'counts' => $combined,
                    'percentages' => $total > 0 ? [
                        'positive' => round(($combined['positive'] / $total) * 100, 1),
                        'neutral' => round(($combined['neutral'] / $total) * 100, 1),
                        'negative' => round(($combined['negative'] / $total) * 100, 1),
                    ] : [
                        'positive' => 0,
                        'neutral' => 0,
                        'negative' => 0,
                    ],
                    'total' => $total,
                ];
            }

            // Get hourly sentiment for chart
            $hourlyData = [];
            for ($i = 23; $i >= 0; $i--) {
                $hour = now()->subHours($i);
                $waHourly = WaConversation::where('user_id', $user->id)
                    ->whereNotNull('ai_sentiment')
                    ->whereBetween('ai_analyzed_at', [$hour->copy()->startOfHour(), $hour->copy()->endOfHour()])
                    ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                    ->groupBy('ai_sentiment')
                    ->pluck('count', 'ai_sentiment')
                    ->toArray();

                $igHourly = Conversation::where('user_id', $user->id)
                    ->whereNotNull('ai_sentiment')
                    ->whereBetween('ai_analyzed_at', [$hour->copy()->startOfHour(), $hour->copy()->endOfHour()])
                    ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                    ->groupBy('ai_sentiment')
                    ->pluck('count', 'ai_sentiment')
                    ->toArray();

                $hourlyData[] = [
                    'hour' => $hour->format('H:i'),
                    'positive' => ($waHourly['positive'] ?? 0) + ($igHourly['positive'] ?? 0),
                    'neutral' => ($waHourly['neutral'] ?? 0) + ($igHourly['neutral'] ?? 0),
                    'negative' => ($waHourly['negative'] ?? 0) + ($igHourly['negative'] ?? 0),
                ];
            }

            return [
                'summary' => $result,
                'hourly' => $hourlyData,
                'timestamp' => now()->toIso8601String(),
            ];
        });

        return response()->json([
            'ok' => true,
            'sentiment' => $trend,
        ]);
    }

    /**
     * Get recent activity feed.
     */
    public function getRecentActivity()
    {
        $user = Auth::user();
        $limit = request()->input('limit', 20);

        // WhatsApp recent messages
        $waMessages = WaMessage::where('user_id', $user->id)
            ->where('direction', 'incoming')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => 'wa_' . $msg->id,
                    'platform' => 'whatsapp',
                    'type' => 'message',
                    'contact' => [
                        'name' => $msg->push_name ?? 'Unknown',
                        'identifier' => $msg->phone_number,
                    ],
                    'content' => \Str::limit($msg->message, 100),
                    'has_bot_reply' => !empty($msg->bot_reply),
                    'sentiment' => $msg->conversation?->ai_sentiment ?? null,
                    'timestamp' => $msg->created_at->toIso8601String(),
                    'time_ago' => $msg->created_at->diffForHumans(),
                ];
            });

        // Instagram recent messages
        $igMessages = Message::whereHas('conversation', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('sender_type', 'contact')
            ->latest()
            ->limit($limit)
            ->with('conversation')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => 'ig_' . $msg->id,
                    'platform' => 'instagram',
                    'type' => 'message',
                    'contact' => [
                        'name' => $msg->conversation?->display_name ?? 'Unknown',
                        'identifier' => $msg->conversation?->instagram_user_id,
                    ],
                    'content' => \Str::limit($msg->content, 100),
                    'has_bot_reply' => $msg->is_replied_by_bot,
                    'sentiment' => $msg->conversation?->ai_sentiment ?? null,
                    'timestamp' => $msg->created_at->toIso8601String(),
                    'time_ago' => $msg->created_at->diffForHumans(),
                ];
            });

        // Web widget recent messages
        $webMessages = WebMessage::whereHas('conversation', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('direction', 'incoming')
            ->latest()
            ->limit($limit)
            ->with('conversation')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => 'web_' . $msg->id,
                    'platform' => 'web',
                    'type' => 'message',
                    'contact' => [
                        'name' => $msg->conversation?->visitor_name ?? 'Visitor',
                        'identifier' => $msg->conversation?->visitor_id,
                    ],
                    'content' => \Str::limit($msg->content, 100),
                    'has_bot_reply' => $msg->is_bot_reply ?? false,
                    'sentiment' => $msg->conversation?->ai_sentiment ?? null,
                    'timestamp' => $msg->created_at->toIso8601String(),
                    'time_ago' => $msg->created_at->diffForHumans(),
                ];
            });

        // Merge and sort by timestamp
        $activities = $waMessages
            ->merge($igMessages)
            ->merge($webMessages)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();

        return response()->json([
            'ok' => true,
            'activities' => $activities,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get dashboard overview stats (for initial load).
     */
    public function getOverview()
    {
        $user = Auth::user();

        // Clear caches to get fresh data
        Cache::forget("active_conversations_{$user->id}");
        Cache::forget("queue_stats_{$user->id}");

        $activeConversations = $this->getActiveConversations()->getData()->stats;
        $queueStats = $this->getQueueLength()->getData()->queue;
        $sentiment = $this->getSentimentTrend()->getData()->sentiment;
        $recentActivity = $this->getRecentActivity()->getData()->activities;

        return response()->json([
            'ok' => true,
            'overview' => [
                'active_conversations' => $activeConversations,
                'queue' => $queueStats,
                'sentiment' => $sentiment->summary->last_24h ?? null,
                'recent_activity_count' => count($recentActivity),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast dashboard stats update.
     */
    public function broadcastStatsUpdate()
    {
        $user = Auth::user();

        // Get fresh stats
        $activeConversations = $this->getActiveConversations()->getData()->stats;

        // Broadcast to user's private channel
        broadcast(new DashboardStatsUpdated($user->id, [
            'active_conversations' => $activeConversations,
            'timestamp' => now()->toIso8601String(),
        ]));

        return response()->json([
            'ok' => true,
            'message' => 'Stats update broadcasted',
        ]);
    }

    /**
     * Calculate average wait time.
     */
    protected function calculateAverageWaitTime(int $userId): float
    {
        // Simplified calculation - average time between customer message and first response
        $waWaitTimes = WaMessage::where('user_id', $userId)
            ->where('direction', 'incoming')
            ->whereNotNull('bot_reply')
            ->where('created_at', '>=', now()->subHours(24))
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_wait')
            ->value('avg_wait');

        return round($waWaitTimes ?? 0, 1);
    }

    /**
     * Ping endpoint to keep session alive.
     */
    public function ping()
    {
        $user = Auth::user();

        // Update last activity
        Cache::put("user_last_activity_{$user->id}", now()->toIso8601String(), 300);

        return response()->json([
            'ok' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
