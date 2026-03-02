@extends('layouts.dark')

@section('title', 'Comparative Reports')

@section('content')
<div class="space-y-6" x-data="{
    comparisonType: 'wow',
    customDateRange: false,
    period1Start: '',
    period1End: '',
    period2Start: '',
    period2End: '',
    activeMetric: 'all'
}">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Comparative Reports</h1>
            <p class="text-slate-400 text-sm">Compare metrics across different time periods and analyze trends</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="customDateRange = !customDateRange" 
                    class="flex items-center gap-2 px-4 py-2 bg-surface-dark border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition-all text-sm">
                <span class="material-symbols-outlined text-[18px]">date_range</span>
                <span x-text="customDateRange ? 'Hide Dates' : 'Custom Range'"></span>
            </button>
            <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-surface-dark border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition-all text-sm">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Print
            </button>
        </div>
    </div>

    @include('components.page-help', [
        'title' => 'Perbandingan Analitik',
        'description' => 'Bandingkan performa chat bot antar periode untuk melihat tren dan perubahan.',
        'tips' => ['Pilih periode yang ingin dibandingkan', 'Lihat persentase perubahan setiap metrik', 'Identifikasi tren naik atau turun', 'Export laporan perbandingan']
    ])

    <!-- Period Comparison Selector -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">compare_arrows</span>
            Select Comparison Period
        </h3>
        
        <!-- Comparison Type Buttons -->
        <div class="flex flex-wrap gap-3 mb-6">
            <button @click="comparisonType = 'wow'" 
                    :class="comparisonType === 'wow' ? 'bg-primary text-white border-primary' : 'bg-slate-800 text-slate-400 border-slate-700'"
                    class="px-4 py-2 rounded-lg border text-sm font-medium transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">calendar_view_week</span>
                Week over Week
            </button>
            <button @click="comparisonType = 'mom'" 
                    :class="comparisonType === 'mom' ? 'bg-primary text-white border-primary' : 'bg-slate-800 text-slate-400 border-slate-700'"
                    class="px-4 py-2 rounded-lg border text-sm font-medium transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">calendar_view_month</span>
                Month over Month
            </button>
            <button @click="comparisonType = 'qoq'" 
                    :class="comparisonType === 'qoq' ? 'bg-primary text-white border-primary' : 'bg-slate-800 text-slate-400 border-slate-700'"
                    class="px-4 py-2 rounded-lg border text-sm font-medium transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">calendar_view_day</span>
                Quarter over Quarter
            </button>
            <button @click="comparisonType = 'yoy'" 
                    :class="comparisonType === 'yoy' ? 'bg-primary text-white border-primary' : 'bg-slate-800 text-slate-400 border-slate-700'"
                    class="px-4 py-2 rounded-lg border text-sm font-medium transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">event_available</span>
                Year over Year
            </button>
            <button @click="comparisonType = 'custom'" 
                    :class="comparisonType === 'custom' ? 'bg-primary text-white border-primary' : 'bg-slate-800 text-slate-400 border-slate-700'"
                    class="px-4 py-2 rounded-lg border text-sm font-medium transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">edit_calendar</span>
                Custom
            </button>
        </div>

        <!-- Custom Date Range (shown when custom is selected) -->
        <div x-show="comparisonType === 'custom' || customDateRange" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-800/50 rounded-xl border border-slate-700">
            <!-- Period 1 -->
            <div>
                <h4 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    Period 1 (Current)
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">Start Date</label>
                        <input type="date" x-model="period1Start" 
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">End Date</label>
                        <input type="date" x-model="period1End" 
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
            </div>
            <!-- Period 2 -->
            <div>
                <h4 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                    Period 2 (Previous)
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">Start Date</label>
                        <input type="date" x-model="period2Start" 
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-2 focus:ring-slate-500 focus:border-slate-500">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">End Date</label>
                        <input type="date" x-model="period2End" 
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-2 focus:ring-slate-500 focus:border-slate-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Period Display -->
        <div class="mt-6 flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-3 px-4 py-2 bg-primary/10 rounded-lg border border-primary/20">
                <span class="material-symbols-outlined text-primary">event</span>
                <div>
                    <span class="text-xs text-slate-400 block">Current Period</span>
                    <span class="text-sm font-bold text-white">Jan 8 - Jan 14, 2026</span>
                </div>
            </div>
            <div class="flex items-center gap-3 px-4 py-2 bg-slate-800/50 rounded-lg border border-slate-700">
                <span class="material-symbols-outlined text-slate-400">history</span>
                <div>
                    <span class="text-xs text-slate-400 block">Previous Period</span>
                    <span class="text-sm font-bold text-white">Jan 1 - Jan 7, 2026</span>
                </div>
            </div>
            <button class="ml-auto px-4 py-2 bg-primary hover:bg-primary/80 text-white rounded-lg text-sm font-medium transition-all">
                Apply Comparison
            </button>
        </div>
    </div>

    <!-- Side-by-Side Metrics Comparison -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @php
            $comparisonMetrics = $metrics ?? [
                ['name' => 'Total Conversations', 'icon' => 'chat', 'current' => 1247, 'previous' => 1089, 'unit' => ''],
                ['name' => 'Avg Response Time', 'icon' => 'timer', 'current' => 45, 'previous' => 52, 'unit' => 's', 'lowerIsBetter' => true],
                ['name' => 'Resolution Rate', 'icon' => 'check_circle', 'current' => 87, 'previous' => 82, 'unit' => '%'],
                ['name' => 'CSAT Score', 'icon' => 'sentiment_satisfied', 'current' => 4.6, 'previous' => 4.3, 'unit' => '/5'],
                ['name' => 'Escalation Rate', 'icon' => 'swap_horiz', 'current' => 13, 'previous' => 18, 'unit' => '%', 'lowerIsBetter' => true],
                ['name' => 'First Response Time', 'icon' => 'schedule', 'current' => 28, 'previous' => 35, 'unit' => 's', 'lowerIsBetter' => true],
                ['name' => 'AI Accuracy', 'icon' => 'psychology', 'current' => 94, 'previous' => 91, 'unit' => '%'],
                ['name' => 'Active Contacts', 'icon' => 'group', 'current' => 523, 'previous' => 489, 'unit' => ''],
            ];
        @endphp

        @foreach($comparisonMetrics as $metric)
            @php
                $change = $metric['current'] - $metric['previous'];
                $percentChange = $metric['previous'] != 0 ? round(($change / $metric['previous']) * 100, 1) : 0;
                $isPositive = ($metric['lowerIsBetter'] ?? false) ? $change < 0 : $change > 0;
                $changeColor = $isPositive ? 'text-green-400' : 'text-red-400';
                $bgColor = $isPositive ? 'bg-green-500/10 border-green-500/20' : 'bg-red-500/10 border-red-500/20';
                $arrowIcon = $change > 0 ? 'trending_up' : ($change < 0 ? 'trending_down' : 'trending_flat');
            @endphp
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl bg-slate-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">{{ $metric['icon'] }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wider">{{ $metric['name'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 rounded-full {{ $bgColor }}">
                    <span class="material-symbols-outlined text-[16px] {{ $changeColor }}">{{ $arrowIcon }}</span>
                    <span class="text-xs font-bold {{ $changeColor }}">{{ abs($percentChange) }}%</span>
                </div>
            </div>
            
            <!-- Side-by-side values -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Current Period -->
                <div class="p-4 bg-primary/5 rounded-xl border border-primary/10">
                    <span class="text-xs text-primary block mb-1">Current Period</span>
                    <span class="text-2xl font-bold text-white">{{ $metric['current'] }}{{ $metric['unit'] }}</span>
                </div>
                <!-- Previous Period -->
                <div class="p-4 bg-slate-800/50 rounded-xl border border-slate-700">
                    <span class="text-xs text-slate-400 block mb-1">Previous Period</span>
                    <span class="text-2xl font-bold text-slate-400">{{ $metric['previous'] }}{{ $metric['unit'] }}</span>
                </div>
            </div>
            
            <!-- Change indicator -->
            <div class="mt-4 flex items-center justify-between">
                <span class="text-xs text-slate-500">Change from previous</span>
                <span class="text-sm font-bold {{ $changeColor }}">
                    {{ $change > 0 ? '+' : '' }}{{ $change }}{{ $metric['unit'] }}
                </span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Trend Comparison Chart -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-semibold text-lg text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">multiline_chart</span>
                Trend Comparison
            </h3>
            <div class="flex items-center gap-2">
                <button class="px-3 py-1 bg-primary text-white text-xs rounded-lg">All Metrics</button>
                <button class="px-3 py-1 bg-slate-800 text-slate-400 text-xs rounded-lg hover:bg-slate-700">Conversations</button>
                <button class="px-3 py-1 bg-slate-800 text-slate-400 text-xs rounded-lg hover:bg-slate-700">Response Time</button>
                <button class="px-3 py-1 bg-slate-800 text-slate-400 text-xs rounded-lg hover:bg-slate-700">CSAT</button>
            </div>
        </div>
        
        @php
            $trendData = $comparisonTrends ?? [
                ['day' => 'Mon', 'current' => 180, 'previous' => 165],
                ['day' => 'Tue', 'current' => 220, 'previous' => 195],
                ['day' => 'Wed', 'current' => 195, 'previous' => 180],
                ['day' => 'Thu', 'current' => 240, 'previous' => 210],
                ['day' => 'Fri', 'current' => 210, 'previous' => 185],
                ['day' => 'Sat', 'current' => 150, 'previous' => 140],
                ['day' => 'Sun', 'current' => 130, 'previous' => 120],
            ];
            $maxValue = max(array_merge(array_column($trendData, 'current'), array_column($trendData, 'previous')));
        @endphp
        
        <div class="h-64 relative">
            <!-- Y-axis labels -->
            <div class="absolute left-0 top-0 bottom-8 w-10 flex flex-col justify-between text-xs text-slate-500">
                <span>{{ $maxValue }}</span>
                <span>{{ round($maxValue * 0.75) }}</span>
                <span>{{ round($maxValue * 0.5) }}</span>
                <span>{{ round($maxValue * 0.25) }}</span>
                <span>0</span>
            </div>
            <!-- Chart -->
            <div class="ml-12 h-full flex items-end gap-4 pb-8">
                @foreach($trendData as $data)
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full flex items-end gap-1 h-40">
                        <!-- Current period bar -->
                        <div class="flex-1 bg-primary rounded-t relative group cursor-pointer"
                             style="height: {{ ($data['current'] / $maxValue) * 100 }}%">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ $data['current'] }}
                            </div>
                        </div>
                        <!-- Previous period bar -->
                        <div class="flex-1 bg-slate-600 rounded-t relative group cursor-pointer"
                             style="height: {{ ($data['previous'] / $maxValue) * 100 }}%">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ $data['previous'] }}
                            </div>
                        </div>
                    </div>
                    <span class="text-xs text-slate-500">{{ $data['day'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex items-center justify-center gap-6 mt-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-primary"></span>
                <span class="text-sm text-slate-400">Current Period</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-slate-600"></span>
                <span class="text-sm text-slate-400">Previous Period</span>
            </div>
        </div>
    </div>

    <!-- Benchmark Comparison Section -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-semibold text-lg text-white mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">emoji_events</span>
            Industry Benchmark Comparison
        </h3>
        
        @php
            $benchmarks = $benchmarkData ?? [
                ['metric' => 'First Response Time', 'unit' => 's', 'your' => 28, 'industry' => 45, 'top' => 15, 'lowerIsBetter' => true],
                ['metric' => 'Resolution Rate', 'unit' => '%', 'your' => 87, 'industry' => 75, 'top' => 92],
                ['metric' => 'CSAT Score', 'unit' => '/5', 'your' => 4.6, 'industry' => 4.0, 'top' => 4.8],
                ['metric' => 'Escalation Rate', 'unit' => '%', 'your' => 13, 'industry' => 25, 'top' => 8, 'lowerIsBetter' => true],
                ['metric' => 'AI Accuracy', 'unit' => '%', 'your' => 94, 'industry' => 85, 'top' => 97],
            ];
        @endphp

        <div class="space-y-6">
            @foreach($benchmarks as $bench)
                @php
                    $vsIndustry = $bench['lowerIsBetter'] ?? false 
                        ? (($bench['industry'] - $bench['your']) / $bench['industry'] * 100)
                        : (($bench['your'] - $bench['industry']) / $bench['industry'] * 100);
                    $isBetter = $vsIndustry > 0;
                    $yourPosition = ($bench['your'] - $bench['industry']) / ($bench['top'] - $bench['industry']) * 100;
                    $yourPosition = max(0, min(100, $yourPosition));
                @endphp
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-white font-medium">{{ $bench['metric'] }}</span>
                    <div class="flex items-center gap-4">
                        <span class="text-xs {{ $isBetter ? 'text-green-400' : 'text-red-400' }}">
                            {{ $isBetter ? '+' : '' }}{{ round($vsIndustry) }}% vs industry
                        </span>
                        <span class="text-sm font-bold text-primary">{{ $bench['your'] }}{{ $bench['unit'] }}</span>
                    </div>
                </div>
                <!-- Benchmark bar -->
                <div class="relative h-8 bg-slate-800 rounded-lg overflow-hidden">
                    <!-- Industry average marker -->
                    <div class="absolute top-0 bottom-0 w-0.5 bg-yellow-500 z-10" style="left: 25%">
                        <div class="absolute -top-5 left-1/2 -translate-x-1/2 text-[10px] text-yellow-500 whitespace-nowrap">Industry Avg</div>
                    </div>
                    <!-- Top performer marker -->
                    <div class="absolute top-0 bottom-0 w-0.5 bg-green-500 z-10" style="left: 100%">
                        <div class="absolute -top-5 right-0 text-[10px] text-green-500 whitespace-nowrap">Top 10%</div>
                    </div>
                    <!-- Your position -->
                    <div class="absolute top-1 bottom-1 w-4 bg-primary rounded transition-all duration-500" 
                         style="left: calc({{ $yourPosition }}% - 8px)">
                        <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] text-primary whitespace-nowrap">You</div>
                    </div>
                </div>
                <div class="flex justify-between mt-1 text-xs text-slate-500">
                    <span>{{ $bench['industry'] }}{{ $bench['unit'] }}</span>
                    <span>{{ $bench['top'] }}{{ $bench['unit'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Forecast/Projection Chart -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-semibold text-lg text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">insights</span>
                Forecast & Projection
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Based on last 30 days trend</span>
                <span class="px-2 py-1 bg-purple-500/20 text-purple-400 text-xs rounded">ML Predicted</span>
            </div>
        </div>
        
        @php
            $forecastData = $forecast ?? [
                ['period' => 'Week 1', 'actual' => 180, 'predicted' => null, 'upper' => null, 'lower' => null],
                ['period' => 'Week 2', 'actual' => 210, 'predicted' => null, 'upper' => null, 'lower' => null],
                ['period' => 'Week 3', 'actual' => 195, 'predicted' => null, 'upper' => null, 'lower' => null],
                ['period' => 'Week 4', 'actual' => 240, 'predicted' => null, 'upper' => null, 'lower' => null],
                ['period' => 'Week 5', 'actual' => null, 'predicted' => 225, 'upper' => 245, 'lower' => 205],
                ['period' => 'Week 6', 'actual' => null, 'predicted' => 235, 'upper' => 260, 'lower' => 210],
                ['period' => 'Week 7', 'actual' => null, 'predicted' => 250, 'upper' => 280, 'lower' => 220],
                ['period' => 'Week 8', 'actual' => null, 'predicted' => 245, 'upper' => 275, 'lower' => 215],
            ];
            $maxForecast = collect($forecastData)->flatMap(fn($d) => [$d['actual'] ?? 0, $d['predicted'] ?? 0, $d['upper'] ?? 0])->max();
        @endphp
        
        <div class="h-64 relative">
            <div class="absolute left-0 top-0 bottom-8 w-10 flex flex-col justify-between text-xs text-slate-500">
                <span>{{ $maxForecast }}</span>
                <span>{{ round($maxForecast * 0.75) }}</span>
                <span>{{ round($maxForecast * 0.5) }}</span>
                <span>{{ round($maxForecast * 0.25) }}</span>
                <span>0</span>
            </div>
            <div class="ml-12 h-full flex items-end gap-2 pb-8">
                @foreach($forecastData as $i => $data)
                <div class="flex-1 flex flex-col items-center gap-2 group">
                    @if($data['actual'])
                        <!-- Actual data point -->
                        <div class="w-full flex justify-center">
                            <div class="w-4 h-4 rounded-full bg-primary border-2 border-surface-dark relative cursor-pointer hover:scale-125 transition-transform"
                                 style="margin-bottom: {{ ($data['actual'] / $maxForecast) * 160 - 8 }}px;">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    {{ $data['actual'] }} (Actual)
                                </div>
                            </div>
                        </div>
                        <!-- Connector line -->
                        @if($i < count($forecastData) - 1 && $forecastData[$i + 1]['actual'])
                        <div class="absolute w-full h-0.5 bg-primary/50" style="bottom: {{ ($data['actual'] / $maxForecast) * 160 + 32 }}px; width: calc(12.5% - 8px);"></div>
                        @endif
                    @else
                        <!-- Predicted with confidence interval -->
                        <div class="w-full relative" style="height: 160px;">
                            <!-- Confidence interval -->
                            <div class="absolute w-full bg-purple-500/20 rounded" 
                                 style="bottom: {{ ($data['lower'] / $maxForecast) * 160 }}px; height: {{ (($data['upper'] - $data['lower']) / $maxForecast) * 160 }}px;">
                            </div>
                            <!-- Prediction point -->
                            <div class="absolute w-3 h-3 rounded-full bg-purple-400 border-2 border-surface-dark left-1/2 -translate-x-1/2 cursor-pointer hover:scale-125 transition-transform"
                                 style="bottom: {{ ($data['predicted'] / $maxForecast) * 160 - 6 }}px;">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    {{ $data['predicted'] }} (Predicted)
                                </div>
                            </div>
                        </div>
                    @endif
                    <span class="text-xs {{ $data['actual'] ? 'text-slate-500' : 'text-purple-400' }}">{{ $data['period'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex items-center justify-center gap-6 mt-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-primary"></span>
                <span class="text-sm text-slate-400">Actual</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-purple-400"></span>
                <span class="text-sm text-slate-400">Predicted</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-purple-500/20"></span>
                <span class="text-sm text-slate-400">Confidence Range</span>
            </div>
        </div>
    </div>

    <!-- Summary Insights -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-green-500/10 rounded-2xl p-6 border border-green-500/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="size-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">trending_up</span>
                </div>
                <h4 class="font-semibold text-white">Improvements</h4>
            </div>
            <ul class="space-y-2 text-sm">
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
                    <span>Response time improved by 13.5%</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
                    <span>CSAT score increased to 4.6/5</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
                    <span>Escalation rate reduced by 5%</span>
                </li>
            </ul>
        </div>

        <div class="bg-yellow-500/10 rounded-2xl p-6 border border-yellow-500/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="size-10 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-400">lightbulb</span>
                </div>
                <h4 class="font-semibold text-white">Opportunities</h4>
            </div>
            <ul class="space-y-2 text-sm">
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-yellow-400 text-[18px]">info</span>
                    <span>Weekend response time needs attention</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-yellow-400 text-[18px]">info</span>
                    <span>Instagram queries 15% slower than WhatsApp</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-yellow-400 text-[18px]">info</span>
                    <span>AI accuracy can improve in technical queries</span>
                </li>
            </ul>
        </div>

        <div class="bg-blue-500/10 rounded-2xl p-6 border border-blue-500/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="size-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">auto_awesome</span>
                </div>
                <h4 class="font-semibold text-white">Predictions</h4>
            </div>
            <ul class="space-y-2 text-sm">
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-blue-400 text-[18px]">forecast</span>
                    <span>Next week: ~225 conversations (+6%)</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-blue-400 text-[18px]">forecast</span>
                    <span>Projected CSAT: 4.7 by month end</span>
                </li>
                <li class="flex items-start gap-2 text-slate-300">
                    <span class="material-symbols-outlined text-blue-400 text-[18px]">forecast</span>
                    <span>AI accuracy trending toward 96%</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
