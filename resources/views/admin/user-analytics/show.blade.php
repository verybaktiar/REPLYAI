@extends('admin.layouts.app')

@section('title', 'User Analytics - ' . $user->name)
@section('page_title', 'User Detail Analytics')

@section('content')

<!-- User Header -->
<div class="bg-surface-dark rounded-xl p-6 border border-slate-800 mb-6">
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-primary to-blue-600 flex items-center justify-center text-2xl font-black text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                <p class="text-slate-400">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-2">
                    @if($user->subscription && $user->subscription->status === 'active')
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-400 border border-green-500/20">
                            {{ $user->subscription->plan->name ?? 'Active' }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-700 text-slate-400">Free</span>
                    @endif
                    @if($user->is_vip)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">VIP</span>
                    @endif
                    @if($user->whatsappDevices()->where('status', 'connected')->exists())
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-400 border border-green-500/20">WhatsApp</span>
                    @endif
                    @if($user->instagramAccounts()->where('is_active', true)->exists())
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-pink-500/10 text-pink-400 border border-pink-500/20">Instagram</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="text-right">
            <div class="text-sm text-slate-500 mb-1">Engagement Score</div>
            <div class="flex items-center gap-2">
                @php
                    $scoreColor = $engagementScore >= 80 ? 'text-green-400' : ($engagementScore >= 50 ? 'text-yellow-400' : 'text-red-400');
                    $scoreBg = $engagementScore >= 80 ? 'bg-green-500/10' : ($engagementScore >= 50 ? 'bg-yellow-500/10' : 'bg-red-500/10');
                @endphp
                <div class="w-16 h-16 rounded-full {{ $scoreBg }} flex items-center justify-center border-2 {{ $scoreColor }} border-current">
                    <span class="text-xl font-black {{ $scoreColor }}">{{ $engagementScore }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Member Since</div>
        <div class="font-bold">{{ $user->created_at->format('M d, Y') }}</div>
        <div class="text-xs text-slate-400">{{ $user->created_at->diffForHumans() }}</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Last Login</div>
        <div class="font-bold">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</div>
        <div class="text-xs text-slate-400">{{ $user->last_login_at?->format('M d, H:i') ?? 'N/A' }}</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">Last Active</div>
        <div class="font-bold">{{ $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'Never' }}</div>
        <div class="text-xs text-slate-400">{{ $user->last_activity_at?->format('M d, H:i') ?? 'N/A' }}</div>
    </div>
    <div class="bg-surface-dark rounded-xl p-4 border border-slate-800">
        <div class="text-sm text-slate-500 mb-1">WA Devices</div>
        <div class="font-bold">{{ $user->whatsappDevices()->count() }}</div>
        <div class="text-xs text-slate-400">{{ $user->whatsappDevices()->where('status', 'connected')->count() }} connected</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Daily Activity Chart -->
    <div class="lg:col-span-2 bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">trending_up</span>
            Daily Activity (Last 30 Days)
        </h3>
        <canvas id="activityChart" height="200"></canvas>
    </div>

    <!-- Login Patterns -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">schedule</span>
            Login Patterns
        </h3>
        <div class="space-y-4">
            <div>
                <div class="text-sm text-slate-500 mb-2">By Day of Week</div>
                <div class="space-y-2">
                    @php
                        $maxDayLogins = max(1, max($loginPatterns['by_day']));
                    @endphp
                    @foreach($loginPatterns['by_day'] as $day => $count)
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400 w-20">{{ $day }}</span>
                        <div class="flex-1 h-2 bg-surface-light rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full" style="width: {{ min(100, ($count / $maxDayLogins) * 100) }}%"></div>
                        </div>
                        <span class="text-xs font-bold w-8 text-right">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Feature Breakdown -->
    <div class="bg-surface-dark rounded-xl p-6 border border-slate-800">
        <h3 class="font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">analytics</span>
            Feature Usage Breakdown
        </h3>
        <div class="grid grid-cols-2 gap-4">
            @foreach($featureBreakdown as $feature => $count)
            @php
                $icons = [
                    'whatsapp_messages' => 'chat',
                    'instagram_messages' => 'photo_camera',
                    'web_widget_conversations' => 'web',
                    'broadcasts_sent' => 'campaign',
                    'kb_articles' => 'library_books',
                    'auto_rules' => 'rule',
                ];
                $colors = [
                    'whatsapp_messages' => 'text-green-400 bg-green-500/10',
                    'instagram_messages' => 'text-pink-400 bg-pink-500/10',
                    'web_widget_conversations' => 'text-blue-400 bg-blue-500/10',
                    'broadcasts_sent' => 'text-yellow-400 bg-yellow-500/10',
                    'kb_articles' => 'text-purple-400 bg-purple-500/10',
                    'auto_rules' => 'text-orange-400 bg-orange-500/10',
                ];
            @endphp
            <div class="p-4 bg-surface-light rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $colors[$feature] ?? 'text-slate-400 bg-slate-700' }} flex items-center justify-center">
                        <span class="material-symbols-outlined">{{ $icons[$feature] ?? 'help' }}</span>
                    </div>
                    <div>
                        <div class="text-2xl font-black">{{ number_format($count) }}</div>
                        <div class="text-xs text-slate-500">{{ ucfirst(str_replace('_', ' ', $feature)) }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Active Sessions -->
    <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800">
            <h3 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">devices</span>
                Active Sessions
            </h3>
        </div>
        <div class="divide-y divide-slate-800 max-h-80 overflow-y-auto">
            @forelse($sessions as $session)
            <div class="px-6 py-4 flex items-center justify-between hover:bg-surface-light/30 transition">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-slate-400">
                        {{ strpos($session->user_agent, 'Mobile') !== false ? 'smartphone' : 'computer' }}
                    </span>
                    <div>
                        <div class="font-medium text-sm">{{ $session->ip_address }}</div>
                        <div class="text-xs text-slate-500">{{ Str::limit($session->user_agent, 50) }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs {{ $session->last_activity >= now()->subMinutes(5)->timestamp ? 'text-green-400' : 'text-slate-500' }}">
                        {{ $session->last_activity >= now()->subMinutes(5)->timestamp ? 'Active now' : 'Last seen ' . Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                    </div>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-slate-500">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-20">devices_off</span>
                <p>No active sessions</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Activity Timeline -->
<div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
        <h3 class="font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">timeline</span>
            Activity Timeline
        </h3>
    </div>
    <div class="divide-y divide-slate-800 max-h-96 overflow-y-auto">
        @forelse($activityTimeline as $activity)
        @php
            $actionColors = [
                'login' => 'text-green-400 bg-green-500/10',
                'logout' => 'text-slate-400 bg-slate-700',
                'create' => 'text-blue-400 bg-blue-500/10',
                'update' => 'text-yellow-400 bg-yellow-500/10',
                'delete' => 'text-red-400 bg-red-500/10',
            ];
            $color = $actionColors[$activity->action] ?? 'text-slate-400 bg-slate-700';
        @endphp
        <div class="px-6 py-3 flex items-center gap-4 hover:bg-surface-light/30 transition">
            <div class="w-2 h-2 rounded-full {{ strpos($color, 'green') !== false ? 'bg-green-400' : (strpos($color, 'blue') !== false ? 'bg-blue-400' : (strpos($color, 'yellow') !== false ? 'bg-yellow-400' : (strpos($color, 'red') !== false ? 'bg-red-400' : 'bg-slate-400'))) }}"></div>
            <div class="flex-1">
                <span class="px-2 py-1 rounded text-xs font-bold {{ $color }}">{{ ucfirst($activity->action) }}</span>
                @if($activity->description)
                    <span class="text-sm text-slate-400 ml-2">{{ $activity->description }}</span>
                @endif
            </div>
            <div class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</div>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-slate-500">
            <span class="material-symbols-outlined text-4xl mb-2 opacity-20">history_off</span>
            <p>No activity recorded</p>
        </div>
        @endforelse
    </div>
</div>

<div class="mt-6 flex items-center gap-4">
    <a href="{{ route('admin.user-analytics.index') }}" class="flex items-center gap-2 px-4 py-2 bg-surface-light hover:bg-slate-700 rounded-lg transition">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Back to Analytics
    </a>
    <a href="{{ route('admin.users.show', $user->id) }}" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/80 rounded-lg transition">
        <span class="material-symbols-outlined text-sm">person</span>
        View User Profile
    </a>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Activity Chart
new Chart(document.getElementById('activityChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($dailyActivity, 'date')) !!}.map(d => d.slice(5)),
        datasets: [{
            label: 'Activities',
            data: {!! json_encode(array_column($dailyActivity, 'activities')) !!},
            borderColor: '#135bec',
            backgroundColor: 'rgba(19, 91, 236, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
        }, {
            label: 'Messages',
            data: {!! json_encode(array_column($dailyActivity, 'messages')) !!},
            borderColor: '#10b981',
            backgroundColor: 'transparent',
            borderDash: [5, 5],
            tension: 0.4,
            pointRadius: 2,
        }]
    },
    options: {
        responsive: true,
        interaction: { intersect: false },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
            x: { grid: { display: false } }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: { color: '#94a3b8' }
            }
        }
    }
});
</script>
@endpush
