@extends('admin.layouts.app')

@section('title', 'Edit Admin')
@section('page_title', 'Edit Admin: ' . $admin->name)

@section('content')

<a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
    <span class="material-symbols-outlined text-lg">arrow_back</span>
    Back to Admin List
</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Edit Form -->
    <div class="lg:col-span-2">
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h2 class="text-xl font-bold mb-6">Edit Admin</h2>

            <form action="{{ route('admin.admins.update', $admin) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium mb-2">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                           class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                           class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('email')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium mb-2">Role</label>
                    <select name="role" required
                            class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="superadmin" {{ old('role', $admin->role) === 'superadmin' ? 'selected' : '' }}>
                            Superadmin - Full Access
                        </option>
                        <option value="finance" {{ old('role', $admin->role) === 'finance' ? 'selected' : '' }}>
                            Finance - Payments & Revenue
                        </option>
                        <option value="support" {{ old('role', $admin->role) === 'support' ? 'selected' : '' }}>
                            Support - Tickets & View Only
                        </option>
                    </select>
                    @error('role')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $admin->is_active) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-slate-700 bg-surface-light text-primary focus:ring-primary">
                    <label for="is_active" class="text-sm">Active</label>
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/80 rounded-xl font-medium transition">
                        Update Admin
                    </button>
                    <a href="{{ route('admin.admins.index') }}" class="text-slate-400 hover:text-white">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Reset Password -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-500">key</span>
                Reset Password
            </h3>
            <form action="{{ route('admin.admins.reset-password', $admin) }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium mb-2">New Password</label>
                    <div class="flex gap-2">
                        <input type="text" name="password" id="new_password" required
                               class="flex-1 px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white font-mono text-sm">
                        <button type="button" onclick="generatePassword()" 
                                class="px-3 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm">
                            Generate
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Confirm Password</label>
                    <input type="text" name="password_confirmation" required
                           class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white font-mono text-sm">
                </div>

                <button type="submit" class="w-full px-4 py-2 bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-400 rounded-xl text-sm font-medium transition"
                        onclick="return confirm('Yakin reset password admin ini?')">
                    Reset Password
                </button>
            </form>
        </div>

        <!-- Admin Info -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4">Admin Info</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Created</span>
                    <span>{{ $admin->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Last Login</span>
                    <span>{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Last IP</span>
                    <span class="font-mono">{{ $admin->last_login_ip ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">2FA Status</span>
                    <span class="{{ $admin->two_factor_enabled ? 'text-green-400' : 'text-slate-500' }}">
                        {{ $admin->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Activities</span>
                    <span>{{ number_format($admin->activityLogs()->count()) }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.admins.activity', $admin) }}" 
                   class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm transition">
                    <span class="material-symbols-outlined">history</span>
                    View Activity Log
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function generatePassword() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 16; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('new_password').value = password;
    document.getElementsByName('password_confirmation')[0].value = password;
}
</script>

@endsection
