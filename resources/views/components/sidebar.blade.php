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

<!-- Mobile Sidebar Wrapper with Alpine.js -->
<div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    
    <!-- Mobile Header with Hamburger -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-[#111722] border-b border-[#232f48] px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="p-2 -ml-2 text-[#92a4c9] hover:text-white hover:bg-[#232f48] rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <div class="bg-center bg-no-repeat bg-cover rounded-full size-8 shadow-lg" style='background-image: url("https://ui-avatars.com/api/?name=Reply+AI&background=0D8ABC&color=fff");'></div>
                <h1 class="text-sm font-bold text-white">ReplyAI</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="flex items-center gap-1 px-2 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                Online
            </span>
        </div>
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="lg:hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
         @click="sidebarOpen = false"
         x-cloak>
    </div>
    
    <!-- Mobile Sidebar Panel -->
    <aside x-show="sidebarOpen"
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="lg:hidden fixed left-0 top-0 bottom-0 z-50 w-72 bg-[#111722] border-r border-[#232f48] flex flex-col overflow-hidden"
           @click.stop
           x-cloak>
        
        <!-- Mobile Sidebar Header -->
        <div class="flex items-center justify-between px-4 py-4 border-b border-[#232f48]">
            <div class="flex items-center gap-3">
                <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 shadow-lg" style='background-image: url("https://ui-avatars.com/api/?name=Reply+AI&background=0D8ABC&color=fff");'></div>
                <div>
                    <h1 class="text-base font-bold leading-none text-white">ReplyAI Admin</h1>
                    <p class="text-xs text-[#92a4c9] mt-1">Multi-Channel AI Platform</p>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="p-2 text-[#92a4c9] hover:text-white hover:bg-[#232f48] rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Mobile Navigation Links -->
        <nav class="flex flex-col gap-1 flex-1 overflow-y-auto px-3 py-4">
            @include('components.sidebar-nav-links')
        </nav>
        
        <!-- Mobile User Profile -->
        <div class="border-t border-[#232f48] p-3">
            <div class="p-3 rounded-lg bg-[#232f48]/50 flex items-center gap-3">
                <div class="size-8 rounded-full bg-gradient-to-tr from-purple-500 to-primary flex items-center justify-center text-xs font-bold text-white">DM</div>
                <div class="flex flex-col overflow-hidden flex-1">
                    <p class="text-white text-sm font-medium truncate">Admin</p>
                    <p class="text-[#92a4c9] text-xs truncate">admin@rspkusolo.com</p>
                </div>
                <button class="text-[#92a4c9] hover:text-white">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Desktop Sidebar (Original) -->
    <aside class="hidden lg:flex flex-col w-72 h-full bg-[#111722] border-r border-[#232f48] shrink-0">
        <!-- Brand -->
        <div class="flex items-center gap-3 px-6 py-6 mb-2">
            <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 shadow-lg relative" style='background-image: url("https://ui-avatars.com/api/?name=Reply+AI&background=0D8ABC&color=fff");'></div>
            <div>
                <h1 class="text-base font-bold leading-none text-white">ReplyAI Admin</h1>
                <p class="text-xs text-[#92a4c9] mt-1">Multi-Channel AI Platform</p>
            </div>
        </div>
        <!-- Navigation Links -->
        <nav class="flex flex-col gap-1 flex-1 overflow-y-auto px-4">
            @include('components.sidebar-nav-links')
        </nav>
        <!-- User Profile (Bottom) -->
        <div class="border-t border-[#232f48] p-4">
            <div class="p-3 rounded-lg bg-[#232f48]/50 flex items-center gap-3">
                <div class="size-8 rounded-full bg-gradient-to-tr from-purple-500 to-primary flex items-center justify-center text-xs font-bold text-white">DM</div>
                <div class="flex flex-col overflow-hidden">
                    <p class="text-white text-sm font-medium truncate">Admin</p>
                    <p class="text-[#92a4c9] text-xs truncate">admin@rspkusolo.com</p>
                </div>
                <button class="ml-auto text-[#92a4c9] hover:text-white">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </button>
            </div>
        </div>
    </aside>
</div>

