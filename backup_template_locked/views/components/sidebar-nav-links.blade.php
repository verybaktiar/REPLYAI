{{-- ==================== SIDEBAR - USER FRIENDLY VERSION ==================== --}}

{{-- Dashboard --}}
<a class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('dashboard') }}">
    <span class="text-lg">🏠</span>
    <span class="text-sm font-medium">Beranda</span>
</a>

{{-- Section: PESAN --}}
<div class="mt-4 mb-1 px-4">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">💬 Pesan</p>
    <p class="text-[9px] text-gray-600">Lihat percakapan pelanggan</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('inbox*') && !request()->routeIs('whatsapp.inbox*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('inbox') }}">
    <span class="text-base">📬</span>
    <span class="text-sm">Kotak Masuk</span>
    @if(isset($stats['pending_inbox']) && $stats['pending_inbox'] > 0)
        <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $stats['pending_inbox'] }}</span>
    @endif
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.inbox*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('whatsapp.inbox') }}">
    <span class="text-base">💬</span>
    <span class="text-sm">WhatsApp</span>
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

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('sequences*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('sequences.index') }}">
    <span class="text-base">📅</span>
    <span class="text-sm">Pesan Otomatis</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('simulator*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('simulator.index') }}">
    <span class="text-base">🧪</span>
    <span class="text-sm">Test Bot</span>
</a>

{{-- Section: PROMOSI --}}
<div class="mt-4 mb-1 px-4">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">📢 Promosi</p>
    <p class="text-[9px] text-gray-600">Kirim pesan massal</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.broadcast*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('whatsapp.broadcast.index') }}">
    <span class="text-base">📣</span>
    <span class="text-sm">Broadcast</span>
</a>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('web-widgets*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('web-widgets.index') }}">
    <span class="text-base">🌐</span>
    <span class="text-sm">Chat di Website</span>
</a>

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

{{-- Section: PENGATURAN --}}
<div class="mt-4 mb-1 px-4">
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">⚙️ Pengaturan</p>
    <p class="text-[9px] text-gray-600">Konfigurasi sistem</p>
</div>

<a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('whatsapp.settings*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('whatsapp.settings') }}">
    <span class="text-base">📲</span>
    <span class="text-sm">Hubungkan WhatsApp</span>
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

{{-- Bantuan --}}
<div class="mt-4 pt-3 border-t border-gray-800">
    <a class="flex items-center gap-3 px-4 py-2 rounded-lg transition-all {{ request()->routeIs('documentation.*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}" href="{{ route('documentation.index') }}">
        <span class="text-base">❓</span>
        <span class="text-sm">Bantuan</span>
    </a>
</div>
