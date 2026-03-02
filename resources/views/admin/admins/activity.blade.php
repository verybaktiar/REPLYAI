@extends('admin.layouts.app')

@section('title', 'Admin Activity')
@section('page_title', 'Activity Log: ' . $admin->name)

@section('content')

<a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
    <span class="material-symbols-outlined text-lg">arrow_back</span>
    Back to Admin List
</a>

<!-- Admin Header -->
<div class="flex items-center gap-4 mb-6 p-4 bg-surface-dark rounded-xl border border-slate-800">
    <div class="w-12 h-12 rounded-full {{ $admin->isSuperAdmin() ? 'bg-red-500/20 text-red-500' : ($admin->role === 'finance' ? 'bg-green-500/20 text-green-500' : 'bg-blue-500/20 text-blue-500') }} flex items-center justify-center font-bold text-lg">
        {{ strtoupper(substr($admin->name, 0, 1)) }}
    </div>
    <div>
        <h2 class="font-bold">{{ $admin->name }}</h2>
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <span>{{ $admin->email }}</span>
            <span class="px-2 py-0.5 bg-slate-700 rounded text-xs">{{ $admin->role_label }}</span>
        </div>
    </div>
</div>

<!-- Activity Log -->
<div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
        <h3 class="font-bold">Activity History</h3>
        <span class="text-sm text-slate-400">{{ $activities->total() }} records</span>
    </div>
    
    <table class="w-full">
        <thead class="bg-surface-light">
            <tr>
                <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">Time</th>
                <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">Action</th>
                <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">Description</th>
                <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">IP Address</th>
                <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">Risk</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($activities as $activity)
            <tr class="hover:bg-surface-light/30 transition">
                <td class="px-6 py-3 text-sm text-slate-400">
                    <div>{{ $activity->created_at->format('d M Y') }}</div>
                    <div class="text-xs">{{ $activity->created_at->format('H:i:s') }}</div>
                </td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 rounded text-xs font-medium
                        {{ $activity->action === 'login' ? 'bg-green-500/20 text-green-400' : 
                           ($activity->action === 'logout' ? 'bg-slate-700 text-slate-400' :
                           ($activity->action === 'unauthorized_access_attempt' ? 'bg-red-500/20 text-red-400' :
                           'bg-primary/20 text-primary')) }}">
                        {{ $activity->action }}
                    </span>
                </td>
                <td class="px-6 py-3 text-sm">
                    {{ $activity->description }}
                    @if($activity->target_type)
                    <div class="text-xs text-slate-500 mt-1">
                        Target: {{ class_basename($activity->target_type) }} #{{ $activity->target_id }}
                    </div>
                    @endif
                </td>
                <td class="px-6 py-3 text-sm text-slate-400 font-mono">
                    {{ $activity->ip_address ?? '-' }}
                </td>
                <td class="px-6 py-3">
                    @if($activity->risk_score > 0)
                    <span class="px-2 py-1 rounded text-xs font-medium
                        {{ $activity->risk_score >= 8 ? 'bg-red-500/20 text-red-400' : 
                           ($activity->risk_score >= 5 ? 'bg-orange-500/20 text-orange-400' :
                           'bg-yellow-500/20 text-yellow-400') }}">
                        {{ $activity->risk_score }}
                    </span>
                    @else
                    <span class="text-slate-500">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl mb-2 block">history</span>
                    No activity records found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $activities->links() }}
</div>

@endsection
