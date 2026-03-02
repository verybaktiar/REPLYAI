<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Terlalu Banyak Request - ReplyAI</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
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
<body class="bg-background-dark text-white font-display antialiased min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md text-center">
        
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="inline-flex items-center gap-2">
                <span class="text-3xl font-black text-primary">REPLY</span>
                <span class="text-3xl font-black text-white">AI</span>
            </a>
        </div>

        <!-- Error Content -->
        <div class="bg-surface-dark rounded-2xl p-8 border border-slate-700">
            
            <div class="mb-6">
                <div class="w-20 h-20 mx-auto bg-yellow-500/20 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold mb-2">Terlalu Banyak Request</h1>
                <p class="text-slate-400">{{ $message ?? 'Anda telah melakukan terlalu banyak request dalam waktu singkat.' }}</p>
            </div>

            @if(isset($retryAfter) && $retryAfter > 0)
            <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-xl">
                <p class="text-sm text-yellow-300">
                    Silakan tunggu <span id="countdown" class="font-bold">{{ $retryAfter }}</span> detik sebelum mencoba lagi.
                </p>
            </div>
            @endif

            <a href="/" class="inline-block py-3 px-6 rounded-xl bg-primary text-white font-semibold hover:bg-primary/90 transition">
                Kembali ke Beranda
            </a>
        </div>

        <p class="mt-6 text-sm text-slate-500">
            Error 429 - Rate Limit Exceeded
        </p>
    </div>

    @if(isset($retryAfter) && $retryAfter > 0)
    <script>
        let countdown = {{ $retryAfter }};
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                location.reload();
            }
        }, 1000);
    </script>
    @endif

</body>
</html>
