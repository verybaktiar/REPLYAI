@extends('admin.layouts.app')

@section('title', 'Plans Management')
@section('page_title', 'Plans Management')

@section('content')

<p class="text-slate-400 mb-6">Kelola pricing dan features paket subscription</p>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($plans as $plan)
    <div class="bg-surface-dark rounded-2xl p-6 border-2 {{ $plan->is_active ? 'border-slate-800' : 'border-red-500/30' }}">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-xl font-bold mb-1">{{ $plan->name }}</h3>
                <p class="text-sm text-slate-400">{{ $plan->description }}</p>
            </div>
            @if(!$plan->is_active)
            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">Inactive</span>
            @endif
        </div>

        <div class="mb-4">
            @if($plan->price_monthly_original_display)
                <div class="text-xs text-slate-500 line-through mb-1">{{ $plan->price_monthly_original_display }}</div>
            @elseif($plan->price_monthly_original > $plan->price_monthly)
                <div class="text-xs text-slate-500 line-through mb-1">Rp {{ number_format($plan->price_monthly_original, 0, ',', '.') }}</div>
            @endif

            <div class="flex items-baseline gap-1">
                @if($plan->price_monthly_display)
                    <span class="text-3xl font-black text-primary">{{ $plan->price_monthly_display }}</span>
                @else
                    <span class="text-3xl font-black text-primary">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                @endif
                <span class="text-slate-400">/bulan</span>
            </div>
        </div>

        <!-- Limits -->
        <div class="space-y-2 mb-4 text-sm">
            @if(isset($plan->features['max_contacts']))
            <div class="flex justify-between">
                <span class="text-slate-400">Max Contacts</span>
                <span class="font-medium">{{ number_format($plan->features['max_contacts']) }}</span>
            </div>
            @endif
            @if(isset($plan->features['max_ai_messages']))
            <div class="flex justify-between">
                <span class="text-slate-400">AI Messages/mo</span>
                <span class="font-medium">{{ number_format($plan->features['max_ai_messages']) }}</span>
            </div>
            @endif
        </div>

        <!-- Features -->
        <div class="space-y-1 mb-4 text-sm">
            @foreach($plan->features ?? [] as $feature => $enabled)
                @if(is_bool($enabled) || $enabled === true || $enabled === '1')
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-green-400">check_circle</span>
                    <span class="text-slate-300">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                </div>
                @endif
            @endforeach
        </div>

        <a href="{{ route('admin.plans.edit', $plan) }}" class="block w-full text-center py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition">
            Edit Plan
        </a>
    </div>
    @endforeach
</div>

@endsection
