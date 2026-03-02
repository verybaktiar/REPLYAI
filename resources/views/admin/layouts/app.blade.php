<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - REPLYAI</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-dark": "#0a0e17",
                        "surface-dark": "#141b2a",
                        "surface-light": "#1c2537",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover { background: rgba(19, 91, 236, 0.1); }
        .sidebar-link.active { background: rgba(19, 91, 236, 0.15); border-left: 3px solid #135bec; }
    </style>
</head>
<body class="bg-background-dark text-white font-display antialiased min-h-screen">
    @php
        $adminUser = Auth::guard('admin')->user();
        $isSuperAdmin = $adminUser->isSuperAdmin();
        $isFinance = $adminUser->role === \App\Models\AdminUser::ROLE_FINANCE;
        $isSupport = $adminUser->role === \App\Models\AdminUser::ROLE_SUPPORT;
        $canManagePayments = $adminUser->canManagePayments();
        $canManageTenants = $adminUser->canManageTenants();
    @endphp
    
    {{-- Command Palette --}}
    @include('admin.partials.command-palette')

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-surface-dark border-r border-slate-800 flex flex-col fixed h-screen">
            <!-- Logo -->
            <div class="p-5 border-b border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="text-2xl font-black text-primary">REPLY</span>
                    <span class="text-2xl font-black text-white">AI</span>
                </a>
                <div class="flex items-center gap-2 mt-3 px-3 py-1.5 rounded-lg border {{ $isSuperAdmin ? 'bg-red-500/10 border-red-500/20' : ($isFinance ? 'bg-green-500/10 border-green-500/20' : 'bg-blue-500/10 border-blue-500/20') }}">
                    <span class="material-symbols-outlined {{ $isSuperAdmin ? 'text-red-500' : ($isFinance ? 'text-green-500' : 'text-blue-500') }} text-sm">shield</span>
                    <span class="text-xs font-semibold {{ $isSuperAdmin ? 'text-red-400' : ($isFinance ? 'text-green-400' : 'text-blue-400') }}">{{ $adminUser->role_label }} Panel</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-4 overflow-y-auto" x-data="{ 
                expanded: { main: true, finance: true, system: true, operations: true }
            }">
                <div class="px-4 mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menu Utama</span>
                </div>
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">dashboard</span>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">group</span>
                    <span class="font-medium">Users</span>
                </a>

                @if($canManagePayments)
                <a href="{{ route('admin.payments.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.payments.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">payments</span>
                    <span class="font-medium">Payments</span>
                    @php $pendingCount = \App\Models\Payment::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                    <span class="ml-auto px-2 py-0.5 bg-orange-500 text-white text-xs font-bold rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>
                @endif

                @if($isSuperAdmin)
                <a href="{{ route('admin.plans.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.plans.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">package_2</span>
                    <span class="font-medium">Plans</span>
                </a>

                <a href="{{ route('admin.promo-codes.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.promo-codes.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">local_offer</span>
                    <span class="font-medium">Promo Codes</span>
                </a>
                @endif

                @if($canManageTenants)
                <a href="{{ route('admin.support.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.support.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">support_agent</span>
                    <span class="font-medium">Support</span>
                    @php $openTickets = \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count(); @endphp
                    @if($openTickets > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full">{{ $openTickets }}</span>
                    @endif
                </a>
                @endif

                @if($canManagePayments)
                <div class="px-4 mt-6 mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Analytics</span>
                </div>

                <a href="{{ route('admin.revenue.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.revenue.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">payments</span>
                    <span class="font-medium">Revenue</span>
                </a>

                <a href="{{ route('admin.analytics.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.analytics.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">analytics</span>
                    <span class="font-medium">Advanced Analytics</span>
                </a>
                @endif

                @if($isSuperAdmin)
                <a href="{{ route('admin.stats.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.stats.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">bar_chart</span>
                    <span class="font-medium">Platform Stats</span>
                </a>

                <a href="{{ route('admin.alerts.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.alerts.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">notifications_active</span>
                    <span class="font-medium">Alerts</span>
                    @php 
                        $expiringCount = \App\Models\Subscription::where('status', 'active')
                            ->where('expires_at', '<=', now()->addDays(7))
                            ->where('expires_at', '>', now())
                            ->count();
                    @endphp
                    @if($expiringCount > 0)
                    <span class="ml-auto px-2 py-0.5 bg-yellow-500 text-black text-xs font-bold rounded-full">{{ $expiringCount }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.broadcast.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.broadcast.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">campaign</span>
                    <span class="font-medium">Broadcast</span>
                </a>
                @endif

                <div class="px-4 mt-6 mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">System</span>
                </div>

                @if($isSuperAdmin)
                <a href="{{ route('admin.security.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.security.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">security</span>
                    <span class="font-medium">Security Center</span>
                    @php $unresolvedAlerts = \App\Models\SecurityAlert::unresolved()->count(); @endphp
                    @if($unresolvedAlerts > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full">{{ $unresolvedAlerts }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.admins.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.admins.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
                    <span class="font-medium">Admin Management</span>
                </a>
                @endif

                <a href="{{ route('admin.activity-logs.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.activity-logs.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">history</span>
                    <span class="font-medium">Activity Logs</span>
                </a>

                @if($isSuperAdmin)
                <a href="{{ route('admin.system-health.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.system-health.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">monitor_heart</span>
                    <span class="font-medium">System Health</span>
                </a>

                <a href="{{ route('admin.system-alerts.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.system-alerts.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">notification_important</span>
                    <span class="font-medium">System Alerts</span>
                    @php
                        $activeAlertRules = count(array_filter(Cache::get('system_alert_rules', []), fn($r) => $r['enabled'] ?? false));
                        $alertHistory = Cache::get('system_alert_history', []);
                        $alertsToday = count(array_filter($alertHistory, fn($h) => strtotime($h['created_at'] ?? 0) >= strtotime('today')));
                    @endphp
                    @if($alertsToday > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500/20 text-red-500 text-[10px] font-black rounded-full">{{ $alertsToday }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.user-analytics.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.user-analytics.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">person_search</span>
                    <span class="font-medium">User Analytics</span>
                </a>

                <a href="{{ route('admin.devices.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.devices.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">devices</span>
                    <span class="font-medium">Devices</span>
                    @php 
                        $orphanedDevices = \App\Models\WhatsAppDevice::whereNull('user_id')->count() + \App\Models\InstagramAccount::whereNull('user_id')->count();
                        $disconnectedDevices = \App\Models\WhatsAppDevice::where('status', '!=', \App\Models\WhatsAppDevice::STATUS_CONNECTED)->count();
                    @endphp
                    @if($orphanedDevices > 0 || $disconnectedDevices > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500/20 text-red-500 text-[10px] font-black rounded-full">{{ $orphanedDevices + $disconnectedDevices }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.schedule.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.schedule.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">schedule</span>
                    <span class="font-medium">Schedule</span>
                </a>

                <a href="{{ route('admin.ai-providers.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.ai-providers.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">psychology</span>
                    <span class="font-medium">AI Providers</span>
                    @php 
                        $providerService = app(\App\Services\AiProviderService::class);
                        $providers = $providerService->getProviderStatus();
                        $unhealthy = collect($providers)->filter(fn($p) => !$p['healthy'])->count();
                    @endphp
                    @if($unhealthy > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500/20 text-red-500 text-[10px] font-black rounded-full">{{ $unhealthy }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.api-monitor.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.api-monitor.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">speed</span>
                    <span class="font-medium">API Monitor</span>
                </a>

                <a href="{{ route('admin.webhook-logs.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.webhook-logs.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">webhook</span>
                    <span class="font-medium">Webhook Logs</span>
                    @php $failedWebhooks = \App\Models\WebhookLog::where('status', 'failed')->count(); @endphp
                    @if($failedWebhooks > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500/20 text-red-500 text-[10px] font-black rounded-full">{{ $failedWebhooks }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.failed-jobs.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.failed-jobs.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">error</span>
                    <span class="font-medium">Failed Jobs</span>
                    @php $failedCount = DB::table('failed_jobs')->count(); @endphp
                    @if($failedCount > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500/20 text-red-500 text-[10px] font-black rounded-full">{{ $failedCount }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.logs.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.logs.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">terminal</span>
                    <span class="font-medium">System Logs</span>
                </a>

                <a href="{{ route('admin.backups.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.backups.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">database</span>
                    <span class="font-medium">System Backups</span>
                </a>

                <a href="{{ route('admin.settings.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">settings</span>
                    <span class="font-medium">Settings</span>
                </a>

                <a href="{{ route('admin.maintenance-mode.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.maintenance-mode.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">construction</span>
                    <span class="font-medium">Maintenance Mode</span>
                    @php 
                        $maintenanceEnabled = \App\Models\SystemSetting::get('maintenance_mode_enabled', false);
                    @endphp
                    @if($maintenanceEnabled)
                    <span class="ml-auto px-2 py-0.5 bg-red-500 text-white text-[10px] font-black rounded-full">ON</span>
                    @endif
                </a>

                <a href="{{ route('admin.data-transfer.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.data-transfer.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">sync_alt</span>
                    <span class="font-medium">Data Transfer</span>
                </a>

                <a href="{{ route('admin.templates.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.templates.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">description</span>
                    <span class="font-medium">Message Templates</span>
                </a>

                <a href="{{ route('admin.query-runner.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.query-runner.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">database</span>
                    <span class="font-medium">Query Runner</span>
                </a>

                <div class="px-4 mt-6 mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Operations</span>
                </div>

                <a href="{{ route('admin.refunds.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.refunds.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">receipt_long</span>
                    <span class="font-medium">Refunds</span>
                    @php $pendingRefunds = \App\Models\Refund::where('status', 'pending')->count(); @endphp
                    @if($pendingRefunds > 0)
                    <span class="ml-auto px-2 py-0.5 bg-yellow-500 text-black text-xs font-bold rounded-full">{{ $pendingRefunds }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.feedback.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.feedback.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">rate_review</span>
                    <span class="font-medium">Feedback</span>
                </a>

                <a href="{{ route('admin.bulk.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.bulk.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">bolt</span>
                    <span class="font-medium">Bulk Actions</span>
                </a>

                <div class="px-4 mt-6 mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Monitoring</span>
                </div>

                <a href="{{ route('admin.email-logs.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.email-logs.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">mail</span>
                    <span class="font-medium">Email Logs</span>
                </a>

                <a href="{{ route('admin.api-usage.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.api-usage.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">api</span>
                    <span class="font-medium">API Usage</span>
                </a>

                <a href="{{ route('admin.webhook-logs.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.webhook-logs.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">webhook</span>
                    <span class="font-medium">Webhook Logs</span>
                    @php 
                        $failedWebhooks = 0;
                        try {
                            $failedWebhooks = DB::table('webhook_logs')->where('status', 'failed')->count();
                        } catch (\Exception $e) {}
                    @endphp
                    @if($failedWebhooks > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500/20 text-red-500 text-[10px] font-black rounded-full">{{ $failedWebhooks }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.feature-flags.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.feature-flags.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">flag</span>
                    <span class="font-medium">Feature Flags</span>
                </a>

                <a href="{{ route('admin.qa.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.qa.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">bug_report</span>
                    <span class="font-medium">QA Testing</span>
                </a>

                <div class="px-4 mt-6 mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tools</span>
                </div>

                <a href="{{ route('admin.query-runner.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.query-runner.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">database</span>
                    <span class="font-medium">Query Runner</span>
                </a>

                <a href="{{ route('admin.templates.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.templates.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">description</span>
                    <span class="font-medium">Message Templates</span>
                </a>
                @endif
            </nav>

            <!-- Admin Profile -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full {{ $isSuperAdmin ? 'bg-red-500/20 text-red-500' : ($isFinance ? 'bg-green-500/20 text-green-500' : 'bg-blue-500/20 text-blue-500') }} flex items-center justify-center font-bold">
                        {{ strtoupper(substr($adminUser->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm truncate">{{ $adminUser->name }}</div>
                        <div class="text-xs {{ $isSuperAdmin ? 'text-red-400' : ($isFinance ? 'text-green-400' : 'text-blue-400') }}">{{ $adminUser->role_label }}</div>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg text-sm text-slate-300 transition">
                        <span class="material-symbols-outlined text-lg">logout</span>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-surface-dark/80 backdrop-blur-lg border-b border-slate-800 sticky top-0 z-10">
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold">@yield('page_title', 'Dashboard')</h1>
                        @if($isSuperAdmin)
                            <span class="px-2 py-0.5 bg-red-500/20 text-red-400 text-xs font-semibold rounded border border-red-500/30">SUPERADMIN</span>
                        @elseif($isFinance)
                            <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs font-semibold rounded border border-green-500/30">FINANCE</span>
                        @elseif($isSupport)
                            <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 text-xs font-semibold rounded border border-blue-500/30">SUPPORT</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        {{-- Command Palette Trigger --}}
                        <button @click="$dispatch('keydown', {key: 'k', metaKey: true})" 
                                class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg text-sm text-slate-400 hover:text-white transition">
                            <span class="material-symbols-outlined text-lg">search</span>
                            <span>Search</span>
                            <kbd class="px-1.5 py-0.5 bg-slate-700 rounded text-xs">⌘K</kbd>
                        </button>
                        
                        {{-- Notifications --}}
                        @include('admin.partials.notifications')
                        
                        <a href="/" target="_blank" class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
                            <span class="material-symbols-outlined text-lg">open_in_new</span>
                            View Website
                        </a>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-6">
                @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 flex items-center gap-3">
                    <span class="material-symbols-outlined">check_circle</span>
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 flex items-center gap-3">
                    <span class="material-symbols-outlined">error</span>
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

{{-- Keyboard Shortcuts Help Modal --}}
<div x-data="{ showShortcuts: false }" @keydown.window.shift.?="showShortcuts = true">
    <div x-show="showShortcuts" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/80" @click="showShortcuts = false"></div>
        <div class="relative bg-surface-dark rounded-2xl border border-slate-700 p-6 max-w-md w-full">
            <h3 class="font-bold text-lg mb-4">Keyboard Shortcuts</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Command Palette</span>
                    <kbd class="px-2 py-1 bg-slate-700 rounded">⌘K</kbd>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Go to Dashboard</span>
                    <kbd class="px-2 py-1 bg-slate-700 rounded">G D</kbd>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Go to Users</span>
                    <kbd class="px-2 py-1 bg-slate-700 rounded">G U</kbd>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Go to Payments</span>
                    <kbd class="px-2 py-1 bg-slate-700 rounded">G P</kbd>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Help</span>
                    <kbd class="px-2 py-1 bg-slate-700 rounded">Shift ?</kbd>
                </div>
            </div>
            <button @click="showShortcuts = false" class="mt-4 w-full px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm">Close</button>
        </div>
    </div>
</div>

{{-- Toast Notifications Container --}}
<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

<script>
// Toast notification helper
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const colors = type === 'success' ? 'bg-green-500/20 border-green-500/50 text-green-300' : 
                   type === 'error' ? 'bg-red-500/20 border-red-500/50 text-red-300' : 
                   'bg-blue-500/20 border-blue-500/50 text-blue-300';
    
    toast.className = `px-4 py-3 rounded-xl border ${colors} flex items-center gap-2 animate-fade-in`;
    toast.innerHTML = `
        <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</span>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Only if not in input
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    
    // G + key for navigation
    if (e.key === 'g' || e.key === 'G') {
        document.addEventListener('keydown', function nav(e2) {
            document.removeEventListener('keydown', nav);
            switch(e2.key.toLowerCase()) {
                case 'd': window.location.href = '{{ route("admin.dashboard") }}'; break;
                case 'u': window.location.href = '{{ route("admin.users.index") }}'; break;
                case 'p': window.location.href = '{{ route("admin.payments.index") }}'; break;
                case 's': window.location.href = '{{ route("admin.support.index") }}'; break;
            }
        }, { once: true });
    }
});
</script>

@stack('scripts')

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>

</body>
</html>
