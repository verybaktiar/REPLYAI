@include('components.impersonation-banner')
<style>
    /* Sidebar scrollbar styling - applied globally when sidebar is included */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #111722; }
    ::-webkit-scrollbar-thumb { background: #324467; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #445577; }
    
    /* Mobile sidebar animations */
    .sidebar-enter { animation: slideInLeft 0.3s ease-out; }
    .sidebar-leave { animation: slideOutLeft 0.2s ease-in; }
    @keyframes slideInLeft {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutLeft {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(-100%); opacity: 0; }
    }
</style>

<div x-data="{ mobileSidebarOpen: false }" class="contents">
    <!-- Mobile Top Header -->
    <div class="lg:hidden fixed left-0 right-0 h-14 bg-[#030712] border-b border-gray-800 flex items-center justify-between px-4 z-[40] transition-all duration-300"
         style="top: {{ session()->has('impersonating_from_admin') ? '44px' : '0' }}"
         :class="mobileSidebarOpen ? 'opacity-0 pointer-events-none' : 'opacity-100'">
        <div class="flex items-center gap-2">
            <button @click="mobileSidebarOpen = true" class="p-2 -ml-2 text-gray-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[24px]">menu</span>
            </button>
            <span class="text-sm font-bold text-white tracking-tight">ReplyAI</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="size-8 rounded-full bg-blue-600 flex items-center justify-center text-[10px] font-black text-white">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation (Visible only on mobile) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 h-16 bg-[#030712]/80 backdrop-blur-lg border-t border-gray-800 flex items-center justify-around px-2 z-40 pb-safe">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 min-w-[60px] transition-all duration-300 {{ request()->routeIs('dashboard*') ? 'text-blue-500 scale-110' : 'text-gray-500' }}">
            <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('dashboard*') ? 'filled' : '' }}">dashboard</span>
            <span class="text-[9px] font-bold uppercase tracking-tighter">Home</span>
        </a>
        <a href="{{ route('whatsapp.inbox') }}" class="flex flex-col items-center gap-1 min-w-[60px] transition-all duration-300 {{ request()->routeIs('whatsapp.inbox*') || request()->routeIs('inbox*') ? 'text-blue-500 scale-110' : 'text-gray-500' }}">
            <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('whatsapp.inbox*') || request()->routeIs('inbox*') ? 'filled' : '' }}">chat</span>
            <span class="text-[9px] font-bold uppercase tracking-tighter">Chat</span>
        </a>
        
        <!-- Floating Action Button (FAB) for Broadcast -->
        <div class="relative -top-6">
            <a href="{{ route('whatsapp.broadcast.index') }}" 
               class="bg-blue-600 w-14 h-14 rounded-full flex items-center justify-center shadow-lg shadow-blue-900/40 active:scale-95 transition-transform border-4 border-[#030712]">
                <span class="material-symbols-outlined text-white text-[28px]">campaign</span>
            </a>
        </div>

        <button @click="mobileSidebarOpen = true" class="flex flex-col items-center gap-1 min-w-[60px] text-gray-500 active:scale-95 transition-transform">
            <span class="material-symbols-outlined text-[24px]">grid_view</span>
            <span class="text-[9px] font-bold uppercase tracking-tighter">Menu</span>
        </button>

        <a href="{{ route('settings.business') }}" class="flex flex-col items-center gap-1 min-w-[60px] transition-all duration-300 {{ request()->routeIs('settings.business*') ? 'text-blue-500 scale-110' : 'text-gray-500' }}">
            <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('settings.business*') ? 'filled' : '' }}">person_settings</span>
            <span class="text-[9px] font-bold uppercase tracking-tighter">Profil</span>
        </a>
    </div>

    <!-- Mobile Sidebar Drawer -->
    <div x-show="mobileSidebarOpen" 
         class="fixed inset-0 z-[300] lg:hidden" 
         style="display: none;">
        <!-- Backdrop -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileSidebarOpen = false"
             class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        <!-- Sidebar Content -->
        <div x-show="mobileSidebarOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative w-80 max-w-[85%] h-full bg-[#0f172a] shadow-2xl flex flex-col border-r border-gray-800">
            
            <!-- Header inside drawer -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-gray-800 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-blue-600 flex items-center justify-center">
                        <span class="text-white font-black text-sm">R</span>
                    </div>
                    <span class="text-lg font-bold text-white tracking-tight">ReplyAI</span>
                </div>
                <button @click="mobileSidebarOpen = false" class="p-2 -mr-2 text-gray-500 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Scrollable Nav -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-1 custom-scrollbar">
                @include('components.sidebar-nav-links')
            </nav>

            <!-- Bottom User Info -->
            <div class="p-6 border-t border-gray-800 bg-gray-950/50">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold text-white border-2 border-blue-400/20">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        Keluar Aplikasi
                    </button>
                </form>
            </div>
        </div>
    </div>

<!-- Desktop Sidebar - flex-shrink-0, NOT fixed (integrates with Root Cage flexbox) -->
<aside class="hidden lg:flex flex-col w-64 h-full bg-gray-900 border-r border-gray-800 flex-shrink-0">
    <!-- Brand Header -->
    <div class="h-16 flex items-center px-4 border-b border-gray-800 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-lg bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-900/20">
                <span class="text-white font-black text-xl">R</span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white leading-none tracking-tight">ReplyAI</h1>
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-widest mt-0.5">Chatbot AI</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Links (flex-1 overflow-y-auto - ONLY this scrolls) -->
    <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
        @include('components.sidebar-nav-links')
    </nav>
    
    <!-- User Profile with Logout (flex-shrink-0) -->
    <div class="border-t border-gray-800 p-4 flex-shrink-0">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors group">
                <div class="size-8 rounded-full bg-blue-600 flex items-center justify-center text-xs font-bold text-white">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                </div>
                <span class="material-symbols-outlined text-gray-600 group-hover:text-red-400 text-lg transition-colors">logout</span>
            </button>
        </form>
    </div>
</aside>
</div>

