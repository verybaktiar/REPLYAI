@extends('admin.layouts.app')

@section('title', 'Revenue Dashboard')
@section('page_title', 'Revenue Dashboard')

@section('content')
@php
    // MRR Calculation (Monthly Recurring Revenue)
    $activeSubscriptions = \App\Models\Subscription::where('status', 'active')
        ->with('plan')
        ->get();
    
    $mrr = $activeSubscriptions->sum(function($sub) {
        if (!$sub->plan) return 0;
        $monthlyPrice = $sub->billing_cycle === 'yearly' 
            ? $sub->plan->price_yearly / 12 
            : $sub->plan->price_monthly;
        return $monthlyPrice;
    });
    
    // ARR (Annual Recurring Revenue)
    $arr = $mrr * 12;
    
    // Revenue this month
    $revenueThisMonth = \App\Models\Payment::where('status', 'paid')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('amount');
    
    // Revenue last month
    $revenueLastMonth = \App\Models\Payment::where('status', 'paid')
        ->whereMonth('created_at', now()->subMonth()->month)
        ->whereYear('created_at', now()->subMonth()->year)
        ->sum('amount');
    
    // Growth percentage
    $revenueGrowth = $revenueLastMonth > 0 
        ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1) 
        : 0;
    
    // Revenue by Plan (last 30 days) - simplified query
    $revenueByPlan = \App\Models\Payment::where('payments.status', 'paid')
        ->where('payments.created_at', '>=', now()->subDays(30))
        ->join('plans', 'payments.plan_id', '=', 'plans.id')
        ->selectRaw('plans.name, plans.slug, SUM(payments.amount) as total')
        ->groupBy('plans.id', 'plans.name', 'plans.slug')
        ->orderByDesc('total')
        ->get();
    
    $totalRevenueByPlan = $revenueByPlan->sum('total') ?: 1;
    
    // Revenue trend (30 days)
    $revenueTrend = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $amount = \App\Models\Payment::where('status', 'paid')
            ->whereDate('created_at', $date)
            ->sum('amount');
        $revenueTrend[$date] = $amount;
    }
    $maxTrend = max($revenueTrend) ?: 1;
    
    // Top Paying Users
    $topPayingUsers = \App\Models\Payment::where('status', 'paid')
        ->selectRaw('user_id, SUM(amount) as total_paid, COUNT(*) as payment_count')
        ->groupBy('user_id')
        ->orderByDesc('total_paid')
        ->limit(5)
        ->with('user')
        ->get();
    
    // Payment Methods Distribution
    $paymentMethods = \App\Models\Payment::where('status', 'paid')
        ->where('created_at', '>=', now()->subDays(30))
        ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
        ->groupBy('payment_method')
        ->orderByDesc('total')
        ->get();
    
    $totalPaymentMethodAmount = $paymentMethods->sum('total') ?: 1;
@endphp

<!-- Revenue Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- MRR -->
    <div class="bg-gradient-to-br from-primary to-blue-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium opacity-80">MRR (Monthly)</span>
            <span class="material-symbols-outlined text-2xl opacity-60">trending_up</span>
        </div>
        <div class="text-3xl font-black">Rp {{ number_format($mrr, 0, ',', '.') }}</div>
        <div class="mt-2 text-sm opacity-80">{{ $activeSubscriptions->count() }} active subscriptions</div>
    </div>

    <!-- ARR -->
    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium opacity-80">ARR (Annual)</span>
            <span class="material-symbols-outlined text-2xl opacity-60">calendar_month</span>
        </div>
        <div class="text-3xl font-black">Rp {{ number_format($arr, 0, ',', '.') }}</div>
        <div class="mt-2 text-sm opacity-80">Projected annual revenue</div>
    </div>

    <!-- Revenue This Month -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-400">Bulan Ini</span>
            @if($revenueGrowth >= 0)
            <span class="flex items-center gap-1 text-green-400 text-sm">
                <span class="material-symbols-outlined text-sm">arrow_upward</span>
                {{ $revenueGrowth }}%
            </span>
            @else
            <span class="flex items-center gap-1 text-red-400 text-sm">
                <span class="material-symbols-outlined text-sm">arrow_downward</span>
                {{ abs($revenueGrowth) }}%
            </span>
            @endif
        </div>
        <div class="text-3xl font-black text-green-400">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</div>
        <div class="mt-2 text-sm text-slate-500">vs Rp {{ number_format($revenueLastMonth, 0, ',', '.') }} bulan lalu</div>
    </div>

    <!-- Average Revenue per User (ARPU) -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-400">ARPU</span>
            <span class="material-symbols-outlined text-2xl text-slate-500">person</span>
        </div>
        @php $arpu = $activeSubscriptions->count() > 0 ? $mrr / $activeSubscriptions->count() : 0; @endphp
        <div class="text-3xl font-black">Rp {{ number_format($arpu, 0, ',', '.') }}</div>
        <div class="mt-2 text-sm text-slate-500">Per user per month</div>
    </div>
</div>

<!-- Revenue Charts -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- 30-Day Trend -->
    <div class="lg:col-span-2 bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-6">Revenue Trend (30 Hari)</h3>
        <div class="flex items-end gap-1 h-48">
            @foreach($revenueTrend as $date => $amount)
            <div class="flex-1 flex flex-col items-center group relative">
                <div class="absolute bottom-full mb-2 hidden group-hover:block bg-slate-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                    {{ \Carbon\Carbon::parse($date)->format('d M') }}: Rp {{ number_format($amount, 0, ',', '.') }}
                </div>
                <div class="w-full rounded-t transition-all group-hover:opacity-80" 
                     style="height: {{ max(($amount / $maxTrend) * 160, 2) }}px; background: linear-gradient(to top, #135bec, #8b5cf6);"></div>
            </div>
            @endforeach
        </div>
        <div class="flex justify-between mt-2 text-xs text-slate-500">
            <span>{{ now()->subDays(29)->format('d M') }}</span>
            <span>{{ now()->format('d M') }}</span>
        </div>
    </div>

    <!-- Revenue by Plan -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-6">Revenue by Plan</h3>
        <div class="space-y-4">
            @forelse($revenueByPlan as $item)
            @php $percent = ($item->total / $totalRevenueByPlan) * 100; @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium">{{ $item->name }}</span>
                    <span class="text-slate-400">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
                <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary to-purple-500" style="width: {{ $percent }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Belum ada data</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Top Paying Users & Payment Methods -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Top Paying Users -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-4">Top Paying Customers</h3>
        <div class="space-y-3">
            @foreach($topPayingUsers as $index => $payment)
            <div class="flex items-center gap-3 p-3 bg-surface-light rounded-xl">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold
                    {{ $index === 0 ? 'bg-yellow-500 text-black' : ($index === 1 ? 'bg-slate-400 text-black' : 'bg-slate-600 text-white') }}">
                    {{ $index + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate">{{ $payment->user->name ?? 'Unknown' }}</div>
                    <div class="text-xs text-slate-500">{{ $payment->payment_count }} payments</div>
                </div>
                <span class="text-sm font-bold text-green-400">Rp {{ number_format($payment->total_paid, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-4">Payment Methods (30 Days)</h3>
        <div class="space-y-4">
            @forelse($paymentMethods as $method)
            @php 
                $percent = ($method->total / $totalPaymentMethodAmount) * 100;
                $methodName = match($method->payment_method) {
                    'bank_transfer' => 'Transfer Bank',
                    'midtrans' => 'Midtrans (QRIS/VA)',
                    'manual' => 'Manual',
                    default => ucfirst($method->payment_method ?? 'Other')
                };
            @endphp
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">
                        {{ $method->payment_method === 'midtrans' ? 'qr_code_2' : 'account_balance' }}
                    </span>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium">{{ $methodName }}</span>
                        <span class="text-slate-400">{{ $method->count }} txn</span>
                    </div>
                    <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
                <span class="text-sm font-medium">{{ round($percent) }}%</span>
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Belum ada data</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
