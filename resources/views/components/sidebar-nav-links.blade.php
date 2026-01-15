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

<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('sequences*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('sequences.index') }}">
    <span class="material-symbols-outlined text-[24px]">timeline</span>
    <span class="text-sm font-medium">Sequences</span>
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

<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('web-widgets*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('web-widgets.index') }}">
    <span class="material-symbols-outlined text-[24px]">widgets</span>
    <span class="text-sm font-medium">Web Widget</span>
</a>

<div class="mt-4 mb-2 px-3">
    <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">Settings</p>
</div>

<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('settings.business*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('settings.business') }}">
    <span class="material-symbols-outlined text-[24px]">business</span>
    <span class="text-sm font-medium">Business Profile</span>
</a>

<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('settings.index') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('settings.index') }}">
    <span class="material-symbols-outlined text-[24px]">schedule</span>
    <span class="text-sm font-medium">Business Hours</span>
</a>

<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('takeover.logs*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('takeover.logs') }}">
    <span class="material-symbols-outlined text-[24px]">swap_horiz</span>
    <span class="text-sm font-medium">Takeover Logs</span>
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
