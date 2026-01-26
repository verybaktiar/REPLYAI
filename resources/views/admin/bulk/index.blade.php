@extends('admin.layouts.app')

@section('title', 'Maintenance & Tools')
@section('page_title', 'Maintenance & Tools')

@section('content')
<div class="mb-8">
    <h2 class="text-xl font-black italic">SISTEM CONTROL PANEL ⚡</h2>
    <p class="text-sm text-slate-400">Pusat kendali untuk pembersihan data, pemeliharaan cache, dan aksi massal sistem.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Cache Management -->
    <div class="bg-surface-dark rounded-3xl p-6 border border-slate-800 shadow-xl">
        <div class="size-12 rounded-2xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 mb-6">
            <span class="material-symbols-outlined">memory</span>
        </div>
        <h3 class="font-bold text-lg mb-2">Cache Management</h3>
        <p class="text-xs text-slate-500 mb-6">Bersihkan cache aplikasi dan konfigurasi untuk menerapkan perubahan terbaru.</p>
        
        <div class="space-y-3">
            <form action="{{ route('admin.maintenance.clear-cache') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">cached</span>
                    Clear App Cache
                </button>
            </form>
            <form action="{{ route('admin.maintenance.clear-views') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">visibility</span>
                    Clear View Cache
                </button>
            </form>
        </div>
    </div>

    <!-- Data Pruning -->
    <div class="bg-surface-dark rounded-3xl p-6 border border-slate-800 shadow-xl">
        <div class="size-12 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-500 mb-6">
            <span class="material-symbols-outlined">delete_sweep</span>
        </div>
        <h3 class="font-bold text-lg mb-2">Pembersihan Data</h3>
        <p class="text-xs text-slate-500 mb-6">Hapus data lama yang sudah tidak diperlukan untuk menghemat ruang penyimpanan.</p>
        
        <form action="{{ route('admin.maintenance.prune-logs') }}" method="POST" onsubmit="return confirm('Hapus log aktivitas yang lebih lama dari 30 hari?')">
            @csrf
            <button type="submit" class="w-full py-4 bg-red-500/10 hover:bg-red-500/20 text-red-500 border border-red-500/20 rounded-xl text-sm font-black transition flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">history</span>
                PRUNE OLD LOGS (>30 Days)
            </button>
        </form>
    </div>

    <!-- Global Actions -->
    <div class="bg-surface-dark rounded-3xl p-6 border border-slate-800 shadow-xl">
        <div class="size-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary mb-6">
            <span class="material-symbols-outlined">restart_alt</span>
        </div>
        <h3 class="font-bold text-lg mb-2">Aksi Massal Global</h3>
        <p class="text-xs text-slate-500 mb-6">Lakukan reset atau perubahan status pada seluruh entitas di platform.</p>
        
        <form action="{{ route('admin.bulk.reset-usage') }}" method="POST" onsubmit="return confirm('PERINGATAN: Ini akan mereset kuota pesan seluruh pengguna menjadi nol. Lanjutkan?')">
            @csrf
            <button type="submit" class="w-full py-4 bg-primary/10 hover:bg-primary/20 text-primary border border-primary/20 rounded-xl text-sm font-black transition flex items-center justify-center gap-2 mb-4">
                <span class="material-symbols-outlined text-lg">bolt</span>
                RESET ALL USER QUOTAS
            </button>
        </form>

        <form action="{{ route('admin.bulk.extend') }}" method="POST" class="p-4 bg-background-dark/30 rounded-2xl border border-slate-800">
            @csrf
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Extend Subscriptions (Days)</label>
            <div class="flex gap-2">
                <input type="number" name="days" value="7" min="1" max="365" class="flex-1 bg-surface-dark border border-slate-700 rounded-lg px-3 py-2 text-sm focus:border-primary">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-primary text-white rounded-lg text-xs font-bold transition">
                    EXTEND
                </button>
            </div>
            <p class="text-[9px] text-slate-600 mt-2 italic">*Berlaku untuk semua user dengan langganan aktif.</p>
        </form>
    </div>
</div>

<div class="mt-8 bg-surface-dark rounded-3xl p-8 border border-slate-800 shadow-xl">
    <div class="flex items-start gap-6">
        <div class="size-16 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-500 border border-blue-500/20 shrink-0">
            <span class="material-symbols-outlined text-3xl">info</span>
        </div>
        <div>
            <h4 class="text-lg font-bold mb-2">Panduan Pemeliharaan Sistem</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
                <div class="space-y-2">
                    <p class="text-sm font-bold text-slate-300">Kapan harus Clear Cache?</p>
                    <p class="text-xs text-slate-500 italic">Gunakan ini jika Anda merubah file konfigurasi (.env) atau melakukan pembaruan kode namun tidak melihat perubahan di website.</p>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-bold text-slate-300">Apa efek Prune Logs?</p>
                    <p class="text-xs text-slate-500 italic">Menghapus baris data di tabel Activity Logs. Ini sangat disarankan dilakukan secara berkala (misal 1x sebulan) agar database tidak membengkak.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
