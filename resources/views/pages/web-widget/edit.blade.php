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
        <header class="h-16 flex items-center justify-between px-6 lg:px-8 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <a href="{{ route('web-widgets.index') }}" class="p-2 -ml-2 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-bold text-lg dark:text-white">{{ $title }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ubah konfigurasi widget "{{ $widget->name }}"</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $widget->is_active ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-slate-500/10 text-slate-500 border border-slate-500/20' }}">
                    {{ $widget->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
            <div class="max-w-2xl mx-auto flex flex-col gap-6">
                <!-- Form -->
                <form action="{{ route('web-widgets.update', $widget) }}" method="POST" class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 space-y-6">
                        <!-- API Key (Read Only) -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">API Key</label>
                            <div class="flex gap-2">
                                <input type="text" value="{{ $widget->api_key }}" readonly
                                       class="flex-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 text-slate-500 dark:text-slate-400 font-mono text-sm"/>
                                <button type="button" onclick="copyApiKey()" 
                                        class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">content_copy</span>
                                </button>
                            </div>
                        </div>

                        <!-- Widget Name -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Nama Widget <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required value="{{ old('name', $widget->name) }}"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                   placeholder="Contoh: Website Klinik Utama"/>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Domain -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Domain (Opsional)</label>
                            <input type="text" name="domain" value="{{ old('domain', $widget->domain) }}"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                   placeholder="Contoh: klinik-utama.com"/>
                            <p class="text-xs text-slate-500 mt-1">Kosongkan untuk mengizinkan semua domain</p>
                        </div>

                        <!-- Welcome Message -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Pesan Sambutan</label>
                            <textarea name="welcome_message" rows="3"
                                      class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                      placeholder="Halo! Ada yang bisa kami bantu?">{{ old('welcome_message', $widget->welcome_message) }}</textarea>
                        </div>

                        <!-- Bot Name -->
                        <div>
                            <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Nama Bot</label>
                            <input type="text" name="bot_name" value="{{ old('bot_name', $widget->bot_name) }}"
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                   placeholder="Bot ReplyAI"/>
                        </div>

                        <!-- Appearance -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Warna Utama</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="primary_color" value="{{ old('primary_color', $widget->primary_color) }}"
                                           class="size-10 rounded-lg cursor-pointer border border-slate-200 dark:border-slate-700 bg-transparent"/>
                                    <input type="text" id="color-text" value="{{ old('primary_color', $widget->primary_color) }}" readonly
                                           class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2.5 dark:text-white text-slate-900 text-sm"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Posisi Widget</label>
                                <select name="position" 
                                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 focus:ring-2 focus:ring-primary/50 focus:border-primary/50">
                                    <option value="bottom-right" {{ old('position', $widget->position) == 'bottom-right' ? 'selected' : '' }}>Kanan Bawah</option>
                                    <option value="bottom-left" {{ old('position', $widget->position) == 'bottom-left' ? 'selected' : '' }}>Kiri Bawah</option>
                                </select>
                            </div>
                        </div>

                        <!-- Status Toggle -->
                        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700/50">
                            <div>
                                <h4 class="text-sm font-medium dark:text-white text-slate-900">Status Widget</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Nonaktifkan untuk menghentikan widget sementara</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $widget->is_active ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-300 dark:bg-slate-700 rounded-full peer peer-checked:bg-primary transition-colors peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-200 dark:border-slate-700/50 flex justify-between">
                        <form action="{{ route('web-widgets.destroy', $widget) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus widget ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 text-red-500 hover:text-red-600 transition-colors">
                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                Hapus Widget
                            </button>
                        </form>
                        
                        <div class="flex gap-3">
                            <a href="{{ route('web-widgets.index') }}" 
                               class="px-4 py-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                                <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Embed Code Section -->
                <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                    <h3 class="font-semibold dark:text-white text-slate-900 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">code</span>
                        Embed Code
                    </h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">Salin kode berikut dan paste sebelum tag <code class="text-primary font-mono bg-primary/10 px-1 rounded">&lt;/body&gt;</code> di website Anda:</p>
                    <div class="relative">
                        <pre id="embed-code" class="bg-slate-100 dark:bg-slate-800 rounded-lg p-4 text-xs text-green-600 dark:text-green-400 overflow-x-auto border border-slate-200 dark:border-slate-700 font-mono">{{ $widget->embed_code }}</pre>
                        <button onclick="copyEmbedCode()" class="absolute top-2 right-2 px-2 py-1 bg-primary/20 text-primary rounded text-xs hover:bg-primary/30 transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined" style="font-size: 14px;">content_copy</span>
                            Copy
                        </button>
                    </div>
                </div>
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
    
    function copyApiKey() {
        navigator.clipboard.writeText('{{ $widget->api_key }}').then(() => {
            alert('API Key berhasil disalin!');
        });
    }
    
    function copyEmbedCode() {
        const code = document.getElementById('embed-code').textContent;
        navigator.clipboard.writeText(code).then(() => {
            alert('Embed code berhasil disalin!');
        });
    }
</script>

</body>
</html>
