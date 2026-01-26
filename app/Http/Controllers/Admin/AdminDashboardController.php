<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Plan;
use App\Models\WaMessage;
use App\Models\AutoReplyLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Core Stats
        $stats = [
            'total_users' => User::count(),
            'vip_users' => User::where('is_vip', true)->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'active_subs' => Subscription::where('status', 'active')->count(),
            'open_tickets' => SupportTicket::whereIn('status', ['open', 'in_progress'])->count(),
        ];

        // 2. User Growth Chart (Last 30 Days)
        $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $userGrowthChart = $this->fillMissingDates($userGrowth, 30);

        // 3. Revenue Trends Chart (Last 30 Days)
        $revenueData = Payment::where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $revenueChart = $this->fillMissingDates($revenueData, 30);

        // 4. Usage Distribution (AI Messages vs Broadcasts)
        $usageStats = [
            'ai_messages' => AutoReplyLog::count(),
            'broadcasts' => WaMessage::count(), // Simplified proxy for usage
        ];

        // 5. Top Plans
        $topPlans = Subscription::where('status', 'active')
            ->selectRaw('plan_id, COUNT(*) as count')
            ->groupBy('plan_id')
            ->orderByDesc('count')
            ->limit(3)
            ->get()
            ->map(function($item) {
                $plan = Plan::find($item->plan_id);
                return [
                    'name' => $plan->name ?? 'Unknown',
                    'count' => $item->count
                ];
            });

        // 6. Recent Users
        $recentUsers = User::latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'userGrowthChart',
            'revenueChart',
            'usageStats',
            'topPlans',
            'recentUsers'
        ));
    }

    private function fillMissingDates(array $data, int $days): array
    {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[$date] = $data[$date] ?? 0;
        }
        return $result;
    }
}
