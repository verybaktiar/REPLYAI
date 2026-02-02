<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <title>{{ $title ?? 'Dashboard | ReplyAI' }}</title>
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#030712">
    <link rel="apple-touch-icon" href="/logo-round.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                        gray: {
                            950: '#030712',
                            900: '#0f172a',
                            800: '#1e293b',
                        },
                        indigo: {
                            950: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        
        .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        
        .root-cage { height: 100dvh; display: flex; overflow: hidden; background-color: #030712; }
        .glass-header { backdrop-filter: blur(12px); background-color: rgba(3, 7, 18, 0.7); border-bottom: 1px solid rgba(30, 41, 59, 0.5); }
    </style>
</head>
<body class="bg-gray-950 text-white font-sans antialiased">

    <div class="root-cage">
        
        @include('components.sidebar')

        <!-- MAIN VIEWPORT -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- STICKY HEADER -->
            <header class="h-16 flex-shrink-0 flex items-center justify-between px-6 glass-header z-30">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-extrabold tracking-tight text-white uppercase italic">Dashboard</h1>
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-full">
                        <div class="size-2 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Sistem Aktif</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-bold text-gray-300">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    @include('components.language-switcher')
                </div>
            </header>

            <!-- SCROLLABLE CONTENT -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 lg:p-12 pb-24 lg:pb-12 space-y-8 scroll-smooth custom-scrollbar">
                <div class="max-w-7xl mx-auto space-y-8 pb-12">

                    <!-- STEPPER / ONBOARDING [IF INCOMPLETE] -->
                    @if($setup_progress < 100)
                    <section class="relative overflow-hidden bg-indigo-950/20 border border-indigo-500/20 rounded-3xl p-8 group">
                        <!-- Decorative Glow -->
                        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] -mr-32 -mt-32 group-hover:bg-indigo-500/15 transition-all duration-700"></div>
                        
                        <div class="relative z-10 flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-4">
                                    <h2 class="text-3xl font-extrabold tracking-tighter text-white">Langkah Awal</h2>
                                    <div class="flex items-center gap-2 px-3 py-1 bg-indigo-500 text-white rounded-xl shadow-xl shadow-indigo-950/40">
                                        <span class="text-xs font-black uppercase tracking-widest">{{ round($setup_progress) }}% Selesai</span>
                                    </div>
                                </div>
                                <p class="mt-2 text-indigo-200/60 font-medium max-w-2xl text-sm">
                                    Optimalkan ReplyAI dengan menyelesaikan konfigurasi dasar. Chatbot Anda akan lebih pintar setiap langkahnya.
                                </p>
                                
                                <div class="mt-8 flex flex-wrap gap-4">
                                    @php
                                        $steps = [
                                            ['key' => 'account', 'label' => 'Daftar Akun', 'done' => true],
                                            ['key' => 'wa_connected', 'label' => 'Hubungkan WA', 'done' => $onboarding['wa_connected']],
                                            ['key' => 'kb_added', 'label' => 'Knowledge Base', 'done' => $onboarding['kb_added']],
                                            ['key' => 'chat_tested', 'label' => 'Test Chat', 'done' => $onboarding['chat_tested']],
                                            ['key' => 'ai_active', 'label' => 'Aktifkan AI', 'done' => $onboarding['ai_active']],
                                        ];
                                    @endphp

                                    @foreach($steps as $step)
                                    <div class="flex items-center gap-2.5 px-4 py-2 rounded-2xl border transition-all duration-300 {{ $step['done'] ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-white/5 border-white/10 text-gray-500' }}">
                                        <span class="material-symbols-outlined text-[18px] {{ $step['done'] ? 'filled' : '' }}">{{ $step['done'] ? 'check_circle' : 'pending' }}</span>
                                        <span class="text-[11px] font-black uppercase tracking-wider">{{ $step['label'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 min-w-[220px] w-full lg:w-auto">
                                <a href="{{ route('whatsapp.settings') }}" class="flex items-center justify-center gap-3 px-8 py-5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-2xl shadow-indigo-950/60">
                                    Mulai Sekarang
                                    <span class="material-symbols-outlined text-[20px]">bolt</span>
                                </a>
                                <button class="text-[10px] font-black text-gray-500 hover:text-white uppercase tracking-widest transition-colors text-center">Lewati Dulu</button>
                            </div>
                        </div>
                    </section>
                    @endif

                    <!-- HIGH PERFORMANCE STATS -->
                    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                        
                        <!-- 1. TOTAL MESSAGES -->
                        <div class="relative overflow-hidden bg-gray-900 border border-gray-800 rounded-3xl p-6 group hover:border-gray-700 transition-all duration-500">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-blue-500/20 transition-all"></div>
                            <div class="relative z-10 flex flex-col h-full">
                                <div class="flex items-center justify-between mb-8">
                                    <div class="size-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-blue-500 text-[28px]">forum</span>
                                    </div>
                                    @if($stats['total_messages'] > 0)
                                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-blue-500/10 border border-blue-500/20 rounded-full">
                                        <span class="material-symbols-outlined text-[14px] text-blue-400">trending_up</span>
                                        <span class="text-[10px] font-black text-blue-400 uppercase">{{ $stats['msg_trend'] }}%</span>
                                    </div>
                                    @endif
                                </div>
                                @if($stats['total_messages'] > 0)
                                    <h3 class="text-4xl font-black tracking-tighter text-white">{{ number_format($stats['total_messages'], 0, ',', '.') }}</h3>
                                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mt-1">Total Pesan Masuk</p>
                                @else
                                    <h3 class="text-lg font-semibold text-gray-300">Hubungkan</h3>
                                    <p class="text-xs text-gray-500 mt-1">Aktifkan WhatsApp untuk menerima pesan</p>
                                    <a href="{{ route('whatsapp.settings') }}" class="text-blue-400 text-[10px] font-bold mt-2 hover:underline">Koneksi →</a>
                                @endif
                            </div>
                        </div>

                        <!-- 2. AI RESOLUTION -->
                        <div class="relative overflow-hidden bg-gray-900 border border-gray-800 rounded-3xl p-6 group hover:border-gray-700 transition-all duration-500">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-purple-500/20 transition-all"></div>
                            <div class="relative z-10 flex flex-col h-full">
                                <div class="flex items-center justify-between mb-8">
                                    <div class="size-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-purple-500 text-[28px]">smart_toy</span>
                                    </div>
                                    @if($stats['ai_responses'] > 0)
                                    <div class="flex items-center gap-1.5 px-2.5 py-1 bg-purple-500/10 border border-purple-500/20 rounded-full">
                                        <span class="material-symbols-outlined text-[14px] text-purple-400">auto_awesome</span>
                                        <span class="text-[10px] font-black text-purple-400 uppercase">{{ $stats['ai_rate'] }}%</span>
                                    </div>
                                    @endif
                                </div>
                                @if($stats['ai_responses'] > 0)
                                    <h3 class="text-4xl font-black tracking-tighter text-white">{{ number_format($stats['ai_responses'], 0, ',', '.') }}</h3>
                                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mt-1">Direspon AI</p>
                                @else
                                    <h3 class="text-lg font-semibold text-gray-300">Konfigurasi</h3>
                                    <p class="text-xs text-gray-500 mt-1">Setup Knowledge Base untuk auto-reply</p>
                                    <a href="{{ route('kb.index') }}" class="text-purple-400 text-[10px] font-bold mt-2 hover:underline">Setup AI →</a>
                                @endif
                            </div>
                        </div>

                        <!-- 3. PENDING CHATS -->
                        <div class="relative overflow-hidden bg-gray-900 border border-gray-800 rounded-3xl p-6 group hover:border-gray-700 transition-all duration-500">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-orange-500/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-orange-500/20 transition-all"></div>
                            <div class="relative z-10 flex flex-col h-full">
                                <div class="flex items-center justify-between mb-8">
                                    <div class="size-12 rounded-2xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-orange-500 text-[28px]">pending_actions</span>
                                    </div>
                                    @if($stats['pending_replies'] > 0)
                                    <div class="px-2.5 py-1 bg-red-500 text-white rounded-full animate-pulse shadow-[0_0_15px_rgba(239,68,68,0.4)]">
                                        <span class="text-[9px] font-black uppercase tracking-widest">Urgent</span>
                                    </div>
                                    @endif
                                </div>
                                <h3 class="text-4xl font-black tracking-tighter text-white">{{ number_format($stats['pending_replies'], 0, ',', '.') }}</h3>
                                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mt-1">Menunggu Balasan</p>
                            </div>
                        </div>

                        <!-- 4. KNOWLEDGE BASE -->
                        <div class="relative overflow-hidden bg-gray-900 border border-gray-800 rounded-3xl p-6 group hover:border-gray-700 transition-all duration-500">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-emerald-500/20 transition-all"></div>
                            <div class="relative z-10 flex flex-col h-full">
                                <div class="flex items-center justify-between mb-8">
                                    <div class="size-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-emerald-500 text-[28px]">menu_book</span>
                                    </div>
                                </div>
                                <h3 class="text-4xl font-black tracking-tighter text-white">{{ number_format($stats['kb_articles'], 0, ',', '.') }}</h3>
                                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mt-1">Artikel Pengetahuan</p>
                            </div>
                        </div>

                    </section>

                    <!-- ANALYTICS & ACTIVITY -->
                    <section class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <!-- MAIN CHART -->
                        <div class="lg:col-span-8 bg-gray-900 border border-gray-800 rounded-[2.5rem] p-10 shadow-2xl">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-6 mb-12">
                                <div>
                                    <h3 class="text-2xl font-black tracking-tight text-white uppercase italic">Volume Percakapan</h3>
                                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em] mt-1">Laporan 7 Hari Terakhir</p>
                                </div>
                                <div class="flex items-center gap-6 bg-gray-950/50 p-2 rounded-2xl border border-gray-800">
                                    <div class="flex items-center gap-2 px-3">
                                        <div class="size-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></div>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Customer</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-3">
                                        <div class="size-2 rounded-full bg-purple-500 shadow-[0_0_8px_rgba(168,85,247,0.5)]"></div>
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">AI Agent</span>
                                    </div>
                                </div>
                            </div>

                            <div class="h-[300px] md:h-[400px] relative">
                                @if(collect($trend7Days)->sum('messages') == 0)
                                <div class="flex flex-col items-center justify-center h-full text-center relative">
                                    <div class="absolute inset-0 bg-blue-500/5 blur-[100px] rounded-full"></div>
                                    <div class="w-24 h-24 rounded-[2rem] bg-gray-800 flex items-center justify-center mb-8 border border-gray-700 shadow-2xl">
                                        <span class="material-symbols-outlined text-gray-600 text-[48px] animate-pulse">analytics</span>
                                    </div>
                                    <h4 class="text-xl font-black text-gray-400 uppercase tracking-tight">Menunggu Data Pertama</h4>
                                    <p class="text-xs text-gray-600 max-w-xs mt-3 font-medium uppercase tracking-widest">Hubungkan WhatsApp Anda untuk melihat visualisasi AI secara real-time.</p>
                                    <a href="{{ route('whatsapp.settings') }}" class="mt-6 flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                        Hubungkan WhatsApp
                                    </a>
                                </div>
                                @else
                                <canvas id="mainDashboardChart"></canvas>
                                @endif
                            </div>
                        </div>

                        <!-- SIDEBAR WIDGETS -->
                        <div class="lg:col-span-4 space-y-8">
                            
                            <!-- QUICK ACTIONS -->
                            <div class="bg-gray-900 border border-gray-800 rounded-[2.5rem] p-8 shadow-xl">
                                <h3 class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em] mb-8 text-center italic">NAVIGASI CEPAT</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <a href="{{ route('whatsapp.inbox') }}" class="flex flex-col items-center justify-center gap-4 p-6 bg-gray-950 border border-gray-800 rounded-3xl hover:border-blue-500/30 hover:bg-blue-500/5 transition-all group">
                                        <div class="size-12 rounded-2xl bg-gray-900 flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-600/10 transition-all">
                                            <span class="material-symbols-outlined text-gray-500 group-hover:text-blue-400">chat_bubble</span>
                                        </div>
                                        <span class="text-[10px] font-black text-gray-500 group-hover:text-white uppercase tracking-widest">INBOX</span>
                                    </a>
                                    <a href="{{ route('kb.index') }}" class="flex flex-col items-center justify-center gap-4 p-6 bg-gray-950 border border-gray-800 rounded-3xl hover:border-purple-500/30 hover:bg-purple-500/5 transition-all group">
                                        <div class="size-12 rounded-2xl bg-gray-900 flex items-center justify-center group-hover:scale-110 group-hover:bg-purple-600/10 transition-all">
                                            <span class="material-symbols-outlined text-gray-500 group-hover:text-purple-400">psychology</span>
                                        </div>
                                        <span class="text-[10px] font-black text-gray-500 group-hover:text-white uppercase tracking-widest">SETUP AI</span>
                                    </a>
                                    <a href="{{ route('whatsapp.broadcast.index') }}" class="flex flex-col items-center justify-center gap-4 p-6 bg-gray-950 border border-gray-800 rounded-3xl hover:border-orange-500/30 hover:bg-orange-500/5 transition-all group">
                                        <div class="size-12 rounded-2xl bg-gray-900 flex items-center justify-center group-hover:scale-110 group-hover:bg-orange-600/10 transition-all">
                                            <span class="material-symbols-outlined text-gray-500 group-hover:text-orange-400">campaign</span>
                                        </div>
                                        <span class="text-[10px] font-black text-gray-500 group-hover:text-white uppercase tracking-widest">BROADCAST</span>
                                    </a>
                                    <a href="{{ route('settings.business') }}" class="flex flex-col items-center justify-center gap-4 p-6 bg-gray-950 border border-gray-800 rounded-3xl hover:border-white/30 hover:bg-white/5 transition-all group">
                                        <div class="size-12 rounded-2xl bg-gray-900 flex items-center justify-center group-hover:scale-110 group-hover:bg-white/10 transition-all">
                                            <span class="material-symbols-outlined text-gray-500 group-hover:text-white">business_center</span>
                                        </div>
                                        <span class="text-[10px] font-black text-gray-500 group-hover:text-white uppercase tracking-widest">PROFIL</span>
                                    </a>
                                </div>
                            </div>

                            <!-- RECENT ACTIVITY -->
                            <div class="bg-gray-900 border border-gray-800 rounded-[2.5rem] p-8 shadow-xl">
                                <h3 class="text-[10px] font-black text-gray-600 uppercase tracking-[0.2em] mb-6 px-2 italic">Aktivitas Live</h3>
                                <div class="space-y-4">
                                    @forelse($activities as $activity)
                                    <div class="flex gap-4 p-4 rounded-3xl bg-gray-950 border border-gray-800/50 hover:bg-white/[0.02] transition-colors relative overflow-hidden group">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 {{ $activity['type'] == 'contact' ? 'bg-blue-600' : 'bg-purple-600' }}"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-black text-white leading-tight truncate uppercase tracking-tight">{{ $activity['text'] }}</p>
                                            <p class="text-[9px] font-black text-gray-600 mt-1 uppercase tracking-[0.15em]">{{ $activity['time'] }}</p>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="py-12 text-center opacity-30">
                                        <span class="material-symbols-outlined text-[40px] block mb-2">inbox</span>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Belum ada aktivitas</span>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- VIP MEMBER CARD -->
                            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 border border-white/10 rounded-[2.5rem] p-8 relative overflow-hidden group shadow-2xl">
                                <div class="absolute right-0 bottom-0 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl -mr-16 -mb-16"></div>
                                <div class="relative z-10 flex items-center gap-6">
                                    <div class="size-16 rounded-[1.5rem] bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center shadow-2xl shadow-indigo-950">
                                        <span class="material-symbols-outlined text-indigo-300 text-[32px]">insights</span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Estimasi Kuota</p>
                                        <p class="text-2xl font-black text-white tracking-tighter mt-1 italic">± {{ $stats['forecast_days'] }} Hari Tersisa</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <!-- Chart JS Scripts -->
    @if(collect($trend7Days)->sum('messages') > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('mainDashboardChart').getContext('2d');
            const trendData = @json($trend7Days);
            
            const blueGradient = ctx.createLinearGradient(0, 0, 0, 400);
            blueGradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
            blueGradient.addColorStop(1, 'rgba(3, 7, 18, 0)');

            const purpleGradient = ctx.createLinearGradient(0, 0, 0, 400);
            purpleGradient.addColorStop(0, 'rgba(168, 85, 247, 0.3)');
            purpleGradient.addColorStop(1, 'rgba(3, 7, 18, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.date),
                    datasets: [
                        {
                            label: 'Customer',
                            data: trendData.map(d => d.messages),
                            borderColor: '#3b82f6',
                            borderWidth: 6,
                            backgroundColor: blueGradient,
                            fill: true,
                            tension: 0.45,
                            pointRadius: 0,
                            pointHoverRadius: 10,
                            pointHoverBackgroundColor: '#3b82f6',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 4,
                        },
                        {
                            label: 'AI Agent',
                            data: trendData.map(d => d.ai_replies),
                            borderColor: '#a855f7',
                            borderWidth: 6,
                            backgroundColor: purpleGradient,
                            fill: true,
                            tension: 0.45,
                            pointRadius: 0,
                            pointHoverRadius: 10,
                            pointHoverBackgroundColor: '#a855f7',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 4,
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
                            backgroundColor: 'rgba(3, 7, 18, 0.95)',
                            padding: 24,
                            cornerRadius: 24,
                            titleFont: { size: 12, weight: 'bold', family: "'Plus Jakarta Sans', sans-serif" },
                            bodyFont: { size: 16, weight: '800', family: "'Plus Jakarta Sans', sans-serif" },
                            boxPadding: 10,
                            borderWidth: 1,
                            borderColor: 'rgba(30, 41, 59, 0.8)',
                            usePointStyle: true,
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { 
                                color: '#475569', 
                                font: { size: 11, weight: '800', family: "'Plus Jakarta Sans', sans-serif" },
                                padding: 15
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(30, 41, 59, 0.5)', drawTicks: false, drawBorder: false },
                            ticks: { 
                                color: '#475569', 
                                font: { size: 11, weight: '800', family: "'Plus Jakarta Sans', sans-serif" },
                                padding: 15,
                                stepSize: 5
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endif

    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('SW Registered'))
                    .catch(err => console.log('SW Error', err));
            });
        }
    </script>
</body>
</html>

