<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Upgrade Paket - ReplyAI</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
<body class="bg-background-dark text-white font-display antialiased">

    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-lg w-full">
            <div class="bg-surface-dark rounded-2xl p-8 border border-slate-700 text-center">
                
                <!-- Lock Icon -->
                <div class="w-20 h-20 mx-auto mb-6 bg-yellow-500/20 rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-500 text-4xl">lock</span>
                </div>
                
                <!-- Title -->
                <h1 class="text-2xl font-bold mb-4">Upgrade Diperlukan</h1>
                
                <!-- Message -->
                <p class="text-slate-400 mb-6">
                    @if(session('upgrade_prompt'))
                        {{ session('upgrade_prompt') }}
                    @else
                        Fitur <strong class="text-white">{{ $featureName ?? 'ini' }}</strong> tidak tersedia di paket Anda saat ini.
                        Upgrade ke paket yang lebih tinggi untuk mengakses fitur ini.
                    @endif
                </p>

                <!-- Current Plan Info -->
                @auth
                @php $currentPlan = auth()->user()->getPlan(); @endphp
                @if($currentPlan)
                <div class="bg-background-dark rounded-xl p-4 mb-6 text-left">
                    <div class="text-sm text-slate-400 mb-1">Paket Anda saat ini:</div>
                    <div class="font-bold text-lg">{{ $currentPlan->name }}</div>
                </div>
                @endif
                @endauth

                <!-- Feature Benefits -->
                @if(isset($feature))
                <div class="text-left mb-6">
                    <div class="text-sm font-medium text-slate-300 mb-3">Dengan upgrade, Anda bisa:</div>
                    <ul class="space-y-2 text-sm text-slate-400">
                        @switch($feature)
                            @case('broadcast')
                            @case('broadcasts')
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Kirim pesan ke banyak kontak sekaligus
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Jadwalkan broadcast otomatis
                                </li>
                                @break
                            @case('sequences')
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Buat follow-up otomatis bertahap
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Nurturing leads secara otomatis
                                </li>
                                @break
                            @case('web_widgets')
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Embed chat widget di website Anda
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Tangkap leads langsung dari website
                                </li>
                                @break
                            @case('api_access')
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Akses API untuk integrasi custom
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Hubungkan dengan sistem Anda
                                </li>
                                @break
                            @default
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Akses ke semua fitur premium
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                    Kuota lebih besar
                                </li>
                        @endswitch
                    </ul>
                </div>
                @endif

                <!-- CTA Buttons -->
                <div class="space-y-3">
                    <a href="{{ route('pricing') }}" class="block w-full py-4 bg-primary hover:bg-primary/90 rounded-xl font-bold text-center transition">
                        Lihat Paket Tersedia
                    </a>
                    <a href="{{ route('dashboard') }}" class="block w-full py-3 bg-slate-800 hover:bg-slate-700 rounded-xl font-medium text-center transition">
                        Kembali ke Dashboard
                    </a>
                </div>
                
            </div>
        </div>
    </div>

</body>
</html>
