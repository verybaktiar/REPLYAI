@extends('admin.layouts.app')

@section('title', 'API Usage')
@section('page_title', 'API Usage Dashboard')

@section('content')
@php
    // Top API users (30 days)
    $topUsers = \App\Models\ApiUsageLog::where('created_at', '>=', now()->subDays(30))
        ->selectRaw('user_id, COUNT(*) as total_calls')
        ->groupBy('user_id')
        ->orderByDesc('total_calls')
        ->limit(10)
        ->with('user')
        ->get();
    
    // Top endpoints
    $topEndpoints = \App\Models\ApiUsageLog::where('created_at', '>=', now()->subDays(30))
        ->selectRaw('endpoint, COUNT(*) as count')
        ->groupBy('endpoint')
        ->orderByDesc('count')
        ->limit(10)
        ->get();
    
    // Daily calls (7 days)
    $dailyCalls = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $count = \App\Models\ApiUsageLog::whereDate('created_at', $date)->count();
        $dailyCalls[$date] = $count;
    }
    $maxCalls = max($dailyCalls) ?: 1;
    
    // Total stats
    $totalCalls = \App\Models\ApiUsageLog::where('created_at', '>=', now()->subDays(30))->count();
    $todayCalls = \App\Models\ApiUsageLog::whereDate('created_at', today())->count();
@endphp

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="text-3xl font-black">{{ number_format($totalCalls) }}</div>
        <div class="text-sm text-slate-400">Total Calls (30d)</div>
    </div>
    <div class="bg-primary/10 border border-primary/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-primary">{{ number_format($todayCalls) }}</div>
        <div class="text-sm text-slate-400">Today</div>
    </div>
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="text-3xl font-black">{{ $topUsers->count() }}</div>
        <div class="text-sm text-slate-400">Active API Users</div>
    </div>
</div>

<!-- Charts & Lists -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Daily Call Trend -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-6">API Calls (7 Days)</h3>
        <div class="flex items-end gap-2 h-40">
            @foreach($dailyCalls as $date => $count)
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="w-full bg-gradient-to-t from-primary to-purple-500 rounded-t" 
                     style="height: {{ max(($count / $maxCalls) * 120, 4) }}px;"></div>
                <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Top Endpoints -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-6">Top Endpoints</h3>
        <div class="space-y-3">
            @forelse($topEndpoints as $ep)
            <div class="flex items-center gap-3">
                <code class="flex-1 text-xs bg-slate-800 px-2 py-1 rounded truncate">{{ $ep->endpoint }}</code>
                <span class="text-sm font-bold text-slate-400">{{ number_format($ep->count) }}</span>
            </div>
            @empty
            <p class="text-slate-500 text-sm">No data</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Top Users -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
    <h3 class="font-bold text-lg mb-6">Top API Users (30 Days)</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-slate-500 border-b border-slate-800">
                    <th class="pb-3">#</th>
                    <th class="pb-3">User</th>
                    <th class="pb-3">Email</th>
                    <th class="pb-3">Total Calls</th>
                    <th class="pb-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($topUsers as $i => $usage)
                <tr>
                    <td class="py-3 text-sm">{{ $i + 1 }}</td>
                    <td class="py-3 font-medium">{{ $usage->user->name ?? 'Unknown' }}</td>
                    <td class="py-3 text-sm text-slate-400">{{ $usage->user->email ?? '-' }}</td>
                    <td class="py-3">
                        <span class="px-3 py-1 bg-primary/20 text-primary rounded font-bold text-sm">
                            {{ number_format($usage->total_calls) }}
                        </span>
                    </td>
                    <td class="py-3">
                        <a href="{{ route('admin.users.show', $usage->user_id) }}" class="text-sm text-primary hover:underline">View User</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
