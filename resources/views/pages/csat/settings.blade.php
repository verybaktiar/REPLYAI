@extends('layouts.dark')

@section('title', 'Pengaturan CSAT')

@section('content')
@php
    $user = Auth::user();
@endphp

<style>
    /* Fix select dropdown styling for dark mode */
    select option {
        background-color: #1e2634;
        color: white;
        padding: 10px;
    }
</style>

<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Pengaturan CSAT</h1>
            <p class="text-slate-400">Atur survey kepuasan pelanggan</p>
        </div>
        <a href="{{ route('csat.index') }}" class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-medium transition">
            <span class="material-symbols-outlined text-lg">bar_chart</span>
            Lihat Analitik
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 flex items-center gap-2">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('csat.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Main Toggle -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center text-2xl">
                        ⭐
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Aktifkan CSAT</h3>
                        <p class="text-sm text-slate-400">Kirim survey kepuasan setelah percakapan selesai</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="csat_enabled" value="1" class="sr-only peer" {{ $user->csat_enabled ? 'checked' : '' }}>
                    <div class="w-14 h-7 bg-slate-700 peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                </label>
            </div>
        </div>

        <!-- Platform Settings -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 space-y-4">
            <h3 class="font-semibold mb-2">Platform</h3>
            
            <!-- Instagram -->
            <div class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl border border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-lg">
                        <span class="material-symbols-outlined">photo_camera</span>
                    </div>
                    <div>
                        <div class="font-medium">Instagram</div>
                        <div class="text-xs text-slate-400">Kirim CSAT ke DM Instagram</div>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="csat_instagram_enabled" value="1" class="sr-only peer" {{ $user->csat_instagram_enabled ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-700 peer-focus:ring-2 peer-focus:ring-pink-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                </label>
            </div>

            <!-- WhatsApp -->
            <div class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl border border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center text-white text-lg">
                        <span class="material-symbols-outlined">chat</span>
                    </div>
                    <div>
                        <div class="font-medium">WhatsApp</div>
                        <div class="text-xs text-slate-400">Kirim CSAT ke chat WhatsApp</div>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="csat_whatsapp_enabled" value="1" class="sr-only peer" {{ $user->csat_whatsapp_enabled ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-700 peer-focus:ring-2 peer-focus:ring-green-500/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                </label>
            </div>
        </div>

        <!-- Delay Setting -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold mb-4">Waktu Tunggu</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Kirim CSAT setelah</label>
                    <div class="relative">
                        <select name="csat_delay_minutes" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                            <option value="0" {{ ($user->csat_delay_minutes ?? 5) == 0 ? 'selected' : '' }}>Langsung (0 menit)</option>
                            <option value="5" {{ ($user->csat_delay_minutes ?? 5) == 5 ? 'selected' : '' }}>5 menit</option>
                            <option value="10" {{ ($user->csat_delay_minutes ?? 5) == 10 ? 'selected' : '' }}>10 menit</option>
                            <option value="15" {{ ($user->csat_delay_minutes ?? 5) == 15 ? 'selected' : '' }}>15 menit</option>
                            <option value="30" {{ ($user->csat_delay_minutes ?? 5) == 30 ? 'selected' : '' }}>30 menit</option>
                            <option value="60" {{ ($user->csat_delay_minutes ?? 5) == 60 ? 'selected' : '' }}>1 jam</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <p class="text-sm text-slate-500">Waktu tunggu setelah pesan terakhir sebelum mengirim survey CSAT</p>
                </div>
            </div>
        </div>

        <!-- Custom Message -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold mb-4">Pesan Custom (Opsional)</h3>
            <div>
                <textarea name="csat_message" 
                          rows="4" 
                          class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:border-primary focus:ring-1 focus:ring-primary resize-none"
                          placeholder="Kosongkan untuk menggunakan pesan default...">{{ $user->csat_message }}</textarea>
                <p class="text-xs text-slate-500 mt-2">
                    Pesan default: "Bagaimana pelayanan kami? Balas dengan angka 1-5"
                </p>
            </div>
        </div>

        <!-- Preview -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-semibold mb-4">Preview Pesan</h3>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined text-sm">smart_toy</span>
                    </div>
                    <div class="bg-primary text-white px-4 py-3 rounded-2xl rounded-tl-sm text-sm whitespace-pre-line max-w-md">
@if($user->csat_message)
{{ $user->csat_message }}
@else
Bagaimana pelayanan kami? 😊

Balas dengan angka 1-5:
⭐ 1 = Sangat Buruk
⭐⭐ 2 = Buruk  
⭐⭐⭐ 3 = Cukup
⭐⭐⭐⭐ 4 = Baik
⭐⭐⭐⭐⭐ 5 = Sangat Baik
@endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('csat.index') }}" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 rounded-xl font-medium transition">
                Batal
            </a>
            <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition">
                <span class="material-symbols-outlined">save</span>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
