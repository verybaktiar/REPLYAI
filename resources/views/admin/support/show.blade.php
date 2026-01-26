@extends('admin.layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)
@section('page_title', 'Support Ticket')

@section('content')

<a href="{{ route('admin.support.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
    <span class="material-symbols-outlined text-lg">arrow_back</span>
    Kembali ke Support Tickets
</a>

<!-- Ticket Info -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 mb-6">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold">Ticket #{{ $ticket->ticket_number }}</h2>
            <div class="text-xl font-medium mt-2">{{ $ticket->subject }}</div>
        </div>
        <div>
            @if($ticket->status === 'open')
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-500/20 text-yellow-400">Open</span>
            @elseif($ticket->status === 'in_progress')
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-500/20 text-blue-400">In Progress</span>
            @else
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-500/20 text-green-400">Closed</span>
            @endif
        </div>
    </div>
    <div class="grid md:grid-cols-4 gap-4">
        <div>
            <div class="text-sm text-slate-400 mb-1">User</div>
            <div class="font-medium">{{ $ticket->user->name }}</div>
            <div class="text-sm text-slate-400">{{ $ticket->user->email }}</div>
        </div>
        <div>
            <div class="text-sm text-slate-400 mb-1">Created</div>
            <div>{{ $ticket->created_at->format('d M Y, H:i') }}</div>
        </div>
        <div>
            <div class="text-sm text-slate-400 mb-1">Category</div>
            <div>{{ ucfirst($ticket->category) }}</div>
        </div>
        <div>
            <div class="text-sm text-slate-400 mb-1">Priority</div>
            <div>
                @if($ticket->priority === 'high')
                <span class="px-2 py-1 rounded-full text-xs bg-red-500/20 text-red-400 font-semibold">High</span>
                @elseif($ticket->priority === 'medium')
                <span class="px-2 py-1 rounded-full text-xs bg-yellow-500/20 text-yellow-400 font-semibold">Medium</span>
                @else
                <span class="px-2 py-1 rounded-full text-xs bg-green-500/20 text-green-400 font-semibold">Low</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Messages Thread -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 mb-6">
    <h3 class="text-lg font-bold mb-4">Messages</h3>
    
    <div class="space-y-4">
        <!-- Original Message -->
        <div class="flex gap-4 p-4 bg-surface-light rounded-xl">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold">
                    {{ substr($ticket->user->name, 0, 1) }}
                </div>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-medium">{{ $ticket->user->name }}</div>
                    <div class="text-xs text-slate-500">{{ $ticket->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div class="text-slate-300 whitespace-pre-wrap">{{ $ticket->message }}</div>
            </div>
        </div>

        <!-- Replies -->
        @foreach($ticket->replies as $reply)
        <div class="flex gap-4 p-4 {{ $reply->is_staff ? 'bg-primary/10 ml-8' : 'bg-surface-light' }} rounded-xl">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full {{ $reply->is_staff ? 'bg-green-600' : 'bg-primary' }} flex items-center justify-center font-bold">
                    {{ $reply->is_staff ? 'A' : substr($reply->user->name ?? '', 0, 1) }}
                </div>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-medium">
                        {{ $reply->is_staff ? 'Admin' : ($reply->user->name ?? 'Unknown') }}
                        @if($reply->is_staff)
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-green-500/20 text-green-400">Staff</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500">{{ $reply->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div class="text-slate-300 whitespace-pre-wrap">{{ $reply->message }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Reply Form (jika belum closed) -->
@if($ticket->status !== 'closed')
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 mb-6">
    <h3 class="text-lg font-bold mb-4">Reply to Ticket</h3>
    
    <form action="{{ route('admin.support.reply', $ticket) }}" method="POST">
        @csrf
        <div class="mb-4">
            <textarea name="message" rows="5" required
                      class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary"
                      placeholder="Ketik balasan Anda..."></textarea>
        </div>
        <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition">
            Send Reply
        </button>
    </form>
</div>
@endif

<!-- Actions -->
<div class="flex gap-4">
    @if($ticket->status === 'closed')
    <form action="{{ route('admin.support.reopen', $ticket) }}" method="POST" class="flex-1">
        @csrf
        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 rounded-xl font-semibold transition">
            Reopen Ticket
        </button>
    </form>
    @else
    <form action="{{ route('admin.support.close', $ticket) }}" method="POST" class="flex-1" onsubmit="return confirm('Close ticket ini?')">
        @csrf
        <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 rounded-xl font-semibold transition">
            Close Ticket
        </button>
    </form>
    @endif
</div>

@endsection
