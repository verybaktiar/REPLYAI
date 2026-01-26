<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Tiket Support</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-dark": "#101622",
                        "surface-dark": "#1a2230",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-dark text-white font-display antialiased overflow-hidden">
<div class="flex flex-col lg:flex-row h-screen w-full">
    @include('components.sidebar')

    <main class="flex-1 flex flex-col h-full overflow-auto relative pt-14 lg:pt-0">
        <header class="hidden lg:flex h-16 items-center justify-between px-6 lg:px-8 border-b border-slate-800 bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="p-2 -ml-2 text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-bold text-lg">Tiket Support</h1>
                    <p class="text-xs text-slate-400">Kelola permintaan bantuan Anda</p>
                </div>
            </div>
            <a href="{{ route('support.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-medium hover:bg-primary/90">
                <span class="material-symbols-outlined">add</span>
                Buat Tiket
            </a>
        </header>

        <div class="flex-1 overflow-auto p-6 lg:p-8">
            
            @if($tickets->isEmpty())
            <div class="text-center py-16">
                <span class="material-symbols-outlined text-6xl text-slate-600 mb-4">support_agent</span>
                <h2 class="text-xl font-bold mb-2">Belum Ada Tiket</h2>
                <p class="text-slate-400 mb-6">Anda belum pernah membuat tiket support.</p>
                <a href="{{ route('support.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary/90">
                    <span class="material-symbols-outlined">add</span>
                    Buat Tiket
                </a>
            </div>
            @else
            
            <div class="space-y-4">
                @foreach($tickets as $ticket)
                <a href="{{ route('support.show', $ticket) }}" class="block bg-surface-dark rounded-xl p-5 border border-slate-700 hover:border-primary transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-mono text-slate-400">{{ $ticket->ticket_number }}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $ticket->status_color }}-500/20 text-{{ $ticket->status_color }}-400">
                                    {{ $ticket->status_label }}
                                </span>
                            </div>
                            <h3 class="font-semibold text-lg truncate">{{ $ticket->subject }}</h3>
                            <p class="text-sm text-slate-400">{{ $ticket->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-500">chevron_right</span>
                    </div>
                </a>
                @endforeach
            </div>
            
            <div class="mt-6">{{ $tickets->links() }}</div>
            @endif

        </div>
    </main>
</div>
</body>
</html>
