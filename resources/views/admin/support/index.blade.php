@extends('admin.layouts.app')

@section('title', 'Support Tickets')
@section('page_title', 'Support Tickets')

@section('content')

<!-- Filter Tabs -->
<div class="flex items-center gap-4 mb-6 flex-wrap">
    <a href="{{ route('admin.support.index', ['status' => 'open']) }}" 
       class="px-6 py-3 rounded-xl border font-semibold transition {{ $status === 'open' ? 'bg-primary border-primary text-white' : 'bg-surface-dark border-slate-700 text-slate-300 hover:border-primary' }}">
        Open <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-yellow-500 text-black">{{ $stats['open'] }}</span>
    </a>
    <a href="{{ route('admin.support.index', ['status' => 'in_progress']) }}" 
       class="px-6 py-3 rounded-xl border font-semibold transition {{ $status === 'in_progress' ? 'bg-primary border-primary text-white' : 'bg-surface-dark border-slate-700 text-slate-300 hover:border-primary' }}">
        In Progress <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-blue-500 text-black">{{ $stats['in_progress'] }}</span>
    </a>
    <a href="{{ route('admin.support.index', ['status' => 'closed']) }}" 
       class="px-6 py-3 rounded-xl border font-semibold transition {{ $status === 'closed' ? 'bg-primary border-primary text-white' : 'bg-surface-dark border-slate-700 text-slate-300 hover:border-primary' }}">
        Closed <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-green-500 text-black">{{ $stats['closed'] }}</span>
    </a>
    <a href="{{ route('admin.support.index', ['status' => 'all']) }}" 
       class="px-6 py-3 rounded-xl border font-semibold transition {{ $status === 'all' ? 'bg-primary border-primary text-white' : 'bg-surface-dark border-slate-700 text-slate-300 hover:border-primary' }}">
        All
    </a>
</div>

<!-- Tickets Table -->
<div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-surface-light border-b border-slate-800">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Ticket #</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">User</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Category</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Subject</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Priority</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Created</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-surface-light/50 transition">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm">{{ $ticket->ticket_number }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium">{{ $ticket->user->name }}</div>
                        <div class="text-sm text-slate-400">{{ $ticket->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ ucfirst($ticket->category) }}</td>
                    <td class="px-6 py-4">
                        <div class="font-medium max-w-xs truncate">{{ $ticket->subject }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($ticket->priority === 'high')
                        <span class="px-2 py-1 rounded-full text-xs bg-red-500/20 text-red-400">High</span>
                        @elseif($ticket->priority === 'medium')
                        <span class="px-2 py-1 rounded-full text-xs bg-yellow-500/20 text-yellow-400">Medium</span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs bg-green-500/20 text-green-400">Low</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($ticket->status === 'open')
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">Open</span>
                        @elseif($ticket->status === 'in_progress')
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400">In Progress</span>
                        @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">Closed</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">
                        {{ $ticket->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.support.show', $ticket) }}" class="px-4 py-2 bg-primary hover:bg-primary/90 rounded-lg text-sm font-medium transition">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-5xl mb-3 opacity-50">support_agent</span>
                        <p>Tidak ada ticket {{ $status !== 'all' ? $status : '' }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tickets->hasPages())
    <div class="px-6 py-4 border-t border-slate-800">
        {{ $tickets->links() }}
    </div>
    @endif
</div>

@endsection
