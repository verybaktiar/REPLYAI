@extends('layouts.dark')

@section('title', '500 - Server Error')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="text-center px-6">
        <!-- Animated 500 -->
        <div class="relative mb-8">
            <h1 class="text-[180px] font-black text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-orange-500 to-yellow-500 leading-none opacity-20">
                500
            </h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-8xl">⚠️</span>
            </div>
        </div>

        <!-- Message -->
        <h2 class="text-3xl font-bold text-white mb-4">Terjadi Kesalahan Server</h2>
        <p class="text-lg text-slate-400 mb-8 max-w-md mx-auto">
            Maaf, terjadi kesalahan di server kami. Tim teknis sudah diberitahu dan sedang memperbaiki masalah ini.
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="window.location.reload()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Coba Lagi
            </button>
            <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Kembali ke Beranda
            </a>
        </div>

        <!-- Status -->
        <div class="mt-12 p-4 bg-slate-800/50 rounded-xl inline-block">
            <p class="text-sm text-slate-400">
                Error ID: <code class="text-primary">{{ now()->timestamp }}</code>
            </p>
        </div>

        <!-- Footer -->
        <p class="mt-6 text-sm text-slate-500">
            Butuh bantuan segera? <a href="/support" class="text-primary hover:underline">Hubungi Support</a>
        </p>
    </div>
</div>
@endsection
