@extends('admin.layouts.app')

@section('title', 'Maintenance Mode')
@section('page_title', 'Maintenance Mode')

@section('content')
<div class="space-y-6">
    {{-- Status Card --}}
    <div class="bg-surface-dark rounded-xl border {{ $settings['enabled'] ? 'border-red-500/50' : 'border-green-500/50' }} p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl {{ $settings['enabled'] ? 'bg-red-500/20 text-red-500' : 'bg-green-500/20 text-green-500' }} flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">
                        {{ $settings['enabled'] ? 'construction' : 'check_circle' }}
                    </span>
                </div>
                <div>
                    <h2 class="text-xl font-bold">Maintenance Mode is {{ $settings['enabled'] ? 'ENABLED' : 'DISABLED' }}</h2>
                    <p class="text-slate-400">
                        @if($settings['enabled'])
                            Your site is currently in maintenance mode. Only admins and whitelisted IPs can access it.
                        @else
                            Your site is accessible to all users.
                        @endif
                    </p>
                </div>
            </div>
            <form action="{{ $settings['enabled'] ? route('admin.maintenance-mode.disable') : route('admin.maintenance-mode.enable') }}" method="POST">
                @csrf
                @if(!$settings['enabled'])
                    <input type="hidden" name="message" value="{{ $settings['message'] }}">
                @endif
                <button type="submit" 
                        class="px-6 py-3 rounded-lg font-medium transition {{ $settings['enabled'] ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-red-500 hover:bg-red-600 text-white' }}">
                    {{ $settings['enabled'] ? 'Disable Maintenance Mode' : 'Enable Maintenance Mode' }}
                </button>
            </form>
        </div>
    </div>

    @if($settings['enabled'])
    {{-- Countdown Timer --}}
    @if($settings['countdown_enabled'] && $settings['countdown_end'])
    <div class="bg-surface-dark rounded-xl border border-slate-700 p-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">timer</span>
            Countdown Timer
        </h3>
        <div class="flex items-center justify-center py-8">
            <div class="grid grid-cols-4 gap-4 text-center" id="countdown">
                <div class="bg-surface-light rounded-lg p-4 min-w-[80px]">
                    <div class="text-3xl font-bold text-primary" id="days">00</div>
                    <div class="text-xs text-slate-400 uppercase mt-1">Days</div>
                </div>
                <div class="bg-surface-light rounded-lg p-4 min-w-[80px]">
                    <div class="text-3xl font-bold text-primary" id="hours">00</div>
                    <div class="text-xs text-slate-400 uppercase mt-1">Hours</div>
                </div>
                <div class="bg-surface-light rounded-lg p-4 min-w-[80px]">
                    <div class="text-3xl font-bold text-primary" id="minutes">00</div>
                    <div class="text-xs text-slate-400 uppercase mt-1">Minutes</div>
                </div>
                <div class="bg-surface-light rounded-lg p-4 min-w-[80px]">
                    <div class="text-3xl font-bold text-primary" id="seconds">00</div>
                    <div class="text-xs text-slate-400 uppercase mt-1">Seconds</div>
                </div>
            </div>
        </div>
        <p class="text-center text-slate-400 text-sm">
            Estimated completion: <span class="text-white">{{ \Carbon\Carbon::parse($settings['countdown_end'])->format('F j, Y g:i A') }}</span>
        </p>
    </div>
    @endif
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Settings Form --}}
        <div class="bg-surface-dark rounded-xl border border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">settings</span>
                Maintenance Settings
            </h3>
            
            <form action="{{ route('admin.maintenance-mode.enable') }}" method="POST" class="space-y-4">
                @csrf
                
                {{-- Message --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Maintenance Message</label>
                    <textarea name="message" rows="3" 
                              class="w-full bg-surface-light border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:border-primary focus:ring-1 focus:ring-primary transition"
                              placeholder="Enter message to display to visitors...">{{ old('message', $settings['message']) }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">This message will be shown to visitors during maintenance mode.</p>
                </div>

                {{-- Countdown Timer --}}
                <div class="flex items-center justify-between p-4 bg-surface-light rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Enable Countdown Timer</label>
                        <p class="text-xs text-slate-500">Show countdown to estimated completion time</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="countdown_enabled" value="1" 
                               class="sr-only peer" {{ $settings['countdown_enabled'] ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-700 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                {{-- Countdown End Time --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Estimated Completion Time</label>
                    <input type="datetime-local" name="countdown_end" 
                           value="{{ $settings['countdown_end'] ? \Carbon\Carbon::parse($settings['countdown_end'])->format('Y-m-d\TH:i') : '' }}"
                           class="w-full bg-surface-light border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary transition">
                </div>

                <button type="submit" class="w-full px-4 py-3 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium transition">
                    {{ $settings['enabled'] ? 'Update Settings' : 'Enable Maintenance Mode' }}
                </button>
            </form>
        </div>

        {{-- IP Whitelist --}}
        <div class="bg-surface-dark rounded-xl border border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">network_check</span>
                IP Whitelist
            </h3>
            
            {{-- Add IP Form --}}
            <form action="{{ route('admin.maintenance-mode.whitelist') }}" method="POST" class="mb-6">
                @csrf
                <div class="flex gap-2">
                    <div class="flex-1">
                        <input type="text" name="ip" placeholder="Enter IP address (e.g., 192.168.1.1)" required
                               class="w-full bg-surface-light border border-slate-700 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:border-primary focus:ring-1 focus:ring-primary transition">
                    </div>
                    <button type="submit" class="px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg transition">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
                <input type="text" name="description" placeholder="Description (optional)" 
                       class="w-full mt-2 bg-surface-light border border-slate-700 rounded-lg px-4 py-2 text-sm text-white placeholder-slate-500 focus:border-primary transition">
                <p class="text-xs text-slate-500 mt-2">
                    <span class="material-symbols-outlined text-sm align-text-bottom">info</span>
                    Your current IP: <span class="text-primary font-mono">{{ request()->ip() }}</span>
                    <button type="button" onclick="document.querySelector('input[name=ip]').value='{{ request()->ip() }}'" 
                            class="ml-2 text-primary hover:underline cursor-pointer">Use this IP</button>
                </p>
            </form>

            {{-- Whitelisted IPs List --}}
            <div class="space-y-2 max-h-[300px] overflow-y-auto">
                @forelse($settings['allowed_ips'] as $index => $item)
                    @php
                        $ip = is_array($item) ? $item['ip'] : $item;
                        $description = is_array($item) ? ($item['description'] ?? '') : '';
                        $addedBy = is_array($item) ? ($item['added_by'] ?? 'Unknown') : 'Unknown';
                        $addedAt = is_array($item) && isset($item['added_at']) ? \Carbon\Carbon::parse($item['added_at']) : null;
                    @endphp
                    <div class="flex items-center justify-between p-3 bg-surface-light rounded-lg border {{ $ip === request()->ip() ? 'border-primary/50' : 'border-slate-700' }}">
                        <div>
                            <div class="font-mono text-sm {{ $ip === request()->ip() ? 'text-primary' : 'text-white' }}">
                                {{ $ip }}
                                @if($ip === request()->ip())
                                    <span class="ml-2 text-xs bg-primary/20 text-primary px-2 py-0.5 rounded">You</span>
                                @endif
                            </div>
                            @if($description)
                                <div class="text-xs text-slate-400 mt-1">{{ $description }}</div>
                            @endif
                            @if($addedAt)
                                <div class="text-xs text-slate-500 mt-0.5">Added by {{ $addedBy }} {{ $addedAt->diffForHumans() }}</div>
                            @endif
                        </div>
                        <form action="{{ route('admin.maintenance-mode.remove-whitelist', ['ip' => $ip]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition" 
                                    onclick="return confirm('Remove this IP from whitelist?')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500">
                        <span class="material-symbols-outlined text-4xl mb-2">network_locked</span>
                        <p>No IPs whitelisted yet.</p>
                        <p class="text-sm">Only admin users will be able to access the site during maintenance.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Preview Section --}}
    <div class="bg-surface-dark rounded-xl border border-slate-700 p-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">preview</span>
            Maintenance Page Preview
        </h3>
        <div class="border border-slate-700 rounded-lg overflow-hidden bg-slate-900">
            <div class="bg-surface-light px-4 py-2 flex items-center gap-2">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                </div>
                <div class="flex-1 text-center text-xs text-slate-500 font-mono">Maintenance Page Preview</div>
            </div>
            <div class="p-12 flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-5xl text-primary">construction</span>
                </div>
                <h1 class="text-2xl font-bold mb-4">Under Maintenance</h1>
                <p class="text-slate-400 max-w-md">{{ $settings['message'] }}</p>
                @if($settings['countdown_enabled'] && $settings['countdown_end'])
                    <div class="mt-6 flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined">timer</span>
                        <span>Back online in: {{ \Carbon\Carbon::parse($settings['countdown_end'])->diffForHumans() }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($settings['enabled'] && $settings['countdown_enabled'] && $settings['countdown_end'])
<script>
    function updateCountdown() {
        const endTime = new Date('{{ $settings['countdown_end'] }}').getTime();
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            document.getElementById('days').textContent = '00';
            document.getElementById('hours').textContent = '00';
            document.getElementById('minutes').textContent = '00';
            document.getElementById('seconds').textContent = '00';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('days').textContent = String(days).padStart(2, '0');
        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
</script>
@endif
@endsection
