<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Analisis &amp; Laporan - REPLYAI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Tailwind Configuration -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#111722",
                        "surface-dark": "#192233",
                        "border-dark": "#324467",
                        "text-secondary": "#92a4c9",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        /* Custom scrollbar for dashboard tables */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111722; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #324467; border-radius: 10px; }
        .conic-chart { background: conic-gradient(#135bec 0% {{ $waPercentage }}%, #38bdf8 {{ $waPercentage }}% 100%); }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row">

<!-- Sidebar Navigation -->
@include('components.sidebar')


<!-- Main Content Area -->
<main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
    <!-- Scrollable content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 lg:p-10 pb-20">
        <div class="max-w-[1200px] mx-auto flex flex-col gap-8">
            <!-- Header & Controls -->
            <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-3">
                        <h2 class="text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em] text-white">Statistik</h2>
                        @include('components.page-help', [
                            'title' => 'Statistik',
                            'description' => 'Lihat laporan performa chatbot dan aktivitas pelanggan.',
                            'tips' => [
                                'Total Conversations = jumlah percakapan dengan pelanggan',
                                'Bot Resolution Rate = persentase pesan yang dijawab bot',
                                'Human Handoff = persentase yang ditangani CS',
                                'Export Report untuk download data'
                            ]
                        ])
                    </div>
                    <p class="text-text-secondary text-base font-normal">Ringkasan performa chatbot Anda.</p>
                </div>
                <!-- Filters Toolbar -->
                <div class="flex flex-wrap items-end gap-2 sm:gap-3 w-full xl:w-auto">
                    <!-- Select Bot -->
                    <div class="flex flex-col gap-1.5 flex-1 min-w-[120px] sm:flex-none sm:w-auto">
                        <label class="text-[10px] sm:text-xs font-semibold text-text-secondary uppercase tracking-wider">Bot</label>
                        <div class="relative">
                            <select id="botFilter" onchange="applyFilters()" class="appearance-none bg-surface-dark border border-border-dark text-white text-xs sm:text-sm rounded-lg focus:ring-primary focus:border-primary block w-full sm:w-36 p-2 sm:p-2.5 pr-7">
                                <option value="all" {{ ($currentBot ?? 'all') == 'all' ? 'selected' : '' }}>All Bots</option>
                                <option value="appointment" {{ ($currentBot ?? '') == 'appointment' ? 'selected' : '' }}>Appointment</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-text-secondary">
                                <span class="material-symbols-outlined text-sm">expand_more</span>
                            </div>
                        </div>
                    </div>
                    <!-- Platform -->
                    <div class="flex flex-col gap-1.5 flex-1 min-w-[120px] sm:flex-none sm:w-auto">
                        <label class="text-[10px] sm:text-xs font-semibold text-text-secondary uppercase tracking-wider">Platform</label>
                        <div class="relative">
                            <select id="platformFilter" onchange="applyFilters()" class="appearance-none bg-surface-dark border border-border-dark text-white text-xs sm:text-sm rounded-lg focus:ring-primary focus:border-primary block w-full sm:w-32 p-2 sm:p-2.5 pr-7">
                                <option value="all" {{ ($currentPlatform ?? 'all') == 'all' ? 'selected' : '' }}>All</option>
                                <option value="whatsapp" {{ ($currentPlatform ?? '') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="instagram" {{ ($currentPlatform ?? '') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-text-secondary">
                                <span class="material-symbols-outlined text-sm">expand_more</span>
                            </div>
                        </div>
                    </div>
                    <!-- Export Button -->
                    <button onclick="exportReport()" class="bg-primary hover:bg-blue-600 text-white font-medium rounded-lg text-xs sm:text-sm px-3 sm:px-5 py-2 sm:py-2.5 flex items-center gap-1.5 sm:gap-2 transition-colors ml-auto sm:ml-0 h-[36px] sm:h-[42px] mt-auto">
                        <span class="material-symbols-outlined" style="font-size: 18px;">download</span>
                        <span class="hidden sm:inline">Export Report</span>
                        <span class="sm:hidden">Export</span>
                    </button>
                </div>
            </div>

            <!-- KPI Scorecards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Chats -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-5 flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="p-2 bg-blue-500/10 rounded-lg">
                            <span class="material-symbols-outlined text-blue-400" style="font-size: 24px;">forum</span>
                        </div>
                        <span class="flex items-center text-emerald-400 text-xs font-medium bg-emerald-500/10 px-2 py-1 rounded-full">
                            <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
                            +12%
                        </span>
                    </div>
                    <div>
                        <p class="text-text-secondary text-sm font-medium">Total Conversations</p>
                        <h3 class="text-white text-3xl font-bold mt-1">{{ number_format($totalConversations) }}</h3>
                    </div>
                </div>
                <!-- Resolution Rate -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-5 flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="p-2 bg-emerald-500/10 rounded-lg">
                            <span class="material-symbols-outlined text-emerald-400" style="font-size: 24px;">check_circle</span>
                        </div>
                        <span class="flex items-center text-emerald-400 text-xs font-medium bg-emerald-500/10 px-2 py-1 rounded-full">
                            <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
                            +2.1%
                        </span>
                    </div>
                    <div>
                        <p class="text-text-secondary text-sm font-medium">Bot Resolution Rate</p>
                        <h3 class="text-white text-3xl font-bold mt-1">{{ $resolutionRate }}%</h3>
                    </div>
                </div>
                <!-- Avg Response Time -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-5 flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="p-2 bg-purple-500/10 rounded-lg">
                            <span class="material-symbols-outlined text-purple-400" style="font-size: 24px;">timer</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-text-secondary text-sm font-medium">Avg Response Time</p>
                        <h3 class="text-white text-3xl font-bold mt-1">{{ $avgResponseTime ?? 0 }}s</h3>
                    </div>
                </div>
                <!-- Human Handoff -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-5 flex flex-col gap-4">
                    <div class="flex justify-between items-start">
                        <div class="p-2 bg-orange-500/10 rounded-lg">
                            <span class="material-symbols-outlined text-orange-400" style="font-size: 24px;">support_agent</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-text-secondary text-sm font-medium">Human Handoff</p>
                        <h3 class="text-white text-3xl font-bold mt-1">{{ $handoffRate }}%</h3>
                    </div>
                </div>
            </div>

            <!-- Main Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Traffic Volume Line Chart -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-6 lg:col-span-2 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-white text-lg font-bold">Daily Conversation Volume</h3>
                        <button class="text-primary hover:text-white text-sm font-medium transition-colors">View Details</button>
                    </div>
                    <!-- CSS Chart Placeholder -->
                    <div class="flex-1 min-h-[240px] relative w-full flex items-end justify-between gap-1 pt-8">
                        <!-- SVG Curve -->
                        <svg class="absolute inset-0 w-full h-full" preserveaspectratio="none" viewbox="0 0 100 40">
                            <defs>
                                <lineargradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#135bec" stop-opacity="0.3"></stop>
                                    <stop offset="100%" stop-color="#135bec" stop-opacity="0"></stop>
                                </lineargradient>
                            </defs>
                            <path d="M0,35 Q10,25 20,28 T40,20 T60,25 T80,10 T100,15 L100,40 L0,40 Z" fill="url(#chartGradient)"></path>
                            <path d="M0,35 Q10,25 20,28 T40,20 T60,25 T80,10 T100,15" fill="none" stroke="#135bec" stroke-width="0.5"></path>
                        </svg>
                        <!-- Grid Lines -->
                        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-text-secondary text-[10px] uppercase font-medium">
                            <div class="w-full border-b border-border-dark/30 h-0 flex items-center"><span class="bg-surface-dark pr-2">200</span></div>
                            <div class="w-full border-b border-border-dark/30 h-0 flex items-center"><span class="bg-surface-dark pr-2">150</span></div>
                            <div class="w-full border-b border-border-dark/30 h-0 flex items-center"><span class="bg-surface-dark pr-2">100</span></div>
                            <div class="w-full border-b border-border-dark/30 h-0 flex items-center"><span class="bg-surface-dark pr-2">50</span></div>
                            <div class="w-full border-b border-border-dark/30 h-0 flex items-center"><span class="bg-surface-dark pr-2">0</span></div>
                        </div>
                    </div>
                   <div class="flex justify-between text-text-secondary text-xs mt-4 uppercase font-medium">
                        <span>Day 1</span>
                        <span>Day 7</span>
                        <span>Day 14</span>
                        <span>Day 21</span>
                        <span>Day 30</span>
                    </div>
                </div>
                <!-- Source Split Donut -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-6 flex flex-col">
                    <h3 class="text-white text-lg font-bold mb-6">Traffic by Platform</h3>
                    <div class="flex-1 flex flex-col items-center justify-center gap-6">
                        <!-- Donut -->
                        <div class="relative size-48 rounded-full conic-chart">
                            <div class="absolute inset-4 bg-surface-dark rounded-full flex flex-col items-center justify-center">
                                <span class="text-3xl font-bold text-white">{{ number_format($totalConversations) }}</span>
                                <span class="text-xs text-text-secondary font-medium uppercase tracking-wide">Total Chats</span>
                            </div>
                        </div>
                        <!-- Legend -->
                        <div class="w-full flex flex-col gap-3">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-[#111722]">
                                <div class="flex items-center gap-3">
                                    <div class="size-3 rounded-full bg-primary"></div>
                                    <span class="text-sm text-white font-medium">WhatsApp</span>
                                </div>
                                <span class="text-sm font-bold text-white">{{ $waPercentage }}%</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-[#111722]">
                                <div class="flex items-center gap-3">
                                    <div class="size-3 rounded-full bg-sky-400"></div>
                                    <span class="text-sm text-white font-medium">Instagram</span>
                                </div>
                                <span class="text-sm font-bold text-white">{{ $igPercentage }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Analysis Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Inquiries -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-6">
                    <h3 class="text-white text-lg font-bold mb-6">Top Patient Inquiries</h3>
                    <div class="flex flex-col gap-5">
                        <!-- Item -->
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-white font-medium">Doctor Appointment</span>
                                <span class="text-text-secondary">482 chats</span>
                            </div>
                            <div class="w-full bg-[#111722] rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                        <!-- Item -->
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-white font-medium">Doctor Schedule &amp; Hours</span>
                                <span class="text-text-secondary">310 chats</span>
                            </div>
                            <div class="w-full bg-[#111722] rounded-full h-2">
                                <div class="bg-primary/80 h-2 rounded-full" style="width: 55%"></div>
                            </div>
                        </div>
                        <!-- Item -->
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-white font-medium">Service Costs / BPJS</span>
                                <span class="text-text-secondary">205 chats</span>
                            </div>
                            <div class="w-full bg-[#111722] rounded-full h-2">
                                <div class="bg-primary/60 h-2 rounded-full" style="width: 35%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Query Word Cloud / Tags -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-6 flex flex-col">
                    <h3 class="text-white text-lg font-bold mb-6">Trending Topics</h3>
                    <div class="flex flex-wrap gap-2 content-start">
                        <span class="px-4 py-2 bg-primary/20 text-primary border border-primary/30 rounded-full text-sm font-medium">#Appointment</span>
                        <span class="px-4 py-2 bg-surface-dark border border-border-dark text-white rounded-full text-sm font-medium hover:bg-primary/10 transition">#JadwalDokter</span>
                        <span class="px-5 py-3 bg-surface-dark border border-border-dark text-white rounded-full text-base font-bold hover:bg-primary/10 transition">#BPJS</span>
                        <span class="px-3 py-1.5 bg-surface-dark border border-border-dark text-text-secondary rounded-full text-xs hover:bg-primary/10 transition">#Emergency</span>
                        <span class="px-4 py-2 bg-surface-dark border border-border-dark text-white rounded-full text-sm font-medium hover:bg-primary/10 transition">#PoliAnak</span>
                        <span class="px-4 py-2 bg-surface-dark border border-border-dark text-white rounded-full text-sm font-medium hover:bg-primary/10 transition">#Radiology</span>
                        <span class="px-6 py-4 bg-primary/10 text-white border border-primary/20 rounded-full text-lg font-bold">#Biaya</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden flex flex-col">
                <div class="p-4 md:p-6 border-b border-border-dark flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <h3 class="text-white text-base md:text-lg font-bold">Recent Sessions</h3>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <input class="bg-[#111722] border border-border-dark rounded-lg text-xs md:text-sm text-white px-3 py-2 w-full sm:w-48 focus:ring-primary focus:border-primary" placeholder="Search ID..." type="text"/>
                    </div>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs md:text-sm text-text-secondary min-w-[500px]">
                        <thead class="bg-[#111722] text-[10px] md:text-xs uppercase font-semibold text-text-secondary">
                            <tr>
                                <th class="px-3 md:px-6 py-3 md:py-4">Timestamp</th>
                                <th class="px-3 md:px-6 py-3 md:py-4">Trigger</th>
                                <th class="px-3 md:px-6 py-3 md:py-4">Platform</th>
                                <th class="px-3 md:px-6 py-3 md:py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark">
                            @forelse($recentLogs as $log)
                                <tr class="hover:bg-[#1f2b40] transition-colors">
                                    <td class="px-3 md:px-6 py-3 md:py-4 font-medium text-white whitespace-nowrap">{{ $log['time']->format('m-d H:i') }}</td>
                                    <td class="px-3 md:px-6 py-3 md:py-4 text-white max-w-[200px] truncate">"{{ $log['message'] }}"</td>
                                    <td class="px-3 md:px-6 py-3 md:py-4">
                                        <div class="flex items-center gap-2">
                                             @if($log['platform'] === 'whatsapp')
                                                <span class="text-green-500 material-symbols-outlined" style="font-size: 18px;">chat</span> WhatsApp
                                             @else
                                                <span class="text-pink-500 material-symbols-outlined" style="font-size: 18px;">photo_camera</span> Instagram
                                             @endif
                                        </div>
                                    </td>
                                    <td class="px-3 md:px-6 py-3 md:py-4">
                                        @if($log['status'] === 'resolved' || $log['status'] === 'success' || $log['status'] === 'sent')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Resolved</span>
                                        @elseif($log['status'] === 'pending')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">Pending</span>
                                        @else
                                             <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-500/10 text-orange-500 border border-orange-500/20">{{ ucfirst($log['status']) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 md:px-6 py-4 text-center">No recent sessions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Apply filters - redirect with query params
function applyFilters() {
    const platform = document.getElementById('platformFilter').value;
    const bot = document.getElementById('botFilter').value;
    
    let url = '{{ route("analytics.index") }}?';
    const params = new URLSearchParams();
    
    if (platform !== 'all') params.append('platform', platform);
    if (bot !== 'all') params.append('bot', bot);
    
    window.location.href = url + params.toString();
}

// Export report - download CSV
function exportReport() {
    const platform = document.getElementById('platformFilter').value;
    const bot = document.getElementById('botFilter').value;
    
    let url = '{{ route("analytics.export") }}?';
    const params = new URLSearchParams();
    
    if (platform !== 'all') params.append('platform', platform);
    if (bot !== 'all') params.append('bot', bot);
    
    // Open download link
    window.location.href = url + params.toString();
}
</script>

</body>
</html>
