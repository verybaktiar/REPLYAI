@extends('layouts.app')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="text-center px-6">
        <!-- Animated 404 -->
        <div class="relative mb-8">
            <h1 class="text-[180px] font-black text-transparent bg-clip-text bg-gradient-to-r from-primary via-purple-500 to-pink-500 leading-none opacity-20">
                404
            </h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-8xl">🔍</span>
            </div>
        </div>

        <!-- Message -->
        <h2 class="text-3xl font-bold text-white mb-4">Halaman Tidak Ditemukan</h2>
        <p class="text-lg text-slate-400 mb-8 max-w-md mx-auto">
            Oops! Sepertinya halaman yang kamu cari tidak ada atau sudah dipindahkan.
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-all hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Kembali ke Beranda
            </a>
            <a href="/dashboard" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                Dashboard
            </a>
        </div>

        <!-- Footer -->
        <p class="mt-12 text-sm text-slate-500">
            Butuh bantuan? <a href="/support" class="text-primary hover:underline">Hubungi Support</a>
        </p>
    </div>
</div>
@endsection
