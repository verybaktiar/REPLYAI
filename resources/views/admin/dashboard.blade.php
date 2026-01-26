@extends('admin.layouts.app')

@section('title', 'Admin Intelligence Dashboard')
@section('page_title', 'Intelligence Dashboard')

@section('content')

<!-- Welcome & Meta -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black mb-1 text-white">Selamat datang, {{ Auth::guard('admin')->user()->name }}! 📊</h2>
        <p class="text-slate-400">Analisis pertumbuhan dan performa platform ReplyAI hari ini.</p>
    </div>
    <div class="flex items-center gap-3 px-4 py-2 bg-primary/10 border border-primary/20 rounded-xl">
        <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
        <span class="text-xs font-bold text-primary uppercase tracking-widest">Real-time Analytics Active</span>
    </div>
</div>

<!-- Quick Stats Grid -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-slate-700 transition">
        <div class="text-sm text-slate-500 mb-1 border-b border-slate-800 pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">group</span> Users
        </div>
        <div class="text-2xl font-black mt-2">{{ number_format($stats['total_users']) }}</div>
    </div>

    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-yellow-500/30 transition">
        <div class="text-sm text-slate-500 mb-1 border-b border-slate-800 pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-yellow-500">star</span> VIP
        </div>
        <div class="text-2xl font-black mt-2 text-yellow-400">{{ number_format($stats['vip_users']) }}</div>
    </div>

    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-orange-500/30 transition">
        <div class="text-sm text-slate-500 mb-1 border-b border-slate-800 pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-orange-500">pending</span> Pending Pay
        </div>
        <div class="text-2xl font-black mt-2 text-orange-400">{{ number_format($stats['pending_payments']) }}</div>
    </div>

    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-green-500/30 transition">
        <div class="text-sm text-slate-500 mb-1 border-b border-slate-800 pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-green-500">verified</span> Active Subs
        </div>
        <div class="text-2xl font-black mt-2 text-green-400">{{ number_format($stats['active_subs']) }}</div>
    </div>

    <div class="bg-surface-dark rounded-2xl p-5 border border-slate-800 hover:border-red-500/30 transition">
        <div class="text-sm text-slate-500 mb-1 border-b border-slate-800 pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-red-500">support</span> Tickets
        </div>
        <div class="text-2xl font-black mt-2 text-red-400">{{ number_format($stats['open_tickets']) }}</div>
    </div>
</div>

<!-- Main Intelligence Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    
    <!-- User Growth Chart -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">trending_up</span>
                User Growth (30 Days)
            </h3>
        </div>
        <div class="h-64">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-green-500">payments</span>
                Revenue Analytics
            </h3>
        </div>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Usage Distribution -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 shadow-xl">
        <h3 class="font-bold mb-6 flex items-center gap-2 text-sm uppercase tracking-widest text-slate-500">
            Usage Distribution
        </h3>
        <div class="h-48 mb-6">
            <canvas id="usageDoughnut"></canvas>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center text-xs">
                <span class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-primary"></div> AI Messages</span>
                <span class="font-bold">{{ number_format($usageStats['ai_messages']) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs">
                <span class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-emerald-500"></div> Broadcasts</span>
                <span class="font-bold">{{ number_format($usageStats['broadcasts']) }}</span>
            </div>
        </div>
    </div>

    <!-- Top Plans & Recent Users -->
    <div class="lg:col-span-2 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Top Plans -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
                <h3 class="font-bold text-sm uppercase tracking-widest text-slate-500 mb-4">Top Plans</h3>
                <div class="space-y-3">
                    @forelse($topPlans as $index => $plan)
                    <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-xl border border-slate-700/50 hover:border-primary/50 transition">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-black
                            {{ $index === 0 ? 'bg-yellow-500 text-black' : ($index === 1 ? 'bg-slate-400 text-black' : 'bg-amber-700 text-white') }}">
                            #{{ $index + 1 }}
                        </span>
                        <span class="flex-1 font-bold text-sm">{{ $plan['name'] }}</span>
                        <span class="text-xs text-slate-500">{{ $plan['count'] }} active</span>
                    </div>
                    @empty
                    @endforelse
                </div>
            </div>

            <!-- Recent Users -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
                <h3 class="font-bold text-sm uppercase tracking-widest text-slate-500 mb-4">Recent Onboarding</h3>
                <div class="space-y-3">
                    @foreach($recentUsers as $user)
                    <div class="flex items-center gap-3 p-2 hover:bg-slate-800/30 rounded-xl transition">
                        <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-xs font-bold text-primary">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-xs truncate text-white">{{ $user->name }}</div>
                            <div class="text-[10px] text-slate-500">{{ $user->created_at->diffForHumans() }}</div>
                        </div>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="p-1.5 text-slate-500 hover:text-white transition">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- System Shortcuts -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.failed-jobs.index') }}" class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl hover:bg-red-500/20 transition group">
                <span class="material-symbols-outlined text-red-500 mb-2 group-hover:scale-110 transition">error</span>
                <div class="text-xs font-bold text-white uppercase">Failed Jobs</div>
            </a>
            <a href="{{ route('admin.system-health') }}" class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-2xl hover:bg-blue-500/20 transition group">
                <span class="material-symbols-outlined text-blue-500 mb-2 group-hover:scale-110 transition">monitor_heart</span>
                <div class="text-xs font-bold text-white uppercase">Health</div>
            </a>
            <a href="{{ route('admin.logs.index') }}" class="p-4 bg-purple-500/10 border border-purple-500/20 rounded-2xl hover:bg-purple-500/20 transition group">
                <span class="material-symbols-outlined text-purple-500 mb-2 group-hover:scale-110 transition">terminal</span>
                <div class="text-xs font-bold text-white uppercase">System Logs</div>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-2xl hover:bg-yellow-500/20 transition group">
                <span class="material-symbols-outlined text-yellow-500 mb-2 group-hover:scale-110 transition">settings</span>
                <div class="text-xs font-bold text-white uppercase">Settings</div>
            </a>
        </div>
    </div>
</div>

<!-- Chart Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Global Configuration
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', sans-serif";

    // User Growth Chart
    new Chart(document.getElementById('userGrowthChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($userGrowthChart)) !!}.map(d => d.split('-').slice(1).join('/')),
            datasets: [{
                label: 'New Users',
                data: {!! json_encode(array_values($userGrowthChart)) !!},
                borderColor: '#135bec',
                backgroundColor: 'rgba(19, 91, 236, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 0,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Revenue Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($revenueChart)) !!}.map(d => d.split('-').slice(1).join('/')),
            datasets: [{
                label: 'Daily Revenue',
                data: {!! json_encode(array_values($revenueChart)) !!},
                backgroundColor: '#10b981',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Usage Doughnut Chart
    new Chart(document.getElementById('usageDoughnut'), {
        type: 'doughnut',
        data: {
            labels: ['AI Messages', 'Broadcasts'],
            datasets: [{
                data: [{{ $usageStats['ai_messages'] }}, {{ $usageStats['broadcasts'] }}],
                backgroundColor: ['#135bec', '#10b981'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { 
                legend: { display: false }
            }
        }
    });
</script>

@endsection
