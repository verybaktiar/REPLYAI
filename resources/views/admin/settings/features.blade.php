@extends('admin.layouts.app')

@section('title', 'Feature Management')
@section('page_title', 'Feature Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold">Pengaturan Fitur & Sistem</h2>
        <p class="text-sm text-slate-400">Aktifkan atau matikan modul platform secara global.</p>
    </div>
    <div class="flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-xl border border-primary/20">
        <span class="material-symbols-outlined text-primary text-sm">info</span>
        <span class="text-xs font-bold text-primary uppercase tracking-widest">Global Enforcement</span>
    </div>
</div>

<form action="{{ route('admin.settings.features.update') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($groups as $groupName => $settings)
        <div class="bg-surface-dark rounded-3xl p-6 border border-slate-800 shadow-xl self-start">
            <div class="flex items-center gap-3 mb-6">
                <div class="size-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined">
                        {{ $groupName === 'features' ? 'extension' : ($groupName === 'registration' ? 'person_add' : 'settings') }}
                    </span>
                </div>
                <h3 class="font-black text-lg uppercase tracking-tight">{{ ucfirst($groupName) }}</h3>
            </div>

            <div class="space-y-4">
                @foreach($settings as $setting)
                <div class="p-4 bg-background-dark/50 rounded-2xl border border-slate-800 hover:border-slate-700 transition group">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-bold text-white mb-0.5 group-hover:text-primary transition">{{ $setting->label }}</label>
                            <p class="text-xs text-slate-500 line-clamp-2">{{ $setting->description }}</p>
                        </div>
                        
                        @if($setting->type === 'boolean')
                        <div class="relative inline-flex items-center cursor-pointer" x-data="{ on: {{ $setting->value == '1' ? 'true' : 'false' }} }">
                            <input type="hidden" name="settings[{{ $setting->key }}]" :value="on ? '1' : '0'">
                            <button type="button" 
                                    @click="on = !on"
                                    :class="on ? 'bg-primary' : 'bg-slate-700'"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                <span :class="on ? 'translate-x-5' : 'translate-x-0'"
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                        </div>
                        @else
                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" 
                               class="w-32 bg-surface-dark border border-slate-700 rounded-xl px-3 py-1.5 text-sm focus:border-primary transition">
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 flex justify-end">
        <button type="submit" class="px-8 py-4 bg-primary hover:bg-primary/80 text-white rounded-2xl font-black shadow-lg shadow-primary/20 transition flex items-center gap-2">
            <span class="material-symbols-outlined">save</span>
            SIMPAN PERUBAHAN
        </button>
    </div>
</form>

@endsection
