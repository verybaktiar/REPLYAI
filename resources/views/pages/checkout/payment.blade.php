<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Pembayaran {{ $payment->invoice_number }}</title>
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
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-background-dark text-white font-display antialiased min-h-screen">
    
    <nav class="fixed top-0 w-full z-50 bg-background-dark/80 backdrop-blur-sm border-b border-slate-700">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-4">
                    @if($payment->status === 'pending')
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-1 text-slate-400 hover:text-white transition">
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        <span class="hidden sm:inline text-sm">Dashboard</span>
                    </a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <span class="text-2xl font-black text-primary">REPLY</span>
                        <span class="text-2xl font-black text-white">AI</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-16 px-4">
        <div class="max-w-2xl mx-auto">
            
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300">
                {{ session('error') }}
            </div>
            @endif

            <!-- Invoice Card -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="text-xs text-slate-400">Invoice</div>
                        <div class="text-xl font-bold font-mono">{{ $payment->invoice_number }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-400">Status</div>
                        @if($payment->status === 'pending')
                            <span class="inline-flex items-center gap-1 text-yellow-400 font-medium">
                                <span class="material-symbols-outlined text-lg">schedule</span>
                                Menunggu Pembayaran
                            </span>
                        @elseif($payment->status === 'paid')
                            <span class="inline-flex items-center gap-1 text-green-400 font-medium">
                                <span class="material-symbols-outlined text-lg">check_circle</span>
                                Lunas
                            </span>
                        @endif
                    </div>
                </div>

                <hr class="border-slate-700 my-4"/>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Paket</span>
                        <span>{{ $payment->plan->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Durasi</span>
                        <span>{{ $payment->duration_months }} bulan</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Harga</span>
                        <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                    </div>
                    @if($payment->discount > 0)
                    <div class="flex justify-between text-green-400">
                        <span>Diskon</span>
                        <span>- Rp {{ number_format($payment->discount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>

                <hr class="border-slate-700 my-4"/>

                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold">Total Bayar</span>
                    <span class="text-2xl font-black text-primary">Rp {{ number_format($payment->total, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($payment->status === 'pending')
            
            <!-- Payment Method Selection -->
            <div class="bg-surface-dark rounded-2xl p-6 border border-slate-700 mb-6">
                <h3 class="font-semibold mb-4">Pilih Metode Pembayaran</h3>
                
                <!-- Midtrans - Quick Payment (Recommended) -->
                <div class="mb-4">
                    <a href="{{ route('checkout.midtrans.pay', $payment->invoice_number) }}" 
                       class="block p-5 rounded-xl bg-gradient-to-r from-primary/20 to-blue-600/20 border-2 border-primary hover:from-primary/30 hover:to-blue-600/30 transition group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-primary/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary text-2xl">flash_on</span>
                                </div>
                                <div>
                                    <div class="font-bold text-lg flex items-center gap-2">
                                        Bayar Instan
                                        <span class="px-2 py-0.5 text-xs bg-green-500 text-white rounded-full font-semibold">RECOMMENDED</span>
                                    </div>
                                    <div class="text-slate-400 text-sm">Virtual Account, GoPay, QRIS, Kartu Kredit</div>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-2xl text-primary group-hover:translate-x-1 transition">arrow_forward</span>
                        </div>
                    </a>
                </div>

                <!-- Divider -->
                <div class="flex items-center gap-4 my-4">
                    <div class="flex-1 border-t border-slate-700"></div>
                    <span class="text-slate-500 text-sm">ATAU</span>
                    <div class="flex-1 border-t border-slate-700"></div>
                </div>

                <!-- Manual Transfer Toggle -->
                <div x-data="{ showManual: false }">
                    <button @click="showManual = !showManual" 
                            class="w-full p-5 rounded-xl bg-background-dark border border-slate-700 hover:border-slate-600 transition flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-slate-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-slate-300 text-2xl">account_balance</span>
                            </div>
                            <div class="text-left">
                                <div class="font-semibold">Transfer Bank Manual</div>
                                <div class="text-slate-400 text-sm">Upload bukti transfer, diverifikasi 1x24 jam</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-xl text-slate-400" x-text="showManual ? 'expand_less' : 'expand_more'"></span>
                    </button>

                    <!-- Manual Transfer Content -->
                    <div x-show="showManual" x-transition class="mt-4 space-y-4">
                        <!-- Bank Transfer Info -->
                        <div class="p-4 rounded-xl bg-background-dark border border-slate-700">
                            <h4 class="font-semibold mb-3 text-sm text-slate-300">Transfer ke Rekening:</h4>
                            <div class="space-y-3">
                                @foreach($bankInfo as $bank)
                                <div class="p-3 rounded-lg bg-slate-800/50">
                                    <div class="font-bold text-sm mb-1">{{ $bank['bank'] }}</div>
                                    <div class="font-mono text-lg tracking-wider">{{ $bank['account_number'] }}</div>
                                    <div class="text-slate-400 text-xs mt-1">a.n. {{ $bank['account_name'] }}</div>
                                </div>
                                @endforeach
                            </div>
                            <p class="text-sm text-yellow-400 mt-3">
                                Transfer tepat: <strong>Rp {{ number_format($payment->total, 0, ',', '.') }}</strong>
                            </p>
                        </div>

                        <!-- Upload Proof Form -->
                        <form action="{{ route('checkout.upload-proof', $payment) }}" method="POST" enctype="multipart/form-data" class="p-4 rounded-xl bg-background-dark border border-slate-700">
                            @csrf
                            <h4 class="font-semibold mb-3 text-sm text-slate-300">Upload Bukti Transfer:</h4>
                            <div class="mb-4">
                                <input type="file" name="proof" accept="image/*" required
                                       class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-primary file:text-white file:font-semibold file:cursor-pointer">
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-semibold transition">
                                Upload Bukti Transfer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Payment Security Info -->
            <div class="flex items-center justify-center gap-2 text-slate-500 text-sm">
                <span class="material-symbols-outlined text-lg">verified_user</span>
                <span>Pembayaran aman & terenkripsi</span>
            </div>

            <!-- Back to Dashboard -->
            <div class="mt-6 text-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition text-sm">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Kembali ke Dashboard
                </a>
            </div>


            @else

            <div class="text-center py-8">
                <span class="material-symbols-outlined text-6xl text-green-500 mb-4">verified</span>
                <h2 class="text-2xl font-bold mb-2">Pembayaran Berhasil!</h2>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary/90">
                    Ke Dashboard
                </a>
            </div>

            @endif

        </div>
    </main>

</body>
</html>
