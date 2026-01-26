@extends('admin.layouts.app')

@section('title', 'Feature Flags')
@section('page_title', 'Feature Flags')

@section('content')
@php
    $flags = \App\Models\FeatureFlag::orderBy('name')->get();
@endphp

<!-- Add New Flag -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 mb-8">
    <h3 class="font-bold text-lg mb-4">Add Feature Flag</h3>
    <form action="{{ route('admin.feature-flags.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @csrf
        <input type="text" name="key" placeholder="feature_key" required 
               class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white">
        <input type="text" name="name" placeholder="Feature Name" required 
               class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white">
        <select name="scope" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white">
            <option value="global">Global</option>
            <option value="plan">Per Plan</option>
            <option value="user">Per User</option>
        </select>
        <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/90 rounded-lg font-medium">
            Add Flag
        </button>
    </form>
</div>

<!-- Feature Flags List -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
    <h3 class="font-bold text-lg mb-6">All Feature Flags</h3>
    <div class="space-y-4">
        @forelse($flags as $flag)
        <div class="p-4 bg-surface-light rounded-xl border border-slate-700 flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-1">
                    <code class="text-sm bg-slate-800 px-2 py-0.5 rounded">{{ $flag->key }}</code>
                    <span class="font-medium">{{ $flag->name }}</span>
                    <span class="text-xs px-2 py-0.5 rounded 
                        {{ $flag->scope === 'global' ? 'bg-blue-500/20 text-blue-400' : 
                           ($flag->scope === 'plan' ? 'bg-purple-500/20 text-purple-400' : 'bg-green-500/20 text-green-400') }}">
                        {{ ucfirst($flag->scope) }}
                    </span>
                </div>
                <p class="text-sm text-slate-500">{{ $flag->description ?? 'No description' }}</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-400">{{ $flag->rollout_percentage }}%</span>
                <form action="{{ route('admin.feature-flags.toggle', $flag) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-14 h-7 rounded-full relative transition
                        {{ $flag->is_enabled ? 'bg-green-500' : 'bg-slate-700' }}">
                        <span class="absolute top-0.5 w-6 h-6 bg-white rounded-full transition-all
                            {{ $flag->is_enabled ? 'left-[30px]' : 'left-[4px]' }}"></span>
                    </button>
                </form>
                <form action="{{ route('admin.feature-flags.destroy', $flag) }}" method="POST" 
                      onsubmit="return confirm('Delete this flag?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-red-400 hover:bg-red-500/20 rounded-lg">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-slate-500">
            <span class="material-symbols-outlined text-3xl block mb-2">toggle_off</span>
            No feature flags defined yet
        </div>
        @endforelse
    </div>
</div>
@endsection
