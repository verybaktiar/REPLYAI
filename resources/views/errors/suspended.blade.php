<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Akun Ditangguhkan - ReplyAI</title>
    
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: "#135bec",
                        "background-dark": "#0a0e17",
                        "surface-dark": "#141b2a",
                    },
                    fontFamily: { sans: ["Inter", "sans-serif"] },
                }
            }
        }
    </script>
</head>
<body class="bg-background-dark text-white font-sans antialiased min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="mb-8 flex justify-center">
            <div class="size-24 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center shadow-2xl shadow-red-500/10">
                <span class="material-symbols-outlined text-red-500 text-5xl">block</span>
            </div>
        </div>
        
        <h1 class="text-3xl font-black mb-4">Akun Ditangguhkan 🔒</h1>
        
        <p class="text-slate-400 mb-8 leading-relaxed">
            Maaf, akun Anda (<strong>{{ session('suspended_email') }}</strong>) telah ditangguhkan oleh administrator karena adanya pelanggaran kebijakan atau masalah keamanan.
        </p>
        
        <div class="bg-surface-dark border border-slate-800 rounded-3xl p-6 mb-8 text-left">
            <h3 class="font-bold text-sm uppercase tracking-widest text-slate-500 mb-4">Langkah Selanjutnya:</h3>
            <ul class="space-y-4">
                <li class="flex gap-3">
                    <span class="material-symbols-outlined text-primary text-xl">contact_support</span>
                    <p class="text-sm text-slate-300">Hubungi tim dukungan kami melalui email di <a href="mailto:support@replyai.com" class="text-primary font-bold hover:underline">support@replyai.com</a>.</p>
                </li>
                <li class="flex gap-3">
                    <span class="material-symbols-outlined text-primary text-xl">description</span>
                    <p class="text-sm text-slate-300">Tinjau kembali syarat dan ketentuan penggunaan platform kami.</p>
                </li>
            </ul>
        </div>
        
        <div class="flex flex-col gap-3">
            <a href="{{ route('home') }}" class="w-full py-4 bg-primary hover:bg-primary/80 text-white rounded-2xl font-bold transition shadow-lg shadow-primary/20">
                Kembali ke Beranda
            </a>
            <p class="text-xs text-slate-600 mt-4">REPLYAI SECURITY ENFORCEMENT</p>
        </div>
    </div>
</body>
</html>
