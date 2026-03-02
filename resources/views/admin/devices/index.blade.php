@extends('admin.layouts.app')

@section('title', 'Device Management')
@section('page_title', 'Device Management')

@section('content')

<!-- Stats Overview -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-2 h-2 rounded-full bg-green-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase">WA Connected</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $stats['wa_connected'] }}</p>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase">WA Connecting</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $stats['wa_connecting'] }}</p>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-2 h-2 rounded-full bg-red-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase">WA Disconnected</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $stats['wa_disconnected'] }}</p>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-2 h-2 rounded-full bg-green-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase">IG Active</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $stats['ig_active'] }}</p>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-2 h-2 rounded-full bg-orange-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase">IG Expired</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $stats['ig_expired'] }}</p>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-2 h-2 rounded-full bg-purple-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase">Orphaned WA</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $stats['orphaned_wa'] }}</p>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-2 h-2 rounded-full bg-pink-500"></div>
            <span class="text-xs font-bold text-slate-400 uppercase">Orphaned IG</span>
        </div>
        <p class="text-2xl font-bold text-white">{{ $stats['orphaned_ig'] }}</p>
    </div>
</div>

<!-- Alerts -->
@if($stats['wa_disconnected'] > 0 || $stats['ig_expired'] > 0)
<div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined">warning</span>
        <span>{{ $stats['wa_disconnected'] + $stats['ig_expired'] }} device(s) need attention</span>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- WhatsApp Devices -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-green-500">chat</span>
                WhatsApp Devices ({{ $waDevices->count() }})
            </h3>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.devices.bulk') }}" class="inline" id="waBulkForm">
                    @csrf
                    <input type="hidden" name="type" value="wa">
                    <input type="hidden" name="action" id="waBulkAction">
                    <input type="hidden" name="devices" id="waSelectedDevices">
                </form>
                @if($stats['orphaned_wa'] > 0)
                <form method="POST" action="{{ route('admin.devices.cleanup-orphaned') }}" class="inline" onsubmit="return confirm('Delete all orphaned WA devices?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/10 text-red-400 text-xs font-bold border border-red-500/20 hover:bg-red-500 hover:text-white transition">
                        Clean Orphaned
                    </button>
                </form>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-background-dark/50 text-slate-400 text-xs">
                    <tr>
                        <th class="px-4 py-3"><input type="checkbox" id="selectAllWa" class="rounded bg-slate-700 border-slate-600"></th>
                        <th class="px-4 py-3">Device</th>
                        <th class="px-4 py-3">Owner</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($waDevices as $device)
                    <tr class="hover:bg-surface-light/10 transition {{ $device->user_id ? '' : 'bg-red-500/5' }}">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="wa-checkbox rounded bg-slate-700 border-slate-600" value="{{ $device->session_id }}">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-white">{{ $device->phone_number ?? $device->session_id }}</div>
                            <div class="text-xs text-slate-500">{{ $device->created_at->diffForHumans() }}</div>
                            @if(!$device->user_id)
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-red-500/20 text-red-400">ORPHANED</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($device->businessProfile?->user)
                            <div class="text-sm text-slate-300">{{ $device->businessProfile->user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $device->businessProfile->user->email }}</div>
                            @else
                            <span class="text-xs text-red-400 italic">No owner</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($device->status === \App\Models\WhatsAppDevice::STATUS_CONNECTED)
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-green-500/10 text-green-400 border border-green-500/20">CONNECTED</span>
                            @elseif($device->status === \App\Models\WhatsAppDevice::STATUS_CONNECTING)
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">CONNECTING</span>
                            @else
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/20">DISCONNECTED</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if($device->status !== \App\Models\WhatsAppDevice::STATUS_CONNECTED)
                                <form method="POST" action="{{ route('admin.devices.wa.reconnect', $device->session_id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition" title="Reconnect">
                                        <span class="material-symbols-outlined text-sm">sync</span>
                                    </button>
                                </form>
                                @endif
                                @if($device->status === \App\Models\WhatsAppDevice::STATUS_CONNECTED)
                                <form method="POST" action="{{ route('admin.devices.wa.disconnect', $device->session_id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500 hover:text-white transition" title="Disconnect">
                                        <span class="material-symbols-outlined text-sm">pause</span>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.devices.wa.destroy', $device->session_id) }}" class="inline" onsubmit="return confirm('Delete this device permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition" title="Delete">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            No WhatsApp devices found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-800 bg-surface-light/20 flex gap-2">
            <button onclick="waBulkAction('reconnect')" class="px-3 py-1.5 rounded-lg bg-blue-500/10 text-blue-400 text-xs font-bold hover:bg-blue-500 hover:text-white transition">Reconnect Selected</button>
            <button onclick="waBulkAction('disconnect')" class="px-3 py-1.5 rounded-lg bg-yellow-500/10 text-yellow-400 text-xs font-bold hover:bg-yellow-500 hover:text-white transition">Disconnect Selected</button>
            <button onclick="waBulkAction('delete')" class="px-3 py-1.5 rounded-lg bg-red-500/10 text-red-400 text-xs font-bold hover:bg-red-500 hover:text-white transition">Delete Selected</button>
        </div>
    </div>

    <!-- Instagram Accounts -->
    <div class="bg-surface-dark rounded-2xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 bg-surface-light/30 flex items-center justify-between">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-pink-500">photo_camera</span>
                Instagram Accounts ({{ $igAccounts->count() }})
            </h3>
            <div class="flex gap-2">
                @if($stats['orphaned_ig'] > 0)
                <form method="POST" action="{{ route('admin.devices.cleanup-orphaned') }}" class="inline" onsubmit="return confirm('Delete all orphaned IG accounts?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/10 text-red-400 text-xs font-bold border border-red-500/20 hover:bg-red-500 hover:text-white transition">
                        Clean Orphaned
                    </button>
                </form>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-background-dark/50 text-slate-400 text-xs">
                    <tr>
                        <th class="px-4 py-3"><input type="checkbox" id="selectAllIg" class="rounded bg-slate-700 border-slate-600"></th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">Owner</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($igAccounts as $account)
                    @php
                        $isExpired = !$account->token_expires_at || $account->token_expires_at <= now();
                    @endphp
                    <tr class="hover:bg-surface-light/10 transition {{ $account->user_id ? '' : 'bg-red-500/5' }}">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="ig-checkbox rounded bg-slate-700 border-slate-600" value="{{ $account->id }}">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-white">{{ $account->username }}</div>
                            <div class="text-xs text-slate-500">IG ID: {{ $account->instagram_id }}</div>
                            @if(!$account->user_id)
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-red-500/20 text-red-400">ORPHANED</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($account->user)
                            <div class="text-sm text-slate-300">{{ $account->user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $account->user->email }}</div>
                            @else
                            <span class="text-xs text-red-400 italic">No owner</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($account->is_active && !$isExpired)
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-green-500/10 text-green-400 border border-green-500/20">ACTIVE</span>
                            @elseif($account->is_active && $isExpired)
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-orange-500/10 text-orange-400 border border-orange-500/20">TOKEN EXPIRED</span>
                            @else
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-400 border border-slate-500/20">INACTIVE</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if($isExpired || !$account->is_active)
                                <a href="{{ route('admin.devices.ig.reconnect', $account->id) }}" class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white transition" title="Reconnect">
                                    <span class="material-symbols-outlined text-sm">sync</span>
                                </a>
                                @endif
                                @if($account->is_active)
                                <form method="POST" action="{{ route('admin.devices.ig.disconnect', $account->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-yellow-500/10 text-yellow-400 hover:bg-yellow-500 hover:text-white transition" title="Disconnect">
                                        <span class="material-symbols-outlined text-sm">pause</span>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.devices.ig.destroy', $account->id) }}" class="inline" onsubmit="return confirm('Delete this account permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white transition" title="Delete">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            No Instagram accounts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // WhatsApp bulk actions
    document.getElementById('selectAllWa')?.addEventListener('change', function() {
        document.querySelectorAll('.wa-checkbox').forEach(cb => cb.checked = this.checked);
    });

    function waBulkAction(action) {
        const selected = Array.from(document.querySelectorAll('.wa-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one device');
            return;
        }
        if (!confirm(`Are you sure you want to ${action} ${selected.length} device(s)?`)) return;
        
        document.getElementById('waBulkAction').value = action;
        document.getElementById('waSelectedDevices').value = JSON.stringify(selected);
        document.getElementById('waBulkForm').submit();
    }

    // Instagram bulk actions
    document.getElementById('selectAllIg')?.addEventListener('change', function() {
        document.querySelectorAll('.ig-checkbox').forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
