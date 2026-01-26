@extends('admin.layouts.app')

@section('title', 'Failed Jobs Monitoring')
@section('page_title', 'Failed Jobs Monitoring')

@section('content')

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <p class="text-slate-400">Monitor dan kelola antrean background task yang gagal</p>
    </div>
    <div class="flex items-center gap-3">
        <form action="{{ route('admin.failed-jobs.retry-all') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/80 rounded-xl font-medium transition" onclick="return confirm('Retry all failed jobs?')">
                <span class="material-symbols-outlined text-lg">restart_alt</span>
                Retry All
            </button>
        </form>
        <form action="{{ route('admin.failed-jobs.flush') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-xl font-medium transition border border-red-500/30" onclick="return confirm('Clear all failed jobs?')">
                <span class="material-symbols-outlined text-lg">delete_sweep</span>
                Flush All
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="bg-green-500/10 border border-green-500/30 text-green-500 p-4 rounded-xl mb-6 flex items-center gap-3">
    <span class="material-symbols-outlined">check_circle</span>
    {{ session('success') }}
</div>
@endif

<div class="bg-surface-dark rounded-xl overflow-hidden border border-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-surface-light">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">ID</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Queue & Connection</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Failed At</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($failedJobs as $job)
                <tr class="hover:bg-surface-light/50 transition">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm text-slate-500">#{{ $job->id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium">{{ $job->queue }}</div>
                        <div class="text-xs text-slate-500">{{ $job->connection }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</div>
                        <div class="text-xs text-slate-500">{{ $job->failed_at }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <form action="{{ route('admin.failed-jobs.retry', $job->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition" title="Retry">
                                    <span class="material-symbols-outlined">restart_alt</span>
                                </button>
                            </form>
                            <button class="p-2 text-slate-400 hover:bg-slate-700 rounded-lg transition" 
                                    onclick="alert('Error Details:\n\n' + {{ json_encode($job->exception) }})" 
                                    title="View Error">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                            <form action="{{ route('admin.failed-jobs.destroy', $job->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition" title="Delete" onclick="return confirm('Delete this job?')">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center text-green-500">
                                <span class="material-symbols-outlined text-4xl">check_circle</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white">Semua Lancar!</h3>
                                <p class="text-slate-400">Tidak ada background task yang gagal saat ini.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($failedJobs->hasPages())
    <div class="px-6 py-4 border-t border-slate-800 bg-surface-light/30">
        {{ $failedJobs->links() }}
    </div>
    @endif
</div>

@endsection
