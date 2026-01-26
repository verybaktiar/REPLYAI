@extends('layouts.dark')

@section('title', 'Customer Satisfaction')

@section('content')
@php
    $user = Auth::user();
    $csatService = new \App\Services\CsatService();
    $analytics = $csatService->getAnalytics($user->id, 30);
    
    // Recent ratings
    $recentRatings = \App\Models\CsatRating::where('user_id', $user->id)
        ->whereNotNull('rating')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Customer Satisfaction</h1>
            <p class="text-slate-400">Pantau kepuasan pelanggan dari rating CSAT</p>
        </div>
        <div class="flex items-center gap-4">
            <!-- Status Badge -->
            @if($user->csat_enabled)
            <span class="flex items-center gap-1 px-3 py-1.5 bg-green-500/20 text-green-400 rounded-full text-sm font-medium">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                Aktif
            </span>
            @else
            <span class="flex items-center gap-1 px-3 py-1.5 bg-slate-700 text-slate-400 rounded-full text-sm font-medium">
                <span class="w-2 h-2 bg-slate-500 rounded-full"></span>
                Nonaktif
            </span>
            @endif
            
            <!-- Settings Button -->
            <a href="{{ route('csat.settings') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-light hover:bg-slate-700 border border-slate-700 rounded-xl text-sm font-medium transition">
                <span class="material-symbols-outlined text-lg">settings</span>
                Pengaturan
            </a>
        </div>
    </div>


    <!-- Score Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Overall Score -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 col-span-1 md:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-300">Skor Keseluruhan</h3>
                <span class="text-3xl">⭐</span>
            </div>
            <div class="flex items-end gap-4">
                <span class="text-6xl font-bold text-yellow-400">{{ $analytics['average_rating'] }}</span>
                <span class="text-2xl text-slate-500 mb-2">/ 5.0</span>
            </div>
            <div class="mt-4 flex items-center gap-4 text-sm">
                <span class="text-slate-400">{{ $analytics['total_responses'] }} respons</span>
                <span class="text-slate-600">|</span>
                <span class="text-slate-400">{{ $analytics['response_rate'] }}% response rate</span>
            </div>
        </div>

        <!-- Instagram Score -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xl">📸</span>
                <h3 class="font-semibold text-slate-300">Instagram</h3>
            </div>
            <div class="flex items-end gap-2">
                <span class="text-4xl font-bold text-pink-400">{{ $analytics['average_instagram'] }}</span>
                <span class="text-lg text-slate-500 mb-1">/ 5.0</span>
            </div>
        </div>

        <!-- WhatsApp Score -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xl">💬</span>
                <h3 class="font-semibold text-slate-300">WhatsApp</h3>
            </div>
            <div class="flex items-end gap-2">
                <span class="text-4xl font-bold text-green-400">{{ $analytics['average_whatsapp'] }}</span>
                <span class="text-lg text-slate-500 mb-1">/ 5.0</span>
            </div>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribution Chart -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-lg mb-6">Distribusi Rating</h3>
            <div class="space-y-4">
                @foreach($analytics['distribution'] as $star => $percent)
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1 w-20">
                        <span class="text-yellow-400">{{ str_repeat('⭐', $star) }}</span>
                    </div>
                    <div class="flex-1 h-4 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full {{ $star >= 4 ? 'bg-green-500' : ($star >= 3 ? 'bg-yellow-500' : 'bg-red-500') }} transition-all" 
                             style="width: {{ $percent }}%"></div>
                    </div>
                    <span class="text-sm text-slate-400 w-12 text-right">{{ $percent }}%</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Ratings -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold text-lg mb-6">Rating Terbaru</h3>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($recentRatings as $rating)
                <div class="flex items-center justify-between p-3 bg-surface-light rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="{{ $rating->platform === 'instagram' ? 'text-pink-400' : 'text-green-400' }}">
                            {{ $rating->platform === 'instagram' ? '📸' : '💬' }}
                        </span>
                        <div>
                            <div class="font-medium text-sm">{{ $rating->contact_name ?: 'Customer' }}</div>
                            <div class="text-xs text-slate-500">{{ $rating->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-yellow-400">{{ str_repeat('⭐', $rating->rating) }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-500">
                    <span class="text-3xl block mb-2">📊</span>
                    <p>Belum ada rating CSAT</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- CSAT Settings Info -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h3 class="font-semibold text-lg mb-4">💡 Cara Kerja CSAT</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-start gap-3">
                <span class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold">1</span>
                <div>
                    <div class="font-medium">Kirim Permintaan</div>
                    <p class="text-sm text-slate-400">Setelah percakapan selesai, bot otomatis mengirim permintaan rating</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold">2</span>
                <div>
                    <div class="font-medium">Pelanggan Memberi Rating</div>
                    <p class="text-sm text-slate-400">Pelanggan membalas dengan angka 1-5 atau emoji bintang</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold">3</span>
                <div>
                    <div class="font-medium">Lihat Analitik</div>
                    <p class="text-sm text-slate-400">Pantau skor dan distribusi rating di halaman ini</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
