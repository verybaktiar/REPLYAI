@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Kembali ke Daftar Users
    </a>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        @csrf
        @method('PUT')

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
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary">
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium mb-2">Email <span class="text-red-400">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary">
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium mb-2">Password Baru</label>
            <input type="password" name="password"
                class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                placeholder="Kosongkan jika tidak diubah">
        </div>

        <div class="mb-5">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_vip" value="1" {{ old('is_vip', $user->is_vip) ? 'checked' : '' }}
                    class="w-5 h-5 rounded bg-surface-light border-slate-700 text-yellow-500 focus:ring-yellow-500">
                <span class="font-medium">⭐ Status VIP</span>
            </label>
        </div>

        <hr class="border-slate-700 my-6">

        @if($user->subscription)
        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 mb-6">
            <div class="font-medium text-green-400">{{ $user->subscription->plan->name ?? 'Unknown Plan' }}</div>
            <div class="text-sm text-slate-400">Berakhir: {{ $user->subscription->expires_at?->format('d M Y') ?? '-' }}</div>
        </div>
        @else
        <div class="bg-slate-800 rounded-xl p-4 mb-6">
            <div class="text-slate-400">Tidak ada subscription aktif</div>
            <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-primary hover:underline">Assign subscription →</a>
        </div>
        @endif

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white font-semibold py-3 rounded-xl transition">
                <span class="material-symbols-outlined align-middle mr-2">save</span>
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-3 text-slate-400 hover:text-white">Batal</a>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="mt-6 bg-red-500/5 border border-red-500/20 rounded-2xl p-6">
        <h3 class="font-bold text-red-400 mb-4">Zona Berbahaya</h3>
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
            onsubmit="return confirm('YAKIN hapus user {{ $user->name }}?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-6 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-xl border border-red-500/30 transition">
                <span class="material-symbols-outlined align-middle mr-1 text-lg">delete</span>
                Hapus User
            </button>
        </form>
    </div>
</div>

@endsection
