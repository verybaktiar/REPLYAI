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
<div class="flex h-screen w-full">
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
    <aside class="hidden lg:flex flex-col w-72 h-full bg-[#111722] border-r border-[#232f48] shrink-0 fixed lg:static top-0 bottom-0 left-0 z-40">
        <!-- Brand -->
        <div class="flex items-center gap-3 px-6 py-6 mb-2">
            <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 shadow-lg relative" style='background-image: url("https://ui-avatars.com/api/?name=Reply+AI&background=0D8ABC&color=fff");'></div>
            <div>
                <h1 class="text-base font-bold leading-none text-white">ReplyAI Admin</h1>
                <p class="text-xs text-[#92a4c9] mt-1">RS PKU Solo Bot</p>
            </div>
        </div>
        <!-- Navigation Links -->
        <nav class="flex flex-col gap-1 flex-1 overflow-y-auto px-4">
            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('dashboard') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('dashboard') }}">
                <span class="material-symbols-outlined text-[24px]">grid_view</span>
                <span class="text-sm font-medium">Dashboard</span>
            </a>
            
            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('analytics*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('analytics.index') }}">
                <span class="material-symbols-outlined text-[24px]">pie_chart</span>
                <span class="text-sm font-medium">Analisis & Laporan</span>
            </a>

            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('contacts*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('contacts.index') }}">
                <span class="material-symbols-outlined text-[24px]">groups</span>
                <span class="text-sm font-medium">Data Kontak (CRM)</span>
            </a>

            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('inbox*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('inbox') }}">
                <span class="material-symbols-outlined text-[24px]">chat_bubble</span>
                <span class="text-sm font-medium">Kotak Masuk</span>
                @if(isset($stats['pending_inbox']) && $stats['pending_inbox'] > 0)
                <span class="ml-auto bg-white/10 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md text-center min-w-[20px]">{{ $stats['pending_inbox'] }}</span>
                @endif
            </a>
            
            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('rules*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('rules.index') }}">
                <span class="material-symbols-outlined text-[24px]">smart_toy</span>
                <span class="text-sm font-medium">Manajemen Bot</span>
            </a>
            
            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('kb*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('kb.index') }}">
                <span class="material-symbols-outlined text-[24px]">menu_book</span>
                <span class="text-sm font-medium">Knowledge Base</span>
            </a>

            <!-- New Links -->
            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('simulator*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('simulator.index') }}">
                <span class="material-symbols-outlined text-[24px]">science</span>
                <span class="text-sm font-medium">Simulator</span>
            </a>
            
            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('settings*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('settings.index') }}">
                <span class="material-symbols-outlined text-[24px]">settings</span>
                <span class="text-sm font-medium">Settings (Hours)</span>
            </a>

            <div class="mt-4 mb-2 px-3">
                <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">System</p>
            </div>
            <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('logs*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('logs.index') }}">
                <span class="material-symbols-outlined text-[24px]">history</span>
                <span class="text-sm font-medium">Log Aktivitas</span>
            </a>
        </nav>
        <!-- User Profile (Bottom) -->
        <div class="border-t border-[#232f48] p-4">
             <div class="p-3 rounded-lg bg-[#232f48]/50 flex items-center gap-3">
                <div class="size-8 rounded-full bg-gradient-to-tr from-purple-500 to-primary flex items-center justify-center text-xs font-bold text-white">DM</div>
                <div class="flex flex-col overflow-hidden">
                    <p class="text-white text-sm font-medium truncate">Admin</p>
                    <p class="text-[#92a4c9] text-xs truncate">admin@rspkusolo.com</p>
                </div>
                <button class="ml-auto text-[#92a4c9] hover:text-white">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </button>
            </div>
        </div>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Top Header -->
        <header class="h-16 flex items-center justify-between px-6 lg:px-8 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
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
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
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

                <!-- Main Chart Section (Simulated for visual appeal as requested) -->
                <div class="flex flex-col gap-6 p-6 rounded-xl bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h3 class="text-lg font-bold dark:text-white text-slate-900">Interaction Volume</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Activity simulated based on bot triggers.</p>
                        </div>
                    </div>
                    <div class="w-full h-[240px] relative">
                         <!-- Chart SVG (Static from user design) -->
                        <svg class="w-full h-full" fill="none" preserveaspectratio="none" viewbox="0 0 800 240" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <lineargradient gradientunits="userSpaceOnUse" id="chartGradient" x1="400" x2="400" y1="0" y2="240">
                                    <stop stop-color="#135bec" stop-opacity="0.2"></stop>
                                    <stop offset="1" stop-color="#135bec" stop-opacity="0"></stop>
                                </lineargradient>
                            </defs>
                            <line stroke="#334155" stroke-dasharray="4 4" stroke-opacity="0.2" x1="0" x2="800" y1="200" y2="200"></line>
                            <line stroke="#334155" stroke-dasharray="4 4" stroke-opacity="0.2" x1="0" x2="800" y1="140" y2="140"></line>
                            <line stroke="#334155" stroke-dasharray="4 4" stroke-opacity="0.2" x1="0" x2="800" y1="80" y2="80"></line>
                            <path d="M0,180 C50,180 50,120 100,120 C150,120 150,160 200,160 C250,160 250,60 300,60 C350,60 350,100 400,100 C450,100 450,40 500,40 C550,40 550,140 600,140 C650,140 650,80 700,80 C750,80 750,110 800,110" fill="none" stroke="#135bec" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"></path>
                            <path d="M0,180 C50,180 50,120 100,120 C150,120 150,160 200,160 C250,160 250,60 300,60 C350,60 350,100 400,100 C450,100 450,40 500,40 C550,40 550,140 600,140 C650,140 650,80 700,80 C750,80 750,110 800,110 V240 H0 Z" fill="url(#chartGradient)"></path>
                        </svg>
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
</html>
