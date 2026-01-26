<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sesi Aktif - ReplyAI</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-background-dark text-white font-display">
<div class="flex h-screen">
    @include('components.sidebar')
    
    <main class="flex-1 overflow-y-auto p-6 lg:p-10 pt-16 lg:pt-10">
        <div class="max-w-3xl mx-auto">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('account.index') }}" class="text-slate-400 hover:text-white text-sm flex items-center gap-1 mb-4">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Akun
                </a>
                <h1 class="text-2xl font-bold text-white">Sesi Aktif</h1>
                <p class="text-slate-400 mt-1">Kelola perangkat yang sedang login ke akun Anda</p>
            </div>

            {{-- Logout All Button --}}
            @if($sessions->count() > 1)
            <form action="{{ route('sessions.destroy-all') }}" method="POST" class="mb-6">
                @csrf
                @method('DELETE')
                <button type="submit" 
                    onclick="return confirm('Logout dari semua perangkat lain?')"
                    class="text-sm text-red-400 hover:text-red-300 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">logout</span>
                    Logout dari semua perangkat lain
                </button>
            </form>
            @endif

            {{-- Sessions List --}}
            <div class="space-y-4">
                @foreach($sessions as $session)
                <div class="bg-surface-dark rounded-xl border {{ $session['is_current'] ? 'border-primary' : 'border-slate-700' }} p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full {{ $session['is_current'] ? 'bg-primary/20' : 'bg-slate-800' }} flex items-center justify-center">
                                <span class="material-symbols-outlined {{ $session['is_current'] ? 'text-primary' : 'text-slate-400' }}">
                                    {{ $session['device']['icon'] }}
                                </span>
                            </div>
                            <div>
                                <p class="font-semibold text-white">
                                    {{ $session['device']['platform'] }} - {{ $session['device']['browser'] }}
                                    @if($session['is_current'])
                                    <span class="ml-2 text-xs bg-primary/20 text-primary px-2 py-0.5 rounded-full">Perangkat ini</span>
                                    @endif
                                </p>
                                <p class="text-sm text-slate-400 mt-1">
                                    <span class="material-symbols-outlined text-sm align-middle">location_on</span>
                                    {{ $session['ip_address'] }}
                                    <span class="mx-2">•</span>
                                    Aktif {{ $session['last_activity'] }}
                                </p>
                            </div>
                        </div>
                        
                        @if(!$session['is_current'])
                        <form action="{{ route('sessions.destroy', $session['id']) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                onclick="return confirm('Logout dari perangkat ini?')"
                                class="px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition">
                                Logout
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Security Tips --}}
            <div class="mt-8 p-4 bg-slate-800/50 rounded-xl border border-slate-700">
                <h4 class="font-semibold text-white mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-yellow-500">tips_and_updates</span>
                    Tips Keamanan
                </h4>
                <ul class="text-sm text-slate-400 space-y-1">
                    <li>• Logout dari perangkat yang tidak Anda kenali</li>
                    <li>• Jangan login dari komputer umum</li>
                    <li>• Ganti password jika ada aktivitas mencurigakan</li>
                </ul>
            </div>
        </div>
    </main>
</div>

@include('components.toast')
</body>
</html>
