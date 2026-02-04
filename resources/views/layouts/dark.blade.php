<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ReplyAI') - Admin</title>
    <link rel="manifest" href="/manifest.json?v=2">
    <meta name="theme-color" content="#111722">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('SW registered!', reg))
                    .catch(err => console.log('SW registration failed!', err));
            });
        }
    </script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind & App JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #232f48; border-radius: 10px; }
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
                max-height: 92vh;
                overflow-y: auto;
            }
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-background-dark text-white font-sans antialiased overflow-hidden">
    <div class="flex flex-col lg:flex-row h-screen w-full">
        <!-- Sidebar Navigation -->
        @include('components.sidebar')

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-hidden relative pt-14 lg:pt-0">
            <!-- Top Header (Desktop only - mobile header is in sidebar component) -->
            <header class="hidden lg:flex h-16 items-center justify-between px-6 border-b border-border-dark bg-background-dark/80 backdrop-blur-md sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-2 text-text-secondary text-xs font-bold uppercase tracking-widest">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Notifications (Announcements) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative size-9 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center text-text-secondary hover:text-white transition group">
                            <span class="material-symbols-outlined text-[20px]">notifications</span>
                            @if(isset($unread_announcements) && $unread_announcements->count() > 0)
                            <span class="absolute top-1.5 right-1.5 size-2 bg-red-500 rounded-full border-2 border-background-dark"></span>
                            @endif
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-3 w-80 bg-surface-dark border border-border-dark rounded-2xl shadow-2xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-border-dark flex items-center justify-between bg-background-dark/50">
                                <span class="text-xs font-bold uppercase tracking-widest text-text-secondary">Notifications</span>
                                @if(isset($unread_announcements) && $unread_announcements->count() > 0)
                                <span class="px-2 py-0.5 bg-primary/20 text-primary text-[10px] font-black rounded-full">{{ $unread_announcements->count() }} NEW</span>
                                @endif
                            </div>
                            <div class="max-h-[400px] overflow-y-auto">
                                @forelse($unread_announcements ?? [] as $announcement)
                                <div class="p-4 border-b border-border-dark last:border-0 hover:bg-white/5 transition group">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 size-2 rounded-full shrink-0
                                            {{ $announcement->style === 'danger' ? 'bg-red-500' : 
                                               ($announcement->style === 'warning' ? 'bg-yellow-500' : 
                                               ($announcement->style === 'success' ? 'bg-green-500' : 'bg-primary')) }}">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-white mb-1 leading-tight">{{ $announcement->title }}</p>
                                            <p class="text-xs text-text-secondary line-clamp-3 leading-relaxed mb-2">{{ $announcement->message }}</p>
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] text-slate-500">{{ $announcement->created_at->diffForHumans() }}</span>
                                                <form action="{{ route('announcements.read', $announcement->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-[10px] font-bold text-primary hover:underline">Mark as read</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="p-8 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-700 mb-2">notifications_off</span>
                                    <p class="text-xs text-slate-500 font-medium">No new announcements</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 px-3 py-1.5 bg-whatsapp/10 rounded-full border border-whatsapp/20">
                        <div class="size-2 bg-whatsapp rounded-full animate-pulse"></div>
                        <span class="text-[10px] font-bold text-whatsapp uppercase tracking-widest">System Online</span>
                    </div>

                    <!-- Language Switcher -->
                    @include('components.language-switcher')

                    <div class="size-9 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center">
                        <span class="material-symbols-outlined text-text-secondary">person</span>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-10 pb-24 lg:pb-10 pt-24 lg:pt-10 scroll-smooth custom-scrollbar {{ session()->has('impersonating_from_admin') ? 'mt-11' : '' }}">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
