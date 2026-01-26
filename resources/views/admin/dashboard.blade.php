@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
@php
    // Dashboard Stats
    $totalUsers = \App\Models\User::count();
    $vipUsers = \App\Models\User::where('is_vip', true)->count();
    $pendingPayments = \App\Models\Payment::where('status', 'pending')->count();
    $activeSubs = \App\Models\Subscription::where('status', 'active')->count();
    $openTickets = \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count();
    
    // Revenue 7 hari terakhir
    $revenueData = \App\Models\Payment::where('status', 'paid')
        ->where('created_at', '>=', now()->subDays(7))
        ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->pluck('total', 'date')
        ->toArray();
    
    // Fill missing dates
    $revenue7Days = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $revenue7Days[$date] = $revenueData[$date] ?? 0;
    }
    $totalRevenue7Days = array_sum($revenue7Days);
    $maxRevenue = max($revenue7Days) ?: 1;
    
    // Top Plans
    $topPlans = \App\Models\Subscription::where('status', 'active')
        ->selectRaw('plan_id, COUNT(*) as count')
        ->groupBy('plan_id')
        ->orderByDesc('count')
        ->limit(3)
        ->get()
        ->map(function($item) {
            $plan = \App\Models\Plan::find($item->plan_id);
            return [
                'name' => $plan->name ?? 'Unknown',
                'count' => $item->count
            ];
        });
    
    // Recent Users
    $recentUsers = \App\Models\User::latest()->limit(5)->get();
@endphp

<!-- Welcome -->
<div class="mb-8">
    <h2 class="text-2xl font-black mb-1">Selamat datang, {{ Auth::guard('admin')->user()->name }}! 👋</h2>
    <p class="text-slate-400">Berikut ringkasan sistem hari ini.</p>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-slate-700 transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl text-primary">group</span>
            </div>
        </div>
        <div class="text-2xl font-black">{{ number_format($totalUsers) }}</div>
        <div class="text-sm text-slate-500">Total Users</div>
    </div>

    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-yellow-500/50 transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl text-yellow-500">star</span>
            </div>
        </div>
        <div class="text-2xl font-black text-yellow-400">{{ number_format($vipUsers) }}</div>
        <div class="text-sm text-slate-500">VIP Users</div>
    </div>

    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-orange-500/50 transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl text-orange-500">pending</span>
            </div>
        </div>
        <div class="text-2xl font-black text-orange-400">{{ number_format($pendingPayments) }}</div>
        <div class="text-sm text-slate-500">Pending Pay</div>
    </div>

    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-green-500/50 transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl text-green-500">verified</span>
            </div>
        </div>
        <div class="text-2xl font-black text-green-400">{{ number_format($activeSubs) }}</div>
        <div class="text-sm text-slate-500">Active Subs</div>
    </div>

    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-red-500/50 transition">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl text-red-500">support</span>
            </div>
        </div>
        <div class="text-2xl font-black text-red-400">{{ number_format($openTickets) }}</div>
        <div class="text-sm text-slate-500">Open Tickets</div>
    </div>
</div>

<!-- Revenue & Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- Revenue Chart -->
    <div class="lg:col-span-2 bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-lg">Revenue 7 Hari Terakhir</h3>
            <span class="text-lg text-green-400 font-bold">Rp {{ number_format($totalRevenue7Days, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-end gap-3 h-40">
            @foreach($revenue7Days as $date => $amount)
            <div class="flex-1 flex flex-col items-center gap-2">
                <div class="w-full rounded-t-lg relative overflow-hidden" style="height: {{ max(($amount / $maxRevenue) * 100, 5) }}%">
                    <div class="absolute inset-0 bg-gradient-to-t from-primary to-primary/40"></div>
                </div>
                <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Top Plans -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-4">Top Plans</h3>
        <div class="space-y-3">
            @forelse($topPlans as $index => $plan)
            <div class="flex items-center gap-3 p-3 bg-surface-light rounded-xl">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold
                    {{ $index === 0 ? 'bg-yellow-500 text-black' : ($index === 1 ? 'bg-slate-400 text-black' : 'bg-amber-700 text-white') }}">
                    {{ $index + 1 }}
                </span>
                <span class="flex-1 font-medium">{{ $plan['name'] }}</span>
                <span class="text-sm text-slate-400">{{ $plan['count'] }} users</span>
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Belum ada data</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Users -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Quick Actions -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 p-4 bg-surface-light hover:bg-primary/10 rounded-xl border border-slate-800 hover:border-primary/50 transition group">
                <span class="material-symbols-outlined text-2xl text-cyan-500 group-hover:scale-110 transition">person_add</span>
                <div>
                    <div class="font-semibold text-sm">Add User</div>
                    <div class="text-xs text-slate-500">Tambah manual</div>
                </div>
            </a>
            <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="flex items-center gap-3 p-4 bg-surface-light hover:bg-green-500/10 rounded-xl border border-slate-800 hover:border-green-500/50 transition group">
                <span class="material-symbols-outlined text-2xl text-green-500 group-hover:scale-110 transition">payment</span>
                <div>
                    <div class="font-semibold text-sm">Payments</div>
                    <div class="text-xs text-slate-500">{{ $pendingPayments }} pending</div>
                </div>
            </a>
            <a href="{{ route('admin.support.index') }}" class="flex items-center gap-3 p-4 bg-surface-light hover:bg-yellow-500/10 rounded-xl border border-slate-800 hover:border-yellow-500/50 transition group">
                <span class="material-symbols-outlined text-2xl text-yellow-500 group-hover:scale-110 transition">support_agent</span>
                <div>
                    <div class="font-semibold text-sm">Support</div>
                    <div class="text-xs text-slate-500">{{ $openTickets }} open</div>
                </div>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 p-4 bg-surface-light hover:bg-purple-500/10 rounded-xl border border-slate-800 hover:border-purple-500/50 transition group">
                <span class="material-symbols-outlined text-2xl text-purple-500 group-hover:scale-110 transition">settings</span>
                <div>
                    <div class="font-semibold text-sm">Settings</div>
                    <div class="text-xs text-slate-500">System config</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg">User Terbaru</h3>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-primary hover:underline">Lihat semua →</a>
        </div>
        <div class="space-y-3">
            @foreach($recentUsers as $user)
            <div class="flex items-center gap-3 p-3 bg-surface-light rounded-xl">
                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-bold text-primary">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate flex items-center gap-2">
                        {{ $user->name }}
                        @if($user->is_vip)
                        <span class="text-yellow-500">⭐</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                </div>
                <span class="text-xs text-slate-500">{{ $user->created_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
