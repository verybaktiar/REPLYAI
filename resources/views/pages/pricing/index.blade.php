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

    <div class="min-h-screen py-12 px-4">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-black text-white mb-4">
                    Pilih Paket yang Sesuai
                </h1>
                <p class="text-xl text-slate-400">
                    Untuk melanjutkan, silakan pilih paket langganan
                </p>
            </div>

            <!-- Pending Invoice Banner -->
            @if(isset($pendingPayment) && $pendingPayment)
            <div class="max-w-3xl mx-auto mb-8 p-5 rounded-2xl bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/50">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-yellow-500/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-yellow-400 text-2xl">receipt_long</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-yellow-300">Anda memiliki invoice yang menunggu pembayaran</h3>
                            <p class="text-slate-400 text-sm">
                                Invoice <span class="font-mono font-bold text-white">{{ $pendingPayment->invoice_number }}</span> 
                                • Paket {{ $pendingPayment->plan->name ?? 'Unknown' }}
                                • Rp {{ number_format($pendingPayment->total, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('checkout.payment', $pendingPayment->invoice_number) }}" 
                       class="w-full md:w-auto px-6 py-3 bg-yellow-500 hover:bg-yellow-400 text-black font-bold rounded-xl transition text-center">
                        Lanjutkan Bayar →
                    </a>
                </div>
            </div>
            @endif

            @if(session('warning'))
            <div class="max-w-2xl mx-auto mb-8 p-4 rounded-xl bg-yellow-500/20 border border-yellow-500/50 text-yellow-300">
                {{ session('warning') }}
            </div>
            @endif

            <!-- Pricing Cards -->
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                @foreach($plans as $plan)
                @if(!$plan->is_trial && $plan->slug !== 'gratis')
                <div class="bg-surface-dark rounded-2xl p-8 border-2 {{ $plan->slug === 'pro' ? 'border-primary' : 'border-slate-700' }} relative">
                    
                    @if($plan->slug === 'pro')
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="px-4 py-1 bg-primary text-white text-sm font-bold rounded-full">
                            🔥 Paling Populer
                        </span>
                    </div>
                    @endif

                    <!-- Plan Name -->
                    <h3 class="text-2xl font-bold text-white mb-2">{{ $plan->name }}</h3>
                    <p class="text-slate-400 text-sm mb-6">{{ $plan->description }}</p>

                    <!-- Price -->
                    <div class="mb-6">
                        <div class="flex items-baseline gap-2">
                            @php
                                $priceFormatted = $plan->price_monthly >= 1000000 
                                    ? number_format($plan->price_monthly / 1000000, 1) . 'jt'
                                    : number_format($plan->price_monthly / 1000, 0) . 'rb';
                            @endphp
                            <span class="text-4xl font-black text-white">Rp {{ $priceFormatted }}</span>
                            <span class="text-slate-400">/bulan</span>
                        </div>
                        @if($plan->price_yearly > 0)
                        @php
                            $yearlyFormatted = $plan->price_yearly >= 1000000 
                                ? number_format($plan->price_yearly / 1000000, 1) . 'jt'
                                : number_format($plan->price_yearly / 1000, 0) . 'rb';
                            $savingsPercent = round((1 - ($plan->price_yearly / ($plan->price_monthly * 12))) * 100);
                        @endphp
                        <p class="text-sm text-slate-500 mt-2">
                            atau Rp {{ $yearlyFormatted }}/tahun (hemat {{ $savingsPercent }}%)
                        </p>
                        @endif
                    </div>

                    <!-- Features -->
                    @php $features = $plan->features ?? []; @endphp
                    <ul class="space-y-3 mb-8">
                        <!-- Pesan AI -->
                        @if(isset($features['ai_messages']))
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ $features['ai_messages'] == -1 ? 'Unlimited' : number_format($features['ai_messages']) }} Pesan AI/bulan</span>
                        </li>
                        @endif

                        <!-- Kontak -->
                        @if(isset($features['contacts']))
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ $features['contacts'] == -1 ? 'Unlimited' : number_format($features['contacts']) }} Kontak</span>
                        </li>
                        @endif

                        <!-- WhatsApp Devices -->
                        @if(isset($features['wa_devices']))
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ $features['wa_devices'] == -1 ? 'Multi' : $features['wa_devices'] }} Perangkat WhatsApp</span>
                        </li>
                        @endif

                        <!-- Team Members -->
                        @if(isset($features['team_members']) && $features['team_members'] > 0)
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ $features['team_members'] == -1 ? 'Unlimited' : $features['team_members'] }} Admin</span>
                        </li>
                        @endif

                        <!-- Broadcast -->
                        @if(isset($features['broadcasts']) && $features['broadcasts'] > 0)
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ $features['broadcasts'] == -1 ? 'Unlimited' : number_format($features['broadcasts']) }} Broadcast/bulan</span>
                        </li>
                        @endif

                        <!-- Sequences -->
                        @if(isset($features['sequences']) && $features['sequences'] > 0)
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>Drip Sequences</span>
                        </li>
                        @endif

                        <!-- Web Widget -->
                        @if(isset($features['web_widgets']) && $features['web_widgets'] > 0)
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>Web Chat Widget</span>
                        </li>
                        @endif

                        <!-- Export Report -->
                        @if(!empty($features['analytics_export']))
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>Export Laporan</span>
                        </li>
                        @endif

                        <!-- API Access -->
                        @if(!empty($features['api_access']))
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>Akses API</span>
                        </li>
                        @endif

                        <!-- Priority Support -->
                        @if(!empty($features['priority_support']))
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>Dukungan Prioritas</span>
                        </li>
                        @endif

                        <!-- Remove Branding -->
                        @if(!empty($features['remove_branding']))
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>Tanpa Branding ReplyAI</span>
                        </li>
                        @endif

                        <!-- SLA -->
                        @if(!empty($features['sla']))
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>SLA Guarantee</span>
                        </li>
                        @endif
                    </ul>

                    <!-- CTA Button -->
                    <a href="{{ route('checkout.index', ['plan' => $plan->slug]) }}" 
                       class="block w-full py-4 text-center font-bold rounded-xl transition {{ $plan->slug === 'pro' ? 'bg-primary hover:bg-primary/90 text-white' : 'bg-slate-800 hover:bg-slate-700 text-white border border-slate-700' }}">
                        Pilih {{ $plan->name }}
                    </a>
                </div>
                @endif
                @endforeach
            </div>

            <!-- Info Box -->
            <div class="max-w-2xl mx-auto bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 text-center">
                <p class="text-slate-300">
                    <strong>Butuh bantuan memilih paket?</strong><br>
                    Hubungi kami di <a href="mailto:support@replyai.com" class="text-primary hover:underline">support@replyai.com</a>
                    atau WhatsApp <a href="https://wa.me/6285168842886" class="text-primary hover:underline">+62 851-6884-2886</a>
                </p>
            </div>

        </div>
    </div>

</body>
</html>