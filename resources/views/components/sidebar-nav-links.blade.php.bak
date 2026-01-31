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

{{-- 1. BERANDA --}}
<a class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-gray-400 hover:bg-gray-800/50 hover:text-white' }}" href="{{ route('dashboard') }}">
    <span class="material-symbols-outlined text-[24px] {{ request()->routeIs('dashboard') ? 'filled' : '' }}">dashboard</span>
    <div class="flex flex-col">
        <span class="text-xs font-bold uppercase tracking-tighter">Beranda</span>
    </div>
</a>

{{-- 2. CHAT (Grouped) --}}
@php
    $isChatActive = request()->routeIs('inbox*') || request()->routeIs('whatsapp.inbox*') || request()->routeIs('contacts*');
@endphp
<div x-data="{ open: {{ $isChatActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ $isChatActive ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isChatActive ? 'filled' : '' }}">chat</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Chat</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="flex items-center justify-between py-2 text-xs {{ request()->routeIs('inbox*') && !request()->routeIs('whatsapp.inbox*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('inbox') }}">
            <span>Instagram</span>
            @if($unreadInbox > 0)
                <span class="bg-red-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ $unreadInbox }}</span>
            @endif
        </a>
        <a class="flex items-center justify-between py-2 text-xs {{ request()->routeIs('whatsapp.inbox*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.inbox') }}">
            <span>WhatsApp</span>
            @if($unreadWhatsApp > 0)
                <span class="bg-green-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ $unreadWhatsApp }}</span>
            @endif
        </a>
        <a class="block py-2 text-xs {{ request()->routeIs('contacts*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('contacts.index') }}">Kontak</a>
    </div>
</div>

{{-- 3. AI SETUP (Grouped) --}}
@php
    $isAiActive = request()->routeIs('rules*') || request()->routeIs('kb*');
@endphp
<div x-data="{ open: {{ $isAiActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ $isAiActive ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
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
            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ $isPromoActive ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isPromoActive ? 'filled' : '' }}">campaign</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Promosi</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="block py-2 text-xs {{ request()->routeIs('whatsapp.broadcast*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.broadcast.index') }}">Broadcast</a>
        <a class="block py-2 text-xs {{ request()->routeIs('sequences*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('sequences.index') }}">Pesan Terjadwal</a>
    </div>
</div>

{{-- 5. PENGATURAN (Grouped) --}}
@php
    $isSettingsActive = request()->routeIs('analytics*') || request()->routeIs('settings*') || request()->routeIs('logs*') || request()->routeIs('whatsapp.settings*') || request()->routeIs('instagram.settings*');
@endphp
<div x-data="{ open: {{ $isSettingsActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ $isSettingsActive ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' : 'text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isSettingsActive ? 'filled' : '' }}">settings</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Pengaturan</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="block py-2 text-xs {{ request()->routeIs('analytics*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('analytics.index') }}">Statistik</a>
        <a class="block py-2 text-xs {{ request()->routeIs('whatsapp.settings*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.settings') }}">WhatsApp Connect</a>
        <a class="block py-2 text-xs {{ request()->routeIs('instagram.settings*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('instagram.settings') }}">Instagram Connect</a>
        <a class="block py-2 text-xs {{ request()->routeIs('settings.business*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('settings.business') }}">Profil Bisnis</a>
        <a class="block py-2 text-xs {{ request()->routeIs('logs*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('logs.index') }}">Riwayat Aktivitas</a>
    </div>
</div>

{{-- VIP/Plan Info --}}
<div class="mt-auto pt-6 px-4">
    @if($isVip)
        <div class="p-3 bg-gradient-to-br from-yellow-500/20 to-orange-500/10 border border-yellow-500/20 rounded-xl">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-yellow-500 text-sm">workspace_premium</span>
                <span class="text-[10px] font-black text-yellow-500 uppercase tracking-tighter">VIP Member</span>
            </div>
            <p class="text-[9px] text-gray-500">Akses tanpa batas aktif</p>
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
