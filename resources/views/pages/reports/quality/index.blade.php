@extends('layouts.dark')

@section('title', 'Quality Reports')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'overview',
    dateRange: '30',
    showHeatmapDetails: false,
    selectedHour: null,
    selectedDay: null
}">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Quality Reports</h1>
            <p class="text-slate-400 text-sm">Analyze conversation quality, sentiment, and response time metrics</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('reports.quality') }}" class="flex items-center gap-2">
                <select name="range" onchange="this.form.submit()" class="bg-surface-dark border border-slate-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="7" {{ request('range', '30') == '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="14" {{ request('range', '30') == '14' ? 'selected' : '' }}>Last 14 Days</option>
                    <option value="30" {{ request('range', '30') == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ request('range', '30') == '90' ? 'selected' : '' }}>Last 90 Days</option>
                </select>
            </form>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-surface-dark border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition-all text-sm">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Print
            </button>
        </div>
    </div>

    @include('components.page-help', [
        'title' => 'Kualitas Chat',
        'description' => 'Analisis kualitas percakapan dan respons bot.',
        'tips' => ['Review percakapan dengan rating rendah', 'Analisis respons yang tidak relevan', 'Lihat metrik response time', 'Identifikasi area untuk training ulang AI']
    ])

    <!-- Quality Score Overview -->
    <div class="bg-gradient-to-r from-primary/10 via-purple-500/10 to-blue-500/10 rounded-2xl p-8 border border-primary/20">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="relative">
                    <svg class="w-32 h-32 transform -rotate-90">
                        <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" class="text-slate-800"/>
                        <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none" 
                                class="text-primary" 
                                stroke-dasharray="351.86"
                                stroke-dashoffset="{{ 351.86 - (351.86 * ($qualityScore ?? 87) / 100) }}"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-4xl font-black text-white">{{ $qualityScore ?? 0 }}</span>
                        <span class="text-xs text-slate-400 uppercase tracking-wider">Score</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-2">Conversation Quality Score</h3>
                    <p class="text-slate-400 text-sm max-w-md">Overall quality rating based on sentiment analysis, response times, resolution rates, and customer satisfaction.</p>
                    <div class="flex items-center gap-4 mt-4">
                        <span class="flex items-center gap-1 text-xs text-green-400">
                            <span class="material-symbols-outlined text-[16px]">trending_up</span>
                            +5.2% from last period
                        </span>
                        <span class="text-slate-600">|</span>
                        <span class="text-xs text-slate-400">Target: 90+</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-surface-dark/50 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-green-400 text-lg">sentiment_satisfied</span>
                        <span class="text-xs text-slate-400">Positive</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $sentimentPositive ?? 0 }}%</span>
                </div>
                <div class="bg-surface-dark/50 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-red-400 text-lg">sentiment_dissatisfied</span>
                        <span class="text-xs text-slate-400">Negative</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $sentimentNegative ?? 0 }}%</span>
                </div>
                <div class="bg-surface-dark/50 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-yellow-400 text-lg">sentiment_neutral</span>
                        <span class="text-xs text-slate-400">Neutral</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $sentimentNeutral ?? 0 }}%</span>
                </div>
                <div class="bg-surface-dark/50 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-blue-400 text-lg">psychology</span>
                        <span class="text-xs text-slate-400">Bot Handled</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $botHandled ?? 0 }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- First Response Time -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="size-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-blue-400">timer</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ ($frt ?? 45) <= 60 ? 'bg-green-500/20 text-green-400' : (($frt ?? 45) <= 120 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                    {{ ($frt ?? 45) <= 60 ? 'Excellent' : (($frt ?? 45) <= 120 ? 'Good' : 'Needs Work') }}
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">First Response Time</p>
            <h4 class="text-3xl font-black text-white">{{ $frt ?? 45 }}s</h4>
            <p class="text-xs text-slate-500 mt-2">Average time to first response</p>
        </div>

        <!-- Average Response Time -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="size-12 rounded-xl bg-purple-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-purple-400">speed</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ ($art ?? 120) <= 120 ? 'bg-green-500/20 text-green-400' : (($art ?? 120) <= 300 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                    {{ ($art ?? 120) <= 120 ? 'Fast' : (($art ?? 120) <= 300 ? 'Average' : 'Slow') }}
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Avg Response Time</p>
            <h4 class="text-3xl font-black text-white">{{ $art ?? 120 }}s</h4>
            <p class="text-xs text-slate-500 mt-2">Average time between messages</p>
        </div>

        <!-- Resolution Time -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="size-12 rounded-xl bg-green-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-green-400">check_circle</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ ($resolutionTime ?? 480) <= 600 ? 'bg-green-500/20 text-green-400' : (($resolutionTime ?? 480) <= 1800 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                    {{ ($resolutionTime ?? 480) <= 600 ? 'Quick' : (($resolutionTime ?? 480) <= 1800 ? 'Normal' : 'Slow') }}
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Resolution Time</p>
            <h4 class="text-3xl font-black text-white">{{ $resolutionTime ?? 480 }}s</h4>
            <p class="text-xs text-slate-500 mt-2">Average time to resolve issue</p>
        </div>

        <!-- Escalation Rate -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="size-12 rounded-xl bg-orange-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-orange-400">swap_horiz</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ ($escalationRate ?? 26) <= 20 ? 'bg-green-500/20 text-green-400' : (($escalationRate ?? 26) <= 35 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                    {{ ($escalationRate ?? 26) <= 20 ? 'Low' : (($escalationRate ?? 26) <= 35 ? 'Moderate' : 'High') }}
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Escalation Rate</p>
            <h4 class="text-3xl font-black text-white">{{ $escalationRate ?? 26 }}%</h4>
            <p class="text-xs text-slate-500 mt-2">Bot to human handoff rate</p>
        </div>
    </div>

    <!-- Charts Row 1: Sentiment Analysis & Escalation -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sentiment Analysis Chart -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-lg text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">donut_large</span>
                Sentiment Distribution
            </h3>
            <div class="flex items-center justify-center">
                <div class="relative w-48 h-48">
                    <svg viewBox="0 0 100 100" class="transform -rotate-90 w-full h-full">
                        <!-- Background circle -->
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#1e293b" stroke-width="20"/>
                        <!-- Positive segment -->
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#22c55e" stroke-width="20"
                                stroke-dasharray="{{ 2 * pi() * 40 * ($sentimentPositive ?? 68) / 100 }} {{ 2 * pi() * 40 }}"
                                stroke-dashoffset="0"/>
                        <!-- Neutral segment -->
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#eab308" stroke-width="20"
                                stroke-dasharray="{{ 2 * pi() * 40 * ($sentimentNeutral ?? 20) / 100 }} {{ 2 * pi() * 40 }}"
                                stroke-dashoffset="-{{ 2 * pi() * 40 * ($sentimentPositive ?? 68) / 100 }}"/>
                        <!-- Negative segment -->
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="20"
                                stroke-dasharray="{{ 2 * pi() * 40 * ($sentimentNegative ?? 12) / 100 }} {{ 2 * pi() * 40 }}"
                                stroke-dashoffset="-{{ 2 * pi() * 40 * (($sentimentPositive ?? 68) + ($sentimentNeutral ?? 20)) / 100 }}"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-bold text-white">{{ $totalConversations ?? 1247 }}</span>
                        <span class="text-xs text-slate-400">Total</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="text-center p-3 bg-green-500/10 rounded-xl border border-green-500/20">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-xs text-slate-400">Positive</span>
                    </div>
                    <span class="text-lg font-bold text-white">{{ $sentimentPositive ?? 0 }}%</span>
                </div>
                <div class="text-center p-3 bg-yellow-500/10 rounded-xl border border-yellow-500/20">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span class="text-xs text-slate-400">Neutral</span>
                    </div>
                    <span class="text-lg font-bold text-white">{{ $sentimentNeutral ?? 0 }}%</span>
                </div>
                <div class="text-center p-3 bg-red-500/10 rounded-xl border border-red-500/20">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="text-xs text-slate-400">Negative</span>
                    </div>
                    <span class="text-lg font-bold text-white">{{ $sentimentNegative ?? 0 }}%</span>
                </div>
            </div>
        </div>

        <!-- Escalation Trend Chart -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-lg text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">trending_up</span>
                Escalation Trend (Bot → Human)
            </h3>
            <div class="space-y-4">
                @php
                    $escalationData = $escalationTrend ?? [
                        ['date' => 'Mon', 'bot' => 85, 'human' => 15],
                        ['date' => 'Tue', 'bot' => 78, 'human' => 22],
                        ['date' => 'Wed', 'bot' => 82, 'human' => 18],
                        ['date' => 'Thu', 'bot' => 75, 'human' => 25],
                        ['date' => 'Fri', 'bot' => 80, 'human' => 20],
                        ['date' => 'Sat', 'bot' => 88, 'human' => 12],
                        ['date' => 'Sun', 'bot' => 90, 'human' => 10],
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach($escalationData as $data)
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-slate-400 w-10">{{ $data['date'] }}</span>
                        <div class="flex-1 flex items-center gap-1">
                            <div class="h-6 bg-primary rounded-l flex items-center justify-end px-2 text-xs font-bold text-white" 
                                 style="width: {{ $data['bot'] }}%">
                                {{ $data['bot'] }}%
                            </div>
                            <div class="h-6 bg-orange-500 rounded-r flex items-center px-2 text-xs font-bold text-white" 
                                 style="width: {{ $data['human'] }}%">
                                {{ $data['human'] }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-primary"></span>
                    <span class="text-sm text-slate-400">Bot Resolved</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-orange-500"></span>
                    <span class="text-sm text-slate-400">Human Handoff</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Response Time Heatmap -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-semibold text-lg text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">grid_on</span>
                Response Time Heatmap
            </h3>
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-400">Fast</span>
                <div class="flex gap-1">
                    <span class="w-4 h-4 rounded bg-green-500/20"></span>
                    <span class="w-4 h-4 rounded bg-green-500/40"></span>
                    <span class="w-4 h-4 rounded bg-yellow-500/40"></span>
                    <span class="w-4 h-4 rounded bg-orange-500/40"></span>
                    <span class="w-4 h-4 rounded bg-red-500/40"></span>
                </div>
                <span class="text-slate-400">Slow</span>
            </div>
        </div>
        
        @php
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $hours = ['00', '04', '08', '12', '16', '20'];
            // Simulated heatmap data (response times in seconds)
            $heatmapData = [
                [45, 30, 120, 180, 90, 60, 35, 25, 85, 150, 200, 175],
                [40, 35, 110, 190, 95, 55, 40, 30, 80, 140, 210, 160],
                [50, 40, 130, 200, 85, 65, 45, 35, 95, 160, 220, 180],
                [55, 45, 140, 220, 100, 70, 50, 40, 110, 180, 240, 190],
                [60, 50, 150, 250, 120, 80, 55, 45, 130, 200, 280, 220],
                [35, 25, 80, 120, 60, 40, 30, 20, 60, 100, 140, 110],
                [30, 20, 60, 100, 50, 35, 25, 15, 50, 80, 120, 90],
            ];
        @endphp

        <div class="overflow-x-auto">
            <div class="min-w-[600px]">
                <!-- Hour labels -->
                <div class="flex items-center mb-2">
                    <div class="w-16"></div>
                    @foreach($hours as $hour)
                    <div class="flex-1 text-center text-xs text-slate-500">{{ $hour }}:00</div>
                    @endforeach
                </div>
                <!-- Heatmap grid -->
                <div class="space-y-1">
                    @foreach($days as $dayIndex => $day)
                    <div class="flex items-center gap-1">
                        <div class="w-16 text-xs text-slate-400 text-right pr-2">{{ $day }}</div>
                        <div class="flex-1 grid grid-cols-6 gap-1">
                            @foreach($hours as $hourIndex => $hour)
                                @php
                                    $value = $heatmapData[$dayIndex][$hourIndex * 2] ?? rand(30, 250);
                                    $colorClass = $value <= 60 ? 'bg-green-500/40' : 
                                                 ($value <= 120 ? 'bg-green-500/20' : 
                                                 ($value <= 180 ? 'bg-yellow-500/40' : 
                                                 ($value <= 240 ? 'bg-orange-500/40' : 'bg-red-500/40')));
                                @endphp
                            <div class="h-8 rounded cursor-pointer hover:ring-2 hover:ring-white/50 transition-all {{ $colorClass }} relative group"
                                 @click="selectedHour = '{{ $hour }}'; selectedDay = '{{ $day }}'; showHeatmapDetails = true">
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                    {{ $day }} {{ $hour }}:00 - {{ $value }}s avg
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-4">Click on any cell to see detailed metrics for that time period.</p>
    </div>

    <!-- Quality Trends Over Time -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-semibold text-lg text-white mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">show_chart</span>
            Quality Trends Over Time
        </h3>
        @php
            $trendData = $qualityTrends ?? [
                ['period' => 'Week 1', 'quality' => 82, 'response' => 78, 'resolution' => 85],
                ['period' => 'Week 2', 'quality' => 84, 'response' => 80, 'resolution' => 86],
                ['period' => 'Week 3', 'quality' => 83, 'response' => 82, 'resolution' => 84],
                ['period' => 'Week 4', 'quality' => 86, 'response' => 85, 'resolution' => 88],
                ['period' => 'Week 5', 'quality' => 85, 'response' => 83, 'resolution' => 87],
                ['period' => 'Week 6', 'quality' => 88, 'response' => 87, 'resolution' => 89],
                ['period' => 'Week 7', 'quality' => 87, 'response' => 86, 'resolution' => 88],
                ['period' => 'Week 8', 'quality' => 90, 'response' => 89, 'resolution' => 91],
            ];
        @endphp
        <div class="h-64 relative">
            <!-- Y-axis labels -->
            <div class="absolute left-0 top-0 bottom-8 w-10 flex flex-col justify-between text-xs text-slate-500">
                <span>100</span>
                <span>75</span>
                <span>50</span>
                <span>25</span>
                <span>0</span>
            </div>
            <!-- Chart area -->
            <div class="ml-12 h-full flex items-end gap-2 pb-8">
                @foreach($trendData as $data)
                <div class="flex-1 flex flex-col items-center gap-1 group">
                    <!-- Quality line point -->
                    <div class="w-full h-40 relative">
                        <div class="absolute w-3 h-3 bg-primary rounded-full left-1/2 -translate-x-1/2 cursor-pointer hover:scale-150 transition-transform"
                             style="bottom: {{ $data['quality'] * 0.4 }}px;">
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                Quality: {{ $data['quality'] }}%
                            </div>
                        </div>
                        <!-- Response line point -->
                        <div class="absolute w-3 h-3 bg-blue-400 rounded-full left-1/2 -translate-x-1/2 -ml-4 cursor-pointer hover:scale-150 transition-transform"
                             style="bottom: {{ $data['response'] * 0.4 }}px;">
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                Response: {{ $data['response'] }}%
                            </div>
                        </div>
                        <!-- Resolution line point -->
                        <div class="absolute w-3 h-3 bg-green-400 rounded-full left-1/2 -translate-x-1/2 ml-4 cursor-pointer hover:scale-150 transition-transform"
                             style="bottom: {{ $data['resolution'] * 0.4 }}px;">
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                Resolution: {{ $data['resolution'] }}%
                            </div>
                        </div>
                    </div>
                    <span class="text-xs text-slate-500">{{ $data['period'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex items-center justify-center gap-6 mt-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-primary"></span>
                <span class="text-sm text-slate-400">Quality Score</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-400"></span>
                <span class="text-sm text-slate-400">Response Time</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-400"></span>
                <span class="text-sm text-slate-400">Resolution Rate</span>
            </div>
        </div>
    </div>

    <!-- Quality Breakdown by Category -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">assessment</span>
                Quality Metrics Breakdown
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-800/50 text-[10px] uppercase font-black text-slate-500 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Metric Category</th>
                        <th class="px-6 py-4 text-right">Current</th>
                        <th class="px-6 py-4 text-right">Previous</th>
                        <th class="px-6 py-4 text-right">Change</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @php
                        $qualityMetrics = $metricsBreakdown ?? [
                            ['category' => 'Response Accuracy', 'current' => 94, 'previous' => 91, 'target' => 90],
                            ['category' => 'Sentiment Analysis', 'current' => 89, 'previous' => 87, 'target' => 85],
                            ['category' => 'Intent Recognition', 'current' => 92, 'previous' => 88, 'target' => 85],
                            ['category' => 'First Contact Resolution', 'current' => 76, 'previous' => 72, 'target' => 75],
                            ['category' => 'Conversation Completion', 'current' => 88, 'previous' => 85, 'target' => 85],
                            ['category' => 'Grammar & Politeness', 'current' => 96, 'previous' => 95, 'target' => 90],
                        ];
                    @endphp
                    @foreach($qualityMetrics as $metric)
                        @php
                            $change = $metric['current'] - $metric['previous'];
                            $changeClass = $change > 0 ? 'text-green-400' : ($change < 0 ? 'text-red-400' : 'text-slate-400');
                            $changeIcon = $change > 0 ? 'trending_up' : ($change < 0 ? 'trending_down' : 'remove');
                            $status = $metric['current'] >= $metric['target'] ? 'On Track' : 'Needs Attention';
                            $statusClass = $metric['current'] >= $metric['target'] ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-400';
                        @endphp
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm text-white font-medium">{{ $metric['category'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-bold text-white">{{ $metric['current'] }}%</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm text-slate-400">{{ $metric['previous'] }}%</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-bold {{ $changeClass }} flex items-center justify-end gap-1">
                                <span class="material-symbols-outlined text-[16px]">{{ $changeIcon }}</span>
                                {{ abs($change) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 {{ $statusClass }} text-[9px] font-black uppercase rounded-full">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh data every 5 minutes
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            window.location.reload();
        }
    }, 300000);
</script>
@endpush
