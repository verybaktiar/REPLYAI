{{-- ==================== SIDEBAR - DYNAMIC FEATURE GATING ==================== --}}
@php
    $user = auth()->user();
    $isVip = $user?->is_vip ?? false;
    
    // Helper untuk cek akses fitur
    $hasFeature = fn($feature) => $isVip || ($user?->hasFeature($feature) ?? false);
    
    // Unread message counts - count messages from contacts (incoming) in last 24h that might need attention
    // Using sender_type='contact' as indicator of incoming messages
    $unreadInbox = 0;
    $unreadWhatsApp = 0;
    
    try {
        $unreadInbox = \App\Models\Message::whereHas('conversation', function($q) use ($user) {
                $q->whereHas('instagramAccount', fn($q2) => $q2->where('user_id', $user?->id));
            })
            ->where('sender_type', 'contact')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereDoesntHave('conversation', fn($q) => $q->where('status', 'agent_handling'))
            ->count();
    } catch (\Exception $e) {
        $unreadInbox = 0;
    }
    
    try {
        $unreadWhatsApp = \App\Models\WaMessage::whereHas('waConversation', function($q) use ($user) {
                $q->where('user_id', $user?->id);
            })
            ->where('direction', 'incoming')
            ->where('is_read', false)
            ->count();
    } catch (\Exception $e) {
        $unreadWhatsApp = 0;
    }
@endphp

{{-- Dashboard (semua user bisa akses) --}}
<a class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('dashboard') }}" data-tour="sidebar">
    <span class="text-lg">🏠</span>
    <span class="text-sm font-medium">Beranda</span>
</a>

{{-- Section: PESAN (semua user bisa akses) --}}
<div class="mt-4 mb-1 px-4">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">💬 Pesan</p>
    <p class="text-[9px] text-gray-600">Lihat percakapan pelanggan</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('inbox*') && !request()->routeIs('whatsapp.inbox*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('inbox') }}" data-tour="inbox">
    <span class="text-base">📬</span>
    <span class="text-sm">Kotak Masuk</span>
    @if($unreadInbox > 0)
        <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full animate-pulse">{{ $unreadInbox > 99 ? '99+' : $unreadInbox }}</span>
    @endif
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.inbox*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('whatsapp.inbox') }}">
    <span class="text-base">💬</span>
    <span class="text-sm">WhatsApp</span>
    @if($unreadWhatsApp > 0)
        <span class="ml-auto bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-full animate-pulse">{{ $unreadWhatsApp > 99 ? '99+' : $unreadWhatsApp }}</span>
    @endif
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('contacts*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('contacts.index') }}">
    <span class="text-base">👥</span>
    <span class="text-sm">Daftar Pelanggan</span>
</a>

{{-- Section: CHATBOT --}}
<div class="mt-4 mb-1 px-4">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">🤖 Chatbot</p>
    <p class="text-[9px] text-gray-600">Atur respon otomatis</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('rules*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('rules.index') }}">
    <span class="text-base">⚙️</span>
    <span class="text-sm">Pengaturan Bot</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('kb*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('kb.index') }}">
    <span class="text-base">📚</span>
    <span class="text-sm">Info Produk</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('quick-replies*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('quick-replies.index') }}">
    <span class="text-base">⚡</span>
    <span class="text-sm">Balasan Cepat</span>
</a>

{{-- Sequences - PRO Feature --}}
@if($hasFeature('sequences'))
<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('sequences*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('sequences.index') }}">
    <span class="text-base">📅</span>
    <span class="text-sm">Pesan Otomatis</span>
</a>
@else
<a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-500 hover:bg-gray-800/50 opacity-60" href="{{ route('upgrade', ['feature' => 'sequences']) }}">
    <span class="text-base">📅</span>
    <span class="text-sm">Pesan Otomatis</span>
    <span class="ml-auto px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] font-bold rounded">PRO</span>
</a>
@endif

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('simulator*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('simulator.index') }}">
    <span class="text-base">🧪</span>
    <span class="text-sm">Test Bot</span>
</a>

{{-- Section: PROMOSI --}}
<div class="mt-4 mb-1 px-4">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">📢 Promosi</p>
    <p class="text-[9px] text-gray-600">Kirim pesan massal</p>
</div>

{{-- Broadcast - PRO Feature --}}
@if($hasFeature('broadcasts'))
<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.broadcast*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('whatsapp.broadcast.index') }}">
    <span class="text-base">📣</span>
    <span class="text-sm">Broadcast</span>
</a>
@else
<a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-500 hover:bg-gray-800/50 opacity-60" href="{{ route('upgrade', ['feature' => 'broadcasts']) }}">
    <span class="text-base">📣</span>
    <span class="text-sm">Broadcast</span>
    <span class="ml-auto px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] font-bold rounded">PRO</span>
</a>
@endif

{{-- Web Widget - PRO Feature --}}
@if($hasFeature('web_widgets'))
<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('web-widgets*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('web-widgets.index') }}">
    <span class="text-base">🌐</span>
    <span class="text-sm">Chat di Website</span>
</a>
@else
<a class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-500 hover:bg-gray-800/50 opacity-60" href="{{ route('upgrade', ['feature' => 'web_widgets']) }}">
    <span class="text-base">🌐</span>
    <span class="text-sm">Chat di Website</span>
    <span class="ml-auto px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] font-bold rounded">PRO</span>
</a>
@endif

{{-- Section: LAPORAN --}}
<div class="mt-4 mb-1 px-4">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">📊 Laporan</p>
    <p class="text-[9px] text-gray-600">Lihat statistik</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('analytics*') && !request()->routeIs('whatsapp.analytics*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('analytics.index') }}">
    <span class="text-base">📈</span>
    <span class="text-sm">Statistik</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.analytics*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('whatsapp.analytics') }}">
    <span class="text-base">📱</span>
    <span class="text-sm">Laporan WhatsApp</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('csat*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('csat.index') }}">
    <span class="text-base">⭐</span>
    <span class="text-sm">Kepuasan Pelanggan</span>
</a>

{{-- Section: PENGATURAN --}}
<div class="mt-4 mb-1 px-4">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">⚙️ Pengaturan</p>
    <p class="text-[9px] text-gray-600">Konfigurasi sistem</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.settings*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('whatsapp.settings') }}">
    <span class="text-base">📲</span>
    <span class="text-sm">Hubungkan WhatsApp</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('instagram.settings*') ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('instagram.settings') }}">
    <span class="text-base">📸</span>
    <span class="text-sm">Hubungkan Instagram</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('settings.business*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('settings.business') }}">
    <span class="text-base">🏢</span>
    <span class="text-sm">Profil Bisnis</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('settings.index') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('settings.index') }}">
    <span class="text-base">🕐</span>
    <span class="text-sm">Jam Buka</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('logs*') || request()->routeIs('takeover.logs*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('logs.index') }}">
    <span class="text-base">📋</span>
    <span class="text-sm">Riwayat Aktivitas</span>
</a>

{{-- VIP Badge (hanya untuk VIP users) --}}
@if($isVip)
<div class="mt-4 px-4">
    <div class="px-3 py-2 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
        <p class="text-xs font-bold text-yellow-400">⭐ VIP Access</p>
        <p class="text-[10px] text-yellow-500/70">Semua fitur aktif</p>
    </div>
</div>
@endif

{{-- Subscription Info --}}
@if($user)
@php $plan = $user->getPlan(); @endphp
@if($plan && !$plan->is_free)
<div class="mt-4 px-4">
    <div class="px-3 py-2 bg-primary/10 border border-primary/30 rounded-lg">
        <p class="text-xs font-bold text-primary">{{ $plan->name }}</p>
        <p class="text-[10px] text-gray-500">Paket aktif</p>
    </div>
</div>
@endif
@endif

{{-- Bantuan --}}
<div class="mt-4 pt-3 border-t border-gray-800">
    <a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('documentation.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('documentation.index') }}">
        <span class="text-base">❓</span>
        <span class="text-sm">Bantuan</span>
    </a>
</div>
