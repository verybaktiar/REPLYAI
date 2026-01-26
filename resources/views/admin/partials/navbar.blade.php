<nav class="bg-surface-dark border-b border-slate-700 px-6 py-4 mb-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                <span class="text-xl font-black text-primary">REPLY</span>
                <span class="text-xl font-black text-white">AI</span>
            </a>
            <div class="h-6 w-px bg-slate-700"></div>
            <div class="flex items-center gap-2 px-3 py-1 bg-background-dark rounded-full border border-slate-700">
                <span class="material-symbols-outlined text-yellow-500 text-sm">shield</span>
                <span class="text-xs font-semibold text-slate-300">Admin Panel</span>
            </div>

            <!-- Nav Menu -->
            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-background-dark transition {{ request()->routeIs('admin.dashboard') ? 'bg-background-dark text-primary' : 'text-slate-300' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-background-dark transition {{ request()->routeIs('admin.users.*') ? 'bg-background-dark text-primary' : 'text-slate-300' }}">
                    Users
                </a>
                <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-background-dark transition {{ request()->routeIs('admin.payments.*') ? 'bg-background-dark text-primary' : 'text-slate-300' }}">
                    Payments
                </a>
                <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-background-dark transition {{ request()->routeIs('admin.plans.*') ? 'bg-background-dark text-primary' : 'text-slate-300' }}">
                    Plans
                </a>
                <a href="{{ route('admin.promo-codes.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-background-dark transition {{ request()->routeIs('admin.promo-codes.*') ? 'bg-background-dark text-primary' : 'text-slate-300' }}">
                    Promos
                </a>
                <a href="{{ route('admin.support.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-background-dark transition {{ request()->routeIs('admin.support.*') ? 'bg-background-dark text-primary' : 'text-slate-300' }}">
                    Support
                </a>
                <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-background-dark transition {{ request()->routeIs('admin.activity-logs.*') ? 'bg-background-dark text-primary' : 'text-slate-300' }}">
                    Logs
                </a>
                <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium hover:bg-background-dark transition {{ request()->routeIs('admin.settings.*') ? 'bg-background-dark text-primary' : 'text-slate-300' }}">
                    Settings
                </a>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-400 hidden sm:block">{{ Auth::guard('admin')->user()->name }}</span>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-background-dark hover:bg-slate-800 rounded-xl border border-slate-700 transition text-sm">
                    <span class="material-symbols-outlined text-lg">logout</span>
                    <span class="hidden sm:inline">Logout</span>
                </button>
            </form>
        </div>
    </div>
</nav>
