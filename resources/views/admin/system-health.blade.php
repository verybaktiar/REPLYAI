@extends('admin.layouts.app')

@section('title', 'System Health')
@section('page_title', 'System Health')

@section('content')
@php
    // Check Database Connection
    $dbStatus = true;
    $dbMessage = 'Connected';
    try {
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        $dbStatus = false;
        $dbMessage = 'Connection failed';
    }
    
    // Check Redis/Cache
    $cacheStatus = true;
    $cacheMessage = 'Working';
    try {
        Cache::put('health_check', 'ok', 10);
        $cacheStatus = Cache::get('health_check') === 'ok';
        $cacheMessage = $cacheStatus ? 'Working' : 'Not working';
    } catch (\Exception $e) {
        $cacheStatus = false;
        $cacheMessage = 'Not configured';
    }
    
    // Check Queue
    $queueDriver = config('queue.default');
    $queueStatus = $queueDriver !== 'sync';
    $queueMessage = ucfirst($queueDriver);
    
    // Check Disk Space
    $diskTotal = disk_total_space(base_path());
    $diskFree = disk_free_space(base_path());
    $diskUsedPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100);
    $diskStatus = $diskUsedPercent < 90;
    
    // Check Instagram Webhook Config
    $igWebhookUrl = config('services.instagram.webhook_url');
    $igConfigured = !empty(config('services.instagram.app_id')) && !empty(config('services.instagram.app_secret'));
    
    // Check WhatsApp Config
    $waConfigured = !empty(config('services.fonnte.token'));
    
    // Recent Logs Summary
    $recentErrorCount = 0;
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        $today = now()->format('Y-m-d');
        $recentErrorCount = substr_count($logContent, "[$today ") && substr_count($logContent, '.ERROR:');
    }
    
    // Active Users (last 24h)
    $activeUsers24h = \App\Models\User::where('updated_at', '>=', now()->subDay())->count();
    
    // Instagram Accounts Status
    $activeIgAccounts = \App\Models\InstagramAccount::where('is_active', true)->count();
    $expiringTokens = \App\Models\InstagramAccount::where('is_active', true)
        ->whereNotNull('token_expires_at')
        ->where('token_expires_at', '<=', now()->addDays(7))
        ->count();
@endphp

<!-- Status Overview -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
    <!-- Database -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-3 h-3 rounded-full {{ $dbStatus ? 'bg-green-500' : 'bg-red-500' }} animate-pulse"></div>
            <span class="text-sm font-medium">Database</span>
        </div>
        <p class="text-xs text-slate-500">{{ $dbMessage }}</p>
    </div>
    
    <!-- Cache -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-3 h-3 rounded-full {{ $cacheStatus ? 'bg-green-500' : 'bg-yellow-500' }}"></div>
            <span class="text-sm font-medium">Cache</span>
        </div>
        <p class="text-xs text-slate-500">{{ $cacheMessage }}</p>
    </div>
    
    <!-- Queue -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-3 h-3 rounded-full {{ $queueStatus ? 'bg-green-500' : 'bg-yellow-500' }}"></div>
            <span class="text-sm font-medium">Queue</span>
        </div>
        <p class="text-xs text-slate-500">{{ $queueMessage }}</p>
    </div>
    
    <!-- Disk -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-3 h-3 rounded-full {{ $diskStatus ? 'bg-green-500' : 'bg-red-500' }}"></div>
            <span class="text-sm font-medium">Disk</span>
        </div>
        <p class="text-xs text-slate-500">{{ $diskUsedPercent }}% used</p>
    </div>
    
    <!-- Instagram API -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-3 h-3 rounded-full {{ $igConfigured ? 'bg-green-500' : 'bg-red-500' }}"></div>
            <span class="text-sm font-medium">Instagram</span>
        </div>
        <p class="text-xs text-slate-500">{{ $igConfigured ? 'Configured' : 'Not configured' }}</p>
    </div>
    
    <!-- WhatsApp API -->
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-3 h-3 rounded-full {{ $waConfigured ? 'bg-green-500' : 'bg-red-500' }}"></div>
            <span class="text-sm font-medium">WhatsApp</span>
        </div>
        <p class="text-xs text-slate-500">{{ $waConfigured ? 'Configured' : 'Not configured' }}</p>
    </div>
</div>

<!-- Detailed Stats -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Instagram Accounts -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
            <span>📸</span> Instagram Accounts
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Active Accounts</span>
                <span class="font-bold text-green-400">{{ $activeIgAccounts }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Expiring Tokens (7 days)</span>
                <span class="font-bold {{ $expiringTokens > 0 ? 'text-yellow-400' : 'text-slate-500' }}">{{ $expiringTokens }}</span>
            </div>
        </div>
        @if($expiringTokens > 0)
        <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
            <p class="text-yellow-400 text-sm">⚠️ {{ $expiringTokens }} token(s) will expire soon. Run <code class="bg-slate-800 px-1 rounded">php artisan instagram:refresh-tokens</code></p>
        </div>
        @endif
    </div>
    
    <!-- System Activity -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
            <span>📊</span> System Activity
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Active Users (24h)</span>
                <span class="font-bold text-primary">{{ $activeUsers24h }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Disk Usage</span>
                <div class="flex items-center gap-2">
                    <div class="w-24 h-2 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full {{ $diskUsedPercent > 80 ? 'bg-red-500' : ($diskUsedPercent > 60 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ $diskUsedPercent }}%"></div>
                    </div>
                    <span class="text-sm">{{ $diskUsedPercent }}%</span>
                </div>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">PHP Version</span>
                <span class="font-mono text-sm">{{ PHP_VERSION }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Laravel Version</span>
                <span class="font-mono text-sm">{{ app()->version() }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
    <h3 class="font-bold text-lg mb-4">🔧 Maintenance Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <form method="POST" action="{{ route('admin.maintenance.clear-cache') }}" class="inline">
            @csrf
            <button type="submit" class="w-full p-4 bg-surface-light hover:bg-primary/10 rounded-xl border border-slate-800 hover:border-primary/50 transition text-left">
                <div class="text-lg mb-1">🗑️</div>
                <div class="font-semibold text-sm">Clear Cache</div>
                <div class="text-xs text-slate-500">Application cache</div>
            </button>
        </form>
        
        <form method="POST" action="{{ route('admin.maintenance.clear-views') }}" class="inline">
            @csrf
            <button type="submit" class="w-full p-4 bg-surface-light hover:bg-primary/10 rounded-xl border border-slate-800 hover:border-primary/50 transition text-left">
                <div class="text-lg mb-1">🖼️</div>
                <div class="font-semibold text-sm">Clear Views</div>
                <div class="text-xs text-slate-500">Compiled views</div>
            </button>
        </form>
        
        <form method="POST" action="{{ route('admin.maintenance.refresh-tokens') }}" class="inline">
            @csrf
            <button type="submit" class="w-full p-4 bg-surface-light hover:bg-primary/10 rounded-xl border border-slate-800 hover:border-primary/50 transition text-left">
                <div class="text-lg mb-1">🔄</div>
                <div class="font-semibold text-sm">Refresh IG Tokens</div>
                <div class="text-xs text-slate-500">Instagram tokens</div>
            </button>
        </form>
        
        <a href="{{ route('admin.activity-logs.index') }}" class="w-full p-4 bg-surface-light hover:bg-primary/10 rounded-xl border border-slate-800 hover:border-primary/50 transition text-left block">
            <div class="text-lg mb-1">📋</div>
            <div class="font-semibold text-sm">View Logs</div>
            <div class="text-xs text-slate-500">Activity logs</div>
        </a>
    </div>
</div>

@if(session('success'))
<script>
    alert('{{ session('success') }}');
</script>
@endif
@endsection
