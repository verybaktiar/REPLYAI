<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Setup Bisnis Anda - ReplyAI</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-background-dark text-white font-display min-h-screen flex items-center justify-center p-4">
    
<div x-data="{ 
    step: 1, 
    industry: '', 
    businessName: '{{ $user->name }}',
    loading: false 
}" class="w-full max-w-2xl">
    
    {{-- Progress Bar --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-slate-400">Langkah <span x-text="step"></span> dari 3</span>
            <a href="{{ route('onboarding.skip') }}" class="text-sm text-slate-500 hover:text-white transition">Lewati →</a>
        </div>
        <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-primary to-blue-400 transition-all duration-500" 
                 :style="'width: ' + (step * 33.33) + '%'"></div>
        </div>
    </div>

    {{-- Card Container --}}
    <div class="bg-surface-dark rounded-2xl border border-slate-700 p-8 shadow-xl">
        
        {{-- Step 1: Pilih Industri --}}
        <div x-show="step === 1" x-transition>
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-full bg-primary/20 flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">🏢</span>
                </div>
                <h1 class="text-2xl font-bold mb-2">Selamat Datang di ReplyAI! 👋</h1>
                <p class="text-slate-400">Pilih jenis bisnis Anda agar AI bisa menyesuaikan gaya komunikasi</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                @foreach($industries as $key => $ind)
                <button type="button"
                    @click="industry = '{{ $key }}'"
                    :class="industry === '{{ $key }}' ? 'border-primary bg-primary/10' : 'border-slate-700 hover:border-slate-600'"
                    class="p-4 rounded-xl border-2 transition-all text-center">
                    <span class="text-2xl block mb-1">{{ $ind['icon'] }}</span>
                    <span class="text-xs text-slate-300">{{ $ind['label'] }}</span>
                </button>
                @endforeach
            </div>

            <button @click="step = 2" :disabled="!industry"
                :class="industry ? 'bg-primary hover:bg-blue-600' : 'bg-slate-700 cursor-not-allowed'"
                class="w-full py-3 rounded-xl font-semibold transition-colors">
                Lanjut →
            </button>
        </div>

        {{-- Step 2: Nama Bisnis --}}
        <div x-show="step === 2" x-transition>
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-full bg-purple-500/20 flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">✏️</span>
                </div>
                <h1 class="text-2xl font-bold mb-2">Nama Bisnis Anda</h1>
                <p class="text-slate-400">AI akan menyebut nama ini saat berkomunikasi dengan pelanggan</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-300 mb-2">Nama Bisnis / Brand</label>
                <input type="text" x-model="businessName" 
                    placeholder="Contoh: Toko Baju Cantik"
                    class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-xl focus:border-primary focus:ring-1 focus:ring-primary text-white placeholder-slate-500">
            </div>

            <div class="flex gap-3">
                <button @click="step = 1" class="flex-1 py-3 rounded-xl border border-slate-700 hover:bg-slate-800 transition-colors">
                    ← Kembali
                </button>
                <button @click="step = 3" :disabled="!businessName.trim()"
                    :class="businessName.trim() ? 'bg-primary hover:bg-blue-600' : 'bg-slate-700 cursor-not-allowed'"
                    class="flex-1 py-3 rounded-xl font-semibold transition-colors">
                    Lanjut →
                </button>
            </div>
        </div>

        {{-- Step 3: Konfirmasi --}}
        <div x-show="step === 3" x-transition>
            <form action="{{ route('onboarding.store') }}" method="POST" @submit="loading = true">
                @csrf
                <input type="hidden" name="business_industry" :value="industry">
                <input type="hidden" name="business_name" :value="businessName">

                <div class="text-center mb-8">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center mx-auto mb-4 animate-pulse">
                        <span class="text-5xl">🎉</span>
                    </div>
                    <h1 class="text-2xl font-bold mb-2">Siap Memulai!</h1>
                    <p class="text-slate-400">Konfirmasi data bisnis Anda</p>
                </div>

                <div class="bg-slate-800/50 rounded-xl p-4 mb-6 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Nama Bisnis</span>
                        <span class="font-medium" x-text="businessName"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Jenis Industri</span>
                        <span class="font-medium" x-text="industry.charAt(0).toUpperCase() + industry.slice(1)"></span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="step = 2" class="flex-1 py-3 rounded-xl border border-slate-700 hover:bg-slate-800 transition-colors">
                        ← Kembali
                    </button>
                    <button type="submit" :disabled="loading"
                        class="flex-1 py-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 font-semibold transition-all">
                        <span x-show="!loading">🚀 Mulai Sekarang</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Footer --}}
    <p class="text-center text-slate-500 text-sm mt-6">
        Butuh bantuan? <a href="#" class="text-primary hover:underline">Hubungi Support</a>
    </p>
</div>

</body>
</html>
