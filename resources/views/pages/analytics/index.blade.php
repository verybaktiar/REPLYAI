@extends('layouts.dark')

@section('title', __('analytics.title'))

@section('content')
@php
    $user = Auth::user();
    $subscription = $user->getActiveSubscription();
    $plan = $subscription?->plan;
    
    // Usage Stats
    $usageRecords = \App\Models\UsageRecord::where('user_id', $user->id)
        ->where('period_start', '>=', now()->subDays(30))
        ->get();
    
    // AI Messages
    $aiMessagesUsed = $usageRecords->sum('ai_messages_used');
    $aiMessagesLimit = $plan?->limits['ai_messages_monthly'] ?? 500;
    $aiMessagesPercent = $aiMessagesLimit > 0 ? round(($aiMessagesUsed / $aiMessagesLimit) * 100) : 0;
    
    // Contacts (use WaConversation as contacts proxy)
    $totalContacts = \App\Models\WaConversation::where('user_id', $user->id)->count();
    $contactsLimit = $plan?->limits['contacts'] ?? 1000;
    $contactsPercent = $contactsLimit > 0 ? round(($totalContacts / $contactsLimit) * 100) : 0;
    
    // Broadcasts
    $broadcastsUsed = $usageRecords->sum('broadcasts_used');
    $broadcastsLimit = $plan?->limits['broadcasts_monthly'] ?? 5;
    $broadcastsPercent = $broadcastsLimit > 0 ? round(($broadcastsUsed / $broadcastsLimit) * 100) : 0;
    
    // Message Stats (last 7 days) - use WaMessage
    $messageStats = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i)->format('Y-m-d');
        $messageStats[$date] = [
            'incoming' => \App\Models\WaMessage::where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->where('direction', 'incoming')
                ->count(),
            'outgoing' => \App\Models\WaMessage::where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->where('direction', 'outgoing')
                ->count(),
        ];
    }
    $maxMessages = max(1, max(array_map(fn($s) => $s['incoming'] + $s['outgoing'], $messageStats)));
@endphp

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ __('analytics.title') }}</h1>
            <p class="text-slate-400">{{ __('analytics.subtitle') }}</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <span>{{ __('analytics.period') }}: {{ __('analytics.last_30_days') }}</span>
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
</div>
@endsection
