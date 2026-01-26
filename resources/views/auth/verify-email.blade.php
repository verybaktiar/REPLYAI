<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - ReplyAI</title>
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
    
    <div class="w-full max-w-md">
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
                <div class="w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold mb-2">Verify Your Email</h1>
                <p class="text-slate-400">Kami telah mengirim link verifikasi ke email Anda.</p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300 text-sm">
                    Link verifikasi baru telah dikirim ke email Anda!
                </div>
            @endif

            <div class="mb-6 p-4 rounded-xl bg-slate-800 border border-slate-700">
                <p class="text-sm text-slate-300">
                    Terima kasih telah mendaftar! Sebelum melanjutkan, silakan cek email Anda untuk link verifikasi yang kami kirim.
                </p>
                <p class="text-sm text-slate-400 mt-3">
                    Jika Anda tidak menerima email, klik tombol di bawah untuk mengirim ulang.
                </p>
            </div>

            <div class="space-y-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition">
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-700 rounded-xl font-semibold border border-slate-700 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-slate-500">
            <p>&copy; 2026 ReplyAI. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
