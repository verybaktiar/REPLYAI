@extends('admin.layouts.app')

@section('title', 'Bulk Actions')
@section('page_title', 'Bulk Actions')

@section('content')
<!-- Bulk Email -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 mb-8">
    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">mail</span>
        Bulk Email
    </h3>
    <form action="{{ route('admin.bulk.email') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-2">Target Audience</label>
                <select name="audience" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white">
                    <option value="all">All Users</option>
                    <option value="active">Active Subscribers</option>
                    <option value="free">Free Users</option>
                    <option value="expiring">Expiring Soon (7 days)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-2">Subject</label>
                <input type="text" name="subject" required 
                       class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white">
            </div>
        </div>
        <div>
            <label class="block text-sm text-slate-400 mb-2">Message</label>
            <textarea name="message" rows="4" required
                      class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white resize-none"></textarea>
        </div>
        <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/90 rounded-lg font-medium">
            Send Bulk Email
        </button>
    </form>
</div>

<!-- Bulk Subscription Extend -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 mb-8">
    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-green-500">schedule</span>
        Extend Subscriptions
    </h3>
    <form action="{{ route('admin.bulk.extend') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf
        <div>
            <label class="block text-sm text-slate-400 mb-2">Target</label>
            <select name="target" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white">
                <option value="expiring">Expiring in 7 days</option>
                <option value="all_active">All Active</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-slate-400 mb-2">Extend by (days)</label>
            <input type="number" name="days" value="7" min="1" max="365" required
                   class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white">
        </div>
        <div class="flex items-end">
            <button type="submit" class="px-6 py-2 bg-green-500 hover:bg-green-600 rounded-lg font-medium text-white">
                Extend Subscriptions
            </button>
        </div>
    </form>
</div>

<!-- Bulk Reset Usage -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-yellow-500">restart_alt</span>
        Reset Usage Limits
    </h3>
    <form action="{{ route('admin.bulk.reset-usage') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block text-sm text-slate-400 mb-2">Plan</label>
            <select name="plan_id" class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white">
                <option value="">All Plans</option>
                @foreach(\App\Models\Plan::all() as $plan)
                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 rounded-lg font-medium text-black">
                Reset All Usage
            </button>
        </div>
    </form>
</div>
@endsection
