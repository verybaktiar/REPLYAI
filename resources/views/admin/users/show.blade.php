@extends('admin.layouts.app')

@section('title', 'Detail User')
@section('page_title', 'Detail User')

@section('content')

<a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
    <span class="material-symbols-outlined text-lg">arrow_back</span>
    Kembali ke Daftar Users
</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <div class="flex items-start gap-4 mb-6">
                <div class="w-16 h-16 rounded-full bg-primary/20 flex items-center justify-center text-2xl font-bold text-primary">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                        @if($user->is_suspended)
                        <span class="px-3 py-1 bg-red-500/20 text-red-500 border border-red-500/30 rounded-full text-[10px] font-black uppercase tracking-widest animate-pulse">Account Suspended</span>
                        @endif
                        @if($user->is_vip)
                        <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 rounded text-xs font-bold">⭐ VIP</span>
                        @endif
                    </div>
                    <div class="text-slate-400">{{ $user->email }}</div>
                    <div class="text-sm text-slate-500 mt-1">
                        Bergabung {{ $user->created_at->format('d M Y') }}
                        @if($user->email_verified_at)
                        <span class="text-green-400 ml-2">✓ Verified</span>
                        @else
                        <span class="text-yellow-400 ml-2">⏳ Unverified</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-medium transition">
                    <span class="material-symbols-outlined align-middle mr-1">edit</span>
                    Edit
                </a>
            </div>

            <!-- Quick Actions -->
            <div class="flex gap-3 pt-4 border-t border-slate-800">
                <form action="{{ route('admin.users.toggle-vip', $user) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 {{ $user->is_vip ? 'bg-orange-500/20 text-orange-400 hover:bg-orange-500/30' : 'bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30' }} rounded-xl text-sm font-medium transition">
                        <span class="material-symbols-outlined align-middle mr-1 text-lg">{{ $user->is_vip ? 'star_half' : 'star' }}</span>
                        {{ $user->is_vip ? 'Remove VIP' : 'Set VIP' }}
                    </button>
                </form>
                <form action="{{ route('admin.users.impersonate', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-purple-500/20 text-purple-400 hover:bg-purple-500/30 rounded-xl text-sm font-medium transition">
                        <span class="material-symbols-outlined align-middle mr-1 text-lg">login</span>
                        Impersonate
                    </button>
                </form>

                @if($user->is_suspended)
                <form action="{{ route('admin.users.activate', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-500/20 text-green-400 hover:bg-green-500/30 rounded-xl text-sm font-medium transition">
                        <span class="material-symbols-outlined align-middle mr-1 text-lg">check_circle</span>
                        Activate
                    </button>
                </form>
                @else
                <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-xl text-sm font-medium transition" onclick="return confirm('Suspend user ini?')">
                        <span class="material-symbols-outlined align-middle mr-1 text-lg">block</span>
                        Suspend
                    </button>
                </form>
                @endif

                <form action="{{ route('admin.users.reset-usage', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl text-sm font-medium transition">
                        <span class="material-symbols-outlined align-middle mr-1 text-lg">restart_alt</span>
                        Reset Usage
                    </button>
                </form>
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-bold text-lg mb-4">Riwayat Pembayaran</h3>
            @if($user->payments->count() > 0)
            <div class="space-y-3">
                @foreach($user->payments as $payment)
                <div class="flex items-center justify-between p-3 bg-surface-light rounded-xl">
                    <div>
                        <div class="font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                        <div class="text-sm text-slate-400">{{ $payment->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-medium 
                        {{ $payment->status === 'paid' ? 'bg-green-500/20 text-green-400' : ($payment->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-slate-500 text-center py-4">Belum ada pembayaran</p>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Subscription -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
            <h3 class="font-bold text-lg mb-4">Subscription</h3>
            @if($user->subscription && $user->subscription->status === 'active')
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 mb-4">
                <div class="font-bold text-green-400 text-lg">{{ $user->subscription->plan->name ?? 'Unknown' }}</div>
                <div class="text-sm text-slate-400 mt-1">
                    Berakhir: {{ $user->subscription->expires_at?->format('d M Y') ?? '-' }}
                </div>
            </div>
            @else
            <div class="bg-slate-800 rounded-xl p-4 mb-4">
                <div class="text-slate-400">Tidak ada subscription aktif</div>
            </div>
            @endif

            <!-- Assign Subscription Form -->
            <form action="{{ route('admin.users.assign-subscription', $user) }}" method="POST">
                @csrf
                <h4 class="text-sm font-medium mb-3">Assign Subscription Manual</h4>
                <div class="space-y-3">
                    <select name="plan_id" required class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-2 text-white text-sm">
                        <option value="">Pilih Plan</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <select name="duration_months" required class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-2 text-white text-sm">
                        <option value="">Durasi</option>
                        <option value="1">1 Bulan</option>
                        <option value="3">3 Bulan</option>
                        <option value="6">6 Bulan</option>
                        <option value="12">12 Bulan</option>
                    </select>
                    <button type="submit" class="w-full bg-primary hover:bg-primary/80 rounded-xl py-2 text-sm font-medium transition">
                        Assign Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
