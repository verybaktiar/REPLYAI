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

<!-- Mobile Sidebar Wrapper with Alpine.js -->
<div x-data="{ 
    sidebarOpen: false, 
    openSubmenu: null,
    toggleSubmenu(menu) { this.openSubmenu = (this.openSubmenu === menu) ? null : menu }
}" @keydown.escape.window="sidebarOpen = false">
    
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
                {{ __('sidebar.online') }}
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
                    <p class="text-xs text-[#92a4c9] mt-1">{{ __('sidebar.multi_channel') }}</p>
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
                <div class="size-8 rounded-full bg-gradient-to-tr from-purple-500 to-primary flex items-center justify-center text-xs font-bold text-white">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex flex-col overflow-hidden flex-1">
                    <p class="text-white text-sm font-medium truncate">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-[#92a4c9] text-xs truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[#92a4c9] hover:text-red-400 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex flex-col w-64 h-full bg-gray-900 border-r border-gray-800 shrink-0">
        <!-- Brand Header -->
        <div class="px-4 py-4 border-b border-gray-800">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-blue-600 flex items-center justify-center">
                    <span class="text-white font-bold text-lg">R</span>
                </div>
                <div>
                    <h1 class="text-base font-bold text-white">ReplyAI</h1>
                    <p class="text-xs text-gray-500">{{ __('sidebar.chatbot_platform') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
            @include('components.sidebar-nav-links')
        </nav>
        
        <!-- User Profile with Logout -->
        <div class="border-t border-gray-800 p-3">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition-colors group">
                    <div class="size-8 rounded-full bg-blue-600 flex items-center justify-center text-xs font-bold text-white">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0 text-left">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                    </div>
                    <span class="material-symbols-outlined text-gray-500 group-hover:text-red-400 text-lg transition-colors">logout</span>
                </button>
            </form>
        </div>
    </aside>
</div>

