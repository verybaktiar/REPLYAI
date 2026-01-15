<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>WhatsApp Analytics - REPLYAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "whatsapp": "#25D366",
                        "background-dark": "#111722",
                        "surface-dark": "#192233",
                        "border-dark": "#324467",
                        "text-secondary": "#92a4c9",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        /* Custom scrollbar for dark theme */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #111722; }
        ::-webkit-scrollbar-thumb { background: #324467; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #445577; }
    </style>
</head>
<body class="bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row">
    
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto pt-14 lg:pt-0">
        <div class="p-4 md:p-8">
            <h1 class="text-2xl font-bold mb-8">WhatsApp Analytics</h1>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Messages -->
                <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-text-secondary text-sm font-medium">Total Messages</p>
                            <h3 class="text-2xl font-bold mt-1 text-white">{{ number_format($summary['totalMessages']) }}</h3>
                        </div>
                        <div class="p-2 bg-blue-500/10 text-blue-400 rounded-lg">
                            <span class="material-symbols-outlined">chat</span>
                        </div>
                    </div>
                </div>

                <!-- Total Contacts -->
                <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-text-secondary text-sm font-medium">Active Contacts</p>
                            <h3 class="text-2xl font-bold mt-1 text-white">{{ number_format($summary['totalContacts']) }}</h3>
                        </div>
                        <div class="p-2 bg-green-500/10 text-green-400 rounded-lg">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                    </div>
                </div>

                <!-- Broadcast Campaigns -->
                <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-text-secondary text-sm font-medium">Broadcasts Sent</p>
                            <h3 class="text-2xl font-bold mt-1 text-white">{{ number_format($summary['totalBroadcasts']) }}</h3>
                        </div>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-lg">
                            <span class="material-symbols-outlined">campaign</span>
                        </div>
                    </div>
                </div>

                <!-- Messages Delivered -->
                <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-text-secondary text-sm font-medium">Broadcast Msgs</p>
                            <h3 class="text-2xl font-bold mt-1 text-white">{{ number_format($summary['broadcastsSent']) }}</h3>
                        </div>
                        <div class="p-2 bg-orange-500/10 text-orange-400 rounded-lg">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Line Chart: Daily Activity -->
                <div class="lg:col-span-2 bg-surface-dark border border-border-dark p-6 rounded-xl">
                    <h3 class="font-bold text-white mb-6">Activity - Last 7 Days</h3>
                    <div id="activityChart"></div>
                </div>

                <!-- Pie Chart: Distribution -->
                <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                    <h3 class="font-bold text-white mb-6">Message Distribution</h3>
                    <div id="distributionChart" class="flex justify-center"></div>
                </div>
            </div>

            <!-- Top Contacts Table -->
            <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden">
                <div class="p-6 border-b border-border-dark flex justify-between items-center">
                    <h3 class="font-bold text-white">Top Active Users</h3>
                    <a href="{{ route('whatsapp.inbox') }}" class="text-primary text-sm hover:underline">View All in Inbox</a>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-white/5 text-text-secondary text-xs font-semibold uppercase">
                        <tr>
                            <th class="px-6 py-4">Contact</th>
                            <th class="px-6 py-4">Phone Number</th>
                            <th class="px-6 py-4 text-center">Total Messages</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark">
                        @forelse($topContacts as $contact)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-white font-medium">
                                {{ $contact->push_name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-gray-400 font-mono">
                                {{ $contact->phone_number }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-white/10 px-3 py-1 rounded-full text-xs font-bold text-white">
                                    {{ $contact->total }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('whatsapp.api.messages', $contact->phone_number) }}" class="text-text-secondary hover:text-white">
                                    <span class="material-symbols-outlined">history</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-text-secondary">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <script>
        // --- Activity Chart (Line) ---
        const activityOptions = {
            series: [{
                name: 'Incoming (Users)',
                data: @json($chartData['incoming'])
            }, {
                name: 'Outgoing (Bot/Admin)',
                data: @json($chartData['outgoing'])
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                background: 'transparent'
            },
            colors: ['#25D366', '#135bec'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: @json($chartData['dates']),
                labels: { style: { colors: '#92a4c9' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#92a4c9' } }
            },
            grid: {
                borderColor: '#324467',
                strokeDashArray: 4,
            },
            theme: { mode: 'dark' },
            legend: {
                labels: { colors: '#fff' }
            }
        };
        new ApexCharts(document.querySelector("#activityChart"), activityOptions).render();

        // --- Distribution Chart (Donut) ---
        const distOptions = {
            series: [{{ $distribution['user'] }}, {{ $distribution['bot'] }}, {{ $distribution['manual'] }}],
            labels: ['User Incoming', 'Bot Replies', 'Admin Replies'],
            chart: {
                type: 'donut',
                height: 350,
                background: 'transparent'
            },
            colors: ['#25D366', '#3b82f6', '#f59e0b'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                color: '#fff'
                            }
                        }
                    }
                }
            },
            stroke: { show: false },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                labels: { colors: '#fff' }
            }
        };
        new ApexCharts(document.querySelector("#distributionChart"), distOptions).render();
    </script>
</body>
</html>
