@extends('admin.layouts.app')

@section('title', 'Promo Codes')
@section('page_title', 'Promo Codes')

@section('content')

<div class="flex items-center justify-between mb-6">
    <p class="text-slate-400">Kelola kode promo dan diskon</p>
    <a href="{{ route('admin.promo-codes.create') }}" class="px-6 py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition flex items-center gap-2">
        <span class="material-symbols-outlined">add</span>
        Create Promo
    </a>
</div>

<div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-surface-light border-b border-slate-800">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Code</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Type</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Value</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Usage</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Expires</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($promoCodes as $promo)
                <tr class="hover:bg-surface-light/50 transition">
                    <td class="px-6 py-4">
                        <span class="font-mono font-bold text-primary text-lg">{{ $promo->code }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($promo->type === 'percentage')
                        <span class="px-2 py-1 rounded-full text-xs bg-purple-500/20 text-purple-400">Percentage</span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs bg-blue-500/20 text-blue-400">Fixed</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-semibold">
                        @if($promo->type === 'percentage')
                        {{ $promo->value }}%
                        @else
                        Rp {{ number_format($promo->value, 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm">{{ $promo->used_count }} / {{ $promo->max_uses ?? '∞' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">
                        @if($promo->expires_at)
                        {{ $promo->expires_at->format('d M Y') }}
                        @else
                        <span class="text-slate-500">No expiry</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($promo->is_active)
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">Active</span>
                        @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.promo-codes.edit', $promo) }}" class="p-2 bg-primary hover:bg-primary/90 rounded-lg text-sm transition">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                            <form action="{{ route('admin.promo-codes.destroy', $promo) }}" method="POST" onsubmit="return confirm('Hapus promo code ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm transition">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-5xl mb-3 opacity-50">local_offer</span>
                        <p>Belum ada promo code</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($promoCodes->hasPages())
    <div class="px-6 py-4 border-t border-slate-800">
        {{ $promoCodes->links() }}
    </div>
    @endif
</div>

@endsection
