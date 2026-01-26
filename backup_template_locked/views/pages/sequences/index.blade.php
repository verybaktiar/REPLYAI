<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>REPLYAI - {{ $title }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Theme Configuration -->
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                        "surface-dark": "#1a2230", 
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
        ::-webkit-scrollbar-thumb { background: #282e39; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden antialiased">
<div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Sidebar Navigation -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
        <!-- Top Header - Desktop only -->
        <header class="hidden lg:flex h-16 items-center justify-between px-6 lg:px-8 border-b border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="font-bold text-lg dark:text-white">Follow-up Otomatis</h1>
                        @include('components.page-help', [
                            'title' => 'Follow-up Otomatis (Sequence)',
                            'description' => 'Kirim pesan otomatis secara berurutan untuk follow-up pelanggan.',
                            'tips' => [
                                'Buat sequence untuk welcome message baru',
                                'Atur delay waktu antar pesan (menit/jam/hari)',
                                'Lihat berapa kontak yang terdaftar di sequence',
                                'Aktifkan/nonaktifkan sequence sesuai kebutuhan'
                            ]
                        ])
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pesan otomatis berurutan untuk follow-up</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('sequences.create') }}" 
                   class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                    <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                    Buat Sequence Baru
                </a>
            </div>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
            <div class="max-w-6xl mx-auto flex flex-col gap-8">
                
                <!-- Success Message -->
                @if(session('success'))
                    <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Info Card -->
                <div class="bg-gradient-to-r from-primary/10 to-purple-500/10 border border-primary/20 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-primary/20 rounded-lg">
                            <span class="material-symbols-outlined text-primary text-2xl">timeline</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-white mb-1">Apa itu Sequence?</h3>
                            <p class="text-slate-400 text-sm">Sequence adalah serangkaian pesan otomatis yang dikirim berdasarkan waktu atau trigger tertentu. Gunakan untuk welcome series, follow-up leads, reminder appointment, dan nurturing customer.</p>
                        </div>
                    </div>
                </div>

                <!-- Sequences Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($sequences as $sequence)
                        <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden hover:border-primary dark:hover:border-primary transition-colors shadow-sm group">
                            <!-- Sequence Header -->
                            <div class="p-5 border-b border-slate-100 dark:border-slate-700/50">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-lg bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white shadow-lg">
                                            <span class="material-symbols-outlined" style="font-size: 20px;">timeline</span>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold dark:text-white text-slate-900">{{ $sequence->name }}</h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $sequence->trigger_type_label }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $sequence->is_active ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-slate-500/10 text-slate-500 border border-slate-500/20' }}">
                                        {{ $sequence->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>

                                @if($sequence->description)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 line-clamp-2">{{ $sequence->description }}</p>
                                @endif
                                
                                <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">format_list_numbered</span>
                                        {{ $sequence->steps_count }} langkah
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">{{ $sequence->platform === 'all' ? 'devices' : ($sequence->platform === 'whatsapp' ? 'chat' : 'public') }}</span>
                                        {{ $sequence->platform_label }}
                                    </span>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/30 grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <p class="text-lg font-bold text-primary">{{ $sequence->total_enrolled }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Terdaftar</p>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-amber-500">{{ $sequence->active_enrollments_count }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Aktif</p>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-green-500">{{ $sequence->completed_enrollments_count }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Selesai</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="p-4 border-t border-slate-100 dark:border-slate-700/50">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('sequences.enrollments', $sequence) }}" 
                                       class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-primary/10 text-primary border border-primary/30 rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">group</span>
                                        Lihat Kontak
                                    </a>
                                    <a href="{{ route('sequences.edit', $sequence) }}" 
                                       class="flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-600 rounded-lg text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                                    </a>
                                    <button onclick="toggleSequence({{ $sequence->id }})" 
                                            class="flex items-center justify-center gap-1.5 px-3 py-2 {{ $sequence->is_active ? 'bg-amber-500/10 text-amber-500 border border-amber-500/30 hover:bg-amber-500/20' : 'bg-green-500/10 text-green-500 border border-green-500/30 hover:bg-green-500/20' }} rounded-lg text-xs font-medium transition-colors">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">{{ $sequence->is_active ? 'pause' : 'play_arrow' }}</span>
                                    </button>
                                    <form action="{{ route('sequences.destroy', $sequence) }}" method="POST" class="inline" onsubmit="return confirm('Hapus sequence ini? Semua enrollment juga akan dihapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center justify-center gap-1.5 px-3 py-2 bg-red-500/10 text-red-500 border border-red-500/30 rounded-lg text-xs font-medium hover:bg-red-500/20 transition-colors">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                            <div class="size-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined text-slate-400 text-4xl">timeline</span>
                            </div>
                            <h3 class="dark:text-white text-slate-900 font-bold text-lg mb-2">Belum Ada Sequence</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 max-w-sm">Buat sequence pertama Anda untuk mulai mengotomatisasi pesan follow-up.</p>
                            <a href="{{ route('sequences.create') }}" 
                               class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                                Buat Sequence Pertama
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- How It Works -->
                <div class="bg-surface-dark rounded-xl border border-slate-800 p-6 shadow-sm">
                    <h2 class="text-lg font-bold dark:text-white text-slate-900 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">help</span>
                        Cara Kerja Sequence
                    </h2>
                    <div class="grid md:grid-cols-4 gap-6">
                        <div class="flex gap-3">
                            <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-sm">1</div>
                            <div>
                                <h3 class="font-medium dark:text-white text-slate-900 text-sm mb-1">Buat Sequence</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Tentukan nama, trigger, dan platform target.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-sm">2</div>
                            <div>
                                <h3 class="font-medium dark:text-white text-slate-900 text-sm mb-1">Tambah Langkah</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Buat pesan-pesan dengan delay (menit/jam/hari).</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-sm">3</div>
                            <div>
                                <h3 class="font-medium dark:text-white text-slate-900 text-sm mb-1">Aktifkan</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Sequence akan berjalan otomatis berdasarkan trigger.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-sm">4</div>
                            <div>
                                <h3 class="font-medium dark:text-white text-slate-900 text-sm mb-1">Pantau</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Lihat statistik dan kontak yang terdaftar.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    async function toggleSequence(id) {
        try {
            const response = await fetch(`/sequences/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                window.location.reload();
            }
        } catch (error) {
            console.error('Error toggling sequence:', error);
        }
    }
</script>

</body>
</html>
