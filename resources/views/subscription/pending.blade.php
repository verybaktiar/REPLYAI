<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menunggu Verifikasi - ReplyAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
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
<body class="bg-background-dark text-white font-display antialiased min-h-screen flex items-center justify-center p-6">
    
    <div class="w-full max-w-2xl">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2 text-2xl font-black">
                <span class="text-primary">REPLY</span>
                <span class="text-white">AI</span>
            </a>
        </div>

        <!-- Card -->
        <div class="bg-surface-dark rounded-2xl p-8 border border-slate-700">
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-2">Menunggu Verifikasi Pembayaran</h1>
                <p class="text-slate-400">Pembayaran Anda sedang diverifikasi oleh tim kami.</p>
            </div>

            @if(session('info'))
                <div class="mb-6 p-4 rounded-xl bg-blue-500/20 border border-blue-500/50 text-blue-300 text-sm">
                    {{ session('info') }}
                </div>
            @endif

            @if(auth()->user()->subscription && auth()->user()->subscription->latestPayment)
            <div class="mb-6 p-6 rounded-xl bg-slate-800 border border-slate-700">
                <h2 class="font-bold text-lg mb-4">Detail Pembayaran</h2>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-400 mb-1">Invoice</div>
                        <div class="font-mono">{{ auth()->user()->subscription->latestPayment->invoice_number }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 mb-1">Plan</div>
                        <div class="font-semibold">{{ auth()->user()->subscription->plan->name }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 mb-1">Total</div>
                        <div class="font-bold text-primary">Rp {{ number_format(auth()->user()->subscription->latestPayment->amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 mb-1">Status</div>
                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-400">
                                Pending
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="p-6 rounded-xl bg-blue-500/10 border border-blue-500/30 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-slate-300">
                        <p class="font-semibold mb-2">Proses Verifikasi</p>
                        <p>Tim kami akan memverifikasi pembayaran Anda dalam <strong>1-24 jam</strong>. Anda akan menerima notifikasi email setelah pembayaran diverifikasi.</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                @if(auth()->user()->subscription && auth()->user()->subscription->latestPayment)
                <a href="{{ route('checkout.payment', auth()->user()->subscription->latestPayment->invoice_number) }}" 
                   class="flex-1 py-3 px-6 bg-primary hover:bg-primary/90 rounded-xl font-semibold text-center transition">
                    Lihat Detail Pembayaran
                </a>
                @endif
                <a href="{{ route('account.index') }}" class="flex-1 py-3 px-6 bg-slate-800 hover:bg-slate-700 rounded-xl font-semibold text-center border border-slate-700 transition">
                    My Account
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-slate-500">
            <p>Butuh bantuan? <a href="mailto:support@replyai.com" class="text-primary hover:underline">Hubungi Support</a></p>
        </div>
    </div>

</body>
</html>
