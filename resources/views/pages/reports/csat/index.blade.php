@extends('layouts.dark')

@section('title', 'CSAT Reports')

@section('content')
<div class="space-y-6" x-data="{
    selectedPlatform: 'all',
    dateRange: '30',
    showNpsDetails: false,
    activeTab: 'overview'
}">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">CSAT Reports</h1>
            <p class="text-slate-400 text-sm">Customer satisfaction metrics, NPS scores, and feedback analysis</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Platform Filter -->
            <select x-model="selectedPlatform" class="bg-surface-dark border border-slate-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary">
                <option value="all">All Platforms</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="instagram">Instagram</option>
            </select>
            <form method="GET" action="{{ route('reports.csat') }}" class="flex items-center gap-2">
                <select name="range" onchange="this.form.submit()" class="bg-surface-dark border border-slate-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary">
                    <option value="7" {{ request('range', '30') == '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="14" {{ request('range', '30') == '14' ? 'selected' : '' }}>Last 14 Days</option>
                    <option value="30" {{ request('range', '30') == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ request('range', '30') == '90' ? 'selected' : '' }}>Last 90 Days</option>
                </select>
            </form>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-surface-dark border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition-all text-sm">
                <span class="material-symbols-outlined text-[18px]">print</span>
            </button>
        </div>
    </div>

    @include('components.page-help', [
        'title' => 'Customer Satisfaction',
        'description' => 'Ukur kepuasan pelanggan berdasarkan rating dan feedback yang diterima.',
        'tips' => ['Monitor CSAT score secara berkala', 'Analisis feedback negatif untuk perbaikan', 'Lihat tren kepuasan pelanggan', 'Respons cepat terhadap rating rendah']
    ])

    <!-- CSAT Score Hero Section -->
    <div class="bg-gradient-to-r from-yellow-500/10 via-orange-500/10 to-red-500/10 rounded-2xl p-8 border border-yellow-500/20">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <!-- Main CSAT Score -->
            <div class="flex items-center gap-8">
                <div class="relative">
                    <svg class="w-40 h-40 transform -rotate-90">
                        <circle cx="80" cy="80" r="70" stroke="currentColor" stroke-width="12" fill="none" class="text-slate-800"/>
                        @php
                            $csatScore = $csat ?? 4.6;
                            $csatPercent = ($csatScore / 5) * 100;
                            $circumference = 2 * pi() * 70;
                            $strokeDashoffset = $circumference - ($circumference * $csatPercent / 100);
                            $scoreColor = $csatScore >= 4.5 ? 'text-green-400' : ($csatScore >= 4.0 ? 'text-yellow-400' : ($csatScore >= 3.0 ? 'text-orange-400' : 'text-red-400'));
                        @endphp
                        <circle cx="80" cy="80" r="70" stroke="currentColor" stroke-width="12" fill="none" 
                                class="{{ $scoreColor }} transition-all duration-1000"
                                stroke-dasharray="{{ $circumference }}"
                                stroke-dashoffset="{{ $strokeDashoffset }}"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-5xl font-black text-white">{{ $csatScore }}</span>
                        <div class="flex text-yellow-400 mt-1">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($csatScore))
                                    <span class="material-symbols-outlined text-[20px]">star</span>
                                @elseif($i - 0.5 <= $csatScore)
                                    <span class="material-symbols-outlined text-[20px]">star_half</span>
                                @else
                                    <span class="material-symbols-outlined text-[20px] text-slate-600">star</span>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white mb-2">Customer Satisfaction Score</h3>
                    <p class="text-slate-400 max-w-md">Based on {{ $totalResponses ?? 847 }} customer ratings across all platforms</p>
                    @php
                        $csatChange = $csatChange ?? 0.3;
                        $csatChangeColor = $csatChange > 0 ? 'text-green-400' : 'text-red-400';
                        $csatChangeIcon = $csatChange > 0 ? 'trending_up' : 'trending_down';
                    @endphp
                    <div class="flex items-center gap-4 mt-4">
                        <span class="flex items-center gap-1 {{ $csatChangeColor }}">
                            <span class="material-symbols-outlined text-[18px]">{{ $csatChangeIcon }}</span>
                            {{ $csatChange > 0 ? '+' : '' }}{{ $csatChange }} from last period
                        </span>
                        <span class="text-slate-600">|</span>
                        <span class="text-slate-400">Industry avg: 4.2</span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-surface-dark/50 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-green-400">thumb_up</span>
                        <span class="text-xs text-slate-400">Satisfied</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $satisfiedPercent ?? 78 }}%</span>
                    <span class="text-xs text-green-400 block">4-5 stars</span>
                </div>
                <div class="bg-surface-dark/50 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-yellow-400">thumbs_up_down</span>
                        <span class="text-xs text-slate-400">Neutral</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $neutralPercent ?? 15 }}%</span>
                    <span class="text-xs text-yellow-400 block">3 stars</span>
                </div>
                <div class="bg-surface-dark/50 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-red-400">thumb_down</span>
                        <span class="text-xs text-slate-400">Dissatisfied</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $dissatisfiedPercent ?? 7 }}%</span>
                    <span class="text-xs text-red-400 block">1-2 stars</span>
                </div>
                <div class="bg-surface-dark/50 rounded-xl p-4 border border-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-blue-400">rate_review</span>
                        <span class="text-xs text-slate-400">Response Rate</span>
                    </div>
                    <span class="text-2xl font-bold text-white">{{ $responseRate ?? 42 }}%</span>
                    <span class="text-xs text-slate-400 block">of customers</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Comparison & NPS Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Platform Breakdown -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">devices</span>
                By Platform
            </h3>
            <div class="space-y-4">
                <!-- WhatsApp -->
                <div class="p-4 bg-green-500/5 rounded-xl border border-green-500/10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">💬</span>
                            <span class="text-white font-medium">WhatsApp</span>
                        </div>
                        <span class="text-2xl font-bold text-green-400">{{ $whatsappCsat ?? 4.7 }}</span>
                    </div>
                    <div class="flex items-center gap-1 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="material-symbols-outlined text-[16px] {{ $i <= round($whatsappCsat ?? 4.7) ? 'text-green-400' : 'text-slate-700' }}">star</span>
                        @endfor
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">{{ $whatsappResponses ?? 523 }} responses</span>
                        <span class="text-green-400">+0.2</span>
                    </div>
                </div>
                <!-- Instagram -->
                <div class="p-4 bg-pink-500/5 rounded-xl border border-pink-500/10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📸</span>
                            <span class="text-white font-medium">Instagram</span>
                        </div>
                        <span class="text-2xl font-bold text-pink-400">{{ $instagramCsat ?? 4.4 }}</span>
                    </div>
                    <div class="flex items-center gap-1 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="material-symbols-outlined text-[16px] {{ $i <= round($instagramCsat ?? 4.4) ? 'text-pink-400' : 'text-slate-700' }}">star</span>
                        @endfor
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">{{ $instagramResponses ?? 324 }} responses</span>
                        <span class="text-pink-400">+0.1</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- NPS Score -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-semibold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">speed</span>
                    Net Promoter Score (NPS)
                </h3>
                <button @click="showNpsDetails = !showNpsDetails" class="text-sm text-primary hover:underline">
                    <span x-text="showNpsDetails ? 'Hide Details' : 'View Details'"></span>
                </button>
            </div>
            
            @php
                $npsScore = $nps ?? 42;
                $promoters = $npsBreakdown['promoters'] ?? 48;
                $passives = $npsBreakdown['passives'] ?? 35;
                $detractors = $npsBreakdown['detractors'] ?? 17;
                $npsColor = $npsScore >= 50 ? 'text-green-400' : ($npsScore >= 30 ? 'text-yellow-400' : ($npsScore >= 0 ? 'text-orange-400' : 'text-red-400'));
            @endphp

            <div class="flex flex-col md:flex-row gap-8">
                <!-- NPS Gauge -->
                <div class="flex-1">
                    <div class="relative h-24 bg-slate-800 rounded-full overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-red-500 via-yellow-500 to-green-500 opacity-30"></div>
                        <div class="absolute top-0 bottom-0 w-0.5 bg-white/50" style="left: 50%"></div>
                        @php
                            $npsPosition = (($npsScore + 100) / 200) * 100;
                        @endphp
                        <div class="absolute top-0 bottom-0 w-1 bg-white shadow-lg transition-all duration-1000" style="left: {{ $npsPosition }}%">
                            <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-white rounded-full shadow-lg"></div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-slate-500">
                        <span>-100</span>
                        <span>0</span>
                        <span>+100</span>
                    </div>
                    <div class="text-center mt-4">
                        <span class="text-5xl font-black {{ $npsColor }}">{{ $npsScore > 0 ? '+' : '' }}{{ $npsScore }}</span>
                        <span class="text-sm text-slate-400 block mt-1">
                            {{ $npsScore >= 50 ? 'Excellent' : ($npsScore >= 30 ? 'Good' : ($npsScore >= 0 ? 'Average' : 'Needs Work')) }}
                        </span>
                    </div>
                </div>

                <!-- NPS Breakdown -->
                <div class="flex-1 space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-green-400 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">sentiment_very_satisfied</span>
                                Promoters (9-10)
                            </span>
                            <span class="text-sm font-bold text-white">{{ $promoters }}%</span>
                        </div>
                        <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width: {{ $promoters }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-yellow-400 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">sentiment_satisfied</span>
                                Passives (7-8)
                            </span>
                            <span class="text-sm font-bold text-white">{{ $passives }}%</span>
                        </div>
                        <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-500 rounded-full" style="width: {{ $passives }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-red-400 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">sentiment_dissatisfied</span>
                                Detractors (0-6)
                            </span>
                            <span class="text-sm font-bold text-white">{{ $detractors }}%</span>
                        </div>
                        <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-red-500 rounded-full" style="width: {{ $detractors }}%"></div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 pt-2 border-t border-slate-800">
                        Based on {{ $npsResponses ?? 412 }} NPS survey responses
                    </p>
                </div>
            </div>

            <!-- NPS Formula (collapsible) -->
            <div x-show="showNpsDetails" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-6 p-4 bg-slate-800/50 rounded-xl border border-slate-700">
                <p class="text-sm text-slate-300">
                    <strong class="text-white">NPS Formula:</strong> % Promoters ({{ $promoters }}%) - % Detractors ({{ $detractors }}%) = <span class="{{ $npsColor }} font-bold">{{ $npsScore }}</span>
                </p>
                <div class="grid grid-cols-3 gap-4 mt-4 text-center">
                    <div class="p-3 bg-red-500/10 rounded-lg">
                        <span class="text-xs text-red-400 block">Below 0</span>
                        <span class="text-xs text-slate-400">Needs Improvement</span>
                    </div>
                    <div class="p-3 bg-yellow-500/10 rounded-lg">
                        <span class="text-xs text-yellow-400 block">0 to 30</span>
                        <span class="text-xs text-slate-400">Good</span>
                    </div>
                    <div class="p-3 bg-green-500/10 rounded-lg">
                        <span class="text-xs text-green-400 block">Above 50</span>
                        <span class="text-xs text-slate-400">Excellent</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">bar_chart</span>
                Rating Distribution
            </h3>
            <div class="space-y-4">
                @php
                    $distribution = $ratingDistribution ?? [
                        5 => 45,
                        4 => 33,
                        3 => 12,
                        2 => 6,
                        1 => 4,
                    ];
                @endphp
                @foreach($distribution as $star => $percent)
                    @php
                        $barColor = $star >= 4 ? 'bg-green-500' : ($star == 3 ? 'bg-yellow-500' : 'bg-red-500');
                        $starColor = $star >= 4 ? 'text-green-400' : ($star == 3 ? 'text-yellow-400' : 'text-red-400');
                    @endphp
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1 w-24">
                        <span class="material-symbols-outlined text-[18px] {{ $starColor }}">star</span>
                        <span class="text-sm text-white font-medium">{{ $star }}</span>
                    </div>
                    <div class="flex-1 h-4 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full {{ $barColor }} rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                    </div>
                    <span class="text-sm text-slate-400 w-12 text-right">{{ $percent }}%</span>
                </div>
                @endforeach
            </div>
            <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between">
                <span class="text-sm text-slate-400">Total Ratings</span>
                <span class="text-lg font-bold text-white">{{ array_sum($distribution) }}</span>
            </div>
        </div>

        <!-- CSAT Trends Over Time -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">show_chart</span>
                CSAT Trends
            </h3>
            @php
                $trendData = $csatTrends ?? [
                    ['period' => 'Week 1', 'whatsapp' => 4.5, 'instagram' => 4.2, 'overall' => 4.4],
                    ['period' => 'Week 2', 'whatsapp' => 4.6, 'instagram' => 4.3, 'overall' => 4.5],
                    ['period' => 'Week 3', 'whatsapp' => 4.7, 'instagram' => 4.4, 'overall' => 4.6],
                    ['period' => 'Week 4', 'whatsapp' => 4.7, 'instagram' => 4.4, 'overall' => 4.6],
                ];
            @endphp
            <div class="h-48 relative">
                <div class="absolute left-0 top-0 bottom-8 w-8 flex flex-col justify-between text-xs text-slate-500">
                    <span>5.0</span>
                    <span>4.0</span>
                    <span>3.0</span>
                    <span>2.0</span>
                    <span>1.0</span>
                </div>
                <div class="ml-10 h-full flex items-end gap-4 pb-8">
                    @foreach($trendData as $data)
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full flex justify-center gap-1 h-32 items-end">
                            <div class="w-3 bg-green-500 rounded-t relative group cursor-pointer" style="height: {{ $data['whatsapp'] * 25 }}px;">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    WA: {{ $data['whatsapp'] }}
                                </div>
                            </div>
                            <div class="w-3 bg-pink-500 rounded-t relative group cursor-pointer" style="height: {{ $data['instagram'] * 25 }}px;">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    IG: {{ $data['instagram'] }}
                                </div>
                            </div>
                            <div class="w-3 bg-primary rounded-t relative group cursor-pointer" style="height: {{ $data['overall'] * 25 }}px;">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    Avg: {{ $data['overall'] }}
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-slate-500">{{ $data['period'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center justify-center gap-6 mt-2">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-green-500"></span>
                    <span class="text-xs text-slate-400">WhatsApp</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-pink-500"></span>
                    <span class="text-xs text-slate-400">Instagram</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-primary"></span>
                    <span class="text-xs text-slate-400">Overall</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Comments List -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-lg text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">reviews</span>
                Recent Feedback Comments
            </h3>
            <div class="flex items-center gap-2">
                <select class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg px-3 py-1.5">
                    <option value="all">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
                <select class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg px-3 py-1.5">
                    <option value="all">All Platforms</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="instagram">Instagram</option>
                </select>
            </div>
        </div>
        <div class="divide-y divide-slate-800 max-h-96 overflow-y-auto">
            @php
                $feedbackComments = $recentFeedback ?? [
                    ['name' => 'Sarah M.', 'platform' => 'whatsapp', 'rating' => 5, 'comment' => 'Very helpful and quick response! The bot understood my question perfectly.', 'time' => '2 hours ago', 'tags' => ['helpful', 'quick']],
                    ['name' => 'John D.', 'platform' => 'instagram', 'rating' => 4, 'comment' => 'Good service overall, but took a bit long to connect to human support.', 'time' => '5 hours ago', 'tags' => ['slow_handoff']],
                    ['name' => 'Lisa K.', 'platform' => 'whatsapp', 'rating' => 5, 'comment' => 'Amazing! Solved my problem in seconds. Great AI!', 'time' => '1 day ago', 'tags' => ['efficient']],
                    ['name' => 'Mike R.', 'platform' => 'instagram', 'rating' => 3, 'comment' => 'It was okay, but the bot did not understand my specific question at first.', 'time' => '1 day ago', 'tags' => ['misunderstanding']],
                    ['name' => 'Emma W.', 'platform' => 'whatsapp', 'rating' => 5, 'comment' => 'Best customer service experience I have had. Fast and accurate!', 'time' => '2 days ago', 'tags' => ['excellent']],
                    ['name' => 'David L.', 'platform' => 'whatsapp', 'rating' => 2, 'comment' => 'Had to repeat my question multiple times. Frustrating experience.', 'time' => '2 days ago', 'tags' => ['repetition']],
                ];
            @endphp
            @forelse($feedbackComments as $feedback)
                @php
                    $platformIcon = $feedback['platform'] === 'whatsapp' ? '💬' : '📸';
                    $platformColor = $feedback['platform'] === 'whatsapp' ? 'text-green-400' : 'text-pink-400';
                    $ratingColor = $feedback['rating'] >= 4 ? 'text-green-400' : ($feedback['rating'] == 3 ? 'text-yellow-400' : 'text-red-400');
                @endphp
            <div class="p-6 hover:bg-white/[0.02] transition-colors">
                <div class="flex items-start gap-4">
                    <div class="size-10 rounded-full bg-slate-800 flex items-center justify-center flex-shrink-0">
                        <span class="text-lg">{{ strtoupper(substr($feedback['name'], 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="text-white font-medium">{{ $feedback['name'] }}</span>
                                <span class="{{ $platformColor }} text-lg">{{ $platformIcon }}</span>
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="material-symbols-outlined text-[16px] {{ $i <= $feedback['rating'] ? $ratingColor : 'text-slate-700' }}">star</span>
                                    @endfor
                                </div>
                            </div>
                            <span class="text-xs text-slate-500">{{ $feedback['time'] }}</span>
                        </div>
                        <p class="text-sm text-slate-300 mb-3">{{ $feedback['comment'] }}</p>
                        <div class="flex items-center gap-2">
                            @foreach($feedback['tags'] as $tag)
                                @php
                                    $tagColors = [
                                        'helpful' => 'bg-green-500/10 text-green-400',
                                        'quick' => 'bg-blue-500/10 text-blue-400',
                                        'efficient' => 'bg-green-500/10 text-green-400',
                                        'excellent' => 'bg-green-500/10 text-green-400',
                                        'slow_handoff' => 'bg-yellow-500/10 text-yellow-400',
                                        'misunderstanding' => 'bg-orange-500/10 text-orange-400',
                                        'repetition' => 'bg-red-500/10 text-red-400',
                                    ];
                                    $tagClass = $tagColors[$tag] ?? 'bg-slate-700 text-slate-400';
                                @endphp
                            <span class="px-2 py-1 rounded-full text-[10px] font-medium {{ $tagClass }}">
                                {{ str_replace('_', ' ', $tag) }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-500">
                <span class="material-symbols-outlined text-4xl mb-2">inbox</span>
                <p>No feedback comments yet</p>
            </div>
            @endforelse
        </div>
        <div class="px-6 py-4 border-t border-slate-800">
            <button class="w-full py-2 text-sm text-primary hover:underline">Load More Comments</button>
        </div>
    </div>

    <!-- Key Insights -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-green-500/10 rounded-2xl p-6 border border-green-500/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="size-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">sentiment_very_satisfied</span>
                </div>
                <h4 class="font-semibold text-white">Top Praise</h4>
            </div>
            <ul class="space-y-2 text-sm">
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
                    <span>Quick response times mentioned by 68%</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
                    <span>AI understands questions well (82%)</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
                    <span>Friendly and professional tone</span>
                </li>
            </ul>
        </div>

        <div class="bg-yellow-500/10 rounded-2xl p-6 border border-yellow-500/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="size-10 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-400">construction</span>
                </div>
                <h4 class="font-semibold text-white">Areas to Improve</h4>
            </div>
            <ul class="space-y-2 text-sm">
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-yellow-400 text-[18px]">info</span>
                    <span>Instagram response 23% slower than WhatsApp</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-yellow-400 text-[18px]">info</span>
                    <span>Technical queries need better handling</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-yellow-400 text-[18px]">info</span>
                    <span>Weekend CSAT drops by 0.3 points</span>
                </li>
            </ul>
        </div>

        <div class="bg-blue-500/10 rounded-2xl p-6 border border-blue-500/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="size-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">lightbulb</span>
                </div>
                <h4 class="font-semibold text-white">Recommendations</h4>
            </div>
            <ul class="space-y-2 text-sm">
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-blue-400 text-[18px]">arrow_forward</span>
                    <span>Add more training for technical topics</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-blue-400 text-[18px]">arrow_forward</span>
                    <span>Optimize Instagram response flow</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-blue-400 text-[18px]">arrow_forward</span>
                    <span>Implement weekend priority queue</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            window.location.reload();
        }
    }, 300000);
</script>
@endpush
