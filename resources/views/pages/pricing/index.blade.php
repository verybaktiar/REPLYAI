<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Pilih Paket - ReplyAI</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-dark": "#101622",
                        "surface-dark": "#1a2230",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-dark text-white font-display antialiased">

    <!-- Navbar Simple -->
    <nav class="bg-surface-dark border-b border-slate-700">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center gap-2 text-2xl font-black">
                    <span class="text-primary">REPLY</span>
                    <span class="text-white">AI</span>
                </a>
                @auth
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg transition">
                        Logout
                    </button>
                </form>
                @else
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-primary hover:bg-primary/90 rounded-lg transition">Register</a>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <div class="min-h-screen py-20 px-4 relative overflow-hidden">
        <!-- Background Glow -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-primary/10 blur-[120px] -z-10"></div>

        <div class="max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-20">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/50 border border-slate-700 mb-6">
                    <span class="material-symbols-outlined text-yellow-500 text-sm filled">stars</span>
                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">Investasi yang Terjangkau</span>
                </div>
                <h1 class="text-5xl md:text-6xl font-black text-white mb-6 tracking-tight">
                    Investasi yang <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-blue-400">Terjangkau</span>
                </h1>
                <p class="text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
                    Pilih paket yang sesuai dengan skala bisnis Anda. Upgrade kapan saja.
                </p>
            </div>

            <!-- Pricing Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-start">
                @foreach($plans as $plan)
                @if(!$plan->is_trial && $plan->slug !== 'gratis')
                @php 
                    $isPro = $plan->slug === 'pro';
                    $hasOriginal = $plan->price_monthly_original > 0;
                    $features = $plan->features ?? [];
                @endphp
                
                <div class="group relative flex flex-col h-full bg-[#192233]/40 backdrop-blur-sm rounded-[32px] border {{ $isPro ? 'border-primary shadow-[0_0_40px_rgba(19,91,236,0.15)] scale-105 z-10' : 'border-slate-800' }} p-8 transition-all hover:translate-y-[-8px]">
                    
                    @if($isPro)
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1.5 bg-primary text-white text-[10px] font-black rounded-full uppercase tracking-widest shadow-lg">
                        Paling Laris
                    </div>
                    @endif

                    <!-- Header -->
                    <div class="mb-8">
                        <h3 class="text-2xl font-black text-white mb-2">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ $plan->description }}</p>
                    </div>

                    <!-- Price -->
                    <div class="mb-8">
                        @php
                            $discountPercent = 0;
                            if ($plan->price_monthly_original > $plan->price_monthly && $plan->price_monthly_original > 0) {
                                $discountPercent = round((($plan->price_monthly_original - $plan->price_monthly) / $plan->price_monthly_original) * 100);
                            }
                        @endphp

                        <div class="flex items-center gap-2 mb-2 min-h-[24px]">
                            @if($plan->price_monthly_original_display)
                                <div class="text-red-400/80 text-sm line-through font-medium">
                                    {{ $plan->price_monthly_original_display }}
                                </div>
                            @elseif($hasOriginal)
                                <div class="text-red-400/80 text-sm line-through font-medium">
                                    Rp {{ number_format($plan->price_monthly_original, 0, ',', '.') }}
                                </div>
                            @endif

                            @if($discountPercent > 0)
                                <span class="bg-green-500/10 text-green-400 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider border border-green-500/20">
                                    Hemat {{ $discountPercent }}%
                                </span>
                            @endif
                        </div>

                        <div class="flex items-baseline gap-2">
                            @if($plan->price_monthly_display)
                                <span class="text-5xl font-black text-white tracking-tight">{{ $plan->price_monthly_display }}</span>
                            @else
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xl font-bold text-white">Rp</span>
                                    <span class="text-5xl font-black text-white tracking-tight">{{ $plan->price_monthly > 0 ? number_format($plan->price_monthly, 0, ',', '.') : $plan->name }}</span>
                                </div>
                            @endif
                            
                            @if($plan->price_monthly > 0)
                                <span class="text-slate-500 text-sm font-bold">/bulan</span>
                            @endif
                        </div>
                    </div>

                    <div class="h-px bg-slate-800 w-full mb-8"></div>

                    <!-- Features -->
                    <ul class="flex-1 space-y-4 mb-10 text-xs">
                        @if(!empty($plan->features_list))
                            @foreach($plan->features_list as $feature)
                                <li class="flex items-start gap-3 text-slate-300">
                                    <span class="material-symbols-outlined text-green-500 text-lg leading-none">check_circle</span>
                                    <span>{!! $feature !!}</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>

                    <!-- CTA -->
                    @if($plan->slug === 'enterprise')
                        <a href="https://wa.me/6285168842886" target="_blank"
                           class="block w-full py-4 text-center font-black rounded-2xl border border-slate-700 hover:bg-slate-800 text-white transition-all text-sm uppercase tracking-widest">
                            Hubungi Tim Sales
                        </a>
                    @elseif($plan->slug === 'custom')
                        <a href="https://wa.me/6285168842886" target="_blank"
                           class="block w-full py-4 text-center font-black rounded-2xl border border-slate-700 hover:bg-slate-800 text-white transition-all text-sm uppercase tracking-widest">
                            Konsultasi Custom
                        </a>
                    @else
                        <a href="{{ route('checkout.index', ['plan' => $plan->slug]) }}" 
                           class="block w-full py-4 text-center font-black rounded-2xl transition-all {{ $isPro ? 'bg-gradient-to-r from-[#8b5cf6] to-[#d946ef] text-white shadow-lg shadow-purple-500/20 hover:scale-105 active:scale-95' : 'bg-slate-800/80 hover:bg-slate-700 text-white' }} text-sm uppercase tracking-widest">
                            Mulai Paket {{ $plan->name }}
                        </a>
                    @endif
                </div>
                @endif
                @endforeach

                <!-- Custom/Enterprise Card -->
                <div class="group relative flex flex-col h-full bg-[#192233]/40 backdrop-blur-sm rounded-[32px] border border-slate-800 p-8 transition-all hover:translate-y-[-8px]">
                    <div class="mb-8">
                        <h3 class="text-2xl font-black text-white mb-2">Custom</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Solusi khusus sesuai kebutuhan bisnis</p>
                    </div>

                    <div class="mb-8">
                        <div class="flex items-baseline gap-2">
                            <span class="text-lg font-bold text-white">Rp</span>
                            <span class="text-4xl font-black text-white">Custom</span>
                        </div>
                    </div>

                    <div class="h-px bg-slate-800 w-full mb-8"></div>

                    <ul class="flex-1 space-y-4 mb-10 text-xs">
                        <li class="flex items-start gap-3 text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg leading-none">check_circle</span>
                            <span>Fitur Sesuai Permintaan</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg leading-none">check_circle</span>
                            <span>White Label (Tanpa Branding ReplyAI)</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg leading-none">check_circle</span>
                            <span>Server Khusus</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg leading-none">check_circle</span>
                            <span>Integrasi Sistem Khusus</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg leading-none">check_circle</span>
                            <span>AI Dilatih Data Internal</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg leading-none">check_circle</span>
                            <span>Dukungan Teknis Dedicated</span>
                        </li>
                    </ul>

                    <a href="https://wa.me/6285168842886" target="_blank"
                       class="block w-full py-4 text-center font-black rounded-2xl border border-slate-700 hover:bg-slate-800 text-white transition-all text-sm uppercase tracking-widest">
                        Konsultasi Custom
                    </a>
                </div>
            </div>

            <!-- Trust Badge -->
            <div class="mt-20 text-center">
                <p class="text-slate-500 text-sm font-bold uppercase tracking-widest mb-8">Dipercaya oleh ribuan pebisnis</p>
                <div class="flex flex-wrap justify-center items-center gap-12 opacity-30 grayscale hover:grayscale-0 transition-all duration-500">
                    {{-- Placeholder untuk logo-logo klien --}}
                    <span class="text-2xl font-black">TOKOHP</span>
                    <span class="text-2xl font-black">KLINIKKU</span>
                    <span class="text-2xl font-black">STOREAI</span>
                    <span class="text-2xl font-black">PROPERTYX</span>
                </div>
            </div>
        </div>
    </div>

        </div>
    </div>

</body>
</html>