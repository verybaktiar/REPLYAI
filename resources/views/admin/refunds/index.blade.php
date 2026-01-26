@extends('admin.layouts.app')

@section('title', 'Refund Management')
@section('page_title', 'Refund Management')

@section('content')
@php
    $pendingRefunds = \App\Models\Refund::where('status', 'pending')->with(['user', 'payment'])->latest()->get();
    $processedRefunds = \App\Models\Refund::whereIn('status', ['approved', 'rejected', 'processed'])
        ->with(['user', 'payment', 'processor'])
        ->latest()
        ->limit(20)
        ->get();
@endphp

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-yellow-400">{{ $pendingRefunds->count() }}</div>
        <div class="text-sm text-slate-400">Pending Requests</div>
    </div>
    <div class="bg-green-500/10 border border-green-500/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-green-400">{{ \App\Models\Refund::where('status', 'approved')->count() }}</div>
        <div class="text-sm text-slate-400">Approved</div>
    </div>
    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-red-400">{{ \App\Models\Refund::where('status', 'rejected')->count() }}</div>
        <div class="text-sm text-slate-400">Rejected</div>
    </div>
    <div class="bg-primary/10 border border-primary/30 rounded-2xl p-6">
        <div class="text-3xl font-black text-primary">Rp {{ number_format(\App\Models\Refund::where('status', 'processed')->sum('amount'), 0, ',', '.') }}</div>
        <div class="text-sm text-slate-400">Total Refunded</div>
    </div>
</div>

<!-- Pending Refunds -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 mb-8">
    <h3 class="font-bold text-lg mb-6 flex items-center gap-2">
        <span class="material-symbols-outlined text-yellow-500">pending</span>
        Pending Refund Requests
    </h3>
    
    @forelse($pendingRefunds as $refund)
    <div class="p-4 bg-surface-light rounded-xl mb-4 border border-slate-700">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="font-bold">{{ $refund->user->name ?? 'Unknown' }}</span>
                    <span class="text-xs text-slate-500">{{ $refund->user->email ?? '' }}</span>
                </div>
                <div class="text-sm text-slate-400 mb-2">
                    Invoice: {{ $refund->payment->invoice_number ?? '-' }} | 
                    Amount: <span class="text-yellow-400 font-bold">Rp {{ number_format($refund->amount, 0, ',', '.') }}</span>
                </div>
                <div class="text-sm bg-slate-800 p-2 rounded">
                    <strong>Reason:</strong> {{ $refund->reason ?? 'No reason provided' }}
                </div>
            </div>
            <div class="flex gap-2 ml-4">
                <form action="{{ route('admin.refunds.approve', $refund) }}" method="POST">
                    @csrf
                    <button class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-medium">
                        Approve
                    </button>
                </form>
                <form action="{{ route('admin.refunds.reject', $refund) }}" method="POST">
                    @csrf
                    <button class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium">
                        Reject
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-8 text-slate-500">
        <span class="material-symbols-outlined text-3xl block mb-2">check_circle</span>
        Tidak ada refund request pending
    </div>
    @endforelse
</div>

<!-- Processed Refunds -->
<div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
    <h3 class="font-bold text-lg mb-6">Recent Processed Refunds</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-slate-500 border-b border-slate-800">
                    <th class="pb-3">User</th>
                    <th class="pb-3">Amount</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3">Processed By</th>
                    <th class="pb-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($processedRefunds as $refund)
                <tr>
                    <td class="py-3 text-sm">{{ $refund->user->name ?? 'Unknown' }}</td>
                    <td class="py-3 text-sm font-bold">Rp {{ number_format($refund->amount, 0, ',', '.') }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            {{ $refund->status === 'approved' ? 'bg-green-500/20 text-green-400' : 
                               ($refund->status === 'rejected' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400') }}">
                            {{ ucfirst($refund->status) }}
                        </span>
                    </td>
                    <td class="py-3 text-sm text-slate-400">{{ $refund->processor->name ?? '-' }}</td>
                    <td class="py-3 text-sm text-slate-400">{{ $refund->processed_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-4 text-center text-slate-500">No processed refunds</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
