<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - REPLYAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
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
                        "surface-light": "#1c2537",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        .animate-pulse-slow {
            animation: pulse-slow 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-background-dark text-white font-display antialiased min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full text-center">
        {{-- Icon --}}
        <div class="relative mb-8">
            <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full"></div>
            <div class="relative w-32 h-32 mx-auto bg-surface-dark rounded-full border border-slate-700 flex items-center justify-center animate-float">
                <span class="material-symbols-outlined text-6xl text-primary">construction</span>
            </div>
            {{-- Gear decorations --}}
            <div class="absolute top-4 -left-4 text-slate-600 animate-spin" style="animation-duration: 8s;">
                <span class="material-symbols-outlined text-3xl">settings</span>
            </div>
            <div class="absolute bottom-4 -right-4 text-slate-600 animate-spin" style="animation-duration: 12s; animation-direction: reverse;">
                <span class="material-symbols-outlined text-4xl">settings</span>
            </div>
        </div>

        {{-- Title --}}
        <h1 class="text-4xl font-bold mb-4">Under Maintenance</h1>
        
        {{-- Message --}}
        <p class="text-slate-400 text-lg mb-8">
            {{ $message ?? 'We are currently performing maintenance. Please check back soon.' }}
        </p>

        {{-- Countdown Timer --}}
        @if($countdownEnabled && $countdownEnd)
        <div class="mb-8">
            <p class="text-sm text-slate-500 mb-4">We'll be back in:</p>
            <div class="grid grid-cols-4 gap-3" id="countdown">
                <div class="bg-surface-dark rounded-xl border border-slate-700 p-4">
                    <div class="text-2xl font-bold text-primary" id="days">00</div>
                    <div class="text-xs text-slate-500 uppercase mt-1">Days</div>
                </div>
                <div class="bg-surface-dark rounded-xl border border-slate-700 p-4">
                    <div class="text-2xl font-bold text-primary" id="hours">00</div>
                    <div class="text-xs text-slate-500 uppercase mt-1">Hours</div>
                </div>
                <div class="bg-surface-dark rounded-xl border border-slate-700 p-4">
                    <div class="text-2xl font-bold text-primary" id="minutes">00</div>
                    <div class="text-xs text-slate-500 uppercase mt-1">Minutes</div>
                </div>
                <div class="bg-surface-dark rounded-xl border border-slate-700 p-4">
                    <div class="text-2xl font-bold text-primary" id="seconds">00</div>
                    <div class="text-xs text-slate-500 uppercase mt-1">Seconds</div>
                </div>
            </div>
            <p class="text-xs text-slate-600 mt-3">
                Estimated completion: {{ \Carbon\Carbon::parse($countdownEnd)->format('F j, Y g:i A') }}
            </p>
        </div>
        @endif

        {{-- Progress Bar Animation --}}
        <div class="mb-8">
            <div class="h-1 bg-surface-light rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full animate-pulse-slow" style="width: 60%;"></div>
            </div>
            <p class="text-xs text-slate-500 mt-2">Working on improvements...</p>
        </div>

        {{-- Contact / Help --}}
        <div class="text-sm text-slate-500">
            <p>Need urgent assistance?</p>
            <a href="mailto:support@replyai.com" class="text-primary hover:underline inline-flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined text-sm">mail</span>
                Contact Support
            </a>
        </div>

        {{-- Admin Login Link --}}
        <div class="mt-8 pt-6 border-t border-slate-800">
            <a href="{{ route('admin.login') }}" class="text-xs text-slate-600 hover:text-slate-400 transition">
                Admin Login
            </a>
        </div>
    </div>

    @if($countdownEnabled && $countdownEnd)
    <script>
        function updateCountdown() {
            const endTime = new Date('{{ $countdownEnd }}').getTime();
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                document.getElementById('days').textContent = '00';
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
    @endif
</body>
</html>
