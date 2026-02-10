{{-- ==================== SIDEBAR - PROFESSIONAL GROUPS ==================== --}}
@php
    $user = auth()->user();
    $isVip = $user?->is_vip ?? false;
    $hasFeature = fn($feature) => $isVip || ($user?->hasFeature($feature) ?? false);
    
    // Unread counts (Incoming only)
    $unreadInbox = 0; $unreadWhatsApp = 0;
    try {
        $unreadInbox = \App\Models\Message::whereHas('conversation', function($q) use ($user) {
                $q->whereHas('instagramAccount', fn($q2) => $q2->where('user_id', $user?->id));
            })
            ->where('sender_type', 'contact')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereDoesntHave('conversation', fn($q) => $q->where('status', 'agent_handling'))
            ->count();
            
        $unreadWhatsApp = \App\Models\WaMessage::whereHas('waConversation', function($q) use ($user) {
                $q->where('user_id', $user?->id);
            })
            ->where('direction', 'incoming')->where('is_read', false)->count();
    } catch (\Exception $e) {}
@endphp

{{-- 1. OVERVIEW --}}
<a class="flex items-center gap-3 px-4 py-2.5 transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}" href="{{ route('dashboard') }}">
    <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('dashboard') ? 'filled' : '' }}">dashboard</span>
    <div class="flex flex-col">
        <span class="text-xs font-bold uppercase tracking-tighter">Overview</span>
    </div>
</a>

{{-- 2. CHAT (Grouped) --}}
@php
    $isChatActive = request()->routeIs('inbox*') || request()->routeIs('whatsapp.inbox*') || request()->routeIs('contacts*');
@endphp
<div x-data="{ open: {{ $isChatActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 transition-all {{ $isChatActive ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isChatActive ? 'filled' : '' }}">chat</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Chat</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="flex items-center justify-between py-2 text-xs {{ request()->routeIs('whatsapp.inbox*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.inbox') }}">
            <span>WhatsApp</span>
            @if($unreadWhatsApp > 0)
                <span class="bg-green-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ $unreadWhatsApp }}</span>
            @endif
        </a>
        <a class="flex items-center justify-between py-2 text-xs {{ request()->routeIs('inbox*') && !request()->routeIs('whatsapp.inbox*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('inbox') }}">
            <span>Instagram</span>
            @if($unreadInbox > 0)
                <span class="bg-red-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ $unreadInbox }}</span>
            @endif
        </a>
        <a class="block py-2 text-xs {{ request()->routeIs('contacts*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('contacts.index') }}">Kontak</a>
    </div>
</div>

{{-- 3. LAPORAN (Grouped) --}}
@php
    $isReportActive = request()->routeIs('analytics*') || request()->routeIs('logs*') || request()->routeIs('admin.analytics*');
@endphp
<div x-data="{ open: {{ $isReportActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 transition-all {{ $isReportActive ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isReportActive ? 'filled' : '' }}">bar_chart_4_bars</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Laporan</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="block py-2 text-xs {{ request()->routeIs('admin.analytics*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('admin.analytics.index') }}">🤖 AI Analytics</a>
        <a class="block py-2 text-xs {{ request()->routeIs('analytics*') && !request()->routeIs('admin.analytics*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('analytics.index') }}">Statistik</a>
        <a class="block py-2 text-xs {{ request()->routeIs('logs*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('logs.index') }}">Riwayat Aktivitas</a>
    </div>
</div>

{{-- 3. AI SETUP (Grouped) --}}
@php
    $isAiActive = request()->routeIs('rules*') || request()->routeIs('kb*');
@endphp
<div x-data="{ open: {{ $isAiActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 transition-all {{ $isAiActive ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isAiActive ? 'filled' : '' }}">psychology</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">AI Setup</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="block py-2 text-xs {{ request()->routeIs('kb*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('kb.index') }}">Basis Pengetahuan</a>
        <a class="block py-2 text-xs {{ request()->routeIs('rules*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('rules.index') }}">Atur Balasan</a>
    </div>
</div>

{{-- 4. PROMOSI (Grouped) --}}
@php
    $isPromoActive = request()->routeIs('whatsapp.broadcast*') || request()->routeIs('sequences*');
@endphp
<div x-data="{ open: {{ $isPromoActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 transition-all {{ $isPromoActive ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isPromoActive ? 'filled' : '' }}">campaign</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Promosi</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="block py-2 text-xs {{ request()->routeIs('whatsapp.broadcast*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.broadcast.index') }}">Broadcast</a>
        <a class="block py-2 text-xs {{ request()->routeIs('sequences*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('sequences.index') }}">Pesan Terjadwal</a>
    </div>
</div>

{{-- 6. PENGATURAN (Grouped) --}}
@php
    $isSettingsActive = request()->routeIs('settings*') || request()->routeIs('whatsapp.settings*') || request()->routeIs('instagram.settings*');
@endphp
<div x-data="{ open: {{ $isSettingsActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 transition-all {{ $isSettingsActive ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isSettingsActive ? 'filled' : '' }}">admin_panel_settings</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Integrasi & Profil</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="block py-2 text-xs {{ request()->routeIs('whatsapp.settings*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.settings') }}">WhatsApp Connect</a>
        <a class="block py-2 text-xs {{ request()->routeIs('instagram.settings*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('instagram.settings') }}">Instagram Connect</a>
        <a class="block py-2 text-xs {{ request()->routeIs('settings.business*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('settings.business') }}">Profil Bisnis</a>
        <a class="block py-2 text-xs text-gray-500 cursor-not-allowed opacity-50" href="#">Preferensi Sistem</a>
    </div>
</div>

{{-- VIP/Plan Info --}}
<div class="mt-auto pt-6 px-4">
    @if($isVip)
        <div class="p-4 bg-gradient-to-r from-indigo-600 to-blue-600 rounded-xl shadow-lg shadow-indigo-900/40 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-16 h-16 bg-white/10 rounded-full -mr-8 -mt-8 blur-2xl group-hover:bg-white/20 transition-all"></div>
            <div class="flex items-center gap-2 mb-1 relative z-10">
                <span class="material-symbols-outlined text-white text-sm filled">workspace_premium</span>
                <span class="text-[10px] font-black text-white uppercase tracking-wider">VIP Member</span>
            </div>
            <p class="text-[9px] text-indigo-100/80 relative z-10">Akses tanpa batas aktif</p>
        </div>
    @elseif($user && ($plan = $user->getPlan()))
        <div class="p-3 bg-blue-500/5 border border-blue-500/10 rounded-xl">
            <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">{{ $plan->name }}</p>
            <div class="flex items-center justify-between text-[9px] text-gray-500">
                <span>Status: Aktif</span>
                <a href="{{ route('pricing') }}" class="text-blue-400 hover:underline">Upgrade</a>
            </div>
        </div>
    @endif
</div>

{{-- Bantuan --}}
<div class="mt-4 pt-4 border-t border-gray-800">
    <a class="flex items-center gap-3 px-4 py-2 text-gray-500 hover:text-white transition-colors" href="{{ route('documentation.index') }}">
        <span class="material-symbols-outlined text-[20px]">help</span>
        <span class="text-sm">Bantuan</span>
    </a>
</div>
