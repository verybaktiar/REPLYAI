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


<!-- Queue Worker Status -->
<div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
    <div class="flex items-center gap-3 mb-2">
        <div class="w-2.5 h-2.5 rounded-full {{ $queueStatus ? 'bg-green-500' : 'bg-red-500' }}"></div>
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Queue Worker</span>
    </div>
    <p class="text-sm font-bold text-white">{{ $queueStatus ? 'RUNNING' : 'STOPPED' }}</p>
</div>

<!-- WA Service Status -->
<div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
    <div class="flex items-center gap-3 mb-2">
        <div class="w-2.5 h-2.5 rounded-full {{ $waServiceStatus ? 'bg-green-500' : 'bg-red-500' }}"></div>
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">WA Service</span>
    </div>
    <p class="text-sm font-bold text-white">{{ $waServiceStatus ? 'RUNNING' : 'STOPPED' }}</p>
</div>

    <!-- System Ports Status -->
    @foreach($systemPorts as $port)
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-2.5 h-2.5 rounded-full {{ $port['status'] === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $port['name'] }}</span>
        </div>
        <p class="text-sm font-bold text-white">{{ $port['status'] === 'online' ? 'PORT ' . $port['port'] . ' RUNNING' : 'OFFLINE' }}</p>
    </div>
    @endforeach
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
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-surface-light/20 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl {{ $isWa ? 'bg-green-500/10 text-green-500' : 'bg-pink-500/10 text-pink-500' }}">
                                <span>{{ $isWa ? 'WA' : 'IG' }}</span>
                            </div>
                            <div>
                                <div class="font-bold text-white">{{ $isWa ? ($account->phone_number ?? $account->session_id) : $account->username }}</div>
                                @if($user)
                                    <div class="text-xs text-slate-400">Owner: {{ $user->name }} ({{ $user->email }})</div>
                                @else
                                    <div class="text-xs text-red-500 italic">⚠️ Data owner tidak ditemukan (Orphaned Account)</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 rounded bg-red-500/10 text-red-400 text-[10px] font-bold uppercase border border-red-500/20">Disconnected</span>
                            @if($user)
                                <a href="{{ route('admin.users.show', $user->id) }}" class="p-2 text-slate-400 hover:text-white transition" title="Contact / Manage User">
                                    <span class="material-symbols-outlined text-lg">person_search</span>
                                </a>
                            @else
                                <form method="POST" action="{{ $isWa ? route('whatsapp.destroy', $account->session_id) : route('instagram.disconnect') }}" onsubmit="return confirm('Hapus data yatim ini?')">
                                    @csrf
                                    @if(!$isWa) @method('POST') @else @method('DELETE') @endif
                                    <button type="submit" class="p-2 text-red-500 hover:text-red-400 transition" title="Hapus Data Yatim">
                                        <span class="material-symbols-outlined text-lg">delete_forever</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-20">verified_user</span>
                        <p>Semua saluran WhatsApp & Instagram lancar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- PM2 Services Management -->
        <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
                <h3 class="font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-500">terminal</span>
                    Internal Services (PM2)
                </h3>
            </div>
            <div class="overflow-x-auto text-sm">
                <table class="w-full text-left">
                    <thead class="bg-background-dark/50 text-slate-400 font-medium border-b border-slate-800 text-xs">
                        <tr>
                            <th class="px-6 py-3">Service Name</th>
                            <th class="px-6 py-3 text-center">ID</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">CPU / RAM</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($pm2Status as $proc)
                        <tr class="hover:bg-surface-light/10 transition group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white flex items-center gap-2">
                                    {{ $proc['name'] }}
                                    <span class="text-[9px] font-normal px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 uppercase tracking-tight">{{ $proc['mode'] }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono">Restarts: {{ $proc['restart_count'] }}</div>
                            </td>
                            <td class="px-6 py-4 text-center text-slate-400 font-mono">{{ $proc['pm_id'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $proc['status'] === 'online' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                    {{ $proc['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-slate-300">{{ $proc['cpu'] }}%</span>
                                <span class="text-slate-500 mx-1">/</span>
                                <span class="text-slate-300">{{ $proc['memory'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.system-health.service-action') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="service_name" value="{{ $proc['name'] }}">
                                        @if($proc['status'] === 'online')
                                            <input type="hidden" name="action" value="restart">
                                            <button type="submit" class="p-1.5 rounded-lg bg-yellow-500/10 text-yellow-500 hover:bg-yellow-500 hover:text-white transition" title="Restart Service">
                                                <span class="material-symbols-outlined text-sm">restart_alt</span>
                                            </button>
                                            
                                            <input type="hidden" name="action" value="stop">
                                            <button type="submit" onclick="this.form.action.value='stop'; return confirm('Stop service {{ $proc['name'] }}?')" class="p-1.5 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition" title="Stop Service">
                                                <span class="material-symbols-outlined text-sm">stop</span>
                                            </button>
                                        @else
                                            <input type="hidden" name="action" value="start">
                                            <button type="submit" class="p-1.5 rounded-lg bg-green-500/10 text-green-500 hover:bg-green-500 hover:text-white transition" title="Start Service">
                                                <span class="material-symbols-outlined text-sm">play_arrow</span>
                                            </button>
                                        @endif
                                    </form>
                                    
                                    <button onclick="showLogs('{{ $proc['name'] }}')" class="p-1.5 rounded-lg bg-blue-500/10 text-blue-500 hover:bg-blue-500 hover:text-white transition" title="View Logs">
                                        <span class="material-symbols-outlined text-sm">description</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">Tidak ada proses PM2 yang berjalan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
                <form method="POST" action="{{ route('admin.system-health.cleanup-orphans') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua akun WA/IG yang tidak memiliki pemilik?')">
                    @csrf
                    <button class="w-full text-left px-4 py-2 rounded-lg bg-surface-light hover:bg-red-500/20 transition text-sm flex items-center gap-3 text-red-400">
                        <span class="material-symbols-outlined text-red-400 text-lg">delete_sweep</span>
                        Bersihkan Data Yatim
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

@push('scripts')
<div id="logsModal" class="fixed inset-0 bg-background-dark/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-surface-dark border border-slate-800 rounded-2xl w-full max-w-4xl max-h-[80vh] flex flex-col overflow-hidden shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-surface-light/30">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-400">terminal</span>
                Service Logs: <span id="modalServiceName" class="text-white"></span>
            </h3>
            <button onclick="hideLogs()" class="p-2 text-slate-400 hover:text-white transition">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div id="logsContent" class="p-6 overflow-y-auto font-mono text-xs text-slate-300 whitespace-pre-wrap bg-background-dark/50">
            Loading logs...
        </div>
        <div class="px-6 py-4 border-t border-slate-800 bg-surface-light/30 flex justify-end gap-3">
            <button onclick="refreshLogs()" class="px-4 py-2 bg-blue-500/10 text-blue-400 rounded-lg text-sm font-bold border border-blue-500/20 hover:bg-blue-500 hover:text-white transition flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">refresh</span>
                Refresh
            </button>
            <button onclick="hideLogs()" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition">Tutup</button>
        </div>
    </div>
</div>

<script>
    let currentLogService = '';

    function showLogs(serviceName) {
        currentLogService = serviceName;
        document.getElementById('modalServiceName').innerText = serviceName;
        document.getElementById('logsModal').classList.remove('hidden');
        document.getElementById('logsContent').innerText = 'Loading logs...';
        
        refreshLogs();
    }

    function hideLogs() {
        document.getElementById('logsModal').classList.add('hidden');
    }

    function refreshLogs() {
        if (!currentLogService) return;
        
        fetch(`{{ route('admin.system-health.logs') }}?service_name=${encodeURIComponent(currentLogService)}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('logsContent').innerText = data;
                // Scroll to bottom
                const content = document.getElementById('logsContent');
                content.scrollTop = content.scrollHeight;
            })
            .catch(error => {
                document.getElementById('logsContent').innerText = 'Error loading logs: ' + error;
            });
    }

    // Close on escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideLogs();
    });
</script>
@endpush
