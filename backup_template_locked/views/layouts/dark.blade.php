<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ReplyAI') - Admin</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: "#135bec",
                        whatsapp: "#25D366",
                        "background-dark": "#101622",
                        "surface-dark": "#1a2230",
                        "border-dark": "#232f48",
                        "text-secondary": "#92a4c9",
                    }
                }
            }
        }
    </script>
    
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #232f48; border-radius: 10px; }
        [x-cloak] { display: none !important; }
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
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-whatsapp/10 rounded-full border border-whatsapp/20">
                        <div class="size-2 bg-whatsapp rounded-full animate-pulse"></div>
                        <span class="text-[10px] font-bold text-whatsapp uppercase tracking-widest">System Online</span>
                    </div>
                    <div class="size-9 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center">
                        <span class="material-symbols-outlined text-text-secondary">person</span>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-10 scroll-smooth custom-scrollbar">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
