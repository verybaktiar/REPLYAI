@extends('admin.layouts.app')

@section('title', 'User Management')
@section('page_title', 'User Management')

@section('content')

<!-- Header -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <p class="text-slate-400">Kelola user, subscription, dan VIP status</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="px-3 py-1 bg-slate-700 text-slate-300 rounded-full text-sm">
            {{ $users->total() }} Users
        </span>
        <a href="{{ route('admin.users.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/80 rounded-xl font-medium transition">
            <span class="material-symbols-outlined text-lg">person_add</span>
            Add User
        </a>
    </div>
</div>

<!-- Filters -->
<div class="bg-surface-dark rounded-xl p-4 mb-6 border border-slate-800">
    <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari nama atau email..." 
                   class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        
        <select name="plan" class="px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-white">
            <option value="">Semua Plan</option>
            <option value="vip" {{ request('plan') === 'vip' ? 'selected' : '' }}>⭐ VIP Only</option>
            <option value="no_subscription" {{ request('plan') === 'no_subscription' ? 'selected' : '' }}>Tanpa Subscription</option>
            @foreach($plans as $plan)
            <option value="{{ $plan->slug }}" {{ request('plan') === $plan->slug ? 'selected' : '' }}>{{ $plan->name }}</option>
            @endforeach
        </select>

        <select name="verified" class="px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-white">
            <option value="">Semua Status</option>
            <option value="yes" {{ request('verified') === 'yes' ? 'selected' : '' }}>Verified</option>
            <option value="no" {{ request('verified') === 'no' ? 'selected' : '' }}>Belum Verified</option>
        </select>

        <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/90 rounded-lg font-medium transition">
            <span class="material-symbols-outlined text-lg">search</span>
        </button>
    </form>
</div>

<!-- Users Table -->
<div class="bg-surface-dark rounded-xl overflow-hidden border border-slate-800">
    <table class="w-full">
        <thead class="bg-surface-light">
            <tr>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">User</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Plan</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Status</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Joined</th>
                <th class="text-right px-6 py-4 text-sm font-semibold text-slate-400">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($users as $user)
            <tr class="hover:bg-surface-light/50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-medium flex items-center gap-2">
                                {{ $user->name }}
                                @if($user->is_vip)
                                <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 rounded text-xs font-bold">VIP</span>
                                @endif
                            </div>
                            <div class="text-sm text-slate-400">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    @if($user->subscription && $user->subscription->plan)
                    <span class="px-3 py-1 bg-primary/20 text-primary rounded-full text-sm font-medium">
                        {{ $user->subscription->plan->name }}
                    </span>
                    @else
                    <span class="px-3 py-1 bg-slate-700 text-slate-400 rounded-full text-sm">
                        Free
                    </span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <form action="{{ route('admin.users.toggle-verify', $user) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="flex items-center gap-1 text-sm hover:opacity-80 transition cursor-pointer" title="Klik untuk {{ $user->email_verified_at ? 'unverify' : 'verify' }}">
                            @if($user->email_verified_at)
                            <span class="flex items-center gap-1 text-green-400">
                                <span class="material-symbols-outlined text-lg">verified</span>
                                Verified
                            </span>
                            @else
                            <span class="flex items-center gap-1 text-yellow-400">
                                <span class="material-symbols-outlined text-lg">pending</span>
                                Pending
                            </span>
                            @endif
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 text-sm text-slate-400">
                    {{ $user->created_at->format('d M Y') }}
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <!-- Toggle VIP -->
                        <form action="{{ route('admin.users.toggle-vip', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="p-2 rounded-lg text-sm transition {{ $user->is_vip ? 'bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30' : 'bg-slate-700 text-slate-400 hover:bg-slate-600' }}"
                                    title="{{ $user->is_vip ? 'Remove VIP' : 'Set VIP' }}">
                                <span class="material-symbols-outlined text-lg">{{ $user->is_vip ? 'star' : 'star_outline' }}</span>
                            </button>
                        </form>

                        <!-- Edit -->
                        <a href="{{ route('admin.users.edit', $user) }}" 
                           class="p-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition"
                           title="Edit">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </a>

                        <!-- Impersonate -->
                        <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="p-2 bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 rounded-lg transition"
                                    title="Login sebagai user ini">
                                <span class="material-symbols-outlined text-lg">login</span>
                            </button>
                        </form>

                        <!-- View Detail -->
                        <a href="{{ route('admin.users.show', $user) }}" 
                           class="p-2 bg-primary/20 hover:bg-primary/30 text-primary rounded-lg transition"
                           title="Detail">
                            <span class="material-symbols-outlined text-lg">visibility</span>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl mb-2 block">person_off</span>
                    Tidak ada user ditemukan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $users->appends(request()->query())->links() }}
</div>

@endsection
