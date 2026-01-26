<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - ReplyAI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
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
    
    <!-- Midtrans Snap.js -->
    <script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
</head>
<body class="bg-background-dark text-white">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="/" class="inline-flex items-center gap-2">
                    <span class="text-3xl font-black text-primary">REPLY</span>
                    <span class="text-3xl font-black text-white">AI</span>
                </a>
            </div>

            <!-- Payment Card -->
            <div class="bg-surface-dark rounded-2xl p-8 border border-slate-700/50">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold mb-2">Pembayaran</h1>
                    <p class="text-slate-400">Anda akan membayar untuk:</p>
                </div>

                <!-- Order Summary -->
                <div class="bg-background-dark rounded-xl p-4 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400">Paket</span>
                        <span class="font-semibold">{{ $payment->plan->name }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400">Durasi</span>
                        <span>{{ $payment->duration_months }} bulan</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400">Invoice</span>
                        <span class="font-mono text-sm">{{ $payment->invoice_number }}</span>
                    </div>
                    <div class="border-t border-slate-700 my-3"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold">Total</span>
                        <span class="text-2xl font-bold text-primary">Rp {{ number_format($payment->total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Pay Button -->
                <button 
                    id="pay-button"
                    class="w-full py-4 px-6 bg-gradient-to-r from-primary to-blue-600 hover:from-primary/90 hover:to-blue-600/90 rounded-xl font-semibold text-lg transition flex items-center justify-center gap-3"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Bayar Sekarang
                </button>

                <!-- Loading State -->
                <div id="loading" class="hidden w-full py-4 px-6 bg-slate-700 rounded-xl text-center">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses...</span>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="text-center mt-6">
                    <a href="{{ route('checkout.payment', $payment->invoice_number) }}" class="text-slate-400 hover:text-white transition">
                        ← Kembali ke pilihan pembayaran
                    </a>
                </div>
            </div>

            <!-- Security Info -->
            <div class="text-center mt-6">
                <div class="flex items-center justify-center gap-2 text-slate-500 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Pembayaran aman diproses oleh Midtrans</span>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        document.getElementById('pay-button').addEventListener('click', function () {
            // Hide pay button, show loading
            document.getElementById('pay-button').classList.add('hidden');
            document.getElementById('loading').classList.remove('hidden');

            // Trigger Snap popup
            snap.pay('{{ $snapData['token'] }}', {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    window.location.href = '{{ route('checkout.midtrans.finish') }}?invoice={{ $payment->invoice_number }}';
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    // Tampilkan pesan dan opsi
                    document.getElementById('pay-button').classList.remove('hidden');
                    document.getElementById('loading').classList.add('hidden');
                    
                    // Tanyakan user apakah mau lanjut bayar atau ganti metode
                    if (confirm('Pembayaran Anda pending. Klik OK untuk melihat instruksi pembayaran, atau Cancel untuk memilih metode lain.')) {
                        window.location.href = '{{ route('checkout.midtrans.finish') }}?invoice={{ $payment->invoice_number }}';
                    }
                    // Jika cancel, tetap di halaman ini
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    alert('Pembayaran gagal. Silakan coba lagi atau pilih metode lain.');
                    document.getElementById('pay-button').classList.remove('hidden');
                    document.getElementById('loading').classList.add('hidden');
                },
                onClose: function() {
                    console.log('Customer closed the popup');
                    document.getElementById('pay-button').classList.remove('hidden');
                    document.getElementById('loading').classList.add('hidden');
                    // Tidak redirect, tetap di halaman ini
                }
            });
        });
    </script>
</body>
</html>

