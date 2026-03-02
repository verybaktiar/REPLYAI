@extends('layouts.dark')

@section('title', 'Template Laporan')

@section('content')
<div class="space-y-6" x-data="templatesApp()">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Template Laporan</h1>
            <p class="text-slate-400 text-sm">Kelola template dengan metrik dan visualisasi yang dapat disesuaikan</p>
        </div>
        <button @click="openModal()" 
                class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/80 text-white rounded-xl font-semibold transition-all shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Buat Template
        </button>
    </div>

    @include('components.page-help', [
        'title' => 'Template Laporan',
        'description' => 'Buat dan kelola template laporan kustom.',
        'tips' => ['Buat template dengan metrik pilihan', 'Simpan template untuk penggunaan berulang', 'Share template dengan tim', 'Gunakan template untuk export cepat']
    ])

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-surface-dark rounded-xl p-4 border border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">dashboard</span>
                </div>
                <div>
                    <p class="text-2xl font-black text-white" x-text="stats.total">0</p>
                    <p class="text-xs text-text-secondary">Total Template</p>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl p-4 border border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-400">star</span>
                </div>
                <div>
                    <p class="text-2xl font-black text-white" x-text="stats.default">0</p>
                    <p class="text-xs text-text-secondary">Default</p>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl p-4 border border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-400">content_copy</span>
                </div>
                <div>
                    <p class="text-2xl font-black text-white" x-text="stats.used">0</p>
                    <p class="text-xs text-text-secondary">Digunakan</p>
                </div>
            </div>
        </div>
        <div class="bg-surface-dark rounded-xl p-4 border border-border-dark">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-pink-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-pink-400">favorite</span>
                </div>
                <div>
                    <p class="text-2xl font-black text-white" x-text="stats.favorites">0</p>
                    <p class="text-xs text-text-secondary">Favorit</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <template x-for="template in templates" :key="template.id">
            <div class="template-card bg-surface-dark rounded-xl border border-border-dark overflow-hidden relative group"
                 :class="{ 'ring-2 ring-primary/50': template.is_default }">
                <!-- Default Badge -->
                <div x-show="template.is_default" 
                     class="absolute top-3 right-3 px-2 py-1 bg-primary/20 text-primary text-[10px] font-bold uppercase tracking-wider rounded-full flex items-center gap-1 z-10">
                    <span class="material-symbols-outlined text-[12px]">star</span>
                    Default
                </div>
                
                <!-- Shared Badge -->
                <div x-show="template.is_shared" 
                     class="absolute top-3 left-3 px-2 py-1 bg-purple-500/20 text-purple-400 text-[10px] font-bold uppercase tracking-wider rounded-full flex items-center gap-1 z-10">
                    <span class="material-symbols-outlined text-[12px]">share</span>
                    Shared
                </div>
                
                <!-- Chart Preview -->
                <div class="chart-preview h-44 p-4 relative overflow-hidden bg-gradient-to-br from-surface-dark to-background-dark">
                    <!-- Preview Content based on chart type -->
                    <div class="h-full flex items-end justify-between gap-2 px-4 pb-2">
                        <template x-if="template.chart_type === 'bar'">
                            <div class="w-full flex items-end justify-between gap-2 h-full">
                                <template x-for="(height, i) in [40, 65, 45, 80, 55, 70, 60]">
                                    <div class="flex-1 bg-primary/60 rounded-t transition-all hover:bg-primary"
                                         :style="`height: ${height}%`"></div>
                                </template>
                            </div>
                        </template>
                        <template x-if="template.chart_type === 'line'">
                            <svg class="w-full h-full" viewBox="0 0 100 50" preserveAspectRatio="none">
                                <polyline fill="none" stroke="#135bec" stroke-width="2" 
                                          points="0,40 15,30 30,35 45,20 60,25 75,10 90,15 100,5"/>
                                <polygon fill="url(#lineGradient-1)" opacity="0.3"
                                         points="0,40 15,30 30,35 45,20 60,25 75,10 90,15 100,5 100,50 0,50"/>
                                <defs>
                                    <linearGradient id="lineGradient-1" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#135bec;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#135bec;stop-opacity:0" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </template>
                        <template x-if="template.chart_type === 'pie'">
                            <div class="w-24 h-24 rounded-full mx-auto relative"
                                 style="background: conic-gradient(#135bec 0deg 120deg, #10b981 120deg 240deg, #f59e0b 240deg 360deg)">
                                <div class="absolute inset-4 bg-surface-dark rounded-full"></div>
                            </div>
                        </template>
                        <template x-if="template.chart_type === 'heatmap'">
                            <div class="w-full h-full grid grid-cols-7 gap-1">
                                <template x-for="i in 28">
                                    <div class="rounded-sm"
                                         :class="[
                                             'bg-primary/', 
                                             ['10', '30', '50', '70', '90'][Math.floor(Math.random() * 5)]
                                         ].join('')"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <!-- Chart Type Icon -->
                    <div class="absolute top-3 left-3 size-8 rounded-lg bg-surface-dark/80 backdrop-blur flex items-center justify-center"
                         :class="template.is_shared ? 'top-10' : ''">
                        <span class="material-symbols-outlined text-primary text-[18px]" x-text="getChartIcon(template.chart_type)"></span>
                    </div>
                </div>

                <!-- Card Content -->
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-white text-lg" x-text="template.name"></h3>
                            <p class="text-xs text-text-secondary mt-1 line-clamp-2" x-text="template.description"></p>
                        </div>
                    </div>

                    <!-- Metrics Count -->
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-text-secondary text-[16px]">checklist</span>
                        <span class="text-xs text-text-secondary">
                            <span x-text="template.metrics.length"></span> metrik
                        </span>
                    </div>

                    <!-- Metrics Tags -->
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        <template x-for="metric in template.metrics.slice(0, 3)">
                            <span class="px-2 py-0.5 bg-surface-lighter text-text-secondary text-[10px] rounded-md capitalize"
                                  x-text="formatMetric(metric)"></span>
                        </template>
                        <span x-show="template.metrics.length > 3" 
                              class="px-2 py-0.5 bg-surface-lighter text-text-secondary text-[10px] rounded-md"
                              x-text="`+${template.metrics.length - 3}`"></span>
                    </div>

                    <!-- Stats & Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-border-dark">
                        <div class="flex items-center gap-3 text-xs text-text-secondary">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                <span x-text="template.last_used || 'Belum digunakan'"></span>
                            </span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="duplicateTemplate(template)" 
                                    class="p-2 rounded-lg hover:bg-surface-lighter text-text-secondary hover:text-white transition-colors"
                                    title="Duplikat">
                                <span class="material-symbols-outlined text-[18px]">content_copy</span>
                            </button>
                            <button @click="editTemplate(template)" 
                                    class="p-2 rounded-lg hover:bg-surface-lighter text-text-secondary hover:text-white transition-colors"
                                    title="Edit">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                            <button @click="deleteTemplate(template)" 
                                    class="p-2 rounded-lg hover:bg-red-500/10 text-text-secondary hover:text-red-400 transition-colors"
                                    title="Hapus">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <!-- Set as Default Button -->
                    <button x-show="!template.is_default"
                            @click="setAsDefault(template)"
                            class="mt-4 w-full py-2 rounded-lg border border-border-dark text-text-secondary hover:text-white hover:bg-surface-lighter text-sm font-medium transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">star_border</span>
                        Jadikan Default
                    </button>
                </div>
            </div>
        </template>
        
        <!-- Empty State -->
        <div x-show="templates.length === 0" class="col-span-full">
            <x-empty-state 
                icon="dashboard_customize" 
                title="Belum ada template" 
                description="Buat template laporan untuk menyimpan konfigurasi metrik dan visualisasi favorit Anda."
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
             class="modal-content-mobile w-full max-w-2xl rounded-2xl bg-surface-dark border border-border-dark shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                <h3 class="text-xl font-bold text-white" x-text="modal.isEdit ? 'Edit Template' : 'Buat Template'"></h3>
                <button @click="closeModal()" class="p-2 rounded-lg hover:bg-surface-lighter text-text-secondary hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                <!-- Name & Description -->
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Nama Template</label>
                        <input type="text" 
                               x-model="modal.form.name"
                               class="w-full px-4 py-2.5 bg-background-dark border border-border-dark rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                               placeholder="Contoh: Dashboard Performa Mingguan">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Deskripsi</label>
                        <textarea x-model="modal.form.description"
                                  rows="2"
                                  class="w-full px-4 py-2.5 bg-background-dark border border-border-dark rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors resize-none"
                                  placeholder="Deskripsi singkat tentang template ini..."></textarea>
                    </div>
                </div>

                <!-- Chart Type Selector -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-3">Jenis Chart</label>
                    <div class="grid grid-cols-4 gap-3">
                        <button @click="modal.form.chart_type = 'line'"
                                :class="modal.form.chart_type === 'line' ? 'bg-primary/20 border-primary text-white' : 'bg-background-dark border-border-dark text-text-secondary hover:text-white'"
                                class="p-4 rounded-xl border text-center transition-colors">
                            <span class="material-symbols-outlined text-2xl mb-2">show_chart</span>
                            <p class="text-xs font-medium">Line</p>
                        </button>
                        <button @click="modal.form.chart_type = 'bar'"
                                :class="modal.form.chart_type === 'bar' ? 'bg-primary/20 border-primary text-white' : 'bg-background-dark border-border-dark text-text-secondary hover:text-white'"
                                class="p-4 rounded-xl border text-center transition-colors">
                            <span class="material-symbols-outlined text-2xl mb-2">bar_chart</span>
                            <p class="text-xs font-medium">Bar</p>
                        </button>
                        <button @click="modal.form.chart_type = 'pie'"
                                :class="modal.form.chart_type === 'pie' ? 'bg-primary/20 border-primary text-white' : 'bg-background-dark border-border-dark text-text-secondary hover:text-white'"
                                class="p-4 rounded-xl border text-center transition-colors">
                            <span class="material-symbols-outlined text-2xl mb-2">pie_chart</span>
                            <p class="text-xs font-medium">Pie</p>
                        </button>
                        <button @click="modal.form.chart_type = 'heatmap'"
                                :class="modal.form.chart_type === 'heatmap' ? 'bg-primary/20 border-primary text-white' : 'bg-background-dark border-border-dark text-text-secondary hover:text-white'"
                                class="p-4 rounded-xl border text-center transition-colors">
                            <span class="material-symbols-outlined text-2xl mb-2">apps</span>
                            <p class="text-xs font-medium">Heatmap</p>
                        </button>
                    </div>
                </div>

                <!-- Metrics Selector -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-3">Pilih Metrik</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <template x-for="metric in availableMetrics" :key="metric.value">
                            <label class="flex items-center gap-3 p-3 bg-background-dark border border-border-dark rounded-lg cursor-pointer hover:border-primary/50 transition-colors"
                                   :class="{ 'border-primary bg-primary/10': modal.form.metrics.includes(metric.value) }">
                                <input type="checkbox" 
                                       :value="metric.value"
                                       x-model="modal.form.metrics"
                                       class="sr-only">
                                <div class="size-5 rounded border flex items-center justify-center transition-colors"
                                     :class="modal.form.metrics.includes(metric.value) ? 'bg-primary border-primary' : 'border-border-dark'">
                                    <span x-show="modal.form.metrics.includes(metric.value)" class="material-symbols-outlined text-white text-[14px]">check</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-text-secondary text-[18px]" x-text="metric.icon"></span>
                                    <span class="text-sm text-white" x-text="metric.label"></span>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Preview Section -->
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-3">Preview</label>
                    <div class="bg-background-dark rounded-xl p-4 border border-border-dark">
                        <div class="flex items-center justify-center h-32">
                            <div class="text-center">
                                <span class="material-symbols-outlined text-4xl text-text-secondary mb-2" x-text="getChartIcon(modal.form.chart_type)"></span>
                                <p class="text-xs text-text-secondary capitalize" x-text="modal.form.chart_type + ' chart preview'"></p>
                                <p class="text-xs text-slate-500 mt-1"><span x-text="modal.form.metrics.length"></span> metrik dipilih</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Set as Default -->
                <div class="flex items-center justify-between p-4 bg-background-dark rounded-xl border border-border-dark">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">star</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-white">Jadikan Default</label>
                            <p class="text-xs text-text-secondary">Template ini akan digunakan sebagai default untuk laporan baru</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="modal.form.is_default" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-border-dark flex gap-3 justify-end">
                <button @click="closeModal()" 
                        class="px-5 py-2.5 rounded-lg border border-border-dark text-slate-300 hover:text-white hover:bg-surface-lighter text-sm font-medium transition-colors">
                    Batal
                </button>
                <button @click="saveTemplate()" 
                        :disabled="modal.loading || !isFormValid()"
                        class="px-5 py-2.5 rounded-lg bg-primary hover:bg-primary/80 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                    <span x-show="modal.loading" class="material-symbols-outlined text-[18px] animate-spin">sync</span>
                    <span x-text="modal.isEdit ? 'Simpan' : 'Buat Template'"></span>
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
            <h3 class="text-xl font-bold text-white mb-2">Hapus Template?</h3>
            <p class="text-sm text-text-secondary mb-6">
                Template "<span x-text="deleteModal.name"></span>" akan dihapus permanen.
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
function templatesApp() {
    return {
        modal: { 
            show: false, 
            isEdit: false,
            loading: false,
            form: {
                id: null,
                name: '',
                description: '',
                chart_type: 'line',
                metrics: [],
                is_default: false
            }
        },
        deleteModal: { show: false, id: null, name: '', loading: false },
        stats: {
            total: 5,
            default: 2,
            used: 24,
            favorites: 3
        },
        availableMetrics: [
            { value: 'conversations', label: 'Percakapan', icon: 'chat' },
            { value: 'messages', label: 'Pesan', icon: 'message' },
            { value: 'response_time', label: 'Waktu Respon', icon: 'timer' },
            { value: 'resolution_rate', label: 'Tingkat Resolusi', icon: 'check_circle' },
            { value: 'csat_score', label: 'Skor CSAT', icon: 'sentiment_satisfied' },
            { value: 'agent_performance', label: 'Performa Agent', icon: 'people' },
            { value: 'ai_handling', label: 'Penanganan AI', icon: 'smart_toy' },
            { value: 'handoff_rate', label: 'Rate Handoff', icon: 'transfer_within_a_station' },
            { value: 'peak_hours', label: 'Jam Sibuk', icon: 'schedule' },
            { value: 'channel_distribution', label: 'Distribusi Channel', icon: 'device_hub' },
            { value: 'sentiment', label: 'Sentimen', icon: 'mood' },
            { value: 'topics', label: 'Topik', icon: 'topic' }
        ],
        templates: [
            {
                id: 1,
                name: 'Performa Mingguan',
                description: 'Overview mingguan performa chat dan agent',
                chart_type: 'bar',
                metrics: ['conversations', 'messages', 'response_time', 'resolution_rate'],
                is_default: true,
                is_shared: false,
                last_used: '2 hari lalu'
            },
            {
                id: 2,
                name: 'Analisis CSAT',
                description: 'Analisis kepuasan pelanggan dan feedback',
                chart_type: 'line',
                metrics: ['csat_score', 'conversations', 'resolution_rate', 'sentiment'],
                is_default: false,
                is_shared: true,
                last_used: '1 minggu lalu'
            },
            {
                id: 3,
                name: 'Produktivitas Agent',
                description: 'Metrik produktivitas tim support',
                chart_type: 'pie',
                metrics: ['agent_performance', 'messages', 'response_time'],
                is_default: false,
                is_shared: false,
                last_used: '3 hari lalu'
            },
            {
                id: 4,
                name: 'Performa AI',
                description: 'Analisis performa AI dan handoff rate',
                chart_type: 'line',
                metrics: ['ai_handling', 'handoff_rate', 'conversations'],
                is_default: true,
                is_shared: true,
                last_used: 'Kemarin'
            },
            {
                id: 5,
                name: 'Aktivitas Heatmap',
                description: 'Distribusi aktivitas per jam dan hari',
                chart_type: 'heatmap',
                metrics: ['peak_hours', 'conversations', 'messages'],
                is_default: false,
                is_shared: false,
                last_used: null
            }
        ],

        getChartIcon(type) {
            const icons = {
                bar: 'bar_chart',
                line: 'show_chart',
                pie: 'pie_chart',
                heatmap: 'apps'
            };
            return icons[type] || 'bar_chart';
        },

        formatMetric(metric) {
            const labels = {
                conversations: 'Percakapan',
                messages: 'Pesan',
                response_time: 'Waktu Respon',
                resolution_rate: 'Resolusi',
                csat_score: 'CSAT',
                agent_performance: 'Agent',
                ai_handling: 'AI',
                handoff_rate: 'Handoff',
                peak_hours: 'Jam Sibuk',
                channel_distribution: 'Channel',
                sentiment: 'Sentimen',
                topics: 'Topik'
            };
            return labels[metric] || metric;
        },

        isFormValid() {
            return this.modal.form.name.trim() && this.modal.form.metrics.length > 0;
        },

        openModal(template = null) {
            if (template) {
                this.modal.isEdit = true;
                this.modal.form = { 
                    id: template.id,
                    name: template.name,
                    description: template.description,
                    chart_type: template.chart_type,
                    metrics: [...template.metrics],
                    is_default: template.is_default
                };
            } else {
                this.modal.isEdit = false;
                this.modal.form = {
                    id: null,
                    name: '',
                    description: '',
                    chart_type: 'line',
                    metrics: [],
                    is_default: false
                };
            }
            this.modal.show = true;
        },

        closeModal() {
            this.modal.show = false;
            this.modal.loading = false;
        },

        editTemplate(template) {
            this.openModal(template);
        },

        saveTemplate() {
            this.modal.loading = true;
            
            setTimeout(() => {
                if (this.modal.isEdit) {
                    const index = this.templates.findIndex(t => t.id === this.modal.form.id);
                    if (index !== -1) {
                        // Remove default from others if this is set as default
                        if (this.modal.form.is_default) {
                            this.templates.forEach(t => t.is_default = false);
                            this.stats.default = 1;
                        }
                        this.templates[index] = { 
                            ...this.templates[index],
                            name: this.modal.form.name,
                            description: this.modal.form.description,
                            chart_type: this.modal.form.chart_type,
                            metrics: [...this.modal.form.metrics],
                            is_default: this.modal.form.is_default
                        };
                    }
                    this.showToast('Template berhasil diupdate', 'success');
                } else {
                    if (this.modal.form.is_default) {
                        this.templates.forEach(t => t.is_default = false);
                        this.stats.default++;
                    }
                    const newTemplate = {
                        id: Date.now(),
                        name: this.modal.form.name,
                        description: this.modal.form.description,
                        chart_type: this.modal.form.chart_type,
                        metrics: [...this.modal.form.metrics],
                        is_default: this.modal.form.is_default,
                        is_shared: false,
                        last_used: null
                    };
                    this.templates.push(newTemplate);
                    this.stats.total++;
                    this.showToast('Template berhasil dibuat', 'success');
                }
                this.closeModal();
            }, 800);
        },

        duplicateTemplate(template) {
            const newTemplate = {
                ...template,
                id: Date.now(),
                name: `${template.name} (Copy)`,
                is_default: false,
                is_shared: false,
                last_used: null
            };
            this.templates.push(newTemplate);
            this.stats.total++;
            this.showToast('Template berhasil diduplikat', 'success');
        },

        setAsDefault(template) {
            this.templates.forEach(t => t.is_default = false);
            template.is_default = true;
            this.stats.default = this.templates.filter(t => t.is_default).length;
            this.showToast('Template dijadikan default', 'success');
        },

        deleteTemplate(template) {
            this.deleteModal = { show: true, id: template.id, name: template.name, loading: false };
        },

        confirmDelete() {
            this.deleteModal.loading = true;
            
            setTimeout(() => {
                const index = this.templates.findIndex(t => t.id === this.deleteModal.id);
                if (index !== -1) {
                    const template = this.templates[index];
                    if (template.is_default) this.stats.default--;
                    this.templates.splice(index, 1);
                    this.stats.total--;
                }
                this.showToast('Template berhasil dihapus', 'success');
                this.deleteModal.show = false;
            }, 600);
        },

        showToast(message, type = 'success') {
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
.template-card {
    transition: all 0.2s ease;
}
.template-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px -10px rgba(19, 91, 236, 0.25);
}

.chart-preview {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
}
</style>
@endpush
