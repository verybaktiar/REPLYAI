@extends('admin.layouts.app')

@section('title', 'Webhook & Integration Logs')
@section('page_title', 'Webhook & Integration Logs')

@section('content')

@if(!$tableExists)
<div class="mb-6 p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 flex items-center gap-3">
    <span class="material-symbols-outlined">info</span>
    <div>
        <p class="font-medium">Development Mode</p>
        <p class="text-sm text-yellow-500/80">The webhook_logs table does not exist. Showing mock data for demonstration purposes.</p>
    </div>
</div>
@endif

{{-- Integration Health Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach($integrations as $key => $integration)
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800 hover:border-slate-700 transition">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $integration['color'] }}-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-{{ $integration['color'] }}-500">{{ $integration['icon'] }}</span>
                </div>
                <div>
                    <h3 class="font-semibold text-white">{{ $integration['name'] }}</h3>
                    <span class="text-xs text-slate-400">Integration</span>
                </div>
            </div>
            <span class="px-2 py-1 rounded-full text-xs font-bold 
                {{ $integration['status'] === 'healthy' ? 'bg-green-500/20 text-green-400' : 
                   ($integration['status'] === 'unconfigured' ? 'bg-gray-500/20 text-gray-400' : 'bg-red-500/20 text-red-400') }}">
                {{ ucfirst($integration['status']) }}
            </span>
        </div>
        @if(isset($integration['last_check']))
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span>Last checked</span>
            <span>{{ \Carbon\Carbon::parse($integration['last_check'])->diffForHumans() }}</span>
        </div>
        @endif
        @if(isset($integration['response_time']))
        <div class="flex items-center justify-between text-xs text-slate-500 mt-1">
            <span>Response time</span>
            <span class="text-green-400">{{ $integration['response_time'] }}</span>
        </div>
        @endif
        @if(isset($integration['error']))
        <div class="mt-2 text-xs text-red-400 truncate" title="{{ $integration['error'] }}">
            {{ $integration['error'] }}
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-slate-400">webhook</span>
            <span class="text-sm text-slate-400">Total Webhooks</span>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="bg-green-500/5 rounded-xl p-5 border border-green-500/20">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-green-500">check_circle</span>
            <span class="text-sm text-green-400/80">Successful</span>
        </div>
        <div class="text-2xl font-black text-green-500">{{ number_format($stats['successful']) }}</div>
    </div>
    <div class="bg-red-500/5 rounded-xl p-5 border border-red-500/20">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-red-500">error</span>
            <span class="text-sm text-red-400/80">Failed</span>
        </div>
        <div class="text-2xl font-black text-red-500">{{ number_format($stats['failed']) }}</div>
    </div>
    <div class="bg-yellow-500/5 rounded-xl p-5 border border-yellow-500/20">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-yellow-500">pending</span>
            <span class="text-sm text-yellow-400/80">Pending</span>
        </div>
        <div class="text-2xl font-black text-yellow-500">{{ number_format($stats['pending']) }}</div>
    </div>
</div>

{{-- Filters & Actions --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <form method="GET" action="{{ route('admin.webhook-logs.index') }}" class="flex flex-wrap items-center gap-3">
        <select name="provider" class="bg-surface-dark border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
            <option value="">All Providers</option>
            @foreach($providers as $provider)
            <option value="{{ $provider }}" {{ request('provider') == $provider ? 'selected' : '' }}>
                {{ ucfirst($provider) }}
            </option>
            @endforeach
        </select>
        
        <select name="status" class="bg-surface-dark border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary">
            <option value="">All Status</option>
            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
        
        <input type="text" name="search" value="{{ request('search') }}" 
               placeholder="Search URL..." 
               class="bg-surface-dark border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-primary w-48">
        
        <button type="submit" class="px-4 py-2 bg-surface-light hover:bg-surface-light/80 rounded-lg text-sm text-white transition">
            <span class="material-symbols-outlined text-lg">search</span>
        </button>
        
        @if(request()->hasAny(['provider', 'status', 'search']))
        <a href="{{ route('admin.webhook-logs.index') }}" class="px-4 py-2 text-slate-400 hover:text-white transition">
            <span class="material-symbols-outlined text-lg">clear</span>
        </a>
        @endif
    </form>
    
    <div class="flex items-center gap-3">
        <form action="{{ route('admin.webhook-logs.retry-all') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/80 rounded-lg font-medium transition text-sm" 
                    onclick="return confirm('Retry all failed webhooks?')"
                    {{ $stats['failed'] == 0 ? 'disabled' : '' }}>
                <span class="material-symbols-outlined text-lg">restart_alt</span>
                Retry All Failed
            </button>
        </form>
    </div>
</div>

{{-- Webhook Logs Table --}}
<div class="bg-surface-dark rounded-xl overflow-hidden border border-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-surface-light">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">ID</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Provider</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">URL</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Status</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">HTTP</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Created</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($webhookLogs as $log)
                <tr class="hover:bg-surface-light/50 transition">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm text-slate-500">#{{ $log->id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-slate-800 rounded text-xs font-medium text-slate-300">
                            {{ ucfirst($log->provider) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-300 truncate max-w-xs" title="{{ $log->url }}">
                            {{ $log->url }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($log->status == 'success')
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-500/10 text-green-400 rounded text-xs font-medium">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            Success
                        </span>
                        @elseif($log->status == 'failed')
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-500/10 text-red-400 rounded text-xs font-medium">
                            <span class="material-symbols-outlined text-xs">error</span>
                            Failed
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-500/10 text-yellow-400 rounded text-xs font-medium">
                            <span class="material-symbols-outlined text-xs">pending</span>
                            Pending
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($log->http_status)
                        <span class="font-mono text-sm {{ $log->http_status >= 200 && $log->http_status < 300 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $log->http_status }}
                        </span>
                        @else
                        <span class="text-slate-500">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-300">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</div>
                        <div class="text-xs text-slate-500">{{ $log->created_at }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            @if($log->status == 'failed')
                            <form action="{{ route('admin.webhook-logs.retry', $log->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition" title="Retry">
                                    <span class="material-symbols-outlined">restart_alt</span>
                                </button>
                            </form>
                            @endif
                            <button onclick="showPayloadModal({{ $log->id }})" 
                                    class="p-2 text-slate-400 hover:bg-slate-700 rounded-lg transition" 
                                    title="View Payload">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                            <a href="{{ route('admin.webhook-logs.show', $log->id) }}" 
                               class="p-2 text-slate-400 hover:bg-slate-700 rounded-lg transition" 
                               title="View Details">
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl">webhook</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white">No Webhook Logs</h3>
                                <p class="text-slate-400">No webhook logs found matching your criteria.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($webhookLogs->hasPages())
    <div class="px-6 py-4 border-t border-slate-800 bg-surface-light/30">
        {{ $webhookLogs->links() }}
    </div>
    @endif
</div>

{{-- Payload Modal --}}
<div id="payloadModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closePayloadModal()"></div>
    <div class="absolute inset-4 md:inset-10 bg-surface-dark rounded-2xl border border-slate-700 flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700">
            <h3 class="font-bold text-lg">Webhook Payload</h3>
            <button onclick="closePayloadModal()" class="p-2 text-slate-400 hover:text-white transition">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-auto p-6">
            <pre id="payloadContent" class="text-sm text-green-400 font-mono bg-slate-950 p-4 rounded-lg overflow-x-auto"></pre>
        </div>
    </div>
</div>

<script>
// Store payloads for modal display
const webhookPayloads = {};

@foreach($webhookLogs as $log)
webhookPayloads[{{ $log->id }}] = {
    payload: {!! json_encode($log->payload) !!},
    response: {!! json_encode($log->response) !!},
    headers: {!! json_encode($log->headers ?? '{}') !!}
};
@endforeach

function showPayloadModal(id) {
    const data = webhookPayloads[id];
    if (!data) return;
    
    let content = '';
    
    // Headers
    if (data.headers && data.headers !== '{}') {
        content += '<div class="mb-4"><h4 class="text-sm font-bold text-slate-300 mb-2">Headers</h4>';
        content += '<pre class="text-xs text-blue-400 font-mono bg-slate-950 p-3 rounded-lg overflow-x-auto">' + 
                   formatJSON(data.headers) + '</pre></div>';
    }
    
    // Payload
    content += '<div class="mb-4"><h4 class="text-sm font-bold text-slate-300 mb-2">Request Payload</h4>';
    content += '<pre class="text-xs text-green-400 font-mono bg-slate-950 p-3 rounded-lg overflow-x-auto">' + 
               formatJSON(data.payload) + '</pre></div>';
    
    // Response
    if (data.response) {
        content += '<div><h4 class="text-sm font-bold text-slate-300 mb-2">Response</h4>';
        content += '<pre class="text-xs text-yellow-400 font-mono bg-slate-950 p-3 rounded-lg overflow-x-auto">' + 
                   formatJSON(data.response) + '</pre></div>';
    }
    
    document.getElementById('payloadContent').innerHTML = content;
    document.getElementById('payloadModal').classList.remove('hidden');
}

function closePayloadModal() {
    document.getElementById('payloadModal').classList.add('hidden');
}

function formatJSON(jsonStr) {
    try {
        const obj = typeof jsonStr === 'string' ? JSON.parse(jsonStr) : jsonStr;
        return JSON.stringify(obj, null, 2);
    } catch (e) {
        return jsonStr || 'No data';
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePayloadModal();
    }
});
</script>

@endsection
