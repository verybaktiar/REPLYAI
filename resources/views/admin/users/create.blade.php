@extends('admin.layouts.app')

@section('title', 'Tambah User')
@section('page_title', 'Tambah User Baru')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Kembali ke Daftar Users
    </a>

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        @csrf

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-6">
            <ul class="text-sm text-red-400 list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="mb-5">
            <label class="block text-sm font-medium mb-2">Nama Lengkap <span class="text-red-400">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary focus:border-primary"
                placeholder="Nama user">
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium mb-2">Email <span class="text-red-400">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary focus:border-primary"
                placeholder="email@example.com">
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium mb-2">Password <span class="text-red-400">*</span></label>
            <input type="password" name="password" required
                class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary focus:border-primary"
                placeholder="Minimal 8 karakter">
        </div>

        <div class="mb-5">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_vip" value="1" {{ old('is_vip') ? 'checked' : '' }}
                    class="w-5 h-5 rounded bg-surface-light border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <span class="font-medium">⭐ Jadikan VIP</span>
                <span class="text-sm text-slate-400">(Akses semua fitur)</span>
            </label>
        </div>

        <hr class="border-slate-700 my-6">

        <div class="mb-5">
            <h3 class="font-semibold mb-3">Assign Subscription (Opsional)</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Paket</label>
                    <select name="plan_id" class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                        <option value="">-- Tidak ada --</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Durasi</label>
                    <select name="duration_months" class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                        <option value="">-- Pilih --</option>
                        <option value="1">1 Bulan</option>
                        <option value="3">3 Bulan</option>
                        <option value="6">6 Bulan</option>
                        <option value="12">12 Bulan</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white font-semibold py-3 rounded-xl transition">
                <span class="material-symbols-outlined align-middle mr-2">person_add</span>
                Buat User
            </button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-3 text-slate-400 hover:text-white">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection
