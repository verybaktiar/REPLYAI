@extends('layouts.dark')

@section('title', '503 - Sedang Maintenance')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="text-center px-6">
        <!-- Animated Icon -->
        <div class="relative mb-8">
            <div class="w-32 h-32 mx-auto bg-gradient-to-r from-primary to-purple-500 rounded-full flex items-center justify-center animate-pulse">
                <span class="text-6xl">🔧</span>
            </div>
        </div>

        <!-- Message -->
        <h2 class="text-3xl font-bold text-white mb-4">Sedang Maintenance</h2>
        <p class="text-lg text-slate-400 mb-8 max-w-md mx-auto">
            Kami sedang melakukan peningkatan sistem untuk pengalaman yang lebih baik. Silakan coba lagi dalam beberapa menit.
        </p>

        <!-- Countdown Timer Placeholder -->
        <div class="inline-flex items-center gap-4 px-6 py-4 bg-slate-800/50 rounded-xl mb-8">
            <div class="text-center">
                <span class="block text-2xl font-bold text-primary">~15</span>
                <span class="text-xs text-slate-400">Menit</span>
            </div>
            <span class="text-slate-600">|</span>
            <span class="text-sm text-slate-400">Estimasi selesai</span>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="window.location.reload()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh Halaman
            </button>
        </div>

        <!-- Footer -->
        <p class="mt-12 text-sm text-slate-500">
            Follow update di <a href="https://twitter.com/replyai" class="text-primary hover:underline">@replyai</a>
        </p>
    </div>
</div>
@endsection
