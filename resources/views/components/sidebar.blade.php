<aside class="hidden lg:flex flex-col w-72 h-full bg-[#111722] border-r border-[#232f48] shrink-0 fixed lg:static top-0 bottom-0 left-0 z-40">
    <!-- Brand -->
    <div class="flex items-center gap-3 px-6 py-6 mb-2">
        <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 shadow-lg relative" style='background-image: url("https://ui-avatars.com/api/?name=Reply+AI&background=0D8ABC&color=fff");'></div>
        <div>
            <h1 class="text-base font-bold leading-none text-white">ReplyAI Admin</h1>
            <p class="text-xs text-[#92a4c9] mt-1">RS PKU Solo Bot</p>
        </div>
    </div>
    <!-- Navigation Links -->
    <nav class="flex flex-col gap-1 flex-1 overflow-y-auto px-4">
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('dashboard') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('dashboard') }}">
            <span class="material-symbols-outlined text-[24px]">grid_view</span>
            <span class="text-sm font-medium">Dashboard</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('analytics*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('analytics.index') }}">
            <span class="material-symbols-outlined text-[24px]">pie_chart</span>
            <span class="text-sm font-medium">Analisis & Laporan</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('contacts*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('contacts.index') }}">
            <span class="material-symbols-outlined text-[24px]">groups</span>
            <span class="text-sm font-medium">Data Kontak (CRM)</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('inbox*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('inbox') }}">
            <span class="material-symbols-outlined text-[24px]">chat_bubble</span>
            <span class="text-sm font-medium">Kotak Masuk</span>
            @if(isset($conversations) && $conversations instanceof \Illuminate\Database\Eloquent\Collection && $conversations->count() > 0)
                <span class="ml-auto bg-white/10 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md text-center min-w-[20px]">{{ $conversations->count() }}</span>
            @elseif(isset($stats['pending_inbox']) && $stats['pending_inbox'] > 0)
                 <span class="ml-auto bg-white/10 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md text-center min-w-[20px]">{{ $stats['pending_inbox'] }}</span>
            @endif
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('rules*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('rules.index') }}">
            <span class="material-symbols-outlined text-[24px]">smart_toy</span>
            <span class="text-sm font-medium">Manajemen Bot</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('kb*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('kb.index') }}">
            <span class="material-symbols-outlined text-[24px]">menu_book</span>
            <span class="text-sm font-medium">Knowledge Base</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('quick-replies*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('quick-replies.index') }}">
            <span class="material-symbols-outlined text-[24px]">bolt</span>
            <span class="text-sm font-medium">Quick Replies</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('simulator*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('simulator.index') }}">
            <span class="material-symbols-outlined text-[24px]">science</span>
            <span class="text-sm font-medium">Simulator</span>
        </a>

        <div class="mt-4 mb-2 px-3">
            <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">Integrasi</p>
        </div>

        <!-- WhatsApp Menu Group -->
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('whatsapp.inbox*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('whatsapp.inbox') }}">
            <span class="material-symbols-outlined text-[24px]">chat</span>
            <span class="text-sm font-medium">WhatsApp Inbox</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('whatsapp.broadcast*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('whatsapp.broadcast.index') }}">
            <span class="material-symbols-outlined text-[24px]">campaign</span>
            <span class="text-sm font-medium">Broadcast</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('whatsapp.settings*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('whatsapp.settings') }}">
            <span class="material-symbols-outlined text-[24px]">perm_data_setting</span>
            <span class="text-sm font-medium">WA Settings</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('whatsapp.analytics*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('whatsapp.analytics') }}">
            <span class="material-symbols-outlined text-[24px]">analytics</span>
            <span class="text-sm font-medium">Analytics</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('settings*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('settings.index') }}">
            <span class="material-symbols-outlined text-[24px]">settings</span>
            <span class="text-sm font-medium">Settings (Hours)</span>
        </a>

        <div class="mt-4 mb-2 px-3">
            <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">System</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('logs*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('logs.index') }}">
            <span class="material-symbols-outlined text-[24px]">history</span>
            <span class="text-sm font-medium">Log Aktivitas</span>
        </a>
        <div class="mt-4 mb-2 px-3">
            <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">Help & Guide</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('documentation.*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('documentation.index') }}">
            <span class="material-symbols-outlined text-[24px]">menu_book</span>
            <span class="text-sm font-medium">Documentation</span>
        </a>

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
