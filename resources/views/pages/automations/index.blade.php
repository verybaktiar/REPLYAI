<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat Automation - ReplyAI</title>
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
        
        .toggle-checkbox:checked { right: 0; border-color: #135bec; }
        .toggle-checkbox:checked + .toggle-label { background-color: #135bec; }
        
        .automation-card {
            transition: all 0.2s ease;
        }
        .automation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px -10px rgba(19, 91, 236, 0.2);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden" x-data="automationApp()">
<div class="flex flex-col lg:flex-row h-screen w-full">
    @include('components.sidebar')

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Header -->
        <header class="h-14 border-b border-gray-800 bg-background-dark/80 backdrop-blur-md flex items-center justify-between px-6 z-20 shrink-0">
            <div class="flex items-center gap-2 text-text-secondary text-xs font-bold uppercase tracking-widest">
                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1 bg-green-500/10 rounded-full border border-green-500/20">
                    <div class="size-1.5 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold text-green-500 uppercase tracking-widest">System Online</span>
                </div>
                @include('components.language-switcher')
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 lg:px-12">
            <div class="max-w-[1400px] mx-auto flex flex-col gap-6">
                <!-- Page Heading -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="flex flex-col gap-2 max-w-2xl">
                        <div class="flex items-center gap-3">
                            <h1 class="text-white text-3xl md:text-4xl font-black leading-tight tracking-tight">Chat Automation</h1>
                            @include('components.page-help', [
                                'title' => 'Panduan Chat Automation',
                                'description' => 'Kelola pesan otomatis untuk berbagai skenario',
                                'tips' => [
                                    'Welcome Message: Dikirim saat kontak baru pertama chat',
                                    'Away Message: Balasan otomatis di luar jam kerja',
                                    'Keyword Reply: Balasan berdasarkan kata kunci',
                                    'Follow-up: Pesan tindak lanjut setelah tidak ada balasan'
                                ]
                            ])
                        </div>
                        <p class="text-text-secondary text-base font-normal">
                            Kelola pesan otomatis untuk welcome, away, keyword, dan follow-up
                        </p>
                    </div>
                    <a href="{{ route('automations.create') }}" class="flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-lg h-12 px-6 bg-primary hover:bg-blue-600 transition-colors text-white text-sm font-bold shadow-lg shadow-blue-900/20">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        <span>Buat Automation</span>
                    </a>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-surface-dark rounded-xl p-4 border border-surface-lighter">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="size-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-500">auto_awesome</span>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-white">{{ $stats['total'] }}</p>
                                <p class="text-xs text-text-secondary">Total Automation</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark rounded-xl p-4 border border-surface-lighter">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="size-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-500">check_circle</span>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-white">{{ $stats['active'] }}</p>
                                <p class="text-xs text-text-secondary">Aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark rounded-xl p-4 border border-surface-lighter">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="size-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-purple-500">trending_up</span>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-white">{{ $stats['triggered'] }}</p>
                                <p class="text-xs text-text-secondary">Total Triggered</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-surface-dark rounded-xl p-4 border border-surface-lighter">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="size-10 rounded-lg bg-orange-500/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-orange-500">schedule</span>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-white">{{ $stats['by_type']['follow_up'] }}</p>
                                <p class="text-xs text-text-secondary">Follow-up</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2">
                    <button @click="filter = 'all'" 
                            :class="filter === 'all' ? 'bg-primary text-white' : 'bg-surface-dark text-text-secondary hover:text-white'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
                        Semua ({{ $stats['total'] }})
                    </button>
                    <button @click="filter = 'welcome'" 
                            :class="filter === 'welcome' ? 'bg-primary text-white' : 'bg-surface-dark text-text-secondary hover:text-white'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">waving_hand</span>
                        Welcome
                    </button>
                    <button @click="filter = 'away'" 
                            :class="filter === 'away' ? 'bg-primary text-white' : 'bg-surface-dark text-text-secondary hover:text-white'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">schedule</span>
                        Away
                    </button>
                    <button @click="filter = 'keyword'" 
                            :class="filter === 'keyword' ? 'bg-primary text-white' : 'bg-surface-dark text-text-secondary hover:text-white'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">key</span>
                        Keyword
                    </button>
                    <button @click="filter = 'follow_up'" 
                            :class="filter === 'follow_up' ? 'bg-primary text-white' : 'bg-surface-dark text-text-secondary hover:text-white'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">follow_the_signs</span>
                        Follow-up
                    </button>
                </div>

                <!-- Automations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @forelse($automations as $automation)
                    <div x-show="filter === 'all' || filter === '{{ $automation->type }}'"
                         x-transition
                         class="automation-card bg-surface-dark rounded-xl border border-surface-lighter overflow-hidden group"
                         data-type="{{ $automation->type }}">
                        
                        <!-- Card Header -->
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-12 rounded-xl flex items-center justify-center
                                        {{ $automation->type === 'welcome' ? 'bg-green-500/10 text-green-500' : '' }}
                                        {{ $automation->type === 'away' ? 'bg-orange-500/10 text-orange-500' : '' }}
                                        {{ $automation->type === 'keyword' ? 'bg-blue-500/10 text-blue-500' : '' }}
                                        {{ $automation->type === 'follow_up' ? 'bg-purple-500/10 text-purple-500' : '' }}
                                    ">
                                        <span class="material-symbols-outlined text-2xl">
                                            {{ $automation->type === 'welcome' ? 'waving_hand' : '' }}
                                            {{ $automation->type === 'away' ? 'schedule' : '' }}
                                            {{ $automation->type === 'keyword' ? 'key' : '' }}
                                            {{ $automation->type === 'follow_up' ? 'follow_the_signs' : '' }}
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white">{{ $automation->name }}</h3>
                                        <span class="text-xs text-text-secondary capitalize">{{ str_replace('_', ' ', $automation->type) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Toggle Switch -->
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           class="sr-only peer" 
                                           :checked="{{ $automation->is_active ? 'true' : 'false' }}"
                                           @change="toggleStatus({{ $automation->id }}, $event.target.checked)">
                                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer 
                                                peer-checked:after:translate-x-full peer-checked:after:border-white 
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                                after:bg-white after:border-gray-300 after:border after:rounded-full 
                                                after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                    </div>
                                </label>
                            </div>

                            <!-- Message Preview -->
                            <div class="bg-[#111722] rounded-lg p-3 mb-4">
                                <p class="text-sm text-gray-300 line-clamp-3">{{ $automation->message }}</p>
                            </div>

                            <!-- Type Specific Info -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                @if($automation->type === 'away' && $automation->away_start_time && $automation->away_end_time)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-orange-500/10 text-orange-400 text-xs rounded-lg">
                                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                                        {{ $automation->away_start_time->format('H:i') }} - {{ $automation->away_end_time->format('H:i') }}
                                    </span>
                                    @if($automation->away_days)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-surface-lighter text-text-secondary text-xs rounded-lg">
                                            {{ count($automation->away_days) }} hari
                                        </span>
                                    @endif
                                @endif

                                @if($automation->type === 'keyword' && $automation->keywords)
                                    @foreach(array_slice($automation->keywords, 0, 3) as $keyword)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-500/10 text-blue-400 text-xs rounded-lg">
                                            #{{ $keyword }}
                                        </span>
                                    @endforeach
                                    @if(count($automation->keywords) > 3)
                                        <span class="inline-flex items-center px-2 py-1 bg-surface-lighter text-text-secondary text-xs rounded-lg">
                                            +{{ count($automation->keywords) - 3 }}
                                        </span>
                                    @endif
                                @endif

                                @if($automation->type === 'follow_up' && $automation->delay_hours)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-500/10 text-purple-400 text-xs rounded-lg">
                                        <span class="material-symbols-outlined text-[14px]">timer</span>
                                        {{ $automation->delay_hours }} jam
                                    </span>
                                @endif
                            </div>

                            <!-- Stats & Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-surface-lighter">
                                <div class="flex items-center gap-4 text-xs text-text-secondary">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">play_arrow</span>
                                        {{ $automation->trigger_count }}x triggered
                                    </span>
                                    @if($automation->last_triggered_at)
                                        <span class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">schedule</span>
                                            {{ $automation->last_triggered_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('automations.edit', $automation) }}" 
                                       class="p-2 rounded-lg hover:bg-surface-lighter text-text-secondary hover:text-white transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <button @click="deleteAutomation({{ $automation->id }}, '{{ $automation->name }}')" 
                                            class="p-2 rounded-lg hover:bg-red-500/10 text-text-secondary hover:text-red-500 transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-12">
                        <x-empty-state 
                            icon="auto_awesome" 
                            title="Belum ada automation" 
                            description="Buat automation pertama Anda untuk mengotomatisasi chat"
                            actionLabel="Buat Automation"
                            actionUrl="{{ route('automations.create') }}"
                        />
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Toast Notification -->
<div x-show="toast.show" 
     x-transition
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
            Automation "<span x-text="deleteModal.name"></span>" akan dihapus permanen.
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
function automationApp() {
    return {
        filter: 'all',
        toast: { show: false, message: '', type: 'success' },
        deleteModal: { show: false, id: null, name: '', loading: false },
        
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 3000);
        },

        async toggleStatus(id, isActive) {
            try {
                const response = await fetch(`/automations/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    this.showToast(`Automation ${isActive ? 'diaktifkan' : 'dinonaktifkan'}`, 'success');
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                this.showToast('Gagal mengubah status', 'error');
                event.target.checked = !isActive;
            }
        },

        deleteAutomation(id, name) {
            this.deleteModal = { show: true, id, name, loading: false };
        },

        async confirmDelete() {
            this.deleteModal.loading = true;
            
            try {
                const response = await fetch(`/automations/${this.deleteModal.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    this.showToast('Automation berhasil dihapus', 'success');
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                this.showToast('Gagal menghapus automation', 'error');
            } finally {
                this.deleteModal.loading = false;
                this.deleteModal.show = false;
            }
        }
    }
}
</script>
</body>
</html>
