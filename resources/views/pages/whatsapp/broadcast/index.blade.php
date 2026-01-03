<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>WhatsApp Broadcast - REPLYAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "whatsapp": "#25D366",
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
</head>
<body class="bg-background-dark font-display text-white overflow-hidden h-screen flex">
    
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto">
        
        <div class="p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold">Broadcast Messages</h1>
                    <p class="text-text-secondary mt-1">Kirim pesan massal ke kontak WhatsApp Anda dengan aman.</p>
                </div>
                <a href="{{ route('whatsapp.broadcast.create') }}" class="flex items-center gap-2 bg-primary hover:bg-blue-600 px-4 py-2 rounded-lg font-medium transition-colors">
                    <span class="material-symbols-outlined">add</span>
                    Buat Broadcast Baru
                </a>
            </div>

            <!-- Stats/Alert -->
            <div class="bg-blue-900/20 border border-blue-500/30 p-4 rounded-xl mb-8 flex items-start gap-3">
                <span class="material-symbols-outlined text-blue-400">info</span>
                <div>
                    <h3 class="font-bold text-blue-400 mb-1">Queue Worker Required</h3>
                    <p class="text-sm text-text-secondary">Pastikan command <code>php artisan queue:work</code> berjalan di server agar pesan dapat terkirim secara background.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-900/20 border border-green-500/30 p-4 rounded-xl mb-8 flex items-start gap-3 text-green-400">
                    <span class="material-symbols-outlined">check_circle</span>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- Table -->
            <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-white/5 text-text-secondary text-sm font-semibold uppercase">
                        <tr>
                            <th class="px-6 py-4">Campaign</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Progress</th>
                            <th class="px-6 py-4">Created At</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark">
                        @forelse($broadcasts as $broadcast)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-white">{{ $broadcast->title }}</p>
                                <p class="text-xs text-text-secondary mt-1 truncate max-w-xs">{{ Str::limit($broadcast->message, 50) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColor = match($broadcast->status) {
                                        'draft' => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                        'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'completed' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                        'canceled' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        default => 'bg-gray-500/10 text-gray-400'
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium border {{ $statusColor }}">
                                    {{ ucfirst($broadcast->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-center">
                                    <span class="text-xs font-medium text-white mb-1">
                                        {{ $broadcast->sent_count }} / {{ $broadcast->targets_count }}
                                    </span>
                                    <div class="w-full max-w-[100px] h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                        @php
                                            $percent = $broadcast->targets_count > 0 
                                                ? ($broadcast->sent_count / $broadcast->targets_count) * 100 
                                                : 0;
                                        @endphp
                                        <div class="h-full bg-whatsapp transition-all duration-500" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-text-secondary">
                                {{ $broadcast->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('whatsapp.broadcast.show', $broadcast) }}" class="text-primary hover:text-blue-400 font-medium text-sm">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-text-secondary">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl mb-4 opacity-50">campaign</span>
                                    <p class="text-lg font-medium">Belum ada broadcast</p>
                                    <p class="text-sm mt-1">Buat campaign pertama Anda untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $broadcasts->links() }}
            </div>
        </div>
    </main>
</body>
</html>
