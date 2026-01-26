<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Subscription</title>
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
                        "background-dark": "#101622",
                        "surface-dark": "#1a2230",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-dark text-white font-display antialiased min-h-screen">
    
    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 bg-background-dark/80 backdrop-blur-sm border-b border-slate-700">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <span class="text-2xl font-black text-primary">REPLY</span>
                    <span class="text-2xl font-black text-white">AI</span>
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-1 text-sm text-slate-400 hover:text-white transition">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Dashboard
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-16 px-4">
        <div class="max-w-4xl mx-auto">
            
            <h1 class="text-2xl font-bold mb-8">Subscription Anda</h1>

            <!-- Current Plan Card -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700 mb-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="material-symbols-outlined text-primary text-3xl">workspace_premium</span>
                            <div>
                                <h2 class="text-2xl font-bold">{{ $plan->name ?? 'Gratis' }}</h2>
                                <p class="text-slate-400 text-sm">{{ $plan->description ?? 'Paket dasar' }}</p>
                            </div>
                        </div>
                        
                        @if($subscription)
                        <div class="mt-4 flex flex-wrap gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                <span>Status: <strong class="text-green-400">Aktif</strong></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-lg">calendar_today</span>
                                <span>Berlaku hingga: <strong>{{ $subscription->expires_at->format('d M Y') }}</strong></span>
                            </div>
                        </div>
                        @else
                        <div class="mt-4">
                            <span class="inline-flex items-center gap-2 px-3 py-1 bg-slate-700 rounded-full text-sm text-slate-300">
                                <span class="material-symbols-outlined text-lg">info</span>
                                Belum berlangganan
                            </span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('subscription.upgrade') }}" 
                           class="px-6 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition text-center">
                            {{ $subscription ? 'Upgrade Paket' : 'Pilih Paket' }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Usage Stats -->
            @if($subscription && isset($usageStats))
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700 mb-8">
                <h3 class="text-lg font-semibold mb-4">Penggunaan Bulan Ini</h3>
                
                <div class="grid md:grid-cols-2 gap-4">
                    @if(isset($usageStats['ai_messages']))
                    <div class="p-4 bg-background-dark rounded-xl">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-400 text-sm">Pesan AI</span>
                            <span class="font-semibold">
                                {{ number_format($usageStats['ai_messages']['used'] ?? 0) }} / 
                                {{ $usageStats['ai_messages']['limit'] == -1 ? '∞' : number_format($usageStats['ai_messages']['limit'] ?? 0) }}
                            </span>
                        </div>
                        @if(isset($usageStats['ai_messages']['limit']) && $usageStats['ai_messages']['limit'] != -1)
                        @php 
                            $percent = $usageStats['ai_messages']['limit'] > 0 
                                ? min(100, ($usageStats['ai_messages']['used'] / $usageStats['ai_messages']['limit']) * 100) 
                                : 0;
                        @endphp
                        <div class="w-full bg-slate-700 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if(isset($usageStats['contacts']))
                    <div class="p-4 bg-background-dark rounded-xl">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-400 text-sm">Kontak</span>
                            <span class="font-semibold">
                                {{ number_format($usageStats['contacts']['used'] ?? 0) }} / 
                                {{ $usageStats['contacts']['limit'] == -1 ? '∞' : number_format($usageStats['contacts']['limit'] ?? 0) }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Plan Features -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700">
                <h3 class="text-lg font-semibold mb-4">Fitur Paket {{ $plan->name ?? 'Gratis' }}</h3>
                
                <ul class="grid md:grid-cols-2 gap-3">
                    @php $features = $plan->features ?? []; @endphp
                    
                    @if(isset($features['ai_messages']))
                    <li class="flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                        {{ $features['ai_messages'] == -1 ? 'Unlimited' : number_format($features['ai_messages']) }} Pesan AI/bulan
                    </li>
                    @endif
                    
                    @if(isset($features['contacts']))
                    <li class="flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                        {{ $features['contacts'] == -1 ? 'Unlimited' : number_format($features['contacts']) }} Kontak
                    </li>
                    @endif
                    
                    @if(isset($features['wa_devices']))
                    <li class="flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                        {{ $features['wa_devices'] }} WhatsApp Device
                    </li>
                    @endif
                    
                    @if(isset($features['broadcasts']) && $features['broadcasts'] > 0)
                    <li class="flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                        {{ $features['broadcasts'] == -1 ? 'Unlimited' : number_format($features['broadcasts']) }} Broadcast/bulan
                    </li>
                    @endif
                    
                    @if(isset($features['sequences']) && $features['sequences'] > 0)
                    <li class="flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                        Drip Sequences
                    </li>
                    @endif
                    
                    @if(isset($features['web_widgets']) && $features['web_widgets'] > 0)
                    <li class="flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                        Web Chat Widget
                    </li>
                    @endif
                </ul>
            </div>

        </div>
    </main>

</body>
</html>
