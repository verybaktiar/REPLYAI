{{-- ==================== SIDEBAR - PROFESSIONAL GROUPS ==================== --}}
@php
    $user = auth()->user();
    $isVip = $user?->is_vip ?? false;
    $hasFeature = fn($feature) => $isVip || ($user?->hasFeature($feature) ?? false);
    
    // Get user tier for menu filtering
    $planTier = $user?->getPlanTier() ?? 'umkm';
    $isUmkm = $planTier === 'umkm';
    $isBusiness = $planTier === 'business';
    $isEnterprise = $planTier === 'enterprise';
    
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

{{-- 2. CHAT (Grouped by Tier) --}}
@php
    $isChatActive = request()->routeIs('inbox*') || request()->routeIs('whatsapp.inbox*') || request()->routeIs('contacts*') || request()->routeIs('chat.inbox*') || request()->routeIs('my-assignments*') || request()->routeIs('instagram.comments*') || request()->routeIs('segments*');
    $isInstagramActive = request()->routeIs('inbox*') && !request()->routeIs('whatsapp.inbox*') || request()->routeIs('instagram.comments*');
    
    // My assignments count
    $myAssignmentsCount = 0;
    try {
        $myAssignmentsCount = \App\Models\ChatAssignment::where('user_id', $user?->id)
            ->where('status', 'active')
            ->count();
    } catch (\Exception $e) {}
@endphp
<div x-data="{ open: {{ $isChatActive ? 'true' : 'false' }}, igOpen: {{ $isInstagramActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 transition-all {{ $isChatActive ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isChatActive ? 'filled' : '' }}">chat</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Chat</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        {{-- WhatsApp --}}
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('whatsapp.inbox*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.inbox') }}">
            <span class="material-symbols-outlined text-[16px]">chat_bubble</span>
            <span class="flex-1">WhatsApp</span>
            @if($unreadWhatsApp > 0)
                <span class="bg-green-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ $unreadWhatsApp }}</span>
            @endif
        </a>
        
        {{-- Instagram - UMKM: Simple link, Business+: Group --}}
        @if($isUmkm)
            {{-- UMKM: Simple Instagram link (DM only) --}}
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('inbox*') && !request()->routeIs('whatsapp.inbox*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('inbox') }}">
                <svg class="w-4 h-4 text-pink-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                </svg>
                <span class="flex-1">Instagram</span>
                @if($unreadInbox > 0)
                    <span class="bg-red-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ $unreadInbox }}</span>
                @endif
            </a>
        @else
            {{-- Business+: Group with Comments --}}
            <div class="space-y-1">
                <button @click="igOpen = !igOpen" class="w-full flex items-center gap-2 py-2 text-xs {{ $isInstagramActive ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}">
                    <svg class="w-4 h-4 text-pink-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                    </svg>
                    <span class="flex-1 text-left">Instagram</span>
                    <span class="material-symbols-outlined text-xs transition-transform" :class="igOpen ? 'rotate-180' : ''">expand_more</span>
                </button>
                
                <div x-show="igOpen" x-collapse class="ml-4 border-l border-gray-700 pl-3 space-y-1">
                    {{-- Instagram DM --}}
                    <a class="flex items-center gap-2 py-1.5 text-xs {{ request()->routeIs('inbox*') && !request()->routeIs('whatsapp.inbox*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('inbox') }}">
                        <span class="material-symbols-outlined text-[14px]">chat</span>
                        <span class="flex-1">DM</span>
                        @if($unreadInbox > 0)
                            <span class="bg-red-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ $unreadInbox }}</span>
                        @endif
                    </a>
                    
                    {{-- Instagram Comments - Enterprise only --}}
                    @if($isEnterprise)
                        <a class="flex items-center gap-2 py-1.5 text-xs {{ request()->routeIs('instagram.comments*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('instagram.comments') }}">
                            <span class="material-symbols-outlined text-[14px]">mode_comment</span>
                            <span>Komentar</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif
        
        {{-- Kontak --}}
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('contacts*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('contacts.index') }}">
            <span class="material-symbols-outlined text-[16px]">contacts</span>
            <span>Kontak</span>
        </a>
        
        {{-- Segment - Business+ only --}}
        @if($isBusiness || $isEnterprise)
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('segments*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('segments.index') }}">
                <span class="material-symbols-outlined text-[16px]">folder_copy</span>
                <span>Segment</span>
            </a>
        @endif
        
        {{-- Tugas Saya --}}
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('my-assignments*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('my-assignments') }}">
            <span class="material-symbols-outlined text-[16px]">assignment_ind</span>
            <span class="flex-1">Tugas Saya</span>
            @if($myAssignmentsCount > 0)
                <span class="bg-blue-500 text-white text-[9px] px-1.5 py-0.5 rounded-full">{{ $myAssignmentsCount }}</span>
            @endif
        </a>
    </div>
</div>

{{-- 4. LAPORAN (Grouped by Tier) --}}
@php
    $isReportActive = request()->routeIs('analytics*') || request()->routeIs('logs*') || request()->routeIs('admin.analytics*') || request()->routeIs('reports.*') || request()->routeIs('ai-performance*');
@endphp
<div x-data="{ open: {{ $isReportActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 transition-all {{ $isReportActive ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isReportActive ? 'filled' : '' }}">bar_chart_4_bars</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">Laporan</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        {{-- === UMKM: Statistik & Riwayat saja === --}}
        @if(Route::has('analytics.index'))
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('analytics*') && !request()->routeIs('admin.analytics*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('analytics.index') }}">
            <span class="material-symbols-outlined text-[16px]">analytics</span>
            <span>Statistik</span>
        </a>
        @endif
        
        {{-- AI Analytics - Business+ only --}}
        @if($isBusiness || $isEnterprise)
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('ai-performance*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('ai-performance.index') }}">
                <span class="material-symbols-outlined text-[16px]">smart_toy</span>
                <span>AI Analytics</span>
            </a>
        @endif
        
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('logs*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('logs.index') }}">
            <span class="material-symbols-outlined text-[16px]">history</span>
            <span>Riwayat</span>
        </a>
        
        {{-- === BUSINESS & ENTERPRISE ONLY === --}}
        @if($isBusiness || $isEnterprise)
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('reports.realtime') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('reports.realtime') }}">
                <span class="material-symbols-outlined text-[16px]">rss_feed</span>
                <span>Realtime</span>
            </a>
            
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('reports.quality') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('reports.quality') }}">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                <span>Kualitas Chat</span>
            </a>
        @endif
        
        {{-- === ENTERPRISE ONLY === --}}
        @if($isEnterprise)
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('reports.comparative') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('reports.comparative') }}">
                <span class="material-symbols-outlined text-[16px]">compare_arrows</span>
                <span>Perbandingan</span>
            </a>
            
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('reports.scheduled*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('reports.scheduled.index') }}">
                <span class="material-symbols-outlined text-[16px]">schedule</span>
                <span>Jadwal Laporan</span>
            </a>
            
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('reports.templates*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('reports.templates.index') }}">
                <span class="material-symbols-outlined text-[16px]">description</span>
                <span>Template</span>
            </a>
        @endif
    </div>
</div>

{{-- 3. AI SETUP (Grouped by Tier) --}}
@php
    $isAiActive = request()->routeIs('rules*') || request()->routeIs('kb*') || request()->routeIs('quick-replies*') || request()->routeIs('automations*');
@endphp
<div x-data="{ open: {{ $isAiActive ? 'true' : 'false' }} }" class="space-y-1">
    <button @click="open = !open" 
            class="w-full flex items-center gap-3 px-4 py-2.5 transition-all {{ $isAiActive ? 'bg-blue-500/10 text-blue-400 border-l-[3px] border-blue-500' : 'rounded-xl text-gray-400 hover:bg-gray-800/50 hover:text-white' }}">
        <span class="material-symbols-outlined text-[24px] {{ $isAiActive ? 'filled' : '' }}">psychology</span>
        <span class="text-xs font-bold uppercase tracking-tighter text-left flex-1">AI Setup</span>
        <span class="material-symbols-outlined text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" x-collapse class="ml-4 border-l border-gray-800 pl-4 space-y-1">
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('kb*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('kb.index') }}">
            <span class="material-symbols-outlined text-[16px]">school</span>
            <span>Basis Pengetahuan</span>
        </a>
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('rules*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('rules.index') }}">
            <span class="material-symbols-outlined text-[16px]">rule</span>
            <span>Atur Balasan</span>
        </a>
        
        {{-- Chat Automation - Business+ only --}}
        @if($isBusiness || $isEnterprise)
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('automations*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('automations.index') }}">
                <span class="material-symbols-outlined text-[16px]">auto_mode</span>
                <span>Chat Automation</span>
            </a>
        @endif
        
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('quick-replies*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('quick-replies.index') }}">
            <span class="material-symbols-outlined text-[16px]">quickreply</span>
            <span>Balasan Cepat</span>
        </a>
    </div>
</div>

{{-- 4. PROMOSI (Grouped by Tier) - Business & Enterprise only --}}
@if($isBusiness || $isEnterprise)
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
        {{-- Broadcast: Business & Enterprise only --}}
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('whatsapp.broadcast*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.broadcast.index') }}">
            <span class="material-symbols-outlined text-[16px]">campaign</span>
            <span>Broadcast</span>
        </a>
        
        {{-- Sequences: Enterprise only --}}
        @if($isEnterprise)
            <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('sequences*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('sequences.index') }}">
                <span class="material-symbols-outlined text-[16px]">schedule_send</span>
                <span>Pesan Terjadwal</span>
            </a>
        @endif
    </div>
</div>
@endif

{{-- 6. INTEGRASI & PROFIL (Grouped) --}}
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
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('whatsapp.settings*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('whatsapp.settings') }}">
            <span class="material-symbols-outlined text-[16px]">chat</span>
            <span>WhatsApp Connect</span>
        </a>
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('instagram.settings*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('instagram.settings') }}">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            <span>Instagram Connect</span>
        </a>
        <a class="flex items-center gap-2 py-2 text-xs {{ request()->routeIs('settings.business*') ? 'text-white font-bold' : 'text-gray-500 hover:text-white' }}" href="{{ route('settings.business') }}">
            <span class="material-symbols-outlined text-[16px]">business</span>
            <span>Profil Bisnis</span>
        </a>
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
            <div class="flex items-center justify-between mb-1">
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">{{ $plan->name }}</p>
                {{-- Tier Badge --}}
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded-full 
                    {{ $planTier === 'enterprise' ? 'bg-purple-500/20 text-purple-400' : 
                       ($planTier === 'business' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-gray-500/20 text-gray-400') }}">
                    {{ $planTier === 'enterprise' ? 'Enterprise' : ($planTier === 'business' ? 'Business' : 'UMKM') }}
                </span>
            </div>
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
