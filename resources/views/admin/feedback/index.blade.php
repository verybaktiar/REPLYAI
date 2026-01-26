@extends('admin.layouts.app')

@section('title', 'User Feedback')
@section('page_title', 'User Feedback Center')

@section('content')
@php
    $feedbacks = \App\Models\UserFeedback::with('user')
        ->orderByRaw("FIELD(status, 'new', 'reviewed', 'planned', 'in_progress', 'done', 'declined')")
        ->orderByDesc('created_at')
        ->paginate(20);
    
    $stats = [
        'new' => \App\Models\UserFeedback::where('status', 'new')->count(),
        'planned' => \App\Models\UserFeedback::where('status', 'planned')->count(),
        'in_progress' => \App\Models\UserFeedback::where('status', 'in_progress')->count(),
    ];
@endphp

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-yellow-400">{{ $stats['new'] }}</div>
        <div class="text-sm text-slate-400">New Feedback</div>
    </div>
    <div class="bg-blue-500/10 border border-blue-500/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-blue-400">{{ $stats['planned'] }}</div>
        <div class="text-sm text-slate-400">Planned</div>
    </div>
    <div class="bg-purple-500/10 border border-purple-500/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-purple-400">{{ $stats['in_progress'] }}</div>
        <div class="text-sm text-slate-400">In Progress</div>
    </div>
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <div class="text-3xl font-black">{{ \App\Models\UserFeedback::count() }}</div>
        <div class="text-sm text-slate-400">Total Feedback</div>
    </div>
</div>

<!-- Feedback List -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
    <h3 class="font-bold text-lg mb-6">All Feedback</h3>
    <div class="space-y-4">
        @forelse($feedbacks as $fb)
        <div class="p-4 bg-surface-light rounded-xl border-l-4 
            {{ $fb->type === 'bug' ? 'border-red-500' : ($fb->type === 'feature' ? 'border-blue-500' : ($fb->type === 'improvement' ? 'border-green-500' : 'border-slate-500')) }}">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded text-xs font-bold
                            {{ $fb->type === 'bug' ? 'bg-red-500/20 text-red-400' : ($fb->type === 'feature' ? 'bg-blue-500/20 text-blue-400' : 'bg-green-500/20 text-green-400') }}">
                            {{ ucfirst($fb->type) }}
                        </span>
                        <span class="font-medium">{{ $fb->title }}</span>
                    </div>
                    <p class="text-sm text-slate-400 mb-2">{{ Str::limit($fb->description, 200) }}</p>
                    <div class="text-xs text-slate-500">
                        By {{ $fb->user->name ?? 'Unknown' }} • {{ $fb->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="ml-4">
                    <form action="{{ route('admin.feedback.update-status', $fb) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()" 
                                class="text-xs px-2 py-1 bg-slate-800 border border-slate-700 rounded text-white">
                            @foreach(['new', 'reviewed', 'planned', 'in_progress', 'done', 'declined'] as $status)
                            <option value="{{ $status }}" {{ $fb->status === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-slate-500">
            <span class="material-symbols-outlined text-3xl block mb-2">feedback</span>
            No feedback yet
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $feedbacks->links() }}</div>
</div>
@endsection
