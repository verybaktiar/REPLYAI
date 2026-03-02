<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi 2FA - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-dark": "#0a0e17",
                        "surface-dark": "#141b2a",
                    },
                },
            },
        }
    </script>
    <style>
        .otp-input {
            width: 3rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            border: 2px solid #232f48;
            background: #0a0e17;
            color: white;
            border-radius: 0.75rem;
            transition: all 0.2s;
        }
        .otp-input:focus {
            border-color: #135bec;
            outline: none;
            box-shadow: 0 0 0 3px rgba(19, 91, 236, 0.2);
        }
    </style>
</head>
<body class="bg-background-dark text-white font-['Inter'] min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-4">
                <span class="text-3xl font-black text-primary">REPLY</span>
                <span class="text-3xl font-black text-white">AI</span>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-yellow-500/10 rounded-lg border border-yellow-500/20">
                <span class="material-symbols-outlined text-yellow-500 text-sm">shield</span>
                <span class="text-xs font-semibold text-yellow-400">Admin Panel</span>
            </div>
        </div>
        
        <!-- 2FA Card -->
        <div class="bg-surface-dark rounded-2xl border border-slate-800 p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-primary text-3xl">verified_user</span>
                </div>
                <h1 class="text-xl font-bold mb-2">Verifikasi Dua Faktor</h1>
                <p class="text-sm text-slate-400">
                    Masukkan kode 6 digit dari aplikasi authenticator Anda.
                </p>
            </div>
            
            @if($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-sm">
                {{ $errors->first() }}
            </div>
            @endif
            
            <!-- TOTP Form -->
            <form method="POST" action="{{ route('admin.2fa.verify') }}" class="mb-6">
                @csrf
                <div class="flex justify-center gap-2 mb-6" id="otp-container">
                    <input type="text" name="code" maxlength="6" 
                           class="w-full h-14 text-center text-2xl font-bold bg-background-dark border-2 border-slate-700 rounded-xl focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/20 transition-all"
                           placeholder="000000"
                           inputmode="numeric"
                           pattern="[0-9]*"
                           autocomplete="one-time-code"
                           autofocus>
                </div>
                
                <button type="submit" class="w-full py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition flex items-center justify-center gap-2">
                    <span>Verifikasi</span>
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </button>
            </form>
            
            <!-- Recovery Code Option -->
            <div class="border-t border-slate-800 pt-6">
                <p class="text-center text-sm text-slate-400 mb-4">Tidak bisa mengakses authenticator?</p>
                
                <form method="POST" action="{{ route('admin.2fa.recovery') }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="recovery_code" 
                           class="flex-1 px-4 py-2 bg-background-dark border border-slate-700 rounded-lg text-sm focus:border-primary focus:outline-none"
                           placeholder="Masukkan recovery code"
                           maxlength="10">
                    <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm font-medium transition">
                        Gunakan
                    </button>
                </form>
            </div>
            
            <!-- Logout -->
            <div class="mt-6 pt-6 border-t border-slate-800 text-center">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-slate-400 hover:text-white transition flex items-center gap-1 mx-auto">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Security Note -->
        <p class="text-center text-xs text-slate-500 mt-6">
            Jika Anda tidak mengenali aktivitas ini, segera hubungi administrator.
        </p>
    </div>
    
    <script>
        // Auto-submit when 6 digits entered
        const otpInput = document.querySelector('input[name="code"]');
        otpInput.addEventListener('input', function(e) {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value.length === 6) {
                this.closest('form').submit();
            }
        });
    </script>
</body>
</html>
