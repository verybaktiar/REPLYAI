<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Buat Tiket Support</title>
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
<body class="bg-background-dark text-white font-display antialiased overflow-hidden">
<div class="flex flex-col lg:flex-row h-screen w-full">
    @include('components.sidebar')

    <main class="flex-1 flex flex-col h-full overflow-auto relative pt-14 lg:pt-0">
        <header class="hidden lg:flex h-16 items-center px-6 lg:px-8 border-b border-slate-800 bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <a href="{{ route('support.index') }}" class="p-2 -ml-2 text-slate-400 hover:text-primary">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-bold text-lg">Buat Tiket Baru</h1>
                    <p class="text-xs text-slate-400">Ceritakan masalah Anda</p>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-auto p-6 lg:p-8">
            <div class="max-w-2xl mx-auto">
                
                <form action="{{ route('support.store') }}" method="POST" enctype="multipart/form-data" class="bg-surface-dark rounded-2xl p-6 border border-slate-700">
                    @csrf
                    
                    <div class="mb-5">
                        <label class="block text-sm font-medium mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih kategori...</option>
                            @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium mb-2">Judul <span class="text-red-500">*</span></label>
                        <input type="text" name="subject" required placeholder="Contoh: Bot tidak merespon"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary">
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium mb-2">Deskripsi <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="5" required placeholder="Jelaskan masalah Anda..."
                                  class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white placeholder:text-slate-500 focus:border-primary focus:ring-primary resize-none"></textarea>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium mb-2">Prioritas</label>
                        <select name="priority" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white focus:border-primary focus:ring-primary">
                            @foreach($priorities as $key => $label)
                            <option value="{{ $key }}" {{ $key === 'medium' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-2">Screenshot (opsional)</label>
                        <input type="file" name="attachments[]" accept="image/*" multiple
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-slate-700 text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-primary file:text-white file:cursor-pointer">
                    </div>

                    <button type="submit" class="w-full py-4 rounded-xl bg-primary text-white font-bold text-lg hover:bg-primary/90 transition">
                        Kirim Tiket
                    </button>
                </form>

            </div>
        </div>
    </main>
</div>
</body>
</html>
