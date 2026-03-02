<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\WaMessage;
use App\Models\Message;
use App\Models\WebConversation;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdvancedUserAnalyticsController extends Controller
{
    /**
     * Check authorization - only superadmin can access
     */
    private function checkAuthorization(): void
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role !== 'superadmin') {
            abort(403, 'Unauthorized. Only superadmin can access user analytics.');
        }
    }

    /**
     * Show user activity dashboard with overview
     */
    public function index(Request $request)
    {
        $this->checkAuthorization();

        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);

        // Summary metrics
        $metrics = [
            'total_users' => User::count(),
            'active_users' => User::where('last_login_at', '>=', $startDate)->count(),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'inactive_users' => User::where(function ($q) use ($startDate) {
                $q->whereNull('last_login_at')
                  ->orWhere('last_login_at', '<', $startDate);
            })->count(),
        ];

        // Activity heatmap data (hourly breakdown for last 7 days)
        $activityHeatmap = $this->getActivityHeatmap();

        // Feature usage statistics
        $featureUsage = $this->getFeatureUsage($startDate);

        // Login history (last 50 logins)
        $loginHistory = ActivityLog::where('action', 'login')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Top active users
        $topUsers = User::select('users.*')
            ->selectRaw('COUNT(DISTINCT activity_logs.id) as login_count')
            ->leftJoin('activity_logs', function ($join) use ($startDate) {
                $join->on('users.id', '=', 'activity_logs.user_id')
                     ->where('activity_logs.action', '=', 'login')
                     ->where('activity_logs.created_at', '>=', $startDate);
            })
            ->groupBy('users.id')
            ->orderByDesc('login_count')
            ->limit(10)
            ->get();

        // Session data summary
        $sessionStats = [
            'avg_session_duration' => $this->calculateAvgSessionDuration($startDate),
            'peak_concurrent_users' => $this->getPeakConcurrentUsers($startDate),
        ];

        // Platform usage distribution
        $platformUsage = $this->getPlatformUsage($startDate);

        return view('admin.user-analytics.index', compact(
            'metrics',
            'activityHeatmap',
            'featureUsage',
            'loginHistory',
            'topUsers',
            'sessionStats',
            'platformUsage',
            'period'
        ));
    }

    /**
     * Show detailed analytics for specific user
     */
    public function userDetail($userId)
    {
        $this->checkAuthorization();

        $user = User::with(['subscription.plan', 'whatsappDevices', 'instagramAccounts'])
            ->findOrFail($userId);

        // User activity timeline
        $activityTimeline = ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Feature usage breakdown
        $featureBreakdown = [
            'whatsapp_messages' => WaMessage::whereHas('waConversation', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'instagram_messages' => Message::whereHas('conversation', function ($q) use ($userId) {
                $q->whereHas('instagramAccount', function ($q2) use ($userId) {
                    $q2->where('user_id', $userId);
                });
            })->count(),
            'web_widget_conversations' => WebConversation::where('user_id', $userId)->count(),
            'broadcasts_sent' => \App\Models\WaBroadcast::where('user_id', $userId)->count(),
            'kb_articles' => \App\Models\KbArticle::where('user_id', $userId)->count(),
            'auto_rules' => \App\Models\AutoReplyRule::where('user_id', $userId)->count(),
        ];

        // Daily activity chart (last 30 days)
        $dailyActivity = $this->getUserDailyActivity($userId, 30);

        // Login patterns
        $loginPatterns = $this->getLoginPatterns($userId);

        // Device/session info
        $sessions = \App\Models\Session::where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->limit(10)
            ->get();

        // Engagement score
        $engagementScore = $this->calculateEngagementScore($user);

        return view('admin.user-analytics.show', compact(
            'user',
            'activityTimeline',
            'featureBreakdown',
            'dailyActivity',
            'loginPatterns',
            'sessions',
            'engagementScore'
        ));
    }

    /**
     * Get activity heatmap data
     */
    private function getActivityHeatmap(): array
    {
        $heatmap = [];
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        for ($day = 0; $day < 7; $day++) {
            for ($hour = 0; $hour < 24; $hour++) {
                $count = ActivityLog::where('action', 'login')
                    ->whereRaw('DAYOFWEEK(created_at) = ?', [$day + 1])
                    ->whereRaw('HOUR(created_at) = ?', [$hour])
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();
                
                $heatmap[$days[$day]][$hour] = $count;
            }
        }
        
        return $heatmap;
    }

    /**
     * Get feature usage statistics
     */
    private function getFeatureUsage($startDate): array
    {
        return [
            'whatsapp' => WaMessage::where('created_at', '>=', $startDate)->count(),
            'instagram' => Message::where('created_at', '>=', $startDate)
                ->whereHas('conversation.instagramAccount')
                ->count(),
            'web_widget' => WebConversation::where('created_at', '>=', $startDate)->count(),
            'broadcasts' => \App\Models\WaBroadcast::where('created_at', '>=', $startDate)->count(),
            'kb_articles' => \App\Models\KbArticle::where('created_at', '>=', $startDate)->count(),
            'rules_triggered' => \App\Models\AutoReplyLog::where('created_at', '>=', $startDate)->count(),
        ];
    }

    /**
     * Calculate average session duration
     */
    private function calculateAvgSessionDuration($startDate): ?float
    {
        $avgDuration = ActivityLog::where('created_at', '>=', $startDate)
            ->whereIn('action', ['login', 'logout'])
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, (SELECT MIN(al2.created_at) FROM activity_logs al2 WHERE al2.user_id = activity_logs.user_id AND al2.action = "logout" AND al2.created_at > activity_logs.created_at))) as avg_duration')
            ->where('action', 'login')
            ->value('avg_duration');

        return $avgDuration ?: 0;
    }

    /**
     * Get peak concurrent users
     */
    private function getPeakConcurrentUsers($startDate): int
    {
        // Approximate by counting unique sessions per hour
        return ActivityLog::where('created_at', '>=', $startDate)
            ->where('action', 'login')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H") as hour, COUNT(DISTINCT user_id) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(1)
            ->value('count') ?: 0;
    }

    /**
     * Get platform usage distribution
     */
    private function getPlatformUsage($startDate): array
    {
        return [
            'web' => ActivityLog::where('created_at', '>=', $startDate)
                ->where('metadata->platform', 'web')
                ->count(),
            'mobile' => ActivityLog::where('created_at', '>=', $startDate)
                ->where('metadata->platform', 'mobile')
                ->count(),
            'api' => ActivityLog::where('created_at', '>=', $startDate)
                ->where('metadata->source', 'api')
                ->count(),
        ];
    }

    /**
     * Get user's daily activity for chart
     */
    private function getUserDailyActivity($userId, $days): array
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            
            $activityCount = ActivityLog::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->count();
            
            $messageCount = WaMessage::whereHas('waConversation', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date,
                'activities' => $activityCount,
                'messages' => $messageCount,
            ];
        }
        
        return $data;
    }

    /**
     * Get login patterns for user
     */
    private function getLoginPatterns($userId): array
    {
        $patterns = [
            'by_hour' => [],
            'by_day' => [],
        ];
        
        // By hour
        for ($hour = 0; $hour < 24; $hour++) {
            $patterns['by_hour'][$hour] = ActivityLog::where('user_id', $userId)
                ->where('action', 'login')
                ->whereRaw('HOUR(created_at) = ?', [$hour])
                ->count();
        }
        
        // By day of week
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($days as $index => $day) {
            $patterns['by_day'][$day] = ActivityLog::where('user_id', $userId)
                ->where('action', 'login')
                ->whereRaw('DAYOFWEEK(created_at) = ?', [$index + 1])
                ->count();
        }
        
        return $patterns;
    }

    /**
     * Calculate engagement score (0-100)
     */
    private function calculateEngagementScore(User $user): int
    {
        $score = 0;
        $maxScore = 100;
        
        // Recent login (20 points)
        if ($user->last_login_at) {
            $daysSinceLogin = now()->diffInDays($user->last_login_at);
            if ($daysSinceLogin <= 1) $score += 20;
            elseif ($daysSinceLogin <= 7) $score += 15;
            elseif ($daysSinceLogin <= 30) $score += 10;
            else $score += 5;
        }
        
        // Active subscription (20 points)
        if ($user->subscription && $user->subscription->status === 'active') {
            $score += 20;
        }
        
        // WhatsApp connected (15 points)
        if ($user->whatsappDevices()->where('status', 'connected')->exists()) {
            $score += 15;
        }
        
        // Instagram connected (10 points)
        if ($user->instagramAccounts()->where('is_active', true)->exists()) {
            $score += 10;
        }
        
        // Recent activity (20 points)
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $score += min(20, $recentActivity * 2);
        
        // Feature usage (15 points)
        $hasKb = \App\Models\KbArticle::where('user_id', $user->id)->exists();
        $hasRules = \App\Models\AutoReplyRule::where('user_id', $user->id)->exists();
        $hasBroadcasts = \App\Models\WaBroadcast::where('user_id', $user->id)->exists();
        
        if ($hasKb) $score += 5;
        if ($hasRules) $score += 5;
        if ($hasBroadcasts) $score += 5;
        
        return min($maxScore, $score);
    }

    /**
     * API endpoint for real-time user analytics
     */
    public function realtime()
    {
        $this->checkAuthorization();
        
        return response()->json([
            'users_online' => User::where('last_activity_at', '>=', now()->subMinutes(5))->count(),
            'active_today' => User::where('last_login_at', '>=', now()->startOfDay())->count(),
            'new_today' => User::whereDate('created_at', now())->count(),
            'top_active' => User::where('last_activity_at', '>=', now()->subMinutes(15))
                ->limit(5)
                ->get(['id', 'name', 'email', 'last_activity_at']),
        ]);
    }
}
