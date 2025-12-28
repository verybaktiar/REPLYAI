<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>REPLYAI - Kotak Masuk</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                        "surface-dark": "#1a202c", 
                        "border-dark": "#2d3748",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem", 
                        "md": "0.375rem",
                        "lg": "0.5rem", 
                        "xl": "0.75rem", 
                    },
                },
            },
        }
    </script>
    <!-- Google Fonts & Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #2d3748; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #4a5568; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden h-screen w-screen flex">
    
<!-- Sidebar -->
<!-- Sidebar Navigation -->
<!-- Sidebar Navigation -->
<!-- Sidebar Navigation -->
<aside class="hidden lg:flex flex-col w-72 h-full bg-[#111722] border-r border-[#232f48] shrink-0 z-20">
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
            @if(isset($conversations) && $conversations instanceof \Illuminate\Database\Eloquent\Collection && $conversations->count() > 0)
                <span class="ml-auto bg-white/10 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md text-center min-w-[20px]">{{ $conversations->count() }}</span>
            @elseif(isset($stats['pending_inbox']) && $stats['pending_inbox'] > 0)
                 <span class="ml-auto bg-white/10 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md text-center min-w-[20px]">{{ $stats['pending_inbox'] }}</span>
            @endif
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('rules*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('rules.index') }}">
            <span class="material-symbols-outlined text-[24px]">smart_toy</span>
            <span class="text-sm font-medium">Manajemen Bot</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('kb*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('kb.index') }}">
            <span class="material-symbols-outlined text-[24px]">menu_book</span>
            <span class="text-sm font-medium">Knowledge Base</span>
        </a>

        <!-- New Links -->
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('simulator*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('simulator.index') }}">
            <span class="material-symbols-outlined text-[24px]">science</span>
            <span class="text-sm font-medium">Simulator</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('settings*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('settings.index') }}">
            <span class="material-symbols-outlined text-[24px]">settings</span>
            <span class="text-sm font-medium">Settings (Hours)</span>
        </a>

        <div class="mt-4 mb-2 px-3">
            <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">System</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('logs*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('logs.index') }}">
            <span class="material-symbols-outlined text-[24px]">history</span>
            <span class="text-sm font-medium">Log Aktivitas</span>
        </a>
    </nav>
    <!-- User Profile (Bottom) -->
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

<!-- Main Content Area -->
<main class="flex flex-1 overflow-hidden h-full relative">
    
    <!-- Conversation List (Middle Column) -->
    <div class="w-full md:w-[380px] flex flex-col border-r border-white/5 bg-[#111722] shrink-0 h-full z-10 {{ $selectedId ? 'hidden md:flex' : 'flex' }}">
        <!-- Header -->
        <div class="p-5 pb-2">
            <div class="flex justify-between items-start mb-1">
                <h2 class="text-2xl font-bold text-white tracking-tight">Kotak Masuk</h2>
                <button class="text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">edit_square</span>
                </button>
            </div>
            
            <!-- Search Filter Client Side -->
            <div class="relative mb-4 mt-4">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" style="font-size: 20px;">search</span>
                <input id="conv-search" class="w-full bg-[#1e2634] border-none rounded-lg py-2.5 pl-10 pr-4 text-sm text-white placeholder-slate-500 focus:ring-1 focus:ring-primary/50" placeholder="Cari nama pasien..." type="text"/>
            </div>
            
             <!-- Chips -->
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide -mx-1 px-1">
                <button class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-primary/20 text-primary border border-primary/30 rounded-full text-xs font-medium hover:bg-primary/30 transition-colors">
                    <span>Semua</span>
                </button>
                <button class="shrink-0 flex items-center gap-1 px-3 py-1.5 bg-[#1e2634] text-slate-300 border border-white/5 rounded-full text-xs font-medium hover:bg-[#2a3446] transition-colors">
                    <span>Instagam</span>
                </button>
                <button class="shrink-0 flex items-center gap-1 px-3 py-1.5 bg-[#1e2634] text-slate-300 border border-white/5 rounded-full text-xs font-medium hover:bg-[#2a3446] transition-colors">
                    <span>WhatsApp</span>
                </button>
            </div>
        </div>

        <!-- List Items -->
        <div class="flex-1 overflow-y-auto" id="conv-list">
            @forelse($conversations as $conv)
                <a href="{{ route('inbox', ['conversation_id' => $conv->id]) }}" 
                   class="flex gap-3 p-4 border-b border-white/5 cursor-pointer transition-colors relative group conv-item
                   {{ $selectedId == $conv->id ? 'bg-[#1e2634] border-l-2 border-l-primary border-b-[#1e2634]' : 'hover:bg-[#1e2634]/50 border-l-2 border-l-transparent' }}"
                   data-name="{{ strtolower($conv->display_name ?? '') }}">
                    
                    <div class="relative shrink-0">
                        <div class="size-12 rounded-full bg-slate-700 bg-center bg-cover" 
                             style='background-image: url("{{ $conv->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($conv->display_name).'&background=374151&color=fff' }}");'></div>
                        <div class="absolute -bottom-0.5 -right-0.5 bg-pink-500 rounded-full p-0.5 border border-[#111722] flex items-center justify-center size-4">
                            <!-- TODO: Check source type if available, else default IG -->
                             <span class="material-symbols-outlined text-white text-[10px]">photo_camera</span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col flex-1 min-w-0">
                        <div class="flex justify-between items-baseline mb-0.5">
                            <h3 class="text-sm font-medium {{ $selectedId == $conv->id ? 'text-white font-semibold' : 'text-slate-300' }} truncate">{{ $conv->display_name ?: 'Guest' }}</h3>
                            <span class="text-[10px] text-slate-500 whitespace-nowrap">{{ ($conv->last_activity_at && $conv->last_activity_at > 946684800) ? \Carbon\Carbon::createFromTimestamp($conv->last_activity_at)->diffForHumans(null, true, true) : '' }}</span>
                        </div>
                         <div class="flex items-center gap-1.5 mb-1">
                             @if($conv->status == 'open')
                                <span class="text-[10px] bg-green-500/10 text-green-400 px-1.5 py-0.5 rounded flex items-center gap-1">Open</span>
                             @else
                                <span class="text-[10px] bg-indigo-500/10 text-slate-400 px-1.5 py-0.5 rounded flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]">smart_toy</span> Bot
                                </span>
                             @endif
                        </div>
                        <p class="text-xs text-slate-400 truncate">{{ $conv->last_message ?: 'No messages yet' }}</p>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-slate-500 text-sm">
                    Belum ada percakapan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Chat Interface (Right Column) -->
    <div class="flex-1 flex flex-col h-full bg-[#101622] relative border-l border-white/5 {{ $selectedId ? 'flex' : 'hidden md:flex' }}">
        @if($selectedId)
            <!-- Chat Header -->
            <header class="h-16 border-b border-white/5 flex items-center justify-between px-6 bg-[#111722] shrink-0">
                <div class="flex items-center gap-3">
                    <a href="{{ route('inbox') }}" class="md:hidden text-slate-400 mr-2">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    
                    <div class="size-8 rounded-full bg-slate-700 bg-center bg-cover" 
                         style='background-image: url("{{ $contact['avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($contact['name']).'&background=374151&color=fff' }}");'></div>
                    <div>
                        <h2 class="text-base font-semibold text-white">{{ $contact['name'] }}</h2>
                        <p class="text-xs text-slate-500">{{ $contact['ig_username'] }}</p>
                    </div>
                </div>
                <!-- Actions -->
                <div class="flex items-center gap-2">
                     <button onclick="window.location.reload()" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition-colors" title="Refresh Chat">
                        <span class="material-symbols-outlined" style="font-size: 20px;">refresh</span>
                    </button>
                </div>
            </header>

            <!-- Messages Area -->
            <div id="messages-area" class="flex-1 overflow-y-auto p-6 flex flex-col gap-4 scroll-smooth bg-background-dark bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-[#1e2634] to-background-dark">
                
                @forelse($messages as $msg)
                    @php
                        // Logic Deteksi Pengirim
                        $type = strtolower($msg->sender_type ?? 'contact');
                        
                        // Cek flag bot dari DB atau sender type
                        $isBot = ($type === 'bot') || ($msg->is_replied_by_bot ?? false);
                        $isAgent = in_array($type, ['agent', 'admin']);
                        
                        // Pesan "Saya" (Kanan) adalah Bot atau Agent
                        $isMe = $isBot || $isAgent;
                        
                        $time = \Carbon\Carbon::parse($msg->created_at)->format('H:i');
                        
                        // Simple Markdown Parser (Bold & Newline)
                        $content = e($msg->content);
                        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content); // Bold **text**
                        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content); // Italic *text*
                        $content = nl2br($content); // Newline
                    @endphp

                    <!-- Message Bubble -->
                    <div class="flex gap-3 max-w-[85%] w-fit {{ $isMe ? 'ml-auto flex-row-reverse' : '' }}">
                        <!-- Avatar -->
                        <div class="size-8 rounded-full flex items-center justify-center shrink-0 
                             {{ $isMe ? ($isBot ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'bg-slate-700 bg-center bg-cover') : 'bg-slate-700 bg-center bg-cover' }}"
                             style="{{ (!$isMe || !$isBot) ? 'background-image: url("'. ($isMe ? 'https://ui-avatars.com/api/?name=Admin&background=135bec&color=fff' : ($contact['avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($contact['name']).'&background=374151&color=fff')).'");' : '' }}">
                             @if($isBot)
                                <span class="material-symbols-outlined" style="font-size: 16px;">smart_toy</span>
                             @endif
                        </div>
                        
                        <div class="flex flex-col gap-1 {{ $isMe ? 'items-end' : 'items-start' }} min-w-0">
                            <!-- Name & Time -->
                            <div class="flex items-baseline gap-2 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                <span class="text-xs font-semibold {{ $isBot ? 'text-indigo-300' : ($isMe ? 'text-white' : 'text-slate-200') }}">
                                    {{ $isBot ? 'ReplyAI Bot' : ($isMe ? 'Admin' : $contact['name']) }}
                                </span>
                                <span class="text-[10px] text-slate-500">{{ $time }}</span>
                            </div>
                            
                            <!-- Bubble Box -->
                            <div class="p-3 rounded-2xl text-sm shadow-sm break-words 
                                {{ $isMe 
                                    ? 'bg-[#135bec] text-white rounded-tr-none' 
                                    : 'bg-[#2a3446] text-slate-200 rounded-tl-none border border-white/5' 
                                }}">
                                {!! $content !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex justify-center mt-10">
                         <span class="text-xs font-medium text-slate-500 bg-[#1e2634] px-4 py-2 rounded-full border border-white/5">Belum ada history pesan.</span>
                    </div>
                @endforelse
                
                <!-- Spacer for scroll -->
                <div id="scroll-anchor" class="h-1"></div>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-white/5 bg-[#111722] z-20 shrink-0">
                <form action="{{ route('inbox.send') }}" method="POST" class="relative flex items-end gap-2 bg-[#1e2634] p-2 rounded-xl border border-white/5 focus-within:ring-1 focus-within:ring-primary/50 transition-all">
                    @csrf
                    <input type="hidden" name="conversation_id" value="{{ $selectedId }}">
                    
                    <button type="button" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition-colors shrink-0">
                        <span class="material-symbols-outlined" style="font-size: 20px;">add_circle</span>
                    </button>
                     <button type="button" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition-colors shrink-0">
                        <span class="material-symbols-outlined" style="font-size: 20px;">mood</span>
                    </button>

                    <textarea name="content" required 
                              class="w-full bg-transparent border-none text-sm text-white placeholder-slate-500 focus:ring-0 resize-none max-h-32 py-2" 
                              placeholder="Ketik pesan balasan..." rows="1"></textarea>
                    
                    <button type="submit" class="p-2 bg-primary text-white rounded-lg hover:bg-blue-600 shadow-lg shadow-primary/20 transition-all hover:scale-105 shrink-0">
                        <span class="material-symbols-outlined fill-current" style="font-size: 20px;">send</span>
                    </button>
                </form>
                @if(session('success'))
                     <div class="text-green-500 text-xs mt-2 px-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">check</span> {{ session('success') }}
                     </div>
                @endif
            </div>

        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center h-full text-center p-8">
                <div class="size-20 bg-[#1e2634] rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-slate-500 text-4xl">chat</span>
                </div>
                <h3 class="text-white font-bold text-lg">Pilih Percakapan</h3>
                <p class="text-slate-400 text-sm mt-2 max-w-xs">Pilih salah satu kontak dari daftar di sebelah kiri untuk melihat riwayat pesan dan membalas chat.</p>
            </div>
        @endif
    </div>
    
    <!-- Detail Panel (Rightmost) -->
    @if($selectedId && isset($contact))
    <aside class="hidden xl:flex w-[300px] bg-[#111722] border-l border-white/5 flex-col shrink-0 h-full overflow-y-auto">
        <div class="p-6 border-b border-white/5">
            <div class="flex flex-col items-center">
                 <div class="size-20 rounded-full bg-slate-700 bg-center bg-cover mb-3 ring-4 ring-[#1e2634]" 
                      style='background-image: url("{{ $contact['avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($contact['name']).'&background=374151&color=fff' }}");'></div>
                <h2 class="text-lg font-bold text-white text-center">{{ $contact['name'] }}</h2>
                 <p class="text-slate-400 text-sm mb-4">{{ $contact['ig_username'] ?? '-' }}</p>
            </div>
            
            <div class="flex gap-3 w-full">
                <button class="flex-1 flex flex-col items-center justify-center p-2 rounded-lg bg-[#1e2634] hover:bg-[#252f3e] transition-colors border border-white/5">
                    <span class="material-symbols-outlined text-slate-400 mb-1" style="font-size: 20px;">history</span>
                    <span class="text-[10px] text-slate-400">History</span>
                </button>
                 <button class="flex-1 flex flex-col items-center justify-center p-2 rounded-lg bg-[#1e2634] hover:bg-[#252f3e] transition-colors border border-white/5">
                    <span class="material-symbols-outlined text-slate-400 mb-1" style="font-size: 20px;">edit_note</span>
                    <span class="text-[10px] text-slate-400">Notes</span>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Detail Kontak</h3>
            <div class="flex flex-col gap-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-400">Platform</span>
                    <span class="text-sm font-medium text-white badge bg-pink-500/10 text-pink-400 px-2 py-0.5 rounded">Instagram</span>
                </div>
                 <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-400">Status</span>
                    <span class="text-sm font-medium text-white">Active</span>
                </div>
            </div>
            
            <div class="mt-8">
                 <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">System Tags</h3>
                 <div class="flex flex-wrap gap-2">
                     <span class="px-2 py-0.5 rounded text-[10px] bg-slate-700 text-slate-300">New Lead</span>
                     <span class="px-2 py-0.5 rounded text-[10px] bg-slate-700 text-slate-300">Bot Handled</span>
                 </div>
            </div>
        </div>
    </aside>
    @endif
    
</main>

<script>
    // Simple Search Filter
    const searchInput = document.getElementById('conv-search');
    const listContainer = document.getElementById('conv-list');
    
    if(searchInput){
        searchInput.addEventListener('input', (e) => {
            const val = e.target.value.toLowerCase();
            const items = listContainer.querySelectorAll('.conv-item');
            
            items.forEach(item => {
                const name = item.dataset.name;
                if(name.includes(val)){
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Scroll to bottom
    const msgArea = document.getElementById('messages-area');
    if(msgArea){
        msgArea.scrollTop = msgArea.scrollHeight;
    }
</script>

</body>
</html>
