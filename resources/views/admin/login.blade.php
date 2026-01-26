<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Admin Login - REPLYAI</title>
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
            <div class="inline-flex items-center gap-2 mb-4">
                <span class="text-3xl font-black text-primary">REPLY</span>
                <span class="text-3xl font-black text-white">AI</span>
            </div>
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-surface-dark rounded-full border border-slate-700">
                <span class="material-symbols-outlined text-yellow-500 text-lg">shield</span>
                <span class="text-sm font-semibold text-slate-300">Super Admin Panel</span>
            </div>
            <p class="text-slate-400 mt-4">Masuk ke panel administrasi</p>
        </div>

        <!-- Login Form -->
        <div class="bg-surface-dark rounded-2xl p-8 border border-slate-700">
            
            @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 text-sm">
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-sm font-medium mb-2 text-slate-300">Email Admin</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">mail</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-12 pr-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary"
                               placeholder="admin@replyai.com">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium mb-2 text-slate-300">Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">lock</span>
                        <input type="password" name="password" required
                               class="w-full pl-12 pr-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-background-dark border-slate-700 text-primary focus:ring-primary">
                        <span class="text-sm text-slate-400">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-4 rounded-xl bg-primary text-white font-bold text-lg hover:bg-primary/90 transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">login</span>
                    <span>Masuk Admin Panel</span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-700">
                <p class="text-xs text-slate-500 text-center">
                    <span class="material-symbols-outlined text-sm align-middle">info</span>
                    Default: admin@replyai.com / Admin123!
                </p>
            </div>
        </div>

        <!-- Back to Site -->
        <div class="mt-6 text-center">
            <a href="/" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-primary transition">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span>Kembali ke Website</span>
            </a>
        </div>

    </div>

</body>
</html>
