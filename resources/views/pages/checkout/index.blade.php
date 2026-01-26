<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Checkout {{ $plan->name }}</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
                <a href="{{ route('pricing') }}" class="flex items-center gap-1 text-sm text-slate-400 hover:text-white">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-16 px-4">
        <div class="max-w-4xl mx-auto">
            
            <h1 class="text-2xl font-bold mb-8 text-center">Checkout Paket {{ $plan->name }}</h1>

            @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300">
                {{ session('error') }}
            </div>
            @endif

            <div class="grid lg:grid-cols-2 gap-8">
                
                <!-- Order Summary -->
                <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700">
                    <h2 class="font-semibold text-lg mb-4">Ringkasan Pesanan</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Paket</span>
                            <span class="font-semibold">{{ $plan->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Harga Bulanan</span>
                            <span>Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <hr class="border-slate-700 my-4"/>

                    <h3 class="font-medium mb-3">Fitur yang Anda dapatkan:</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('ai_messages', 0) == -1 ? 'Unlimited' : number_format($plan->getLimit('ai_messages', 0)) }} pesan AI/bulan
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('contacts', 0) == -1 ? 'Unlimited' : number_format($plan->getLimit('contacts', 0)) }} kontak
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-500 text-lg">check</span>
                            {{ $plan->getLimit('wa_devices', 0) }} WhatsApp device
                        </li>
                    </ul>
                </div>

                <!-- Checkout Form -->
                <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700" x-data="{ duration: '1' }">
                    <h2 class="font-semibold text-lg mb-4">Pilih Durasi</h2>
                    
                    <form action="{{ route('checkout.process', $plan->slug) }}" method="POST">
                        @csrf
                        
                        <div class="space-y-3 mb-6">
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition"
                                   :class="duration === '1' ? 'border-primary bg-primary/10' : 'border-slate-700 hover:border-slate-600'">
                                <input type="radio" name="duration" value="1" x-model="duration" class="text-primary focus:ring-primary">
                                <div class="flex-1">
                                    <span class="font-medium">Bulanan</span>
                                    <span class="text-slate-400 text-sm ml-2">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}/bulan</span>
                                </div>
                            </label>
                            
                            @if($plan->price_yearly)
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition relative"
                                   :class="duration === '12' ? 'border-primary bg-primary/10' : 'border-slate-700 hover:border-slate-600'">
                                <input type="radio" name="duration" value="12" x-model="duration" class="text-primary focus:ring-primary">
                                <div class="flex-1">
                                    <span class="font-medium">Tahunan</span>
                                    <span class="text-slate-400 text-sm ml-2">Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}/tahun</span>
                                </div>
                                <span class="absolute -top-2 right-4 bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">HEMAT 20%</span>
                            </label>
                            @endif
                        </div>

                        <!-- Promo Code -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2">Kode Promo (opsional)</label>
                            <input type="text" name="promo_code" placeholder="Masukkan kode promo" 
                                   class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary">
                        </div>

                        <!-- Total -->
                        <div class="flex justify-between items-center p-4 rounded-xl bg-background-dark mb-6">
                            <span class="text-slate-400">Total Pembayaran</span>
                            <span class="text-2xl font-black text-primary" x-text="duration === '12' ? 'Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}' : 'Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}'"></span>
                        </div>

                        <button type="submit" class="w-full py-4 rounded-xl bg-primary text-white font-bold text-lg hover:bg-primary/90 transition">
                            Lanjutkan Pembayaran
                        </button>
                    </form>
                </div>

            </div>

            <!-- Bank Info Preview -->
            <div class="mt-8 bg-surface-dark rounded-2xl p-6 border border-slate-700">
                <h3 class="font-semibold mb-4">Metode Pembayaran: Transfer Bank</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($bankInfo as $bank)
                    <div class="p-4 rounded-xl bg-background-dark border border-slate-700">
                        <div class="font-bold text-lg mb-2">{{ $bank['bank'] }}</div>
                        <div class="text-slate-400 text-sm">No. Rekening</div>
                        <div class="font-mono text-lg">{{ $bank['account_number'] }}</div>
                        <div class="text-slate-400 text-sm mt-2">Atas Nama</div>
                        <div>{{ $bank['account_name'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </main>

</body>
</html>
