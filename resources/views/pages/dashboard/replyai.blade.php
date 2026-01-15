<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>ReplyAI - Admin Dashboard</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Theme Configuration -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                        "surface-dark": "#1a2230", 
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #282e39; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden antialiased">
<div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
@include('components.sidebar')
    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
        <!-- Top Header -->
        <header class="hidden lg:flex h-16 items-center justify-between px-6 lg:px-8 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-2 lg:hidden">
                <button class="p-2 -ml-2 text-slate-600 dark:text-slate-400">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <span class="font-bold text-lg dark:text-white">ReplyAI</span>
            </div>
            <div class="hidden lg:flex items-center gap-2">
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <div class="flex items-center gap-4 ml-auto">
                {{-- Status Indicator --}}
                <div class="flex items-center gap-2 px-3 py-1.5 bg-green-500/10 rounded-full border border-green-500/20">
                    <div class="size-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-bold text-green-500">SYSTEM ONLINE</span>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 scroll-smooth">
            <div class="max-w-7xl mx-auto flex flex-col gap-8">
                <!-- Page Heading Section -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="flex flex-col gap-1">
                        <h2 class="text-2xl md:text-3xl font-black tracking-tight dark:text-white text-slate-900">Overview</h2>
                        <p class="text-slate-500 dark:text-slate-400">Here's what's happening with your hospital chatbots today.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Stat 1: Total Messages -->
                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-primary rounded-lg">
                                <span class="material-symbols-outlined text-2xl">forum</span>
                            </div>
                            <span class="flex items-center gap-1 text-xs font-bold text-green-500 bg-green-500/10 px-2 py-1 rounded-full">
                                {{ $stats['growth'] > 0 ? '+' : '' }}{{ $stats['growth'] }}% <span class="material-symbols-outlined text-sm">trending_up</span>
                            </span>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Messages</p>
                            <p class="text-2xl font-bold dark:text-white text-slate-900 mt-1">{{ number_format($stats['total_messages']) }}</p>
                        </div>
                    </div>

                    <!-- Stat 2: AI Rate -->
                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="p-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 rounded-lg">
                                <span class="material-symbols-outlined text-2xl">smart_toy</span>
                            </div>
                            <span class="flex items-center gap-1 text-xs font-bold text-green-500 bg-green-500/10 px-2 py-1 rounded-full">
                                Good <span class="material-symbols-outlined text-sm">check</span>
                            </span>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">AI Handled Rate</p>
                            <p class="text-2xl font-bold dark:text-white text-slate-900 mt-1">{{ $stats['ai_rate'] }}%</p>
                        </div>
                    </div>

                    <!-- Stat 3: Pending Inbox -->
                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="p-2 bg-orange-50 dark:bg-orange-900/20 text-orange-600 rounded-lg">
                                <span class="material-symbols-outlined text-2xl">mark_chat_unread</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Pending Chats</p>
                            <p class="text-2xl font-bold dark:text-white text-slate-900 mt-1">{{ $stats['pending_inbox'] }}</p>
                        </div>
                    </div>

                    <!-- Stat 4: KB Count -->
                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-lg">
                                <span class="material-symbols-outlined text-2xl">library_books</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">KB Articles</p>
                            <p class="text-2xl font-bold dark:text-white text-slate-900 mt-1">{{ $stats['kb_count'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Main Chart Section with Chart.js -->
                <div class="flex flex-col gap-6 p-6 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h3 class="text-lg font-bold dark:text-white text-slate-900">Interaction Volume (7 Hari)</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Data pesan masuk dan balasan bot.</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs">
                            <span class="flex items-center gap-1"><span class="size-3 rounded-full bg-primary"></span> Pesan Masuk</span>
                            <span class="flex items-center gap-1"><span class="size-3 rounded-full bg-purple-500"></span> Bot Reply</span>
                        </div>
                    </div>
                    <div class="w-full h-[240px] relative">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Top Questions Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-4 p-6 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold dark:text-white text-slate-900">Top Pertanyaan</h3>
                            <span class="text-xs text-slate-500">5 Terbanyak</span>
                        </div>
                        <div class="flex flex-col gap-2">
                            @forelse($topQuestions as $index => $q)
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                                <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">{{ $index + 1 }}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium dark:text-white text-slate-800 truncate">{{ Str::limit($q->trigger_text, 50) }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">{{ $q->count }}x</span>
                            </div>
                            @empty
                            <div class="text-center text-slate-500 py-4">Belum ada data pertanyaan</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Response Time Card -->
                    <div class="flex flex-col gap-4 p-6 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 shadow-sm">
                        <h3 class="text-lg font-bold dark:text-white text-slate-900">Performa Bot</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30">
                                <span class="material-symbols-outlined text-emerald-600 text-3xl">speed</span>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Avg Response Time</p>
                                <p class="text-2xl font-bold text-emerald-600">{{ $stats['avg_response_time'] }}s</p>
                            </div>
                            <div class="flex flex-col gap-2 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/30">
                                <span class="material-symbols-outlined text-primary text-3xl">psychology</span>
                                <p class="text-xs text-slate-500 dark:text-slate-400">AI Success Rate</p>
                                <p class="text-2xl font-bold text-primary">{{ $stats['ai_rate'] }}%</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Bot berjalan dengan baik</p>
                        </div>
                    </div>
                </div>

                <!-- Lower Section: Quick Actions & Activity -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                    <!-- Quick Actions -->
                    <div class="xl:col-span-1 flex flex-col gap-4">
                        <h3 class="text-lg font-bold dark:text-white text-slate-900 px-1">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('inbox') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-primary dark:hover:border-primary group transition-all">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-primary rounded-full group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined">chat</span>
                                </div>
                                <span class="text-sm font-semibold dark:text-white text-slate-800">Inbox</span>
                            </a>
                            <a href="{{ route('kb.index') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-primary dark:hover:border-primary group transition-all">
                                <div class="p-3 bg-purple-50 dark:bg-purple-900/20 text-purple-600 rounded-full group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined">school</span>
                                </div>
                                <span class="text-sm font-semibold dark:text-white text-slate-800">Train AI</span>
                            </a>
                            <a href="{{ route('rules.index') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-primary dark:hover:border-primary group transition-all">
                                <div class="p-3 bg-orange-50 dark:bg-orange-900/20 text-orange-600 rounded-full group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined">settings_suggest</span>
                                </div>
                                <span class="text-sm font-semibold dark:text-white text-slate-800">Rules</span>
                            </a>
                             <a href="{{ route('logs.index') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 hover:border-primary dark:hover:border-primary group transition-all">
                                <div class="p-3 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined">terminal</span>
                                </div>
                                <span class="text-sm font-semibold dark:text-white text-slate-800">Logs</span>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity / Notifications -->
                    <div class="xl:col-span-2 flex flex-col gap-4">
                        <div class="flex items-center justify-between px-1">
                            <h3 class="text-lg font-bold dark:text-white text-slate-900">Recent Activity</h3>
                        </div>
                        <div class="flex flex-col rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 overflow-hidden">
                            
                            @forelse($activities as $activity)
                            <div class="flex items-center gap-4 p-4 border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer">
                                <div class="relative flex-shrink-0">
                                    <div class="size-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-300 font-bold">
                                         {{ substr($activity['user'], 0, 1) }}
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 bg-green-500 rounded-full p-0.5 border-2 border-white dark:border-surface-dark">
                                        <span class="material-symbols-outlined text-[10px] text-white block">chat</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <p class="text-sm font-bold dark:text-white text-slate-900 truncate">{{ $activity['user'] }}</p>
                                        <span class="text-xs text-slate-500">{{ $activity['time'] }}</span>
                                    </div>
                                    <p class="text-sm text-slate-600 dark:text-slate-400 truncate">{{ $activity['content'] }}</p>
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-slate-500">
                                No recent activity found.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Trend Chart
    const trendData = @json($trend7Days);
    const ctx = document.getElementById('trendChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.date),
            datasets: [
                {
                    label: 'Pesan Masuk',
                    data: trendData.map(d => d.messages),
                    borderColor: '#135bec',
                    backgroundColor: 'rgba(19, 91, 236, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#135bec',
                },
                {
                    label: 'Bot Reply',
                    data: trendData.map(d => d.ai_replies),
                    borderColor: '#a855f7',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#a855f7',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(51, 65, 85, 0.1)'
                    },
                    ticks: {
                        color: '#64748b'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(51, 65, 85, 0.1)'
                    },
                    ticks: {
                        color: '#64748b',
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
</html>
