<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Pilih Paket</title>
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
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <span class="text-2xl font-black text-primary">REPLY</span>
                    <span class="text-2xl font-black text-white">AI</span>
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-1 text-sm text-slate-400 hover:text-white transition">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-16 px-4">
        <div class="max-w-6xl mx-auto">
            
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-3xl font-black mb-4">Pilih Paket yang Tepat untuk Bisnis Anda</h1>
                <p class="text-slate-400 text-lg">
                    @if($feature)
                        Untuk menggunakan fitur <span class="text-primary font-semibold">{{ ucwords(str_replace('_', ' ', $feature)) }}</span>, silakan upgrade paket Anda
                    @else
                        Tingkatkan produktivitas dengan fitur premium ReplyAI
                    @endif
                </p>
                
                @if($currentPlan)
                <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary/10 border border-primary/30 rounded-full text-sm">
                    <span class="material-symbols-outlined text-primary text-lg">workspace_premium</span>
                    Paket Anda saat ini: <span class="font-bold text-primary">{{ $currentPlan->name }}</span>
                </div>
                @endif
            </div>

            <!-- Plans Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @foreach($plans as $plan)
                <div class="relative bg-surface-dark rounded-2xl p-6 border {{ $plan->slug === 'pro' ? 'border-primary ring-2 ring-primary/20' : 'border-slate-700' }} flex flex-col">
                    
                    @if($plan->slug === 'pro')
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                        <span class="bg-primary text-white text-xs font-bold px-4 py-1 rounded-full">PALING POPULER</span>
                    </div>
                    @endif
                    
                    <!-- Plan Header -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-2">{{ $plan->name }}</h3>
                        <p class="text-slate-400 text-sm mb-4">{{ $plan->description ?? 'Untuk bisnis yang berkembang' }}</p>
                        
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-primary">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                            <span class="text-slate-400">/bulan</span>
                        </div>
                        @if($plan->price_yearly)
                        <div class="text-sm text-green-400 mt-1">
                            atau Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}/tahun (hemat 20%)
                        </div>
                        @endif
                    </div>
                    
                    <!-- Features -->
                    <ul class="space-y-3 mb-6 flex-1">
                        <li class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('ai_messages', 0) == -1 ? 'Unlimited' : number_format($plan->getLimit('ai_messages', 0)) }} pesan AI/bulan
                        </li>
                        <li class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('contacts', 0) == -1 ? 'Unlimited' : number_format($plan->getLimit('contacts', 0)) }} kontak
                        </li>
                        <li class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('wa_devices', 0) }} WhatsApp device
                        </li>
                        @if($plan->hasFeature('broadcasts'))
                        <li class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('broadcasts', 0) == -1 ? 'Unlimited' : number_format($plan->getLimit('broadcasts', 0)) }} broadcast/bulan
                        </li>
                        @else
                        <li class="flex items-center gap-2 text-sm text-slate-500">
                            <span class="material-symbols-outlined text-slate-500 text-lg">close</span>
                            Broadcast
                        </li>
                        @endif
                        @if($plan->hasFeature('sequences'))
                        <li class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('sequences', 0) == -1 ? 'Unlimited' : $plan->getLimit('sequences', 0) }} sequences
                        </li>
                        @else
                        <li class="flex items-center gap-2 text-sm text-slate-500">
                            <span class="material-symbols-outlined text-slate-500 text-lg">close</span>
                            Sequences
                        </li>
                        @endif
                        @if($plan->hasFeature('web_widgets'))
                        <li class="flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('web_widgets', 0) == -1 ? 'Unlimited' : $plan->getLimit('web_widgets', 0) }} web widget
                        </li>
                        @else
                        <li class="flex items-center gap-2 text-sm text-slate-500">
                            <span class="material-symbols-outlined text-slate-500 text-lg">close</span>
                            Web Widget
                        </li>
                        @endif
                    </ul>
                    
                    <!-- CTA Button -->
                    @if($currentPlan && $currentPlan->id === $plan->id)
                        <button disabled class="w-full py-3 rounded-xl bg-slate-700 text-slate-400 font-semibold cursor-not-allowed">
                            Paket Saat Ini
                        </button>
                    @else
                        <a href="{{ route('checkout.index', $plan->slug) }}" 
                           class="block w-full py-3 rounded-xl {{ $plan->slug === 'pro' ? 'bg-primary hover:bg-primary/90' : 'bg-slate-700 hover:bg-slate-600' }} text-white font-semibold text-center transition">
                            Pilih Paket
                        </a>
                    @endif
                </div>
                @endforeach
                
            </div>

            <!-- FAQ Link -->
            <div class="text-center mt-12 text-slate-400">
                <p>Punya pertanyaan? <a href="{{ route('support.index') }}" class="text-primary hover:underline">Hubungi tim support kami</a></p>
            </div>

        </div>
    </main>

</body>
</html>
