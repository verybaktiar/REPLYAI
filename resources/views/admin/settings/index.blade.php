@extends('admin.layouts.app')

@section('title', 'System Settings')
@section('page_title', 'System Settings')

@section('content')

<div class="max-w-3xl">
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        @foreach($groups as $groupKey => $group)
        <div class="bg-surface-dark rounded-2xl border border-slate-800 mb-6 overflow-hidden">
            <div class="bg-surface-light px-6 py-4 flex items-center gap-3 border-b border-slate-800">
                <span class="material-symbols-outlined text-2xl text-primary">{{ $group['icon'] }}</span>
                <h2 class="font-bold text-lg">{{ $group['title'] }}</h2>
            </div>
            <div class="p-6 space-y-5">
                @foreach($group['settings'] as $setting)
                <div>
                    <label class="block text-sm font-medium mb-2">{{ $setting['label'] }}</label>
                    
                    @if($setting['type'] === 'boolean')
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="settings[{{ $setting['key'] }}]" value="1"
                            {{ $setting['value'] == '1' ? 'checked' : '' }}
                            class="w-5 h-5 rounded bg-surface-light border-slate-700 text-primary focus:ring-primary">
                        <span class="text-slate-400">{{ $setting['value'] == '1' ? 'Enabled' : 'Disabled' }}</span>
                    </label>
                    
                    @elseif($setting['type'] === 'password')
                    <div class="relative">
                        <input type="password" name="settings[{{ $setting['key'] }}]" 
                            value="{{ $setting['value'] }}"
                            class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary pr-12"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                    
                    @else
                    <input type="text" name="settings[{{ $setting['key'] }}]" 
                        value="{{ $setting['value'] }}"
                        class="w-full bg-surface-light border border-slate-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                        placeholder="{{ $setting['label'] }}">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 bg-primary hover:bg-primary/80 rounded-xl font-semibold transition">
                <span class="material-symbols-outlined align-middle mr-2">save</span>
                Simpan Settings
            </button>
        </div>
    </form>
</div>

<script>
    function togglePassword(btn) {
        const input = btn.previousElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<span class="material-symbols-outlined">visibility_off</span>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<span class="material-symbols-outlined">visibility</span>';
        }
    }
</script>

@endsection
