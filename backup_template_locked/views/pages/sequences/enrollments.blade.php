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
<div class="flex h-screen w-full">
    <!-- Sidebar Navigation -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Top Header -->
        <header class="h-16 flex items-center justify-between px-6 lg:px-8 border-b border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <a href="{{ route('sequences.edit', $sequence) }}" class="p-2 -ml-2 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-bold text-lg dark:text-white">Kontak Terdaftar</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $sequence->name }}</p>
                </div>
            </div>
            <button onclick="openEnrollModal()" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                <span class="material-symbols-outlined" style="font-size: 18px;">person_add</span>
                Tambah Kontak Manual
            </button>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
            <div class="max-w-4xl mx-auto flex flex-col gap-6">
                
                <!-- Success Message -->
                @if(session('success'))
                    <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">error</span>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Stats Card -->
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-surface-dark rounded-xl border border-slate-800 p-4 text-center shadow-sm">
                        <p class="text-2xl font-bold text-primary">{{ $sequence->total_enrolled }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Terdaftar</p>
                    </div>
                    <div class="bg-surface-dark rounded-xl border border-slate-800 p-4 text-center shadow-sm">
                        <p class="text-2xl font-bold text-amber-500">{{ $enrollments->where('status', 'active')->count() }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Sedang Aktif</p>
                    </div>
                    <div class="bg-surface-dark rounded-xl border border-slate-800 p-4 text-center shadow-sm">
                        <p class="text-2xl font-bold text-green-500">{{ $enrollments->where('status', 'completed')->count() }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Selesai</p>
                    </div>
                    <div class="bg-surface-dark rounded-xl border border-slate-800 p-4 text-center shadow-sm">
                        <p class="text-2xl font-bold text-red-500">{{ $enrollments->where('status', 'cancelled')->count() }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Dibatalkan</p>
                    </div>
                </div>

                <!-- Enrollments Table -->
                <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50">
                        <h2 class="font-semibold dark:text-white text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">group</span>
                            Daftar Kontak ({{ $enrollments->total() }})
                        </h2>
                    </div>
                    
                    @if($enrollments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 dark:bg-slate-800/30">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kontak</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Platform</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Progress</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Next Run</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                    @foreach($enrollments as $enrollment)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="size-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white font-bold text-sm">
                                                        {{ strtoupper(substr($enrollment->contact_name ?? $enrollment->contact_identifier, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-medium dark:text-white text-slate-900 text-sm">{{ $enrollment->contact_name ?? '-' }}</p>
                                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $enrollment->contact_identifier }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium
                                                    {{ $enrollment->platform === 'whatsapp' ? 'bg-green-500/10 text-green-500' : '' }}
                                                    {{ $enrollment->platform === 'instagram' ? 'bg-pink-500/10 text-pink-500' : '' }}
                                                    {{ $enrollment->platform === 'web' ? 'bg-blue-500/10 text-blue-500' : '' }}
                                                ">
                                                    <span class="material-symbols-outlined" style="font-size: 14px;">
                                                        {{ $enrollment->platform === 'whatsapp' ? 'chat' : ($enrollment->platform === 'instagram' ? 'photo_camera' : 'public') }}
                                                    </span>
                                                    {{ ucfirst($enrollment->platform) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-1 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                        <div class="h-full bg-primary rounded-full" style="width: {{ $enrollment->progress_percent }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $enrollment->current_step_number }}/{{ $enrollment->total_steps }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $statusColors = [
                                                        'active' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                                        'completed' => 'bg-green-500/10 text-green-500 border-green-500/20',
                                                        'paused' => 'bg-slate-500/10 text-slate-500 border-slate-500/20',
                                                        'cancelled' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                                    ];
                                                @endphp
                                                <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $statusColors[$enrollment->status] ?? '' }}">
                                                    {{ $enrollment->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($enrollment->next_run_at)
                                                    <p class="text-xs dark:text-white text-slate-900">{{ $enrollment->next_run_at->format('d M Y') }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $enrollment->next_run_at->format('H:i') }}</p>
                                                @else
                                                    <span class="text-xs text-slate-500 dark:text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @if($enrollment->status === 'active')
                                                    <form action="{{ route('sequences.enrollment.cancel', $enrollment) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan enrollment ini?')">
                                                        @csrf
                                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                            <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-slate-500">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($enrollments->hasPages())
                            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700/50">
                                {{ $enrollments->links() }}
                            </div>
                        @endif
                    @else
                        <div class="p-12 text-center">
                            <div class="size-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="material-symbols-outlined text-slate-400 text-3xl">group</span>
                            </div>
                            <h3 class="dark:text-white text-slate-900 font-bold text-lg mb-2">Belum Ada Kontak</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Belum ada kontak yang terdaftar dalam sequence ini.</p>
                            <button onclick="openEnrollModal()" 
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                                <span class="material-symbols-outlined" style="font-size: 18px;">person_add</span>
                                Tambah Kontak Manual
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Manual Enroll Modal -->
<div id="enroll-modal" class="hidden fixed inset-0 z-50">
    <div onclick="closeEnrollModal()" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl bg-surface-dark border border-slate-700 shadow-2xl">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-700/50">
                <div>
                    <h3 class="text-xl font-bold dark:text-white text-slate-900">Tambah Kontak Manual</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftarkan kontak ke sequence ini</p>
                </div>
                <button type="button" onclick="closeEnrollModal()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('sequences.enroll', $sequence) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Platform <span class="text-red-500">*</span></label>
                    <select name="platform" required
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 focus:ring-2 focus:ring-primary/50 focus:border-primary/50">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="instagram">Instagram</option>
                        <option value="web">Web Widget</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Identifier Kontak <span class="text-red-500">*</span></label>
                    <input type="text" name="contact_identifier" required
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                           placeholder="No. HP (628xxx) atau IG User ID"/>
                    <p class="text-xs text-slate-500 mt-1">Untuk WhatsApp: gunakan format 628xxx tanpa + atau spasi</p>
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Nama Kontak (Opsional)</label>
                    <input type="text" name="contact_name"
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                           placeholder="Nama kontak"/>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEnrollModal()" class="px-4 py-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                        <span class="material-symbols-outlined" style="font-size: 18px;">person_add</span>
                        Daftarkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEnrollModal() {
        document.getElementById('enroll-modal').classList.remove('hidden');
    }
    
    function closeEnrollModal() {
        document.getElementById('enroll-modal').classList.add('hidden');
    }
</script>

</body>
</html>
