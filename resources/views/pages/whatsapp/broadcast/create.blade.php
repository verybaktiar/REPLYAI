<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>New Broadcast - REPLYAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "whatsapp": "#25D366",
                        "background-dark": "#111722",
                        "surface-dark": "#192233",
                        "border-dark": "#324467",
                        "text-secondary": "#92a4c9",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row">
    
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto pt-14 lg:pt-0">
        
        <div class="max-w-3xl mx-auto w-full p-8">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('whatsapp.broadcast.index') }}" class="p-2 hover:bg-white/5 rounded-full text-text-secondary">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="text-2xl font-bold">New Broadcast Campaign</h1>
            </div>

            <form action="{{ route('whatsapp.broadcast.store') }}" method="POST" enctype="multipart/form-data" x-data="{ targetType: 'all_contacts' }">
                @csrf
                
                <div class="space-y-6">
                    <!-- Section 1: Campaign Info -->
                    <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-primary text-xs flex items-center justify-center">1</span>
                            Campaign Details
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-text-secondary mb-1">Campaign Title</label>
                                <input type="text" name="title" required placeholder="Ex: Promo Akhir Tahun" 
                                    class="w-full bg-[#111722] border border-border-dark rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-text-secondary mb-1">Message Content</label>
                                <textarea name="message" rows="6" placeholder="Halo! Kami ada promo menarik..." 
                                    class="w-full bg-[#111722] border border-border-dark rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                                <p class="text-xs text-text-secondary mt-1">Dukung format WhatsApp: *Bold*, _Italic_, ~Strikethrough~</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-text-secondary mb-1">Media Attachment (Optional)</label>
                                <input type="file" name="file" accept="image/*,video/*,application/pdf"
                                    class="w-full bg-[#111722] border border-border-dark rounded-lg text-sm text-text-secondary file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-white/5 file:text-white hover:file:bg-white/10">
                                <p class="text-xs text-text-secondary mt-1">Max 10MB. Images, Videos, or PDF.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Targets -->
                    <div class="bg-surface-dark border border-border-dark p-6 rounded-xl">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-primary text-xs flex items-center justify-center">2</span>
                            Select Targets
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <label class="block cursor-pointer">
                                <input type="radio" name="target_type" value="all_contacts" class="peer sr-only" x-model="targetType">
                                <div class="p-4 rounded-xl border border-border-dark bg-[#111722] peer-checked:border-primary peer-checked:bg-primary/10 transition-all h-full">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-purple-400">groups</span>
                                        <div>
                                            <span class="block font-semibold">All Contacts</span>
                                            <span class="text-xs text-text-secondary">{{ $contactsCount }} contacts available</span>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="block cursor-pointer">
                                <input type="radio" name="target_type" value="manual" class="peer sr-only" x-model="targetType">
                                <div class="p-4 rounded-xl border border-border-dark bg-[#111722] peer-checked:border-primary peer-checked:bg-primary/10 transition-all h-full">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-orange-400">edit_note</span>
                                        <div>
                                            <span class="block font-semibold">Manual Input</span>
                                            <span class="text-xs text-text-secondary">Copy-paste phone numbers</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div x-show="targetType === 'manual'" x-transition>
                            <label class="block text-sm font-medium text-text-secondary mb-1">Phone Numbers</label>
                            <textarea name="manual_numbers" rows="5" placeholder="628123456789&#10;628198765432" 
                                class="w-full bg-[#111722] border border-border-dark rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                            <p class="text-xs text-text-secondary mt-1">Pisahkan dengan baris baru (Enter). Gunakan kode negara (e.g. 62).</p>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-primary hover:bg-blue-600 text-white px-8 py-3 rounded-lg font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined">send</span>
                            Start Broadcast
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
