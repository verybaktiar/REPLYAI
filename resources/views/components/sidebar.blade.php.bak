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

<!-- Mobile Bottom Navigation (Visible only on mobile) -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 h-16 bg-[#0a0f18] border-t border-gray-800 flex items-center justify-around px-2 z-50">
    <a href="{{ route('whatsapp.inbox') }}" class="flex flex-col items-center gap-1 min-w-[60px] {{ request()->routeIs('whatsapp.inbox*') ? 'text-blue-500' : 'text-gray-500' }}">
        <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('whatsapp.inbox*') ? 'filled' : '' }}">chat</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Chat</span>
    </a>
    <a href="{{ route('kb.index') }}" class="flex flex-col items-center gap-1 min-w-[60px] {{ request()->routeIs('kb*') || request()->routeIs('rules*') ? 'text-blue-500' : 'text-gray-500' }}">
        <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('kb*') || request()->routeIs('rules*') ? 'filled' : '' }}">psychology</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">AI</span>
    </a>
    
    <!-- Floating Action Button (FAB) for Broadcast -->
    <div class="relative -top-6">
        <a href="{{ route('whatsapp.broadcast.index') }}" 
           class="bg-blue-600 w-14 h-14 rounded-full flex items-center justify-center shadow-lg shadow-blue-900/40 active:scale-95 transition-transform">
            <span class="material-symbols-outlined text-white text-[28px]">add</span>
        </a>
    </div>

    <a href="{{ route('whatsapp.broadcast.index') }}" class="flex flex-col items-center gap-1 min-w-[60px] {{ request()->routeIs('whatsapp.broadcast*') ? 'text-blue-500' : 'text-gray-500' }}">
        <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('whatsapp.broadcast*') ? 'filled' : '' }}">campaign</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Promo</span>
    </a>
    <a href="{{ route('analytics.index') }}" class="flex flex-col items-center gap-1 min-w-[60px] {{ request()->routeIs('analytics*') || request()->routeIs('settings*') ? 'text-blue-500' : 'text-gray-500' }}">
        <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('analytics*') ? 'filled' : '' }}">settings</span>
        <span class="text-[10px] font-bold uppercase tracking-tighter">Setting</span>
    </a>
</div>

<!-- Desktop Sidebar -->
<aside class="hidden lg:flex flex-col w-[250px] h-full bg-gray-950 border-r border-gray-800 shrink-0 fixed inset-y-0 left-0 z-50">
    <!-- Brand Header -->
    <div class="px-6 py-8 border-b border-gray-900">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-900/20">
                <span class="text-white font-black text-xl">R</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-white leading-none tracking-tight">ReplyAI</h1>
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-1">Chatbot AI</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Links -->
    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2">
        @include('components.sidebar-nav-links')
    </nav>
    
    <!-- User Profile with Logout -->
    <div class="border-t border-gray-900 p-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-900 transition-all group border border-transparent hover:border-gray-800">
                <div class="size-8 rounded-full bg-blue-600 flex items-center justify-center text-xs font-bold text-white shadow-inner">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                </div>
                <span class="material-symbols-outlined text-gray-600 group-hover:text-red-400 text-lg transition-colors">logout</span>
            </button>
        </form>
    </div>
</aside>
</div>

