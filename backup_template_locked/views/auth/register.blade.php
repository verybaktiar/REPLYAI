<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Daftar</title>
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
<body class="bg-background-dark text-white font-display antialiased min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2">
                <span class="text-3xl font-black text-primary">REPLY</span>
                <span class="text-3xl font-black text-white">AI</span>
            </a>
            <p class="text-slate-400 mt-2">Buat akun baru</p>
        </div>

        <!-- Register Form -->
        <div class="bg-surface-dark rounded-2xl p-8 border border-slate-700">
            
            @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-sm font-medium mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary"
                           placeholder="Nama Anda">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary"
                           placeholder="nama@email.com">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary"
                           placeholder="Minimal 8 karakter">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary"
                           placeholder="Ulangi password">
                </div>

                <button type="submit" class="w-full py-4 rounded-xl bg-primary text-white font-bold text-lg hover:bg-primary/90 transition">
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-slate-400">
                Sudah punya akun? 
                <a href="/login" class="text-primary hover:underline">Masuk</a>
            </div>
        </div>

    </div>

</body>
</html>
