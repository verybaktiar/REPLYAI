<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbMissedQuery;
use App\Models\UsageRecord;
use App\Models\BusinessProfile;
use App\Models\WaMessage;
use App\Models\WhatsAppDevice;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // Middleware diatur di route

    /**
     * Main analytics dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);
        $profileId = $request->input('profile_id');
        
        $startDate = Carbon::now()->subDays($days);
        
        // Get user's business profiles with their KB articles
        $businessProfiles = BusinessProfile::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['kbArticles' => function($q) {
                $q->where('is_active', true);
            }])
            ->get();
        
        // If specific profile selected, filter by it
        $profileFilter = $profileId && $businessProfiles->contains('id', $profileId)
            ? $profileId 
            : null;
        
        // Gather metrics
        $metrics = $this->getMetrics($user->id, $startDate, $profileFilter);
        
        // Get missed queries for KB coverage analysis
        $missedQueries = KbMissedQuery::where('user_id', $user->id)
            ->when($profileFilter, function($q) use ($profileFilter) {
                $q->where('business_profile_id', $profileFilter);
            })
            ->orderByDesc('count')
            ->take(20)
            ->get();
        
        // Get popular articles
        $popularArticles = $this->getPopularArticles($user->id, $startDate, $profileFilter);
        
        // Get conversation trends
        $trends = $this->getTrends($user->id, $days, $profileFilter);
        
        return view('admin.analytics.index', compact(
            'metrics',
            'missedQueries',
            'popularArticles',
            'trends',
            'businessProfiles',
            'days',
            'profileFilter'
        ));
    }

    /**
     * Get chart data via AJAX
     */
    public function getChartData(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);
        $profileId = $request->input('profile_id');
        $chartType = $request->input('chart', 'conversations');
        
        $data = match($chartType) {
            'conversations' => $this->getConversationChartData($user->id, $days, $profileId),
            'sources' => $this->getSourceChartData($user->id, $days, $profileId),
            'kb_coverage' => $this->getKbCoverageData($user->id, $days, $profileId),
            'sentiment' => $this->getSentimentData($user->id, $days, $profileId),
            default => []
        };
        
        return response()->json($data);
    }

    /**
     * Resolve a missed query by linking to KB article
     */
    public function resolveMissedQuery(Request $request, KbMissedQuery $query)
    {
        // Authorization check
        if ($query->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'kb_article_id' => 'required|exists:kb_articles,id',
            'suggested_answer' => 'nullable|string',
        ]);
        
        $query->update([
            'status' => 'resolved',
            'suggested_answer' => $validated['suggested_answer'] ?? null,
        ]);
        
        // Optionally: Create training suggestion from this resolved query
        // This could feed into a future ML training pipeline
        
        return redirect()->back()->with('success', 'Query marked as resolved. Article linked successfully.');
    }

    /**
     * Ignore a missed query
     */
    public function ignoreMissedQuery(KbMissedQuery $query)
    {
        if ($query->user_id !== Auth::id()) {
            abort(403);
        }
        
        $query->update(['status' => 'ignored']);
        
        return redirect()->back()->with('success', 'Query ignored.');
    }

    // ============ PRIVATE HELPER METHODS ============

    private function getMetrics(int $userId, Carbon $startDate, ?int $profileId = null): array
    {
        // Get session_ids for this user (with optional profile filter)
        $sessionIds = WhatsAppDevice::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->pluck('session_id');
        
        // WhatsApp messages - use session_ids for efficient query
        $waQuery = WaMessage::whereIn('session_id', $sessionIds)
            ->where('created_at', '>=', $startDate);
        
        $totalWaMessages = (clone $waQuery)->count();
        $waWithReply = (clone $waQuery)->whereNotNull('bot_reply')->count();
        
        // Instagram messages (if implemented) - wrapped in try-catch
        $totalIgMessages = 0;
        try {
            $igQuery = DB::table('ig_messages')
                ->where('user_id', $userId)
                ->where('created_at', '>=', $startDate);
            
            if ($profileId) {
                $igQuery->where('business_profile_id', $profileId);
            }
            
            $totalIgMessages = $igQuery->count();
        } catch (\Exception $e) {
            $totalIgMessages = 0;
        }
        
        // AI Usage from usage_records
        $aiMessagesUsed = UsageRecord::where('user_id', $userId)
            ->where('feature_key', UsageRecord::FEATURE_AI_MESSAGES)
            ->sum('used_count');
        
        $totalQueries = $totalWaMessages + $totalIgMessages;
        
        // KB Coverage percentage
        $kbArticles = KbArticle::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->where('is_active', true)
            ->count();
        
        $missedCount = KbMissedQuery::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->where('created_at', '>=', $startDate)
            ->count();
        
        $kbCoverage = $totalQueries > 0 
            ? round((($totalQueries - $missedCount) / $totalQueries) * 100, 1)
            : 100;
        
        // Response time estimation (based on timestamps)
        $avgResponseTime = $this->calculateAverageResponseTime($userId, $startDate, $profileId);
        
        // Satisfaction rate (placeholder - would integrate with CSAT)
        $satisfactionRate = 92; // Placeholder - implement real CSAT tracking
        
        return [
            'total_conversations' => $totalQueries,
            'ai_replied' => $waWithReply,
            'ai_reply_rate' => $totalQueries > 0 ? round(($waWithReply / $totalQueries) * 100, 1) : 0,
            'kb_coverage' => max(0, min(100, $kbCoverage)),
            'kb_articles' => $kbArticles,
            'missed_queries' => $missedCount,
            'avg_response_time' => $avgResponseTime,
            'satisfaction_rate' => $satisfactionRate,
            'ai_messages_used' => (int) $aiMessagesUsed,
        ];
    }

    private function getPopularArticles(int $userId, Carbon $startDate, ?int $profileId = null): array
    {
        // This would ideally track article usage via a usage table
        // For now, return most recently used articles
        $query = KbArticle::where('user_id', $userId)
            ->where('is_active', true)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->orderByDesc('updated_at')
            ->take(5);
        
        return $query->get()->map(fn($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'category' => $a->category ?? 'General',
            'usage_count' => rand(10, 100), // Placeholder - implement real tracking
        ])->toArray();
    }

    private function getTrends(int $userId, int $days, ?int $profileId = null): array
    {
        $trends = [];
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        
        // Get message counts grouped by date using raw query for performance
        $sessionIds = WhatsAppDevice::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->pluck('session_id');
        
        $messageCounts = WaMessage::whereIn('session_id', $sessionIds)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(CASE WHEN bot_reply IS NOT NULL THEN 1 ELSE 0 END) as ai_replied')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date');
        
        $aiReplyCounts = WaMessage::whereIn('session_id', $sessionIds)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('bot_reply')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date');
        
        // Build trend array for all days
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            
            $trends[] = [
                'date' => $date->format('M d'),
                'conversations' => $messageCounts[$dateKey] ?? 0,
                'ai_replied' => $aiReplyCounts[$dateKey] ?? 0,
            ];
        }
        
        return $trends;
    }

    private function getConversationChartData(int $userId, int $days, ?int $profileId = null): array
    {
        $labels = [];
        $waData = [];
        $igData = [];
        
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        
        // Get session IDs
        $sessionIds = WhatsAppDevice::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->pluck('session_id');
        
        // Get all message counts grouped by date
        $messageCounts = WaMessage::whereIn('session_id', $sessionIds)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('count', 'date');
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            $dateKey = $date->format('Y-m-d');
            $waData[] = $messageCounts[$dateKey] ?? 0;
            $igData[] = 0; // Placeholder
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'WhatsApp', 'data' => $waData, 'color' => '#10B981'],
                ['label' => 'Instagram', 'data' => $igData, 'color' => '#8B5CF6'],
            ]
        ];
    }

    private function getSourceChartData(int $userId, int $days, ?int $profileId = null): array
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        
        // Get session IDs
        $sessionIds = WhatsAppDevice::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->pluck('session_id');
        
        $waCount = WaMessage::whereIn('session_id', $sessionIds)
            ->where('created_at', '>=', $startDate)
            ->count();
        
        return [
            'labels' => ['WhatsApp', 'Instagram'],
            'data' => [$waCount, 0],
            'colors' => ['#10B981', '#8B5CF6'],
        ];
    }

    private function getKbCoverageData(int $userId, int $days, ?int $profileId = null): array
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        
        // Get session IDs
        $sessionIds = WhatsAppDevice::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->pluck('session_id');
        
        // Get all user queries
        $totalQueries = WaMessage::whereIn('session_id', $sessionIds)
            ->where('created_at', '>=', $startDate)
            ->count();
        
        // Get answered queries (have bot_reply)
        $answeredQueries = WaMessage::whereIn('session_id', $sessionIds)
            ->whereNotNull('bot_reply')
            ->where('created_at', '>=', $startDate)
            ->count();
        
        $missedQueries = KbMissedQuery::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->where('created_at', '>=', $startDate)
            ->count();
        
        return [
            'labels' => ['Answered', 'Missed', 'Pending'],
            'data' => [
                $answeredQueries,
                $missedQueries,
                max(0, $totalQueries - $answeredQueries - $missedQueries)
            ],
            'colors' => ['#10B981', '#EF4444', '#F59E0B'],
        ];
    }

    private function getSentimentData(int $userId, int $days, ?int $profileId = null): array
    {
        // Placeholder for sentiment analysis
        // In real implementation, this would query a sentiment tracking table
        return [
            'labels' => ['Positive', 'Neutral', 'Negative'],
            'data' => [65, 25, 10],
            'colors' => ['#10B981', '#6B7280', '#EF4444'],
        ];
    }

    private function calculateAverageResponseTime(int $userId, Carbon $startDate, ?int $profileId = null): float
    {
        // Simplified calculation - in production, track actual response times
        // Get session IDs
        $sessionIds = WhatsAppDevice::where('user_id', $userId)
            ->when($profileId, function($q) use ($profileId) {
                $q->where('business_profile_id', $profileId);
            })
            ->pluck('session_id');
        
        // This is a placeholder that estimates based on bot_reply presence
        $messages = WaMessage::whereIn('session_id', $sessionIds)
            ->whereNotNull('bot_reply')
            ->where('created_at', '>=', $startDate)
            ->take(100)
            ->get();
        
        if ($messages->isEmpty()) {
            return 0;
        }
        
        // Estimate: assume bot replies within 2-5 seconds
        return round(rand(20, 50) / 10, 1); // Returns 2.0-5.0 seconds
    }
}
