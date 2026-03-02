@extends('layouts.dark')

@section('title', 'Dashboard Real-time')

@section('content')
<div class="space-y-6" 
     x-data="realtimeDashboard()" 
     x-init="init()"
     @visibilitychange.window="handleVisibilityChange()">
    
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard Real-time</h1>
            <p class="text-slate-400 text-sm">Pantau aktivitas live dan performa sistem secara real-time</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Auto-refresh Indicator -->
            <div class="flex items-center gap-2 px-4 py-2 bg-surface-dark border border-border-dark rounded-xl">
                <span class="material-symbols-outlined text-text-secondary text-[18px]">refresh</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-text-secondary">Refresh dalam</span>
                    <span class="text-xs font-bold text-primary w-6 text-center" x-text="refreshCountdown">30</span>
                    <span class="text-xs text-text-secondary">detik</span>
                </div>
                <!-- Progress bar -->
                <div class="w-16 h-1 bg-slate-700 rounded-full overflow-hidden ml-1">
                    <div class="h-full bg-primary rounded-full transition-all duration-1000 ease-linear"
                         :style="`width: ${(refreshCountdown / 30) * 100}%`"></div>
                </div>
            </div>
            
            <!-- WebSocket Status -->
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl border"
                 :class="{
                     'bg-green-500/10 border-green-500/30 text-green-400': wsStatus === 'connected',
                     'bg-yellow-500/10 border-yellow-500/30 text-yellow-400': wsStatus === 'connecting',
                     'bg-red-500/10 border-red-500/30 text-red-400': wsStatus === 'disconnected'
                 }">
                <div class="size-2 rounded-full animate-pulse"
                     :class="{
                         'bg-green-400': wsStatus === 'connected',
                         'bg-yellow-400': wsStatus === 'connecting',
                         'bg-red-400': wsStatus === 'disconnected'
                     }"></div>
                <span class="text-xs font-medium capitalize" x-text="wsStatus"></span>
            </div>

            <!-- Refresh Button -->
            <button @click="refreshData()" 
                    :disabled="isRefreshing"
                    class="p-2.5 bg-surface-dark border border-border-dark text-text-secondary hover:text-white rounded-xl transition-colors"
                    :class="isRefreshing ? 'animate-spin' : ''">
                <span class="material-symbols-outlined text-[20px]">sync</span>
            </button>
        </div>
    </div>

    @include('components.page-help', [
        'title' => 'Realtime Dashboard',
        'description' => 'Pantau aktivitas chat secara real-time.',
        'tips' => ['Lihat pesan masuk secara live', 'Monitor status bot (aktif/offline)', 'Pantau jumlah percakapan aktif', 'Deteksi anomaly atau spike traffic']
    ])

    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Conversations -->
        <div class="bg-surface-dark rounded-xl p-5 border border-border-dark relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-blue-500/10 transition-all"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="size-10 rounded-lg bg-blue-500/10 flex items-center justify-center relative">
                            <span class="material-symbols-outlined text-blue-400">chat</span>
                            <!-- Pulse Animation -->
                            <span class="absolute -top-0.5 -right-0.5 size-3 bg-blue-400 rounded-full animate-ping"></span>
                            <span class="absolute -top-0.5 -right-0.5 size-3 bg-blue-400 rounded-full"></span>
                        </div>
                        <span class="text-sm font-medium text-text-secondary">Percakapan Aktif</span>
                    </div>
                </div>
                <div class="flex items-end gap-3">
                    <span class="text-3xl font-black text-white" x-text="stats.activeConversations">0</span>
                    <span class="text-xs text-green-400 flex items-center gap-0.5 mb-1">
                        <span class="material-symbols-outlined text-[14px]">trending_up</span>
                        <span x-text="'+' + stats.conversationChange">+0</span>
                    </span>
                </div>
                <p class="text-xs text-text-secondary mt-2">Sedang berlangsung sekarang</p>
            </div>
        </div>

        <!-- Queue Length -->
        <div class="bg-surface-dark rounded-xl p-5 border border-border-dark relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-orange-500/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-orange-500/10 transition-all"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="size-10 rounded-lg bg-orange-500/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-orange-400">queue</span>
                        </div>
                        <span class="text-sm font-medium text-text-secondary">Antrian Menunggu</span>
                    </div>
                </div>
                <div class="flex items-end gap-3">
                    <span class="text-3xl font-black text-white" x-text="stats.queueLength">0</span>
                    <span class="text-xs text-slate-400 mb-1">pelanggan</span>
                </div>
                <p class="text-xs text-text-secondary mt-2">
                    Estimasi tunggu: <span class="text-orange-400 font-medium" x-text="stats.estimatedWaitTime">0</span> menit
                </p>
            </div>
        </div>

        <!-- Online Agents -->
        <div class="bg-surface-dark rounded-xl p-5 border border-border-dark relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-green-500/10 transition-all"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="size-10 rounded-lg bg-green-500/10 flex items-center justify-center relative">
                            <span class="material-symbols-outlined text-green-400">support_agent</span>
                            <span class="absolute -bottom-0.5 -right-0.5 size-2.5 bg-green-400 rounded-full border-2 border-surface-dark"></span>
                        </div>
                        <span class="text-sm font-medium text-text-secondary">Agent Online</span>
                    </div>
                </div>
                <div class="flex items-end gap-3">
                    <span class="text-3xl font-black text-white" x-text="stats.onlineAgents">0</span>
                    <span class="text-xs text-slate-400 mb-1">dari <span x-text="stats.totalAgents">0</span></span>
                </div>
                <div class="flex gap-2 mt-2">
                    <span class="text-[10px] px-2 py-0.5 bg-green-500/10 text-green-400 rounded-full flex items-center gap-1">
                        <span class="size-1 bg-green-400 rounded-full"></span>
                        <span x-text="stats.availableAgents">0</span> tersedia
                    </span>
                </div>
            </div>
        </div>

        <!-- Avg Response Time -->
        <div class="bg-surface-dark rounded-xl p-5 border border-border-dark relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-purple-500/10 transition-all"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="size-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-purple-400">timer</span>
                        </div>
                        <span class="text-sm font-medium text-text-secondary">Rata-rata Respon</span>
                    </div>
                </div>
                <div class="flex items-end gap-3">
                    <span class="text-3xl font-black text-white" x-text="stats.avgResponseTime">0</span>
                    <span class="text-xs text-slate-400 mb-1">detik</span>
                </div>
                <p class="text-xs mt-2" 
                   :class="stats.avgResponseTime < 30 ? 'text-green-400' : (stats.avgResponseTime < 60 ? 'text-yellow-400' : 'text-red-400')"
                   x-text="stats.avgResponseTime < 30 ? 'Sangat baik' : (stats.avgResponseTime < 60 ? 'Cukup baik' : 'Perlu perhatian')">
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Live Activity Feed -->
        <div class="lg:col-span-2 bg-surface-dark rounded-xl border border-border-dark overflow-hidden">
            <div class="px-5 py-4 border-b border-border-dark flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">rss_feed</span>
                    <h3 class="font-semibold text-white">Aktivitas Live</h3>
                </div>
                <span class="text-xs text-text-secondary" x-text="activities.length + ' aktivitas'"></span>
            </div>
            <div class="max-h-[400px] overflow-y-auto p-4 space-y-3" id="activity-feed">
                <template x-for="(activity, index) in activities" :key="activity.id">
                    <div class="flex gap-3 p-3 rounded-xl hover:bg-background-dark/50 transition-colors animate-fade-in"
                         :class="index === 0 ? 'bg-primary/5 border border-primary/20' : ''">
                        <!-- Icon -->
                        <div class="size-10 rounded-lg flex items-center justify-center shrink-0"
                             :class="{
                                 'bg-blue-500/10 text-blue-400': activity.type === 'message',
                                 'bg-green-500/10 text-green-400': activity.type === 'resolved',
                                 'bg-purple-500/10 text-purple-400': activity.type === 'assigned',
                                 'bg-orange-500/10 text-orange-400': activity.type === 'waiting',
                                 'bg-red-500/10 text-red-400': activity.type === 'escalated'
                             }">
                            <span class="material-symbols-outlined text-lg" x-text="getActivityIcon(activity.type)"></span>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm text-white">
                                    <span class="font-medium" x-text="activity.user"></span>
                                    <span class="text-text-secondary" x-text="activity.action"></span>
                                </p>
                                <span class="text-[10px] text-text-secondary whitespace-nowrap" x-text="activity.time"></span>
                            </div>
                            <p class="text-xs text-text-secondary mt-0.5 truncate" x-text="activity.detail"></p>
                        </div>
                        
                        <!-- Platform Indicator -->
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="material-symbols-outlined text-base"
                                  :class="activity.platform === 'whatsapp' ? 'text-green-400' : 'text-pink-400'"
                                  x-text="activity.platform === 'whatsapp' ? 'chat' : 'photo_camera'"></span>
                        </div>
                    </div>
                </template>
                
                <!-- Empty State -->
                <div x-show="activities.length === 0" class="py-12 text-center">
                    <span class="material-symbols-outlined text-4xl text-text-secondary mb-2">rss_feed</span>
                    <p class="text-sm text-text-secondary">Belum ada aktivitas</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Sentiment & Agents -->
        <div class="space-y-6">
            <!-- Sentiment Gauge -->
            <div class="bg-surface-dark rounded-xl border border-border-dark p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">mood</span>
                        <h3 class="font-semibold text-white">Sentimen Pelanggan</h3>
                    </div>
                </div>
                
                <!-- SVG Semi-circle Gauge -->
                <div class="relative h-40 flex items-end justify-center mb-4">
                    <svg class="w-full h-full" viewBox="0 0 200 100">
                        <!-- Background arc -->
                        <path d="M 20 100 A 80 80 0 0 1 180 100" 
                              fill="none" 
                              stroke="#1e293b" 
                              stroke-width="20"
                              stroke-linecap="round"/>
                        
                        <!-- Colored segments -->
                        <!-- Negative (Red) -->
                        <path d="M 20 100 A 80 80 0 0 1 60 30.7" 
                              fill="none" 
                              stroke="#ef4444" 
                              stroke-width="20"
                              stroke-linecap="round"
                              opacity="0.3"/>
                        <!-- Neutral (Yellow) -->
                        <path d="M 60 30.7 A 80 80 0 0 1 140 30.7" 
                              fill="none" 
                              stroke="#eab308" 
                              stroke-width="20"
                              stroke-linecap="round"
                              opacity="0.3"/>
                        <!-- Positive (Green) -->
                        <path d="M 140 30.7 A 80 80 0 0 1 180 100" 
                              fill="none" 
                              stroke="#22c55e" 
                              stroke-width="20"
                              stroke-linecap="round"
                              opacity="0.3"/>
                        
                        <!-- Active indicator -->
                        <g :transform="`rotate(${sentimentGaugeAngle}, 100, 100)`">
                            <circle cx="100" cy="20" r="8" fill="#135bec" stroke="white" stroke-width="2"/>
                        </g>
                    </svg>
                    
                    <!-- Center text -->
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 text-center">
                        <span class="text-3xl font-black" 
                              :class="{
                                  'text-green-400': sentimentScore >= 70,
                                  'text-yellow-400': sentimentScore >= 40 && sentimentScore < 70,
                                  'text-red-400': sentimentScore < 40
                              }"
                              x-text="sentimentScore + '%'">0%</span>
                        <p class="text-xs text-text-secondary mt-1" x-text="sentimentLabel">Netral</p>
                    </div>
                </div>
                
                <!-- Breakdown -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="text-center p-3 bg-green-500/5 rounded-lg border border-green-500/20">
                        <span class="text-lg font-bold text-green-400" x-text="sentimentBreakdown.positive + '%'">0%</span>
                        <p class="text-[10px] text-text-secondary mt-1">Positif</p>
                    </div>
                    <div class="text-center p-3 bg-yellow-500/5 rounded-lg border border-yellow-500/20">
                        <span class="text-lg font-bold text-yellow-400" x-text="sentimentBreakdown.neutral + '%'">0%</span>
                        <p class="text-[10px] text-text-secondary mt-1">Netral</p>
                    </div>
                    <div class="text-center p-3 bg-red-500/5 rounded-lg border border-red-500/20">
                        <span class="text-lg font-bold text-red-400" x-text="sentimentBreakdown.negative + '%'">0%</span>
                        <p class="text-[10px] text-text-secondary mt-1">Negatif</p>
                    </div>
                </div>
            </div>

            <!-- Agent Status Grid -->
            <div class="bg-surface-dark rounded-xl border border-border-dark p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">people</span>
                        <h3 class="font-semibold text-white">Status Agent</h3>
                    </div>
                    <a href="#" class="text-xs text-primary hover:underline">Lihat semua</a>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <template x-for="agent in agents.slice(0, 6)" :key="agent.id">
                        <div class="p-3 bg-background-dark rounded-lg border border-border-dark hover:border-primary/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <!-- Avatar -->
                                <div class="relative">
                                    <div class="size-10 rounded-full bg-gradient-to-br from-primary/30 to-purple-500/30 flex items-center justify-center text-sm font-bold text-white"
                                         x-text="agent.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()">
                                    </div>
                                    <!-- Status Dot -->
                                    <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-background-dark"
                                          :class="{
                                              'bg-green-400': agent.status === 'online',
                                              'bg-yellow-400': agent.status === 'busy',
                                              'bg-slate-500': agent.status === 'offline'
                                          }"></span>
                                </div>
                                
                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate" x-text="agent.name"></p>
                                    <p class="text-[10px] text-text-secondary capitalize" x-text="agent.status"></p>
                                </div>
                                
                                <!-- Conversation Count -->
                                <div class="text-right">
                                    <span class="text-sm font-bold text-primary" x-text="agent.conversations">0</span>
                                    <p class="text-[10px] text-text-secondary">chat</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Legend -->
                <div class="flex items-center justify-center gap-4 mt-4 pt-4 border-t border-border-dark">
                    <div class="flex items-center gap-1.5">
                        <span class="size-2 bg-green-400 rounded-full"></span>
                        <span class="text-[10px] text-text-secondary">Online</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="size-2 bg-yellow-400 rounded-full"></span>
                        <span class="text-[10px] text-text-secondary">Sibuk</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="size-2 bg-slate-500 rounded-full"></span>
                        <span class="text-[10px] text-text-secondary">Offline</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function realtimeDashboard() {
    return {
        refreshInterval: null,
        refreshCountdown: 30,
        isRefreshing: false,
        wsStatus: 'connected', // connected, connecting, disconnected
        stats: {
            activeConversations: 0,
            conversationChange: 0,
            queueLength: 0,
            estimatedWaitTime: 0,
            onlineAgents: 0,
            totalAgents: 0,
            availableAgents: 0,
            avgResponseTime: 0
        },
        sentimentScore: 0,
        sentimentBreakdown: {
            positive: 0,
            neutral: 0,
            negative: 0
        },
        agents: [],
        activities: [],

        get sentimentGaugeAngle() {
            // Map 0-100 score to -90 to 90 degrees
            return (this.sentimentScore / 100) * 180 - 90;
        },

        get sentimentLabel() {
            if (this.sentimentScore >= 70) return 'Positif';
            if (this.sentimentScore >= 40) return 'Netral';
            return 'Negatif';
        },

        init() {
            this.startRefreshTimer();
            this.simulateWebSocket();
            
            // Simulate random updates
            setInterval(() => {
                this.simulateRandomUpdate();
            }, 5000);
        },

        startRefreshTimer() {
            this.refreshInterval = setInterval(() => {
                this.refreshCountdown--;
                if (this.refreshCountdown <= 0) {
                    this.refreshData();
                    this.refreshCountdown = 30;
                }
            }, 1000);
        },

        handleVisibilityChange() {
            if (document.hidden) {
                clearInterval(this.refreshInterval);
            } else {
                this.startRefreshTimer();
            }
        },

        refreshData() {
            this.isRefreshing = true;
            
            // Simulate API call
            setTimeout(() => {
                // Randomize stats slightly
                this.stats.activeConversations = Math.max(0, this.stats.activeConversations + Math.floor(Math.random() * 5) - 2);
                this.stats.queueLength = Math.max(0, this.stats.queueLength + Math.floor(Math.random() * 3) - 1);
                this.stats.avgResponseTime = Math.max(5, Math.min(120, this.stats.avgResponseTime + Math.floor(Math.random() * 10) - 5));
                
                this.isRefreshing = false;
            }, 800);
        },

        simulateWebSocket() {
            // Simulate WebSocket connection status changes
            setInterval(() => {
                const rand = Math.random();
                if (rand > 0.95) {
                    this.wsStatus = 'disconnected';
                    setTimeout(() => {
                        this.wsStatus = 'connecting';
                        setTimeout(() => {
                            this.wsStatus = 'connected';
                        }, 1500);
                    }, 2000);
                }
            }, 30000);
        },

        simulateRandomUpdate() {
            // Data simulation disabled - only fetch from server
            // This function is kept for compatibility but does not generate dummy data
            return;
        },

        getActivityIcon(type) {
            const icons = {
                message: 'chat',
                resolved: 'check_circle',
                assigned: 'person_add',
                waiting: 'schedule',
                escalated: 'trending_up'
            };
            return icons[type] || 'info';
        },

        showToast(message, type = 'success') {
            if (window.showToast) {
                window.showToast(message, type);
            }
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
@endpush
