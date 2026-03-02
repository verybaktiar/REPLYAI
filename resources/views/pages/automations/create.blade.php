<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buat Automation - ReplyAI</title>
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
        
        .type-card {
            transition: all 0.2s ease;
        }
        .type-card:hover {
            transform: translateY(-2px);
        }
        .type-card.selected {
            border-color: #135bec;
            background: rgba(19, 91, 236, 0.1);
        }
        
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden" x-data="createAutomation()">
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
                    <h1 class="text-3xl font-black text-white mb-2">Buat Automation Baru</h1>
                    <p class="text-text-secondary">Pilih tipe automation dan konfigurasi sesuai kebutuhan</p>
                </div>

                <form @submit.prevent="submitForm">
                    <!-- Step 1: Select Type -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">category</span>
                            1. Pilih Tipe Automation
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Welcome -->
                            <div @click="form.type = 'welcome'" 
                                 :class="form.type === 'welcome' ? 'selected' : ''"
                                 class="type-card cursor-pointer bg-surface-dark rounded-xl p-5 border border-surface-lighter hover:border-primary/50">
                                <div class="flex items-start gap-4">
                                    <div class="size-12 rounded-xl bg-green-500/10 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-green-500 text-2xl">waving_hand</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white mb-1">Welcome Message</h3>
                                        <p class="text-sm text-text-secondary">Kirim pesan otomatis saat kontak baru pertama kali chat</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Away -->
                            <div @click="form.type = 'away'" 
                                 :class="form.type === 'away' ? 'selected' : ''"
                                 class="type-card cursor-pointer bg-surface-dark rounded-xl p-5 border border-surface-lighter hover:border-primary/50">
                                <div class="flex items-start gap-4">
                                    <div class="size-12 rounded-xl bg-orange-500/10 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-orange-500 text-2xl">schedule</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white mb-1">Away Message</h3>
                                        <p class="text-sm text-text-secondary">Balasan otomatis di luar jam kerja yang ditentukan</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Keyword -->
                            <div @click="form.type = 'keyword'" 
                                 :class="form.type === 'keyword' ? 'selected' : ''"
                                 class="type-card cursor-pointer bg-surface-dark rounded-xl p-5 border border-surface-lighter hover:border-primary/50">
                                <div class="flex items-start gap-4">
                                    <div class="size-12 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-blue-500 text-2xl">key</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white mb-1">Keyword Reply</h3>
                                        <p class="text-sm text-text-secondary">Balasan otomatis berdasarkan kata kunci tertentu</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Follow-up -->
                            <div @click="form.type = 'follow_up'" 
                                 :class="form.type === 'follow_up' ? 'selected' : ''"
                                 class="type-card cursor-pointer bg-surface-dark rounded-xl p-5 border border-surface-lighter hover:border-primary/50">
                                <div class="flex items-start gap-4">
                                    <div class="size-12 rounded-xl bg-purple-500/10 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-purple-500 text-2xl">follow_the_signs</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white mb-1">Follow-up</h3>
                                        <p class="text-sm text-text-secondary">Kirim pesan tindak lanjut setelah tidak ada balasan</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p x-show="errors.type" x-text="errors.type" class="text-red-400 text-sm mt-2"></p>
                    </div>

                    <!-- Step 2: Basic Info -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">tune</span>
                            2. Informasi Dasar
                        </h2>
                        <div class="bg-surface-dark rounded-xl p-5 border border-surface-lighter space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Nama Automation</label>
                                <input type="text" x-model="form.name" 
                                       class="w-full rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary placeholder-text-secondary/50"
                                       placeholder="Contoh: Welcome Message Baru">
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

                    <!-- Step 3: Type-specific Configuration -->
                    <div class="mb-8" x-show="form.type">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">settings</span>
                            3. Konfigurasi <span x-text="form.type ? form.type.replace('_', ' ').replace(/\\b\\w/g, l => l.toUpperCase()) : ''" class="capitalize"></span>
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
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-surface-lighter">
                        <a href="{{ route('automations.index') }}" 
                           class="px-6 py-2.5 rounded-lg border border-surface-lighter text-gray-300 hover:text-white hover:bg-surface-lighter text-sm font-medium transition-colors">
                            Batal
                        </a>
                        <button type="submit" :disabled="submitting"
                                class="px-6 py-2.5 rounded-lg bg-primary hover:bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-900/20 transition-all disabled:opacity-50 flex items-center gap-2">
                            <span x-show="!submitting" class="material-symbols-outlined text-[18px]">save</span>
                            <span x-show="submitting" class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Automation'"></span>
                        </button>
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

<script>
function createAutomation() {
    return {
        form: {
            type: '',
            name: '',
            message: '',
            is_active: true,
            away_start_time: '17:00',
            away_end_time: '08:00',
            away_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            keywords: [],
            match_type: 'contains',
            delay_hours: 24,
        },
        newKeyword: '',
        errors: {},
        submitting: false,
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

        async submitForm() {
            this.errors = {};
            this.submitting = true;

            try {
                const response = await fetch('{{ route('automations.store') }}', {
                    method: 'POST',
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
                    throw new Error(data.message || 'Failed to create automation');
                }

                this.showToast('Automation berhasil dibuat!', 'success');
                
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
