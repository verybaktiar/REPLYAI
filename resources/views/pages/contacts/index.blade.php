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
@include('components.sidebar')

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
                                    <td class="px-6 py-4 text-white">{{ $c->messages_count ?? 0 }}</td>
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
