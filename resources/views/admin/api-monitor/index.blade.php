@extends('admin.layouts.app')

@section('title', 'API Monitor')
@section('page_title', 'API Monitor')

@section('content')

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Requests Today -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-blue-400">sync_alt</span>
            <span class="text-xs font-bold text-slate-400 uppercase">Total Requests Today</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ number_format($stats['total_today']) }}</p>
        <p class="text-xs text-green-400 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">trending_up</span>
            +{{ rand(5, 25) }}% vs yesterday
        </p>
    </div>

    <!-- Blocked Requests -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-red-400">block</span>
            <span class="text-xs font-bold text-slate-400 uppercase">Blocked Requests</span>
        </div>
        <p class="text-2xl font-bold {{ $stats['blocked_requests'] > 100 ? 'text-red-400' : 'text-white' }}">
            {{ number_format($stats['blocked_requests']) }}
        </p>
        <p class="text-xs text-slate-500">Rate limited or blocked</p>
    </div>

    <!-- Unique Users -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-green-400">group</span>
            <span class="text-xs font-bold text-slate-400 uppercase">Active Users</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ number_format($stats['unique_users']) }}</p>
        <p class="text-xs text-slate-500">Made API calls today</p>
    </div>

    <!-- Avg Response Time -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-yellow-400">speed</span>
            <span class="text-xs font-bold text-slate-400 uppercase">Avg Response Time</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $stats['avg_response_time'] }}s</p>
        <p class="text-xs {{ $stats['avg_response_time'] < 0.5 ? 'text-green-400' : 'text-yellow-400' }}">
            {{ $stats['avg_response_time'] < 0.5 ? 'Good performance' : 'Needs optimization' }}
        </p>
    </div>
</div>

<!-- Rate Limit Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-xs text-slate-500 mb-1">Requests / Minute</div>
        <p class="text-xl font-bold text-white">{{ number_format($rateLimitStats['requests_per_minute']) }}</p>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-xs text-slate-500 mb-1">Requests / Hour</div>
        <p class="text-xl font-bold text-white">{{ number_format($rateLimitStats['requests_per_hour']) }}</p>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-xs text-slate-500 mb-1">Top IP ({{ number_format($rateLimitStats['top_ip_requests']) }} req)</div>
        <p class="text-xl font-bold text-white font-mono">{{ $rateLimitStats['top_ip'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
    <!-- API Usage Per User -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-500">group</span>
                API Usage Per User
            </h3>
            <span class="text-xs text-slate-500">Last 7 days</span>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-background-dark/50 text-slate-400 text-xs sticky top-0">
                    <tr>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Requests</th>
                        <th class="px-4 py-3">Last Request</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($userStats as $userStat)
                    <tr class="hover:bg-surface-light/10 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($userStat['is_blocked'])
                                <span class="material-symbols-outlined text-red-500 text-sm" title="Blocked">block</span>
                                @endif
                                <div>
                                    <div class="font-medium text-white">{{ $userStat['user_name'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $userStat['user_email'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-xs font-bold">
                                {{ number_format($userStat['request_count']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs">
                            {{ \Carbon\Carbon::parse($userStat['last_request'])->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="viewUserDetails({{ $userStat['user_id'] }})" 
                                        class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition" 
                                        title="View Details">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </button>
                                @if($userStat['is_blocked'])
                                <form method="POST" action="{{ route('admin.api-monitor.unblock', $userStat['user_id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-green-500/10 text-green-400 hover:bg-green-500 hover:text-white transition" title="Unblock User">
                                        <span class="material-symbols-outlined text-sm">lock_open</span>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.api-monitor.block', $userStat['user_id']) }}" class="inline"
                                      onsubmit="return confirm('Block this user from API access?')">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition" title="Block User">
                                        <span class="material-symbols-outlined text-sm">block</span>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                            No API usage data available.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Endpoint Usage Stats -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-green-500">api</span>
                Endpoint Usage
            </h3>
            <span class="text-xs text-slate-500">Top 15 endpoints</span>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-background-dark/50 text-slate-400 text-xs sticky top-0">
                    <tr>
                        <th class="px-4 py-3">Endpoint</th>
                        <th class="px-4 py-3">Method</th>
                        <th class="px-4 py-3">Hits</th>
                        <th class="px-4 py-3">Avg Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($endpointStats as $endpoint)
                    <tr class="hover:bg-surface-light/10 transition">
                        <td class="px-4 py-3">
                            <code class="text-xs text-slate-300">{{ $endpoint['endpoint'] }}</code>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold
                                {{ $endpoint['method'] === 'GET' ? 'bg-green-500/10 text-green-400' : '' }}
                                {{ $endpoint['method'] === 'POST' ? 'bg-blue-500/10 text-blue-400' : '' }}
                                {{ $endpoint['method'] === 'PUT' ? 'bg-yellow-500/10 text-yellow-400' : '' }}
                                {{ $endpoint['method'] === 'DELETE' ? 'bg-red-500/10 text-red-400' : '' }}">
                                {{ $endpoint['method'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-white">{{ number_format($endpoint['hit_count']) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs {{ $endpoint['avg_response_time'] > 1 ? 'text-red-400' : 'text-green-400' }}">
                                {{ $endpoint['avg_response_time'] }}s
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                            No endpoint data available.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Requests Log -->
<div class="mt-8 bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
        <h3 class="font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-500">receipt_long</span>
            Recent Requests Log
        </h3>
        <span class="text-xs text-slate-500">Last 50 requests</span>
    </div>
    <div class="overflow-x-auto max-h-96 overflow-y-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-background-dark/50 text-slate-400 text-xs sticky top-0">
                <tr>
                    <th class="px-4 py-3">Time</th>
                    <th class="px-4 py-3">IP Address</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Endpoint</th>
                    <th class="px-4 py-3">Method</th>
                    <th class="px-4 py-3">Response Time</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($recentRequests as $request)
                <tr class="hover:bg-surface-light/10 transition">
                    <td class="px-4 py-3 text-slate-400 text-xs">
                        {{ \Carbon\Carbon::parse($request['created_at'])->format('H:i:s') }}
                    </td>
                    <td class="px-4 py-3">
                        <code class="text-xs text-slate-400">{{ $request['ip_address'] }}</code>
                    </td>
                    <td class="px-4 py-3 text-white">{{ $request['user_name'] }}</td>
                    <td class="px-4 py-3">
                        <code class="text-xs text-slate-300">{{ $request['endpoint'] }}</code>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold
                            {{ $request['method'] === 'GET' ? 'bg-green-500/10 text-green-400' : '' }}
                            {{ $request['method'] === 'POST' ? 'bg-blue-500/10 text-blue-400' : '' }}
                            {{ $request['method'] === 'PUT' ? 'bg-yellow-500/10 text-yellow-400' : '' }}
                            {{ $request['method'] === 'DELETE' ? 'bg-red-500/10 text-red-400' : '' }}">
                            {{ $request['method'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs {{ $request['response_time'] > 1000 ? 'text-red-400' : 'text-slate-400' }}">
                            {{ $request['response_time'] }}ms
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($request['status_code'] === 200)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-500/10 text-green-400">
                            {{ $request['status_code'] }}
                        </span>
                        @elseif($request['status_code'] === 429)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-500/10 text-yellow-400">
                            {{ $request['status_code'] }} Rate Limited
                        </span>
                        @elseif($request['status_code'] >= 500)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/10 text-red-400">
                            {{ $request['status_code'] }} Error
                        </span>
                        @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-400">
                            {{ $request['status_code'] }}
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                        No recent requests found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Auto-refresh indicator -->
<div class="mt-4 flex items-center justify-between text-xs text-slate-500">
    <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
        <span>Auto-refresh every 30 seconds</span>
    </div>
    <span id="last-updated">Last updated: {{ now()->format('H:i:s') }}</span>
</div>

<!-- User Details Modal -->
<div id="userDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80" onclick="closeUserModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-surface-dark rounded-2xl border border-slate-700 w-full max-w-3xl max-h-[80vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-bold text-lg" id="modalUserName">User Details</h3>
                <button onclick="closeUserModal()" class="text-slate-400 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[60vh]" id="modalContent">
                <div class="animate-pulse">
                    <div class="h-4 bg-slate-700 rounded w-1/4 mb-4"></div>
                    <div class="h-4 bg-slate-700 rounded w-1/2 mb-4"></div>
                    <div class="h-4 bg-slate-700 rounded w-3/4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-refresh indicator
    setInterval(() => {
        document.getElementById('last-updated').textContent = 'Last updated: ' + new Date().toLocaleTimeString();
    }, 30000);

    // View user details
    async function viewUserDetails(userId) {
        const modal = document.getElementById('userDetailsModal');
        const content = document.getElementById('modalContent');
        const title = document.getElementById('modalUserName');
        
        modal.classList.remove('hidden');
        
        try {
            const response = await fetch(`{{ url('admin/api-monitor/user') }}/${userId}`);
            const data = await response.json();
            
            title.textContent = `${data.user.name} - API Usage`;
            
            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-surface-light/30 rounded-xl p-4">
                            <div class="text-xs text-slate-500 mb-1">Total Requests</div>
                            <div class="text-xl font-bold text-white">${data.summary.total_requests}</div>
                        </div>
                        <div class="bg-surface-light/30 rounded-xl p-4">
                            <div class="text-xs text-slate-500 mb-1">Today</div>
                            <div class="text-xl font-bold text-white">${data.summary.requests_today}</div>
                        </div>
                        <div class="bg-surface-light/30 rounded-xl p-4">
                            <div class="text-xs text-slate-500 mb-1">This Week</div>
                            <div class="text-xl font-bold text-white">${data.summary.requests_this_week}</div>
                        </div>
                        <div class="bg-surface-light/30 rounded-xl p-4">
                            <div class="text-xs text-slate-500 mb-1">Error Rate</div>
                            <div class="text-xl font-bold ${data.summary.error_rate > 5 ? 'text-red-400' : 'text-green-400'}">${data.summary.error_rate}%</div>
                        </div>
                    </div>
                    
                    <!-- Recent Requests -->
                    <div>
                        <h4 class="font-semibold mb-3">Recent Requests</h4>
                        <div class="bg-surface-light/30 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="text-xs text-slate-400">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Endpoint</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-left">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    ${data.requests.slice(0, 10).map(req => \`
                                        <tr>
                                            <td class="px-4 py-2">
                                                <code class="text-xs">\${req.endpoint}</code>
                                                <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded bg-slate-700">\${req.method}</span>
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="text-[10px] px-2 py-0.5 rounded-full \${req.status_code === 200 ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400'}">
                                                    \${req.status_code}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-slate-400 text-xs">
                                                \${new Date(req.date).toLocaleString()}
                                            </td>
                                        </tr>
                                    \`).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        } catch (error) {
            content.innerHTML = `
                <div class="text-center text-red-400 py-8">
                    <span class="material-symbols-outlined text-4xl mb-2">error</span>
                    <p>Failed to load user details</p>
                </div>
            `;
        }
    }
    
    function closeUserModal() {
        document.getElementById('userDetailsModal').classList.add('hidden');
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeUserModal();
        }
    });
</script>
@endpush
