<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Pembayaran Berhasil</title>
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
<body class="bg-background-dark text-white font-display antialiased min-h-screen flex items-center justify-center p-6">
    
    <div class="max-w-2xl w-full">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2 text-2xl font-black">
                <span class="text-primary">REPLY</span>
                <span class="text-white">AI</span>
            </a>
        </div>

        <!-- Success Card -->
        <div class="bg-surface-dark rounded-2xl p-8 border border-slate-700 text-center">
            
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-black mb-4">Bukti Transfer Berhasil Diupload!</h1>
            <p class="text-xl text-slate-400 mb-8">
                Pembayaran Anda sedang diverifikasi oleh tim kami
            </p>

            <!-- Invoice Info -->
            <div class="bg-background-dark rounded-xl p-6 mb-8">
                <div class="text-sm text-slate-400 mb-2">Invoice Number</div>
                <div class="text-2xl font-mono font-bold text-primary">{{ $payment->invoice_number }}</div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 mb-8 text-left">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-slate-300">
                        <p class="font-semibold mb-2">Apa Selanjutnya?</p>
                        <ul class="space-y-2 text-slate-400">
                            <li>• Tim kami akan memverifikasi pembayaran dalam <strong class="text-white">1-24 jam</strong></li>
                            <li>• Anda akan menerima email konfirmasi setelah diverifikasi</li>
                            <li>• Subscription Anda akan aktif secara otomatis</li>
                        </ul>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 text-sm">
                {{ session('success') }}
            </div>
            @endif

            <!-- Payment Summary -->
            <div class="bg-background-dark rounded-xl p-6 mb-8 text-left">
                <h3 class="font-semibold mb-4">Ringkasan Pembayaran</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Paket</span>
                        <span class="font-semibold">{{ $payment->plan->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Durasi</span>
                        <span>{{ $payment->duration_months }} bulan</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Total Bayar</span>
                        <span class="font-bold text-primary">Rp {{ number_format($payment->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('subscription.pending') }}" 
                   class="flex-1 py-3 px-6 bg-slate-800 hover:bg-slate-700 rounded-xl font-semibold text-center border border-slate-700 transition">
                    Lihat Status
                </a>
                <a href="/" 
                   class="flex-1 py-3 px-6 bg-primary hover:bg-primary/90 rounded-xl font-semibold text-center transition">
                    Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Support -->
        <div class="text-center mt-6 text-sm text-slate-500">
            <p>Butuh bantuan? <a href="mailto:support@replyai.com" class="text-primary hover:underline">Hubungi Support</a></p>
        </div>

    </div>

</body>
</html>
