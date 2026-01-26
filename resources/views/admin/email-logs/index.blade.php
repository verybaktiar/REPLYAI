@extends('admin.layouts.app')

@section('title', 'Email Logs')
@section('page_title', 'Email Logs')

@section('content')
@php
    $logs = \App\Models\EmailLog::with('user')
        ->orderByDesc('created_at')
        ->paginate(50);
    
    $stats = [
        'total' => \App\Models\EmailLog::count(),
        'sent' => \App\Models\EmailLog::where('status', 'sent')->count(),
        'failed' => \App\Models\EmailLog::where('status', 'failed')->count(),
    ];
@endphp

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="text-3xl font-black">{{ number_format($stats['total']) }}</div>
        <div class="text-sm text-slate-400">Total Emails</div>
    </div>
    <div class="bg-green-500/10 border border-green-500/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-green-400">{{ number_format($stats['sent']) }}</div>
        <div class="text-sm text-slate-400">Sent Successfully</div>
    </div>
    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-red-400">{{ number_format($stats['failed']) }}</div>
        <div class="text-sm text-slate-400">Failed</div>
    </div>
</div>

<!-- Logs Table -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
    <h3 class="font-bold text-lg mb-6">Email History</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-slate-500 border-b border-slate-800">
                    <th class="pb-3">To</th>
                    <th class="pb-3">Subject</th>
                    <th class="pb-3">Template</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($logs as $log)
                <tr class="hover:bg-surface-light/50">
                    <td class="py-3">
                        <div class="text-sm font-medium">{{ $log->user->name ?? 'Guest' }}</div>
                        <div class="text-xs text-slate-500">{{ $log->to_email }}</div>
                    </td>
                    <td class="py-3 text-sm max-w-xs truncate">{{ $log->subject }}</td>
                    <td class="py-3 text-xs text-slate-400">{{ $log->template ?? '-' }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            {{ $log->status === 'sent' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                            {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td class="py-3 text-sm text-slate-400">{{ $log->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-slate-500">No email logs yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $logs->links() }}</div>
</div>
@endsection
