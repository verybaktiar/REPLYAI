@extends('admin.layouts.app')

@section('title', 'Payments Management')
@section('page_title', 'Payments Management')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>[x-cloak] { display: none !important; }</style>

<!-- Stats & Filter Tabs -->
<div class="flex items-center gap-4 mb-6 flex-wrap">
    <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" 
       class="px-6 py-3 rounded-xl border font-semibold transition {{ $status === 'pending' ? 'bg-primary border-primary text-white' : 'bg-surface-dark border-slate-700 text-slate-300 hover:border-primary' }}">
        Pending <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-yellow-500 text-black">{{ $stats['pending'] }}</span>
    </a>
    <a href="{{ route('admin.payments.index', ['status' => 'paid']) }}" 
       class="px-6 py-3 rounded-xl border font-semibold transition {{ $status === 'paid' ? 'bg-primary border-primary text-white' : 'bg-surface-dark border-slate-700 text-slate-300 hover:border-primary' }}">
        Paid <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-green-500 text-black">{{ $stats['paid'] }}</span>
    </a>
    <a href="{{ route('admin.payments.index', ['status' => 'rejected']) }}" 
       class="px-6 py-3 rounded-xl border font-semibold transition {{ $status === 'rejected' ? 'bg-primary border-primary text-white' : 'bg-surface-dark border-slate-700 text-slate-300 hover:border-primary' }}">
        Rejected <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-red-500 text-black">{{ $stats['rejected'] }}</span>
    </a>
    <a href="{{ route('admin.payments.index', ['status' => 'all']) }}" 
       class="px-6 py-3 rounded-xl border font-semibold transition {{ $status === 'all' ? 'bg-primary border-primary text-white' : 'bg-surface-dark border-slate-700 text-slate-300 hover:border-primary' }}">
        All
    </a>
</div>

<!-- Payments Table -->
<div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-surface-light border-b border-slate-800">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Invoice</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">User</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Plan</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Amount</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($payments as $payment)
                <tr class="hover:bg-surface-light/50 transition" x-data="{ showModal: false }">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm">{{ $payment->invoice_number }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium">{{ $payment->user->name }}</div>
                        <div class="text-sm text-slate-400">{{ $payment->user->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium">{{ $payment->plan->name }}</div>
                        <div class="text-sm text-slate-400">{{ $payment->duration_months }} bulan</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold">Rp {{ number_format($payment->total, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($payment->status === 'pending')
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">Pending</span>
                        @elseif($payment->status === 'paid')
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">Paid</span>
                        @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">Rejected</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">
                        {{ $payment->created_at->format('d M Y, H:i') }}
                    </td>
                    <td class="px-6 py-4">
                        <button @click="showModal = true" class="px-4 py-2 bg-primary hover:bg-primary/90 rounded-lg text-sm font-medium transition">
                            View
                        </button>

                        <!-- Modal -->
                        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div class="absolute inset-0 bg-black/80" @click="showModal = false"></div>
                            <div class="relative bg-surface-dark rounded-2xl p-6 border border-slate-700 max-w-2xl w-full max-h-[90vh] overflow-auto">
                                <button @click="showModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>

                                <h2 class="text-2xl font-bold mb-6">Payment Detail</h2>

                                <div class="space-y-4 mb-6">
                                    <div><span class="text-slate-400">Invoice:</span> <span class="font-mono">{{ $payment->invoice_number }}</span></div>
                                    <div><span class="text-slate-400">User:</span> <span class="font-medium">{{ $payment->user->name }} ({{ $payment->user->email }})</span></div>
                                    <div><span class="text-slate-400">Plan:</span> <span>{{ $payment->plan->name }} - {{ $payment->duration_months }} bulan</span></div>
                                    <div><span class="text-slate-400">Total:</span> <span class="text-xl font-bold text-primary">Rp {{ number_format($payment->total, 0, ',', '.') }}</span></div>
                                </div>

                                @if($payment->proof_url)
                                <div class="mb-6">
                                    <label class="block text-sm font-medium mb-2">Bukti Transfer:</label>
                                    <img src="{{ $payment->proof_url }}" alt="Proof" class="rounded-xl border border-slate-700 max-w-full hover:scale-105 transition cursor-pointer" onclick="window.open(this.src, '_blank')">
                                </div>
                                @else
                                <div class="mb-6 p-4 rounded-xl bg-yellow-500/20 border border-yellow-500/50 text-yellow-300 text-sm">
                                    ⚠️ User belum upload bukti transfer
                                </div>
                                @endif

                                @if($payment->status === 'pending')
                                <div class="flex gap-3">
                                    <form action="{{ route('admin.payments.approve', $payment) }}" method="POST" class="flex-1" onsubmit="return confirm('Yakin approve payment ini?')">
                                        @csrf
                                        <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 rounded-xl font-bold transition">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.payments.reject', $payment) }}" method="POST" class="flex-1" x-data="{ reason: '' }">
                                        @csrf
                                        <input type="hidden" name="reason" x-model="reason">
                                        <button type="button" @click="reason = prompt('Alasan reject:'); if(reason) $el.closest('form').submit();" class="w-full py-3 bg-red-600 hover:bg-red-700 rounded-xl font-bold transition">
                                            ✗ Reject
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-5xl mb-3 opacity-50">payments</span>
                        <p>Tidak ada payment {{ $status !== 'all' ? $status : '' }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="px-6 py-4 border-t border-slate-800">
        {{ $payments->links() }}
    </div>
    @endif
</div>

@endsection
