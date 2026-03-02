@extends('admin.layouts.app')

@section('title', 'Message Templates')
@section('page_title', 'Message Templates')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-slate-400">Manage message templates for notifications and communications</p>
    </div>
    <a href="{{ route('admin.templates.create') }}" class="px-6 py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition flex items-center gap-2">
        <span class="material-symbols-outlined">add</span>
        Create Template
    </a>
</div>

{{-- Filters --}}
<div class="bg-surface-dark rounded-2xl border border-slate-800 p-4 mb-6">
    <form action="{{ route('admin.templates.index') }}" method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search templates..."
                   class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <select name="category" class="px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-sm focus:border-primary focus:outline-none">
                <option value="">All Categories</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="is_active" class="px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-sm focus:border-primary focus:outline-none">
                <option value="">All Status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-surface-light hover:bg-slate-700 rounded-lg text-sm transition flex items-center gap-1">
                <span class="material-symbols-outlined">filter_list</span>
                Filter
            </button>
            <a href="{{ route('admin.templates.index') }}" class="px-4 py-2 bg-surface-light hover:bg-slate-700 rounded-lg text-sm transition">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Templates Grid --}}
<div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-surface-light border-b border-slate-800">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Template</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Category</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Variables</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Updated</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($templates as $template)
                @php
                    $variables = json_decode($template->variables, true) ?? [];
                @endphp
                <tr class="hover:bg-surface-light/50 transition">
                    <td class="px-6 py-4">
                        <div class="font-medium text-white">{{ $template->name }}</div>
                        @if($template->description)
                            <div class="text-sm text-slate-500 mt-1">{{ Str::limit($template->description, 60) }}</div>
                        @endif
                        <div class="text-sm text-slate-400 mt-2 max-w-md truncate font-mono">{{ Str::limit($template->content, 80) }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold 
                            @switch($template->category)
                                @case('welcome') bg-green-500/20 text-green-400 @break
                                @case('notification') bg-blue-500/20 text-blue-400 @break
                                @case('reminder') bg-yellow-500/20 text-yellow-400 @break
                                @case('support') bg-purple-500/20 text-purple-400 @break
                                @case('marketing') bg-pink-500/20 text-pink-400 @break
                                @case('system') bg-slate-500/20 text-slate-400 @break
                                @default bg-gray-500/20 text-gray-400
                            @endswitch">
                            {{ $categories[$template->category] ?? $template->category }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($variables, 0, 3) as $var)
                                <span class="px-2 py-0.5 bg-surface-light rounded text-xs text-slate-400 font-mono">@{{ '{{ $var }}' }}</span>
                            @endforeach
                            @if(count($variables) > 3)
                                <span class="px-2 py-0.5 bg-surface-light rounded text-xs text-slate-400">+{{ count($variables) - 3 }}</span>
                            @endif
                            @if(empty($variables))
                                <span class="text-xs text-slate-600">-</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($template->is_active)
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">Active</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">
                        {{ $template->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.templates.edit', $template) }}" 
                               class="p-2 bg-primary hover:bg-primary/90 rounded-lg text-sm transition"
                               title="Edit">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this template?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm transition" title="Delete">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-5xl mb-3 opacity-50">description</span>
                        <p>No templates found</p>
                        <a href="{{ route('admin.templates.create') }}" class="text-primary hover:underline mt-2 inline-block">Create your first template</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($templates->hasPages())
    <div class="px-6 py-4 border-t border-slate-800">
        {{ $templates->links() }}
    </div>
    @endif
</div>

{{-- Available Variables Reference --}}
<div class="mt-8 bg-surface-dark rounded-2xl border border-slate-800 p-6">
    <h3 class="font-semibold mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">info</span>
        Available Template Variables
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <h4 class="text-sm font-medium text-slate-400 mb-2">User Variables</h4>
            <div class="space-y-1">
                @foreach($availableVariables['user'] as $var => $desc)
                    <div class="flex items-center gap-2 text-sm">
                        <code class="px-2 py-0.5 bg-surface-light rounded text-primary">{{ $var }}</code>
                        <span class="text-slate-500">- {{ $desc }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div>
            <h4 class="text-sm font-medium text-slate-400 mb-2">Order Variables</h4>
            <div class="space-y-1">
                @foreach($availableVariables['order'] as $var => $desc)
                    <div class="flex items-center gap-2 text-sm">
                        <code class="px-2 py-0.5 bg-surface-light rounded text-primary">{{ $var }}</code>
                        <span class="text-slate-500">- {{ $desc }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div>
            <h4 class="text-sm font-medium text-slate-400 mb-2">System Variables</h4>
            <div class="space-y-1">
                @foreach($availableVariables['system'] as $var => $desc)
                    <div class="flex items-center gap-2 text-sm">
                        <code class="px-2 py-0.5 bg-surface-light rounded text-primary">{{ $var }}</code>
                        <span class="text-slate-500">- {{ $desc }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
