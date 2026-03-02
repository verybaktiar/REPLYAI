<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - ReplyAI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
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
                        <span class="material-symbols-outlined text-3xl text-primary">credit_card</span>
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
                    <span class="material-symbols-outlined">credit_card</span>
                    Bayar Sekarang
                </button>

                <!-- Loading State -->
                <div id="loading" class="hidden w-full py-4 px-6 bg-slate-700 rounded-xl text-center">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Membuka metode pembayaran...</span>
                    </div>
                </div>

                <!-- 🔥 PROMINENT: Change Payment Method Button -->
                @if(isset($forceNew) && $forceNew)
                <!-- Mode: Token Baru Aktif -->
                <div class="mt-4 p-3 bg-green-500/10 border border-green-500/30 rounded-xl">
                    <p class="text-xs text-green-400 text-center mb-2">
                        <span class="material-symbols-outlined text-sm align-text-bottom">check_circle</span>
                        Sesi baru aktif - Anda bisa memilih metode pembayaran
                    </p>
                    <a href="{{ route('checkout.midtrans.pay', $payment->invoice_number) }}" 
                       class="block w-full py-2.5 px-4 bg-slate-700 hover:bg-slate-600 rounded-lg font-medium text-sm text-center transition">
                        Kembali ke Token Tersimpan
                    </a>
                </div>
                @else
                <!-- Mode: Token Tersimpan -->
                <div class="mt-4 p-4 bg-yellow-500/10 border-2 border-yellow-500/40 rounded-xl">
                    <div class="flex items-start gap-3 mb-3">
                        <span class="material-symbols-outlined text-yellow-400 text-xl">lightbulb</span>
                        <div>
                            <p class="text-sm font-semibold text-yellow-400">Mau ganti metode?</p>
                            <p class="text-xs text-yellow-200/70 mt-0.5">
                                Contoh: VA → QRIS, atau QRIS → GoPay
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('checkout.midtrans.pay', $payment->invoice_number) }}?new=1" 
                       class="block w-full py-3 px-4 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-400 hover:to-orange-400 rounded-xl font-bold text-sm text-black text-center transition shadow-lg shadow-yellow-500/20"
                       title="Buat sesi baru untuk memilih metode lain">
                        <span class="material-symbols-outlined text-base align-text-bottom">sync</span>
                        Ganti Metode Pembayaran
                    </a>
                </div>
                @endif

                <!-- Back to Payment Options -->
                <div class="text-center mt-4 pt-4 border-t border-slate-700">
                    <a href="{{ route('checkout.payment', $payment->invoice_number) }}" class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-white transition">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                        Kembali ke Semua Pilihan
                    </a>
                </div>
            </div>

            <!-- Security Info & Trust Badges -->
            <div class="mt-6 space-y-4">
                <!-- Trust Badges -->
                <div class="flex items-center justify-center gap-6 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-emerald-400 text-sm">lock</span>
                        SSL Secure
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-400 text-sm">verified</span>
                        Verified by Midtrans
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-purple-400 text-sm">shield</span>
                        256-bit Encryption
                    </span>
                </div>
                
                <!-- Help Text -->
                <p class="text-xs text-slate-500 text-center max-w-sm mx-auto">
                    Anda akan diarahkan ke halaman pembayaran Midtrans. 
                    Pilih metode Virtual Account, QRIS, E-Wallet, atau Kartu Kredit.
                </p>
                
                <!-- Midtrans Logo Placeholder -->
                <div class="flex items-center justify-center gap-2 pt-2 border-t border-slate-700/50">
                    <span class="text-[10px] text-slate-500 uppercase tracking-widest">Powered by</span>
                    <span class="font-bold text-slate-400">Midtrans</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔥 CUSTOM MODAL: Payment Pending -->
    <div id="pending-modal" class="fixed inset-0 z-50 hidden" x-data="{ open: false }">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity opacity-0" id="pending-backdrop"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-surface-dark border border-slate-600 rounded-2xl max-w-md w-full p-6 relative transform scale-95 opacity-0 transition-all" id="pending-content">
                <!-- Icon -->
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-yellow-400">schedule</span>
                </div>
                
                <!-- Title -->
                <h3 class="text-xl font-bold text-center mb-2">Pembayaran Pending</h3>
                <p class="text-slate-400 text-center text-sm mb-4">
                    Anda telah memilih metode pembayaran. Selesaikan pembayaran sesuai instruksi.
                </p>
                
                <!-- 🔥 INFO BOX: Highlight ganti metode -->
                <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border-2 border-yellow-500/50 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-yellow-500/30 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-yellow-400 text-xl">swap_horiz</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-yellow-400">Ingin ganti metode pembayaran?</p>
                            <p class="text-xs text-yellow-200/80 mt-1">
                                Contoh: <span class="font-mono bg-yellow-500/20 px-1 rounded">Bank Transfer (VA)</span> → <span class="font-mono bg-yellow-500/20 px-1 rounded">QRIS</span>
                            </p>
                            <p class="text-xs text-yellow-300 mt-2">
                                👉 Klik tombol <strong>"Pilih Metode Lain"</strong> di bawah
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Options -->
                <div class="space-y-3">
                    <!-- Primary: Lihat Instruksi -->
                    <a href="{{ route('checkout.midtrans.finish') }}?invoice={{ $payment->invoice_number }}" 
                       class="block w-full py-3.5 px-4 bg-primary hover:bg-primary/90 rounded-xl font-bold text-center transition flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-sm">visibility</span>
                        Lihat Instruksi Pembayaran
                    </a>
                    
                    <!-- 🔥 HIGHLIGHT: Pilih Metode Lain (Yellow/Orange) -->
                    <a href="{{ route('checkout.midtrans.pay', $payment->invoice_number) }}?new=1" 
                       class="block w-full py-3.5 px-4 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-400 hover:to-orange-400 rounded-xl font-bold text-center text-black transition flex items-center justify-center gap-2 shadow-lg shadow-yellow-500/30 border-2 border-yellow-400">
                        <span class="material-symbols-outlined text-sm">sync</span>
                        Pilih Metode Lain
                        <span class="text-xs bg-black/20 px-2 py-0.5 rounded-full">VA ↔ QRIS</span>
                    </a>
                    
                    <!-- Secondary: Tutup -->
                    <button onclick="closePendingModal()" 
                            class="block w-full py-3 px-4 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-xl font-medium text-center transition flex items-center justify-center gap-2 text-slate-400">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                        Tutup & Kembali
                    </button>
                </div>
                
                <!-- Info -->
                <p class="text-xs text-slate-500 text-center mt-4">
                    Anda juga bisa menyelesaikan pembayaran nanti dari halaman Invoice
                </p>
            </div>
        </div>
    </div>

    <!-- 🔥 CUSTOM MODAL: Payment Error -->
    <div id="error-modal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity opacity-0" id="error-backdrop"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-surface-dark border border-red-500/30 rounded-2xl max-w-md w-full p-6 relative transform scale-95 opacity-0 transition-all" id="error-content">
                <!-- Icon -->
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-red-400">error</span>
                </div>
                
                <!-- Title -->
                <h3 class="text-xl font-bold text-center mb-2">Pembayaran Gagal</h3>
                <p class="text-slate-400 text-center text-sm mb-6" id="error-message">
                    Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.
                </p>
                
                <!-- Options -->
                <div class="space-y-3">
                    <button onclick="closeErrorModal(); document.getElementById('pay-button').click();" 
                            class="block w-full py-3 px-4 bg-primary hover:bg-primary/90 rounded-xl font-semibold text-center transition flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">refresh</span>
                        Coba Lagi
                    </button>
                    
                    <a href="{{ route('checkout.payment', $payment->invoice_number) }}" 
                       class="block w-full py-3 px-4 bg-slate-700 hover:bg-slate-600 rounded-xl font-semibold text-center transition flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                        Kembali ke Pilihan Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        // Show Pending Modal
        function showPendingModal() {
            const modal = document.getElementById('pending-modal');
            const backdrop = document.getElementById('pending-backdrop');
            const content = document.getElementById('pending-content');
            
            modal.classList.remove('hidden');
            
            // Animate in
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        
        // Close Pending Modal
        function closePendingModal() {
            const modal = document.getElementById('pending-modal');
            const backdrop = document.getElementById('pending-backdrop');
            const content = document.getElementById('pending-content');
            
            // Animate out
            backdrop.classList.add('opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                // Reset button state
                document.getElementById('pay-button').classList.remove('hidden');
                document.getElementById('loading').classList.add('hidden');
            }, 300);
        }
        
        // Show Error Modal
        function showErrorModal(message) {
            const modal = document.getElementById('error-modal');
            const backdrop = document.getElementById('error-backdrop');
            const content = document.getElementById('error-content');
            const msgEl = document.getElementById('error-message');
            
            msgEl.textContent = message || 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.';
            modal.classList.remove('hidden');
            
            // Animate in
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        
        // Close Error Modal
        function closeErrorModal() {
            const modal = document.getElementById('error-modal');
            const backdrop = document.getElementById('error-backdrop');
            const content = document.getElementById('error-content');
            
            // Animate out
            backdrop.classList.add('opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                // Reset button state
                document.getElementById('pay-button').classList.remove('hidden');
                document.getElementById('loading').classList.add('hidden');
            }, 300);
        }

        // Pay Button Handler
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
                    // Show custom modal instead of confirm()
                    showPendingModal();
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    showErrorModal('Pembayaran gagal diproses. Silakan coba lagi atau pilih metode lain.');
                },
                onClose: function() {
                    console.log('Customer closed the popup');
                    // Reset button state
                    document.getElementById('pay-button').classList.remove('hidden');
                    document.getElementById('loading').classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>
