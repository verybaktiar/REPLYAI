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
<a class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('dashboard') }}">
    <span class="material-symbols-outlined text-[22px]">dashboard</span>
    <span class="text-sm font-semibold">Beranda</span>
</a>

{{-- 2. PESAN --}}
<div class="mt-6 mb-2 px-4 flex items-center justify-between">
    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Pesan</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('inbox*') && !request()->routeIs('whatsapp.inbox*') ? 'bg-blue-600/10 text-blue-400' : 'text-gray-400 hover:bg-gray-800' }}" href="{{ route('inbox') }}">
    <span class="material-symbols-outlined text-[20px]">inbox</span>
    <span class="text-sm">Kotak Masuk</span>
    @if($unreadInbox > 0)
        <span class="ml-auto bg-red-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $unreadInbox > 99 ? '99+' : $unreadInbox }}</span>
    @endif
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.inbox*') ? 'bg-green-600/10 text-green-400' : 'text-gray-400 hover:bg-gray-800' }}" href="{{ route('whatsapp.inbox') }}">
    <span class="material-symbols-outlined text-[20px]">chat</span>
    <span class="text-sm font-semibold">WhatsApp</span>
    @if($unreadWhatsApp > 0)
        <span class="ml-auto bg-green-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $unreadWhatsApp > 99 ? '99+' : $unreadWhatsApp }}</span>
    @endif
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('contacts*') ? 'bg-blue-600/10 text-blue-400' : 'text-gray-400 hover:bg-gray-800' }}" href="{{ route('contacts.index') }}">
    <span class="material-symbols-outlined text-[20px]">group</span>
    <span class="text-sm">Pelanggan</span>
</a>

{{-- 3. CHATBOT --}}
<div class="mt-6 mb-2 px-4 flex items-center justify-between">
    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">AI & Automation</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('rules*') ? 'bg-blue-600/10 text-blue-400' : 'text-gray-400 hover:bg-gray-800' }}" href="{{ route('rules.index') }}">
    <span class="material-symbols-outlined text-[20px]">smart_toy</span>
    <span class="text-sm">Aturan Bot</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('kb*') ? 'bg-blue-600/10 text-blue-400' : 'text-gray-400 hover:bg-gray-800' }}" href="{{ route('kb.index') }}">
    <span class="material-symbols-outlined text-[20px]">neurology</span>
    <span class="text-sm">Knowledge Base</span>
</a>

{{-- Sequences --}}
@if($hasFeature('sequences'))
<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('sequences*') ? 'bg-blue-600/10 text-blue-400' : 'text-gray-400 hover:bg-gray-800' }}" href="{{ route('sequences.index') }}">
    <span class="material-symbols-outlined text-[20px]">schedule_send</span>
    <span class="text-sm">Pesan Terjadwal</span>
</a>
@else
<a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 opacity-60" href="{{ route('upgrade', ['feature' => 'sequences']) }}">
    <span class="material-symbols-outlined text-[20px]">lock</span>
    <span class="text-sm">Pesan Terjadwal</span>
    <span class="ml-auto text-[9px] font-bold bg-yellow-500/20 text-yellow-500 px-1 rounded">PRO</span>
</a>
@endif

{{-- 4. TOOLS --}}
<div class="mt-6 mb-2 px-4 flex items-center justify-between">
    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Growth</p>
</div>

{{-- Broadcast --}}
@if($hasFeature('broadcasts'))
<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.broadcast*') ? 'bg-blue-600/10 text-blue-400' : 'text-gray-400 hover:bg-gray-800' }}" href="{{ route('whatsapp.broadcast.index') }}">
    <span class="material-symbols-outlined text-[20px]">campaign</span>
    <span class="text-sm">Broadcast</span>
</a>
@else
<a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 opacity-60" href="{{ route('upgrade', ['feature' => 'broadcasts']) }}">
    <span class="material-symbols-outlined text-[20px]">lock</span>
    <span class="text-sm">Broadcast</span>
</a>
@endif

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('analytics*') ? 'bg-blue-600/10 text-blue-400' : 'text-gray-400 hover:bg-gray-800' }}" href="{{ route('analytics.index') }}">
    <span class="material-symbols-outlined text-[20px]">analytics</span>
    <span class="text-sm">Statistik</span>
</a>

{{-- 5. PENGATURAN (Grouped) --}}
<div class="mt-6 mb-2">
    <button @click="toggleSubmenu('settings')" 
            class="w-full flex items-center gap-3 px-4 py-2 rounded-lg transition-all text-gray-400 hover:bg-gray-800">
        <span class="material-symbols-outlined text-[20px]">settings</span>
        <span class="text-sm font-medium">Pengaturan</span>
        <span class="material-symbols-outlined ml-auto text-sm transition-transform" :class="openSubmenu === 'settings' ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="openSubmenu === 'settings'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-1 ml-4 border-l border-gray-800 pl-4 space-y-1">
        
        <a class="block py-2 text-xs {{ request()->routeIs('whatsapp.settings*') ? 'text-white' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.settings') }}">WhatsApp Connect</a>
        <a class="block py-2 text-xs {{ request()->routeIs('instagram.settings*') ? 'text-white' : 'text-gray-500 hover:text-white' }}" href="{{ route('instagram.settings') }}">Instagram Connect</a>
        <a class="block py-2 text-xs {{ request()->routeIs('settings.business*') ? 'text-white' : 'text-gray-500 hover:text-white' }}" href="{{ route('settings.business') }}">Profil Bisnis</a>
        <a class="block py-2 text-xs {{ request()->routeIs('settings.index') ? 'text-white' : 'text-gray-500 hover:text-white' }}" href="{{ route('settings.index') }}">Jam Operasional</a>
        <a class="block py-2 text-xs {{ request()->routeIs('logs*') ? 'text-white' : 'text-gray-500 hover:text-white' }}" href="{{ route('logs.index') }}">Riwayat Aktivitas</a>
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
