<x-enterprise-layout title="{{ __('inbox.title') }}">
    <div class="contents" x-data="{ 
        search: '',
        filter: 'all',
        showDetail: {{ $selectedId ? 'true' : 'false' }},
        sidebarOpen: false
    }">


<!-- Main Content Area -->
<main class="flex flex-1 flex-col overflow-hidden h-full relative">
    <!-- Top Header for Inbox -->
    <header class="h-14 border-b border-white/5 bg-[#111722] flex items-center justify-between px-6 z-20 shrink-0">
        <div class="flex items-center gap-2 text-slate-500 text-xs font-bold uppercase tracking-widest">
            <span class="material-symbols-outlined text-[18px]">calendar_today</span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 px-3 py-1 bg-green-500/10 rounded-full border border-green-500/20">
                <div class="size-1.5 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-[10px] font-bold text-green-500 uppercase tracking-widest">{{ __('inbox.system_online') }}</span>
            </div>
            @include('components.language-switcher')
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden h-full">
    @if($hasInstagramAccount ?? false)
    <!-- Conversation List (Middle Column) -->
    <div class="w-full md:w-[340px] lg:w-[360px] flex flex-col border-r border-white/5 bg-[#111722] shrink-0 h-full z-10 {{ $selectedId ? 'hidden md:flex' : 'flex' }}">
        <!-- Header -->
        <div class="p-5 pb-2">
            <div class="flex justify-between items-start mb-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl font-bold text-white tracking-tight">{{ __('inbox.title') }}</h2>
                    @include('components.page-help', [
                        'title' => __('inbox.title'),
                        'description' => __('inbox.help_description'),
                        'tips' => [
                            __('inbox.tip_1', ['default' => 'Pilih percakapan dari daftar di kiri']),
                            __('inbox.tip_2', ['default' => 'Warna hijau = Bot yang menjawab']),
                            __('inbox.tip_3', ['default' => 'Warna merah = CS yang menangani']),
                            __('inbox.tip_4', ['default' => 'Klik "Ambil Alih" untuk menjawab manual']),
                            __('inbox.tip_5', ['default' => 'Klik "Bot Kembali" untuk mengaktifkan bot lagi'])
                        ]
                    ])
                </div>
                <button class="text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">edit_square</span>
                </button>
            </div>
            
            <!-- Search Filter Client Side -->
            <div class="relative mb-4 mt-4">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" style="font-size: 20px;">search</span>
                <input id="conv-search" class="w-full bg-[#1e2634] border-none rounded-lg py-2.5 pl-10 pr-4 text-sm text-white placeholder-slate-500 focus:ring-1 focus:ring-primary/50" placeholder="{{ __('inbox.search_placeholder') }}" type="text"/>
            </div>
            
             <!-- Chips - Functional Filters -->
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide -mx-1 px-1" id="filter-chips">
                <button data-filter="all" class="filter-chip active shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-primary/20 text-primary border border-primary/30 rounded-full text-xs font-medium hover:bg-primary/30 transition-colors">
                    <span>{{ __('inbox.filter_all') }}</span>
                </button>
                <button data-filter="bot_handling" class="filter-chip shrink-0 flex items-center gap-1 px-3 py-1.5 bg-[#1e2634] text-slate-300 border border-white/5 rounded-full text-xs font-medium hover:bg-[#2a3446] transition-colors">
                    <span class="material-symbols-outlined text-green-400" style="font-size: 14px;">smart_toy</span>
                    <span>{{ __('inbox.filter_bot') }}</span>
                </button>
                <button data-filter="agent_handling" class="filter-chip shrink-0 flex items-center gap-1 px-3 py-1.5 bg-[#1e2634] text-slate-300 border border-white/5 rounded-full text-xs font-medium hover:bg-[#2a3446] transition-colors">
                    <span class="material-symbols-outlined text-red-400" style="font-size: 14px;">headset_mic</span>
                    <span>{{ __('inbox.filter_cs') }}</span>
                </button>
                <button data-filter="escalated" class="filter-chip shrink-0 flex items-center gap-1 px-3 py-1.5 bg-[#1e2634] text-slate-300 border border-white/5 rounded-full text-xs font-medium hover:bg-[#2a3446] transition-colors">
                    <span class="material-symbols-outlined text-orange-400" style="font-size: 14px;">priority_high</span>
                    <span>{{ __('inbox.filter_escalated') }}</span>
                </button>
            </div>
        </div>

        <!-- List Items -->
        <div class="flex-1 overflow-y-auto" id="conv-list">
            @forelse($conversations as $conv)
                @php
                    // Detect last message sender
                    $lastMsg = $conv->messages()->orderByDesc('created_at')->first();
                    $lastSenderType = $lastMsg?->sender_type ?? 'user';
                    $isLastFromBot = in_array($lastSenderType, ['bot', 'agent', 'admin']);
                @endphp
                <a href="{{ route('inbox', ['conversation_id' => $conv->id]) }}" 
                   class="flex gap-3 p-4 border-b border-white/5 cursor-pointer transition-colors relative group conv-item
                   {{ $selectedId == $conv->id ? 'bg-[#1e2634] border-l-2 border-l-primary border-b-[#1e2634]' : 'hover:bg-[#1e2634]/50 border-l-2 border-l-transparent' }}"
                   data-name="{{ strtolower($conv->display_name ?? '') }}"
                   data-status="{{ $conv->status ?? 'bot_handling' }}">
                    
                    <div class="relative shrink-0">
                        <div class="size-12 rounded-full bg-slate-700 bg-center bg-cover" 
                             style='background-image: url("{{ $conv->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($conv->display_name).'&background=374151&color=fff' }}");'></div>
                        <!-- Status Indicator Dot -->
                        <span class="absolute -top-0.5 -left-0.5 w-3 h-3 rounded-full border-2 border-[#111722]
                            @if($conv->status == 'agent_handling') bg-red-500
                            @elseif($conv->status == 'escalated') bg-orange-500 animate-pulse
                            @else bg-green-500
                            @endif"></span>
                        <div class="absolute -bottom-0.5 -right-0.5 bg-pink-500 rounded-full p-0.5 border border-[#111722] flex items-center justify-center size-4">
                             <span class="material-symbols-outlined text-white text-[10px]">photo_camera</span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col flex-1 min-w-0">
                        <div class="flex justify-between items-baseline mb-0.5">
                            <h3 class="text-sm font-medium {{ $selectedId == $conv->id ? 'text-white font-semibold' : 'text-slate-300' }} truncate">{{ $conv->display_name ?: 'Guest' }}</h3>
                            <span class="text-[10px] text-slate-500 whitespace-nowrap">{{ ($conv->last_activity_at && $conv->last_activity_at > 946684800) ? \Carbon\Carbon::createFromTimestamp($conv->last_activity_at)->diffForHumans(null, true, true) : '' }}</span>
                        </div>
                         <div class="flex items-center gap-1.5 mb-1">
                             @if($conv->status == 'agent_handling')
                                <span class="text-[10px] bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]">headset_mic</span> {{ __('inbox.status_agent') }}
                                </span>
                             @elseif($conv->status == 'escalated')
                                <span class="text-[10px] bg-orange-500/20 text-orange-400 px-1.5 py-0.5 rounded flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]">priority_high</span> {{ __('inbox.status_escalated') }}
                                </span>
                             @else
                                <span class="text-[10px] bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]">smart_toy</span> {{ __('inbox.status_bot') }}
                                </span>
                             @endif
                        </div>
                        <p class="text-xs text-slate-400 truncate flex items-center gap-1">
                            @if($isLastFromBot)
                                <span class="material-symbols-outlined text-indigo-400" style="font-size: 12px;">smart_toy</span>
                            @else
                                <span class="material-symbols-outlined text-slate-500" style="font-size: 12px;">person</span>
                            @endif
                            {{ $conv->last_message ?: __('inbox.no_messages') }}
                        </p>
                    </div>
                </a>
            @empty
                @if(!($hasInstagramAccount ?? false))
                    <!-- User belum connect Instagram -->
                    <div class="p-8 text-center">
                        <div class="size-16 bg-pink-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-pink-400 text-3xl">photo_camera</span>
                        </div>
                        <h3 class="text-white font-semibold mb-2">{{ __('inbox.connect_title') }}</h3>
                        <p class="text-slate-400 text-xs mb-4">{{ __('inbox.connect_text') }}</p>
                        <a href="{{ route('instagram.settings') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-500 via-pink-500 to-orange-400 text-white text-xs font-medium rounded-lg hover:opacity-90 transition">
                            <span class="material-symbols-outlined" style="font-size: 16px;">add_link</span>
                            {{ __('inbox.connect_button') }}
                        </a>
                    </div>
                @else
                    <div class="p-8 text-center text-slate-500 text-sm">
                        {{ __('inbox.no_conversations') }}
                    </div>
                @endif
            @endforelse
        </div>
    </div>

    <!-- Chat Interface (Right Column) -->
    <div class="flex-1 flex flex-col h-full bg-[#101622] relative border-l border-white/5 min-w-0 {{ $selectedId ? 'flex' : 'hidden md:flex' }}">
        @if($selectedId)
            <!-- Chat Header -->
            <header class="min-h-[64px] border-b border-white/5 flex items-center justify-between px-4 lg:px-6 bg-[#111722] shrink-0 gap-2 flex-wrap py-2">
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
                <div class="flex items-center gap-1.5 lg:gap-2 flex-wrap justify-end">
                    @php
                        $convStatus = $conversations->firstWhere('id', $selectedId)?->status ?? 'bot_handling';
                        $agentRepliedAt = $conversations->firstWhere('id', $selectedId)?->agent_replied_at;
                        $hoursLeft = $agentRepliedAt ? 4 - now()->diffInHours($agentRepliedAt) : 0;
                        
                        // Calculate 24-hour window for Instagram messaging
                        $selectedConv = $conversations->firstWhere('id', $selectedId);
                        $lastUserMessage = $selectedConv?->messages()?->where('sender_type', 'user')->orderByDesc('created_at')->first();
                        $lastUserMsgTime = $lastUserMessage?->created_at;
                        $windowExpired = $lastUserMsgTime ? now()->diffInHours($lastUserMsgTime) >= 24 : true;
                        $hoursRemaining = $lastUserMsgTime ? max(0, 24 - now()->diffInHours($lastUserMsgTime)) : 0;
                    @endphp

                    <!-- 24-Hour Window Indicator -->
                    @if($windowExpired)
                        <span class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] lg:text-xs font-medium bg-red-500/20 text-red-400 border border-red-500/30" title="{{ __('inbox.window_expired_title', ['default' => 'Jendela 24 jam sudah berakhir. Tunggu user mengirim pesan baru.']) }}">
                            <span class="material-symbols-outlined" style="font-size: 12px;">schedule</span>
                            <span class="hidden sm:inline">Expired</span>
                        </span>
                    @elseif($hoursRemaining <= 6)
                        <span class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] lg:text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30" title="Sisa {{ $hoursRemaining }} jam untuk membalas">
                            <span class="material-symbols-outlined" style="font-size: 12px;">schedule</span>
                            {{ $hoursRemaining }}j
                        </span>
                    @else
                        <span class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] lg:text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30" title="Sisa {{ $hoursRemaining }} jam untuk membalas">
                            <span class="material-symbols-outlined" style="font-size: 12px;">schedule</span>
                            {{ $hoursRemaining }}j
                        </span>
                    @endif

                    <!-- Status Badge -->
                    @if($convStatus === 'agent_handling')
                        <span class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] lg:text-xs font-medium bg-red-500/20 text-red-400 border border-red-500/30">
                            <span class="material-symbols-outlined" style="font-size: 12px;">headset_mic</span>
                            <span class="hidden lg:inline">{{ __('inbox.filter_cs') }}</span>
                        </span>
                    @elseif($convStatus === 'escalated')
                        <span class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] lg:text-xs font-medium bg-orange-500/20 text-orange-400 border border-orange-500/30">
                            <span class="material-symbols-outlined" style="font-size: 12px;">priority_high</span>
                            <span class="hidden lg:inline">{{ __('inbox.filter_escalated') }}</span>
                        </span>
                    @else
                        <span class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] lg:text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                            <span class="material-symbols-outlined" style="font-size: 12px;">smart_toy</span>
                            <span class="hidden lg:inline">{{ __('inbox.filter_bot') }}</span>
                        </span>
                    @endif

                    <!-- Takeover Button (when bot is active) -->
                    @if($convStatus === 'bot_handling' || !$convStatus)
                        <form action="{{ route('takeover.ig.takeover', $selectedId) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-1 px-2 lg:px-3 py-1.5 rounded-lg text-[10px] lg:text-xs font-medium bg-amber-500 hover:bg-amber-600 text-white transition-colors" title="{{ __('inbox.takeover_button') }}">
                                <span class="material-symbols-outlined" style="font-size: 14px;">headset_mic</span>
                                <span class="hidden sm:inline">{{ __('inbox.takeover_button') }}</span>
                            </button>
                        </form>
                    @endif

                    <!-- Handback Button (more prominent when agent_handling) -->
                    @if($convStatus === 'agent_handling' || $convStatus === 'escalated')
                        <form action="{{ route('inbox.handback', $selectedId) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-1 px-2 lg:px-3 py-1.5 rounded-lg text-[10px] lg:text-xs font-medium bg-green-500 hover:bg-green-600 text-white transition-colors" title="{{ __('inbox.handback_button') }}">
                                <span class="material-symbols-outlined" style="font-size: 14px;">replay</span>
                                <span class="hidden sm:inline">{{ __('inbox.handback_button') }}</span>
                            </button>
                        </form>
                    @endif

                     <button onclick="window.location.reload()" class="p-1.5 lg:p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition-colors" title="Refresh Chat">
                        <span class="material-symbols-outlined" style="font-size: 18px;">refresh</span>
                    </button>
                    
                    <!-- Toggle Detail Panel Button -->
                    <button onclick="toggleDetailPanel()" class="hidden xl:flex p-1.5 lg:p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition-colors" title="Toggle Detail Panel" id="toggle-panel-btn">
                        <span class="material-symbols-outlined" style="font-size: 18px;">right_panel_open</span>
                    </button>
                </div>
            </header>

            <!-- Messages Area -->
            <!-- Messages Area -->
            <div id="messages-area" class="flex-1 overflow-y-auto p-6 flex flex-col gap-4 bg-background-dark bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-[#1e2634] to-background-dark relative">
                
                @if(!($hasInstagramAccount ?? false))
                    <!-- Full Cover Disconnected State -->
                    <div class="absolute inset-0 z-20 bg-[#101622]/90 backdrop-blur-sm flex flex-col items-center justify-center p-8 text-center">
                         <div class="max-w-lg w-full bg-[#1e2634] rounded-2xl border border-white/5 p-10 shadow-2xl relative overflow-hidden group">
                            <!-- Glow Effect -->
                            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-1/2 bg-gradient-to-b from-purple-500/10 to-transparent blur-3xl -z-10 group-hover:from-purple-500/20 transition-all duration-700"></div>

                            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 flex items-center justify-center shadow-lg shadow-pink-500/20 group-hover:scale-110 transition-transform duration-500">
                                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-3">{{ __('inbox.connect_title') }}</h3>
                            <p class="text-slate-400 text-sm mb-8 leading-relaxed">
                                {{ __('inbox.connect_text') }}
                            </p>
                            
                            <a href="{{ route('instagram.connect') }}" class="inline-flex w-full justify-center items-center gap-2 px-8 py-4 bg-gradient-to-r from-purple-500 via-pink-500 to-orange-400 hover:from-purple-600 hover:via-pink-600 hover:to-orange-500 rounded-xl font-bold text-white transition shadow-lg shadow-orange-500/20 transform hover:scale-[1.02] active:scale-[0.98]">
                                <span class="material-symbols-outlined">add_link</span>
                                {{ __('inbox.connect_button') }}
                            </a>
                        </div>
                    </div>
                @else
                    @php $lastDate = null; @endphp
                    @forelse($messages as $msg)
                        @php
                            // Timestamp grouping
                            $msgDate = \Carbon\Carbon::parse($msg->created_at);
                            $dateLabel = null;
                            if (!$lastDate || !$msgDate->isSameDay($lastDate)) {
                                if ($msgDate->isToday()) {
                                    $dateLabel = __('inbox.today');
                                } elseif ($msgDate->isYesterday()) {
                                    $dateLabel = __('inbox.yesterday');
                                } else {
                                    $dateLabel = $msgDate->translatedFormat('d F Y');
                                }
                                $lastDate = $msgDate;
                            }
                            
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
                        
                        <!-- Date Separator -->
                        @if($dateLabel)
                            <div class="flex items-center justify-center my-4">
                                <div class="h-px bg-white/10 flex-1"></div>
                                <span class="px-4 text-xs font-medium text-slate-500 bg-background-dark">{{ $dateLabel }}</span>
                                <div class="h-px bg-white/10 flex-1"></div>
                            </div>
                        @endif

                        <!-- Message Bubble -->
                        <div class="flex gap-3 max-w-[85%] w-fit message-animate-in group/msg {{ $isMe ? 'ml-auto flex-row-reverse' : '' }}">
                            <!-- Avatar -->
                            <div class="size-8 rounded-full flex items-center justify-center shrink-0 
                                 {{ $isMe ? ($isBot ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'bg-slate-700 bg-center bg-cover') : 'bg-slate-700 bg-center bg-cover' }}"
                                 style="{{ (!$isMe || !$isBot) ? 'background-image: url("'. ($isMe ? 'https://ui-avatars.com/api/?name=Admin&background=135bec&color=fff' : ($contact['avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($contact['name']).'&background=374151&color=fff')).'");' : '' }}">
                                 @if($isBot)
                                    <span class="material-symbols-outlined" style="font-size: 16px;">smart_toy</span>
                                 @endif
                            </div>
                            
                            <div class="flex flex-col gap-1 {{ $isMe ? 'items-end' : 'items-start' }} min-w-0">
                                <!-- Name & Time with Read Receipt -->
                                <div class="flex items-center gap-2 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                    <span class="text-xs font-semibold {{ $isBot ? 'text-indigo-300' : ($isMe ? 'text-white' : 'text-slate-200') }}">
                                        {{ $isBot ? 'ReplyAI Bot' : ($isMe ? 'Admin' : $contact['name']) }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 flex items-center gap-0.5">
                                        {{ $time }}
                                        @if($isMe)
                                            <!-- Read Receipt -->
                                            <span class="read-receipt read ml-1" title="Terkirim">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M15 6L4 17" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                
                                <!-- Bubble Box with Hover Actions -->
                                <div class="message-bubble relative">
                                    <div class="p-3 rounded-2xl text-sm shadow-sm break-words 
                                        {{ $isMe 
                                            ? 'bg-[#135bec] text-white rounded-tr-none' 
                                            : 'bg-[#2a3446] text-slate-200 rounded-tl-none border border-white/5' 
                                        }}">
                                        {!! $content !!}
                                    </div>
                                    
                                    <!-- Hover Actions (Reactions) -->
                                    <div class="message-actions absolute {{ $isMe ? 'left-0 -translate-x-full pl-1' : 'right-0 translate-x-full pr-1' }} top-1/2 -translate-y-1/2 flex items-center gap-0.5">
                                        <button type="button" class="p-1 rounded-full hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="Suka">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">thumb_up</span>
                                        </button>
                                        <button type="button" class="p-1 rounded-full hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="Reply">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">reply</span>
                                        </button>
                                        <button type="button" class="p-1 rounded-full hover:bg-white/10 text-slate-400 hover:text-white transition-colors" title="More">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">more_vert</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex justify-center mt-10">
                             <span class="text-xs font-medium text-slate-500 bg-[#1e2634] px-4 py-2 rounded-full border border-white/5">{{ __('inbox.no_messages') }}</span>
                        </div>
                    @endforelse
                @endif
                
                <!-- Typing Indicator (hidden by default, shown via JS) -->
                <div id="typing-indicator" class="hidden flex gap-3 max-w-[85%] w-fit">
                    <div class="size-8 rounded-full bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined" style="font-size: 16px;">smart_toy</span>
                    </div>
                    <div class="flex flex-col gap-1 items-start">
                        <span class="text-xs font-semibold text-indigo-300">ReplyAI Bot</span>
                        <div class="bg-[#2a3446] rounded-2xl rounded-tl-none border border-white/5">
                            <div class="typing-indicator">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Spacer for scroll -->
                <div id="scroll-anchor" class="h-1"></div>
            </div>

            <!-- Input Area -->
            @if($hasInstagramAccount ?? false)
            <div class="p-4 border-t border-white/5 bg-[#111722] z-20 shrink-0 relative">
                <form action="{{ route('inbox.send') }}" method="POST" class="relative flex items-end gap-2 bg-[#1e2634] p-2 rounded-xl border border-white/5 focus-within:ring-1 focus-within:ring-primary/50 transition-all">
                    @csrf
                    <input type="hidden" name="conversation_id" value="{{ $selectedId }}">
                    
                    <div class="relative">
                        <button type="button" onclick="toggleQuickReplies()" class="p-2 text-warning hover:text-yellow-400 rounded-lg hover:bg-white/5 transition-colors shrink-0" title="Quick Reply">
                            <span class="material-symbols-outlined" style="font-size: 20px;">bolt</span>
                        </button>
                        <!-- Dropdown -->
                        <div id="qr-dropdown" class="hidden absolute bottom-full left-0 mb-2 w-72 bg-[#1e2634] border border-white/10 rounded-xl shadow-xl overflow-hidden z-30 transform origin-bottom-left transition-all">
                            <div class="px-3 py-2 border-b border-white/5 text-xs font-semibold text-slate-400 bg-[#111722]/50 flex justify-between items-center">
                                <span>{{ __('inbox.quick_replies') }}</span>
                                <span class="text-[10px] bg-slate-700 px-1.5 rounded">Esc to close</span>
                            </div>
                            <div id="qr-list" class="max-h-60 overflow-y-auto custom-scrollbar">
                                <div class="p-4 text-center text-xs text-slate-500 flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined animate-spin text-lg">autorenew</span>
                                    {{ __('inbox.loading_templates') }}
                                </div>
                            </div>
                            <a href="{{ route('quick-replies.index') }}" class="block p-2 text-center text-[10px] text-primary hover:bg-white/5 border-t border-white/5 bg-[#111722]/30">
                                + {{ __('inbox.manage_templates') }}
                            </a>
                        </div>
                    </div>
                    
                    <button type="button" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition-colors shrink-0">
                        <span class="material-symbols-outlined" style="font-size: 20px;">add_circle</span>
                    </button>
                     <button type="button" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition-colors shrink-0">
                        <span class="material-symbols-outlined" style="font-size: 20px;">mood</span>
                    </button>

                    <textarea name="content" required 
                              class="w-full bg-transparent border-none text-sm text-white placeholder-slate-500 focus:ring-0 resize-none max-h-32 py-2" 
                              placeholder="{{ __('inbox.input_placeholder') }}" rows="1"></textarea>
                    
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
            @endif

        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center h-full text-center p-8">
                
                @if(!($hasInstagramAccount ?? false))
                    <!-- Connect Call to Action -->
                     <div class="max-w-md w-full bg-[#1e2634] rounded-2xl border border-white/5 p-8 shadow-2xl">
                         <div class="size-20 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/20">
                            <span class="material-symbols-outlined text-white text-4xl">add_link</span>
                        </div>
                        <h3 class="text-white font-bold text-xl mb-2">{{ __('inbox.connect_title') }}</h3>
                        <p class="text-slate-400 text-sm mb-6">
                            {{ __('inbox.connect_text') }}
                        </p>
                        <a href="{{ route('instagram.connect') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-purple-500 via-pink-500 to-orange-400 hover:from-purple-600 hover:via-pink-600 hover:to-orange-500 rounded-xl font-semibold text-white transition shadow-lg shadow-orange-500/20 transform hover:scale-105 active:scale-95">
                            {{ __('inbox.connect_button') }}
                        </a>
                    </div>
                @else
                    <!-- Select Conversation State -->
                    <div class="size-20 bg-[#1e2634] rounded-full flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-slate-500 text-4xl">chat</span>
                    </div>
                    <h3 class="text-white font-bold text-lg">{{ __('inbox.select_conversation') }}</h3>
                    <p class="text-slate-400 text-sm mt-2 max-w-xs">{{ __('inbox.select_conversation_text') }}</p>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Detail Panel (Rightmost) - Collapsible -->
    @if($selectedId && isset($contact))
    <aside id="detail-panel" class="detail-panel hidden xl:flex w-[280px] 2xl:w-[320px] bg-[#111722] border-l border-white/5 flex-col shrink-0 h-full overflow-y-auto">
        <div class="p-4 2xl:p-6 border-b border-white/5">
            <div class="flex flex-col items-center">
                 <div class="size-16 2xl:size-20 rounded-full bg-slate-700 bg-center bg-cover mb-3 ring-4 ring-[#1e2634]" 
                      style='background-image: url("{{ $contact['avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($contact['name']).'&background=374151&color=fff' }}");'></div>
                <h2 class="text-base 2xl:text-lg font-bold text-white text-center truncate max-w-full px-2">{{ $contact['name'] }}</h2>
                 <p class="text-slate-400 text-xs 2xl:text-sm mb-4 truncate max-w-full">{{ $contact['ig_username'] ?? '-' }}</p>
            </div>
            
            <div class="flex gap-2 w-full">
                <button class="flex-1 flex flex-col items-center justify-center p-2 rounded-lg bg-[#1e2634] hover:bg-[#252f3e] transition-colors border border-white/5">
                    <span class="material-symbols-outlined text-slate-400 mb-1" style="font-size: 18px;">history</span>
                    <span class="text-[10px] text-slate-400">History</span>
                </button>
                 <button class="flex-1 flex flex-col items-center justify-center p-2 rounded-lg bg-[#1e2634] hover:bg-[#252f3e] transition-colors border border-white/5">
                    <span class="material-symbols-outlined text-slate-400 mb-1" style="font-size: 18px;">edit_note</span>
                    <span class="text-[10px] text-slate-400">Notes</span>
                </button>
            </div>
        </div>
        
        <div class="p-4 2xl:p-6">
            <h3 class="text-[10px] 2xl:text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Detail Kontak</h3>
            <div class="flex flex-col gap-3">
                <div class="flex justify-between items-center gap-2">
                    <span class="text-xs 2xl:text-sm text-slate-400 shrink-0">Platform</span>
                    <span class="text-xs 2xl:text-sm font-medium text-white bg-pink-500/10 text-pink-400 px-2 py-0.5 rounded truncate">Instagram</span>
                </div>
                 <div class="flex justify-between items-center gap-2">
                    <span class="text-xs 2xl:text-sm text-slate-400 shrink-0">Status</span>
                    <span class="text-xs 2xl:text-sm font-medium text-white truncate">Active</span>
                </div>
            </div>
            
            <div class="mt-6">
                 <h3 class="text-[10px] 2xl:text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">System Tags</h3>
                 <div class="flex flex-wrap gap-1.5">
                     <span class="px-2 py-0.5 rounded text-[10px] bg-slate-700 text-slate-300">New Lead</span>
                     <span class="px-2 py-0.5 rounded text-[10px] bg-slate-700 text-slate-300">Bot Handled</span>
                 </div>
            </div>
        </div>
    </aside>
    @endif
    
@else
    <!-- Full Screen Disconnected State (Inside Main) -->
    <div class="w-full h-full flex flex-col items-center justify-center bg-[#101622] text-center p-8 relative overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[20%] left-[20%] w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-[20%] right-[20%] w-96 h-96 bg-pink-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <div class="relative z-10 max-w-lg w-full bg-[#1e2634]/50 backdrop-blur-xl rounded-3xl border border-white/5 p-12 shadow-2xl flex flex-col items-center">
            
            <div class="w-24 h-24 mb-8 rounded-full bg-gradient-to-br from-purple-600 via-pink-600 to-orange-500 flex items-center justify-center shadow-lg shadow-pink-500/30 ring-4 ring-white/5">
                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-white mb-4">{{ __('inbox.connect_title') }}</h1>
            <p class="text-slate-400 text-base mb-10 max-w-sm mx-auto leading-relaxed">
                {{ __('inbox.connect_text') }}
            </p>
            
            <a href="{{ route('instagram.connect') }}" class="w-full bg-gradient-to-r from-purple-600 via-pink-600 to-orange-500 hover:from-purple-500 hover:via-pink-500 hover:to-orange-400 text-white font-bold py-4 px-8 rounded-xl shadow-lg shadow-pink-500/20 transform hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                <span class="material-symbols-outlined">add_link</span>
                <span>{{ __('inbox.connect_button') }}</span>
            </a>
            
            <div class="mt-8 flex items-center justify-center gap-6 text-slate-500 text-xs font-medium">
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">lock</span> {{ __('common.secure_encrypted', ['default' => 'Secure & Encrypted']) }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">verified</span> {{ __('common.meta_official_partner', ['default' => 'Meta Official Partner']) }}
                </span>
            </div>
            
        </div>
    </div>
@endif
    </div>
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

    // Scroll to bottom instantly (no animation) like Instagram
    const msgArea = document.getElementById('messages-area');
    const scrollAnchor = document.getElementById('scroll-anchor');
    if(msgArea && scrollAnchor){
        scrollAnchor.scrollIntoView({ behavior: 'instant', block: 'end' });
    } else if(msgArea) {
        msgArea.scrollTop = msgArea.scrollHeight;
    }

    // ==========================================
    // FILTER CHIPS LOGIC
    // ==========================================
    const filterChips = document.querySelectorAll('.filter-chip');
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            // Update active state
            filterChips.forEach(c => {
                c.classList.remove('active', 'bg-primary/20', 'text-primary', 'border-primary/30');
                c.classList.add('bg-[#1e2634]', 'text-slate-300', 'border-white/5');
            });
            chip.classList.remove('bg-[#1e2634]', 'text-slate-300', 'border-white/5');
            chip.classList.add('active', 'bg-primary/20', 'text-primary', 'border-primary/30');
            
            // Filter conversations
            const filter = chip.dataset.filter;
            const items = document.querySelectorAll('.conv-item');
            items.forEach(item => {
                if (filter === 'all' || item.dataset.status === filter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // ==========================================
    // AUTO-REFRESH POLLING (10 seconds)
    // ==========================================
    let lastMessageCount = {{ count($messages) }};
    let isPolling = false;
    const conversationId = {{ $selectedId ?? 'null' }};
    
    async function checkForNewMessages() {
        if (isPolling || !conversationId) return;
        isPolling = true;
        
        try {
            const res = await fetch(`/inbox/check-new?conversation_id=${conversationId}&since=${lastMessageCount}`);
            const data = await res.json();
            
            if (data.has_new) {
                // Show notification badge
                showNewMessageBadge();
                
                // Request browser notification if permission granted
                if (Notification.permission === 'granted' && document.hidden) {
                    new Notification('ReplyAI - Pesan Baru', {
                        body: data.preview || 'Ada pesan baru masuk',
                        icon: 'https://ui-avatars.com/api/?name=Reply+AI&background=0D8ABC&color=fff'
                    });
                }
            }
        } catch (e) {
            console.log('Polling error:', e);
        } finally {
            isPolling = false;
        }
    }
    
    function showNewMessageBadge() {
        // Show a floating badge
        let badge = document.getElementById('new-msg-badge');
        if (!badge) {
            badge = document.createElement('button');
            badge.id = 'new-msg-badge';
            badge.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-primary text-white text-sm font-medium rounded-full shadow-lg flex items-center gap-2 animate-bounce';
            badge.innerHTML = '<span class="material-symbols-outlined" style="font-size: 16px;">arrow_downward</span> ' + (typeof LANG !== 'undefined' && LANG.new_messages ? LANG.new_messages : 'New Message');
            badge.onclick = () => {
                window.location.reload();
            };
            document.body.appendChild(badge);
        }
    }
    
    // Start polling every 10 seconds
    setInterval(checkForNewMessages, 10000);

    // ==========================================
    // KEYBOARD SHORTCUTS
    // ==========================================
    const messageInput = document.querySelector('textarea[name="content"]');
    if (messageInput) {
        messageInput.addEventListener('keydown', (e) => {
            // Enter to send, Shift+Enter for new line
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const form = messageInput.closest('form');
                if (form && messageInput.value.trim()) {
                    form.submit();
                }
            }
        });
        
        // Auto-resize textarea
        messageInput.addEventListener('input', () => {
            messageInput.style.height = 'auto';
            messageInput.style.height = Math.min(messageInput.scrollHeight, 128) + 'px';
        });
    }
    
    // Ctrl+K for search focus
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('conv-search')?.focus();
        }
    });

    // ==========================================
    // BROWSER NOTIFICATION PERMISSION
    // ==========================================
    if ('Notification' in window && Notification.permission === 'default') {
        // Ask for permission after user interaction
        document.body.addEventListener('click', function requestNotifPermission() {
            Notification.requestPermission();
            document.body.removeEventListener('click', requestNotifPermission);
        }, { once: true });
    }

    // ==========================================
    // QUICK REPLY LOGIC
    // ==========================================
    let quickReplies = [];
    let isFetchingQR = false;
    
    async function fetchQuickReplies() {
        if(isFetchingQR) return;
        isFetchingQR = true;
        
        try {
            const res = await fetch('{{ route("api.quick-replies.fetch") }}');
            quickReplies = await res.json();
            renderQuickReplies();
        } catch (e) {
            console.error('Failed to fetch QR', e);
            document.getElementById('qr-list').innerHTML = '<div class="p-3 text-center text-xs text-red-400">Gagal memuat template.</div>';
        } finally {
            isFetchingQR = false;
        }
    }

    function renderQuickReplies() {
        const list = document.getElementById('qr-list');
        if(!quickReplies.length) {
            list.innerHTML = '<div class="p-4 text-center text-xs text-slate-500">Belum ada template.<br>Buat di menu Settings.</div>';
            return;
        }
        
        list.innerHTML = quickReplies.map(qr => `
            <button type="button" onclick="insertQuickReply('${qr.message.replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n')}')" class="w-full text-left p-3 hover:bg-[#2a3446] text-xs text-slate-300 border-b border-white/5 last:border-0 transition-colors group">
                <div class="flex items-center gap-2 mb-1">
                    ${qr.shortcut ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-primary/20 text-primary border border-primary/20">/${qr.shortcut}</span>` : ''}
                </div>
                <div class="line-clamp-2 text-slate-400 group-hover:text-white transition-colors">
                    ${qr.message}
                </div>
            </button>
        `).join('');
    }

    function toggleQuickReplies() {
        const dropdown = document.getElementById('qr-dropdown');
        dropdown.classList.toggle('hidden');
        if(!dropdown.classList.contains('hidden')) {
            // Fetch only if empty to save bandwidth, or always to refresh? Let's fetch if empty.
            if(!quickReplies.length) fetchQuickReplies();
        }
    }

    function insertQuickReply(text) {
        const input = document.querySelector('textarea[name="content"]');
        
        // Simple insert at end
        input.value = text; 
        
        // Resize textarea handling (if auto-resize logic exists, otherwise minimal manual trigger)
        input.style.height = 'auto'; // Reset
        input.style.height = input.scrollHeight + 'px';
        
        input.focus();
        document.getElementById('qr-dropdown').classList.add('hidden');
    }

    // Close dropdown on click outside
    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('qr-dropdown');
        const trigger = document.querySelector('button[title="Quick Reply"]'); // Select by title for uniqueness
        if (dropdown && !dropdown.contains(e.target) && trigger && !trigger.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if(e.key === 'Escape') {
            document.getElementById('qr-dropdown')?.classList.add('hidden');
        }
    });

    // ==========================================
    // TOGGLE DETAIL PANEL
    // ==========================================
    function toggleDetailPanel() {
        const panel = document.getElementById('detail-panel');
        const toggleBtn = document.getElementById('toggle-panel-btn');
        const icon = toggleBtn?.querySelector('.material-symbols-outlined');
        
        if (panel) {
            panel.classList.toggle('collapsed');
            
            // Update icon
            if (icon) {
                if (panel.classList.contains('collapsed')) {
                    icon.textContent = 'right_panel_close';
                    toggleBtn.title = 'Show Detail Panel';
                } else {
                    icon.textContent = 'right_panel_open';
                    toggleBtn.title = 'Hide Detail Panel';
                }
            }
            
            // Save preference to localStorage
            localStorage.setItem('inbox_panel_collapsed', panel.classList.contains('collapsed'));
        }
    }
    
    // Restore panel state from localStorage
    document.addEventListener('DOMContentLoaded', () => {
        const panel = document.getElementById('detail-panel');
        const isCollapsed = localStorage.getItem('inbox_panel_collapsed') === 'true';
        
        if (panel && isCollapsed) {
            panel.classList.add('collapsed');
            const icon = document.querySelector('#toggle-panel-btn .material-symbols-outlined');
            if (icon) icon.textContent = 'right_panel_close';
        }
    });

    // ==========================================
    // TYPING INDICATOR (for demo/simulation)
    // ==========================================
    function showTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            indicator.classList.add('flex');
            
            // Scroll to bottom to show typing
            const scrollAnchor = document.getElementById('scroll-anchor');
            scrollAnchor?.scrollIntoView({ behavior: 'smooth', block: 'end' });
        }
    }
    
    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.classList.add('hidden');
            indicator.classList.remove('flex');
        }
    }
    
    // Show typing indicator when form is being submitted (simulating bot response)
    const sendForm = document.querySelector('form[action*="inbox/send"]');
    if (sendForm) {
        sendForm.addEventListener('submit', () => {
            // Show typing after a short delay (simulating bot thinking)
            setTimeout(showTypingIndicator, 500);
        });
    }

    // ==========================================
    // PIN CONVERSATION (Client-side demo)
    // ==========================================
    function pinConversation(convId) {
        const pinnedList = JSON.parse(localStorage.getItem('pinned_conversations') || '[]');
        
        if (pinnedList.includes(convId)) {
            // Unpin
            const idx = pinnedList.indexOf(convId);
            pinnedList.splice(idx, 1);
        } else {
            // Pin
            pinnedList.push(convId);
        }
        
        localStorage.setItem('pinned_conversations', JSON.stringify(pinnedList));
        updatePinnedUI();
    }
    
    function updatePinnedUI() {
        const pinnedList = JSON.parse(localStorage.getItem('pinned_conversations') || '[]');
        const convItems = document.querySelectorAll('.conv-item');
        
        convItems.forEach(item => {
            const href = item.getAttribute('href');
            const convId = href?.match(/conversation_id=(\d+)/)?.[1];
            
            if (convId && pinnedList.includes(convId)) {
                item.classList.add('pinned');
            } else {
                item.classList.remove('pinned');
            }
        });
        
        // Reorder: move pinned items to top
        const list = document.getElementById('conv-list');
        if (list) {
            const pinned = [...list.querySelectorAll('.conv-item.pinned')];
            pinned.forEach(item => list.prepend(item));
        }
    }
    
    // Initialize pinned UI on load
    document.addEventListener('DOMContentLoaded', updatePinnedUI);

    // ==========================================
    // SMOOTH MESSAGE SCROLL ON LOAD
    // ==========================================
    document.addEventListener('DOMContentLoaded', () => {
        // Add animation class to all messages
        const messages = document.querySelectorAll('.message-animate-in');
        messages.forEach((msg, i) => {
            msg.style.animationDelay = `${i * 0.05}s`;
        });
    });

</script>

<!-- Toast Notification -->
@endif
    </div>
</x-enterprise-layout>
