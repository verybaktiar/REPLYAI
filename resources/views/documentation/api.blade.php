@extends('layouts.dark')

@section('title', 'API Documentation')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">API Documentation</h1>
        <p class="text-slate-400">Integrasikan REPLYAI dengan aplikasi Anda menggunakan API kami.</p>
    </div>
    
    <!-- API Key Info -->
    <div class="bg-slate-800/50 rounded-2xl border border-slate-700 p-6 mb-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-2xl">key</span>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-1">Authentication</h2>
                <p class="text-sm text-slate-400 mb-3">
                    Semua API request memerlukan authentication. Sertakan API key di header:
                </p>
                <code class="block p-3 bg-slate-900 rounded-lg text-sm font-mono">
                    Authorization: Bearer {your_api_key}
                </code>
            </div>
        </div>
    </div>
    
    <!-- Base URL -->
    <div class="bg-slate-800/50 rounded-2xl border border-slate-700 p-6 mb-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-green-500 text-2xl">link</span>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-1">Base URL</h2>
                <code class="block p-3 bg-slate-900 rounded-lg text-sm font-mono">
                    {{ config('app.url') }}/api
                </code>
            </div>
        </div>
    </div>
    
    <!-- Download OpenAPI -->
    <div class="flex gap-4 mb-8">
        <a href="{{ route('api.openapi') }}" target="_blank" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 rounded-lg transition">
            <span class="material-symbols-outlined text-sm">download</span>
            <span>Download OpenAPI Spec (JSON)</span>
        </a>
    </div>
    
    <!-- Endpoints -->
    <div class="space-y-8">
        @foreach($endpoints as $group)
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700 overflow-hidden">
            <div class="p-6 border-b border-slate-700">
                <h2 class="text-xl font-semibold">{{ $group['group'] }}</h2>
            </div>
            
            <div class="divide-y divide-slate-700">
                @foreach($group['endpoints'] as $endpoint)
                <div class="p-6" id="{{ Str::slug($endpoint['path']) }}">
                    <div class="flex items-start gap-4 mb-4">
                        <span class="px-3 py-1 rounded-lg text-sm font-bold
                            {{ $endpoint['method'] === 'GET' ? 'bg-blue-500/20 text-blue-400' : '' }}
                            {{ $endpoint['method'] === 'POST' ? 'bg-green-500/20 text-green-400' : '' }}
                            {{ $endpoint['method'] === 'PUT' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                            {{ $endpoint['method'] === 'PATCH' ? 'bg-orange-500/20 text-orange-400' : '' }}
                            {{ $endpoint['method'] === 'DELETE' ? 'bg-red-500/20 text-red-400' : '' }}">
                            {{ $endpoint['method'] }}
                        </span>
                        <code class="text-sm font-mono text-slate-300">{{ $endpoint['path'] }}</code>
                        @if(isset($endpoint['auth']) && !$endpoint['auth'])
                        <span class="px-2 py-0.5 bg-slate-700 rounded text-xs">Public</span>
                        @endif
                    </div>
                    
                    <p class="text-slate-400 mb-4">{{ $endpoint['description'] }}</p>
                    
                    @if(!empty($endpoint['params']))
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-slate-300 mb-2">Parameters</h3>
                        <div class="bg-slate-900 rounded-lg p-4">
                            @foreach($endpoint['params'] as $name => $type)
                            <div class="flex items-center gap-2 text-sm">
                                <code class="text-primary">{{ $name }}</code>
                                <span class="text-slate-500">-</span>
                                <span class="text-slate-400">{{ $type }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if(!empty($endpoint['response']))
                    <div>
                        <h3 class="text-sm font-semibold text-slate-300 mb-2">Response</h3>
                        <div class="bg-slate-900 rounded-lg p-4">
                            <pre class="text-sm text-slate-400 overflow-x-auto">{{ json_encode($endpoint['response'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Rate Limiting -->
    <div class="mt-8 bg-yellow-500/10 border border-yellow-500/20 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-yellow-500 text-2xl">speed</span>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-1 text-yellow-400">Rate Limiting</h2>
                <p class="text-sm text-slate-400">
                    API memiliki rate limit 60 request per menit. Jika limit terlampaui, 
                    Anda akan menerima response 429 Too Many Requests.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Error Codes -->
    <div class="mt-8 bg-slate-800/50 rounded-2xl border border-slate-700 p-6">
        <h2 class="text-lg font-semibold mb-4">Error Codes</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-3 p-3 bg-slate-900 rounded-lg">
                <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded text-sm font-bold">400</span>
                <span class="text-sm text-slate-400">Bad Request</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-900 rounded-lg">
                <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded text-sm font-bold">401</span>
                <span class="text-sm text-slate-400">Unauthorized</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-900 rounded-lg">
                <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded text-sm font-bold">403</span>
                <span class="text-sm text-slate-400">Forbidden</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-900 rounded-lg">
                <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded text-sm font-bold">404</span>
                <span class="text-sm text-slate-400">Not Found</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-900 rounded-lg">
                <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded text-sm font-bold">429</span>
                <span class="text-sm text-slate-400">Too Many Requests</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-900 rounded-lg">
                <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded text-sm font-bold">500</span>
                <span class="text-sm text-slate-400">Server Error</span>
            </div>
        </div>
    </div>
</div>
@endsection
