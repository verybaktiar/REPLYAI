@extends('admin.layouts.app')

@section('title', 'Activity Logs')
@section('page_title', 'Activity Logs')

@section('content')

<!-- Filters -->
<div class="bg-surface-dark rounded-xl p-4 mb-6 border border-slate-800">
    <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari aktivitas..." 
                   class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        
        <select name="action" class="px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-white">
            <option value="">Semua Action</option>
            @foreach($actions as $action)
            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
            @endforeach
        </select>

        <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/90 rounded-lg font-medium transition">
            Filter
        </button>
    </form>
</div>

<!-- Logs Table -->
<div class="bg-surface-dark rounded-xl overflow-hidden border border-slate-800">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-surface-light">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Waktu</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Admin</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Action</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Deskripsi</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($logs as $log)
                <tr class="hover:bg-surface-light/50 transition">
                    <td class="px-6 py-4 text-sm text-slate-400 whitespace-nowrap">
                        {{ $log->created_at ? $log->created_at->format('d M Y H:i') : '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-medium">{{ $log->admin->name ?? 'Unknown' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $actionColors = [
                                'create_user' => 'bg-green-500/20 text-green-400',
                                'update_user' => 'bg-blue-500/20 text-blue-400',
                                'delete_user' => 'bg-red-500/20 text-red-400',
                                'set_vip' => 'bg-yellow-500/20 text-yellow-400',
                                'remove_vip' => 'bg-orange-500/20 text-orange-400',
                                'assign_subscription' => 'bg-purple-500/20 text-purple-400',
                                'impersonate_user' => 'bg-pink-500/20 text-pink-400',
                                'approve_payment' => 'bg-green-500/20 text-green-400',
                                'reject_payment' => 'bg-red-500/20 text-red-400',
                                'update_settings' => 'bg-cyan-500/20 text-cyan-400',
                            ];
                            $color = $actionColors[$log->action] ?? 'bg-slate-700 text-slate-300';
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $color }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm max-w-md truncate">
                        {{ $log->description }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500 font-mono">
                        {{ $log->ip_address }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                        <span class="material-symbols-outlined text-4xl mb-2 block">history</span>
                        Belum ada aktivitas tercatat
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $logs->appends(request()->query())->links() }}
</div>

@endsection
