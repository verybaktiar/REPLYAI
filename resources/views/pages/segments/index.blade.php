<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Contact Segments - REPLYAI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
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
        
        .segment-card {
            transition: all 0.2s ease;
        }
        .segment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px -10px rgba(19, 91, 236, 0.2);
        }
        
        .color-badge {
            box-shadow: 0 0 0 2px rgba(255,255,255,0.1);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row" x-data="segmentsApp()">

<!-- Sidebar Navigation -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 lg:p-10 pb-20">
        <div class="max-w-[1200px] mx-auto flex flex-col gap-8">
            
            <!-- Header -->
            <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-gradient-to-br from-primary/30 to-purple-500/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">folder_special</span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em] text-white">Segment Kontak</h2>
                    </div>
                    
                    @include('components.page-help', [
                        'title' => 'Segmentasi Pelanggan',
                        'description' => 'Kelompokkan pelanggan berdasarkan kriteria tertentu untuk targeting yang lebih efektif.',
                        'tips' => ['Buat segment baru dengan filter kustom', 'Filter berdasarkan tag, platform, atau aktivitas', 'Gunakan segment untuk broadcast ter-target', 'Edit atau hapus segment yang sudah tidak digunakan']
                    ])
                    
                    <p class="text-text-secondary text-base font-normal">Kelompokkan kontak berdasarkan kriteria tertentu untuk target broadcast dan analisis.</p>
                </div>
                
                <!-- Actions -->
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('segments.create') }}" class="bg-primary hover:bg-blue-600 text-white font-medium rounded-lg text-sm px-4 py-2.5 flex items-center gap-2 transition-colors">
                        <span class="material-symbols-outlined" style="font-size: 20px;">add</span>
                        Buat Segment Baru
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Total Segments -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-4 flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-blue-500/20 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-blue-400">folder_copy</span>
                    </div>
                    <div>
                        <p class="text-text-secondary text-xs uppercase tracking-wider">Total Segment</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_segments'] }}</p>
                    </div>
                </div>
                
                <!-- Auto Update -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-4 flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-emerald-400">auto_mode</span>
                    </div>
                    <div>
                        <p class="text-text-secondary text-xs uppercase tracking-wider">Auto Update</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['auto_update_segments'] }}</p>
                    </div>
                </div>
                
                <!-- Manual -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-4 flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-amber-500/20 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-amber-400">touch_app</span>
                    </div>
                    <div>
                        <p class="text-text-secondary text-xs uppercase tracking-wider">Manual</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['manual_segments'] }}</p>
                    </div>
                </div>
                
                <!-- Total Contacts -->
                <div class="bg-surface-dark border border-border-dark rounded-xl p-4 flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-purple-500/20 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-purple-400">group</span>
                    </div>
                    <div>
                        <p class="text-text-secondary text-xs uppercase tracking-wider">Kontak Tersegmentasi</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total_contacts_in_segments'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Segments Grid -->
            @if($segments->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($segments as $segment)
                <div class="segment-card bg-surface-dark border border-border-dark rounded-xl p-5 relative group">
                    <!-- Color Indicator -->
                    <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl" style="background-color: {{ $segment->color_hex }}"></div>
                    
                    <div class="flex items-start justify-between mt-2">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg flex items-center justify-center" style="background-color: {{ $segment->color_hex }}20">
                                <span class="material-symbols-outlined" style="color: {{ $segment->color_hex }}">folder</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white text-lg">{{ $segment->name }}</h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($segment->is_auto_update)
                                        <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[10px]">auto_mode</span>
                                            Auto
                                        </span>
                                    @else
                                        <span class="text-[10px] bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[10px]">touch_app</span>
                                            Manual
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-1.5 text-text-secondary hover:text-white rounded-lg hover:bg-white/5">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak 
                                 class="absolute right-0 top-full mt-1 w-40 bg-surface-dark border border-border-dark rounded-lg shadow-xl z-10 py-1">
                                <a href="{{ route('segments.show', $segment) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-text-secondary hover:text-white hover:bg-white/5">
                                    <span class="material-symbols-outlined text-base">visibility</span>
                                    Lihat Detail
                                </a>
                                <a href="{{ route('segments.edit', $segment) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-text-secondary hover:text-white hover:bg-white/5">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                    Edit
                                </a>
                                <form action="{{ route('segments.destroy', $segment) }}" method="POST" class="block" onsubmit="return confirm('Yakin ingin menghapus segment ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    @if($segment->description)
                    <p class="text-text-secondary text-sm mt-3 line-clamp-2">{{ $segment->description }}</p>
                    @endif
                    
                    <!-- Stats -->
                    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-border-dark">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-text-secondary text-base">group</span>
                            <span class="text-sm text-white font-medium">{{ $segment->members_count }}</span>
                            <span class="text-xs text-text-secondary">kontak</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-text-secondary text-base">schedule</span>
                            <span class="text-xs text-text-secondary">{{ $segment->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    
                    <!-- Quick Action -->
                    <a href="{{ route('segments.show', $segment) }}" class="mt-4 flex items-center justify-center gap-2 w-full py-2 rounded-lg text-sm font-medium transition-colors"
                       style="background-color: {{ $segment->color_hex }}15; color: {{ $segment->color_hex }}"
                       onmouseover="this.style.backgroundColor='{{ $segment->color_hex }}25'"
                       onmouseout="this.style.backgroundColor='{{ $segment->color_hex }}15'">
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                        Lihat Kontak
                    </a>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $segments->links() }}
            </div>
            @else
            <!-- Empty State -->
            <div class="bg-surface-dark border border-border-dark rounded-xl p-12 text-center">
                <div class="size-20 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-4xl text-text-secondary">folder_open</span>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Belum Ada Segment</h3>
                <p class="text-text-secondary mb-6 max-w-md mx-auto">Buat segment untuk mengelompokkan kontak berdasarkan kriteria tertentu. Segment dapat digunakan untuk target broadcast dan analisis.</p>
                <a href="{{ route('segments.create') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-600 text-white font-medium rounded-lg text-sm px-6 py-3 transition-colors">
                    <span class="material-symbols-outlined">add</span>
                    Buat Segment Pertama
                </a>
            </div>
            @endif

        </div>
    </div>
</main>

<script>
function segmentsApp() {
    return {
        init() {
            // Initialize
        }
    }
}
</script>

</body>
</html>
