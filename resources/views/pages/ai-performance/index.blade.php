@extends('layouts.dark')

@section('title', 'AI Performance Analytics')

@section('content')
@php
    // Calculate max values for charts
    $intentCounts = $popularIntents['intents'] ?? [];
    $maxIntentCount = !empty($intentCounts) && is_array($intentCounts) ? max(array_column($intentCounts, 'count') ?: [1]) : 1;
    
    $gapQueries = isset($knowledgeGaps['top_queries']) ? $knowledgeGaps['top_queries']->toArray() : [];
    $maxGapCount = !empty($gapQueries) ? max(array_column($gapQueries, 'count') ?: [1]) : 1;
@endphp

<div class="space-y-6" x-data="{ activeTab: 'overview' }">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">AI Performance Analytics</h1>
            <p class="text-slate-400 text-sm">Monitor and analyze AI model performance, accuracy, and learning progress</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('ai-performance.index') }}" class="flex items-center gap-2">
                <select name="range" onchange="this.form.submit()" class="bg-surface-dark border border-slate-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="14" {{ $dateRange == '14' ? 'selected' : '' }}>Last 14 Days</option>
                    <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 Days</option>
                </select>
            </form>
            <button @click="window.print()" class="flex items-center gap-2 px-4 py-2 bg-surface-dark border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition-all text-sm">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Print
            </button>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Overall Accuracy -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="size-12 rounded-xl bg-green-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-green-400">psychology</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ $intentAccuracy['overall_accuracy'] >= 80 ? 'bg-green-500/20 text-green-400' : ($intentAccuracy['overall_accuracy'] >= 60 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                    {{ $intentAccuracy['overall_accuracy'] >= 80 ? 'Excellent' : ($intentAccuracy['overall_accuracy'] >= 60 ? 'Good' : 'Needs Work') }}
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Intent Accuracy</p>
            <h4 class="text-3xl font-black text-white">{{ $intentAccuracy['overall_accuracy'] }}%</h4>
            <p class="text-xs text-slate-500 mt-2">{{ $intentAccuracy['total_analyzed'] }} conversations analyzed</p>
        </div>

        <!-- Response Relevance -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="size-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-blue-400">thumb_up</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ $responseRelevance['relevance_score'] >= 80 ? 'bg-green-500/20 text-green-400' : ($responseRelevance['relevance_score'] >= 60 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                    {{ $responseRelevance['relevance_score'] >= 80 ? 'High' : ($responseRelevance['relevance_score'] >= 60 ? 'Medium' : 'Low') }}
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Response Relevance</p>
            <h4 class="text-3xl font-black text-white">{{ $responseRelevance['relevance_score'] }}%</h4>
            <p class="text-xs text-slate-500 mt-2">{{ $responseRelevance['success_rate'] ?? 0 }}% success rate</p>
        </div>

        <!-- Knowledge Gaps -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="size-12 rounded-xl bg-orange-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-orange-400">help_outline</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ $knowledgeGaps['unique_queries'] > 10 ? 'bg-red-500/20 text-red-400' : ($knowledgeGaps['unique_queries'] > 5 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-green-500/20 text-green-400') }}">
                    {{ $knowledgeGaps['unique_queries'] > 10 ? 'Action Needed' : 'Managed' }}
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Knowledge Gaps</p>
            <h4 class="text-3xl font-black text-white">{{ $knowledgeGaps['unique_queries'] }}</h4>
            <p class="text-xs text-slate-500 mt-2">{{ $knowledgeGaps['total_unanswered'] }} unanswered queries</p>
        </div>

        <!-- Improvement Trend -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <div class="size-12 rounded-xl bg-purple-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-purple-400">trending_up</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ $trainingImprovement['trend_direction'] === 'up' ? 'bg-green-500/20 text-green-400' : ($trainingImprovement['trend_direction'] === 'down' ? 'bg-red-500/20 text-red-400' : 'bg-slate-500/20 text-slate-400') }}">
                    {{ $trainingImprovement['trend_direction'] === 'up' ? 'Improving' : ($trainingImprovement['trend_direction'] === 'down' ? 'Declining' : 'Stable') }}
                </span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Improvement Rate</p>
            <h4 class="text-3xl font-black {{ $trainingImprovement['improvement_rate'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                {{ $trainingImprovement['improvement_rate'] >= 0 ? '+' : '' }}{{ $trainingImprovement['improvement_rate'] }}%
            </h4>
            <p class="text-xs text-slate-500 mt-2">vs previous period</p>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Confidence Distribution -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-lg text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">bar_chart</span>
                Confidence Distribution
            </h3>
            <div class="space-y-4">
                @foreach($confidenceDistribution['buckets'] as $key => $bucket)
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="size-3 rounded-full {{ $key === 'high' ? 'bg-green-500' : ($key === 'medium' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                            <span class="text-slate-300">{{ ucfirst($key) }} ({{ $bucket['range'] }})</span>
                        </div>
                        <span class="text-white font-bold">{{ $bucket['percentage'] }}%</span>
                    </div>
                    <div class="h-3 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full {{ $key === 'high' ? 'bg-green-500' : ($key === 'medium' ? 'bg-yellow-500' : 'bg-red-500') }} transition-all duration-500" style="width: {{ $bucket['percentage'] }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $bucket['count'] }} responses</span>
                        <span>{{ $bucket['success_rate'] ?? 0 }}% success</span>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-6 pt-4 border-t border-slate-800">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400">Total AI Responses</span>
                    <span class="text-white font-bold">{{ number_format($confidenceDistribution['total']) }}</span>
                </div>
            </div>
        </div>

        <!-- Popular Intents -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-lg text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">donut_large</span>
                Popular Intents
            </h3>
            <div class="space-y-3">
                @forelse($popularIntents['top_5'] ?? [] as $index => $intent)
                <div class="flex items-center gap-4">
                    <span class="text-xs font-bold text-slate-500 w-6">{{ $index + 1 }}</span>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-white font-medium capitalize">{{ $intent['intent'] }}</span>
                            <span class="text-xs text-slate-400">{{ $intent['count'] }} ({{ $intent['percentage'] }}%)</span>
                        </div>
                        <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-primary to-purple-500 rounded-full" style="width: {{ $maxIntentCount > 0 ? ($intent['count'] / $maxIntentCount) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded {{ $intent['dominant_sentiment'] === 'positive' ? 'bg-green-500/10 text-green-400' : ($intent['dominant_sentiment'] === 'negative' ? 'bg-red-500/10 text-red-400' : 'bg-slate-500/10 text-slate-400') }}">
                        {{ $intent['avg_confidence'] * 100 }}%
                    </span>
                </div>
                @empty
                <div class="text-center py-8 text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2">psychology_alt</span>
                    <p>No intent data available yet</p>
                </div>
                @endforelse
            </div>
            <div class="mt-6 pt-4 border-t border-slate-800">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400">Total Intents Detected</span>
                    <span class="text-white font-bold">{{ number_format($popularIntents['total_intents_detected'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Intent Accuracy by Category -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-lg text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">analytics</span>
                Accuracy by Intent Category
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                        <tr>
                            <th class="pb-3">Intent</th>
                            <th class="pb-3 text-right">Total</th>
                            <th class="pb-3 text-right">Accuracy</th>
                            <th class="pb-3 text-right">Resolution</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($intentAccuracy['by_intent'] ?? [] as $intent)
                        <tr class="hover:bg-white/[0.02]">
                            <td class="py-3">
                                <span class="text-sm text-white capitalize">{{ $intent['intent'] }}</span>
                            </td>
                            <td class="py-3 text-right">
                                <span class="text-sm text-slate-400">{{ $intent['total'] }}</span>
                            </td>
                            <td class="py-3 text-right">
                                <span class="text-sm font-bold {{ $intent['accuracy_percentage'] >= 80 ? 'text-green-400' : ($intent['accuracy_percentage'] >= 60 ? 'text-yellow-400' : 'text-red-400') }}">
                                    {{ $intent['accuracy_percentage'] }}%
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <span class="text-sm text-slate-400">{{ $intent['resolution_rate'] }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">
                                No accuracy data available
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Knowledge Gaps -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-semibold text-lg text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">lightbulb</span>
                    Top Knowledge Gaps
                </h3>
            </div>
            <div class="space-y-3">
                @forelse($knowledgeGaps['top_queries'] ?? [] as $query)
                <div class="flex items-start gap-3 p-3 bg-slate-800/50 rounded-xl">
                    <span class="material-symbols-outlined text-orange-400 text-lg mt-0.5">help_outline</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white truncate">{{ $query['question'] }}</p>
                        <div class="flex items-center gap-4 mt-1 text-xs text-slate-500">
                            <span>Asked {{ $query['count'] }} times</span>
                            <span>Last: {{ \Carbon\Carbon::parse($query['last_asked_at'])->diffForHumans() }}</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded {{ $query['count'] >= 10 ? 'bg-red-500/20 text-red-400' : ($query['count'] >= 5 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-slate-500/20 text-slate-400') }}">
                        {{ $query['count'] >= 20 ? 'Critical' : ($query['count'] >= 10 ? 'High' : ($query['count'] >= 5 ? 'Medium' : 'Low')) }}
                    </span>
                </div>
                @empty
                <div class="text-center py-8 text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2">check_circle</span>
                    <p>No knowledge gaps found</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Training Improvement Chart -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-semibold text-lg text-white mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">trending_up</span>
            Training Improvement Trend
        </h3>
        <div class="h-48 flex items-end gap-2">
            @foreach($trainingImprovement['monthly_history'] ?? [] as $month)
            <div class="flex-1 flex flex-col items-center gap-2">
                <div class="w-full bg-primary/20 rounded-t-lg relative group" style="height: {{ max(4, $month['accuracy'] * 1.6) }}px;">
                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-700 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                        {{ $month['accuracy'] }}% ({{ $month['total'] }})
                    </div>
                </div>
                <span class="text-xs text-slate-500">{{ $month['month'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="mt-4 pt-4 border-t border-slate-800 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-slate-500">Current Accuracy</p>
                <p class="text-lg font-bold text-white">{{ $trainingImprovement['current_performance']['success_rate'] ?? 0 }}%</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Avg Confidence</p>
                <p class="text-lg font-bold text-white">{{ $trainingImprovement['current_performance']['avg_confidence'] ?? 0 }}%</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Interactions</p>
                <p class="text-lg font-bold text-white">{{ number_format($trainingImprovement['current_performance']['total_interactions'] ?? 0) }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Training Examples</p>
                <p class="text-lg font-bold text-white">{{ number_format($trainingImprovement['current_performance']['training_examples_added'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Recent AI Logs -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-lg text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Recent AI Interactions
            </h3>
            <a href="{{ route('logs.index') }}" class="text-xs text-primary hover:underline">View All Logs</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-800/50 text-[10px] uppercase font-black text-slate-500 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Time</th>
                        <th class="px-6 py-4">Source</th>
                        <th class="px-6 py-4">Trigger</th>
                        <th class="px-6 py-4">Confidence</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-white capitalize">{{ $log->response_source ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs text-slate-300 truncate max-w-xs">{{ \Str::limit($log->trigger_text, 50) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->ai_confidence)
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full {{ $log->ai_confidence >= 0.8 ? 'bg-green-500' : ($log->ai_confidence >= 0.5 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $log->ai_confidence * 100 }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-400">{{ round($log->ai_confidence * 100) }}%</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'sent' => 'bg-green-500/10 text-green-400',
                                    'sent_ai' => 'bg-green-500/10 text-green-400',
                                    'success' => 'bg-green-500/10 text-green-400',
                                    'failed' => 'bg-red-500/10 text-red-400',
                                    'error' => 'bg-red-500/10 text-red-400',
                                    'skipped' => 'bg-slate-500/10 text-slate-400',
                                ];
                                $color = $statusColors[$log->status] ?? 'bg-blue-500/10 text-blue-400';
                            @endphp
                            <span class="px-2 py-1 {{ $color }} text-[9px] font-black uppercase rounded-full">
                                {{ $log->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-slate-700 mb-2">inbox</span>
                            <p class="text-slate-500 text-sm">No AI interactions found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- API Endpoints Reference -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-semibold text-lg text-white mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">api</span>
            API Endpoints
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="p-3 bg-slate-800/50 rounded-lg">
                <code class="text-green-400">GET /api/ai-performance/intent-accuracy</code>
                <p class="text-slate-400 text-xs mt-1">Get intent recognition accuracy metrics</p>
            </div>
            <div class="p-3 bg-slate-800/50 rounded-lg">
                <code class="text-green-400">GET /api/ai-performance/response-relevance</code>
                <p class="text-slate-400 text-xs mt-1">Get response quality and relevance scores</p>
            </div>
            <div class="p-3 bg-slate-800/50 rounded-lg">
                <code class="text-green-400">GET /api/ai-performance/knowledge-gaps</code>
                <p class="text-slate-400 text-xs mt-1">Identify missing knowledge base articles</p>
            </div>
            <div class="p-3 bg-slate-800/50 rounded-lg">
                <code class="text-green-400">GET /api/ai-performance/training-improvement</code>
                <p class="text-slate-400 text-xs mt-1">Track AI learning progress over time</p>
            </div>
            <div class="p-3 bg-slate-800/50 rounded-lg">
                <code class="text-green-400">GET /api/ai-performance/confidence-distribution</code>
                <p class="text-slate-400 text-xs mt-1">Get AI confidence histogram data</p>
            </div>
            <div class="p-3 bg-slate-800/50 rounded-lg">
                <code class="text-green-400">GET /api/ai-performance/popular-intents</code>
                <p class="text-slate-400 text-xs mt-1">Get most common customer intents</p>
            </div>
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
