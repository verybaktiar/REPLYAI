@extends('admin.layouts.app')

@section('title', 'AI Provider Monitor')
@section('page_title', 'AI Provider Monitor')

@section('content')

<!-- Header Stats -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-3xl text-blue-500">psychology</span>
        <div>
            <h1 class="text-xl font-bold text-white">AI Provider Monitoring</h1>
            <p class="text-sm text-slate-400">Real-time failover system status</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <span class="px-3 py-1.5 rounded-lg bg-blue-500/10 text-blue-400 text-xs font-medium border border-blue-500/20">
            Primary: {{ strtoupper($config['primary']) }}
        </span>
        <span class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 text-xs font-medium border border-slate-700">
            Secondary: {{ strtoupper($config['secondary']) }}
        </span>
        @if($config['failover_enabled'])
            <span class="px-3 py-1.5 rounded-lg bg-green-500/10 text-green-400 text-xs font-medium border border-green-500/20 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                Failover ON
            </span>
        @else
            <span class="px-3 py-1.5 rounded-lg bg-yellow-500/10 text-yellow-400 text-xs font-medium border border-yellow-500/20">
                Failover OFF
            </span>
        @endif
    </div>
</div>

@php
$primaryProvider = $config['primary'];
$primaryStatus = $providers[$primaryProvider] ?? null;
$hasIssues = $primaryStatus && (!$primaryStatus['healthy'] || $primaryStatus['failures'] > 0);
@endphp

<!-- Emergency Switch Alert - Show when primary has issues -->
@if($hasIssues)
<div class="mb-8 bg-red-500/10 border border-red-500/30 rounded-2xl p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-2xl text-red-400">warning</span>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white mb-1">Primary Provider Issues Detected</h3>
                <p class="text-slate-400 text-sm mb-2">
                    <strong class="text-red-400">{{ strtoupper($primaryProvider) }}</strong> is experiencing problems 
                    ({{ $primaryStatus['failures'] }} failures). 
                    Chat responses may be delayed.
                </p>
                @if($primaryStatus['last_error'])
                    <p class="text-xs text-red-400/80 bg-red-500/10 rounded-lg px-3 py-2">
                        Last error: {{ Str::limit($primaryStatus['last_error'], 80) }}
                    </p>
                @endif
            </div>
        </div>
        @php
            $otherProvider = $primaryProvider === 'megallm' ? 'sumopod' : 'megallm';
            $otherStatus = $providers[$otherProvider] ?? null;
        @endphp
        @if($otherStatus && $otherStatus['available'] && $otherStatus['healthy'])
        <div class="flex-shrink-0">
            <button type="button" id="btn-emergency-switch" data-provider="{{ $otherProvider }}"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold transition shadow-lg shadow-red-500/25 cursor-pointer">
                <span class="material-symbols-outlined">swap_horiz</span>
                Switch to {{ strtoupper($otherProvider) }}
            </button>
        </div>
        @endif
    </div>
</div>
@endif

<!-- Quick Switch Section -->
<div class="mb-8 bg-surface-dark rounded-2xl border border-slate-800 p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-400">swap_vert</span>
            <h3 class="font-bold text-white">Quick Provider Switch</h3>
        </div>
        <span class="text-xs text-slate-500">Manual override default provider</span>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($providers as $name => $status)
        @php
            $isCurrentPrimary = $config['primary'] === $name;
            $canSwitch = $status['available'] && $status['healthy'];
        @endphp
        <div class="relative rounded-xl border {{ $isCurrentPrimary ? 'border-blue-500/50 bg-blue-500/5' : 'border-slate-800 bg-slate-800/30' }} p-4">
            @if($isCurrentPrimary)
                <div class="absolute -top-2 -right-2">
                    <span class="px-2 py-1 rounded-lg bg-blue-500 text-white text-[10px] font-bold">ACTIVE</span>
                </div>
            @endif
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $canSwitch ? 'bg-green-500/10' : 'bg-red-500/10' }} flex items-center justify-center">
                        <span class="material-symbols-outlined {{ $canSwitch ? 'text-green-400' : 'text-red-400' }}">
                            {{ $canSwitch ? 'check_circle' : 'error' }}
                        </span>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">{{ strtoupper($name) }}</h4>
                        <p class="text-xs {{ $canSwitch ? 'text-green-400' : 'text-red-400' }}">
                            {{ $canSwitch ? 'Ready' : 'Unavailable' }}
                        </p>
                    </div>
                </div>
                
                @if(!$isCurrentPrimary && $canSwitch)
                    <button type="button" data-provider="{{ $name }}" class="btn-switch-provider"
                            class="px-4 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium transition cursor-pointer">
                        Set as Default
                    </button>
                @elseif($isCurrentPrimary)
                    <span class="px-4 py-2 rounded-lg bg-slate-700 text-slate-400 text-sm font-medium cursor-not-allowed select-none">
                        Current Default
                    </span>
                @else
                    <span class="px-4 py-2 rounded-lg bg-slate-800 text-slate-500 text-sm font-medium cursor-not-allowed select-none">
                        Cannot Switch
                    </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Provider Cards -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    @foreach($providers as $name => $status)
    @php
        $isHealthy = $status['healthy'] && $status['available'];
        $isWarning = $status['available'] && !$status['healthy'];
        $borderColor = $isHealthy ? 'border-green-500/30' : ($isWarning ? 'border-yellow-500/30' : 'border-red-500/30');
        $headerBg = $isHealthy ? 'bg-green-500/5' : ($isWarning ? 'bg-yellow-500/5' : 'bg-red-500/5');
        $statusColor = $isHealthy ? 'text-green-400' : ($isWarning ? 'text-yellow-400' : 'text-red-400');
        $statusIcon = $isHealthy ? 'check_circle' : ($isWarning ? 'warning' : 'error');
        $statusText = $isHealthy ? 'Healthy' : ($isWarning ? 'Degraded' : 'Unavailable');
    @endphp
    <div class="bg-surface-dark rounded-2xl border {{ $borderColor }} overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-slate-800 {{ $headerBg }} flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl {{ $statusColor }}">{{ $statusIcon }}</span>
                </div>
                <div>
                    <h3 class="font-bold text-white flex items-center gap-2">
                        {{ strtoupper($name) }}
                        @if($config['primary'] === $name)
                            <span class="px-2 py-0.5 rounded-full bg-blue-500 text-white text-[10px] font-bold">PRIMARY</span>
                        @elseif($config['secondary'] === $name)
                            <span class="px-2 py-0.5 rounded-full bg-slate-600 text-white text-[10px] font-bold">SECONDARY</span>
                        @endif
                    </h3>
                    <p class="text-xs {{ $statusColor }}">{{ $statusText }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($status['failures'] > 0)
                    <span class="px-2 py-1 rounded-lg bg-red-500/10 text-red-400 text-xs font-medium">
                        {{ $status['failures'] }} failures
                    </span>
                @endif
            </div>
        </div>
        
        <!-- Card Body -->
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-slate-800/50 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-slate-500 text-sm">key</span>
                        <span class="text-xs text-slate-400 uppercase font-medium">API Key</span>
                    </div>
                    @if($status['has_key'])
                        <span class="text-green-400 text-sm font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">check</span>
                            Configured
                        </span>
                    @else
                        <span class="text-red-400 text-sm font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">close</span>
                            Missing
                        </span>
                    @endif
                </div>
                
                <div class="bg-slate-800/50 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-slate-500 text-sm">toggle_on</span>
                        <span class="text-xs text-slate-400 uppercase font-medium">Status</span>
                    </div>
                    @if($status['enabled'])
                        <span class="text-green-400 text-sm font-medium">Enabled</span>
                    @else
                        <span class="text-slate-500 text-sm font-medium">Disabled</span>
                    @endif
                </div>
                
                <div class="bg-slate-800/50 rounded-xl p-4 col-span-2">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-slate-500 text-sm">memory</span>
                        <span class="text-xs text-slate-400 uppercase font-medium">Model</span>
                    </div>
                    <code class="text-blue-400 text-sm font-mono">{{ $status['model'] }}</code>
                </div>
            </div>
            
            @if($status['last_error'])
            <div class="bg-red-500/5 border border-red-500/20 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-red-400 text-sm mt-0.5">error</span>
                    <div>
                        <p class="text-xs text-red-400 font-medium mb-1">Last Error</p>
                        <p class="text-sm text-red-300/80">{{ Str::limit($status['last_error'], 120) }}</p>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Timestamps -->
            <div class="flex flex-wrap gap-4 mb-6 text-xs text-slate-500">
                @if($status['last_success'])
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-green-400 text-xs">check_circle</span>
                        Last success: {{ \Carbon\Carbon::parse($status['last_success'])->diffForHumans() }}
                    </span>
                @endif
                @if($status['last_failure'])
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-red-400 text-xs">error</span>
                        Last failure: {{ \Carbon\Carbon::parse($status['last_failure'])->diffForHumans() }}
                    </span>
                @endif
            </div>
            
            <!-- Actions -->
            <div class="flex gap-2">
                <button type="button" data-action="test" data-provider="{{ $name }}"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition text-sm font-medium cursor-pointer">
                    <span class="material-symbols-outlined text-sm">science</span>
                    Test
                </button>
                <button type="button" data-action="reset" data-provider="{{ $name }}"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 transition text-sm font-medium cursor-pointer">
                    <span class="material-symbols-outlined text-sm">restart_alt</span>
                    Reset
                </button>
                @if($config['primary'] !== $name)
                <button type="button" data-action="switch" data-provider="{{ $name }}"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-green-500/10 text-green-400 hover:bg-green-500 hover:text-white transition text-sm font-medium cursor-pointer">
                    <span class="material-symbols-outlined text-sm">star</span>
                    Set Primary
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Info Card -->
<div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-500">info</span>
        <h3 class="font-bold text-white">About Failover System</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-400">
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-400 mt-0.5">looks_one</span>
                    <div>
                        <p class="text-white font-medium mb-1">Primary Provider</p>
                        <p>First provider attempted for every AI request.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">looks_two</span>
                    <div>
                        <p class="text-white font-medium mb-1">Secondary Provider</p>
                        <p>Backup provider used when primary fails.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-green-400 mt-0.5">sync</span>
                    <div>
                        <p class="text-white font-medium mb-1">Auto Failover</p>
                        <p>If primary fails 3x in 5 minutes, automatically switches to secondary.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-purple-400 mt-0.5">monitoring</span>
                    <div>
                        <p class="text-white font-medium mb-1">Health Check</p>
                        <p>Each provider is monitored for request success/failure rates.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-slate-800">
            <p class="text-xs text-slate-500 mb-3">SumoPod Free Models:</p>
            <div class="flex flex-wrap gap-2">
                <code class="px-3 py-1.5 rounded-lg bg-slate-800 text-blue-400 text-xs font-mono">kimi-k2-5-260127-free</code>
                <code class="px-3 py-1.5 rounded-lg bg-slate-800 text-blue-400 text-xs font-mono">seed-2-0-mini-free</code>
                <code class="px-3 py-1.5 rounded-lg bg-slate-800 text-blue-400 text-xs font-mono">whisper-1</code>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Emergency Switch Button
    const emergencyBtn = document.getElementById('btn-emergency-switch');
    if (emergencyBtn) {
        emergencyBtn.addEventListener('click', function() {
            const provider = this.dataset.provider;
            const other = provider === 'megallm' ? 'sumopod' : 'megallm';
            
            var message = "🚨 EMERGENCY SWITCH\n\n";
            message += "Switch from " + other.toUpperCase() + " to " + provider.toUpperCase() + "?\n\n";
            message += "This will immediately change the default AI provider for all chat responses.";
            
            if (!confirm(message)) return;
            
            fetch('/admin/ai-providers/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ primary: provider, secondary: other })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Emergency switch successful!\n\nDefault provider is now: ' + provider.toUpperCase());
                    location.reload();
                } else {
                    alert('❌ Switch failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(e => alert('Error: ' + e.message));
        });
    }
    
    // Quick Switch Buttons
    document.querySelectorAll('.btn-switch-provider').forEach(btn => {
        btn.addEventListener('click', function() {
            const provider = this.dataset.provider;
            const other = provider === 'megallm' ? 'sumopod' : 'megallm';
            
            if (!confirm('Set ' + provider.toUpperCase() + ' as default provider?')) return;
            
            fetch('/admin/ai-providers/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ primary: provider, secondary: other })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                }
            })
            .catch(e => alert('Error: ' + e.message));
        });
    });
    
    // Card Action Buttons
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const provider = this.dataset.provider;
            
            if (action === 'test') {
                const originalHtml = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">refresh</span> Testing...';
                
                fetch('/admin/ai-providers/' + provider + '/test')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            var latency = data.result.latency_ms ? data.result.latency_ms.toFixed(2) + 'ms' : 'N/A';
                            alert('✅ ' + provider.toUpperCase() + ' OK!\nLatency: ' + latency + '\nModel: ' + data.result.model);
                        } else {
                            alert('❌ ' + provider.toUpperCase() + ' Failed:\n' + data.result.error);
                        }
                    })
                    .catch(e => alert('Error: ' + e.message))
                    .finally(() => {
                        this.disabled = false;
                        this.innerHTML = originalHtml;
                    });
            }
            
            if (action === 'reset') {
                if (!confirm('Reset failure count for ' + provider.toUpperCase() + '?')) return;
                
                fetch('/admin/ai-providers/' + provider + '/reset', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    }
                })
                .catch(e => alert('Error: ' + e.message));
            }
            
            if (action === 'switch') {
                const other = provider === 'megallm' ? 'sumopod' : 'megallm';
                
                if (!confirm('Set ' + provider.toUpperCase() + ' as PRIMARY and ' + other.toUpperCase() + ' as SECONDARY?')) return;
                
                fetch('/admin/ai-providers/switch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ primary: provider, secondary: other })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    }
                })
                .catch(e => alert('Error: ' + e.message));
            }
        });
    });
})();
</script>

@endsection
