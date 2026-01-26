@extends('admin.layouts.app')

@section('title', 'Edit Plan: ' . $plan->name)
@section('page_title', 'Edit Plan')

@section('content')

<a href="{{ route('admin.plans.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6">
    <span class="material-symbols-outlined text-lg">arrow_back</span>
    Kembali ke Plans
</a>

<form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="space-y-6 max-w-4xl">
    @csrf
    @method('PUT')

    <!-- Basic Info -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h2 class="text-xl font-bold mb-4">Basic Information</h2>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Plan Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Description</label>
                <input type="text" name="description" value="{{ old('description', $plan->description) }}"
                       class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary"
                       placeholder="Untuk bisnis kecil yang baru mulai">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}
                       class="rounded bg-surface-light border-slate-700 text-primary focus:ring-primary">
                <label class="text-sm font-medium">Active (tampil di pricing page)</label>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h2 class="text-xl font-bold mb-4">Pricing</h2>
        
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Monthly Price (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" required min="0"
                       class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Yearly Price (Rp)</label>
                <input type="number" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" min="0"
                       class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary"
                       placeholder="Kosongkan jika tidak ada">
            </div>
        </div>
    </div>

    <!-- Limits -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h2 class="text-xl font-bold mb-4">Limits</h2>
        <p class="text-sm text-slate-400 mb-4">Gunakan -1 untuk unlimited</p>
        
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">AI Messages / Month <span class="text-red-500">*</span></label>
                <input type="number" name="limits[ai_messages]" value="{{ old('limits.ai_messages', $plan->getLimit('ai_messages', 0)) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Contacts <span class="text-red-500">*</span></label>
                <input type="number" name="limits[contacts]" value="{{ old('limits.contacts', $plan->getLimit('contacts', 0)) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">WhatsApp Devices <span class="text-red-500">*</span></label>
                <input type="number" name="limits[wa_devices]" value="{{ old('limits.wa_devices', $plan->getLimit('wa_devices', 0)) }}" required min="0"
                       class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Broadcast / Month</label>
                <input type="number" name="limits[broadcast_per_month]" value="{{ old('limits.broadcast_per_month', $plan->getLimit('broadcast_per_month', 0)) }}" min="0"
                       class="w-full px-4 py-3 rounded-xl bg-surface-light border border-slate-700 text-white focus:border-primary focus:ring-primary">
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="bg-surface-dark rounded-2xl p-6 border border-slate-800">
        <h2 class="text-xl font-bold mb-4">Fitur</h2>
        <p class="text-sm text-slate-400 mb-4">Pilih fitur yang tersedia untuk paket ini</p>
        
        @php
        $featureGroups = [
            '🤖 AI & Bot' => ['ai_reply', 'knowledge_base', 'rules_management', 'simulator', 'quick_reply'],
            '📬 Inbox & CRM' => ['unified_inbox', 'takeover', 'crm_contacts', 'contact_import_export'],
            '📱 Platform' => ['whatsapp', 'instagram', 'web_widget'],
            '📢 Marketing' => ['broadcast', 'sequences'],
            '📊 Analytics' => ['analytics', 'export_reports', 'activity_logs'],
            '⚙️ Settings' => ['business_profile', 'multi_wa_device'],
            '⭐ Support' => ['support_ticket', 'priority_support', 'dedicated_support', 'api_access'],
        ];
        @endphp

        <div class="space-y-6">
            @foreach($featureGroups as $groupName => $features)
            <div>
                <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">{{ $groupName }}</h3>
                <div class="grid md:grid-cols-2 gap-3">
                    @foreach($features as $feature)
                    <label class="flex items-center gap-3 p-3 bg-surface-light rounded-xl border border-slate-700 cursor-pointer hover:border-primary transition">
                        <input type="checkbox" name="features[{{ $feature }}]" value="1" {{ $plan->hasFeature($feature) ? 'checked' : '' }}
                               class="rounded bg-background-dark border-slate-600 text-primary focus:ring-primary">
                        <span class="font-medium text-sm">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Submit -->
    <div class="flex gap-4">
        <button type="submit" class="flex-1 py-4 bg-primary hover:bg-primary/90 rounded-xl font-bold text-lg transition">
            Save Changes
        </button>
        <a href="{{ route('admin.plans.index') }}" class="px-6 py-4 bg-surface-dark hover:bg-slate-800 rounded-xl font-semibold border border-slate-700 transition">
            Cancel
        </a>
    </div>
</form>

@endsection
