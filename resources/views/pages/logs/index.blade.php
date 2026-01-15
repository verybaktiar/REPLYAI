@php
    // Simple Stats Calculation based on fetched logs
    $totalLogs = $logs->count();
    $errorLogs = $logs->where('status', 'failed')->count();
    $aiLogs = $logs->where('response_source', 'ai')->count();
    $errorRate = $totalLogs > 0 ? round(($errorLogs / $totalLogs) * 100, 1) : 0;
    
    // Most active source (Dummy logic for now)
    $topSource = 'Rule-based Bot';
    if ($aiLogs > ($totalLogs / 2)) {
        $topSource = 'AI Assistant';
    }
@endphp
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Log Aktivitas - REPLYAI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                        "surface-dark": "#111722",
                        "surface-highlight": "#232f48",
                        "text-secondary": "#92a4c9",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
         ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #232f48; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display antialiased overflow-x-hidden">
<div class="relative flex flex-col lg:flex-row h-screen w-full bg-background-light dark:bg-background-dark overflow-hidden">
    
    <!-- Sidebar -->
<!-- Sidebar Navigation -->
@include('components.sidebar')

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col h-full overflow-hidden pt-14 lg:pt-0">
        <!-- Top Navbar -->
        <header class="flex items-center justify-between border-b border-surface-highlight bg-surface-dark px-6 py-3 shrink-0 z-20">
            <div class="flex items-center gap-4 text-white lg:hidden">
                <button class="p-1 rounded-md hover:bg-surface-highlight">
                    <span class="material-symbols-outlined text-[24px]">menu</span>
                </button>
                <h2 class="text-lg font-bold leading-tight tracking-tight">REPLYAI Logs</h2>
            </div>
            <!-- Breadcrumbs/Title (Desktop) -->
            <div class="hidden lg:flex items-center gap-2 text-text-secondary text-sm">
                <span>Admin</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span class="text-white font-medium">Log Aktivitas</span>
            </div>
        </header>

        <!-- Scrollable Page Content -->
        <main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark scrollbar-thin scrollbar-thumb-surface-highlight scrollbar-track-transparent">
            <div class="container mx-auto max-w-[1200px] p-6 pb-20 flex flex-col gap-6">
                <!-- Page Heading -->
                <div class="flex flex-wrap justify-between items-end gap-4">
                    <div class="flex flex-col gap-2">
                        <h1 class="text-white text-3xl font-black leading-tight tracking-tight">Log Aktivitas</h1>
                        <p class="text-text-secondary text-base font-normal max-w-2xl">Rekaman kronologis semua event, interaksi bot, dan error sistem.</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="window.location.reload()" class="flex items-center justify-center gap-2 h-10 px-4 rounded-lg border border-surface-highlight bg-surface-dark text-white text-sm font-bold hover:bg-surface-highlight transition-colors">
                            <span class="material-symbols-outlined text-[20px]">refresh</span>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1 rounded-xl p-5 border border-surface-highlight bg-surface-dark shadow-sm">
                        <div class="flex justify-between items-start">
                            <p class="text-text-secondary text-sm font-medium">Total Log (Limit)</p>
                            <span class="material-symbols-outlined text-text-secondary text-[20px]">bar_chart</span>
                        </div>
                        <div class="flex items-end gap-3 mt-2">
                            <p class="text-white text-3xl font-bold leading-none">{{ number_format($totalLogs) }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 rounded-xl p-5 border border-surface-highlight bg-surface-dark shadow-sm">
                        <div class="flex justify-between items-start">
                            <p class="text-text-secondary text-sm font-medium">Error Rate</p>
                            <span class="material-symbols-outlined text-text-secondary text-[20px]">warning</span>
                        </div>
                        <div class="flex items-end gap-3 mt-2">
                            <p class="text-white text-3xl font-bold leading-none">{{ $errorRate }}%</p>
                            <p class="{{ $errorRate > 0 ? 'text-rose-500' : 'text-emerald-500' }} text-sm font-medium mb-1 flex items-center">
                                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                                {{ $errorLogs }} errors
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 rounded-xl p-5 border border-surface-highlight bg-surface-dark shadow-sm">
                        <div class="flex justify-between items-start">
                            <p class="text-text-secondary text-sm font-medium">Top Source</p>
                            <span class="material-symbols-outlined text-text-secondary text-[20px]">smart_toy</span>
                        </div>
                        <div class="mt-2">
                            <p class="text-white text-2xl font-bold leading-tight truncate">{{ $topSource }}</p>
                            <p class="text-text-secondary text-xs mt-1">Based on recent activity</p>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search Toolbar (Visual Only for now) -->
                <div class="flex flex-col gap-4 bg-surface-dark border border-surface-highlight rounded-xl p-4">
                    <form action="{{ route('logs.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-text-secondary">search</span>
                            </div>
                            <input name="search" value="{{ request('search') }}" class="block w-full pl-10 pr-3 py-2.5 border border-surface-highlight rounded-lg leading-5 bg-[#1a2436] text-white placeholder-text-secondary focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm" placeholder="Cari Log ID, Keyword, atau Pesan..." type="text"/>
                        </div>
                        <!-- Filters -->
                         <div class="flex flex-wrap gap-2">
                            <div class="relative min-w-[140px]">
                                <select name="status" onchange="this.form.submit()" class="appearance-none block w-full pl-3 pr-10 py-2.5 border border-surface-highlight rounded-lg leading-5 bg-[#1a2436] text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm cursor-pointer">
                                    <option value="">Semua Status</option>
                                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Error</option>
                                    <option value="no_match" {{ request('status') == 'no_match' ? 'selected' : '' }}>No Match</option>
                                </select>
                            </div>
                            
                             <div class="relative min-w-[100px]">
                                <select name="limit" onchange="this.form.submit()" class="appearance-none block w-full pl-3 pr-8 py-2.5 border border-surface-highlight rounded-lg leading-5 bg-[#1a2436] text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm cursor-pointer" title="Baris per halaman">
                                    <option value="10" {{ request('limit') == '10' ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ (request('limit') == '20' || !request('limit')) ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('limit') == '50' ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('limit') == '100' ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ request('limit') == 'all' ? 'selected' : '' }}>Semua</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-text-secondary">
                                     <span class="material-symbols-outlined text-[20px]">expand_more</span>
                                </div>
                            </div>
                         </div>
                    </form>
                </div>

                <!-- Logs Table -->
                <div class="bg-surface-dark border border-surface-highlight rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-text-secondary">
                            <thead class="bg-[#1a2436] text-xs uppercase font-semibold text-white">
                                <tr>
                                    <th class="px-6 py-4" scope="col">Timestamp</th>
                                    <th class="px-6 py-4" scope="col">Status</th>
                                    <th class="px-6 py-4" scope="col">Platform</th>
                                    <th class="px-6 py-4" scope="col">Trigger / User</th>
                                    <th class="px-6 py-4 w-1/3" scope="col">Response / Error</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-highlight">
                                @forelse($logs as $log)
                                    @php
                                        // Mapping Status Color
                                        $statusColor = 'bg-gray-500/10 text-gray-500 border-gray-500/20';
                                        $statusDot = 'bg-gray-500';
                                        
                                        if($log->status == 'success'){
                                            $statusColor = 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
                                            $statusDot = 'bg-emerald-500';
                                        } elseif($log->status == 'failed'){
                                            $statusColor = 'bg-rose-500/10 text-rose-500 border-rose-500/20';
                                            $statusDot = 'bg-rose-500';
                                        } elseif($log->status == 'no_match' || $log->status == 'fallback'){
                                            $statusColor = 'bg-amber-500/10 text-amber-500 border-amber-500/20';
                                            $statusDot = 'bg-amber-500';
                                        }
                                        
                                        // Platform Icon
                                        $platform = strtolower($log->conversation->source ?? 'system');
                                        $pIcon = 'dns'; $pColor = 'text-gray-400';
                                        if(str_contains($platform, 'whatsapp')) { $pIcon = 'chat'; $pColor = 'text-emerald-500'; }
                                        elseif(str_contains($platform, 'instagram')) { $pIcon = 'photo_camera'; $pColor = 'text-pink-500'; }
                                    @endphp
                                    <tr class="hover:bg-[#1a2436]/50 transition-colors group">
                                        <!-- Timestamp -->
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-white">
                                            {{ $log->created_at->format('Y-m-d') }} 
                                            <span class="text-text-secondary">{{ $log->created_at->format('H:i:s') }}</span>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }} border">
                                                <span class="size-1.5 rounded-full {{ $statusDot }}"></span>
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        
                                        <!-- Platform -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2 text-white capitalize">
                                                <span class="material-symbols-outlined {{ $pColor }}" style="font-size: 18px;">{{ $pIcon }}</span>
                                                {{ $platform }}
                                            </div>
                                        </td>
                                        
                                        <!-- User/Trigger -->
                                        <td class="px-6 py-4">
                                            <div class="text-white font-medium truncate max-w-[150px]" title="{{ $log->trigger_text }}">
                                                "{{ \Illuminate\Support\Str::limit($log->trigger_text, 20) }}"
                                            </div>
                                            <div class="text-xs text-text-secondary mt-0.5">
                                                by {{ $log->conversation->display_name ?? 'Unknown User' }}
                                            </div>
                                        </td>
                                        
                                        <!-- Details -->
                                        <td class="px-6 py-4">
                                            @if($log->status == 'failed')
                                                <p class="text-rose-400 font-medium truncate">System Error</p>
                                                <p class="text-xs truncate max-w-[300px] text-text-secondary" title="{{ $log->error_message }}">{{ $log->error_message ?? 'Unknown Error' }}</p>
                                            @else
                                                <p class="text-white font-medium truncate capitalize">via {{ $log->response_source }}</p>
                                                <p class="text-xs truncate max-w-[300px] text-text-secondary" title="{{ $log->response_text }}">
                                                    {{ \Illuminate\Support\Str::limit($log->response_text, 50) }}
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-text-secondary">
                                            Tidak ada log aktivitas hari ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Footer Info similar to user's pagination -->
                    <div class="flex items-center justify-between border-t border-surface-highlight bg-[#1a2436] px-6 py-3">
                        <div class="text-sm text-text-secondary">
                            Menampilkan {{ $logs->firstItem() ?? 0 }} sampai {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log.
                        </div>
                        <div class="flex gap-1">
                            {{ $logs->appends(request()->query())->links() }} 
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
