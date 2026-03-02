@extends('admin.layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)
@section('page_title', 'Ticket #' . $ticket->ticket_number)

@php
    $adminUser = Auth::guard('admin')->user();
    $isAssigned = $ticket->assigned_admin_id === $adminUser->id;
@endphp

@section('content')

<a href="{{ route('admin.support.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
    <span class="material-symbols-outlined text-lg">arrow_back</span>
    Back to Tickets
</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        {{-- Ticket Header --}}
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono text-slate-400">{{ $ticket->ticket_number }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $ticket->status === 'open' ? 'bg-yellow-500/20 text-yellow-400' : 
                               ($ticket->status === 'in_progress' ? 'bg-blue-500/20 text-blue-400' :
                               ($ticket->status === 'resolved' ? 'bg-green-500/20 text-green-400' : 'bg-slate-700 text-slate-400')) }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $ticket->priority === 'urgent' ? 'bg-red-500/20 text-red-400' : 
                               ($ticket->priority === 'high' ? 'bg-orange-500/20 text-orange-400' :
                               ($ticket->priority === 'medium' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-slate-700 text-slate-400')) }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                    <h1 class="text-xl font-bold">{{ $ticket->subject }}</h1>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    @if($ticket->status !== 'resolved' && $ticket->status !== 'closed')
                    <form action="{{ route('admin.support.resolve', $ticket) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-500/20 text-green-400 hover:bg-green-500/30 rounded-lg text-sm font-medium transition">
                            Resolve
                        </button>
                    </form>
                    @endif
                    
                    @if($ticket->status !== 'closed')
                    <form action="{{ route('admin.support.close', $ticket) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm font-medium transition">
                            Close
                        </button>
                    </form>
                    @else
                    <form action="{{ route('admin.support.reopen', $ticket) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/80 rounded-lg text-sm font-medium transition">
                            Reopen
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            
            {{-- Customer Info --}}
            <div class="flex items-center gap-4 p-4 bg-surface-light rounded-xl mb-4">
                <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-lg">
                    {{ strtoupper(substr($ticket->user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-bold">{{ $ticket->user->name }}</div>
                    <div class="text-sm text-slate-400">{{ $ticket->user->email }}</div>
                </div>
                <div class="ml-auto text-right">
                    <div class="text-sm text-slate-400">Created</div>
                    <div class="font-medium">{{ $ticket->created_at->diffForHumans() }}</div>
                </div>
            </div>
            
            {{-- Message --}}
            <div class="prose prose-invert max-w-none">
                <p class="text-slate-300 whitespace-pre-wrap">{{ $ticket->message }}</p>
            </div>
        </div>
        
        {{-- Replies --}}
        <div class="space-y-4">
            <h3 class="font-bold text-lg">Conversation</h3>
            
            @forelse($ticket->replies as $reply)
            <div class="bg-surface-dark rounded-xl p-4 border border-slate-800 {{ $reply->is_internal ? 'border-l-4 border-l-purple-500' : '' }}">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full {{ $reply->is_staff ? 'bg-primary/20 text-primary' : 'bg-slate-700 text-slate-400' }} flex items-center justify-center font-bold">
                        {{ strtoupper(substr($reply->is_staff ? ($reply->admin?->name ?? 'Staff') : $reply->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold">{{ $reply->is_staff ? ($reply->admin?->name ?? 'Support Staff') : $reply->user->name }}</span>
                            @if($reply->is_internal)
                            <span class="px-2 py-0.5 bg-purple-500/20 text-purple-400 rounded text-xs">Internal Note</span>
                            @endif
                            <span class="text-sm text-slate-500">{{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-slate-300 whitespace-pre-wrap">{{ $reply->message }}</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-slate-500">
                <span class="material-symbols-outlined text-4xl mb-2 block">chat</span>
                No replies yet
            </div>
            @endforelse
        </div>
        
        {{-- Reply Form --}}
        <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
            <form action="{{ route('admin.support.reply', $ticket) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <textarea name="message" rows="4" placeholder="Type your reply..." required
                              class="w-full px-4 py-3 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary focus:ring-1 focus:ring-primary resize-none"></textarea>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer">
                        <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-700 bg-surface-light text-purple-500 focus:ring-purple-500">
                        <span>Internal note (customer won't see)</span>
                    </label>
                    <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/80 rounded-lg font-medium transition">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        {{-- Assignment --}}
        <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">person</span>
                Assignment
            </h3>
            
            <form action="{{ route('admin.support.assign', $ticket) }}" method="POST">
                @csrf
                <select name="admin_id" onchange="this.form.submit()"
                        class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary">
                    <option value="">-- Unassigned --</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ $ticket->assigned_admin_id == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }} {{ $agent->id === Auth::guard('admin')->id() ? '(You)' : '' }}
                    </option>
                    @endforeach
                </select>
            </form>
            
            @if($ticket->assignedAdmin)
            <div class="mt-4 p-3 bg-surface-light rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                        {{ strtoupper(substr($ticket->assignedAdmin->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-medium">{{ $ticket->assignedAdmin->name }}</div>
                        <div class="text-xs text-slate-400">{{ $ticket->assignedAdmin->role_label }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        {{-- SLA Status --}}
        <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">timer</span>
                SLA Status
            </h3>
            
            {{-- First Response --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-slate-400">First Response</span>
                    <span class="text-xs px-2 py-0.5 rounded
                        {{ $slaInfo['first_response']['breached'] ? 'bg-red-500/20 text-red-400' : 
                           ($slaInfo['first_response']['responded'] ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                        {{ $slaInfo['first_response']['responded'] ? ($slaInfo['first_response']['breached'] ? 'Breached' : 'Met') : 'Pending' }}
                    </span>
                </div>
                @if($slaInfo['first_response']['responded'])
                <div class="text-sm">
                    Responded in {{ number_format($slaInfo['first_response']['actual_minutes']) }} minutes
                </div>
                @else
                <div class="text-sm text-slate-400">
                    {{ $slaInfo['first_response']['breached'] ? 'SLA Breached!' : number_format($slaInfo['first_response']['time_remaining'] / 60, 1) . ' hours remaining' }}
                </div>
                @endif
            </div>
            
            {{-- Resolution --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-slate-400">Resolution</span>
                    <span class="text-xs px-2 py-0.5 rounded
                        {{ $slaInfo['resolution']['breached'] ? 'bg-red-500/20 text-red-400' : 
                           ($slaInfo['resolution']['resolved'] ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                        {{ $slaInfo['resolution']['resolved'] ? ($slaInfo['resolution']['breached'] ? 'Breached' : 'Met') : 'Pending' }}
                    </span>
                </div>
                @if($slaInfo['resolution']['resolved'])
                <div class="text-sm">
                    Resolved in {{ number_format($slaInfo['resolution']['actual_minutes'] / 60, 1) }} hours
                </div>
                @else
                <div class="text-sm text-slate-400">
                    {{ $slaInfo['resolution']['breached'] ? 'SLA Breached!' : number_format($slaInfo['resolution']['time_remaining'] / 60, 1) . ' hours remaining' }}
                </div>
                @endif
            </div>
        </div>
        
        {{-- Priority --}}
        <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4">Priority</h3>
            <form action="{{ route('admin.support.priority', $ticket) }}" method="POST">
                @csrf
                <select name="priority" onchange="this.form.submit()"
                        class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary">
                    <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </form>
        </div>
        
        {{-- Internal Notes --}}
        <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-500">note</span>
                Internal Notes
            </h3>
            <form action="{{ route('admin.support.notes', $ticket) }}" method="POST">
                @csrf
                <textarea name="internal_notes" rows="4" placeholder="Add internal notes..."
                          class="w-full px-4 py-3 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 resize-none text-sm mb-3">{{ $ticket->internal_notes }}</textarea>
                <button type="submit" class="w-full px-4 py-2 bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 rounded-lg text-sm font-medium transition">
                    Save Notes
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
