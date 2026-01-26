@extends('admin.layouts.app')

@section('title', 'SaaS Business Intel')
@section('page_title', 'SaaS Business Intel')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-black italic tracking-tighter text-white">REVENUE INTELLIGENCE 💎</h2>
        <p class="text-sm text-slate-400">Analisis mendalam tentang kesehatan finansial dan pertumbuhan platform.</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="px-4 py-2 bg-primary/10 rounded-xl border border-primary/20 flex items-center gap-2">
            <span class="size-2 rounded-full bg-primary animate-pulse"></span>
            <span class="text-xs font-bold text-primary uppercase tracking-widest">Live Data</span>
        </div>
    </div>
</div>

<!-- Elite Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- MRR Card -->
    <div class="bg-surface-dark rounded-[2.5rem] p-8 border border-slate-800 shadow-2xl relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 size-32 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-all duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Monthly Recurring</span>
                <div class="size-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-xl">payments</span>
                </div>
            </div>
            <h3 class="text-3xl font-black text-white mb-2">Rp {{ number_format($metrics['mrr'], 0, ',', '.') }}</h3>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold {{ $metrics['growth']['percentage'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $metrics['growth']['percentage'] >= 0 ? '+' : '' }}{{ $metrics['growth']['percentage'] }}%
                </span>
                <span class="text-[10px] text-slate-600 font-medium uppercase italic">vs Last Month</span>
            </div>
        </div>
    </div>

    <!-- Churn Card -->
    <div class="bg-surface-dark rounded-[2.5rem] p-8 border border-slate-800 shadow-2xl relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 size-32 bg-red-500/5 rounded-full blur-3xl group-hover:bg-red-500/10 transition-all duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Churn Rate</span>
                <div class="size-10 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-xl">person_remove</span>
                </div>
            </div>
            <h3 class="text-3xl font-black text-white mb-2">{{ number_format($metrics['churn'], 1) }}%</h3>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="text-[10px] font-bold uppercase tracking-widest italic">Last 30 Days</span>
            </div>
        </div>
    </div>

    <!-- LTV Card -->
    <div class="bg-surface-dark rounded-[2.5rem] p-8 border border-slate-800 shadow-2xl relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 size-32 bg-yellow-500/5 rounded-full blur-3xl group-hover:bg-yellow-500/10 transition-all duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Customer LTV</span>
                <div class="size-10 rounded-2xl bg-yellow-500/10 flex items-center justify-center text-yellow-500 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-xl">diamond</span>
                </div>
            </div>
            <h3 class="text-3xl font-black text-white mb-2">Rp {{ number_format($metrics['ltv'], 0, ',', '.') }}</h3>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="text-[10px] font-medium uppercase italic">Lifetime Value Projection</span>
            </div>
        </div>
    </div>

    <!-- ARPU Card -->
    <div class="bg-surface-dark rounded-[2.5rem] p-8 border border-slate-800 shadow-2xl relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 size-32 bg-purple-500/5 rounded-full blur-3xl group-hover:bg-purple-500/10 transition-all duration-500"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Avg Revenue (ARPU)</span>
                <div class="size-10 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-500 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-xl">trending_up</span>
                </div>
            </div>
            <h3 class="text-3xl font-black text-white mb-2">Rp {{ number_format($metrics['arpu'], 0, ',', '.') }}</h3>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="text-[10px] font-medium uppercase italic">Revenue Per Account</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Revenue Breakdown -->
    <div class="lg:col-span-2 bg-surface-dark rounded-[3rem] p-10 border border-slate-800 shadow-3xl">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h3 class="text-xl font-bold text-white mb-1">Growth Comparison</h3>
                <p class="text-xs text-slate-500">Perbandingan pendapatan Bulan Ini vs Bulan Lalu.</p>
            </div>
            <div class="flex gap-4">
                <div class="flex items-center gap-2">
                    <span class="size-3 rounded-full bg-primary/20 border border-primary/40"></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Last Month</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="size-3 rounded-full bg-primary shadow-lg shadow-primary/50"></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">This Month</span>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-slate-300">Target Monthly (Example: 50M)</span>
                    <span class="text-xs font-black text-primary">{{ round(($metrics['growth']['this_month'] / 50000000) * 100, 1) }}% Reach</span>
                </div>
                <div class="h-4 bg-background-dark rounded-full overflow-hidden border border-slate-800 p-0.5">
                    <div class="h-full bg-gradient-to-r from-primary/50 to-primary rounded-full shadow-[0_0_15px_rgba(19,91,236,0.5)]" style="width: {{ min(100, ($metrics['growth']['this_month'] / 50000000) * 100) }}%"></div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-10 pt-6 border-t border-slate-800/50">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">THIS MONTH TOTAL</p>
                    <p class="text-2xl font-black text-white">Rp {{ number_format($metrics['growth']['this_month'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">PREVIOUS MONTH</p>
                    <p class="text-2xl font-black text-slate-400">Rp {{ number_format($metrics['growth']['last_month'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SaaS Health Advice -->
    <div class="bg-primary/5 rounded-[3rem] p-10 border border-primary/10 flex flex-col justify-center text-center relative overflow-hidden group">
        <div class="absolute -bottom-20 -left-20 size-64 bg-primary/10 rounded-full blur-[80px]"></div>
        <div class="relative">
            <div class="size-20 rounded-[2rem] bg-primary shadow-2xl shadow-primary/50 mx-auto flex items-center justify-center text-white mb-6 group-hover:rotate-12 transition-transform duration-500">
                <span class="material-symbols-outlined text-4xl">psychology</span>
            </div>
            <h4 class="text-xl font-black text-white mb-4">SaaS Performance Tip</h4>
            
            @if($metrics['churn'] > 5)
            <p class="text-sm text-slate-400 italic">"Sistem mendeteksi <b>Churn Rate tinggi</b>. Pertimbangkan untuk memberikan penawaran khusus atau survei kepada pengguna yang membatalkan langganan."</p>
            @elseif($metrics['growth']['percentage'] > 10)
            <p class="text-sm text-slate-400 italic">"Platform sedang <b>tumbuh pesat!</b> Pastikan server dan infrastruktur siap menangani lonjakan beban pengguna baru."</p>
            @else
            <p class="text-sm text-slate-400 italic">"Pertumbuhan stabil. Inilah saatnya untuk fokus pada <b>Upselling</b> fitur VIP kepada pengguna paket standar."</p>
            @endif

            <button class="mt-8 px-8 py-3 bg-white text-primary rounded-2xl font-black text-xs hover:bg-primary hover:text-white transition shadow-xl">
                RUN FULL AUDIT
            </button>
        </div>
    </div>
</div>

@endsection
