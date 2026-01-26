<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaaSMasterService
{
    /**
     * Get MRR (Monthly Recurring Revenue)
     * Sum of (payment amount / duration) for all active paid periods
     */
    public function getMRR(): float
    {
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        if ($activeSubscriptions === 0) return 0;

        // Simple calculation: sum of monthly equivalents from the last valid payment of each active user
        $mrr = 0;
        $users = User::whereHas('subscription', function($q) {
            $q->where('status', 'active');
        })->with('subscription.plan')->get();

        foreach ($users as $user) {
            if ($user->subscription && $user->subscription->plan) {
                // If it's a fixed plan price, we take it. 
                // Professionals usually calculate (Total Amount / Months)
                $mrr += $user->subscription->plan->price;
            }
        }

        return (float) $mrr;
    }

    /**
     * Get Monthly Churn Rate (Percentage)
     * (Users lost in last 30 days / Users at start of 30 days) * 100
     */
    public function getChurnRate(): float
    {
        $startDate = Carbon::now()->subDays(30);
        
        $usersLost = Subscription::whereIn('status', ['canceled', 'expired'])
            ->where('updated_at', '>=', $startDate)
            ->count();
            
        $totalUsersAtStart = User::where('created_at', '<', $startDate)->count();

        if ($totalUsersAtStart === 0) return 0;

        return ($usersLost / $totalUsersAtStart) * 100;
    }

    /**
     * Get LTV (Life Time Value)
     * Average Revenue Per User / Churn Rate
     */
    public function getLTV(): float
    {
        $arpu = $this->getARPU();
        $churnRate = $this->getChurnRate() / 100;

        if ($churnRate == 0) return $arpu * 12; // Estimate for 1 year if zero churn

        return $arpu / $churnRate;
    }

    /**
     * Get ARPU (Average Revenue Per User)
     */
    public function getARPU(): float
    {
        $activeUsers = User::count();
        if ($activeUsers === 0) return 0;

        return $this->getMRR() / $activeUsers;
    }

    /**
     * Get Revenue Growth (Month over Month)
     */
    public function getRevenueGrowth(): array
    {
        $thisMonth = Payment::where('status', 'paid')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->sum('total');
            
        $lastMonth = Payment::where('status', 'paid')
            ->whereMonth('paid_at', Carbon::now()->subMonth()->month)
            ->sum('total');

        $growth = 0;
        if ($lastMonth > 0) {
            $growth = (($thisMonth - $lastMonth) / $lastMonth) * 100;
        }

        return [
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'percentage' => round($growth, 1)
        ];
    }
}
