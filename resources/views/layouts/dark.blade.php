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
    </style>
    @stack('styles')
</head>
<body class="bg-background-dark text-white font-sans antialiased overflow-hidden">
    <div class="flex h-screen w-full">
        <!-- Sidebar Navigation -->
        @include('components.sidebar')

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 h-full overflow-hidden relative">
            <!-- Top Header -->
            <header class="h-16 flex items-center justify-between px-6 border-b border-border-dark bg-background-dark/80 backdrop-blur-md sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="lg:hidden p-2 -ml-2 text-text-secondary hover:text-white transition-colors">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="hidden lg:flex items-center gap-2 text-text-secondary text-xs font-bold uppercase tracking-widest">
                        <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                        {{ now()->translatedFormat('l, d F Y') }}
                    </div>
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
            <div class="flex-1 overflow-y-auto p-6 lg:p-10 scroll-smooth custom-scrollbar">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </div>
        </main>

        <!-- Mobile Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-30 hidden backdrop-blur-sm transition-opacity opacity-0"></div>
    </div>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (!sidebar) return;

            function openSidebar() {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('flex', 'translate-x-0');
                if (overlay) {
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                }
            }

            function closeSidebar() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('hidden');
                    sidebar.classList.remove('flex');
                    if (overlay) {
                        overlay.classList.add('opacity-0');
                        setTimeout(() => overlay.classList.add('hidden'), 300);
                    }
                }
            }

            toggleBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = sidebar.classList.contains('hidden');
                if (isHidden) openSidebar(); else closeSidebar();
            });

            overlay?.addEventListener('click', closeSidebar);

            // Close on window resize if switching to desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('hidden');
                    sidebar.classList.add('flex');
                    overlay?.classList.add('hidden', 'opacity-0');
                } else {
                    sidebar.classList.add('hidden');
                    sidebar.classList.remove('flex');
                }
            });
        });
    </script>
</body>
</html>



