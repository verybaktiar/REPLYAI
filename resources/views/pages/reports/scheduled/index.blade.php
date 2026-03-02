@extends('layouts.dark')

@section('title', 'Laporan Terjadwal')

@section('content')
<div class="space-y-6" x-data="scheduledReportsApp()">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Laporan Terjadwal</h1>
            <p class="text-slate-400 text-sm">Kelola laporan otomatis yang dikirim ke email Anda</p>
        </div>
        <button @click="openModal()" 
                class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/80 text-white rounded-xl font-semibold transition-all shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Buat Laporan
        </button>
    </div>

    @include('components.page-help', [
        'title' => 'Jadwal Laporan',
        'description' => 'Atur pengiriman laporan otomatis ke email secara berkala.',
        'tips' => ['Buat jadwal laporan harian/mingguan/bulanan', 'Pilih penerima email laporan', 'Pilih metrik yang ingin dilaporkan', 'Edit atau hapus jadwal yang sudah ada']
    ])

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-surface-dark rounded-xl p-4 border border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">schedule</span>
                </div>
                <div>
                    <p class="text-2xl font-black text-white" x-text="stats.total">0</p>
                    <p class="text-xs text-text-secondary">Total Laporan</p>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl p-4 border border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-400">play_circle</span>
                </div>
                <div>
                    <p class="text-2xl font-black text-white" x-text="stats.active">0</p>
                    <p class="text-xs text-text-secondary">Aktif</p>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl p-4 border border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-orange-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-orange-400">pause_circle</span>
                </div>
                <div>
                    <p class="text-2xl font-black text-white" x-text="stats.inactive">0</p>
                    <p class="text-xs text-text-secondary">Nonaktif</p>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl p-4 border border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-400">send</span>
                </div>
                <div>
                    <p class="text-2xl font-black text-white" x-text="stats.sent">0</p>
                    <p class="text-xs text-text-secondary">Terkirim</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <button @click="filter = 'all'" 
                :class="filter === 'all' ? 'bg-primary text-white' : 'bg-surface-dark text-text-secondary hover:text-white'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
            Semua
        </button>
        <button @click="filter = 'active'" 
                :class="filter === 'active' ? 'bg-green-500 text-white' : 'bg-surface-dark text-text-secondary hover:text-white'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">play_circle</span>
            Aktif
        </button>
        <button @click="filter = 'inactive'" 
                :class="filter === 'inactive' ? 'bg-orange-500 text-white' : 'bg-surface-dark text-text-secondary hover:text-white'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px]">pause_circle</span>
            Nonaktif
        </button>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <template x-for="report in filteredReports" :key="report.id">
            <div class="scheduled-card bg-surface-dark rounded-xl border border-border-dark overflow-hidden hover:border-primary/30 transition-all group">
                <div class="p-5">
                    <!-- Card Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-xl flex items-center justify-center"
                                 :class="{
                                     'bg-blue-500/10 text-blue-400': report.report_type === 'analytics',
                                     'bg-green-500/10 text-green-400': report.report_type === 'performance',
                                     'bg-purple-500/10 text-purple-400': report.report_type === 'csat',
                                     'bg-orange-500/10 text-orange-400': report.report_type === 'agents'
                                 }">
                                <span class="material-symbols-outlined text-2xl" x-text="getReportIcon(report.report_type)"></span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white" x-text="report.name"></h3>
                                <span class="text-xs text-text-secondary capitalize" x-text="formatReportType(report.report_type)"></span>
                            </div>
                        </div>
                        
                        <!-- Status Toggle -->
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="sr-only peer" 
                                   :checked="report.is_active"
                                   @change="toggleStatus(report.id, $event.target.checked)">
                            <div class="w-10 h-5 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <!-- Schedule Info -->
                    <div class="bg-background-dark rounded-lg p-3 mb-4 space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-[16px] text-text-secondary">event_repeat</span>
                            <span class="text-slate-300 capitalize" x-text="report.frequency"></span>
                            <span x-show="report.frequency !== 'daily'" class="text-slate-400 text-xs">
                                • <span x-text="getFrequencyDetail(report)"></span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-[16px] text-text-secondary">schedule</span>
                            <span class="text-slate-300" x-text="report.time"></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-[16px] text-text-secondary">email</span>
                            <span class="text-slate-300 truncate" x-text="report.email"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded"
                                  :class="{
                                      'bg-red-500/10 text-red-400': report.format === 'pdf',
                                      'bg-green-500/10 text-green-400': report.format === 'excel',
                                      'bg-blue-500/10 text-blue-400': report.format === 'csv'
                                  }"
                                  x-text="report.format.toUpperCase()"></span>
                        </div>
                    </div>

                    <!-- Next Run & Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-border-dark">
                        <div class="flex items-center gap-2 text-xs text-text-secondary">
                            <span class="material-symbols-outlined text-[16px]" :class="report.is_active ? 'text-green-400' : 'text-slate-500'">rocket_launch</span>
                            <span x-show="report.is_active">Next: <span class="text-slate-300" x-text="report.next_run"></span></span>
                            <span x-show="!report.is_active" class="text-slate-500">Paused</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="sendNow(report)" 
                                    :disabled="sendNowLoading === report.id"
                                    class="p-2 rounded-lg hover:bg-green-500/10 text-text-secondary hover:text-green-400 transition-colors"
                                    title="Kirim sekarang">
                                <span class="material-symbols-outlined text-[18px]" :class="sendNowLoading === report.id ? 'animate-spin' : ''" x-text="sendNowLoading === report.id ? 'sync' : 'send'"></span>
                            </button>
                            <button @click="editReport(report)" 
                                    class="p-2 rounded-lg hover:bg-surface-lighter text-text-secondary hover:text-white transition-colors"
                                    title="Edit">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                            <button @click="deleteReport(report)" 
                                    class="p-2 rounded-lg hover:bg-red-500/10 text-text-secondary hover:text-red-400 transition-colors"
                                    title="Hapus">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
        <!-- Empty State -->
        <div x-show="filteredReports.length === 0" class="col-span-full">
            <x-empty-state 
                icon="schedule_send" 
                title="Belum ada laporan terjadwal" 
                description="Buat laporan terjadwal untuk mendapatkan insight secara otomatis via email."
            />
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="modal.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="closeModal()" 
             class="modal-content-mobile w-full max-w-lg rounded-2xl bg-surface-dark border border-border-dark shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                <h3 class="text-xl font-bold text-white" x-text="modal.isEdit ? 'Edit Laporan' : 'Buat Laporan'"></h3>
                <button @click="closeModal()" class="p-2 rounded-lg hover:bg-surface-lighter text-text-secondary hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Nama Laporan</label>
                    <input type="text" 
                           x-model="modal.form.name"
                           class="w-full px-4 py-2.5 bg-background-dark border border-border-dark rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                           placeholder="Contoh: Laporan Mingguan">
                </div>

                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Jenis Laporan</label>
                    <select x-model="modal.form.report_type"
                            class="w-full px-4 py-2.5 bg-background-dark border border-border-dark rounded-lg text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                        <option value="analytics">Analytics Overview</option>
                        <option value="performance">Performance Report</option>
                        <option value="csat">CSAT Summary</option>
                        <option value="agents">Agent Performance</option>
                        <option value="conversations">Conversation Analysis</option>
                    </select>
                </div>

                <!-- Frequency -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Frekuensi</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button @click="modal.form.frequency = 'daily'"
                                :class="modal.form.frequency === 'daily' ? 'bg-primary text-white border-primary' : 'bg-background-dark text-text-secondary border-border-dark hover:text-white'"
                                class="px-4 py-2.5 rounded-lg border text-sm font-medium transition-colors">
                            Harian
                        </button>
                        <button @click="modal.form.frequency = 'weekly'"
                                :class="modal.form.frequency === 'weekly' ? 'bg-primary text-white border-primary' : 'bg-background-dark text-text-secondary border-border-dark hover:text-white'"
                                class="px-4 py-2.5 rounded-lg border text-sm font-medium transition-colors">
                            Mingguan
                        </button>
                        <button @click="modal.form.frequency = 'monthly'"
                                :class="modal.form.frequency === 'monthly' ? 'bg-primary text-white border-primary' : 'bg-background-dark text-text-secondary border-border-dark hover:text-white'"
                                class="px-4 py-2.5 rounded-lg border text-sm font-medium transition-colors">
                            Bulanan
                        </button>
                    </div>
                </div>

                <!-- Day Selection (for weekly/monthly) -->
                <div x-show="modal.form.frequency !== 'daily'">
                    <label class="block text-sm font-medium text-text-secondary mb-2" x-text="modal.form.frequency === 'weekly' ? 'Hari' : 'Tanggal'"></label>
                    <!-- Weekly Day Selector -->
                    <div x-show="modal.form.frequency === 'weekly'" class="grid grid-cols-7 gap-2">
                        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']">
                            <button @click="modal.form.day = day"
                                    :class="modal.form.day === day ? 'bg-primary text-white' : 'bg-background-dark text-text-secondary hover:text-white'"
                                    class="py-2 rounded-lg text-xs font-medium transition-colors" x-text="day"></button>
                        </template>
                    </div>
                    <!-- Monthly Date Selector -->
                    <select x-show="modal.form.frequency === 'monthly'" 
                            x-model="modal.form.day"
                            class="w-full px-4 py-2.5 bg-background-dark border border-border-dark rounded-lg text-white focus:outline-none focus:border-primary">
                        <template x-for="date in 31">
                            <option :value="date" x-text="date"></option>
                        </template>
                    </select>
                </div>

                <!-- Time -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Waktu</label>
                    <input type="time" 
                           x-model="modal.form.time"
                           class="w-full px-4 py-2.5 bg-background-dark border border-border-dark rounded-lg text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Email Tujuan</label>
                    <input type="email" 
                           x-model="modal.form.email"
                           class="w-full px-4 py-2.5 bg-background-dark border border-border-dark rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                           placeholder="email@example.com">
                </div>

                <!-- Format -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Format</label>
                    <div class="flex gap-3">
                        <button @click="modal.form.format = 'pdf'"
                                :class="modal.form.format === 'pdf' ? 'bg-primary text-white border-primary' : 'bg-background-dark text-text-secondary border-border-dark hover:text-white'"
                                class="flex-1 px-4 py-2.5 rounded-lg border text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                            PDF
                        </button>
                        <button @click="modal.form.format = 'excel'"
                                :class="modal.form.format === 'excel' ? 'bg-primary text-white border-primary' : 'bg-background-dark text-text-secondary border-border-dark hover:text-white'"
                                class="flex-1 px-4 py-2.5 rounded-lg border text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">table</span>
                            Excel
                        </button>
                        <button @click="modal.form.format = 'csv'"
                                :class="modal.form.format === 'csv' ? 'bg-primary text-white border-primary' : 'bg-background-dark text-text-secondary border-border-dark hover:text-white'"
                                class="flex-1 px-4 py-2.5 rounded-lg border text-sm font-medium transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">description</span>
                            CSV
                        </button>
                    </div>
                </div>

                <!-- Metrics Checklist -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-2">Metrik yang Disertakan</label>
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-2">
                        <template x-for="metric in availableMetrics" :key="metric.value">
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-background-dark cursor-pointer transition-colors">
                                <input type="checkbox" 
                                       :value="metric.value"
                                       x-model="modal.form.metrics"
                                       class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-primary focus:ring-primary">
                                <span class="material-symbols-outlined text-text-secondary text-[18px]" x-text="metric.icon"></span>
                                <span class="text-sm text-white" x-text="metric.label"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-border-dark flex gap-3 justify-end">
                <button @click="closeModal()" 
                        class="px-5 py-2.5 rounded-lg border border-border-dark text-slate-300 hover:text-white hover:bg-surface-lighter text-sm font-medium transition-colors">
                    Batal
                </button>
                <button @click="saveReport()" 
                        :disabled="modal.loading || !isFormValid()"
                        class="px-5 py-2.5 rounded-lg bg-primary hover:bg-primary/80 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                    <span x-show="modal.loading" class="material-symbols-outlined text-[18px] animate-spin">sync</span>
                    <span x-text="modal.isEdit ? 'Simpan' : 'Buat Laporan'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="deleteModal.show = false" class="modal-content-mobile w-full max-w-sm rounded-2xl bg-surface-dark border border-border-dark shadow-2xl p-6 text-center">
            <div class="size-14 bg-red-500/10 text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">delete_forever</span>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Hapus Laporan?</h3>
            <p class="text-sm text-text-secondary mb-6">
                Laporan "<span x-text="deleteModal.name"></span>" akan dihapus permanen.
            </p>
            <div class="flex gap-3 justify-center">
                <button @click="deleteModal.show = false" 
                        class="px-5 py-2.5 rounded-lg border border-border-dark text-slate-300 hover:text-white hover:bg-surface-lighter text-sm font-medium transition-colors">
                    Batal
                </button>
                <button @click="confirmDelete()" 
                        :disabled="deleteModal.loading"
                        class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-400 text-white text-sm font-bold shadow-lg shadow-red-500/20 transition-all disabled:opacity-50">
                    <span x-show="!deleteModal.loading">Hapus</span>
                    <span x-show="deleteModal.loading">Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function scheduledReportsApp() {
    return {
        filter: 'all',
        sendNowLoading: null,
        modal: { 
            show: false, 
            isEdit: false,
            loading: false,
            form: {
                id: null,
                name: '',
                report_type: 'analytics',
                frequency: 'daily',
                day: 'Sen',
                time: '09:00',
                email: '',
                format: 'pdf',
                metrics: ['conversations', 'messages', 'response_time']
            }
        },
        deleteModal: { show: false, id: null, name: '', loading: false },
        stats: {
            total: 4,
            active: 3,
            inactive: 1,
            sent: 156
        },
        availableMetrics: [
            { value: 'conversations', label: 'Percakapan', icon: 'chat' },
            { value: 'messages', label: 'Pesan', icon: 'message' },
            { value: 'response_time', label: 'Waktu Respon', icon: 'timer' },
            { value: 'resolution_rate', label: 'Tingkat Resolusi', icon: 'check_circle' },
            { value: 'csat_score', label: 'Skor CSAT', icon: 'sentiment_satisfied' },
            { value: 'agent_performance', label: 'Performa Agent', icon: 'people' },
            { value: 'ai_handling', label: 'Penanganan AI', icon: 'smart_toy' },
            { value: 'handoff_rate', label: 'Rate Handoff', icon: 'transfer_within_a_station' }
        ],
        reports: [
            {
                id: 1,
                name: 'Laporan Mingguan',
                report_type: 'analytics',
                frequency: 'weekly',
                day: 'Sen',
                time: '08:00',
                email: 'admin@company.com',
                format: 'pdf',
                is_active: true,
                next_run: 'Senin, 08:00',
                metrics: ['conversations', 'messages', 'response_time']
            },
            {
                id: 2,
                name: 'Laporan Bulanan',
                report_type: 'performance',
                frequency: 'monthly',
                day: 1,
                time: '09:00',
                email: 'manager@company.com',
                format: 'excel',
                is_active: true,
                next_run: '1 Mar, 09:00',
                metrics: ['resolution_rate', 'csat_score', 'agent_performance']
            },
            {
                id: 3,
                name: 'Ringkasan Harian CSAT',
                report_type: 'csat',
                frequency: 'daily',
                day: null,
                time: '18:00',
                email: 'cs@company.com',
                format: 'csv',
                is_active: false,
                next_run: 'Paused',
                metrics: ['csat_score', 'conversations']
            },
            {
                id: 4,
                name: 'Performa Agent Mingguan',
                report_type: 'agents',
                frequency: 'weekly',
                day: 'Jum',
                time: '17:00',
                email: 'hr@company.com',
                format: 'pdf',
                is_active: true,
                next_run: 'Jumat, 17:00',
                metrics: ['agent_performance', 'response_time', 'resolution_rate']
            }
        ],

        get filteredReports() {
            if (this.filter === 'all') return this.reports;
            return this.reports.filter(r => this.filter === 'active' ? r.is_active : !r.is_active);
        },

        getReportIcon(type) {
            const icons = {
                analytics: 'analytics',
                performance: 'trending_up',
                csat: 'sentiment_satisfied',
                agents: 'people',
                conversations: 'chat'
            };
            return icons[type] || 'description';
        },

        formatReportType(type) {
            const labels = {
                analytics: 'Analytics Overview',
                performance: 'Performance Report',
                csat: 'CSAT Summary',
                agents: 'Agent Performance',
                conversations: 'Conversation Analysis'
            };
            return labels[type] || type;
        },

        getFrequencyDetail(report) {
            if (report.frequency === 'weekly') return report.day;
            if (report.frequency === 'monthly') return 'Tanggal ' + report.day;
            return '';
        },

        isFormValid() {
            return this.modal.form.name.trim() && 
                   this.modal.form.email.trim() && 
                   this.modal.form.time &&
                   this.modal.form.metrics.length > 0;
        },

        openModal(report = null) {
            if (report) {
                this.modal.isEdit = true;
                this.modal.form = { ...report };
            } else {
                this.modal.isEdit = false;
                this.modal.form = {
                    id: null,
                    name: '',
                    report_type: 'analytics',
                    frequency: 'daily',
                    day: 'Sen',
                    time: '09:00',
                    email: '',
                    format: 'pdf',
                    metrics: ['conversations', 'messages', 'response_time']
                };
            }
            this.modal.show = true;
        },

        closeModal() {
            this.modal.show = false;
            this.modal.loading = false;
        },

        editReport(report) {
            this.openModal(report);
        },

        saveReport() {
            this.modal.loading = true;
            
            setTimeout(() => {
                if (this.modal.isEdit) {
                    const index = this.reports.findIndex(r => r.id === this.modal.form.id);
                    if (index !== -1) {
                        this.reports[index] = { 
                            ...this.modal.form,
                            next_run: this.calculateNextRun(this.modal.form)
                        };
                    }
                    this.showToast('Laporan berhasil diupdate', 'success');
                } else {
                    const newReport = {
                        ...this.modal.form,
                        id: Date.now(),
                        next_run: this.calculateNextRun(this.modal.form)
                    };
                    this.reports.push(newReport);
                    this.stats.total++;
                    if (newReport.is_active) this.stats.active++;
                    else this.stats.inactive++;
                    this.showToast('Laporan berhasil dibuat', 'success');
                }
                this.closeModal();
            }, 800);
        },

        calculateNextRun(form) {
            if (!form.is_active) return 'Paused';
            if (form.frequency === 'daily') return 'Besok, ' + form.time;
            if (form.frequency === 'weekly') return form.day + ', ' + form.time;
            return 'Tanggal ' + form.day + ', ' + form.time;
        },

        sendNow(report) {
            this.sendNowLoading = report.id;
            setTimeout(() => {
                this.sendNowLoading = null;
                this.stats.sent++;
                this.showToast('Laporan sedang dikirim ke ' + report.email, 'success');
            }, 1500);
        },

        deleteReport(report) {
            this.deleteModal = { show: true, id: report.id, name: report.name, loading: false };
        },

        confirmDelete() {
            this.deleteModal.loading = true;
            
            setTimeout(() => {
                const index = this.reports.findIndex(r => r.id === this.deleteModal.id);
                if (index !== -1) {
                    const report = this.reports[index];
                    this.stats.total--;
                    if (report.is_active) this.stats.active--;
                    else this.stats.inactive--;
                    this.reports.splice(index, 1);
                }
                this.showToast('Laporan berhasil dihapus', 'success');
                this.deleteModal.show = false;
            }, 600);
        },

        toggleStatus(id, isActive) {
            const report = this.reports.find(r => r.id === id);
            if (report) {
                report.is_active = isActive;
                report.next_run = isActive ? this.calculateNextRun(report) : 'Paused';
                if (isActive) {
                    this.stats.active++;
                    this.stats.inactive--;
                } else {
                    this.stats.active--;
                    this.stats.inactive++;
                }
                this.showToast(`Laporan ${isActive ? 'diaktifkan' : 'dinonaktifkan'}`, 'success');
            }
        },

        showToast(message, type = 'success') {
            // Use the global toast if available, or create simple alert
            if (window.showToast) {
                window.showToast(message, type);
            } else {
                alert(message);
            }
        }
    }
}
</script>
@endpush

@push('styles')
<style>
.scheduled-card {
    transition: all 0.2s ease;
}
.scheduled-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 40px -10px rgba(19, 91, 236, 0.2);
}
</style>
@endpush
