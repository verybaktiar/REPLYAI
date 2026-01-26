@extends('admin.layouts.app')

@section('title', 'System Backups')
@section('page_title', 'Backups')

@section('content')

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
    <div>
        <h2 class="text-2xl font-black mb-1 text-white">Cloud & Local Backups 🗄️</h2>
        <p class="text-slate-400">Kelola cadangan database dan file sistem untuk keamanan data.</p>
    </div>
    <div class="flex items-center gap-3">
        <form action="{{ route('admin.backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-primary hover:bg-primary/80 text-white rounded-2xl font-bold transition shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined">database</span>
                Generate Backup Now
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="bg-green-500/10 border border-green-500/30 text-green-500 p-4 rounded-2xl mb-6 flex items-center gap-3">
    <span class="material-symbols-outlined">check_circle</span>
    <span class="text-sm font-medium">{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="bg-red-500/10 border border-red-500/30 text-red-500 p-4 rounded-2xl mb-6 flex items-center gap-3">
    <span class="material-symbols-outlined">error</span>
    <span class="text-sm font-medium">{{ session('error') }}</span>
</div>
@endif

<div class="bg-surface-dark border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
    <div class="p-6 border-b border-slate-800 bg-slate-800/30 flex items-center justify-between">
        <h3 class="font-bold text-slate-300 flex items-center gap-2 text-sm uppercase tracking-widest">
            <span class="material-symbols-outlined text-slate-500">history</span>
            Backup History
        </h3>
        <span class="text-xs text-slate-500">{{ count($backups) }} files stored</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-500 text-[10px] font-black uppercase tracking-widest">
                    <th class="px-6 py-4">Filename</th>
                    <th class="px-6 py-4">Size</th>
                    <th class="px-6 py-4">Created Date</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($backups as $backup)
                <tr class="hover:bg-white/5 transition group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 group-hover:bg-primary/20 group-hover:text-primary transition">
                                <span class="material-symbols-outlined">folder_zip</span>
                            </div>
                            <span class="font-mono text-xs text-white">{{ $backup['name'] }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-slate-400">{{ $backup['size'] }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-slate-400">{{ $backup['date'] }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.backups.download', $backup['name']) }}" 
                               class="p-2 bg-slate-800 hover:bg-slate-700 rounded-lg text-slate-400 hover:text-white transition title-tip" title="Download">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                            </a>
                            <form action="{{ route('admin.backups.destroy', $backup['name']) }}" method="POST" onsubmit="return confirm('Hapus backup ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-slate-800 hover:bg-red-500/20 rounded-lg text-slate-400 hover:text-red-500 transition title-tip" title="Delete">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <span class="material-symbols-outlined text-5xl text-slate-800 mb-3">database_off</span>
                            <p class="text-slate-500 text-sm italic font-medium">Belum ada file backup yang tersimpan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-2xl p-6 flex gap-4">
        <span class="material-symbols-outlined text-blue-500 text-3xl">info</span>
        <div>
            <h4 class="font-bold text-white mb-1">Local Storage Backup</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
                Backup saat ini disimpan secara lokal di path <code class="bg-slate-900 px-1.5 py-0.5 rounded text-blue-400">storage/app/backups</code>. 
                Sangat disarankan untuk mendownload file backup secara berkala dan menyimpannya di tempat yang aman.
            </p>
        </div>
    </div>
    <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-2xl p-6 flex gap-4">
        <span class="material-symbols-outlined text-yellow-500 text-3xl">warning</span>
        <div>
            <h4 class="font-bold text-white mb-1">Database Only</h4>
            <p class="text-xs text-slate-400 leading-relaxed">
                Secara default, backup ini mencakup struktur dan data database MySQL. File media/upload dalam storage tidak disertakan untuk menjaga performa server.
            </p>
        </div>
    </div>
</div>

@endsection
