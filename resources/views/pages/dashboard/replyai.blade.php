<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <title>{{ $title ?? 'Dashboard | ReplyAI' }}</title>
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json?v=2">
    <meta name="theme-color" content="#030712">
    <link rel="apple-touch-icon" href="/logo-round.png">
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: { 500: '#3b82f6', 600: '#2563eb' },
                        surface: { dark: '#0f172a', light: '#1e293b' }
                    }
                }
            }
        }
    </script>

    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .material-symbols-outlined { 
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; 
        }
        
        .glass-header { 
            backdrop-filter: blur(20px); 
            background: rgba(15, 23, 42, 0.8);
            border-bottom: 1px solid rgba(51, 65, 85, 0.5);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
        }
        
        .stat-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%);
            border: 1px solid rgba(51, 65, 85, 0.4);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .glow-blue { box-shadow: 0 0 40px rgba(59, 130, 246, 0.15); }
        .glow-purple { box-shadow: 0 0 40px rgba(168, 85, 247, 0.15); }
        .glow-emerald { box-shadow: 0 0 40px rgba(16, 185, 129, 0.15); }
    </style>
</head>
<body class="bg-[#030712] text-slate-200 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Header -->
            <header class="glass-header h-16 flex-shrink-0 flex items-center justify-between px-6 lg:px-8 z-30">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold text-white">Dashboard</h1>
                        @include('components.page-help', [
                            'title' => 'Dashboard',
                            'description' => 'Pusat kendali utama untuk memantau aktivitas chat bot dan performa bisnis Anda.',
                            'tips' => [
                                'Pantau total pesan masuk dan respons bot secara real-time',
                                'Lihat statistik performa AI dan jumlah artikel KB',
                                'Gunakan Akses Cepat untuk navigasi ke fitur penting',
                                'Cek grafik Volume Percakapan untuk analisis tren'
                            ]
                        ])
                    </div>
                    
                    @if(auth()->user()->subscription && auth()->user()->subscription->status === 'active')
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/20 rounded-full">
                        <span class="material-symbols-outlined text-amber-400 text-sm">workspace_premium</span>
                        <span class="text-xs font-semibold text-amber-400">{{ auth()->user()->subscription->plan->name ?? 'Premium' }}</span>
                    </div>
                    @endif
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-medium text-slate-300">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    @include('components.language-switcher')
                </div>
            </header>

            <!-- Scrollable Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-8 pb-24">
                <div class="max-w-7xl mx-auto space-y-6">

                    <!-- Welcome Banner -->
                    @if(auth()->user()->subscription && auth()->user()->subscription->status === 'active')
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-800/50 to-slate-900/50 border border-slate-700/50 p-5">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/5 rounded-full blur-3xl -mr-20 -mt-20"></div>
                        <div class="relative flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 border border-amber-500/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-2xl text-amber-400">workspace_premium</span>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-400">Langganan aktif</p>
                                    <p class="text-lg font-semibold text-white">
                                        {{ auth()->user()->subscription->plan->name }}
                                        <span class="text-sm font-normal text-slate-400">hingga {{ auth()->user()->subscription->expires_at->format('d M Y') }}</span>
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('subscription.index') }}" class="text-sm text-blue-400 hover:text-blue-300 font-medium flex items-center gap-1 transition-colors">
                                Detail <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Stats Grid - 4 Cards -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        <!-- Total Messages -->
                        <div class="stat-card rounded-2xl p-5 card-hover glow-blue">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-blue-400">chat</span>
                                </div>
                                @if($stats['total_messages'] > 0)
                                <span class="text-xs font-medium text-emerald-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">trending_up</span>
                                    {{ $stats['msg_trend'] }}%
                                </span>
                                @endif
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-white">{{ number_format($stats['total_messages'], 0, ',', '.') }}</p>
                            <p class="text-sm text-slate-400 mt-1">Total pesan</p>
                        </div>

                        <!-- AI Responses -->
                        <div class="stat-card rounded-2xl p-5 card-hover glow-purple">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-purple-400">smart_toy</span>
                                </div>
                                @if($stats['ai_responses'] > 0)
                                <span class="text-xs font-medium text-purple-400">{{ $stats['ai_rate'] }}%</span>
                                @endif
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-white">{{ number_format($stats['ai_responses'], 0, ',', '.') }}</p>
                            <p class="text-sm text-slate-400 mt-1">Direspon AI</p>
                        </div>

                        <!-- Pending -->
                        <div class="stat-card rounded-2xl p-5 card-hover {{ $stats['pending_replies'] > 0 ? 'border-orange-500/30' : '' }}">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-10 h-10 rounded-xl bg-orange-500/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-orange-400">notifications</span>
                                </div>
                                @if($stats['pending_replies'] > 0)
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white animate-pulse">
                                    {{ $stats['pending_replies'] }}
                                </span>
                                @endif
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-white">{{ number_format($stats['pending_replies'], 0, ',', '.') }}</p>
                            <p class="text-sm text-slate-400 mt-1">Menunggu balas</p>
                        </div>

                        <!-- Knowledge Base -->
                        <div class="stat-card rounded-2xl p-5 card-hover glow-emerald">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-emerald-400">menu_book</span>
                                </div>
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold text-white">{{ number_format($stats['kb_articles'], 0, ',', '.') }}</p>
                            <p class="text-sm text-slate-400 mt-1">Artikel KB</p>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Chart Section - Takes 2 columns -->
                        <div class="lg:col-span-2 bg-slate-900/50 border border-slate-800 rounded-2xl p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-white">Volume Percakapan</h3>
                                    <p class="text-sm text-slate-500">7 hari terakhir</p>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                        <span class="text-slate-400">Customer</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                                        <span class="text-slate-400">AI</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="h-[280px] relative">
                                @if(collect($trend7Days)->sum('messages') == 0)
                                <div class="flex flex-col items-center justify-center h-full text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center mb-4 border border-slate-700">
                                        <span class="material-symbols-outlined text-slate-500 text-3xl">analytics</span>
                                    </div>
                                    <h4 class="text-lg font-medium text-slate-300">Menunggu data</h4>
                                    <p class="text-sm text-slate-500 max-w-xs mt-2">Data akan muncul setelah ada pesan masuk</p>
                                    @if(!$onboarding['wa_connected'])
                                    <a href="{{ route('whatsapp.settings') }}" class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition-colors">
                                        Hubungkan WhatsApp
                                    </a>
                                    @endif
                                </div>
                                @else
                                <canvas id="mainChart"></canvas>
                                @endif
                            </div>
                        </div>

                        <!-- Sidebar Widgets -->
                        <div class="space-y-6">
                            
                            <!-- Quick Actions -->
                            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5">
                                <h3 class="text-sm font-semibold text-slate-400 mb-4 uppercase tracking-wider">Akses Cepat</h3>
                                <div class="grid grid-cols-2 gap-3">
                                    <a href="{{ route('whatsapp.inbox') }}" class="flex flex-col items-center gap-2 p-4 bg-slate-800/50 border border-slate-700/50 rounded-xl hover:border-blue-500/30 hover:bg-blue-500/5 transition-all group">
                                        <span class="material-symbols-outlined text-slate-400 group-hover:text-blue-400 text-2xl">chat</span>
                                        <span class="text-xs font-medium text-slate-300 group-hover:text-white">Inbox</span>
                                    </a>
                                    <a href="{{ route('kb.index') }}" class="flex flex-col items-center gap-2 p-4 bg-slate-800/50 border border-slate-700/50 rounded-xl hover:border-purple-500/30 hover:bg-purple-500/5 transition-all group">
                                        <span class="material-symbols-outlined text-slate-400 group-hover:text-purple-400 text-2xl">psychology</span>
                                        <span class="text-xs font-medium text-slate-300 group-hover:text-white">Setup AI</span>
                                    </a>
                                    <a href="{{ route('whatsapp.broadcast.index') }}" class="flex flex-col items-center gap-2 p-4 bg-slate-800/50 border border-slate-700/50 rounded-xl hover:border-orange-500/30 hover:bg-orange-500/5 transition-all group">
                                        <span class="material-symbols-outlined text-slate-400 group-hover:text-orange-400 text-2xl">campaign</span>
                                        <span class="text-xs font-medium text-slate-300 group-hover:text-white">Broadcast</span>
                                    </a>
                                    <a href="{{ route('settings.business') }}" class="flex flex-col items-center gap-2 p-4 bg-slate-800/50 border border-slate-700/50 rounded-xl hover:border-emerald-500/30 hover:bg-emerald-500/5 transition-all group">
                                        <span class="material-symbols-outlined text-slate-400 group-hover:text-emerald-400 text-2xl">business</span>
                                        <span class="text-xs font-medium text-slate-300 group-hover:text-white">Profil</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Recent Activity -->
                            <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-5">
                                <h3 class="text-sm font-semibold text-slate-400 mb-4 uppercase tracking-wider">Aktivitas Terbaru</h3>
                                <div class="space-y-3">
                                    @forelse($activities as $activity)
                                    <div class="flex gap-3 p-3 rounded-lg bg-slate-800/30 border border-slate-700/30">
                                        <div class="w-1 rounded-full {{ $activity['type'] == 'contact' ? 'bg-blue-500' : 'bg-purple-500' }}"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-slate-300 truncate">{{ $activity['text'] }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $activity['time'] }}</p>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="py-6 text-center text-slate-500">
                                        <span class="material-symbols-outlined text-2xl block mb-2 opacity-50">inbox</span>
                                        <span class="text-sm">Belum ada aktivitas</span>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Forecast Card -->
                            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 p-5">
                                <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-3xl -mr-10 -mt-10"></div>
                                <div class="relative flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white text-2xl">timeline</span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-200">Estimasi kuota</p>
                                        <p class="text-xl font-bold text-white">± {{ $stats['forecast_days'] }} hari</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Setup Progress (if incomplete) -->
                    @if($setupProgress < 100)
                    <div class="bg-gradient-to-r from-indigo-950/30 to-purple-950/30 border border-indigo-500/20 rounded-2xl p-6">
                        <div class="flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-white">Langkah Awal</h3>
                                    <span class="px-2.5 py-0.5 bg-indigo-500 text-white text-xs font-semibold rounded-full">{{ round($setupProgress) }}%</span>
                                </div>
                                <p class="text-sm text-slate-400">Selesaikan setup untuk mengoptimalkan ReplyAI</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $steps = [
                                        ['key' => 'account', 'label' => 'Daftar', 'done' => true],
                                        ['key' => 'wa', 'label' => 'WhatsApp', 'done' => $onboarding['wa_connected']],
                                        ['key' => 'kb', 'label' => 'Knowledge', 'done' => $onboarding['kb_added']],
                                        ['key' => 'test', 'label' => 'Test', 'done' => $onboarding['chat_tested']],
                                        ['key' => 'ai', 'label' => 'AI Aktif', 'done' => $onboarding['ai_active']],
                                    ];
                                @endphp
                                @foreach($steps as $step)
                                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium {{ $step['done'] ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-500 border border-slate-700' }}">
                                    <span class="material-symbols-outlined text-sm">{{ $step['done'] ? 'check_circle' : 'pending' }}</span>
                                    {{ $step['label'] }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </main>
        </div>
    </div>

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    @if(collect($trend7Days)->sum('messages') > 0)
    <script>
        const ctx = document.getElementById('mainChart').getContext('2d');
        const trendData = @json($trend7Days);
        
        const blueGradient = ctx.createLinearGradient(0, 0, 0, 300);
        blueGradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        blueGradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        const purpleGradient = ctx.createLinearGradient(0, 0, 0, 300);
        purpleGradient.addColorStop(0, 'rgba(168, 85, 247, 0.2)');
        purpleGradient.addColorStop(1, 'rgba(168, 85, 247, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [
                    {
                        label: 'Customer',
                        data: trendData.map(d => d.messages),
                        borderColor: '#3b82f6',
                        borderWidth: 3,
                        backgroundColor: blueGradient,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#1e293b',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'AI',
                        data: trendData.map(d => d.ai_replies),
                        borderColor: '#a855f7',
                        borderWidth: 3,
                        backgroundColor: purpleGradient,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#a855f7',
                        pointBorderColor: '#1e293b',
                        pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 12, weight: '600' },
                        bodyFont: { size: 14, weight: '700' },
                        borderColor: 'rgba(51, 65, 85, 0.5)',
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: '#64748b', 
                            font: { size: 11 },
                            padding: 10
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(51, 65, 85, 0.3)', drawBorder: false },
                        ticks: { 
                            color: '#64748b', 
                            font: { size: 11 },
                            padding: 10,
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
    @endif

</body>
</html>
