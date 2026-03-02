<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\WaMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    /**
     * Main analytics dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        // Key Metrics
        $metrics = [
            'total_users' => User::count(),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'active_users' => User::where('last_login_at', '>=', $startDate)->count(),
            'churned_users' => $this->getChurnedUsers($startDate),
            'mrr' => $this->calculateMRR(),
            'arr' => $this->calculateMRR() * 12,
            'avg_revenue_per_user' => $this->calculateARPU(),
            'conversion_rate' => $this->calculateConversionRate($startDate),
        ];

        // Charts Data
        $userGrowth = $this->getUserGrowthData($period);
        $revenueData = $this->getRevenueData($period);
        $churnData = $this->getChurnData($period);

        // Cohort Analysis
        $cohorts = $this->getCohortAnalysis();

        // Feature Usage
        $featureUsage = $this->getFeatureUsage($startDate);

        // Support Metrics
        $supportMetrics = [
            'avg_response_time' => $this->calculateAvgResponseTime($startDate),
            'avg_resolution_time' => $this->calculateAvgResolutionTime($startDate),
            'csat_score' => $this->calculateCSAT($startDate),
            'tickets_by_category' => $this->getTicketsByCategory($startDate),
        ];

        return view('admin.analytics.index', compact(
            'metrics',
            'userGrowth',
            'revenueData',
            'churnData',
            'cohorts',
            'featureUsage',
            'supportMetrics',
            'period'
        ));
    }

    /**
     * Get user growth data for charts
     */
    private function getUserGrowthData(int $days): array
    {
        $data = [];
        $cumulative = 0;

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $newUsers = User::whereDate('created_at', $date)->count();
            $cumulative += $newUsers;
            
            $data[] = [
                'date' => $date,
                'new' => $newUsers,
                'cumulative' => $cumulative,
            ];
        }

        return $data;
    }

    /**
     * Get revenue data
     */
    private function getRevenueData(int $days): array
    {
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $revenue = Payment::whereDate('paid_at', $date)
                ->where('status', 'paid')
                ->sum('amount');
            
            $refunds = Payment::whereDate('updated_at', $date)
                ->where('status', 'refunded')
                ->sum('amount');

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'revenue' => $revenue,
                'refunds' => $refunds,
                'net' => $revenue - $refunds,
            ];
        }

        return $data;
    }

    /**
     * Calculate MRR (Monthly Recurring Revenue)
     */
    private function calculateMRR(): float
    {
        // Simplified MRR calculation using plan's monthly price
        return Subscription::where('status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly') ?? 0;
    }

    /**
     * Calculate ARPU (Average Revenue Per User)
     */
    private function calculateARPU(): float
    {
        $totalRevenue = Payment::where('status', 'paid')
            ->sum('amount');
        
        $totalUsers = User::count();
        
        return $totalUsers > 0 ? $totalRevenue / $totalUsers : 0;
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate($startDate): float
    {
        $visitors = User::where('created_at', '>=', $startDate)->count();
        $subscribers = Subscription::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count('user_id');
        
        return $visitors > 0 ? ($subscribers / $visitors) * 100 : 0;
    }

    /**
     * Get churned users
     */
    private function getChurnedUsers($startDate): int
    {
        return User::whereHas('subscription', function($q) use ($startDate) {
            $q->where('status', 'cancelled')
              ->where('updated_at', '>=', $startDate);
        })->count();
    }

    /**
     * Get churn data for charts
     */
    private function getChurnData(int $days): array
    {
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            
            $churned = Subscription::whereDate('updated_at', $date)
                ->where('status', 'cancelled')
                ->count();
            
            $total = Subscription::whereDate('created_at', '<=', $date)
                ->count();
            
            $data[] = [
                'date' => $date,
                'churned' => $churned,
                'rate' => $total > 0 ? round(($churned / $total) * 100, 2) : 0,
            ];
        }

        return $data;
    }

    /**
     * Cohort Analysis - Retention by signup month
     */
    private function getCohortAnalysis(): array
    {
        $cohorts = [];
        
        // Get users who signed up in last 6 months
        $signupMonths = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('month');

        foreach ($signupMonths as $month) {
            $cohortUsers = User::whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$month])->pluck('id');
            $cohortSize = $cohortUsers->count();
            
            if ($cohortSize === 0) continue;

            $retention = [];
            for ($i = 0; $i <= 5; $i++) {
                $checkDate = Carbon::createFromFormat('Y-m', $month)->addMonths($i);
                if ($checkDate > now()) break;
                
                $activeUsers = User::whereIn('id', $cohortUsers)
                    ->where('last_login_at', '>=', $checkDate->startOfMonth())
                    ->count();
                
                $retention[] = [
                    'month' => $i,
                    'retention' => round(($activeUsers / $cohortSize) * 100, 1),
                ];
            }

            $cohorts[] = [
                'month' => $month,
                'size' => $cohortSize,
                'retention' => $retention,
            ];
        }

        return $cohorts;
    }

    /**
     * Feature usage analytics
     */
    private function getFeatureUsage($startDate): array
    {
        return [
            'ai_messages' => WaMessage::whereNotNull('bot_reply')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'broadcasts' => \App\Models\WaBroadcast::where('created_at', '>=', $startDate)->count(),
            'kb_articles' => \App\Models\KbArticle::where('created_at', '>=', $startDate)->count(),
            'auto_rules' => \App\Models\AutoReplyRule::count(),
            'active_wa' => \App\Models\WhatsAppDevice::where('status', 'connected')->count(),
        ];
    }

    /**
     * Calculate average response time
     */
    private function calculateAvgResponseTime($startDate): ?float
    {
        return SupportTicket::whereNotNull('first_response_at')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as avg_time')
            ->value('avg_time');
    }

    /**
     * Calculate average resolution time
     */
    private function calculateAvgResolutionTime($startDate): ?float
    {
        return SupportTicket::whereNotNull('resolved_at')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_time')
            ->value('avg_time');
    }

    /**
     * Calculate CSAT score
     */
    private function calculateCSAT($startDate): ?float
    {
        return SupportTicket::whereNotNull('rating')
            ->where('created_at', '>=', $startDate)
            ->avg('rating');
    }

    /**
     * Get tickets by category
     */
    private function getTicketsByCategory($startDate): array
    {
        return SupportTicket::where('created_at', '>=', $startDate)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * API endpoint for real-time data
     */
    public function realtime()
    {
        return response()->json([
            'users_online' => User::where('last_activity_at', '>=', now()->subMinutes(5))->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'open_tickets' => SupportTicket::where('status', 'open')->count(),
        ]);
    }
}
