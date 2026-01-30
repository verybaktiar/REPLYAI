@extends('layouts.dark')

@section('title', __('analytics.title'))

@section('content')
@php
    $aiMessagesPercent = $aiMessagesLimit > 0 ? round(($aiMessagesUsed / $aiMessagesLimit) * 100) : 0;
    $contactsPercent = $contactsLimit > 0 ? round(($totalContacts / $contactsLimit) * 100) : 0;
    $broadcastsPercent = $broadcastsLimit > 0 ? round(($broadcastsUsed / $broadcastsLimit) * 100) : 0;
    
    // Message Stats for chart (from controller data)
    $messageStats = [];
    foreach($dailyVolume as $vol) {
        $messageStats[$vol['date']] = [
            'incoming' => $vol['whatsapp'] + $vol['instagram'],
            'outgoing' => $vol['total'] > 0 ? ($vol['total'] * 0.8) : 0, // Placeholder for outgoing daily volume if not provided
        ];
    }
    
    // We already have dailyVolume from controller, let's use it properly
    $maxMessages = 1;
    foreach($dailyVolume as $vol) {
        if(($vol['whatsapp'] + $vol['instagram']) > $maxMessages) {
            $maxMessages = ($vol['whatsapp'] + $vol['instagram']);
        }
    }
@endphp

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ __('analytics.title') }}</h1>
            <p class="text-slate-400">{{ __('analytics.subtitle') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 text-sm text-slate-400 bg-surface-dark px-4 py-2 rounded-lg border border-slate-800">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                <span>{{ __('analytics.period') }}: {{ __('analytics.last_30_days') }}</span>
            </div>
            <a href="{{ route('analytics.export') }}" class="flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-lg hover:bg-primary/20 transition-all font-semibold">
                <span class="material-symbols-outlined text-[20px]">download</span>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Stats Summary Row 2 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Total Pesan</p>
            <h4 class="text-2xl font-black text-white">{{ number_format($totalMessages) }}</h4>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-[10px] text-primary font-bold">In: {{ number_format($incomingCount) }}</span>
                <span class="text-[10px] text-purple-500 font-bold">Out: {{ number_format($outgoingCount) }}</span>
            </div>
        </div>
        
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Bot Resolution</p>
            <h4 class="text-2xl font-black text-green-400">{{ $resolutionRate }}%</h4>
            <p class="text-[10px] text-slate-500 mt-2">Pesan dijawab otomatis oleh bot</p>
        </div>

        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">CS Handoff</p>
            <h4 class="text-2xl font-black text-orange-400">{{ $handoffRate }}%</h4>
            <p class="text-[10px] text-slate-500 mt-2">Sedang ditangani Agent/CS</p>
        </div>

        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Avg Response Time</p>
            <h4 class="text-2xl font-black text-blue-400">{{ $avgResponseTime }}s</h4>
            <p class="text-[10px] text-slate-500 mt-2">Waktu rata-rata bot membalas</p>
        </div>
    </div>

    <!-- Usage Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- AI Messages -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-300">{{ __('analytics.ai_messages') }}</h3>
                <span class="text-2xl">🤖</span>
            </div>
            <div class="flex items-end justify-between mb-3">
                <span class="text-3xl font-bold text-primary">{{ number_format($aiMessagesUsed) }}</span>
                <span class="text-slate-500">/ {{ $aiMessagesLimit == -1 ? '∞' : number_format($aiMessagesLimit) }}</span>
            </div>
            <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-primary to-purple-500 transition-all" style="width: {{ min($aiMessagesPercent, 100) }}%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-2">{{ $aiMessagesPercent }}% {{ __('analytics.used') }}</p>
        </div>

        <!-- Contacts -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-300">{{ __('analytics.contacts') }}</h3>
                <span class="text-2xl">👥</span>
            </div>
            <div class="flex items-end justify-between mb-3">
                <span class="text-3xl font-bold text-green-400">{{ number_format($totalContacts) }}</span>
                <span class="text-slate-500">/ {{ $contactsLimit == -1 ? '∞' : number_format($contactsLimit) }}</span>
            </div>
            <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-green-500 to-emerald-400" style="width: {{ min($contactsPercent, 100) }}%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-2">{{ $contactsPercent }}% {{ __('analytics.used') }}</p>
        </div>

        <!-- Broadcasts -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-300">{{ __('analytics.broadcasts') }}</h3>
                <span class="text-2xl">📢</span>
            </div>
            <div class="flex items-end justify-between mb-3">
                <span class="text-3xl font-bold text-orange-400">{{ number_format($broadcastsUsed) }}</span>
                <span class="text-slate-500">/ {{ $broadcastsLimit == -1 ? '∞' : number_format($broadcastsLimit) }}</span>
            </div>
            <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-orange-500 to-yellow-400" style="width: {{ min($broadcastsPercent, 100) }}%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-2">{{ $broadcastsPercent }}% {{ __('analytics.used') }}</p>
        </div>
    </div>

    <!-- Message Chart -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-semibold text-lg mb-6">{{ __('analytics.message_activity') }}</h3>
        <div class="flex items-end justify-between gap-2 h-48">
            @foreach($messageStats as $date => $stats)
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="w-full flex flex-col gap-1" style="height: 160px;">
                    <!-- Outgoing (top) -->
                    <div class="flex-1 flex flex-col justify-end">
                        <div class="w-full bg-purple-500 rounded-t" 
                             style="height: {{ ($stats['outgoing'] / $maxMessages) * 100 }}%"
                             title="{{ $stats['outgoing'] }} {{ __('analytics.outgoing') }}"></div>
                    </div>
                    <!-- Incoming (bottom) -->
                    <div class="w-full bg-primary rounded-b" 
                         style="height: {{ ($stats['incoming'] / $maxMessages) * 100 }}px; min-height: 4px;"
                         title="{{ $stats['incoming'] }} {{ __('analytics.incoming') }}"></div>
                </div>
                <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
            </div>
            @endforeach
        </div>
        <div class="flex items-center justify-center gap-6 mt-4">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-primary rounded"></div>
                <span class="text-sm text-slate-400">{{ __('analytics.incoming') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-purple-500 rounded"></div>
                <span class="text-sm text-slate-400">{{ __('analytics.outgoing') }}</span>
            </div>
        </div>
    </div>
    <!-- Recent Activity Section -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800">
            <h3 class="font-bold text-lg">Aktivitas Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-800/50 text-[10px] uppercase font-black text-slate-500 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Nama / Nomor</th>
                        <th class="px-6 py-4">Pesan Terakhir</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-white/[0.02] transition-colors group">
                        <td class="px-6 py-4">
                            <span class="text-xs text-slate-400 font-medium">{{ \Carbon\Carbon::parse($log['time'])->diffForHumans() }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-white">{{ $log['name'] }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $log['phone'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs text-slate-300 truncate max-w-xs">{{ $log['message'] }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($log['has_reply'])
                                <span class="px-2 py-1 bg-green-500/10 text-green-400 text-[9px] font-black uppercase rounded-full">Resolved</span>
                            @else
                                <span class="px-2 py-1 bg-blue-500/10 text-blue-400 text-[9px] font-black uppercase rounded-full">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <p class="text-slate-500 text-sm italic">Belum ada aktivitas tercatat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
