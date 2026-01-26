@extends('admin.layouts.app')

@section('title', 'System Health')
@section('page_title', 'System Health')

@section('content')

<!-- Health Notification -->
@if($disconnectedWa->count() > 0 || $disconnectedIg->count() > 0)
<div class="bg-red-500/10 border border-red-500/30 text-red-500 p-4 rounded-xl mb-8 flex items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined animate-pulse">report</span>
        <div>
            <p class="font-bold">Proactive Alert: {{ $disconnectedWa->count() + $disconnectedIg->count() }} Accounts Disconnected</p>
            <p class="text-sm opacity-80">Beberapa akun user memerlukan perhatian segera untuk menjaga kelangsungan layanan.</p>
        </div>
    </div>
    <a href="#outages" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-bold transition hover:bg-red-600">Lihat Detail</a>
</div>
@endif

<!-- Status Overview -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-2.5 h-2.5 rounded-full {{ $dbStatus ? 'bg-green-500' : 'bg-red-500' }}"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Database</span>
        </div>
        <p class="text-sm font-bold text-white">{{ $dbStatus ? 'ONLINE' : 'OFFLINE' }}</p>
    </div>
    
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-2.5 h-2.5 rounded-full {{ $cacheStatus ? 'bg-green-500' : 'bg-yellow-500' }}"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cache</span>
        </div>
        <p class="text-sm font-bold text-white">{{ $cacheStatus ? 'WORKING' : 'DEGRADED' }}</p>
    </div>

    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Disk Usage</span>
        </div>
        <p class="text-sm font-bold text-white">{{ $diskUsedPercent }}% USED</p>
    </div>

    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-2.5 h-2.5 rounded-full bg-primary"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">24h Users</span>
        </div>
        <p class="text-sm font-bold text-white">{{ $activeUsers24h }} ACTIVE</p>
    </div>

    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-2.5 h-2.5 rounded-full bg-purple-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">PHP Version</span>
        </div>
        <p class="text-sm font-bold text-white">{{ PHP_VERSION }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Proactive Outages Monitoring -->
    <div id="outages" class="lg:col-span-2 space-y-6">
        <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
                <h3 class="font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-500">cell_tower</span>
                    Proactive Outages Monitoring
                </h3>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($disconnectedWa->merge($disconnectedIg) as $account)
                    @php 
                        $isWa = $account instanceof \App\Models\WhatsAppDevice;
                        $user = $isWa ? ($account->businessProfile?->user) : $account->user;
                    @endphp
                    @if($user)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-surface-light/20 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl {{ $isWa ? 'bg-green-500/10 text-green-500' : 'bg-pink-500/10 text-pink-500' }}">
                                <span>{{ $isWa ? 'WA' : 'IG' }}</span>
                            </div>
                            <div>
                                <div class="font-bold text-white">{{ $isWa ? ($account->phone_number ?? $account->session_id) : $account->username }}</div>
                                <div class="text-xs text-slate-400">Owner: {{ $user->name }} ({{ $user->email }})</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 rounded bg-red-500/10 text-red-400 text-[10px] font-bold uppercase border border-red-500/20">Disconnected</span>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="p-2 text-slate-400 hover:text-white transition" title="Contact / Manage User">
                                <span class="material-symbols-outlined text-lg">person_search</span>
                            </a>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="px-6 py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-20">verified_user</span>
                        <p>Semua saluran WhatsApp & Instagram lancar.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3rd Party Health -->
    <div class="space-y-6">
        <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30">
                <h3 class="font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">hub</span>
                    3rd Party API Health
                </h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($externalHealth as $provider)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-surface-light/30 border border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $provider['status'] === 'online' ? 'bg-green-500' : ($provider['status'] === 'degraded' ? 'bg-yellow-500' : 'bg-red-500') }}"></div>
                            <span class="font-medium text-sm">{{ $provider['name'] }}</span>
                        </div>
                        <span class="text-[10px] font-bold uppercase {{ $provider['status'] === 'online' ? 'text-green-400' : 'text-yellow-400' }}">
                            {{ $provider['message'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Maintenance -->
        <div class="bg-surface-dark rounded-2xl border border-slate-800 p-6">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-500">build</span>
                Quick Maintenance
            </h3>
            <div class="grid grid-cols-1 gap-2">
                <form method="POST" action="{{ route('admin.maintenance.clear-cache') }}">
                    @csrf
                    <button class="w-full text-left px-4 py-2 rounded-lg bg-surface-light hover:bg-slate-700 transition text-sm flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-400 text-lg">mop</span>
                        Clear Cache
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.maintenance.refresh-tokens') }}">
                    @csrf
                    <button class="w-full text-left px-4 py-2 rounded-lg bg-surface-light hover:bg-slate-700 transition text-sm flex items-center gap-3">
                        <span class="material-symbols-outlined text-purple-400 text-lg">sync_lock</span>
                        Refresh IG Tokens
                    </button>
                </form>
                <a href="{{ route('admin.failed-jobs.index') }}" class="w-full text-left px-4 py-2 rounded-lg bg-surface-light hover:bg-slate-700 transition text-sm flex items-center gap-3 text-red-400">
                    <span class="material-symbols-outlined text-lg">error</span>
                    Failed Jobs Center
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
