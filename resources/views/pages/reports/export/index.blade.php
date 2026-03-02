@extends('layouts.dark')

@section('title', 'Export Reports')

@section('content')
<div class="space-y-6" x-data="{
    selectedFormat: 'pdf',
    reportType: 'overview',
    dateRange: '30',
    customStart: '',
    customEnd: '',
    showPreview: false,
    isScheduled: false,
    scheduleType: 'once',
    scheduleDay: 'monday',
    includeMetrics: {
        conversations: true,
        responseTime: true,
        csat: true,
        sentiment: true,
        aiPerformance: true,
        escalations: true,
        contacts: false,
        broadcasts: false,
        revenue: false
    },
    toggleAllMetrics() {
        const all = Object.values(this.includeMetrics).every(v => v);
        for (let key in this.includeMetrics) {
            this.includeMetrics[key] = !all;
        }
    }
}">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Export Reports</h1>
            <p class="text-slate-400 text-sm">Generate and download reports in various formats</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.export.history') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-dark border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition-all text-sm">
                <span class="material-symbols-outlined text-[18px]">history</span>
                Export History
            </a>
        </div>
    </div>

    @include('components.page-help', [
        'title' => 'Export Laporan',
        'description' => 'Unduh data dan laporan dalam format Excel atau CSV untuk analisis offline.',
        'tips' => ['Pilih rentang tanggal yang diinginkan', 'Pilih jenis data yang akan diekspor', 'Gunakan filter untuk data spesifik', 'Cek history export sebelumnya']
    ])

    <!-- Export Configuration -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Format & Type -->
        <div class="space-y-6">
            <!-- Export Format -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
                <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">description</span>
                    Export Format
                </h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                           :class="selectedFormat === 'pdf' ? 'bg-primary/10 border-primary' : 'bg-slate-800/50 border-slate-700 hover:border-slate-600'">
                        <input type="radio" x-model="selectedFormat" value="pdf" class="hidden">
                        <div class="size-12 rounded-xl flex items-center justify-center"
                             :class="selectedFormat === 'pdf' ? 'bg-red-500/20' : 'bg-slate-700'">
                            <span class="material-symbols-outlined text-2xl" :class="selectedFormat === 'pdf' ? 'text-red-400' : 'text-slate-400'">picture_as_pdf</span>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-white block">PDF Document</span>
                            <span class="text-xs text-slate-400">Best for sharing & printing</span>
                        </div>
                        <span class="material-symbols-outlined text-primary" x-show="selectedFormat === 'pdf'">check_circle</span>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                           :class="selectedFormat === 'excel' ? 'bg-primary/10 border-primary' : 'bg-slate-800/50 border-slate-700 hover:border-slate-600'">
                        <input type="radio" x-model="selectedFormat" value="excel" class="hidden">
                        <div class="size-12 rounded-xl flex items-center justify-center"
                             :class="selectedFormat === 'excel' ? 'bg-green-500/20' : 'bg-slate-700'">
                            <span class="material-symbols-outlined text-2xl" :class="selectedFormat === 'excel' ? 'text-green-400' : 'text-slate-400'">table</span>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-white block">Excel Spreadsheet</span>
                            <span class="text-xs text-slate-400">For data analysis & formulas</span>
                        </div>
                        <span class="material-symbols-outlined text-primary" x-show="selectedFormat === 'excel'">check_circle</span>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                           :class="selectedFormat === 'csv' ? 'bg-primary/10 border-primary' : 'bg-slate-800/50 border-slate-700 hover:border-slate-600'">
                        <input type="radio" x-model="selectedFormat" value="csv" class="hidden">
                        <div class="size-12 rounded-xl flex items-center justify-center"
                             :class="selectedFormat === 'csv' ? 'bg-blue-500/20' : 'bg-slate-700'">
                            <span class="material-symbols-outlined text-2xl" :class="selectedFormat === 'csv' ? 'text-blue-400' : 'text-slate-400'">csv</span>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-white block">CSV File</span>
                            <span class="text-xs text-slate-400">Universal data format</span>
                        </div>
                        <span class="material-symbols-outlined text-primary" x-show="selectedFormat === 'csv'">check_circle</span>
                    </label>
                </div>
            </div>

            <!-- Report Type -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
                <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">folder</span>
                    Report Type
                </h3>
                <select x-model="reportType" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:ring-2 focus:ring-primary focus:border-primary mb-3">
                    <option value="overview">Overview Dashboard</option>
                    <option value="conversations">Conversation Analytics</option>
                    <option value="quality">Quality & Sentiment</option>
                    <option value="performance">AI Performance</option>
                    <option value="csat">CSAT & Feedback</option>
                    <option value="comparative">Comparative Analysis</option>
                    <option value="custom">Custom Report</option>
                </select>
                <p class="text-xs text-slate-400" x-text="{
                    'overview': 'Complete dashboard with all key metrics',
                    'conversations': 'Detailed conversation volume and patterns',
                    'quality': 'Quality metrics, sentiment analysis, and scores',
                    'performance': 'AI accuracy, response times, and efficiency',
                    'csat': 'Customer satisfaction scores and feedback',
                    'comparative': 'Period-over-period comparisons',
                    'custom': 'Build your own report with selected metrics'
                }[reportType]"></p>
            </div>
        </div>

        <!-- Middle Column: Date Range & Metrics -->
        <div class="space-y-6">
            <!-- Date Range Picker -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
                <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">calendar_today</span>
                    Date Range
                </h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all"
                           :class="dateRange === '7' ? 'bg-primary/10 border-primary' : 'border-slate-700 hover:border-slate-600'">
                        <input type="radio" x-model="dateRange" value="7" class="hidden">
                        <span class="text-sm text-white flex-1">Last 7 Days</span>
                        <span class="material-symbols-outlined text-primary" x-show="dateRange === '7'">check</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all"
                           :class="dateRange === '30' ? 'bg-primary/10 border-primary' : 'border-slate-700 hover:border-slate-600'">
                        <input type="radio" x-model="dateRange" value="30" class="hidden">
                        <span class="text-sm text-white flex-1">Last 30 Days</span>
                        <span class="material-symbols-outlined text-primary" x-show="dateRange === '30'">check</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all"
                           :class="dateRange === '90' ? 'bg-primary/10 border-primary' : 'border-slate-700 hover:border-slate-600'">
                        <input type="radio" x-model="dateRange" value="90" class="hidden">
                        <span class="text-sm text-white flex-1">Last 90 Days</span>
                        <span class="material-symbols-outlined text-primary" x-show="dateRange === '90'">check</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all"
                           :class="dateRange === 'custom' ? 'bg-primary/10 border-primary' : 'border-slate-700 hover:border-slate-600'">
                        <input type="radio" x-model="dateRange" value="custom" class="hidden">
                        <span class="text-sm text-white flex-1">Custom Range</span>
                        <span class="material-symbols-outlined text-primary" x-show="dateRange === 'custom'">check</span>
                    </label>
                </div>

                <!-- Custom Date Inputs -->
                <div x-show="dateRange === 'custom'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">Start Date</label>
                        <input type="date" x-model="customStart" 
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1">End Date</label>
                        <input type="date" x-model="customEnd" 
                               class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            <!-- Metrics Checklist -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">checklist</span>
                        Include Metrics
                    </h3>
                    <button @click="toggleAllMetrics()" class="text-xs text-primary hover:underline">
                        <span x-text="Object.values(includeMetrics).every(v => v) ? 'Uncheck All' : 'Check All'"></span>
                    </button>
                </div>
                <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 cursor-pointer transition-all">
                        <input type="checkbox" x-model="includeMetrics.conversations" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-primary focus:ring-primary">
                        <div class="flex-1">
                            <span class="text-sm text-white">Conversations</span>
                            <span class="text-xs text-slate-400 block">Volume, sources, patterns</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 cursor-pointer transition-all">
                        <input type="checkbox" x-model="includeMetrics.responseTime" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-primary focus:ring-primary">
                        <div class="flex-1">
                            <span class="text-sm text-white">Response Time</span>
                            <span class="text-xs text-slate-400 block">FRT, ART, resolution time</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 cursor-pointer transition-all">
                        <input type="checkbox" x-model="includeMetrics.csat" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-primary focus:ring-primary">
                        <div class="flex-1">
                            <span class="text-sm text-white">CSAT Scores</span>
                            <span class="text-xs text-slate-400 block">Ratings, NPS, feedback</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 cursor-pointer transition-all">
                        <input type="checkbox" x-model="includeMetrics.sentiment" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-primary focus:ring-primary">
                        <div class="flex-1">
                            <span class="text-sm text-white">Sentiment Analysis</span>
                            <span class="text-xs text-slate-400 block">Positive, negative, neutral</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 cursor-pointer transition-all">
                        <input type="checkbox" x-model="includeMetrics.aiPerformance" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-primary focus:ring-primary">
                        <div class="flex-1">
                            <span class="text-sm text-white">AI Performance</span>
                            <span class="text-xs text-slate-400 block">Accuracy, confidence, gaps</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 cursor-pointer transition-all">
                        <input type="checkbox" x-model="includeMetrics.escalations" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-primary focus:ring-primary">
                        <div class="flex-1">
                            <span class="text-sm text-white">Escalations</span>
                            <span class="text-xs text-slate-400 block">Bot to human handoffs</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 cursor-pointer transition-all">
                        <input type="checkbox" x-model="includeMetrics.contacts" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-primary focus:ring-primary">
                        <div class="flex-1">
                            <span class="text-sm text-white">Contacts</span>
                            <span class="text-xs text-slate-400 block">New, active, growth</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-800/50 cursor-pointer transition-all">
                        <input type="checkbox" x-model="includeMetrics.broadcasts" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-primary focus:ring-primary">
                        <div class="flex-1">
                            <span class="text-sm text-white">Broadcasts</span>
                            <span class="text-xs text-slate-400 block">Campaigns, delivery, opens</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Right Column: Scheduling & Actions -->
        <div class="space-y-6">
            <!-- Schedule Options -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                        Schedule Export
                    </h3>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="isScheduled" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-700 peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div x-show="isScheduled" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-slate-400 block mb-2">Frequency</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-all"
                                       :class="scheduleType === 'once' ? 'bg-primary/10 border-primary' : 'border-slate-700'">
                                    <input type="radio" x-model="scheduleType" value="once" class="hidden">
                                    <span class="text-sm text-white">One-time</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-all"
                                       :class="scheduleType === 'daily' ? 'bg-primary/10 border-primary' : 'border-slate-700'">
                                    <input type="radio" x-model="scheduleType" value="daily" class="hidden">
                                    <span class="text-sm text-white">Daily</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-all"
                                       :class="scheduleType === 'weekly' ? 'bg-primary/10 border-primary' : 'border-slate-700'">
                                    <input type="radio" x-model="scheduleType" value="weekly" class="hidden">
                                    <span class="text-sm text-white">Weekly</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-all"
                                       :class="scheduleType === 'monthly' ? 'bg-primary/10 border-primary' : 'border-slate-700'">
                                    <input type="radio" x-model="scheduleType" value="monthly" class="hidden">
                                    <span class="text-sm text-white">Monthly</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="scheduleType === 'weekly'">
                            <label class="text-xs text-slate-400 block mb-2">Day of Week</label>
                            <select x-model="scheduleDay" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs text-slate-400 block mb-2">Email Recipients</label>
                            <input type="email" placeholder="email@example.com" 
                                   class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:ring-2 focus:ring-primary">
                            <p class="text-xs text-slate-500 mt-1">Separate multiple emails with commas</p>
                        </div>
                    </div>
                </div>

                <div x-show="!isScheduled" class="text-center py-8 text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2">schedule</span>
                    <p class="text-sm">Enable scheduling to automate report delivery</p>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">preview</span>
                        Preview
                    </h3>
                    <button @click="showPreview = !showPreview" 
                            class="text-xs text-primary hover:underline flex items-center gap-1">
                        <span x-text="showPreview ? 'Hide' : 'Show'"></span>
                        <span class="material-symbols-outlined text-[16px]" x-text="showPreview ? 'expand_less' : 'expand_more'"></span>
                    </button>
                </div>

                <div x-show="showPreview" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="space-y-4">
                    <div class="bg-slate-900 rounded-xl p-4 border border-slate-800">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs text-slate-400 uppercase tracking-wider">Report Preview</span>
                            <span class="text-xs px-2 py-1 bg-primary/20 text-primary rounded" x-text="selectedFormat.toUpperCase()"></span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">Report Type:</span>
                                <span class="text-white capitalize" x-text="reportType.replace('-', ' ')"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">Date Range:</span>
                                <span class="text-white" x-text="dateRange === 'custom' ? (customStart + ' - ' + customEnd) : 'Last ' + dateRange + ' Days'"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">Metrics:</span>
                                <span class="text-white" x-text="Object.values(includeMetrics).filter(v => v).length + ' selected'"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-400">Scheduled:</span>
                                <span class="text-white" x-text="isScheduled ? scheduleType.charAt(0).toUpperCase() + scheduleType.slice(1) : 'No'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="!showPreview" class="text-center py-6 text-slate-500">
                    <span class="material-symbols-outlined text-3xl mb-2">description</span>
                    <p class="text-xs">Click "Show" to preview report details</p>
                </div>
            </div>

            <!-- Export Actions -->
            <div class="bg-gradient-to-br from-primary/20 to-purple-500/20 rounded-2xl p-6 border border-primary/30">
                <h3 class="font-semibold text-white mb-4">Ready to Export?</h3>
                <div class="space-y-3">
                    <button @click="" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary hover:bg-primary/80 text-white rounded-xl font-semibold transition-all">
                        <span class="material-symbols-outlined">download</span>
                        <span x-text="isScheduled ? 'Schedule Export' : 'Download Now'"></span>
                    </button>
                    <button @click="" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl font-medium transition-all">
                        <span class="material-symbols-outlined">save</span>
                        Save as Template
                    </button>
                </div>
                <p class="text-xs text-slate-400 mt-4 text-center">
                    <span x-show="!isScheduled">Estimated file size: ~2.5 MB</span>
                    <span x-show="isScheduled">Reports will be emailed to recipients</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Recent Exports -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-lg text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Recent Exports
            </h3>
            <a href="{{ route('reports.export.history') }}" class="text-sm text-primary hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-800/50 text-[10px] uppercase font-black text-slate-500 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Report Name</th>
                        <th class="px-6 py-4">Format</th>
                        <th class="px-6 py-4">Date Range</th>
                        <th class="px-6 py-4">Generated</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @php
                        $recentExports = $recentExports ?? [
                            ['name' => 'Monthly Overview - January', 'format' => 'pdf', 'range' => 'Jan 1-31, 2026', 'date' => '2026-02-01 09:30', 'status' => 'completed'],
                            ['name' => 'Q4 Performance Report', 'format' => 'excel', 'range' => 'Oct 1 - Dec 31, 2025', 'date' => '2026-01-15 14:22', 'status' => 'completed'],
                            ['name' => 'CSAT Analysis', 'format' => 'csv', 'range' => 'Jan 1-14, 2026', 'date' => '2026-01-14 16:45', 'status' => 'completed'],
                            ['name' => 'Weekly Quality Report', 'format' => 'pdf', 'range' => 'Jan 8-14, 2026', 'date' => '2026-01-14 23:00', 'status' => 'scheduled'],
                        ];
                    @endphp
                    @foreach($recentExports as $export)
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm text-white font-medium">{{ $export['name'] }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold uppercase rounded {{ 
                                $export['format'] === 'pdf' ? 'bg-red-500/10 text-red-400' : 
                                ($export['format'] === 'excel' ? 'bg-green-500/10 text-green-400' : 'bg-blue-500/10 text-blue-400') 
                            }}">
                                {{ strtoupper($export['format']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-slate-400">{{ $export['range'] }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($export['date'])->diffForHumans() }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 text-xs font-bold uppercase rounded-full {{ 
                                $export['status'] === 'completed' ? 'bg-green-500/10 text-green-400' : 
                                ($export['status'] === 'scheduled' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-slate-500/10 text-slate-400') 
                            }}">
                                {{ $export['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($export['status'] === 'completed')
                            <button class="p-2 hover:bg-slate-800 rounded-lg transition-colors text-primary">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                            </button>
                            @else
                            <button class="p-2 hover:bg-slate-800 rounded-lg transition-colors text-slate-500">
                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                            </button>
                            @endif
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
    // Set default custom dates
    document.addEventListener('alpine:init', () => {
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        Alpine.data('exportData', () => ({
            customStart: thirtyDaysAgo.toISOString().split('T')[0],
            customEnd: today.toISOString().split('T')[0]
        }));
    });
</script>
@endpush
