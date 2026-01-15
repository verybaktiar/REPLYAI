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
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row">

<!-- Sidebar Navigation -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 lg:p-10 pb-20">
        <div class="max-w-[1200px] mx-auto flex flex-col gap-8">
            <!-- Header -->
            <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <h2 class="text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em] text-white">Data Kontak</h2>
                    <p class="text-text-secondary text-base font-normal">Manajemen profil pasien dan riwayat interaksi. Total: <span class="text-white font-medium">{{ $total ?? 0 }}</span> kontak</p>
                </div>
                <!-- Controls -->
                <div class="flex flex-wrap gap-3">
                    <!-- Platform Filter -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-text-secondary uppercase tracking-wider">Platform</label>
                        <select id="platformFilter" onchange="applyFilters()" class="bg-surface-dark border border-border-dark text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-36 p-2.5">
                            <option value="all" {{ ($currentPlatform ?? 'all') == 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="whatsapp" {{ ($currentPlatform ?? '') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="instagram" {{ ($currentPlatform ?? '') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                        </select>
                    </div>
                    <!-- Tag Filter -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-text-secondary uppercase tracking-wider">Tag</label>
                        <select id="tagFilter" onchange="applyFilters()" class="bg-surface-dark border border-border-dark text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-32 p-2.5">
                            <option value="">Semua Tag</option>
                            <option value="VIP" {{ ($currentTag ?? '') == 'VIP' ? 'selected' : '' }}>VIP</option>
                            <option value="BPJS" {{ ($currentTag ?? '') == 'BPJS' ? 'selected' : '' }}>BPJS</option>
                            <option value="New Lead" {{ ($currentTag ?? '') == 'New Lead' ? 'selected' : '' }}>New Lead</option>
                        </select>
                    </div>
                    <!-- Search -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-text-secondary uppercase tracking-wider">Cari</label>
                        <div class="relative">
                            <input type="text" id="searchInput" value="{{ $currentSearch ?? '' }}" placeholder="Nama / No. HP..." 
                                   onkeydown="if(event.key==='Enter')applyFilters()"
                                   class="bg-surface-dark border border-border-dark text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-52 p-2.5 pl-10" />
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">search</span>
                        </div>
                    </div>
                    <!-- Export Button -->
                    <div class="flex flex-col gap-1.5 justify-end">
                        <button onclick="exportContacts()" class="bg-primary hover:bg-blue-600 text-white font-medium rounded-lg text-sm px-4 py-2.5 flex items-center gap-2 h-[42px]">
                            <span class="material-symbols-outlined" style="font-size: 20px;">download</span>
                            Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Contacts Table -->
            <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs md:text-sm text-text-secondary min-w-[600px]">
                        <thead class="bg-[#111722] text-[10px] md:text-xs uppercase font-semibold text-text-secondary">
                            <tr>
                                <th class="px-3 md:px-6 py-3 md:py-4">Nama Profil</th>
                                <th class="px-3 md:px-6 py-3 md:py-4">Telepon</th>
                                <th class="px-3 md:px-6 py-3 md:py-4">Platform</th>
                                <th class="px-3 md:px-6 py-3 md:py-4">Pesan</th>
                                <th class="px-3 md:px-6 py-3 md:py-4">Terakhir Aktif</th>
                                <th class="px-3 md:px-6 py-3 md:py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark">
                            @forelse($contacts as $c)
                                <tr class="hover:bg-[#1f2b40] transition-colors">
                                    <td class="px-3 md:px-6 py-3 md:py-4">
                                        <div class="flex items-center gap-2 md:gap-3">
                                            <div class="size-7 md:size-8 rounded-full bg-slate-700 bg-cover shrink-0" style="background-image: url('{{ $c['avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($c['name']).'&background=374151&color=fff' }}');"></div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-white font-medium truncate">{{ $c['name'] }}</span>
                                                @if(!empty($c['tags']))
                                                    <div class="flex gap-1 flex-wrap mt-1">
                                                        @foreach($c['tags'] as $tag)
                                                            <span class="text-[10px] bg-slate-700 text-slate-300 px-1.5 py-0.5 rounded">{{ $tag }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 md:px-6 py-3 md:py-4 text-white whitespace-nowrap">{{ $c['phone'] }}</td>
                                    <td class="px-3 md:px-6 py-3 md:py-4">
                                        <div class="flex items-center gap-1.5">
                                            @if($c['platform'] === 'whatsapp')
                                                <span class="text-green-500 material-symbols-outlined" style="font-size: 16px;">chat</span> 
                                                <span class="text-green-400 hidden sm:inline">WhatsApp</span>
                                                <span class="text-green-400 sm:hidden">WA</span>
                                            @else
                                                <span class="text-pink-500 material-symbols-outlined" style="font-size: 16px;">photo_camera</span> 
                                                <span class="text-pink-400 hidden sm:inline">Instagram</span>
                                                <span class="text-pink-400 sm:hidden">IG</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 md:px-6 py-3 md:py-4 text-white">{{ $c['messages_count'] ?? 0 }}</td>
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($c['last_active'])->diffForHumans() }}</td>
                                    <td class="px-3 md:px-6 py-3 md:py-4">
                                        @if($c['platform'] === 'whatsapp')
                                            <a href="{{ route('whatsapp.inbox') }}?phone={{ $c['phone'] }}" class="text-primary hover:text-white transition-colors flex items-center gap-1">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">chat</span> Lihat Chat
                                            </a>
                                        @elseif($c['conversation_id'])
                                            <a href="{{ route('inbox', ['conversation_id' => $c['conversation_id']]) }}" class="text-primary hover:text-white transition-colors flex items-center gap-1">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">chat</span> Lihat Chat
                                            </a>
                                        @else
                                            <span class="text-text-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-8 text-center text-text-secondary">Belum ada data kontak.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($hasMore ?? false)
                <div class="p-4 border-t border-border-dark flex justify-center gap-2">
                    @if(($page ?? 1) > 1)
                        <a href="?page={{ $page - 1 }}&platform={{ $currentPlatform ?? 'all' }}&search={{ $currentSearch ?? '' }}&tag={{ $currentTag ?? '' }}" 
                           class="px-4 py-2 bg-surface-dark border border-border-dark rounded-lg text-sm hover:bg-primary/20">← Sebelumnya</a>
                    @endif
                    <span class="px-4 py-2 text-text-secondary">Halaman {{ $page ?? 1 }}</span>
                    <a href="?page={{ ($page ?? 1) + 1 }}&platform={{ $currentPlatform ?? 'all' }}&search={{ $currentSearch ?? '' }}&tag={{ $currentTag ?? '' }}" 
                       class="px-4 py-2 bg-surface-dark border border-border-dark rounded-lg text-sm hover:bg-primary/20">Selanjutnya →</a>
                </div>
                @endif
            </div>

        </div>
    </div>
</main>

<script>
function applyFilters() {
    const platform = document.getElementById('platformFilter').value;
    const tag = document.getElementById('tagFilter').value;
    const search = document.getElementById('searchInput').value;
    
    let url = '{{ route("contacts.index") }}?';
    const params = new URLSearchParams();
    
    if (platform !== 'all') params.append('platform', platform);
    if (tag) params.append('tag', tag);
    if (search) params.append('search', search);
    
    window.location.href = url + params.toString();
}

function exportContacts() {
    const platform = document.getElementById('platformFilter').value;
    let url = '{{ route("contacts.export") }}?';
    if (platform !== 'all') url += 'platform=' + platform;
    window.location.href = url;
}
</script>

</body>
</html>

