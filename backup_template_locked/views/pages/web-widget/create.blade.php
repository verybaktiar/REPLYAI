<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - {{ $title }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Theme Configuration -->
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
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
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #282e39; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden antialiased">
<div class="flex h-screen w-full">
    <!-- Sidebar Navigation -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Top Header -->
        <header class="h-16 flex items-center justify-between px-6 lg:px-8 border-b border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <a href="{{ route('web-widgets.index') }}" class="p-2 -ml-2 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-bold text-lg dark:text-white">{{ $title }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Konfigurasi widget chat untuk website Anda</p>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
            <div class="max-w-2xl mx-auto">
                <!-- Form -->
                <form action="{{ route('web-widgets.store') }}" method="POST" class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden shadow-sm">
                    @csrf
                    
                    <div class="p-6 space-y-6">
                        <!-- Widget Name -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Nama Widget <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ old('name') }}"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                   placeholder="Contoh: Website Klinik Utama"/>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Domain -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Domain (Opsional)</label>
                            <input type="text" name="domain" value="{{ old('domain') }}"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                   placeholder="Contoh: klinik-utama.com"/>
                            <p class="text-xs text-slate-500 mt-1">Kosongkan untuk mengizinkan semua domain</p>
                        </div>

                        <!-- Welcome Message -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Pesan Sambutan</label>
                            <textarea name="welcome_message" rows="3"
                                      class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                      placeholder="Halo! Ada yang bisa kami bantu?">{{ old('welcome_message', 'Halo! Ada yang bisa kami bantu?') }}</textarea>
                        </div>

                        <!-- Bot Name -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Nama Bot</label>
                            <input type="text" name="bot_name" value="{{ old('bot_name', 'Bot ReplyAI') }}"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                   placeholder="Bot ReplyAI"/>
                        </div>

                        <!-- Appearance -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Warna Utama</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="primary_color" value="{{ old('primary_color', '#4F46E5') }}"
                                           class="size-10 rounded-lg cursor-pointer border border-slate-700 bg-transparent"/>
                                    <input type="text" id="color-text" value="{{ old('primary_color', '#4F46E5') }}" readonly
                                           class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 dark:text-white text-slate-900 text-sm"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Posisi Widget</label>
                                <select name="position" 
                                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 focus:ring-2 focus:ring-primary/50 focus:border-primary/50">
                                    <option value="bottom-right" {{ old('position') == 'bottom-right' ? 'selected' : '' }}>Kanan Bawah</option>
                                    <option value="bottom-left" {{ old('position') == 'bottom-left' ? 'selected' : '' }}>Kiri Bawah</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-700/50 flex justify-end gap-3">
                        <a href="{{ route('web-widgets.index') }}" 
                           class="px-4 py-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">
                            Batal
                        </a>
                        <button type="submit" 
                                class="flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                            <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                            Buat Widget
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    // Sync color picker with text input
    const colorInput = document.querySelector('input[name="primary_color"]');
    const colorText = document.getElementById('color-text');
    
    colorInput.addEventListener('input', (e) => {
        colorText.value = e.target.value.toUpperCase();
    });
</script>

</body>
</html>
