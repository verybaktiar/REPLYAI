@extends('admin.layouts.app')

@section('title', 'User Analytics')
@section('page_title', 'Advanced User Analytics')

@section('content')

<!-- Period Selector -->
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2">
        <span class="text-sm text-slate-400 mr-2">Period:</span>
        <a href="?period=7" class="px-4 py-2 rounded-lg {{ $period == 7 ? 'bg-primary text-white' : 'bg-surface-light text-slate-400 hover:text-white' }}">7 Days</a>
        <a href="?period=30" class="px-4 py-2 rounded-lg {{ $period == 30 ? 'bg-primary text-white' : 'bg-surface-light text-slate-400 hover:text-white' }}">30 Days</a>
        <a href="?period=90" class="px-4 py-2 rounded-lg {{ $period == 90 ? 'bg-primary text-white' : 'bg-surface-light text-slate-400 hover:text-white' }}">90 Days</a>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="refreshData()" class="flex items-center gap-2 px-4 py-2 bg-surface-light hover:bg-slate-700 rounded-lg transition">
            <span class="material-symbols-outlined text-sm">refresh</span>
            Refresh
        </button>
    </div>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-blue-400">group</span>
            <span class="text-sm text-slate-500">Total Users</span>
        </div>
        <div class="text-2xl font-black">{{ number_format($metrics['total_users']) }}</div>
        <div class="text-xs text-green-400 mt-1">+{{ number_format($metrics['new_users']) }} new</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-green-400">person_check</span>
            <span class="text-sm text-slate-500">Active Users</span>
        </div>
        <div class="text-2xl font-black text-green-400">{{ number_format($metrics['active_users']) }}</div>
        <div class="text-xs text-slate-400 mt-1">{{ $metrics['total_users'] > 0 ? round(($metrics['active_users'] / $metrics['total_users']) * 100, 1) : 0 }}% of total</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-yellow-400">person_off</span>
            <span class="text-sm text-slate-500">Inactive Users</span>
        </div>
        <div class="text-2xl font-black text-yellow-400">{{ number_format($metrics['inactive_users']) }}</div>
        <div class="text-xs text-slate-400 mt-1">No activity in {{ $period }} days</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-purple-400">schedule</span>
            <span class="text-sm text-slate-500">Avg Session</span>
        </div>
        <div class="text-2xl font-black text-purple-400">{{ round($sessionStats['avg_session_duration'], 0) }}m</div>
        <div class="text-xs text-slate-400 mt-1">Peak: {{ $sessionStats['peak_concurrent_users'] }} users</div>
    </div>
</div>

<!-- Activity Heatmap -->
<div class="bg-surface-dark rounded-xl p-6 border border-slate-800 mb-6">
    <h3 class="font-bold mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">calendar_heat_map</span>
        Activity Heatmap (Last 30 Days)
    </h3>
    <div class="overflow-x-auto">
        <div class="min-w-[800px]">
            <div class="flex">
                <div class="w-16"></div>
                <div class="flex-1 grid grid-cols-24 gap-1 text-xs text-center text-slate-500 mb-2">
                    @for($h = 0; $h < 24; $h += 3)
                        <div class="col-span-3">{{ sprintf('%02d:00', $h) }}</div>
                    @endfor
                </div>
            </div>
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
            <div class="flex items-center mb-1">
                <div class="w-16 text-xs text-slate-400 font-medium">{{ $day }}</div>
                <div class="flex-1 grid grid-cols-24 gap-1">
                    @for($hour = 0; $hour < 24; $hour++)
                        @php
                            $count = $activityHeatmap[$day][$hour] ?? 0;
                            $maxCount = max(1, max(array_map('max', $activityHeatmap)));
                            $intensity = $count / $maxCount;
                            $opacity = 0.1 + ($intensity * 0.9);
                        @endphp
                        <div class="h-6 rounded-sm bg-primary cursor-pointer hover:ring-2 hover:ring-white transition-all relative group"
                             style="opacity: {{ $opacity }}">
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-800 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none z-10">
                                {{ $day }} {{ sprintf('%02d:00', $hour) }}: {{ $count }} logins
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
            @endforeach
            <div class="flex items-center mt-4">
                <div class="w-16 text-xs text-slate-500">Less</div>
                <div class="flex gap-1">
                    @foreach([0.1, 0.3, 0.5, 0.7, 0.9] as $op)
                        <div class="w-4 h-4 rounded-sm bg-primary" style="opacity: {{ $op }}"></div>
                    @endforeach
                </div>
                <div class="ml-2 text-xs text-slate-500">More</div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Feature Usage -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">widgets</span>
            Feature Usage
        </h3>
        <div class="space-y-4">
            @foreach($featureUsage as $feature => $count)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-slate-400">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                    <span class="font-bold text-sm">{{ number_format($count) }}</span>
                </div>
                <div class="h-2 bg-surface-light rounded-full overflow-hidden">
                    @php
                        $maxUsage = max(1, max($featureUsage));
                        $percentage = min(100, ($count / $maxUsage) * 100);
                    @endphp
                    <div class="h-full bg-gradient-to-r from-primary to-blue-400 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Platform Usage -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">devices</span>
            Platform Distribution
        </h3>
        <div class="space-y-4">
            @foreach($platformUsage as $platform => $count)
            @php
                $total = max(1, array_sum($platformUsage));
                $percentage = round(($count / $total) * 100, 1);
                $icons = ['web' => 'language', 'mobile' => 'smartphone', 'api' => 'code'];
                $colors = ['web' => 'text-blue-400', 'mobile' => 'text-green-400', 'api' => 'text-purple-400'];
            @endphp
            <div class="flex items-center gap-3 p-3 bg-surface-light rounded-lg">
                <span class="material-symbols-outlined {{ $colors[$platform] ?? 'text-slate-400' }}">{{ $icons[$platform] ?? 'device_unknown' }}</span>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">{{ ucfirst($platform) }}</span>
                        <span class="font-bold">{{ $percentage }}%</span>
                    </div>
                    <div class="text-xs text-slate-500">{{ number_format($count) }} activities</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Top Active Users -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">emoji_events</span>
            Top Active Users
        </h3>
        <div class="space-y-3">
            @foreach($topUsers as $index => $user)
            <a href="{{ route('admin.user-analytics.show', $user->id) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-light transition group">
                <div class="w-6 h-6 rounded-full {{ $index < 3 ? 'bg-primary text-white' : 'bg-slate-700 text-slate-400' }} flex items-center justify-center text-xs font-bold">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate group-hover:text-primary transition">{{ $user->name }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ $user->email }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-sm">{{ $user->login_count }}</div>
                    <div class="text-xs text-slate-500">logins</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Login History -->
<div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
        <h3 class="font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">history</span>
            Recent Login History
        </h3>
        <a href="{{ route('admin.activity-logs.index') }}" class="text-sm text-primary hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-light/50 text-slate-400">
                <tr>
                    <th class="text-left py-3 px-6">User</th>
                    <th class="text-left py-3 px-6">IP Address</th>
                    <th class="text-left py-3 px-6">Time</th>
                    <th class="text-left py-3 px-6">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($loginHistory as $log)
                <tr class="hover:bg-surface-light/30 transition">
                    <td class="py-3 px-6">
                        @if($log->user)
                            <div class="font-medium">{{ $log->user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $log->user->email }}</div>
                        @else
                            <span class="text-slate-500">Unknown</span>
                        @endif
                    </td>
                    <td class="py-3 px-6 text-slate-400 font-mono text-xs">{{ $log->ip_address ?? 'N/A' }}</td>
                    <td class="py-3 px-6 text-slate-400">{{ $log->created_at->diffForHumans() }}</td>
                    <td class="py-3 px-6">
                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-400 border border-green-500/20">
                            Success
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-20">history_off</span>
                        <p>No login history available</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
function refreshData() {
    window.location.reload();
}

// Auto-refresh every 5 minutes
setInterval(() => {
    refreshData();
}, 300000);
</script>
@endpush
