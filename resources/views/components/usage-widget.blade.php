@props(['user'])

@php
    use App\Models\UsageRecord;
    
    $subscription = $user->activeSubscription();
    $plan = $subscription?->plan;
    
    // Jika tidak ada plan, jangan render widget
    $hasValidPlan = $plan && isset($plan->limits);
    
    if ($hasValidPlan) {
        // Ambil limits dari plan
        $limits = [
            'ai_messages' => $plan->limits['ai_messages_monthly'] ?? 500,
            'broadcasts' => $plan->limits['broadcasts_monthly'] ?? 5,
        ];
        
        // Ambil usage saat ini
        $usage = [
            'ai_messages' => UsageRecord::getUsage($user->id, UsageRecord::FEATURE_AI_MESSAGES),
            'broadcasts' => UsageRecord::getUsage($user->id, UsageRecord::FEATURE_BROADCASTS),
        ];
        
        // Hitung persentase
        $aiPercent = $limits['ai_messages'] > 0 ? min(100, round(($usage['ai_messages'] / $limits['ai_messages']) * 100)) : 0;
        $broadcastPercent = $limits['broadcasts'] > 0 ? min(100, round(($usage['broadcasts'] / $limits['broadcasts']) * 100)) : 0;
    }
@endphp

@if($hasValidPlan)
<div class="bg-surface-dark rounded-xl border border-slate-700 p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">monitoring</span>
            Penggunaan Bulan Ini
        </h3>
        <span class="text-xs text-slate-400">Reset: {{ now()->endOfMonth()->format('d M') }}</span>
    </div>
    
    {{-- AI Messages Usage --}}
    <div class="mb-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-300">Pesan AI</span>
            <span class="text-sm font-mono {{ $aiPercent >= 90 ? 'text-red-400' : ($aiPercent >= 70 ? 'text-yellow-400' : 'text-green-400') }}">
                {{ number_format($usage['ai_messages']) }} / {{ number_format($limits['ai_messages']) }}
            </span>
        </div>
        <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
            <div class="h-full transition-all duration-500 rounded-full
                {{ $aiPercent >= 90 ? 'bg-red-500' : ($aiPercent >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                style="width: {{ $aiPercent }}%"></div>
        </div>
        @if($aiPercent >= 80)
        <p class="text-xs text-yellow-400 mt-1">
            ⚠️ Hampir mencapai limit. <a href="{{ route('subscription.index') }}" class="underline">Upgrade paket</a>
        </p>
        @endif
    </div>
    
    {{-- Broadcasts Usage --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-300">Broadcast</span>
            <span class="text-sm font-mono {{ $broadcastPercent >= 90 ? 'text-red-400' : ($broadcastPercent >= 70 ? 'text-yellow-400' : 'text-green-400') }}">
                {{ number_format($usage['broadcasts']) }} / {{ number_format($limits['broadcasts']) }}
            </span>
        </div>
        <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
            <div class="h-full transition-all duration-500 rounded-full
                {{ $broadcastPercent >= 90 ? 'bg-red-500' : ($broadcastPercent >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                style="width: {{ $broadcastPercent }}%"></div>
        </div>
    </div>
    
    {{-- Upgrade CTA if near limit --}}
    @if($aiPercent >= 90 || $broadcastPercent >= 90)
    <a href="{{ route('subscription.index') }}" 
       class="mt-4 w-full block text-center py-2 rounded-lg bg-gradient-to-r from-primary to-blue-500 text-white font-semibold text-sm hover:from-blue-600 hover:to-blue-700 transition-all">
        🚀 Upgrade untuk Unlimited
    </a>
    @endif
</div>
@endif

