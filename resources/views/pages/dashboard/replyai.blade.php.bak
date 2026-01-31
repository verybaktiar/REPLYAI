<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>ReplyAI - {{ __('dashboard.title') }}</title>
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
                        "primary": "#3B82F6",
                        "background-light": "#f6f6f8",
                        "background-dark": "#030712", // gray-950
                        "surface-dark": "#0a101f", // slightly lighter than 950
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
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }

        /* Premium Glow Appreciations */
        .glass-card { background: rgba(10, 16, 31, 0.4); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.05); }
        .skeleton-pulse { background: linear-gradient(90deg, #0a101f 25%, #111827 50%, #0a101f 75%); background-size: 200% 100%; animation: pulse-glow 1.5s infinite; }
        @keyframes pulse-glow { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        
        /* Material Symbols Filled State */
        .filled { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden antialiased">

{{-- Welcome Popup Modal --}}
@if(isset($isFirstLogin) && $isFirstLogin)
<div x-data="{ open: true }" 
     x-show="open" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300 delay-100"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="relative bg-gradient-to-br from-surface-dark to-background-dark rounded-3xl p-8 max-w-md w-full border border-slate-700 shadow-2xl">
        
        {{-- Close Button --}}
        <button @click="open = false" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-white transition">
            <span class="material-symbols-outlined">close</span>
        </button>
        
        {{-- Animated Icon --}}
        <div class="flex justify-center mb-6">
            <div class="relative">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary to-blue-400 flex items-center justify-center animate-pulse">
                    <span class="text-5xl">🎉</span>
                </div>
                <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center border-4 border-surface-dark">
                    <span class="material-symbols-outlined text-white text-sm">check</span>
                </div>
            </div>
        </div>
        
        {{-- Content --}}
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-white mb-2">
                {{ __('dashboard.welcome_back') }}, {{ $user->name ?? 'User' }}! 👋
            </h2>
            <p class="text-slate-400">
                {{ __('dashboard.onboarding_subtitle') }}
            </p>
        </div>
        
        {{-- Tips --}}
        <div class="bg-slate-800/50 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-lg">tips_and_updates</span>
                {{ __('dashboard.quick_actions') }}:
            </h4>
            <ul class="space-y-2 text-sm text-slate-300">
                <li class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                    {{ __('dashboard.connect_wa') }}
                </li>
                <li class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                    {{ __('dashboard.add_kb') }}
                </li>
                <li class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
                    {{ __('dashboard.create_rules') }}
                </li>
            </ul>
        </div>
        
        {{-- CTA Button --}}
        <button @click="open = false" 
                class="w-full py-4 px-6 bg-gradient-to-r from-primary to-blue-500 hover:from-blue-600 hover:to-primary text-white font-bold rounded-xl transition-all transform hover:scale-[1.02] shadow-lg shadow-primary/30">
            {{ __('dashboard.onboarding_title') }} 🚀
        </button>
    </div>
</div>
@endif

<div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
    <!-- Sidebar Navigation -->
@include('components.sidebar')
    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
        <!-- Top Header -->
        <header class="hidden lg:flex h-16 items-center justify-between px-6 lg:px-8 border-b border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
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
                    <span class="text-xs font-bold text-green-500">Aktif</span>
                </div>

                <!-- Language Switcher -->
                @include('components.language-switcher')
            </div>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 pb-24 lg:pb-8 scroll-smooth">
            <div class="max-w-7xl mx-auto flex flex-col gap-8">
                <!-- Page Heading Section -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-3">
                            <h2 class="text-[28px] font-black tracking-tight text-white">Dashboard</h2>
                            @include('components.page-help', [
                                'title' => 'Halaman Beranda',
                                'description' => 'Ini adalah halaman utama yang menampilkan ringkasan aktivitas chatbot Anda hari ini.',
                                'tips' => [
                                    'Lihat Total Messages untuk melihat jumlah pesan masuk',
                                    'AI Handled Rate menunjukkan persentase pesan yang dijawab bot',
                                    'Pending Chats adalah pesan yang perlu ditangani manual',
                                    'Klik Quick Actions untuk akses cepat ke fitur penting'
                                ]
                            ])
                        </div>
                        <p class="text-sm text-gray-400">Ringkasan aktivitas chat hari ini</p>
                    </div>
                </div>



                {{-- Onboarding Checklist (shows only if incomplete) --}}
                @include('components.onboarding-checklist', ['user' => $user])

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Stat 1: Total Messages -->
                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-[#0a101f] border border-gray-800 shadow-sm hover:border-gray-700 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="size-10 rounded-xl bg-blue-900/20 text-blue-500 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px] filled">forum</span>
                            </div>
                            @if($stats['total_messages'] > 0)
                                <span class="flex items-center gap-1 text-xs font-bold text-green-500 bg-green-500/10 px-2.5 py-1 rounded-full border border-green-500/20">
                                    Bagus <span class="material-symbols-outlined text-sm">check</span>
                                </span>
                            @else
                                <span class="text-xs font-bold text-gray-500">-</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-500 text-[10px] uppercase font-black tracking-widest">Total Pesan</p>
                            @if($stats['total_messages'] > 0)
                                <p class="text-2xl font-black text-white mt-1">{{ number_format($stats['total_messages']) }}</p>
                            @else
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">Belum ada data</p>
                                    <a href="{{ route('whatsapp.settings') }}" class="text-[10px] text-blue-500 font-bold hover:underline mt-1">[Hubungkan WhatsApp]</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Stat 2: AI Rate -->
                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-[#0a101f] border border-gray-800 shadow-sm hover:border-gray-700 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="size-10 rounded-xl bg-purple-900/20 text-purple-500 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px] filled">smart_toy</span>
                            </div>
                            @if($stats['ai_rate'] > 0)
                                <span class="flex items-center gap-1 text-xs font-bold text-green-500 bg-green-500/10 px-2.5 py-1 rounded-full border border-green-500/20">
                                    Bagus <span class="material-symbols-outlined text-sm">check</span>
                                </span>
                            @else
                                <span class="text-xs font-bold text-gray-500">-</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-500 text-[10px] uppercase font-black tracking-widest">Direspon AI</p>
                            @if($stats['ai_rate'] > 0)
                                <p class="text-2xl font-black text-white mt-1">{{ $stats['ai_rate'] }}%</p>
                            @else
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">Belum ada data</p>
                                    <a href="{{ route('kb.index') }}" class="text-[10px] text-blue-500 font-bold hover:underline mt-1">[Tambah Knowledge Base]</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Stat 3: Pending Inbox -->
                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-[#0a101f] border border-gray-800 shadow-sm hover:border-gray-700 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="size-10 rounded-xl bg-orange-900/20 text-orange-500 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px] filled">mark_chat_unread</span>
                            </div>
                            @if($stats['pending_inbox'] > 5)
                                <span class="flex items-center gap-1 text-xs font-bold text-orange-500 bg-orange-500/10 px-2.5 py-1 rounded-full border border-orange-500/20">
                                    Perhatian <span class="material-symbols-outlined text-sm">warning</span>
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-500 text-[10px] uppercase font-black tracking-widest">Menunggu Balasan</p>
                            @if($stats['pending_inbox'] > 0)
                                <p class="text-2xl font-black text-white mt-1">{{ $stats['pending_inbox'] }}</p>
                            @else
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600 font-medium">Semua Chat Teratasi</p>
                                    <a href="{{ route('whatsapp.inbox') }}" class="text-[10px] text-blue-500 font-bold hover:underline mt-1">[Buka Inbox]</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Stat 4: KB Count -->
                    <div class="flex flex-col gap-3 p-5 rounded-xl bg-[#0a101f] border border-gray-800 shadow-sm hover:border-gray-700 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="size-10 rounded-xl bg-emerald-900/20 text-emerald-500 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px] filled">library_books</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-500 text-[10px] uppercase font-black tracking-widest">Artikel Pengetahuan</p>
                            @if($stats['kb_count'] > 0)
                                <p class="text-2xl font-black text-white mt-1">{{ $stats['kb_count'] }}</p>
                            @else
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">Belum ada data</p>
                                    <a href="{{ route('kb.index') }}" class="text-[10px] text-blue-500 font-bold hover:underline mt-1">[Buat Artikel Baru]</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main Chart Section with Chart.js -->
                <div class="flex flex-col gap-6 p-6 rounded-xl bg-surface-dark border border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-white">Volume Interaksi</h3>
                            <p class="text-sm text-gray-500">Tren pesan masuk vs respon bot 7 hari terakhir</p>
                        </div>
                        <div class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-tight">
                            <span class="flex items-center gap-1.5 text-blue-400"><span class="size-2 rounded-full bg-blue-500"></span> Pesan Masuk</span>
                            <span class="flex items-center gap-1.5 text-purple-400"><span class="size-2 rounded-full bg-purple-500"></span> Respon Bot AI</span>
                        </div>
                    </div>
                    <div class="w-full h-[240px] relative">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Top Questions Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="flex flex-col gap-4 p-6 rounded-xl bg-surface-dark border border-slate-800 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white">Pertanyaan Populer</h3>
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Input Tertinggi</span>
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
                            <div class="text-center text-slate-500 py-4">{{ __('dashboard.no_questions') }}</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Usage Widget in Column --}}
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between px-1">
                            <h3 class="text-lg font-bold text-white">Limit Penggunaan</h3>
                        </div>
                        @include('components.usage-widget', ['user' => $user, 'cols' => 1])
                    </div>
                    <!-- Response Time Card -->
                    <div class="flex flex-col gap-4 p-6 rounded-xl bg-surface-dark border border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 size-20 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-all"></div>
                        <h3 class="text-lg font-bold text-white relative">Wawasan AI</h3>
                        <div class="space-y-4 relative">
                            <div class="flex items-center gap-4 p-4 rounded-xl bg-blue-500/10 border border-blue-500/20">
                                <span class="material-symbols-outlined text-blue-500 text-3xl filled">insights</span>
                                <div>
                                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Prediksi Kuota</p>
                                    <p class="text-lg font-bold text-white">
                                        ± {{ $stats['forecast_days'] }} Hari Lagi
                                    </p>
                                    <p class="text-[9px] text-slate-500 italic">{{ __('dashboard.forecast_desc') }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1 p-3 rounded-lg bg-slate-800/50">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">{{ __('dashboard.avg_speed') }}</span>
                                    <p class="text-xl font-black text-emerald-500">{{ $stats['avg_response_time'] }}s</p>
                                </div>
                                <div class="flex flex-col gap-1 p-3 rounded-lg bg-slate-800/50">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">{{ __('dashboard.success_rate') }}</span>
                                    <p class="text-xl font-black text-primary">{{ $stats['ai_rate'] }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lower Section: Quick Actions & Activity -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Quick Actions -->
                    <div class="lg:col-span-1 flex flex-col gap-4">
                        <h3 class="text-sm font-black text-gray-500 uppercase tracking-widest px-1">Aksi Cepat</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('whatsapp.inbox') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-xl bg-[#0a101f] border border-gray-800 hover:border-blue-500/50 group transition-all">
                                <div class="p-3 bg-blue-900/20 text-blue-500 rounded-full group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined filled">chat</span>
                                </div>
                                <span class="text-xs font-bold text-gray-300">Buka Chat</span>
                            </a>
                            <a href="{{ route('kb.index') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-xl bg-[#0a101f] border border-gray-800 hover:border-blue-500/50 group transition-all">
                                <div class="p-3 bg-purple-900/20 text-purple-500 rounded-full group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined filled">school</span>
                                </div>
                                <span class="text-xs font-bold text-gray-300">Setup AI</span>
                            </a>
                            <a href="{{ route('whatsapp.broadcast.index') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-xl bg-[#0a101f] border border-gray-800 hover:border-blue-500/50 group transition-all">
                                <div class="p-3 bg-orange-900/20 text-orange-500 rounded-full group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined filled">campaign</span>
                                </div>
                                <span class="text-xs font-bold text-gray-300">Kirim Promo</span>
                            </a>
                             <a href="{{ route('settings.business') }}" class="flex flex-col items-center justify-center gap-3 p-6 rounded-xl bg-[#0a101f] border border-gray-800 hover:border-blue-500/50 group transition-all">
                                <div class="p-3 bg-gray-800 text-gray-400 rounded-full group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined filled">settings</span>
                                </div>
                                <span class="text-xs font-bold text-gray-300">Profil Bisnis</span>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity / Notifications / Empty State -->
                    <div class="lg:col-span-2 flex flex-col gap-4">
                        <div class="flex items-center justify-between px-1">
                            <h3 class="text-sm font-black text-gray-500 uppercase tracking-widest">Aktivitas Terakhir</h3>
                        </div>
                        
                        @if($stats['total_messages'] == 0 && count($activities) == 0)
                            {{-- Empty State --}}
                            <div class="flex flex-col items-center justify-center p-12 bg-[#0a101f] border border-dashed border-gray-800 rounded-xl">
                                <div class="bg-blue-500/10 p-6 rounded-full mb-6">
                                    <span class="material-symbols-outlined text-blue-500 text-[48px]">chat_bubble</span>
                                </div>
                                <h3 class="text-lg font-bold text-white">Belum ada percakapan hari ini</h3>
                                <p class="text-gray-500 text-center text-sm max-w-sm mt-1">
                                    Hubungkan WhatsApp Anda untuk mulai menerima pesan dan biarkan AI kami membantu membalasnya.
                                </p>
                                <a href="{{ route('whatsapp.settings') }}" class="mt-8 flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-900/20">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    Hubungkan WhatsApp Sekarang
                                </a>
                            </div>
                        @else
                            <div class="flex flex-col rounded-xl bg-[#0a101f] border border-gray-800 overflow-hidden divide-y divide-gray-800/50">
                                @forelse($activities as $activity)
                                <div class="flex items-center gap-4 p-4 hover:bg-gray-900/50 transition-colors cursor-pointer group">
                                    <div class="relative flex-shrink-0">
                                        <div class="size-10 rounded-full bg-blue-600/10 border border-blue-500/20 flex items-center justify-center text-blue-400 font-black text-sm">
                                             {{ strtoupper(substr($activity['user'], 0, 1)) }}
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 bg-green-500 rounded-full p-0.5 border-2 border-[#0a101f]">
                                            <span class="material-symbols-outlined text-[10px] text-white block">chat</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-0.5">
                                            <p class="text-sm font-bold text-white truncate group-hover:text-blue-400 transition-colors">{{ $activity['user'] }}</p>
                                            <span class="text-[10px] font-bold text-gray-500">{{ $activity['time'] }}</span>
                                        </div>
                                        <p class="text-xs text-gray-400 truncate">{{ $activity['content'] }}</p>
                                    </div>
                                </div>
                                @empty
                                <div class="p-12 text-center text-gray-600 text-sm">
                                    Belum ada aktivitas baru
                                </div>
                                @endforelse
                            </div>
                        @endif
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
