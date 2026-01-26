@extends('admin.layouts.app')

@section('title', 'Platform Statistics')
@section('page_title', 'Platform Statistics')

@section('content')
@php
    // Message counts with safe fallbacks
    try {
        $igMessages = \App\Models\Message::count();
    } catch (\Exception $e) {
        $igMessages = 0;
    }
    
    try {
        $waMessages = \App\Models\WaMessage::count();
    } catch (\Exception $e) {
        $waMessages = 0;
    }
    
    $totalMessages = $igMessages + $waMessages;
    
    // Today's messages
    try {
        $igMessagesToday = \App\Models\Message::whereDate('created_at', today())->count();
    } catch (\Exception $e) {
        $igMessagesToday = 0;
    }
    
    try {
        $waMessagesToday = \App\Models\WaMessage::whereDate('created_at', today())->count();
    } catch (\Exception $e) {
        $waMessagesToday = 0;
    }
    
    // AI Usage (last 30 days)
    try {
        $aiUsage = \App\Models\Message::where('sender_type', 'bot')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    } catch (\Exception $e) {
        $aiUsage = 0;
    }
    
    // Connected accounts
    try {
        $igAccounts = \App\Models\InstagramAccount::count();
    } catch (\Exception $e) {
        $igAccounts = 0;
    }
    
    // WhatsApp accounts - handle model not existing
    try {
        $waAccounts = class_exists(\App\Models\WaSetting::class) 
            ? \App\Models\WaSetting::count() 
            : \App\Models\WaConversation::distinct('phone_number_id')->count();
    } catch (\Exception $e) {
        $waAccounts = 0;
    }
    
    // Message trend (7 days)
    $messageTrend = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        try {
            $ig = \App\Models\Message::whereDate('created_at', $date)->count();
        } catch (\Exception $e) {
            $ig = 0;
        }
        try {
            $wa = \App\Models\WaMessage::whereDate('created_at', $date)->count();
        } catch (\Exception $e) {
            $wa = 0;
        }
        $messageTrend[$date] = ['ig' => $ig, 'wa' => $wa, 'total' => $ig + $wa];
    }
    $maxMessages = max(array_column($messageTrend, 'total')) ?: 1;
@endphp

<!-- Platform Overview -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Messages -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-slate-400">Total Messages</span>
            <span class="material-symbols-outlined text-slate-500">chat</span>
        </div>
        <div class="text-3xl font-black">{{ number_format($totalMessages) }}</div>
        <div class="text-sm text-slate-500 mt-1">+{{ number_format($igMessagesToday + $waMessagesToday) }} hari ini</div>
    </div>

    <!-- Instagram -->
    <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-2xl p-6 border border-pink-500/30">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-pink-300">Instagram DM</span>
            <span class="material-symbols-outlined text-pink-400">photo_camera</span>
        </div>
        <div class="text-3xl font-black text-pink-400">{{ number_format($igMessages) }}</div>
        <div class="text-sm text-pink-300/70 mt-1">{{ $igAccounts }} akun terhubung</div>
    </div>

    <!-- WhatsApp -->
    <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-2xl p-6 border border-green-500/30">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-green-300">WhatsApp</span>
            <span class="material-symbols-outlined text-green-400">chat</span>
        </div>
        <div class="text-3xl font-black text-green-400">{{ number_format($waMessages) }}</div>
        <div class="text-sm text-green-300/70 mt-1">{{ $waAccounts }} akun terhubung</div>
    </div>

    <!-- AI Responses -->
    <div class="bg-gradient-to-br from-indigo-500/20 to-blue-500/20 rounded-2xl p-6 border border-indigo-500/30">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm text-indigo-300">AI Responses</span>
            <span class="material-symbols-outlined text-indigo-400">smart_toy</span>
        </div>
        <div class="text-3xl font-black text-indigo-400">{{ number_format($aiUsage) }}</div>
        <div class="text-sm text-indigo-300/70 mt-1">30 hari terakhir</div>
    </div>
</div>

<!-- Message Trend Chart -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-6">Message Volume (7 Hari)</h3>
        <div class="flex items-end gap-2 h-48">
            @foreach($messageTrend as $date => $data)
            <div class="flex-1 flex flex-col items-center gap-1 group">
                <div class="w-full flex flex-col justify-end" style="height: 160px;">
                    <!-- WhatsApp -->
                    <div class="w-full bg-green-500 rounded-t transition-all" 
                         style="height: {{ ($data['wa'] / $maxMessages) * 80 }}px;"
                         title="WA: {{ $data['wa'] }}"></div>
                    <!-- Instagram -->
                    <div class="w-full bg-pink-500 transition-all" 
                         style="height: {{ ($data['ig'] / $maxMessages) * 80 }}px;"
                         title="IG: {{ $data['ig'] }}"></div>
                </div>
                <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
            </div>
            @endforeach
        </div>
        <div class="flex items-center justify-center gap-6 mt-4">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-pink-500 rounded"></div>
                <span class="text-sm text-slate-400">Instagram</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-500 rounded"></div>
                <span class="text-sm text-slate-400">WhatsApp</span>
            </div>
        </div>
    </div>

    <!-- Platform Distribution -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-bold text-lg mb-6">Distribusi Platform</h3>
        <div class="flex items-center justify-center mb-6">
            @php 
                $igPercent = $totalMessages > 0 ? round(($igMessages / $totalMessages) * 100) : 0;
                $waPercent = 100 - $igPercent;
            @endphp
            <div class="relative w-40 h-40">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="80" cy="80" r="70" fill="none" stroke="#334155" stroke-width="16"/>
                    <circle cx="80" cy="80" r="70" fill="none" stroke="#ec4899" stroke-width="16"
                            stroke-dasharray="{{ $igPercent * 4.4 }} 440" stroke-linecap="round"/>
                    <circle cx="80" cy="80" r="70" fill="none" stroke="#22c55e" stroke-width="16"
                            stroke-dasharray="{{ $waPercent * 4.4 }} 440" 
                            stroke-dashoffset="-{{ $igPercent * 4.4 }}" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold">{{ number_format($totalMessages) }}</span>
                </div>
            </div>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-pink-500 rounded"></div>
                    <span class="text-sm">Instagram</span>
                </div>
                <span class="text-sm font-bold text-pink-400">{{ $igPercent }}%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-500 rounded"></div>
                    <span class="text-sm">WhatsApp</span>
                </div>
                <span class="text-sm font-bold text-green-400">{{ $waPercent }}%</span>
            </div>
        </div>
    </div>
</div>
@endsection
