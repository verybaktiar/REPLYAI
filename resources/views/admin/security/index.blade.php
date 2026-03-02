@extends('admin.layouts.app')

@section('title', 'Security Dashboard')
@section('page_title', 'Security Center')

@section('content')
<!-- Security Score -->
<div class="mb-6 p-6 bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl border border-slate-700">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold mb-1">Security Score</h2>
            <p class="text-sm text-slate-400">Overall platform security status</p>
        </div>
        <div class="text-right">
            <div class="text-4xl font-black text-green-400" id="security-score">--</div>
            <div class="text-xs text-slate-500">Loading...</div>
        </div>
    </div>
    <div class="mt-4 h-2 bg-slate-700 rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-red-500 via-yellow-500 to-green-500 w-0 transition-all duration-1000" id="security-bar"></div>
    </div>
</div>

<!-- Metrics Grid -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Failed Logins (24h)</div>
        <div class="text-2xl font-bold {{ $metrics['failed_logins_24h'] > 10 ? 'text-red-400' : 'text-white' }}">
            {{ $metrics['failed_logins_24h'] }}
        </div>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Suspicious Activities</div>
        <div class="text-2xl font-bold {{ $metrics['suspicious_activities'] > 0 ? 'text-orange-400' : 'text-white' }}">
            {{ $metrics['suspicious_activities'] }}
        </div>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Active Sessions</div>
        <div class="text-2xl font-bold text-white">{{ $metrics['active_sessions'] }}</div>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Blocked IPs</div>
        <div class="text-2xl font-bold {{ $metrics['blocked_ips'] > 0 ? 'text-yellow-400' : 'text-white' }}">
            {{ $metrics['blocked_ips'] }}
        </div>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Unresolved Alerts</div>
        <div class="text-2xl font-bold {{ $metrics['unresolved_alerts'] > 0 ? 'text-red-400' : 'text-green-400' }}">
            {{ $metrics['unresolved_alerts'] }}
        </div>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">High Risk Events</div>
        <div class="text-2xl font-bold {{ $metrics['high_risk_events'] > 0 ? 'text-red-400' : 'text-white' }}">
            {{ $metrics['high_risk_events'] }}
        </div>
    </div>
</div>

<!-- Risk Distribution -->
<div class="grid lg:grid-cols-3 gap-6 mb-8">
    <!-- Risk Levels -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-red-400">warning</span>
            Risk Distribution (24h)
        </h3>
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-red-500/10 rounded-lg">
                <span class="text-red-400 font-medium">Critical</span>
                <span class="text-xl font-bold">{{ $riskDistribution['critical'] }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-orange-500/10 rounded-lg">
                <span class="text-orange-400 font-medium">High</span>
                <span class="text-xl font-bold">{{ $riskDistribution['high'] }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-yellow-500/10 rounded-lg">
                <span class="text-yellow-400 font-medium">Medium</span>
                <span class="text-xl font-bold">{{ $riskDistribution['medium'] }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-green-500/10 rounded-lg">
                <span class="text-green-400 font-medium">Low</span>
                <span class="text-xl font-bold">{{ $riskDistribution['low'] }}</span>
            </div>
        </div>
    </div>
    
    <!-- Failed Logins by IP -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-orange-400">block</span>
            Failed Logins by IP (24h)
        </h3>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @forelse($failedLogins as $ip)
            <div class="flex items-center justify-between p-3 bg-slate-800 rounded-lg">
                <code class="text-sm">{{ $ip->ip_address }}</code>
                <span class="px-2 py-1 bg-red-500/20 text-red-400 text-xs rounded-full font-bold">
                    {{ $ip->count }}
                </span>
            </div>
            @empty
            <p class="text-slate-500 text-sm">No failed login attempts.</p>
            @endforelse
        </div>
    </div>
    
    <!-- Admin Activity -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-400">group</span>
            Admin Logins (7d)
        </h3>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($adminActivity as $activity)
            <div class="flex items-center justify-between p-3 bg-slate-800 rounded-lg">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary text-xs font-bold">
                        {{ strtoupper(substr($activity->admin->name, 0, 1)) }}
                    </div>
                    <span class="text-sm">{{ $activity->admin->name }}</span>
                </div>
                <span class="text-sm font-medium">{{ $activity->login_count }} logins</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Security Alerts -->
<div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden mb-8">
    <div class="p-6 border-b border-slate-800 flex items-center justify-between">
        <h3 class="font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-red-400">notification_important</span>
            Security Alerts
        </h3>
        @if($metrics['unresolved_alerts'] > 0)
        <span class="px-3 py-1 bg-red-500/20 text-red-400 text-sm rounded-full font-medium">
            {{ $metrics['unresolved_alerts'] }} Unresolved
        </span>
        @endif
    </div>
    
    <div class="divide-y divide-slate-800">
        @forelse($alerts as $alert)
        <div class="p-4 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-red-400">warning</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-medium">{{ $alert->type_label }}</span>
                    <span class="text-xs text-slate-500">{{ $alert->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-slate-400 mb-2">
                    IP: {{ $alert->ip_address }}
                    @if($alert->email_attempted)
                    | Email: {{ $alert->email_attempted }}
                    @endif
                </p>
                <form method="POST" action="{{ route('admin.security.resolve-alert', $alert) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="notes" placeholder="Resolution notes (optional)" 
                           class="flex-1 px-3 py-1 bg-slate-800 border border-slate-700 rounded text-sm">
                    <button type="submit" class="px-3 py-1 bg-green-500/20 text-green-400 rounded text-sm hover:bg-green-500/30 transition">
                        Resolve
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-slate-500">
            <span class="material-symbols-outlined text-4xl mb-2">check_circle</span>
            <p>No unresolved security alerts.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Recent Security Events -->
<div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
    <div class="p-6 border-b border-slate-800 flex items-center justify-between">
        <h3 class="font-semibold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">history</span>
            Recent Security Events
        </h3>
        <a href="{{ route('admin.security.logs') }}" class="text-sm text-primary hover:underline">
            View All Logs
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-800">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase">Time</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase">Admin</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase">Action</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase">Description</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase">IP</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase">Risk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($recentEvents as $event)
                <tr class="hover:bg-slate-800/50">
                    <td class="px-6 py-3 text-sm text-slate-400">
                        {{ $event->created_at->format('H:i') }}
                    </td>
                    <td class="px-6 py-3 text-sm">
                        {{ $event->admin->name ?? 'Unknown' }}
                    </td>
                    <td class="px-6 py-3 text-sm">
                        <span class="px-2 py-1 bg-slate-700 rounded text-xs">
                            {{ $event->action }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-slate-300">
                        {{ Str::limit($event->description, 50) }}
                    </td>
                    <td class="px-6 py-3 text-sm text-slate-400">
                        {{ $event->ip_address }}
                    </td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 rounded text-xs font-bold
                            {{ $event->risk_level === 'critical' ? 'bg-red-500/20 text-red-400' : '' }}
                            {{ $event->risk_level === 'high' ? 'bg-orange-500/20 text-orange-400' : '' }}
                            {{ $event->risk_level === 'medium' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                            {{ $event->risk_level === 'low' ? 'bg-green-500/20 text-green-400' : '' }}">
                            {{ $event->risk_score }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
// Fetch and display security score
fetch('{{ route('admin.security.status') }}')
    .then(response => response.json())
    .then(data => {
        const scoreEl = document.getElementById('security-score');
        const barEl = document.getElementById('security-bar');
        
        scoreEl.textContent = data.score + '%';
        barEl.style.width = data.score + '%';
        
        // Color based on score
        if (data.score >= 80) {
            scoreEl.className = 'text-4xl font-black text-green-400';
        } else if (data.score >= 60) {
            scoreEl.className = 'text-4xl font-black text-yellow-400';
        } else {
            scoreEl.className = 'text-4xl font-black text-red-400';
        }
    })
    .catch(error => {
        console.error('Failed to load security status:', error);
        document.getElementById('security-score').textContent = 'Error';
    });
</script>
@endsection
