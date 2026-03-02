@extends('admin.layouts.app')

@section('title', 'Advanced Analytics')
@section('page_title', 'Advanced Analytics')

@section('content')

<!-- Period Selector -->
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2">
        <a href="?period=7" class="px-4 py-2 rounded-lg {{ $period == 7 ? 'bg-primary text-white' : 'bg-surface-dark text-slate-400 hover:text-white' }}">7 Days</a>
        <a href="?period=30" class="px-4 py-2 rounded-lg {{ $period == 30 ? 'bg-primary text-white' : 'bg-surface-dark text-slate-400 hover:text-white' }}">30 Days</a>
        <a href="?period=90" class="px-4 py-2 rounded-lg {{ $period == 90 ? 'bg-primary text-white' : 'bg-surface-dark text-slate-400 hover:text-white' }}">90 Days</a>
    </div>
    <button onclick="exportReport()" class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition">
        <span class="material-symbols-outlined">download</span>
        Export Report
    </button>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Total Users</div>
        <div class="text-2xl font-black">{{ number_format($metrics['total_users']) }}</div>
        <div class="text-xs text-green-400 mt-1">+{{ number_format($metrics['new_users']) }} new</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">MRR</div>
        <div class="text-2xl font-black text-green-400">Rp {{ number_format($metrics['mrr'], 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-1">ARR: Rp {{ number_format($metrics['arr'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">ARPU</div>
        <div class="text-2xl font-black text-blue-400">Rp {{ number_format($metrics['avg_revenue_per_user'], 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-1">Per user average</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-5 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Conversion Rate</div>
        <div class="text-2xl font-black text-yellow-400">{{ number_format($metrics['conversion_rate'], 1) }}%</div>
        <div class="text-xs text-slate-400 mt-1">Free to paid</div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- User Growth -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4">User Growth</h3>
        <canvas id="userGrowthChart" height="200"></canvas>
    </div>
    
    <!-- Revenue -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4">Revenue Trends</h3>
        <canvas id="revenueChart" height="200"></canvas>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Churn Analysis -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4">Churn Analysis</h3>
        <canvas id="churnChart" height="200"></canvas>
    </div>
    
    <!-- Feature Usage -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4">Feature Usage</h3>
        <div class="space-y-3">
            @foreach($featureUsage as $feature => $count)
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                <span class="font-bold">{{ number_format($count) }}</span>
            </div>
            <div class="h-2 bg-surface-light rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full" style="width: {{ min(100, $count / 1000 * 100) }}%"></div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Support Metrics -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4">Support Performance</h3>
        <div class="space-y-4">
            <div class="p-3 bg-surface-light rounded-lg">
                <div class="text-sm text-slate-400">Avg Response Time</div>
                <div class="font-bold text-lg">
                    @if($supportMetrics['avg_response_time'])
                        {{ number_format($supportMetrics['avg_response_time'] / 60, 1) }} hours
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="p-3 bg-surface-light rounded-lg">
                <div class="text-sm text-slate-400">Avg Resolution Time</div>
                <div class="font-bold text-lg">
                    @if($supportMetrics['avg_resolution_time'])
                        {{ number_format($supportMetrics['avg_resolution_time'] / 60 / 24, 1) }} days
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="p-3 bg-surface-light rounded-lg">
                <div class="text-sm text-slate-400">CSAT Score</div>
                <div class="font-bold text-lg {{ ($supportMetrics['csat_score'] ?? 0) >= 4 ? 'text-green-400' : 'text-yellow-400' }}">
                    {{ $supportMetrics['csat_score'] ? number_format($supportMetrics['csat_score'], 1) . '/5' : 'N/A' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cohort Analysis -->
<div class="bg-surface-dark rounded-xl p-6 border border-slate-800 mb-6">
    <h3 class="font-bold mb-4">Cohort Analysis - User Retention</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700">
                    <th class="text-left py-2 px-3">Cohort</th>
                    <th class="text-center py-2 px-3">Users</th>
                    @for($i = 0; $i <= 5; $i++)
                    <th class="text-center py-2 px-3">Month {{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($cohorts as $cohort)
                <tr class="border-b border-slate-800">
                    <td class="py-2 px-3 font-medium">{{ $cohort['month'] }}</td>
                    <td class="text-center py-2 px-3">{{ $cohort['size'] }}</td>
                    @foreach($cohort['retention'] as $retention)
                    <td class="text-center py-2 px-3">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $retention['retention'] >= 50 ? 'bg-green-500/20 text-green-400' : 
                               ($retention['retention'] >= 25 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                            {{ $retention['retention'] }}%
                        </span>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Growth Chart
new Chart(document.getElementById('userGrowthChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($userGrowth, 'date')) !!}.map(d => d.slice(5)),
        datasets: [{
            label: 'New Users',
            data: {!! json_encode(array_column($userGrowth, 'new')) !!},
            borderColor: '#135bec',
            backgroundColor: 'rgba(19, 91, 236, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Cumulative',
            data: {!! json_encode(array_column($userGrowth, 'cumulative')) !!},
            borderColor: '#10b981',
            borderDash: [5, 5],
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: { intersect: false },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
            y1: { position: 'right', grid: { display: false } },
            x: { grid: { display: false } }
        }
    }
});

// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($revenueData, 'date')) !!}.map(d => d.slice(5)),
        datasets: [{
            label: 'Revenue',
            data: {!! json_encode(array_column($revenueData, 'revenue')) !!},
            backgroundColor: '#10b981',
            borderRadius: 4
        }, {
            label: 'Refunds',
            data: {!! json_encode(array_column($revenueData, 'refunds')) !!},
            backgroundColor: '#ef4444',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});

// Churn Chart
new Chart(document.getElementById('churnChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($churnData, 'date')) !!}.map(d => d.slice(5)),
        datasets: [{
            label: 'Churn Rate %',
            data: {!! json_encode(array_column($churnData, 'rate')) !!},
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});

function exportReport() {
    alert('Export functionality - CSV/PDF export would be implemented here');
}
</script>

@endsection
