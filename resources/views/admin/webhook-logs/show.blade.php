@extends('admin.layouts.app')

@section('title', 'Webhook Details #' . $webhook->id)
@section('page_title', 'Webhook Details #' . $webhook->id)

@section('content')

@if(!$tableExists)
<div class="mb-6 p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 flex items-center gap-3">
    <span class="material-symbols-outlined">info</span>
    <div>
        <p class="font-medium">Development Mode</p>
        <p class="text-sm text-yellow-500/80">Showing mock data for demonstration purposes.</p>
    </div>
</div>
@endif

{{-- Header with Actions --}}
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.webhook-logs.index') }}" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-white">Webhook #{{ $webhook->id }}</h2>
                @if($webhook->status == 'success')
                <span class="px-3 py-1 bg-green-500/10 text-green-400 rounded-full text-xs font-bold border border-green-500/20">
                    Success
                </span>
                @elseif($webhook->status == 'failed')
                <span class="px-3 py-1 bg-red-500/10 text-red-400 rounded-full text-xs font-bold border border-red-500/20">
                    Failed
                </span>
                @else
                <span class="px-3 py-1 bg-yellow-500/10 text-yellow-400 rounded-full text-xs font-bold border border-yellow-500/20">
                    Pending
                </span>
                @endif
            </div>
            <p class="text-sm text-slate-400">{{ $webhook->url }}</p>
        </div>
    </div>
    
    <div class="flex items-center gap-3">
        @if($webhook->status == 'failed')
        <form action="{{ route('admin.webhook-logs.retry', $webhook->id) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/80 rounded-lg font-medium transition">
                <span class="material-symbols-outlined">restart_alt</span>
                Retry Webhook
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Webhook Info Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="text-sm text-slate-400 mb-1">Provider</div>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-500">webhook</span>
            <span class="text-lg font-bold text-white">{{ ucfirst($webhook->provider) }}</span>
        </div>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="text-sm text-slate-400 mb-1">HTTP Status</div>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined {{ $webhook->http_status >= 200 && $webhook->http_status < 300 ? 'text-green-500' : 'text-red-500' }}">
                {{ $webhook->http_status >= 200 && $webhook->http_status < 300 ? 'check_circle' : 'error' }}
            </span>
            <span class="text-lg font-bold {{ $webhook->http_status >= 200 && $webhook->http_status < 300 ? 'text-green-500' : 'text-red-500' }}">
                {{ $webhook->http_status ?? 'N/A' }}
            </span>
        </div>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="text-sm text-slate-400 mb-1">Retry Count</div>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-500">repeat</span>
            <span class="text-lg font-bold text-white">{{ $webhook->retry_count ?? 0 }}</span>
        </div>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="text-sm text-slate-400 mb-1">Created At</div>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-500">schedule</span>
            <span class="text-lg font-bold text-white">{{ \Carbon\Carbon::parse($webhook->created_at)->diffForHumans() }}</span>
        </div>
    </div>
</div>

{{-- Timestamps --}}
<div class="bg-surface-dark rounded-xl p-5 border border-slate-800 mb-6">
    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-slate-400">event</span>
        Timeline
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <div class="text-sm text-slate-400">Created</div>
            <div class="text-sm text-white font-mono">{{ $webhook->created_at }}</div>
        </div>
        <div>
            <div class="text-sm text-slate-400">Updated</div>
            <div class="text-sm text-white font-mono">{{ $webhook->updated_at }}</div>
        </div>
        <div>
            <div class="text-sm text-slate-400">Processing Time</div>
            <div class="text-sm text-white">
                @if($webhook->created_at && $webhook->updated_at)
                {{ \Carbon\Carbon::parse($webhook->created_at)->diffInMilliseconds(\Carbon\Carbon::parse($webhook->updated_at)) }}ms
                @else
                -
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Request Payload --}}
    <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <h3 class="font-semibold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-500">arrow_upward</span>
                Request Payload
            </h3>
            <button onclick="copyToClipboard('requestPayload')" class="p-2 text-slate-400 hover:text-white transition" title="Copy to clipboard">
                <span class="material-symbols-outlined text-lg">content_copy</span>
            </button>
        </div>
        <div class="p-0">
            <pre id="requestPayload" class="text-sm text-green-400 font-mono bg-slate-950 p-5 overflow-x-auto max-h-96">{{ format_json($webhook->payload) }}</pre>
        </div>
    </div>
    
    {{-- Response --}}
    <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <h3 class="font-semibold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-500">arrow_downward</span>
                Response
            </h3>
            <button onclick="copyToClipboard('responsePayload')" class="p-2 text-slate-400 hover:text-white transition" title="Copy to clipboard">
                <span class="material-symbols-outlined text-lg">content_copy</span>
            </button>
        </div>
        <div class="p-0">
            <pre id="responsePayload" class="text-sm text-yellow-400 font-mono bg-slate-950 p-5 overflow-x-auto max-h-96">{{ format_json($webhook->response) }}</pre>
        </div>
    </div>
</div>

{{-- Headers --}}
@if($webhook->headers && $webhook->headers !== '{}')
<div class="mt-6 bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
        <h3 class="font-semibold text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-500">http</span>
            Headers
        </h3>
        <button onclick="copyToClipboard('headersContent')" class="p-2 text-slate-400 hover:text-white transition" title="Copy to clipboard">
            <span class="material-symbols-outlined text-lg">content_copy</span>
        </button>
    </div>
    <div class="p-0">
        <pre id="headersContent" class="text-sm text-blue-400 font-mono bg-slate-950 p-5 overflow-x-auto max-h-64">{{ format_json($webhook->headers) }}</pre>
    </div>
</div>
@endif

{{-- Error Details (if failed) --}}
@if($webhook->status == 'failed')
<div class="mt-6 bg-red-500/5 rounded-xl border border-red-500/20 overflow-hidden">
    <div class="px-5 py-4 border-b border-red-500/20">
        <h3 class="font-semibold text-red-400 flex items-center gap-2">
            <span class="material-symbols-outlined">error</span>
            Error Details
        </h3>
    </div>
    <div class="p-5">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-red-500 mt-0.5">warning</span>
            <div>
                <p class="text-red-400 font-medium">Webhook Delivery Failed</p>
                <p class="text-sm text-red-400/70 mt-1">
                    This webhook failed to deliver. You can retry it using the button above.
                    @if($webhook->retry_count > 0)
                    This webhook has been retried {{ $webhook->retry_count }} time(s).
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Actions Footer --}}
<div class="mt-8 flex items-center justify-between">
    <a href="{{ route('admin.webhook-logs.index') }}" class="flex items-center gap-2 px-4 py-2 text-slate-400 hover:text-white transition">
        <span class="material-symbols-outlined">arrow_back</span>
        Back to List
    </a>
    
    @if($webhook->status == 'failed')
    <form action="{{ route('admin.webhook-logs.retry', $webhook->id) }}" method="POST" class="inline">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-primary hover:bg-primary/80 rounded-lg font-bold transition shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined">restart_alt</span>
            Retry Webhook
        </button>
    </form>
    @endif
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.innerText;
    
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Failed to copy', 'error');
    });
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    const colors = type === 'success' ? 'bg-green-500/20 border-green-500/50 text-green-300' : 
                   type === 'error' ? 'bg-red-500/20 border-red-500/50 text-red-300' : 
                   'bg-blue-500/20 border-blue-500/50 text-blue-300';
    
    toast.className = `px-4 py-3 rounded-xl border ${colors} flex items-center gap-2 animate-fade-in`;
    toast.innerHTML = `
        <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</span>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        toast.style.transition = 'all 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed bottom-4 right-4 z-50 space-y-2';
    document.body.appendChild(container);
    return container;
}
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>

@php
function format_json($json) {
    if (empty($json)) return 'No data';
    try {
        $decoded = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    } catch (\Exception $e) {
        // Fall through to return raw
    }
    return $json;
}
@endphp

@endsection
