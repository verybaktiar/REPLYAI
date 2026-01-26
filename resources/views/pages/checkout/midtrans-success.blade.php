<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Pembayaran Berhasil!</title>
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
    <style>
        /* Confetti Animation */
        .confetti-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 100;
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0;
            animation: confetti-fall 3s ease-out forwards;
        }
        
        @keyframes confetti-fall {
            0% {
                opacity: 1;
                transform: translateY(-100px) rotate(0deg);
            }
            100% {
                opacity: 0;
                transform: translateY(100vh) rotate(720deg);
            }
        }
        
        /* Success icon pulse animation */
        @keyframes pulse-success {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(34, 197, 94, 0);
            }
        }
        
        .pulse-success {
            animation: pulse-success 2s ease-in-out infinite;
        }
        
        /* Checkmark draw animation */
        @keyframes draw-check {
            0% {
                stroke-dashoffset: 100;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        
        .checkmark-path {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: draw-check 0.6s ease-out 0.3s forwards;
        }
        
        /* Fade in animation */
        @keyframes fade-in-up {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            opacity: 0;
            animation: fade-in-up 0.5s ease-out forwards;
        }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }
    </style>
</head>
<body class="bg-background-dark text-white font-display antialiased min-h-screen flex items-center justify-center p-6">
    
    <!-- Confetti Container -->
    <div class="confetti-container" id="confetti"></div>
    
    <div class="max-w-2xl w-full">
        
        <!-- Logo -->
        <div class="text-center mb-8 fade-in-up">
            <a href="/" class="inline-flex items-center gap-2 text-2xl font-black">
                <span class="text-primary">REPLY</span>
                <span class="text-white">AI</span>
            </a>
        </div>

        <!-- Success Card -->
        <div class="bg-surface-dark rounded-2xl p-8 border border-slate-700 text-center">
            
            <!-- Success Icon with Animation -->
            <div class="w-24 h-24 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6 pulse-success fade-in-up delay-100">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path class="checkmark-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-black mb-2 fade-in-up delay-200">
                🎉 Pembayaran Berhasil!
            </h1>
            <p class="text-xl text-slate-400 mb-8 fade-in-up delay-300">
                Selamat! Subscription Anda sudah aktif.
            </p>

            <!-- Invoice Info -->
            <div class="bg-background-dark rounded-xl p-6 mb-6 fade-in-up delay-400">
                <div class="text-sm text-slate-400 mb-2">Invoice Number</div>
                <div class="text-2xl font-mono font-bold text-primary">{{ $payment->invoice_number }}</div>
            </div>

            <!-- Subscription Info -->
            <div class="bg-gradient-to-r from-primary/10 to-blue-600/10 border border-primary/30 rounded-xl p-6 mb-8 text-left fade-in-up delay-500">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-primary/20 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">workspace_premium</span>
                    </div>
                    <div>
                        <div class="font-bold text-lg">{{ $payment->plan->name }}</div>
                        <div class="text-slate-400 text-sm">Aktif selama {{ $payment->duration_months }} bulan</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-400">Total Dibayar</div>
                        <div class="font-bold text-green-400">Rp {{ number_format($payment->total, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400">Berlaku Hingga</div>
                        <div class="font-semibold">
                            @if($subscription)
                                {{ $subscription->expires_at->format('d M Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- What's Next -->
            <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6 mb-8 text-left fade-in-up delay-500">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-400 text-xl mt-0.5">lightbulb</span>
                    <div class="text-sm text-slate-300">
                        <p class="font-semibold mb-2">Apa Selanjutnya?</p>
                        <ul class="space-y-2 text-slate-400">
                            <li>• Hubungkan WhatsApp Anda di menu <strong class="text-white">Integrasi</strong></li>
                            <li>• Atur profil bisnis di <strong class="text-white">Pengaturan</strong></li>
                            <li>• Mulai percakapan dengan pelanggan Anda!</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3 fade-in-up delay-600">
                <a href="{{ route('subscription.index') }}" 
                   class="flex-1 py-3 px-6 bg-slate-800 hover:bg-slate-700 rounded-xl font-semibold text-center border border-slate-700 transition inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">receipt_long</span>
                    Lihat Subscription
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="flex-1 py-3 px-6 bg-primary hover:bg-primary/90 rounded-xl font-semibold text-center transition inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">rocket_launch</span>
                    Mulai Sekarang
                </a>
            </div>
        </div>

        <!-- Receipt Download (Optional) -->
        <div class="text-center mt-6 text-sm text-slate-500 fade-in-up delay-600">
            <p>Email konfirmasi sudah dikirim ke alamat email Anda.</p>
        </div>

    </div>

    <script>
        // Create confetti elements
        function createConfetti() {
            const container = document.getElementById('confetti');
            const colors = ['#135bec', '#22c55e', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'];
            
            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 1 + 's';
                confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                
                // Random shapes
                if (Math.random() > 0.5) {
                    confetti.style.borderRadius = '50%';
                } else {
                    confetti.style.width = '8px';
                    confetti.style.height = '16px';
                }
                
                container.appendChild(confetti);
            }
            
            // Remove confetti after animation
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }
        
        // Start confetti on page load
        window.addEventListener('load', createConfetti);
    </script>

</body>
</html>
</code>
