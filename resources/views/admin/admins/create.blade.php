@extends('admin.layouts.app')

@section('title', 'Create Admin')
@section('page_title', 'Create New Admin')

@section('content')

<a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
    <span class="material-symbols-outlined text-lg">arrow_back</span>
    Back to Admin List
</a>

<div class="max-w-2xl">
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h2 class="text-xl font-bold mb-6">Create New Admin</h2>

        <form action="{{ route('admin.admins.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium mb-2">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary focus:ring-1 focus:ring-primary">
                @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
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
                    <option value="">Select Role</option>
                    <option value="superadmin" {{ old('role') === 'superadmin' ? 'selected' : '' }}>
                        Superadmin - Full Access
                    </option>
                    <option value="finance" {{ old('role') === 'finance' ? 'selected' : '' }}>
                        Finance - Payments & Revenue
                    </option>
                    <option value="support" {{ old('role') === 'support' ? 'selected' : '' }}>
                        Support - Tickets & View Only
                    </option>
                </select>
                @error('role')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium mb-2">Password</label>
                <div class="flex gap-2">
                    <input type="text" name="password" id="password" required
                           class="flex-1 px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary focus:ring-1 focus:ring-primary font-mono">
                    <button type="button" onclick="generatePassword()" 
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-medium transition">
                        Generate
                    </button>
                </div>
                <p class="mt-1 text-xs text-slate-500">Min 12 characters. Copy this password to share with the new admin.</p>
                @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-medium mb-2">Confirm Password</label>
                <input type="text" name="password_confirmation" required
                       class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-xl text-white focus:border-primary focus:ring-1 focus:ring-primary">
            </div>

            <!-- Active Status -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                       class="w-5 h-5 rounded border-slate-700 bg-surface-light text-primary focus:ring-primary">
                <label for="is_active" class="text-sm">Active (can login immediately)</label>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary/80 rounded-xl font-medium transition">
                    Create Admin
                </button>
                <a href="{{ route('admin.admins.index') }}" class="text-slate-400 hover:text-white">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Role Descriptions -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-red-400">shield</span>
                <span class="font-bold text-red-400">Superadmin</span>
            </div>
            <p class="text-xs text-slate-400">Full access to all features including user management, system settings, and admin management.</p>
        </div>
        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-green-400">payments</span>
                <span class="font-bold text-green-400">Finance</span>
            </div>
            <p class="text-xs text-slate-400">Manage payments, refunds, revenue reports. View-only access to users.</p>
        </div>
        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-blue-400">support_agent</span>
                <span class="font-bold text-blue-400">Support</span>
            </div>
            <p class="text-xs text-slate-400">Handle support tickets and view user details. Cannot modify critical data.</p>
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
    document.getElementById('password').value = password;
    document.getElementsByName('password_confirmation')[0].value = password;
}

// Generate initial password
generatePassword();
</script>

@endsection
