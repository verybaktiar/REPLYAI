<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Automation - ReplyAI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
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
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                        "surface-dark": "#1e293b",
                        "surface-lighter": "#232f48",
                        "text-secondary": "#92a4c9",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #232f48; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
        
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden" 
      x-data="editAutomation({{ $automation->id }}, {{ json_encode($automation) }})">
<div class="flex flex-col lg:flex-row h-screen w-full">
    @include('components.sidebar')

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Header -->
        <header class="h-14 border-b border-gray-800 bg-background-dark/80 backdrop-blur-md flex items-center justify-between px-6 z-20 shrink-0">
            <div class="flex items-center gap-4">
                <a href="{{ route('automations.index') }}" class="flex items-center gap-2 text-text-secondary hover:text-white transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                    <span class="text-sm font-medium">Kembali</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                @include('components.language-switcher')
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 lg:px-12">
            <div class="max-w-[800px] mx-auto">
                <!-- Page Title -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="size-10 rounded-xl flex items-center justify-center"
                             :class="{
                                 'bg-green-500/10 text-green-500': form.type === 'welcome',
                                 'bg-orange-500/10 text-orange-500': form.type === 'away',
                                 'bg-blue-500/10 text-blue-500': form.type === 'keyword',
                                 'bg-purple-500/10 text-purple-500': form.type === 'follow_up'
                             }">
                            <span class="material-symbols-outlined text-xl">
                                <template x-if="form.type === 'welcome'">waving_hand</template>
                                <template x-if="form.type === 'away'">schedule</template>
                                <template x-if="form.type === 'keyword'">key</template>
                                <template x-if="form.type === 'follow_up'">follow_the_signs</template>
                            </span>
                        </div>
                        <h1 class="text-3xl font-black text-white">Edit Automation</h1>
                    </div>
                    <p class="text-text-secondary">Perbarui konfigurasi automation Anda</p>
                </div>

                <!-- Stats Banner -->
                <div class="bg-surface-dark rounded-xl p-4 border border-surface-lighter mb-6">
                    <div class="flex items-center gap-6 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-text-secondary">play_arrow</span>
                            <span class="text-text-secondary">Triggered:</span>
                            <span class="font-bold text-white">{{ $automation->trigger_count }}x</span>
                        </div>
                        @if($automation->last_triggered_at)
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-text-secondary">schedule</span>
                            <span class="text-text-secondary">Terakhir:</span>
                            <span class="font-bold text-white">{{ $automation->last_triggered_at->diffForHumans() }}</span>
                        </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-text-secondary">calendar_today</span>
                            <span class="text-text-secondary">Dibuat:</span>
                            <span class="font-bold text-white">{{ $automation->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitForm">
                    <!-- Basic Info -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">tune</span>
                            Informasi Dasar
                        </h2>
                        <div class="bg-surface-dark rounded-xl p-5 border border-surface-lighter space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Tipe Automation</label>
                                <input type="text" disabled :value="form.type.replace('_', ' ').replace(/\\b\\w/g, l => l.toUpperCase())"
                                       class="w-full rounded-lg border border-surface-lighter bg-[#111722]/50 px-4 py-2.5 text-sm text-text-secondary cursor-not-allowed capitalize">
                                <p class="text-xs text-text-secondary mt-1">Tipe automation tidak dapat diubah</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Nama Automation</label>
                                <input type="text" x-model="form.name" 
                                       class="w-full rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary placeholder-text-secondary/50">
                                <p x-show="errors.name" x-text="errors.name" class="text-red-400 text-xs mt-1"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Pesan</label>
                                <textarea x-model="form.message" rows="4"
                                          class="w-full rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary placeholder-text-secondary/50 resize-none"
                                          placeholder="Tulis pesan yang akan dikirim otomatis..."></textarea>
                                <div class="flex justify-between mt-1">
                                    <p x-show="errors.message" x-text="errors.message" class="text-red-400 text-xs"></p>
                                    <p class="text-xs text-text-secondary text-right">
                                        <span x-text="form.message.length"></span>/2000
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                                <span class="text-sm text-gray-200">Aktifkan automation ini</span>
                            </div>
                        </div>
                    </div>

                    <!-- Type-specific Configuration -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">settings</span>
                            Konfigurasi <span x-text="form.type ? form.type.replace('_', ' ').replace(/\\b\\w/g, l => l.toUpperCase()) : ''" class="capitalize"></span>
                        </h2>

                        <!-- Away Message Config -->
                        <div x-show="form.type === 'away'" class="bg-surface-dark rounded-xl p-5 border border-surface-lighter space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-200 mb-2">Jam Mulai</label>
                                    <input type="time" x-model="form.away_start_time"
                                           class="w-full rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                    <p x-show="errors.away_start_time" x-text="errors.away_start_time" class="text-red-400 text-xs mt-1"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-200 mb-2">Jam Selesai</label>
                                    <input type="time" x-model="form.away_end_time"
                                           class="w-full rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                    <p x-show="errors.away_end_time" x-text="errors.away_end_time" class="text-red-400 text-xs mt-1"></p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-3">Hari Aktif</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="day in ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']" :key="day">
                                        <label class="cursor-pointer">
                                            <input type="checkbox" :value="day" x-model="form.away_days" class="sr-only peer">
                                            <div class="px-3 py-2 rounded-lg bg-[#111722] border border-surface-lighter text-text-secondary text-sm capitalize peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white transition-colors">
                                                <span x-text="day.substring(0, 3)"></span>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                                <p x-show="errors.away_days" x-text="errors.away_days" class="text-red-400 text-xs mt-2"></p>
                            </div>
                        </div>

                        <!-- Keyword Config -->
                        <div x-show="form.type === 'keyword'" class="bg-surface-dark rounded-xl p-5 border border-surface-lighter space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Kata Kunci</label>
                                <div class="flex gap-2 mb-2">
                                    <input type="text" x-model="newKeyword" @keydown.enter.prevent="addKeyword"
                                           class="flex-1 rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary placeholder-text-secondary/50"
                                           placeholder="Tambah kata kunci (Enter untuk menambah)">
                                    <button type="button" @click="addKeyword" 
                                            class="px-4 py-2 bg-primary hover:bg-blue-600 rounded-lg text-white text-sm font-medium transition-colors">
                                        <span class="material-symbols-outlined">add</span>
                                    </button>
                                </div>
                                
                                <!-- Keywords Tags -->
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <template x-for="(keyword, index) in form.keywords" :key="index">
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500/10 text-blue-400 text-sm rounded-full">
                                            #<span x-text="keyword"></span>
                                            <button type="button" @click="removeKeyword(index)" 
                                                    class="hover:text-red-400 transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">close</span>
                                            </button>
                                        </span>
                                    </template>
                                </div>
                                <p x-show="errors.keywords" x-text="errors.keywords" class="text-red-400 text-xs mt-2"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Tipe Pencocokan</label>
                                <select x-model="form.match_type"
                                        class="w-full rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="contains">Contains (Mengandung)</option>
                                    <option value="exact">Exact Match (Persis)</option>
                                    <option value="starts_with">Starts With (Diawali)</option>
                                    <option value="regex">Regex Pattern</option>
                                </select>
                            </div>
                        </div>

                        <!-- Follow-up Config -->
                        <div x-show="form.type === 'follow_up'" class="bg-surface-dark rounded-xl p-5 border border-surface-lighter space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Jeda Waktu (jam)</label>
                                <div class="flex items-center gap-4">
                                    <input type="range" x-model="form.delay_hours" min="1" max="168" 
                                           class="flex-1 h-2 bg-[#111722] rounded-lg appearance-none cursor-pointer accent-primary">
                                    <span class="text-white font-bold w-16 text-right" x-text="form.delay_hours + ' jam'"></span>
                                </div>
                                <p class="text-xs text-text-secondary mt-2">
                                    Pesan akan dikirim setelah <span x-text="form.delay_hours"></span> jam tanpa balasan dari kontak
                                </p>
                                <p x-show="errors.delay_hours" x-text="errors.delay_hours" class="text-red-400 text-xs mt-1"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-surface-lighter">
                        <button type="button" @click="deleteAutomation" :disabled="deleting"
                                class="px-6 py-2.5 rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/10 hover:text-red-500 text-sm font-medium transition-colors flex items-center gap-2 disabled:opacity-50">
                            <span x-show="!deleting" class="material-symbols-outlined text-[18px]">delete</span>
                            <span x-show="deleting" class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                            <span x-text="deleting ? 'Menghapus...' : 'Hapus'"></span>
                        </button>
                        
                        <div class="flex items-center gap-3">
                            <a href="{{ route('automations.index') }}" 
                               class="px-6 py-2.5 rounded-lg border border-surface-lighter text-gray-300 hover:text-white hover:bg-surface-lighter text-sm font-medium transition-colors">
                                Batal
                            </a>
                            <button type="submit" :disabled="submitting"
                                    class="px-6 py-2.5 rounded-lg bg-primary hover:bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-900/20 transition-all disabled:opacity-50 flex items-center gap-2">
                                <span x-show="!submitting" class="material-symbols-outlined text-[18px]">save</span>
                                <span x-show="submitting" class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                                <span x-text="submitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<!-- Toast -->
<div x-show="toast.show" x-transition
     class="fixed bottom-5 right-5 z-[60] rounded-xl bg-[#1e293b] border border-[#232f48] px-4 py-3 text-sm text-white shadow-xl flex items-center gap-3">
    <span class="material-symbols-outlined" :class="toast.type === 'success' ? 'text-green-500' : 'text-red-500'"
          x-text="toast.type === 'success' ? 'check_circle' : 'error'"></span>
    <span x-text="toast.message"></span>
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
    <div @click.away="deleteModal.show = false" class="w-full max-w-sm rounded-2xl bg-[#1e293b] border border-[#232f48] shadow-2xl p-6 text-center">
        <div class="size-14 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-3xl">delete_forever</span>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Hapus Automation?</h3>
        <p class="text-sm text-text-secondary mb-6">
            Automation "<span x-text="form.name"></span>" akan dihapus permanen.
        </p>
        <div class="flex gap-3 justify-center">
            <button @click="deleteModal.show = false" 
                    class="px-5 py-2.5 rounded-lg border border-[#232f48] text-gray-300 hover:text-white hover:bg-[#232f48] text-sm font-medium transition-colors">
                Batal
            </button>
            <button @click="confirmDelete()" 
                    :disabled="deleteModal.loading"
                    class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-bold shadow-lg shadow-red-900/20 transition-all disabled:opacity-50">
                <span x-show="!deleteModal.loading">Hapus</span>
                <span x-show="deleteModal.loading">Menghapus...</span>
            </button>
        </div>
    </div>
</div>

<script>
function editAutomation(id, automation) {
    return {
        id: id,
        form: {
            type: automation.type,
            name: automation.name,
            message: automation.message,
            is_active: automation.is_active,
            away_start_time: automation.away_start_time ? automation.away_start_time.substring(0, 5) : '17:00',
            away_end_time: automation.away_end_time ? automation.away_end_time.substring(0, 5) : '08:00',
            away_days: automation.away_days || ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            keywords: automation.keywords || [],
            match_type: automation.match_type || 'contains',
            delay_hours: automation.delay_hours || 24,
        },
        newKeyword: '',
        errors: {},
        submitting: false,
        deleting: false,
        deleteModal: { show: false, loading: false },
        toast: { show: false, message: '', type: 'success' },

        addKeyword() {
            if (this.newKeyword.trim() && !this.form.keywords.includes(this.newKeyword.trim().toLowerCase())) {
                this.form.keywords.push(this.newKeyword.trim().toLowerCase());
                this.newKeyword = '';
            }
        },

        removeKeyword(index) {
            this.form.keywords.splice(index, 1);
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 3000);
        },

        deleteAutomation() {
            this.deleteModal.show = true;
        },

        async confirmDelete() {
            this.deleteModal.loading = true;

            try {
                const response = await fetch(`/automations/${this.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.ok) {
                    this.showToast('Automation berhasil dihapus', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route('automations.index') }}';
                    }, 500);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                this.showToast('Gagal menghapus automation', 'error');
            } finally {
                this.deleteModal.loading = false;
                this.deleteModal.show = false;
            }
        },

        async submitForm() {
            this.errors = {};
            this.submitting = true;

            try {
                const response = await fetch(`/automations/${this.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = data.errors || {};
                        throw new Error('Validation failed');
                    }
                    throw new Error(data.message || 'Failed to update automation');
                }

                this.showToast('Automation berhasil diperbarui!', 'success');
                
                setTimeout(() => {
                    window.location.href = '{{ route('automations.index') }}';
                }, 1000);

            } catch (error) {
                if (error.message !== 'Validation failed') {
                    this.showToast(error.message, 'error');
                }
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
</body>
</html>
