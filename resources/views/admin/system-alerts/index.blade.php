@extends('admin.layouts.app')

@section('title', 'System Alerts')
@section('page_title', 'System Alerts & Notifications')

@section('content')

<!-- Metrics Overview -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-slate-400">storage</span>
            <span class="text-xs text-slate-500 uppercase font-bold">Disk Usage</span>
        </div>
        <div class="text-xl font-black {{ $metrics['disk_usage'] > 80 ? 'text-red-400' : ($metrics['disk_usage'] > 60 ? 'text-yellow-400' : 'text-green-400') }}">
            {{ $metrics['disk_usage'] }}%
        </div>
        <div class="h-1.5 bg-surface-light rounded-full mt-2 overflow-hidden">
            <div class="h-full {{ $metrics['disk_usage'] > 80 ? 'bg-red-400' : ($metrics['disk_usage'] > 60 ? 'bg-yellow-400' : 'bg-green-400') }} rounded-full transition-all" style="width: {{ $metrics['disk_usage'] }}%"></div>
        </div>
    </div>

    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-slate-400">error</span>
            <span class="text-xs text-slate-500 uppercase font-bold">Failed Jobs</span>
        </div>
        <div class="text-xl font-black {{ $metrics['failed_jobs_count'] > 50 ? 'text-red-400' : ($metrics['failed_jobs_count'] > 10 ? 'text-yellow-400' : 'text-green-400') }}">
            {{ number_format($metrics['failed_jobs_count']) }}
        </div>
        <div class="text-xs text-slate-400 mt-2">in queue</div>
    </div>

    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-slate-400">queue</span>
            <span class="text-xs text-slate-500 uppercase font-bold">Queue Size</span>
        </div>
        <div class="text-xl font-black {{ $metrics['queue_size'] > 500 ? 'text-red-400' : ($metrics['queue_size'] > 100 ? 'text-yellow-400' : 'text-green-400') }}">
            {{ number_format($metrics['queue_size']) }}
        </div>
        <div class="text-xs text-slate-400 mt-2">pending jobs</div>
    </div>

    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-slate-400">bug_report</span>
            <span class="text-xs text-slate-500 uppercase font-bold">Error Rate</span>
        </div>
        <div class="text-xl font-black {{ $metrics['error_rate'] > 10 ? 'text-red-400' : ($metrics['error_rate'] > 5 ? 'text-yellow-400' : 'text-green-400') }}">
            {{ $metrics['error_rate'] }}
        </div>
        <div class="text-xs text-slate-400 mt-2">per minute</div>
    </div>

    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-slate-400">notifications_active</span>
            <span class="text-xs text-slate-500 uppercase font-bold">Active Rules</span>
        </div>
        <div class="text-xl font-black text-blue-400">
            {{ count(array_filter($alertRules, fn($r) => $r['enabled'])) }}
        </div>
        <div class="text-xs text-slate-400 mt-2">of {{ count($alertRules) }} total</div>
    </div>
</div>

<!-- Service Status -->
<div class="bg-surface-dark rounded-xl p-4 border border-slate-800 mb-6">
    <h3 class="font-bold mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">health_and_safety</span>
        Service Status
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($metrics['service_status'] as $service)
        <div class="flex items-center gap-3 p-3 bg-surface-light rounded-lg">
            <div class="w-3 h-3 rounded-full {{ $service['healthy'] ? 'bg-green-400 animate-pulse' : 'bg-red-400' }}"></div>
            <div>
                <div class="font-medium text-sm">{{ $service['name'] }}</div>
                <div class="text-xs {{ $service['healthy'] ? 'text-green-400' : 'text-red-400' }}">
                    {{ $service['healthy'] ? 'Operational' : 'Down' }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Alert Rules -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">rule</span>
                    Alert Rules
                </h3>
                <button onclick="showAddModal()" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/80 rounded-lg transition text-sm">
                    <span class="material-symbols-outlined text-sm">add</span>
                    Add Rule
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-light/50 text-slate-400">
                        <tr>
                            <th class="text-left py-3 px-6">Rule</th>
                            <th class="text-left py-3 px-6">Condition</th>
                            <th class="text-left py-3 px-6">Channels</th>
                            <th class="text-center py-3 px-6">Status</th>
                            <th class="text-right py-3 px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($alertRules as $rule)
                        <tr class="hover:bg-surface-light/30 transition">
                            <td class="py-4 px-6">
                                <div class="font-medium">{{ $rule['name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $rule['description'] ?? 'No description' }}</div>
                                @if($rule['last_triggered'])
                                    <div class="text-xs text-slate-500 mt-1">
                                        Last: {{ \Carbon\Carbon::parse($rule['last_triggered'])->diffForHumans() }}
                                        ({{ $rule['trigger_count'] ?? 0 }} times)
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $typeLabels = [
                                        'disk_usage' => 'Disk Usage',
                                        'service_down' => 'Service Down',
                                        'failed_jobs' => 'Failed Jobs',
                                        'error_rate' => 'Error Rate',
                                        'queue_backlog' => 'Queue Backlog',
                                    ];
                                    $conditionLabels = [
                                        'greater_than' => '>',
                                        'less_than' => '<',
                                        'equals' => '=',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded text-xs bg-surface-light">{{ $typeLabels[$rule['type']] ?? $rule['type'] }}</span>
                                <div class="text-xs text-slate-400 mt-1">
                                    {{ $conditionLabels[$rule['condition']] ?? $rule['condition'] }} {{ $rule['threshold'] }}
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex gap-1 flex-wrap">
                                    @foreach($rule['channels'] as $channel)
                                        @php
                                            $channelIcons = [
                                                'email' => 'mail',
                                                'slack' => 'chat',
                                                'telegram' => 'send',
                                                'database' => 'database',
                                            ];
                                        @endphp
                                        <span class="px-1.5 py-0.5 rounded bg-surface-light text-xs" title="{{ $channel }}">
                                            <span class="material-symbols-outlined text-xs">{{ $channelIcons[$channel] ?? 'notifications' }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <form action="{{ route('admin.system-alerts.toggle', $rule['id']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $rule['enabled'] ? 'bg-green-500' : 'bg-slate-600' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $rule['enabled'] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <form action="{{ route('admin.system-alerts.destroy', $rule['id']) }}" method="POST" class="inline" onsubmit="return confirm('Delete this alert rule?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-20">notifications_off</span>
                                <p>No alert rules configured</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alert History -->
        <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800">
                <h3 class="font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Recent Alerts History
                </h3>
            </div>
            <div class="divide-y divide-slate-800 max-h-80 overflow-y-auto">
                @forelse($alertHistory as $history)
                @php
                    $severityColors = [
                        'critical' => 'text-red-400 bg-red-500/10 border-red-500/20',
                        'warning' => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20',
                        'info' => 'text-blue-400 bg-blue-500/10 border-blue-500/20',
                    ];
                    $color = $severityColors[$history['severity']] ?? $severityColors['info'];
                @endphp
                <div class="px-6 py-4 flex items-start gap-4 hover:bg-surface-light/30 transition">
                    <div class="w-2 h-2 rounded-full mt-2 {{ $history['severity'] === 'critical' ? 'bg-red-400' : ($history['severity'] === 'warning' ? 'bg-yellow-400' : 'bg-blue-400') }}"></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium">{{ $history['rule_name'] }}</span>
                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $color }}">{{ ucfirst($history['severity']) }}</span>
                        </div>
                        <p class="text-sm text-slate-400">{{ $history['message'] }}</p>
                        @if(!empty($history['data']))
                            <div class="mt-2 text-xs text-slate-500 font-mono">
                                @foreach($history['data'] as $key => $value)
                                    @if(is_array($value))
                                        {{ $key }}: {{ json_encode($value) }}
                                    @else
                                        {{ $key }}: {{ $value }}
                                    @endif
                                    @if(!$loop->last), @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($history['created_at'])->diffForHumans() }}</div>
                        <div class="flex gap-1 mt-1 justify-end">
                            @foreach($history['channels'] as $ch)
                                @php
                                    $chIcons = ['email' => 'mail', 'slack' => 'chat', 'telegram' => 'send', 'database' => 'database'];
                                @endphp
                                <span class="text-xs text-slate-500" title="Sent via {{ $ch }}">
                                    <span class="material-symbols-outlined text-xs">{{ $chIcons[$ch] ?? 'notifications' }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-20">notifications_off</span>
                    <p>No alerts triggered yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Test Alert -->
        <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">send</span>
                Test Alert
            </h3>
            <p class="text-sm text-slate-400 mb-4">Send a test alert to verify your notification channels are working.</p>
            <form action="{{ route('admin.system-alerts.test') }}" method="POST">
                @csrf
                <div class="space-y-3">
                    @foreach($channels as $channel => $available)
                    <button type="submit" name="channel" value="{{ $channel }}" 
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg border transition text-left {{ $available ? 'border-slate-700 hover:bg-surface-light hover:border-primary' : 'border-slate-800 opacity-50 cursor-not-allowed' }}"
                            {{ !$available ? 'disabled' : '' }}>
                        @php
                            $channelIcons = ['email' => 'mail', 'slack' => 'chat', 'telegram' => 'send', 'database' => 'database'];
                            $channelColors = ['email' => 'text-red-400', 'slack' => 'text-purple-400', 'telegram' => 'text-blue-400', 'database' => 'text-green-400'];
                        @endphp
                        <span class="material-symbols-outlined {{ $channelColors[$channel] ?? 'text-slate-400' }}">{{ $channelIcons[$channel] ?? 'notifications' }}</span>
                        <div class="flex-1">
                            <div class="font-medium text-sm">{{ ucfirst($channel) }}</div>
                            <div class="text-xs text-slate-500">{{ $available ? 'Available' : 'Not configured' }}</div>
                        </div>
                        @if($available)
                            <span class="material-symbols-outlined text-slate-400">send</span>
                        @endif
                    </button>
                    @endforeach
                </div>
            </form>
        </div>

        <!-- Quick Actions -->
        <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">bolt</span>
                Quick Actions
            </h3>
            <div class="space-y-2">
                <a href="{{ route('admin.failed-jobs.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-surface-light hover:bg-slate-700 transition">
                    <span class="material-symbols-outlined text-red-400">error</span>
                    <div class="flex-1">
                        <div class="font-medium text-sm">View Failed Jobs</div>
                        <div class="text-xs text-slate-500">{{ $metrics['failed_jobs_count'] }} jobs waiting</div>
                    </div>
                </a>
                <a href="{{ route('admin.system-health.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-surface-light hover:bg-slate-700 transition">
                    <span class="material-symbols-outlined text-green-400">monitor_heart</span>
                    <div class="flex-1">
                        <div class="font-medium text-sm">System Health</div>
                        <div class="text-xs text-slate-500">View detailed status</div>
                    </div>
                </a>
                <a href="{{ route('admin.logs.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-surface-light hover:bg-slate-700 transition">
                    <span class="material-symbols-outlined text-blue-400">terminal</span>
                    <div class="flex-1">
                        <div class="font-medium text-sm">System Logs</div>
                        <div class="text-xs text-slate-500">Check error logs</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Channel Status -->
        <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">settings</span>
                Channel Configuration
            </h3>
            <div class="space-y-3">
                @foreach($channels as $channel => $available)
                <div class="flex items-center justify-between p-3 bg-surface-light rounded-lg">
                    <span class="font-medium text-sm">{{ ucfirst($channel) }}</span>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $available ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                        {{ $available ? 'Configured' : 'Not Set' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Add Alert Modal -->
<div id="addModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="hideAddModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-surface-dark rounded-2xl border border-slate-700 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="font-bold text-lg">Add Alert Rule</h3>
                <button onclick="hideAddModal()" class="p-2 text-slate-400 hover:text-white transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('admin.system-alerts.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Rule Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg focus:border-primary focus:outline-none"
                           placeholder="e.g., High Disk Usage Alert">
                </div>

                <div>
                    <label class="block text-sm text-slate-400 mb-2">Alert Type</label>
                    <select name="type" required class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg focus:border-primary focus:outline-none">
                        <option value="disk_usage">Disk Usage (%)</option>
                        <option value="failed_jobs">Failed Jobs Count</option>
                        <option value="queue_backlog">Queue Backlog</option>
                        <option value="error_rate">Error Rate (per min)</option>
                        <option value="service_down">Service Down</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-slate-400 mb-2">Condition</label>
                        <select name="condition" required class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg focus:border-primary focus:outline-none">
                            <option value="greater_than">Greater Than (&gt;)</option>
                            <option value="less_than">Less Than (&lt;)</option>
                            <option value="equals">Equals (=)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-400 mb-2">Threshold</label>
                        <input type="number" name="threshold" required step="0.01" min="0"
                               class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg focus:border-primary focus:outline-none"
                               placeholder="e.g., 80">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-slate-400 mb-2">Notification Channels</label>
                    <div class="space-y-2">
                        @foreach(['email' => 'Email', 'slack' => 'Slack', 'telegram' => 'Telegram', 'database' => 'In-App'] as $value => $label)
                        <label class="flex items-center gap-3 p-3 bg-surface-light rounded-lg cursor-pointer hover:bg-slate-700 transition">
                            <input type="checkbox" name="channels[]" value="{{ $value }}" class="w-4 h-4 rounded border-slate-600 text-primary focus:ring-primary bg-surface-light">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-slate-400 mb-2">Cooldown (minutes)</label>
                    <input type="number" name="cooldown" required min="1" max="1440" value="60"
                           class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg focus:border-primary focus:outline-none">
                    <p class="text-xs text-slate-500 mt-1">Minimum time between alerts for this rule</p>
                </div>

                <div>
                    <label class="block text-sm text-slate-400 mb-2">Description (optional)</label>
                    <textarea name="description" rows="2"
                              class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg focus:border-primary focus:outline-none resize-none"
                              placeholder="Brief description of this alert..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="hideAddModal()" class="flex-1 px-4 py-2 bg-surface-light hover:bg-slate-700 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary hover:bg-primary/80 rounded-lg transition">
                        Create Alert Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') hideAddModal();
});

// Auto-refresh metrics every 30 seconds
setInterval(() => {
    fetch('{{ route("admin.system-alerts.metrics") }}')
        .then(response => response.json())
        .then(data => {
            // Could update metrics in real-time here
            console.log('Metrics updated:', data);
        })
        .catch(console.error);
}, 30000);
</script>
@endpush
