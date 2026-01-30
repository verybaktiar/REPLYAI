@extends('admin.layouts.app')

@section('title', 'Activity Logs')
@section('page_title', 'Activity Logs')

@section('content')

<!-- Tabs -->
<div class="flex gap-4 mb-6">
    <a href="{{ route('admin.activity-logs.index', ['type' => 'admin']) }}" 
       class="px-6 py-2 rounded-xl font-bold transition-all {{ $type === 'admin' ? 'bg-primary text-white' : 'bg-surface-dark text-slate-500 border border-slate-800' }}">
       Admin Activity
    </a>
    <a href="{{ route('admin.activity-logs.index', ['type' => 'user']) }}" 
       class="px-6 py-2 rounded-xl font-bold transition-all {{ $type === 'user' ? 'bg-primary text-white' : 'bg-surface-dark text-slate-500 border border-slate-800' }}">
       User Activity
    </a>
</div>

<!-- Filters -->
<div class="bg-surface-dark rounded-xl p-4 mb-6 border border-slate-800">
    <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
        <input type="hidden" name="type" value="{{ $type }}">
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
                    <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">{{ $type === 'admin' ? 'Admin' : 'User' }}</th>
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
                        @if($type === 'user')
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-primary/20 flex items-center justify-center text-[10px] font-bold text-primary">
                                    {{ strtoupper(substr($log->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="font-medium truncate max-w-[120px]">{{ $log->user->name ?? 'Unknown User' }}</span>
                            </div>
                        @else
                            <span class="font-medium">{{ $log->admin->name ?? 'Unknown' }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $actionColors = [
                                // Admin Actions
                                'create_user' => 'bg-green-500/20 text-green-400',
                                'update_user' => 'bg-blue-500/20 text-blue-400',
                                'delete_user' => 'bg-red-500/20 text-red-400',
                                'impersonate_user' => 'bg-pink-500/20 text-pink-400',
                                'set_vip' => 'bg-yellow-500/20 text-yellow-400',
                                'remove_vip' => 'bg-orange-500/20 text-orange-400',
                                
                                // User Actions
                                'auth.login' => 'bg-emerald-500/20 text-emerald-400',
                                'auth.logout' => 'bg-slate-500/20 text-slate-400',
                                'channel.wa_connected' => 'bg-green-500/20 text-green-400',
                                'channel.wa_disconnected' => 'bg-red-500/20 text-red-400',
                                'whatsappdevice.created' => 'bg-green-500/20 text-green-400',
                                'whatsappdevice.deleted' => 'bg-red-500/20 text-red-400',
                                'autoreplyrule.created' => 'bg-indigo-500/20 text-indigo-400',
                                'autoreplyrule.updated' => 'bg-blue-500/20 text-blue-400',
                                'autoreplyrule.deleted' => 'bg-amber-500/20 text-amber-400',
                                'kbarticle.created' => 'bg-purple-500/20 text-purple-400',
                                'kbarticle.updated' => 'bg-blue-500/20 text-blue-400',
                                'kbarticle.deleted' => 'bg-pink-500/20 text-pink-400',
                            ];
                            $color = $actionColors[$log->action] ?? 'bg-slate-700 text-slate-300';
                            $label = str_replace(['whatsappdevice.', 'autoreplyrule.', 'kbarticle.'], '', $log->action);
                            $label = str_replace('.', ':', $label);
                        @endphp
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider {{ $color }}">
                            {{ $label }}
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
