<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>CRM & Customer Data - REPLYAI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
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
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111722; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #324467; border-radius: 10px; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex">

<!-- Sidebar Navigation -->
<aside class="hidden lg:flex flex-col w-72 h-full bg-[#111722] border-r border-[#232f48] shrink-0 fixed lg:static top-0 bottom-0 left-0 z-40">
    <!-- Brand -->
    <div class="flex items-center gap-3 px-6 py-6 mb-2">
        <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 shadow-lg relative" style='background-image: url("https://ui-avatars.com/api/?name=Reply+AI&background=0D8ABC&color=fff");'></div>
        <div>
            <h1 class="text-base font-bold leading-none text-white">ReplyAI Admin</h1>
            <p class="text-xs text-[#92a4c9] mt-1">RS PKU Solo Bot</p>
        </div>
    </div>
    <!-- Navigation Links -->
    <nav class="flex flex-col gap-1 flex-1 overflow-y-auto px-4">
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('dashboard') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('dashboard') }}">
            <span class="material-symbols-outlined text-[24px]">grid_view</span>
            <span class="text-sm font-medium">Dashboard</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('analytics*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('analytics.index') }}">
            <span class="material-symbols-outlined text-[24px]">pie_chart</span>
            <span class="text-sm font-medium">Analisis & Laporan</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('contacts*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('contacts.index') }}">
            <span class="material-symbols-outlined text-[24px]">groups</span>
            <span class="text-sm font-medium">Data Kontak (CRM)</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('inbox*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('inbox') }}">
            <span class="material-symbols-outlined text-[24px]">chat_bubble</span>
            <span class="text-sm font-medium">Kotak Masuk</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('rules*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('rules.index') }}">
            <span class="material-symbols-outlined text-[24px]">smart_toy</span>
            <span class="text-sm font-medium">Manajemen Bot</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('kb*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('kb.index') }}">
            <span class="material-symbols-outlined text-[24px]">menu_book</span>
            <span class="text-sm font-medium">Knowledge Base</span>
        </a>

        <!-- New Links Scaffolding -->
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group hover:text-white hover:bg-[#232f48] text-[#92a4c9] opacity-50 cursor-not-allowed" href="#" title="Coming Soon">
            <span class="material-symbols-outlined text-[24px]">science</span>
            <span class="text-sm font-medium">Simulator</span>
            <span class="ml-auto text-[10px] bg-gray-700 px-1 rounded text-gray-300">Soon</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group hover:text-white hover:bg-[#232f48] text-[#92a4c9] opacity-50 cursor-not-allowed" href="#" title="Coming Soon">
            <span class="material-symbols-outlined text-[24px]">settings</span>
            <span class="text-sm font-medium">Settings (Hours)</span>
             <span class="ml-auto text-[10px] bg-gray-700 px-1 rounded text-gray-300">Soon</span>
        </a>

        <div class="mt-4 mb-2 px-3">
            <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">System</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('logs*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('logs.index') }}">
            <span class="material-symbols-outlined text-[24px]">history</span>
            <span class="text-sm font-medium">Log Aktivitas</span>
        </a>
    </nav>
    <!-- User Profile -->
    <div class="border-t border-[#232f48] p-4">
            <div class="p-3 rounded-lg bg-[#232f48]/50 flex items-center gap-3">
            <div class="size-8 rounded-full bg-gradient-to-tr from-purple-500 to-primary flex items-center justify-center text-xs font-bold text-white">DM</div>
            <div class="flex flex-col overflow-hidden">
                <p class="text-white text-sm font-medium truncate">Admin</p>
                <p class="text-[#92a4c9] text-xs truncate">admin@rspkusolo.com</p>
            </div>
            <button class="ml-auto text-[#92a4c9] hover:text-white">
                <span class="material-symbols-outlined text-[20px]">logout</span>
            </button>
        </div>
    </div>
</aside>

<main class="flex-1 flex flex-col h-full overflow-hidden relative">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6 lg:p-10 pb-20">
        <div class="max-w-[1200px] mx-auto flex flex-col gap-8">
            <!-- Header -->
            <div class="flex justify-between items-end">
                <div class="flex flex-col gap-2">
                    <h2 class="text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em] text-white">Data Kontak</h2>
                    <p class="text-text-secondary text-base font-normal">Manajemen profil pasien dan riwayat interaksi.</p>
                </div>
                <!-- Controls -->
                <div class="flex gap-3">
                     <form action="" method="GET" class="relative flex gap-2">
                        <div class="relative">
                             <select name="tag" onchange="this.form.submit()" class="bg-surface-dark border border-border-dark text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-40 p-2.5">
                                <option value="">Semua Tag</option>
                                <option value="VIP" {{ request('tag') == 'VIP' ? 'selected' : '' }}>VIP</option>
                                <option value="BPJS" {{ request('tag') == 'BPJS' ? 'selected' : '' }}>BPJS</option>
                                <option value="New Lead" {{ request('tag') == 'New Lead' ? 'selected' : '' }}>New Lead</option>
                            </select>
                        </div>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / No. HP..." class="bg-surface-dark border border-border-dark text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-64 p-2.5 pl-10" />
                             <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">search</span>
                        </div>
                    </form>
                    <button class="bg-primary hover:bg-blue-600 text-white font-medium rounded-lg text-sm px-4 py-2.5 flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 20px;">upload</span> Import
                    </button>
                 </div>
            </div>

            <!-- Contacts Table -->
             <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-text-secondary">
                        <thead class="bg-[#111722] text-xs uppercase font-semibold text-text-secondary">
                            <tr>
                                <th class="px-6 py-4">Nama Profil</th>
                                <th class="px-6 py-4">Info Kontak</th>
                                <th class="px-6 py-4">Platform</th>
                                <th class="px-6 py-4">Total Pesan</th>
                                <th class="px-6 py-4">Terakhir Aktif</th>
                                <th class="px-6 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark">
                            @forelse($contacts as $c)
                                <tr class="hover:bg-[#1f2b40] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="size-8 rounded-full bg-slate-700 bg-cover" style="background-image: url('{{ $c->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($c->display_name).'&background=374151&color=fff' }}');"></div>
                                            <span class="text-white font-medium">{{ $c->display_name ?? 'Tanpa Nama' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-white">{{ $c->name }}</span> <!-- NIK/Phone usually stored here -->
                                             <div class="flex gap-1 flex-wrap">
                                                @if($c->tags && is_array($c->tags))
                                                    @foreach($c->tags as $tag)
                                                        <span class="text-[10px] bg-slate-700 text-slate-300 px-1.5 py-0.5 rounded">{{ $tag }}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                             @if(str_contains(strtolower($c->source), 'whatsapp'))
                                                <span class="text-green-500 material-symbols-outlined" style="font-size: 18px;">chat</span> WhatsApp
                                             @else
                                                <span class="text-pink-500 material-symbols-outlined" style="font-size: 18px;">photo_camera</span> Instagram
                                             @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-white">n/a</td> <!-- Need relationship count -->
                                    <td class="px-6 py-4">{{ $c->updated_at->diffForHumans() }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('inbox', ['conversation_id' => $c->id]) }}" class="text-primary hover:text-white transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined" style="font-size: 18px;">chat</span> Lihat Chat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-8 text-center text-text-secondary">Belum ada data kontak.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 @if($contacts->hasPages())
                <div class="p-4 border-t border-border-dark">
                    {{ $contacts->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</main>
</body>
</html>
