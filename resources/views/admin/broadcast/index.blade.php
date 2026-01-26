@extends('admin.layouts.app')

@section('title', 'Broadcast')
@section('page_title', 'Broadcast & Announcements')

@section('content')
@php
    $announcements = \App\Models\Announcement::orderBy('created_at', 'desc')->limit(10)->get();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Create Broadcast -->
    <div class="lg:col-span-2">
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-bold text-lg mb-6">Kirim Broadcast</h3>
            
            <form action="{{ route('admin.broadcast.send') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Broadcast Type -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Tipe Broadcast</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative">
                            <input type="radio" name="type" value="banner" class="peer sr-only" checked>
                            <div class="p-4 bg-surface-light border border-slate-700 rounded-xl cursor-pointer text-center peer-checked:border-primary peer-checked:bg-primary/10 transition">
                                <span class="material-symbols-outlined text-2xl mb-2 block text-primary">campaign</span>
                                <span class="text-sm font-medium">Banner</span>
                                <span class="text-xs text-slate-500 block">Di dashboard</span>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="type" value="email" class="peer sr-only">
                            <div class="p-4 bg-surface-light border border-slate-700 rounded-xl cursor-pointer text-center peer-checked:border-primary peer-checked:bg-primary/10 transition">
                                <span class="material-symbols-outlined text-2xl mb-2 block text-green-400">mail</span>
                                <span class="text-sm font-medium">Email</span>
                                <span class="text-xs text-slate-500 block">Blast email</span>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="type" value="both" class="peer sr-only">
                            <div class="p-4 bg-surface-light border border-slate-700 rounded-xl cursor-pointer text-center peer-checked:border-primary peer-checked:bg-primary/10 transition">
                                <span class="material-symbols-outlined text-2xl mb-2 block text-purple-400">hub</span>
                                <span class="text-sm font-medium">Keduanya</span>
                                <span class="text-xs text-slate-500 block">Banner + Email</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Target Audience -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Target Audience</label>
                    <select name="audience" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white">
                        <option value="all">Semua User</option>
                        <option value="active">User Aktif (Punya Subscription)</option>
                        <option value="vip">VIP Users Only</option>
                        <option value="free">Free Users Only</option>
                        <option value="expiring">User yang Akan Expire (7 hari)</option>
                    </select>
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Judul</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500"
                           placeholder="Contoh: Promo Spesial Akhir Tahun!">
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Pesan</label>
                    <textarea name="message" rows="4" required
                              class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 resize-none"
                              placeholder="Tulis pesan broadcast..."></textarea>
                </div>

                <!-- Banner Style (for banner type) -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Style Banner</label>
                    <div class="grid grid-cols-4 gap-2">
                        <label class="relative">
                            <input type="radio" name="style" value="info" class="peer sr-only" checked>
                            <div class="h-8 bg-blue-500 rounded-lg cursor-pointer ring-2 ring-transparent peer-checked:ring-white peer-checked:ring-offset-2 peer-checked:ring-offset-slate-900"></div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="style" value="success" class="peer sr-only">
                            <div class="h-8 bg-green-500 rounded-lg cursor-pointer ring-2 ring-transparent peer-checked:ring-white peer-checked:ring-offset-2 peer-checked:ring-offset-slate-900"></div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="style" value="warning" class="peer sr-only">
                            <div class="h-8 bg-yellow-500 rounded-lg cursor-pointer ring-2 ring-transparent peer-checked:ring-white peer-checked:ring-offset-2 peer-checked:ring-offset-slate-900"></div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="style" value="danger" class="peer sr-only">
                            <div class="h-8 bg-red-500 rounded-lg cursor-pointer ring-2 ring-transparent peer-checked:ring-white peer-checked:ring-offset-2 peer-checked:ring-offset-slate-900"></div>
                        </label>
                    </div>
                </div>

                <!-- Duration -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Durasi Banner</label>
                    <select name="duration_days" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white">
                        <option value="1">1 Hari</option>
                        <option value="3">3 Hari</option>
                        <option value="7" selected>7 Hari</option>
                        <option value="14">14 Hari</option>
                        <option value="30">30 Hari</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3">
                    <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition">
                        <span class="material-symbols-outlined">send</span>
                        Kirim Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Announcements -->
    <div>
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-bold text-lg mb-4">Broadcast Terbaru</h3>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($announcements as $ann)
                <div class="p-3 bg-surface-light rounded-xl border-l-4 
                    {{ $ann->style === 'success' ? 'border-green-500' : ($ann->style === 'warning' ? 'border-yellow-500' : ($ann->style === 'danger' ? 'border-red-500' : 'border-blue-500')) }}">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-medium text-sm">{{ $ann->title }}</span>
                        @if($ann->is_active)
                        <span class="text-xs bg-green-500/20 text-green-400 px-2 py-0.5 rounded">Active</span>
                        @else
                        <span class="text-xs bg-slate-700 text-slate-400 px-2 py-0.5 rounded">Expired</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 line-clamp-2">{{ $ann->message }}</p>
                    <div class="flex items-center gap-2 mt-2 text-xs text-slate-500">
                        <span>{{ $ann->created_at->format('d M Y') }}</span>
                        <span>•</span>
                        <span>{{ ucfirst($ann->type) }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-500">
                    <span class="material-symbols-outlined text-3xl block mb-2">campaign</span>
                    <p class="text-sm">Belum ada broadcast</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif
@endsection
