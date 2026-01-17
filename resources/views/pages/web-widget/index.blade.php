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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
<div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Sidebar Navigation -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
        <!-- Top Header -->
        <header class="hidden lg:flex h-16 items-center justify-between px-6 lg:px-8 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="p-2 -ml-2 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-bold text-lg dark:text-white">Web Chat Widget</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kelola widget chat untuk website WordPress</p>
                </div>
            </div>
            <div class="flex items-center gap-4 ml-auto">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-green-500/10 rounded-full border border-green-500/20">
                    <div class="size-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-bold text-green-500">ONLINE</span>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
            <div class="max-w-6xl mx-auto flex flex-col gap-8">
                <!-- Page Heading Section -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl md:text-3xl font-black tracking-tight dark:text-white text-slate-900">Chat di Website</h2>
                            @include('components.page-help', [
                                'title' => 'Chat di Website',
                                'description' => 'Widget chat untuk dipasang di website WordPress Anda.',
                                'tips' => [
                                    'Klik "Buat Widget Baru" untuk membuat widget',
                                    'Copy kode embed dan paste ke website',
                                    'Atur warna dan nama bot sesuai brand',
                                    'Chat dari website akan masuk ke inbox'
                                ]
                            ])
                        </div>
                        <p class="text-slate-500 dark:text-slate-400">Buat dan kelola chat widget untuk integrasi WordPress Anda.</p>
                    </div>
                    <a href="{{ route('web-widgets.create') }}" 
                       class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                        <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                        Buat Widget Baru
                    </a>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Widgets Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($widgets as $widget)
                        <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:border-primary dark:hover:border-primary transition-colors shadow-sm">
                            <!-- Widget Header -->
                            <div class="p-5 border-b border-slate-100 dark:border-slate-700/50">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-lg flex items-center justify-center text-white shadow-lg" style="background-color: {{ $widget->primary_color }}">
                                            <span class="material-symbols-outlined" style="font-size: 20px;">chat</span>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold dark:text-white text-slate-900">{{ $widget->name }}</h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $widget->domain ?: 'Semua domain' }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $widget->is_active ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-slate-500/10 text-slate-500 border border-slate-500/20' }}">
                                        {{ $widget->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">forum</span>
                                        {{ $widget->conversations_count }} chat
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">smart_toy</span>
                                        {{ $widget->bot_name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Widget Actions -->
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/30">
                                <div class="flex items-center gap-2">
                                    <button onclick="showEmbedCode('{{ $widget->api_key }}', '{{ addslashes($widget->embed_code) }}')" 
                                            class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-primary/10 text-primary border border-primary/30 rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">code</span>
                                        Embed Code
                                    </button>
                                    <a href="{{ route('web-widgets.edit', $widget) }}" 
                                       class="flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                        <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                                    </a>
                                    <form action="{{ route('web-widgets.destroy', $widget) }}" method="POST" class="inline" onsubmit="return confirm('Hapus widget ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center justify-center gap-1.5 px-3 py-2 bg-red-500/10 text-red-500 border border-red-500/30 rounded-lg text-xs font-medium hover:bg-red-500/20 transition-colors">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                            <div class="size-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                <span class="material-symbols-outlined text-slate-400 text-4xl">widgets</span>
                            </div>
                            <h3 class="dark:text-white text-slate-900 font-bold text-lg mb-2">Belum Ada Widget</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 max-w-sm">Buat widget pertama Anda untuk mulai menerima chat dari website WordPress.</p>
                            <a href="{{ route('web-widgets.create') }}" 
                               class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                                Buat Widget Pertama
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- How It Works Section -->
                <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                    <h2 class="text-lg font-bold dark:text-white text-slate-900 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">help</span>
                        Cara Menggunakan Widget
                    </h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="flex gap-3">
                            <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-sm">1</div>
                            <div>
                                <h3 class="font-medium dark:text-white text-slate-900 text-sm mb-1">Buat Widget</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Klik "Buat Widget Baru" dan isi nama serta konfigurasi widget.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-sm">2</div>
                            <div>
                                <h3 class="font-medium dark:text-white text-slate-900 text-sm mb-1">Copy Embed Code</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Salin kode JavaScript yang disediakan setelah widget dibuat.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 font-bold text-sm">3</div>
                            <div>
                                <h3 class="font-medium dark:text-white text-slate-900 text-sm mb-1">Paste ke WordPress</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Tambahkan kode ke footer WordPress atau gunakan plugin custom HTML.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Embed Code Modal -->
<div id="embed-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70">
    <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-700 w-full max-w-lg mx-4 overflow-hidden shadow-2xl">
        <div class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="font-semibold dark:text-white text-slate-900">Embed Code</h3>
            <button onclick="closeEmbedModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-4">
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">Salin kode berikut dan paste sebelum tag <code class="text-primary font-mono bg-primary/10 px-1 rounded">&lt;/body&gt;</code> di website Anda:</p>
            <div class="relative">
                <pre id="embed-code-content" class="bg-slate-100 dark:bg-slate-800 rounded-lg p-4 text-xs text-green-600 dark:text-green-400 overflow-x-auto border border-slate-200 dark:border-slate-700 font-mono"></pre>
                <button onclick="copyEmbedCode()" class="absolute top-2 right-2 px-2 py-1 bg-primary/20 text-primary rounded text-xs hover:bg-primary/30 transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 14px;">content_copy</span>
                    Copy
                </button>
            </div>
            <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg text-xs text-amber-700 dark:text-amber-400 flex items-start gap-2">
                <span class="material-symbols-outlined" style="font-size: 16px;">info</span>
                <span>Untuk WordPress, gunakan plugin seperti "Insert Headers and Footers" atau tambahkan melalui Theme Editor di <code class="font-mono">footer.php</code>.</span>
            </div>
        </div>
    </div>
</div>

<script>
    let currentEmbedCode = '';
    
    function showEmbedCode(apiKey, embedCode) {
        currentEmbedCode = embedCode.replace(/\\'/g, "'");
        document.getElementById('embed-code-content').textContent = currentEmbedCode;
        document.getElementById('embed-modal').classList.remove('hidden');
    }
    
    function closeEmbedModal() {
        document.getElementById('embed-modal').classList.add('hidden');
    }
    
    function copyEmbedCode() {
        navigator.clipboard.writeText(currentEmbedCode).then(() => {
            alert('Embed code berhasil disalin!');
        });
    }
    
    // Close modal on escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeEmbedModal();
    });
    
    // Close modal on backdrop click
    document.getElementById('embed-modal').addEventListener('click', (e) => {
        if (e.target.id === 'embed-modal') closeEmbedModal();
    });
</script>

</body>
</html>
