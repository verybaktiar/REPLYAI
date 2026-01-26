<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - {{ $ticket->ticket_number }}</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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

    <main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
        <header class="flex h-16 items-center px-6 border-b border-slate-800 bg-background-dark/50 backdrop-blur-sm">
            <a href="{{ route('support.index') }}" class="p-2 -ml-2 text-slate-400 hover:text-primary">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div class="ml-3">
                <span class="text-xs font-mono text-slate-400">{{ $ticket->ticket_number }}</span>
                <h1 class="font-bold truncate max-w-md">{{ $ticket->subject }}</h1>
            </div>
        </header>

        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Messages -->
            <div class="flex-1 overflow-auto p-6 space-y-4">
                
                <!-- Original Message -->
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-white">person</span>
                    </div>
                    <div class="flex-1 max-w-2xl">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold">Anda</span>
                            <span class="text-xs text-slate-400">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="bg-surface-dark rounded-xl p-4 border border-slate-700">
                            <p class="whitespace-pre-wrap">{{ $ticket->message }}</p>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                @foreach($ticket->replies as $reply)
                <div class="flex gap-3 {{ $reply->isFromAdmin() ? 'flex-row-reverse' : '' }}">
                    <div class="w-10 h-10 rounded-full {{ $reply->isFromAdmin() ? 'bg-green-600' : 'bg-primary' }} flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-white">
                            {{ $reply->isFromAdmin() ? 'support_agent' : 'person' }}
                        </span>
                    </div>
                    <div class="flex-1 max-w-2xl {{ $reply->isFromAdmin() ? 'text-right' : '' }}">
                        <div class="flex items-center gap-2 mb-1 {{ $reply->isFromAdmin() ? 'justify-end' : '' }}">
                            <span class="font-semibold">{{ $reply->sender_label }}</span>
                            <span class="text-xs text-slate-400">{{ $reply->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="bg-surface-dark rounded-xl p-4 border border-slate-700 {{ $reply->isFromAdmin() ? 'inline-block' : '' }}">
                            <p class="whitespace-pre-wrap text-left">{{ $reply->message }}</p>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>

            <!-- Reply Form -->
            @if($ticket->status !== 'closed')
            <div class="p-4 border-t border-slate-800 bg-surface-dark">
                <form action="{{ route('support.reply', $ticket) }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="text" name="message" placeholder="Tulis balasan..." required
                           class="flex-1 px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary">
                    <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-primary/90 transition">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </form>
            </div>
            @endif

        </div>
    </main>
</div>
</body>
</html>
