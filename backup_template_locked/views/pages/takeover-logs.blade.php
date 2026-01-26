<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Takeover Activity Logs - REPLYAI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "whatsapp": "#25D366",
                        "background-light": "#f6f6f8",
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
</head>
<body class="bg-background-dark font-display text-white overflow-hidden h-screen flex">

<!-- Sidebar -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden" x-data="takeoverLogs()" x-init="fetchLogs()">
    <!-- Header -->
    <header class="h-16 border-b border-white/5 flex items-center justify-between px-6 bg-[#111722] shrink-0">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-3xl">history</span>
            <div>
                <h2 class="text-lg font-bold text-white">Takeover Activity Logs</h2>
                <p class="text-xs text-text-secondary">Riwayat aktivitas ambil alih dan kembalikan ke bot</p>
            </div>
        </div>
        <button @click="fetchLogs()" class="flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-sm transition-colors">
            <span class="material-symbols-outlined text-base">refresh</span>
            Refresh
        </button>
    </header>

    <!-- Filters -->
    <div class="p-6 pb-0 flex flex-wrap gap-4 items-center">
        <!-- Platform Filter -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-text-secondary">Platform:</label>
            <select x-model="filters.platform" @change="fetchLogs()" 
                    class="bg-surface-dark border border-border-dark text-white text-sm rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                <option value="all">Semua</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="instagram">Instagram</option>
            </select>
        </div>
        
        <!-- Action Filter -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-text-secondary">Aksi:</label>
            <select x-model="filters.action" @change="fetchLogs()"
                    class="bg-surface-dark border border-border-dark text-white text-sm rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                <option value="all">Semua</option>
                <option value="takeover">Ambil Alih</option>
                <option value="handback">Kembalikan ke Bot</option>
                <option value="auto_handback">Auto Handback</option>
                <option value="cs_reply">CS Reply</option>
            </select>
        </div>

        <!-- Stats -->
        <div class="ml-auto flex items-center gap-4 text-sm">
            <span class="text-text-secondary">Total: <span class="text-white font-semibold" x-text="logs.length"></span></span>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="flex-1 overflow-auto p-6">
        <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="text-left text-xs font-semibold text-text-secondary px-4 py-3">Waktu</th>
                        <th class="text-left text-xs font-semibold text-text-secondary px-4 py-3">Platform</th>
                        <th class="text-left text-xs font-semibold text-text-secondary px-4 py-3">Customer</th>
                        <th class="text-left text-xs font-semibold text-text-secondary px-4 py-3">Aksi</th>
                        <th class="text-left text-xs font-semibold text-text-secondary px-4 py-3">CS</th>
                        <th class="text-left text-xs font-semibold text-text-secondary px-4 py-3">Idle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-text-secondary">
                                <span class="material-symbols-outlined animate-spin">sync</span>
                                <p class="mt-2">Memuat data...</p>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && logs.length === 0">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-text-secondary">
                                <span class="material-symbols-outlined text-4xl mb-2">inbox</span>
                                <p>Belum ada aktivitas takeover</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="log in logs" :key="log.id">
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-3">
                                <div class="text-sm text-white" x-text="log.created_at"></div>
                                <div class="text-xs text-text-secondary" x-text="log.time_ago"></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                      :class="log.platform === 'WhatsApp' ? 'bg-green-500/20 text-green-400' : 'bg-pink-500/20 text-pink-400'"
                                      x-text="log.platform"></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-white" x-text="log.customer_name"></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium"
                                      :class="{
                                          'bg-amber-500/20 text-amber-400': log.action === 'Ambil Alih',
                                          'bg-green-500/20 text-green-400': log.action === 'Kembalikan ke Bot',
                                          'bg-blue-500/20 text-blue-400': log.action === 'Auto Handback',
                                          'bg-purple-500/20 text-purple-400': log.action === 'CS Balas'
                                      }"
                                      x-text="log.action"></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-white" x-text="log.actor"></td>
                            <td class="px-4 py-3 text-sm text-text-secondary" x-text="log.idle_duration"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    function takeoverLogs() {
        return {
            logs: [],
            loading: false,
            filters: {
                platform: 'all',
                action: 'all',
            },

            async fetchLogs() {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    if (this.filters.platform !== 'all') params.append('platform', this.filters.platform);
                    if (this.filters.action !== 'all') params.append('action', this.filters.action);

                    const response = await fetch(`{{ route('takeover.logs.data') }}?${params}`);
                    this.logs = await response.json();
                } catch (error) {
                    console.error('Error fetching logs:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>

</body>
</html>
