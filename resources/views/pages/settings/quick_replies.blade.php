<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Balasan Cepat - ReplyAI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
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
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #232f48; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
        .material-symbols-outlined.filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }

        [x-cloak] { display: none !important; }

        @media (max-width: 640px) {
            .modal-content-mobile {
                position: fixed;
                bottom: 0;
                width: 100%;
                max-width: none !important;
                border-radius: 1.5rem 1.5rem 0 0 !important;
                margin: 0 !important;
                padding-bottom: env(safe-area-inset-bottom);
            }
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden">
<div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Header -->
        <header class="h-14 border-b border-border-dark bg-background-dark/80 backdrop-blur-md flex items-center justify-between px-6 z-20 shrink-0">
            <div class="flex items-center gap-2 text-text-secondary text-xs font-bold uppercase tracking-widest">
                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1 bg-whatsapp/10 rounded-full border border-whatsapp/20">
                    <div class="size-1.5 bg-whatsapp rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold text-whatsapp uppercase tracking-widest">System Online</span>
                </div>
                @include('components.language-switcher')
            </div>
        </header>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8 lg:px-12"
             x-data="quickRepliesManager()"
             x-init="init()">
            <div class="max-w-[1200px] mx-auto flex flex-col gap-6">
                <!-- Page Heading -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="flex flex-col gap-2 max-w-2xl">
                        <div class="flex items-center gap-3">
                            <h1 class="text-white text-3xl md:text-4xl font-black leading-tight tracking-tight">Balasan Cepat</h1>
                            @include('components.page-help', [
                                'title' => 'Balasan Cepat (Quick Replies)',
                                'description' => 'Template balasan yang bisa dipakai ulang dengan shortcut.',
                                'tips' => [
                                    'Buat template dengan shortcut unik',
                                    'Ketik / diikuti shortcut di chat untuk memakai',
                                    'Contoh: /greeting untuk pesan selamat datang',
                                    'Template bisa diatur aktif/nonaktif',
                                    'Kelompokkan dengan kategori untuk organisasi lebih baik'
                                ]
                            ])
                        </div>
                        <p class="text-text-secondary text-base font-normal">
                            Kelola template balasan untuk respons lebih cepat saat chat dengan pelanggan.
                        </p>
                    </div>
                    <button 
                        @click="openCreateForm()"
                        type="button"
                        class="flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-lg h-12 px-6 bg-primary hover:bg-blue-600 transition-colors text-white text-sm font-bold shadow-lg shadow-blue-900/20">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        <span>Tambah Quick Reply</span>
                    </button>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-surface-lighter rounded-xl p-4 border border-[#232f48]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary">bolt</span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-white" x-text="quickReplies.length">0</p>
                                <p class="text-xs text-text-secondary">Total Quick Reply</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-lighter rounded-xl p-4 border border-[#232f48]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-500">check_circle</span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-white" x-text="activeCount">0</p>
                                <p class="text-xs text-text-secondary">Aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-lighter rounded-xl p-4 border border-[#232f48]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-purple-500">tag</span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-white" x-text="categories.length">0</p>
                                <p class="text-xs text-text-secondary">Kategori</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search Toolbar -->
                <div class="bg-surface-lighter rounded-xl p-2 flex flex-col lg:flex-row gap-2">
                    <!-- Search -->
                    <div class="flex-1 min-w-[280px]">
                        <div class="relative h-10 w-full group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-text-secondary group-focus-within:text-white transition-colors">search</span>
                            </div>
                            <input 
                                x-model="filter"
                                type="text" 
                                class="block w-full h-full pl-10 pr-3 py-2 border-none rounded-lg bg-[#111722] text-white placeholder-text-secondary focus:ring-1 focus:ring-primary focus:bg-[#0f1520] transition-all text-sm" 
                                placeholder="Cari shortcut atau pesan..."
                            />
                        </div>
                    </div>
                    
                    <div class="w-px h-6 bg-[#232f48] mx-2 self-center hidden lg:block"></div>
                    
                    <!-- Category Filter -->
                    <div class="min-w-[180px]">
                        <select 
                            x-model="selectedCategory"
                            class="w-full h-10 px-3 bg-[#111722] border border-[#232f48] rounded-lg text-sm text-white focus:ring-1 focus:ring-primary focus:border-transparent"
                        >
                            <option value="">Semua Kategori</option>
                            <template x-for="cat in categories" :key="cat">
                                <option :value="cat" x-text="cat"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="w-px h-6 bg-[#232f48] mx-2 self-center hidden lg:block"></div>
                    
                    <div class="flex items-center px-2 text-text-secondary text-sm">
                        <span x-text="filteredReplies.length + ' quick reply'"></span>
                    </div>
                </div>

                <!-- Quick Replies Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <template x-for="reply in filteredReplies" :key="reply.id">
                        <div 
                            class="group bg-surface-lighter rounded-xl border border-[#232f48] hover:border-primary/30 transition-all p-5"
                            :class="{ 'opacity-60': !reply.is_active }"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <!-- Header: Shortcut & Category -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <span 
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono bg-primary/10 text-primary border border-primary/20"
                                            x-text="'/' + reply.shortcut"
                                        ></span>
                                        <span 
                                            class="text-[10px] px-2 py-0.5 rounded bg-[#111722] text-text-secondary"
                                            x-text="reply.category"
                                        ></span>
                                        <span 
                                            x-show="!reply.is_active"
                                            class="text-[10px] px-2 py-0.5 rounded bg-slate-500/10 text-slate-500"
                                        >Nonaktif</span>
                                    </div>
                                    
                                    <!-- Message -->
                                    <p 
                                        class="text-sm text-white whitespace-pre-wrap break-words line-clamp-3"
                                        x-text="reply.message"
                                    ></p>
                                    
                                    <!-- Footer: Usage & Actions -->
                                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-[#232f48]">
                                        <div class="flex items-center gap-3">
                                            <span 
                                                x-show="reply.usage_count > 0"
                                                class="text-[11px] text-text-secondary flex items-center gap-1"
                                            >
                                                <span class="material-symbols-outlined text-[14px]">replay</span>
                                                <span x-text="'Digunakan ' + reply.usage_count + 'x'"></span>
                                            </span>
                                            <span 
                                                x-show="reply.usage_count === 0"
                                                class="text-[11px] text-text-secondary/50"
                                            >
                                                Belum pernah digunakan
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center gap-1">
                                            <!-- Toggle Active -->
                                            <button 
                                                @click="toggleActive(reply)"
                                                type="button"
                                                class="p-2 rounded-lg transition-colors"
                                                :class="reply.is_active ? 'text-green-500 hover:bg-green-500/10' : 'text-slate-500 hover:bg-slate-500/10'"
                                                :title="reply.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                            >
                                                <span class="material-symbols-outlined text-[18px]" x-text="reply.is_active ? 'toggle_on' : 'toggle_off'"></span>
                                            </button>
                                            
                                            <!-- Edit -->
                                            <button 
                                                @click="editReply(reply)"
                                                type="button"
                                                class="p-2 text-text-secondary hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                                                title="Edit"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            
                                            <!-- Delete -->
                                            <button 
                                                @click="confirmDelete(reply)"
                                                type="button"
                                                class="p-2 text-text-secondary hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors"
                                                title="Hapus"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div 
                    x-show="filteredReplies.length === 0 && !loading"
                    class="py-16 flex flex-col items-center justify-center text-center"
                >
                    <div class="w-20 h-20 bg-[#232f48] rounded-full flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-3xl text-text-secondary">bolt</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">
                        <span x-show="quickReplies.length === 0">Belum ada Quick Reply</span>
                        <span x-show="quickReplies.length > 0">Tidak ada hasil</span>
                    </h3>
                    <p class="text-sm text-text-secondary max-w-md mb-6" x-show="quickReplies.length === 0">
                        Tambahkan template balasan untuk respons lebih cepat saat chat dengan pelanggan.
                    </p>
                    <button 
                        x-show="quickReplies.length === 0"
                        @click="openCreateForm()"
                        type="button"
                        class="px-6 py-3 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-blue-900/20"
                    >
                        Buat Quick Reply Pertama
                    </button>
                </div>
            </div>

            {{-- Create/Edit Modal --}}
            <template x-teleport="body">
            <div 
                x-show="showForm"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-cloak
                class="fixed inset-0 z-50"
            >
                <div 
                    @click="closeForm()"
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                ></div>

                <div class="absolute inset-0 flex items-center justify-center p-4">
                    <div 
                        @click.stop
                        class="w-full max-w-lg bg-surface-dark border border-[#232f48] rounded-2xl shadow-2xl modal-content-mobile"
                    >
                        <div class="px-6 py-4 border-b border-[#232f48]">
                            <h3 class="text-lg font-bold text-white" x-text="isEditing ? 'Edit Quick Reply' : 'Tambah Quick Reply'"></h3>
                        </div>

                        <form @submit.prevent="saveReply()" class="p-6 space-y-4">
                            {{-- Shortcut --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Shortcut <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-lg">/</span>
                                    <input 
                                        x-model="form.shortcut"
                                        type="text" 
                                        required
                                        maxlength="50"
                                        placeholder="contoh: greeting"
                                        class="w-full h-11 pl-8 pr-3 bg-[#111722] border rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        :class="errors.shortcut ? 'border-red-500' : 'border-[#232f48]'"
                                    >
                                </div>
                                <p class="text-xs text-red-400 mt-1.5" x-show="errors.shortcut" x-text="errors.shortcut"></p>
                                <p class="text-xs text-text-secondary mt-1.5" x-show="!errors.shortcut">Ketik / diikuti shortcut di chat untuk menggunakan template ini</p>
                            </div>

                            {{-- Category --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Kategori</label>
                                <input 
                                    x-model="form.category"
                                    type="text" 
                                    list="existing-categories"
                                    maxlength="50"
                                    placeholder="Pilih atau buat kategori baru"
                                    class="w-full h-11 px-3 bg-[#111722] border border-[#232f48] rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                >
                                <datalist id="existing-categories">
                                    @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </datalist>
                            </div>

                            {{-- Message --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-200 mb-2">Pesan <span class="text-red-500">*</span></label>
                                <textarea 
                                    x-model="form.message"
                                    required
                                    rows="5"
                                    maxlength="5000"
                                    placeholder="Tulis template pesan di sini..."
                                    class="w-full px-3 py-2.5 bg-[#111722] border rounded-lg text-sm text-white placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                    :class="errors.message ? 'border-red-500' : 'border-[#232f48]'"
                                ></textarea>
                                <p class="text-xs text-red-400 mt-1.5" x-show="errors.message" x-text="errors.message"></p>
                                <div class="flex justify-between mt-1.5">
                                    <p class="text-xs text-text-secondary">Template pesan yang akan dikirim ke pelanggan</p>
                                    <p class="text-xs text-text-secondary" x-text="form.message.length + '/5000'"></p>
                                </div>
                            </div>

                            {{-- Active Toggle --}}
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input 
                                        x-model="form.is_active"
                                        type="checkbox" 
                                        class="sr-only peer"
                                    >
                                    <div class="w-11 h-6 bg-[#232f48] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                                <span class="text-sm text-gray-200">Aktifkan template ini</span>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#232f48]">
                                <button 
                                    @click="closeForm()"
                                    type="button"
                                    class="px-5 py-2.5 text-sm font-medium text-gray-300 hover:text-white hover:bg-[#232f48] rounded-lg transition-colors"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="saving"
                                    class="px-5 py-2.5 bg-primary hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-lg transition-colors flex items-center gap-2 shadow-lg shadow-blue-900/20"
                                >
                                    <span x-show="saving" class="animate-spin material-symbols-outlined text-[18px]">progress_activity</span>
                                    <span x-text="saving ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Quick Reply')"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </template>

            {{-- Delete Confirmation Modal --}}
            <template x-teleport="body">
            <div 
                x-show="showDeleteConfirm"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-cloak
                class="fixed inset-0 z-[60] flex items-center justify-center p-4"
            >
                <div 
                    @click="showDeleteConfirm = false"
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                ></div>

                <div class="relative w-full max-w-sm bg-surface-dark border border-[#232f48] rounded-2xl shadow-2xl p-6 text-center modal-content-mobile">
                    <div class="w-14 h-14 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-2xl">delete_forever</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Hapus Quick Reply?</h3>
                    <p class="text-sm text-text-secondary mb-6">
                        Template <span class="text-white font-medium" x-text="replyToDelete ? '/' + replyToDelete.shortcut : ''"></span> akan dihapus secara permanen.
                    </p>
                    <div class="flex gap-3 justify-center">
                        <button 
                            @click="showDeleteConfirm = false"
                            type="button"
                            class="px-5 py-2.5 rounded-lg border border-[#232f48] text-gray-300 hover:text-white hover:bg-[#232f48] text-sm font-medium transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="deleteReply()"
                            :disabled="deleting"
                            type="button"
                            class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-bold shadow-lg shadow-red-900/20 transition-colors flex items-center gap-2"
                        >
                            <span x-show="deleting" class="animate-spin material-symbols-outlined text-[16px]">progress_activity</span>
                            <span x-text="deleting ? 'Menghapus...' : 'Hapus'"></span>
                        </button>
                    </div>
                </div>
            </div>
            </template>

            {{-- Toast Notification --}}
            <template x-teleport="body">
            <div 
                x-show="toast.show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                x-cloak
                class="fixed bottom-5 right-5 z-[70] rounded-xl bg-surface-dark border border-[#232f48] px-4 py-3 shadow-xl flex items-center gap-3"
            >
                <span 
                    class="material-symbols-outlined"
                    :class="toast.type === 'success' ? 'text-green-500' : 'text-red-500'"
                    x-text="toast.type === 'success' ? 'check_circle' : 'error'"
                ></span>
                <span class="text-sm text-white" x-text="toast.message"></span>
            </div>
            </template>

        </div>
    </main>
</div>

<script>
function quickRepliesManager() {
    return {
        quickReplies: @json($quickReplies->flatten() ?? []),
        categories: @json($categories ?? []),
        loading: false,
        filter: '',
        selectedCategory: '',
        
        // Modal states
        showForm: false,
        showDeleteConfirm: false,
        
        // Form
        isEditing: false,
        form: {
            id: null,
            shortcut: '',
            message: '',
            category: 'Umum',
            is_active: true
        },
        errors: {},
        saving: false,
        
        // Delete
        replyToDelete: null,
        deleting: false,
        
        // Toast
        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        get activeCount() {
            return this.quickReplies.filter(r => r.is_active).length;
        },

        get filteredReplies() {
            let filtered = this.quickReplies;
            
            if (this.selectedCategory) {
                filtered = filtered.filter(r => r.category === this.selectedCategory);
            }
            
            if (this.filter) {
                const q = this.filter.toLowerCase();
                filtered = filtered.filter(r => 
                    r.shortcut.toLowerCase().includes(q) ||
                    r.message.toLowerCase().includes(q) ||
                    r.category.toLowerCase().includes(q)
                );
            }
            
            return filtered.sort((a, b) => {
                // Sort by category first, then by shortcut
                if (a.category !== b.category) {
                    return a.category.localeCompare(b.category);
                }
                return a.shortcut.localeCompare(b.shortcut);
            });
        },

        init() {
            // Sync state across all instances
            window.addEventListener('quick-reply-saved', () => {
                this.loadQuickReplies();
            });
        },

        async loadQuickReplies() {
            this.loading = true;
            try {
                const response = await fetch('/api/quick-replies');
                const data = await response.json();
                
                if (data.success) {
                    const grouped = data.data;
                    this.quickReplies = [];
                    this.categories = Object.keys(grouped).sort();
                    
                    Object.entries(grouped).forEach(([category, items]) => {
                        items.forEach(item => {
                            this.quickReplies.push({ ...item, category });
                        });
                    });
                }
            } catch (error) {
                console.error('Failed to load quick replies:', error);
            } finally {
                this.loading = false;
            }
        },

        openCreateForm() {
            this.resetForm();
            this.isEditing = false;
            this.showForm = true;
        },

        closeForm() {
            this.showForm = false;
            this.errors = {};
        },

        resetForm() {
            this.form = {
                id: null,
                shortcut: '',
                message: '',
                category: 'Umum',
                is_active: true
            };
            this.errors = {};
        },

        editReply(reply) {
            this.form = {
                id: reply.id,
                shortcut: reply.shortcut,
                message: reply.message,
                category: reply.category,
                is_active: reply.is_active
            };
            this.isEditing = true;
            this.showForm = true;
        },

        async saveReply() {
            this.saving = true;
            this.errors = {};
            
            const url = this.isEditing ? `/settings/quick-replies/${this.form.id}` : '/settings/quick-replies';
            const method = this.isEditing ? 'PUT' : 'POST';
            
            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        shortcut: this.form.shortcut,
                        message: this.form.message,
                        category: this.form.category || 'Umum',
                        is_active: this.form.is_active
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = data.errors;
                        return;
                    }
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
                
                this.showToast(data.message, 'success');
                this.closeForm();
                this.loadQuickReplies();
                window.dispatchEvent(new Event('quick-reply-saved'));
                
            } catch (error) {
                this.showToast(error.message || 'Gagal menyimpan', 'error');
            } finally {
                this.saving = false;
            }
        },

        async toggleActive(reply) {
            try {
                const response = await fetch(`/settings/quick-replies/${reply.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        shortcut: reply.shortcut,
                        message: reply.message,
                        category: reply.category,
                        is_active: !reply.is_active
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    reply.is_active = !reply.is_active;
                    this.showToast(data.message, 'success');
                }
            } catch (error) {
                this.showToast('Gagal mengubah status', 'error');
            }
        },

        confirmDelete(reply) {
            this.replyToDelete = reply;
            this.showDeleteConfirm = true;
        },

        async deleteReply() {
            if (!this.replyToDelete) return;
            
            this.deleting = true;
            
            try {
                const response = await fetch(`/settings/quick-replies/${this.replyToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menghapus');
                }
                
                this.showToast(data.message, 'success');
                this.quickReplies = this.quickReplies.filter(r => r.id !== this.replyToDelete.id);
                window.dispatchEvent(new Event('quick-reply-saved'));
                
            } catch (error) {
                this.showToast(error.message, 'error');
            } finally {
                this.deleting = false;
                this.showDeleteConfirm = false;
                this.replyToDelete = null;
            }
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        }
    };
}
</script>

</body>
</html>
