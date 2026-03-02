@extends('admin.layouts.app')

@section('title', 'Admin Management')
@section('page_title', 'Admin Management')

@section('content')

<!-- Header -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <p class="text-slate-400">Kelola admin users, role, dan aktivitas</p>
    </div>
    <a href="{{ route('admin.admins.create') }}" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/80 rounded-xl font-medium transition">
        <span class="material-symbols-outlined text-lg">person_add</span>
        Add Admin
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Total Admins</div>
        <div class="text-2xl font-black">{{ $stats['total'] }}</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Active</div>
        <div class="text-2xl font-black text-green-400">{{ $stats['active'] }}</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Inactive</div>
        <div class="text-2xl font-black text-red-400">{{ $stats['inactive'] }}</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Superadmin</div>
        <div class="text-2xl font-black text-yellow-400">{{ $stats['superadmin'] }}</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">With 2FA</div>
        <div class="text-2xl font-black text-blue-400">{{ $stats['with_2fa'] }}</div>
    </div>
</div>

<!-- Admins Table -->
<div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden mb-6">
    <table class="w-full">
        <thead class="bg-surface-light">
            <tr>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Admin</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Role</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Status</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">2FA</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Last Login</th>
                <th class="text-left px-6 py-4 text-sm font-semibold text-slate-400">Activities</th>
                <th class="text-right px-6 py-4 text-sm font-semibold text-slate-400">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($admins as $admin)
            <tr class="hover:bg-surface-light/50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full {{ $admin->isSuperAdmin() ? 'bg-red-500/20 text-red-500' : ($admin->role === 'finance' ? 'bg-green-500/20 text-green-500' : 'bg-blue-500/20 text-blue-500') }} flex items-center justify-center font-bold">
                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-medium">
                                {{ $admin->name }}
                                @if($admin->id === Auth::guard('admin')->id())
                                <span class="ml-2 px-2 py-0.5 bg-primary/20 text-primary rounded text-xs">You</span>
                                @endif
                            </div>
                            <div class="text-sm text-slate-400">{{ $admin->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        {{ $admin->isSuperAdmin() ? 'bg-red-500/20 text-red-400' : ($admin->role === 'finance' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400') }}">
                        {{ $admin->role_label }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($admin->is_active)
                    <span class="flex items-center gap-1 text-green-400 text-sm">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        Active
                    </span>
                    @else
                    <span class="flex items-center gap-1 text-red-400 text-sm">
                        <span class="material-symbols-outlined text-lg">cancel</span>
                        Inactive
                    </span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($admin->two_factor_enabled)
                    <span class="flex items-center gap-1 text-blue-400 text-sm">
                        <span class="material-symbols-outlined text-lg">security</span>
                        Enabled
                    </span>
                    @else
                    <span class="text-slate-500 text-sm">Disabled</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-slate-400">
                    {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}
                </td>
                <td class="px-6 py-4 text-sm text-slate-400">
                    {{ number_format($admin->activity_logs_count) }}
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.admins.activity', $admin) }}" 
                           class="p-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition"
                           title="View Activity">
                            <span class="material-symbols-outlined text-lg">history</span>
                        </a>
                        
                        @if($admin->id !== Auth::guard('admin')->id())
                        <form action="{{ route('admin.admins.toggle-status', $admin) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="p-2 rounded-lg transition {{ $admin->is_active ? 'bg-orange-500/20 text-orange-400 hover:bg-orange-500/30' : 'bg-green-500/20 text-green-400 hover:bg-green-500/30' }}"
                                    title="{{ $admin->is_active ? 'Deactivate' : 'Activate' }}">
                                <span class="material-symbols-outlined text-lg">{{ $admin->is_active ? 'block' : 'check_circle' }}</span>
                            </button>
                        </form>
                        
                        <a href="{{ route('admin.admins.edit', $admin) }}" 
                           class="p-2 bg-slate-700 hover:bg-slate-600 rounded-lg transition"
                           title="Edit">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </a>
                        
                        <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus admin ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="p-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg transition"
                                    title="Delete">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl mb-2 block">admin_panel_settings</span>
                    Tidak ada admin ditemukan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $admins->links() }}

<!-- Recent Admin Activities -->
<div class="mt-8">
    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">monitoring</span>
        Recent Security Events
    </h3>
    <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
        <table class="w-full">
            <thead class="bg-surface-light">
                <tr>
                    <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">Time</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">Admin</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">Action</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold text-slate-400">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($recentActivities as $activity)
                <tr class="hover:bg-surface-light/30 transition">
                    <td class="px-6 py-3 text-sm text-slate-400">
                        {{ $activity->created_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-3 text-sm">
                        {{ $activity->admin?->name ?? 'Unknown' }}
                    </td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 rounded text-xs font-medium
                            {{ $activity->action === 'failed_login' ? 'bg-red-500/20 text-red-400' : 
                               ($activity->action === 'unauthorized_access_attempt' ? 'bg-orange-500/20 text-orange-400' : 'bg-slate-700 text-slate-400') }}">
                            {{ $activity->action }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-slate-400">
                        {{ $activity->description }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                        No recent security events
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
