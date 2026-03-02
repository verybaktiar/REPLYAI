<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Buat Segment Baru - REPLYAI</title>
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
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111722; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #324467; border-radius: 10px; }
        
        .color-option input:checked + div {
            ring-width: 2px;
            ring-color: white;
            transform: scale(1.1);
        }
        
        .filter-chip {
            animation: slideIn 0.2s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row" x-data="createSegmentApp()">

<!-- Sidebar Navigation -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 lg:p-10 pb-20">
        <div class="max-w-[800px] mx-auto flex flex-col gap-8">
            
            <!-- Header -->
            <div class="flex items-center gap-4">
                <a href="{{ route('segments.index') }}" class="p-2 text-text-secondary hover:text-white rounded-lg hover:bg-white/5 transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-white">Buat Segment Baru</h2>
                    <p class="text-text-secondary text-sm">Buat kelompok kontak dengan kriteria tertentu.</p>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('segments.store') }}" method="POST" class="flex flex-col gap-6" @submit.prevent="submitForm">
                @csrf
                
                <!-- Basic Info Card -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-6">
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">info</span>
                        Informasi Dasar
                    </h3>
                    
                    <div class="flex flex-col gap-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-2">Nama Segment <span class="text-red-400">*</span></label>
                            <input type="text" name="name" x-model="form.name" required
                                   class="w-full bg-background-dark border border-border-dark rounded-lg px-4 py-2.5 text-white placeholder-text-secondary/50 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary"
                                   placeholder="Contoh: Pelanggan VIP, Lead Baru, dll">
                            @error('name')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-2">Deskripsi</label>
                            <textarea name="description" x-model="form.description" rows="3"
                                      class="w-full bg-background-dark border border-border-dark rounded-lg px-4 py-2.5 text-white placeholder-text-secondary/50 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary resize-none"
                                      placeholder="Deskripsi singkat tentang segment ini..."></textarea>
                        </div>
                        
                        <!-- Color Selection -->
                        <div>
                            <label class="block text-sm font-medium text-text-secondary mb-3">Warna Segment <span class="text-red-400">*</span></label>
                            <div class="flex flex-wrap gap-3">
                                @foreach($colors as $colorName => $hex)
                                <label class="color-option cursor-pointer">
                                    <input type="radio" name="color" value="{{ $colorName }}" x-model="form.color" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                                    <div class="size-10 rounded-xl transition-all hover:scale-110" style="background-color: {{ $hex }}" title="{{ $colorName }}"></div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Segment Type Card -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-6">
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">settings</span>
                        Tipe Segment
                    </h3>
                    
                    <div class="flex flex-col gap-3">
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-border-dark cursor-pointer transition-colors hover:bg-white/5" :class="{ 'border-primary bg-primary/5': form.is_auto_update === false }">
                            <input type="radio" name="is_auto_update" value="0" x-model="form.is_auto_update" class="mt-1 text-primary">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-amber-400">touch_app</span>
                                    <span class="font-medium text-white">Manual</span>
                                </div>
                                <p class="text-sm text-text-secondary mt-1">Anda menambahkan dan menghapus kontak secara manual. Cocok untuk segment tetap seperti "VIP Customers".</p>
                            </div>
                        </label>
                        
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-border-dark cursor-pointer transition-colors hover:bg-white/5" :class="{ 'border-primary bg-primary/5': form.is_auto_update === true }">
                            <input type="radio" name="is_auto_update" value="1" x-model="form.is_auto_update" class="mt-1 text-primary">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-emerald-400">auto_mode</span>
                                    <span class="font-medium text-white">Auto Update</span>
                                </div>
                                <p class="text-sm text-text-secondary mt-1">Kontak otomatis masuk/keluar berdasarkan filter criteria. Cocok untuk segment dinamis seperti "Active Users".</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Filters Card (only for auto-update) -->
                <div x-show="form.is_auto_update" x-collapse class="bg-surface-dark border border-border-dark rounded-xl p-6">
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">filter_list</span>
                        Filter Criteria
                    </h3>
                    
                    @include('components.segments.filter-builder', ['tags' => $tags, 'customFields' => $customFields])
                </div>
                
                <!-- Preview Card (only for auto-update with filters) -->
                <div x-show="form.is_auto_update && hasFilters()" x-collapse class="bg-surface-dark border border-border-dark rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">preview</span>
                            Preview Kontak
                        </h3>
                        <button type="button" @click="updatePreview" 
                                class="text-sm text-primary hover:text-white flex items-center gap-1 transition-colors"
                                :disabled="previewLoading">
                            <span class="material-symbols-outlined text-base" :class="{ 'animate-spin': previewLoading }">refresh</span>
                            Refresh
                        </button>
                    </div>
                    
                    <div x-show="previewLoading" class="py-8 text-center">
                        <span class="material-symbols-outlined text-3xl text-text-secondary animate-spin">refresh</span>
                        <p class="text-text-secondary text-sm mt-2">Menghitung kontak...</p>
                    </div>
                    
                    <div x-show="!previewLoading && previewData" class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="size-16 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-2xl font-bold text-primary" x-text="previewData?.count || 0"></span>
                            </div>
                            <div>
                                <p class="text-white font-medium">Kontak Cocok</p>
                                <p class="text-text-secondary text-sm">Kontak yang memenuhi filter criteria</p>
                            </div>
                        </div>
                        
                        <template x-if="previewData?.sample?.length > 0">
                            <div>
                                <p class="text-xs text-text-secondary uppercase tracking-wider mb-2">Contoh Kontak:</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="contact in previewData.sample" :key="contact.id">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-background-dark border border-border-dark text-sm text-text-secondary">
                                            <span class="material-symbols-outlined text-xs" x-text="contact.platform === 'whatsapp' ? 'chat' : 'photo_camera'"></span>
                                            <span x-text="contact.name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <a href="{{ route('segments.index') }}" class="px-6 py-2.5 text-text-secondary hover:text-white font-medium transition-colors">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-primary hover:bg-blue-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                            :disabled="submitting">
                        <span x-show="submitting" class="material-symbols-outlined text-base animate-spin">refresh</span>
                        <span x-text="submitting ? 'Menyimpan...' : 'Buat Segment'"></span>
                    </button>
                </div>
                
            </form>

        </div>
    </div>
</main>

<script>
function createSegmentApp() {
    return {
        form: {
            name: '',
            description: '',
            color: 'blue',
            is_auto_update: false,
            filters: {
                platform: 'both',
                tags: [],
                last_active_days: null,
                message_count_min: null,
                message_count_max: null,
                custom_fields: []
            }
        },
        submitting: false,
        previewLoading: false,
        previewData: null,
        
        init() {
            this.$watch('form.filters', () => {
                this.debouncePreview();
            }, { deep: true });
            
            this.$watch('form.is_auto_update', (value) => {
                if (value && this.hasFilters()) {
                    this.updatePreview();
                }
            });
        },
        
        hasFilters() {
            const f = this.form.filters;
            return f.platform !== 'both' || 
                   (f.tags && f.tags.length > 0) || 
                   f.last_active_days || 
                   f.message_count_min !== null || 
                   f.message_count_max !== null;
        },
        
        debouncePreview() {
            if (this.previewTimeout) clearTimeout(this.previewTimeout);
            this.previewTimeout = setTimeout(() => {
                if (this.form.is_auto_update && this.hasFilters()) {
                    this.updatePreview();
                }
            }, 500);
        },
        
        async updatePreview() {
            this.previewLoading = true;
            
            try {
                const response = await fetch('{{ route("segments.preview-filters") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ filters: this.form.filters })
                });
                
                const data = await response.json();
                this.previewData = data;
            } catch (error) {
                console.error('Preview error:', error);
            } finally {
                this.previewLoading = false;
            }
        },
        
        submitForm(e) {
            this.submitting = true;
            
            // Add filters to form data if auto-update
            if (this.form.is_auto_update) {
                const filtersInput = document.createElement('input');
                filtersInput.type = 'hidden';
                filtersInput.name = 'filters';
                filtersInput.value = JSON.stringify(this.form.filters);
                e.target.appendChild(filtersInput);
            }
            
            e.target.submit();
        }
    }
}
</script>

</body>
</html>
