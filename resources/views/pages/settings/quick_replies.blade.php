<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Quick Replies - ReplyAI Settings</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script id="tailwind-config">
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
</head>
<body class="bg-[#f6f6f8] dark:bg-background-dark text-slate-900 dark:text-white font-display">

<div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Sidebar Placeholder (Same as Dashboard) -->
    <!-- Sidebar (Full) -->
@include('components.sidebar')

    <main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
        <!-- Header -->
        <header class="hidden lg:flex h-16 items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-2">
                <span class="font-bold text-lg dark:text-white">Quick Replies</span>
            </div>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-primary hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <span class="material-symbols-outlined text-[20px]">add</span> Tambah Template
            </button>
        </header>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6 scroll-smooth">
            <div class="max-w-5xl mx-auto">
                
                @if(session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-500/10 text-green-500 border border-green-500/20 flex items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span>
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 p-4 rounded-lg bg-red-500/10 text-red-500 border border-red-500/20">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-medium border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-4 w-32">Shortcut</th>
                                <th class="px-6 py-4">Pesan</th>
                                <th class="px-6 py-4 w-24">Status</th>
                                <th class="px-6 py-4 w-24 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($quickReplies as $qr)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-6 py-4 font-mono text-xs text-primary bg-primary/5 rounded">/{{ $qr->shortcut ?: '-' }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $qr->message }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $qr->is_active ? 'bg-green-500/10 text-green-500' : 'bg-slate-500/10 text-slate-500' }}">
                                        {{ $qr->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick="editReply({{ $qr->id }}, '{{ $qr->shortcut }}', `{{ $qr->message }}`, {{ $qr->is_active }})" class="p-1.5 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <form action="{{ route('quick-replies.destroy', $qr->id) }}" method="POST" onsubmit="return confirm('Hapus template ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-700">bolt</span>
                                        <p>Belum ada quick reply template.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Add/Edit -->
<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-xl max-w-lg w-full p-6 animate-scale-up">
        <h3 id="modalTitle" class="text-lg font-bold dark:text-white mb-4">Tambah Quick Reply</h3>
        <form id="replyForm" action="{{ route('quick-replies.store') }}" method="POST">
            @csrf
            <div id="methodField"></div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Shortcut (Optional)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">/</span>
                        <input type="text" name="shortcut" id="shortcutInput" placeholder="greeting" class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-slate-700 rounded-lg py-2 pl-6 pr-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary dark:text-white">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Shortcode untuk akses cepat (tanpa spasi).</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pesan Balasan</label>
                    <textarea name="message" id="messageInput" rows="4" required class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-slate-700 rounded-lg p-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary dark:text-white" placeholder="Tulis template balasan di sini..."></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="activeInput" value="1" checked class="rounded border-slate-300 text-primary focus:ring-primary">
                    <label for="activeInput" class="text-sm text-slate-700 dark:text-slate-300">Aktifkan template ini</label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-blue-600 rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeModal() {
        document.getElementById('addModal').classList.add('hidden');
        document.getElementById('replyForm').reset();
        document.getElementById('replyForm').action = "{{ route('quick-replies.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('modalTitle').innerText = 'Tambah Quick Reply';
    }

    function editReply(id, shortcut, message, isActive) {
        document.getElementById('addModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Edit Quick Reply';
        document.getElementById('replyForm').action = "/settings/quick-replies/" + id;
        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        document.getElementById('shortcutInput').value = shortcut || '';
        document.getElementById('messageInput').value = message;
        document.getElementById('activeInput').checked = isActive;
    }
</script>

</body>
</html>
