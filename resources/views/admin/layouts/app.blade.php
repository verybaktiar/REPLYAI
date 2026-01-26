<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
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
    
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-surface-dark border-r border-slate-800 flex flex-col fixed h-screen">
            <!-- Logo -->
            <div class="p-5 border-b border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="text-2xl font-black text-primary">REPLY</span>
                    <span class="text-2xl font-black text-white">AI</span>
                </a>
                <div class="flex items-center gap-2 mt-3 px-3 py-1.5 bg-yellow-500/10 rounded-lg border border-yellow-500/20">
                    <span class="material-symbols-outlined text-yellow-500 text-sm">shield</span>
                    <span class="text-xs font-semibold text-yellow-400">Super Admin Panel</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-4 overflow-y-auto">
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

                <a href="{{ route('admin.payments.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.payments.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">payments</span>
                    <span class="font-medium">Payments</span>
                    @php $pendingCount = \App\Models\Payment::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                    <span class="ml-auto px-2 py-0.5 bg-orange-500 text-white text-xs font-bold rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>

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

                <a href="{{ route('admin.support.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.support.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">support_agent</span>
                    <span class="font-medium">Support</span>
                    @php $openTickets = \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count(); @endphp
                    @if($openTickets > 0)
                    <span class="ml-auto px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full">{{ $openTickets }}</span>
                    @endif
                </a>

                <div class="px-4 mt-6 mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Analytics</span>
                </div>

                <a href="{{ route('admin.revenue.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.revenue.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">payments</span>
                    <span class="font-medium">Revenue</span>
                </a>

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

                <div class="px-4 mt-6 mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">System</span>
                </div>

                <a href="{{ route('admin.activity-logs.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.activity-logs.*') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">history</span>
                    <span class="font-medium">Activity Logs</span>
                </a>

                <a href="{{ route('admin.system-health') }}" 
                   class="sidebar-link flex items-center gap-3 px-5 py-3 mx-2 rounded-lg {{ request()->routeIs('admin.system-health') ? 'active text-primary' : 'text-slate-300' }}">
                    <span class="material-symbols-outlined text-xl">monitor_heart</span>
                    <span class="font-medium">System Health</span>
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
            </nav>

            <!-- Admin Profile -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                        {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm truncate">{{ Auth::guard('admin')->user()->name }}</div>
                        <div class="text-xs text-slate-500">Super Admin</div>
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
                    <h1 class="text-xl font-bold">@yield('page_title', 'Dashboard')</h1>
                    <div class="flex items-center gap-4">
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

</body>
</html>
