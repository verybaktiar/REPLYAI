@extends('layouts.dark')

@section('title', 'AI Analytics Dashboard')

@push('styles')
<style>
    /* Fix chart overflow issues */
    .chart-container {
        position: relative;
        height: 100%;
        width: 100%;
        overflow: hidden;
    }
    canvas {
        max-height: 100% !important;
        max-width: 100% !important;
    }
</style>
@endpush

@section('content')
<div class="p-4 mx-auto max-w-7xl">
    <!-- Header -->
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                🤖 AI Analytics Dashboard
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Monitor your AI performance, KB coverage, and customer interactions
            </p>
        </div>
        
        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <!-- Time Range -->
            <select id="daysFilter" onchange="updateDashboard()" 
                class="px-4 py-2 text-sm border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
            </select>
            
            <!-- Business Profile -->
            <select id="profileFilter" onchange="updateDashboard()"
                class="px-4 py-2 text-sm border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary">
                <option value="">All Profiles</option>
                @foreach($businessProfiles as $profile)
                    <option value="{{ $profile->id }}" {{ $profileFilter == $profile->id ? 'selected' : '' }}>
                        {{ $profile->name }}
                    </option>
                @endforeach
            </select>
            
            <!-- Export -->
            <a href="{{ route('analytics.export') }}" 
                class="flex items-center gap-2 px-4 py-2 text-sm text-white transition bg-primary rounded-lg hover:bg-primary/90">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
            </a>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Conversations -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Conversations</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($metrics['total_conversations']) }}</p>
                </div>
                <div class="p-3 text-blue-600 bg-blue-100 rounded-lg dark:bg-blue-900/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 dark:text-green-400">
                    {{ $metrics['ai_reply_rate'] }}%
                </span>
                <span class="ml-2 text-gray-500 dark:text-gray-400">replied by AI</span>
            </div>
        </div>

        <!-- KB Coverage -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">KB Coverage</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $metrics['kb_coverage'] }}%</p>
                </div>
                <div class="p-3 rounded-lg {{ $metrics['kb_coverage'] >= 80 ? 'text-green-600 bg-green-100 dark:bg-green-900/30' : ($metrics['kb_coverage'] >= 50 ? 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30' : 'text-red-600 bg-red-100 dark:bg-red-900/30') }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-500 dark:text-gray-400">{{ number_format($metrics['kb_articles']) }} articles</span>
                @if($metrics['missed_queries'] > 0)
                    <span class="ml-auto text-red-600 dark:text-red-400">{{ $metrics['missed_queries'] }} missed</span>
                @endif
            </div>
        </div>

        <!-- Response Time -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Response Time</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $metrics['avg_response_time'] }}s</p>
                </div>
                <div class="p-3 text-purple-600 bg-purple-100 rounded-lg dark:bg-purple-900/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 dark:text-green-400">Fast</span>
                <span class="ml-2 text-gray-500 dark:text-gray-400">average</span>
            </div>
        </div>

        <!-- Satisfaction -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Satisfaction Rate</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $metrics['satisfaction_rate'] }}%</p>
                </div>
                <div class="p-3 text-pink-600 bg-pink-100 rounded-lg dark:bg-pink-900/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 dark:text-green-400">Excellent</span>
                <span class="ml-2 text-gray-500 dark:text-gray-400">rating</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid gap-6 mb-6 lg:grid-cols-2">
        <!-- Conversation Trends -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">📈 Conversation Trends</h3>
            <div class="chart-container" style="height: 250px; max-height: 250px;">
                <canvas id="conversationChart"></canvas>
            </div>
        </div>

        <!-- Message Sources -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">📱 Message Sources</h3>
            <div class="chart-container flex items-center justify-center" style="height: 250px; max-height: 250px;">
                <canvas id="sourceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- KB Coverage & Sentiment Row -->
    <div class="grid gap-6 mb-6 lg:grid-cols-2">
        <!-- KB Coverage Analysis -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📚 KB Coverage Analysis</h3>
                <a href="{{ route('kb.index') }}" class="text-sm text-primary hover:underline">Manage KB →</a>
            </div>
            <div class="chart-container flex items-center justify-center mb-4" style="height: 200px; max-height: 200px;">
                <canvas id="kbCoverageChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="p-3 bg-green-50 rounded-lg dark:bg-green-900/20">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($metrics['ai_replied']) }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Answered</p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg dark:bg-red-900/20">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($metrics['missed_queries']) }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Missed</p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg dark:bg-yellow-900/20">
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($metrics['kb_articles']) }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Articles</p>
                </div>
            </div>
        </div>

        <!-- Sentiment Analysis -->
        <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">😊 Customer Sentiment</h3>
            <div class="chart-container flex items-center justify-center mb-4" style="height: 200px; max-height: 200px;">
                <canvas id="sentimentChart"></canvas>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">😊 Positive</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                            <div class="h-2 bg-green-500 rounded-full" style="width: 65%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">65%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">😐 Neutral</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                            <div class="h-2 bg-gray-500 rounded-full" style="width: 25%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">25%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">😔 Negative</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                            <div class="h-2 bg-red-500 rounded-full" style="width: 10%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">10%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Missed Queries & Training Suggestions -->
    <div class="p-5 mb-6 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">❓ Missed Queries & Training Suggestions</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Questions the AI couldn't answer - add these to your KB to improve coverage</p>
            </div>
            <a href="{{ route('kb.index') }}" class="px-4 py-2 text-sm text-white transition bg-primary rounded-lg hover:bg-primary/90">
                + Add Article
            </a>
        </div>
        
        @if(count($missedQueries) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Question</th>
                            <th class="px-4 py-3">Count</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missedQueries as $query)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ Str::limit($query->question, 80) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900/30 dark:text-blue-400">
                                        {{ $query->count }}x
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($query->status === 'pending')
                                        <span class="px-2 py-1 text-xs font-medium text-yellow-600 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-400">
                                            Pending
                                        </span>
                                    @elseif($query->status === 'resolved')
                                        <span class="px-2 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">
                                            Resolved
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-400">
                                            Ignored
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        @if($query->status === 'pending')
                                            <button onclick="resolveQuery({{ $query->id }})" 
                                                class="px-3 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-700">
                                                Resolve
                                            </button>
                                            <form action="{{ route('admin.analytics.missed-query.ignore', $query) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1 text-xs text-gray-700 bg-gray-200 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">
                                                    Ignore
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-500">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>Great job! No missed queries in this period.</p>
            </div>
        @endif
    </div>

    <!-- Popular Articles -->
    <div class="p-5 bg-white border border-gray-200 rounded-xl dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">🔥 Most Used KB Articles</h3>
            <a href="{{ route('kb.index') }}" class="text-sm text-primary hover:underline">View All →</a>
        </div>
        
        @if(count($popularArticles) > 0)
            <div class="space-y-3">
                @foreach($popularArticles as $article)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg dark:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white bg-primary rounded-full">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $article['title'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $article['category'] }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-medium text-primary bg-primary/10 rounded-full">
                            {{ $article['usage_count'] }} uses
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center text-gray-500 dark:text-gray-400">No usage data available yet.</p>
        @endif
    </div>
</div>

<!-- Resolve Query Modal -->
<div id="resolveModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeResolveModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Resolve Missed Query</h3>
                <form id="resolveForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Link to KB Article</label>
                        <select name="kb_article_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                            <option value="">Select an article...</option>
                            @foreach($businessProfiles as $profile)
                                @if($profile->kbArticles && $profile->kbArticles->count() > 0)
                                    <optgroup label="{{ $profile->name }}">
                                        @foreach($profile->kbArticles->where('is_active', true) as $kb)
                                            <option value="{{ $kb->id }}">{{ $kb->title }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Suggested Answer (Optional)</label>
                        <textarea name="suggested_answer" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Enter a suggested answer..."></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
                <button type="button" onclick="submitResolveForm()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Resolve
                </button>
                <button type="button" onclick="closeResolveModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize charts
let conversationChart, sourceChart, kbCoverageChart, sentimentChart;

document.addEventListener('DOMContentLoaded', function() {
    initCharts();
});

function initCharts() {
    // Conversation Trends Chart
    const convCtx = document.getElementById('conversationChart').getContext('2d');
    conversationChart = new Chart(convCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($trends)->pluck('date')) !!},
            datasets: [{
                label: 'Conversations',
                data: {!! json_encode(collect($trends)->pluck('conversations')) !!},
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'AI Replied',
                data: {!! json_encode(collect($trends)->pluck('ai_replied')) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 200,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // Source Chart (Doughnut)
    const sourceCtx = document.getElementById('sourceChart').getContext('2d');
    sourceChart = new Chart(sourceCtx, {
        type: 'doughnut',
        data: {
            labels: ['WhatsApp', 'Instagram'],
            datasets: [{
                data: [{{ $metrics['total_conversations'] }}, 0],
                backgroundColor: ['#10B981', '#8B5CF6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 200,
            cutout: '70%',
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // KB Coverage Chart
    const kbCtx = document.getElementById('kbCoverageChart').getContext('2d');
    kbCoverageChart = new Chart(kbCtx, {
        type: 'doughnut',
        data: {
            labels: ['Answered', 'Missed', 'Pending'],
            datasets: [{
                data: [{{ $metrics['ai_replied'] }}, {{ $metrics['missed_queries'] }}, 0],
                backgroundColor: ['#10B981', '#EF4444', '#F59E0B'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 200,
            cutout: '60%',
            plugins: { 
                legend: { position: 'bottom' },
                title: { display: true, text: 'Query Resolution' }
            }
        }
    });

    // Sentiment Chart
    const sentCtx = document.getElementById('sentimentChart').getContext('2d');
    sentimentChart = new Chart(sentCtx, {
        type: 'pie',
        data: {
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: ['#10B981', '#6B7280', '#EF4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            resizeDelay: 200,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

// Update dashboard based on filters
function updateDashboard() {
    const days = document.getElementById('daysFilter').value;
    const profileId = document.getElementById('profileFilter').value;
    
    const url = new URL(window.location.href);
    url.searchParams.set('days', days);
    if (profileId) url.searchParams.set('profile_id', profileId);
    else url.searchParams.delete('profile_id');
    
    window.location.href = url.toString();
}

// Modal functions
let currentQueryId = null;

function resolveQuery(queryId) {
    currentQueryId = queryId;
    const form = document.getElementById('resolveForm');
    form.action = `/admin/analytics/missed-query/${queryId}/resolve`;
    document.getElementById('resolveModal').classList.remove('hidden');
}

function closeResolveModal() {
    document.getElementById('resolveModal').classList.add('hidden');
    currentQueryId = null;
}

function submitResolveForm() {
    document.getElementById('resolveForm').submit();
}
</script>
@endpush
