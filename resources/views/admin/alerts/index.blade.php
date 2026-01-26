@extends('admin.layouts.app')

@section('title', 'Subscription Alerts')
@section('page_title', 'Subscription Alerts')

@section('content')
@php
    // Subscriptions expiring in 7 days
    $expiringSubscriptions = \App\Models\Subscription::where('status', 'active')
        ->where('expires_at', '<=', now()->addDays(7))
        ->where('expires_at', '>', now())
        ->with(['user', 'plan'])
        ->orderBy('expires_at')
        ->get();
    
    // Already expired (last 30 days)
    $expiredSubscriptions = \App\Models\Subscription::where('status', 'active')
        ->where('expires_at', '<', now())
        ->where('expires_at', '>=', now()->subDays(30))
        ->with(['user', 'plan'])
        ->orderByDesc('expires_at')
        ->limit(20)
        ->get();
    
    // Churn risk: Users with subscriptions but old updated_at (no recent activity)
    // Using updated_at as proxy for last activity since last_login_at doesn't exist
    $churnRiskUsers = \App\Models\User::whereHas('subscription', function($q) {
            $q->where('status', 'active');
        })
        ->where('updated_at', '<', now()->subDays(14))
        ->with('subscription.plan')
        ->limit(20)
        ->get();
@endphp

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-2xl p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl text-yellow-500">schedule</span>
            </div>
            <div>
                <div class="text-3xl font-black text-yellow-400">{{ $expiringSubscriptions->count() }}</div>
                <div class="text-sm text-slate-400">Akan Expire (7 hari)</div>
            </div>
        </div>
    </div>

    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl text-red-500">error</span>
            </div>
            <div>
                <div class="text-3xl font-black text-red-400">{{ $expiredSubscriptions->count() }}</div>
                <div class="text-sm text-slate-400">Sudah Expired</div>
            </div>
        </div>
    </div>

    <div class="bg-orange-500/10 border border-orange-500/30 rounded-2xl p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl text-orange-500">person_off</span>
            </div>
            <div>
                <div class="text-3xl font-black text-orange-400">{{ $churnRiskUsers->count() }}</div>
                <div class="text-sm text-slate-400">Churn Risk</div>
            </div>
        </div>
    </div>
</div>

<!-- Expiring Soon -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="font-bold text-lg flex items-center gap-2">
            <span class="material-symbols-outlined text-yellow-500">schedule</span>
            Akan Expire dalam 7 Hari
        </h3>
        <span class="text-sm text-slate-500">{{ $expiringSubscriptions->count() }} users</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-slate-500 border-b border-slate-800">
                    <th class="pb-3">User</th>
                    <th class="pb-3">Plan</th>
                    <th class="pb-3">Expires</th>
                    <th class="pb-3">Days Left</th>
                    <th class="pb-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($expiringSubscriptions as $sub)
                <tr class="hover:bg-surface-light/50">
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-xs font-bold text-primary">
                                {{ strtoupper(substr($sub->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-sm">{{ $sub->user->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-slate-500">{{ $sub->user->email ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <span class="px-2 py-1 bg-primary/20 text-primary rounded text-xs font-medium">
                            {{ $sub->plan->name ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="py-3 text-sm text-slate-400">{{ $sub->expires_at->format('d M Y') }}</td>
                    <td class="py-3">
                        @php $daysLeft = now()->diffInDays($sub->expires_at, false); @endphp
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            {{ $daysLeft <= 1 ? 'bg-red-500/20 text-red-400' : ($daysLeft <= 3 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-slate-700 text-slate-300') }}">
                            {{ $daysLeft }} hari
                        </span>
                    </td>
                    <td class="py-3 text-right">
                        <a href="{{ route('admin.users.show', $sub->user_id) }}" class="px-3 py-1 bg-slate-700 hover:bg-slate-600 rounded text-xs transition">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-slate-500">
                        <span class="material-symbols-outlined text-3xl block mb-2">celebration</span>
                        Tidak ada subscription yang akan expire
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Churn Risk -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
    <div class="flex items-center justify-between mb-6">
        <h3 class="font-bold text-lg flex items-center gap-2">
            <span class="material-symbols-outlined text-orange-500">person_off</span>
            Churn Risk (Tidak Aktif 7+ Hari)
        </h3>
        <span class="text-sm text-slate-500">{{ $churnRiskUsers->count() }} users</span>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($churnRiskUsers as $user)
        <div class="p-4 bg-surface-light rounded-xl border border-slate-700/50 hover:border-orange-500/30 transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-orange-500/20 flex items-center justify-center text-sm font-bold text-orange-400">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate">{{ $user->name }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">
                    Last activity: {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Unknown' }}
                </span>
                <a href="{{ route('admin.users.show', $user) }}" class="text-primary hover:underline">Detail →</a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-8 text-center text-slate-500">
            <span class="material-symbols-outlined text-3xl block mb-2">mood</span>
            Semua user aktif! Tidak ada churn risk.
        </div>
        @endforelse
    </div>
</div>
@endsection
