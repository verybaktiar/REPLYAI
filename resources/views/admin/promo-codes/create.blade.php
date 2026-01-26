@extends('admin.layouts.app')

@section('title', 'Create Promo Code')
@section('page_title', 'Create Promo Code')

@section('content')

<a href="{{ route('admin.promo-codes.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
    <span class="material-symbols-outlined text-lg">arrow_back</span>
    Kembali ke Promo Codes
</a>

<form action="{{ route('admin.promo-codes.store') }}" method="POST" class="max-w-2xl">
    @csrf

    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800 space-y-4 mb-6">
        
        <div>
            <label class="block text-sm font-medium mb-2">Promo Code <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="{{ old('code') }}" required
                   class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white uppercase font-mono focus:border-primary focus:ring-primary"
                   placeholder="DISC50">
            <p class="text-xs text-slate-400 mt-1">Akan otomatis diubah ke UPPERCASE</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Type <span class="text-red-500">*</span></label>
            <select name="type" required
                    class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary">
                <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (Rp)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Value <span class="text-red-500">*</span></label>
            <input type="number" name="value" value="{{ old('value') }}" required min="0" step="0.01"
                   class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary"
                   placeholder="50">
            <p class="text-xs text-slate-400 mt-1">Percentage: 1-100. Fixed: nominal Rupiah</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Max Uses</label>
            <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1"
                   class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary"
                   placeholder="Unlimited">
            <p class="text-xs text-slate-400 mt-1">Kosongkan untuk unlimited usage</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Expires At</label>
            <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                   class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary">
            <p class="text-xs text-slate-400 mt-1">Kosongkan untuk tidak ada expiry</p>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                   class="rounded bg-surface-light border-slate-700 text-primary focus:ring-primary">
            <label class="text-sm font-medium">Active (tampil untuk user)</label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="flex-1 py-4 bg-primary hover:bg-primary/90 rounded-xl font-bold text-lg transition">
            Create Promo Code
        </button>
        <a href="{{ route('admin.promo-codes.index') }}" class="px-6 py-4 bg-surface-dark hover:bg-slate-800 rounded-xl font-semibold border border-slate-700 transition">
            Cancel
        </a>
    </div>
</form>

@endsection
