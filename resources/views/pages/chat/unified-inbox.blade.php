<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Unified Inbox - REPLYAI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "whatsapp": "#25D366",
                        "whatsapp-dark": "#128C7E",
                        "instagram": "#E4405F",
                        "instagram-dark": "#C13584",
                        "background-light": "#f6f6f8",
                        "background-dark": "#0f172a",
                        "surface-dark": "#1e293b",
                        "border-dark": "#334155",
                        "text-secondary": "#94a3b8",
                        "bubble-in": "#334155",
                        "bubble-out": "#135bec",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        [x-cloak] { display: none !important; }
        
        .typing-indicator span {
            animation: typing 1.4s infinite ease-in-out;
        }
        .typing-indicator span:nth-child(1) { animation-delay: 0s; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-6px); opacity: 1; }
        }
        
        .message-bubble {
            position: relative;
            border-radius: 12px;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        }
        
        .message-in {
            border-top-left-radius: 0;
        }
        
        .message-out {
            border-top-right-radius: 0;
        }

        .chat-bg {
            background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
            background-repeat: repeat;
            opacity: 0.06;
        }
        
        .platform-badge-whatsapp {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }
        .platform-badge-instagram {
            background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        }
    </style>
</head>
<body class="bg-gray-950 font-display text-white antialiased overflow-hidden flex flex-col h-[100dvh]">
    
<!-- ROOT CONTAINER -->
<div class="flex-1 min-h-0 bg-gray-950 flex overflow-hidden" x-data="unifiedInbox()" x-init="init()">

    <!-- SIDEBAR -->
    @include('components.sidebar')
    
    <!-- MAIN CONTENT -->
    <main class="flex-1 min-w-0 flex flex-row h-full overflow-hidden">

        <!-- COLUMN 1: CHAT LIST (Fixed width 360px on desktop) -->
        <div :class="activeChat ? 'hidden lg:flex' : 'flex'" class="w-full lg:w-[360px] flex-col border-r border-gray-800 bg-gray-900 shrink-0 {{ session()->has('impersonating_from_admin') ? 'mt-11' : '' }}">

            <!-- Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 shrink-0 bg-gray-900">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-white tracking-tight">Inbox</h2>
                    <div class="bg-primary/20 text-primary text-xs px-2 py-0.5 rounded-full font-medium" x-text="conversations.length"></div>
                </div>
                <div class="flex gap-1">
                    <button @click="fetchConversations()" class="p-2 hover:bg-gray-800 rounded-lg text-gray-400 hover:text-white transition-colors" title="Refresh">
                        <span class="material-symbols-outlined text-xl">refresh</span>
                    </button>
                </div>
            </div>
            
            <!-- Search & Filters -->
            <div class="p-3 border-b border-gray-800 space-y-3 shrink-0 bg-gray-900 z-10">
                <!-- Search -->
                <div class="relative group">
                    <input 
                        type="text" 
                        x-model="search"
                        @input.debounce.300ms="fetchConversations(false)"
                        placeholder="Search conversations..." 
                        class="w-full bg-gray-800/50 border border-gray-700 text-white rounded-lg pl-9 pr-4 py-2 text-sm focus:ring-1 focus:ring-primary focus:border-primary placeholder-gray-500 transition-all"
                    >
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-gray-500 text-lg group-focus-within:text-primary transition-colors">search</span>
                </div>
                
                <!-- Platform Filter Tabs -->
                <div class="flex gap-2">
                    <button @click="setPlatformFilter('all')" 
                            class="flex-1 py-1.5 text-xs font-medium rounded-md transition-colors relative"
                            :class="platformFilter === 'all' ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-400 hover:bg-gray-800/50 hover:text-gray-300'">
                        All
                        <span x-show="unreadCounts.total > 0" 
                              x-text="unreadCounts.total"
                              class="absolute -top-1.5 -right-1 min-w-[16px] h-4 px-1 bg-red-500 text-white text-[9px] rounded-full flex items-center justify-center font-bold"></span>
                    </button>
                    <button @click="setPlatformFilter('whatsapp')" 
                            class="flex-1 py-1.5 text-xs font-medium rounded-md transition-colors relative flex items-center justify-center gap-1"
                            :class="platformFilter === 'whatsapp' ? 'bg-whatsapp/20 text-whatsapp shadow-sm' : 'text-gray-400 hover:bg-gray-800/50 hover:text-gray-300'">
                        <span class="material-symbols-outlined text-xs">chat</span>
                        WA
                        <span x-show="unreadCounts.whatsapp > 0" 
                              x-text="unreadCounts.whatsapp"
                              class="absolute -top-1.5 -right-1 min-w-[16px] h-4 px-1 bg-whatsapp text-gray-900 text-[9px] rounded-full flex items-center justify-center font-bold"></span>
                    </button>
                    <button @click="setPlatformFilter('instagram')" 
                            class="flex-1 py-1.5 text-xs font-medium rounded-md transition-colors relative flex items-center justify-center gap-1"
                            :class="platformFilter === 'instagram' ? 'bg-instagram/20 text-instagram shadow-sm' : 'text-gray-400 hover:bg-gray-800/50 hover:text-gray-300'">
                        <span class="material-symbols-outlined text-xs">photo_camera</span>
                        IG
                        <span x-show="unreadCounts.instagram > 0" 
                              x-text="unreadCounts.instagram"
                              class="absolute -top-1.5 -right-1 min-w-[16px] h-4 px-1 bg-instagram text-white text-[9px] rounded-full flex items-center justify-center font-bold"></span>
                    </button>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex gap-2">
                    <button @click="statusFilter = 'all'" 
                            class="flex-1 py-1 text-[11px] font-medium rounded transition-colors"
                            :class="statusFilter === 'all' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:bg-gray-800/50'">
                        All
                    </button>
                    <button @click="statusFilter = 'unread'" 
                            class="flex-1 py-1 text-[11px] font-medium rounded transition-colors"
                            :class="statusFilter === 'unread' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:bg-gray-800/50'">
                        Unread
                    </button>
                    <button @click="statusFilter = 'human'" 
                            class="flex-1 py-1 text-[11px] font-medium rounded transition-colors"
                            :class="statusFilter === 'human' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:bg-gray-800/50'">
                        Human
                    </button>
                </div>
            </div>

            <!-- Chat List Items -->
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <!-- Loading Skeleton -->
                <template x-if="isLoadingConversations && conversations.length === 0">
                    <div class="p-4 space-y-4">
                        <template x-for="i in 5">
                            <div class="animate-pulse flex space-x-3">
                                <div class="rounded-full bg-gray-800 h-12 w-12 shrink-0"></div>
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-2 bg-gray-800 rounded w-3/4"></div>
                                    <div class="h-2 bg-gray-800 rounded w-1/2"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="!isLoadingConversations && filteredConversations.length === 0">
                    <div class="p-8 text-center flex flex-col items-center justify-center h-full text-gray-500">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                        <p class="text-sm">No conversations found</p>
                        <p class="text-xs mt-1 opacity-70" x-show="search">Try adjusting your search</p>
                    </div>
                </template>

                <!-- List -->
                <template x-for="chat in filteredConversations" :key="chat.platform + '-' + chat.id">
                    <div 
                        @click="selectChat(chat)"
                        class="p-3 cursor-pointer transition-colors relative hover:bg-gray-800/50 group border-b border-gray-800/50"
                        :class="isActiveChat(chat) ? 'bg-gray-800 border-l-2 border-l-primary' : 'border-l-2 border-l-transparent'"
                    >
                        <div class="flex gap-3">
                            <!-- Avatar with Platform Badge -->
                            <div class="relative shrink-0">
                                <div class="h-12 w-12 rounded-full flex items-center justify-center text-white font-bold text-lg border border-gray-600"
                                     :class="chat.platform === 'instagram' ? 'bg-gradient-to-br from-purple-600 to-pink-600' : 'bg-gray-700'">
                                    <template x-if="chat.avatar">
                                        <img :src="chat.avatar" class="w-full h-full rounded-full object-cover">
                                    </template>
                                    <template x-if="!chat.avatar">
                                        <span x-text="getInitials(chat.name)"></span>
                                    </template>
                                </div>
                                <!-- Platform Badge -->
                                <div class="absolute -bottom-1 -right-1 rounded-full p-0.5"
                                     :class="chat.platform === 'whatsapp' ? 'bg-whatsapp' : 'bg-instagram'">
                                    <span class="material-symbols-outlined text-[10px] text-white" 
                                          x-text="chat.platform === 'whatsapp' ? 'chat' : 'photo_camera'"></span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <div class="flex justify-between items-center mb-0.5">
                                    <span class="font-medium text-white text-sm truncate" x-text="chat.name"></span>
                                    <span class="text-[10px] text-gray-500 shrink-0 ml-2" x-text="chat.last_message_time"></span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs truncate flex-1" 
                                       :class="chat.unread_count > 0 ? 'font-semibold text-white' : 'text-gray-400'"
                                       x-text="chat.last_message"></p>
                                    
                                    <!-- Unread Badge -->
                                    <template x-if="chat.unread_count > 0">
                                        <span class="bg-primary text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center" 
                                              x-text="chat.unread_count"></span>
                                    </template>
                                    
                                    <!-- Status Indicator -->
                                    <template x-if="chat.unread_count === 0">
                                        <span class="w-2 h-2 rounded-full shrink-0" 
                                              :class="{
                                                  'bg-whatsapp': chat.platform === 'whatsapp' && chat.status === 'bot_active',
                                                  'bg-amber-500': chat.status === 'agent_handling' || chat.status === 'human',
                                                  'bg-instagram': chat.platform === 'instagram' && chat.status === 'bot_handling',
                                                  'bg-gray-500': chat.status === 'resolved' || chat.status === 'closed'
                                              }"
                                              :title="chat.status"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- COLUMN 2: CHAT AREA (Flex Grow) -->
        <div :class="activeChat ? 'flex' : 'hidden lg:flex'" class="flex-1 flex-col bg-[#0b141a] relative min-w-0 {{ session()->has('impersonating_from_admin') ? 'mt-11' : '' }}">
            
            <!-- Chat Background -->
            <div class="absolute inset-0 z-0 chat-bg"></div>

            <!-- Empty State -->
            <template x-if="!activeChat">
                <div class="flex-1 flex flex-col items-center justify-center z-10 text-center p-8">
                    <div class="w-32 h-32 bg-gray-800/50 rounded-full flex items-center justify-center mb-6 backdrop-blur-sm relative">
                        <span class="material-symbols-outlined text-6xl text-gray-500">inbox</span>
                        <!-- Platform Icons -->
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-whatsapp rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-lg">chat</span>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-instagram rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-lg">photo_camera</span>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-200 mb-2">Unified Inbox</h2>
                    <p class="text-gray-400 max-w-md">Manage all your WhatsApp and Instagram conversations in one place.<br>Select a conversation to start chatting.</p>
                    <div class="mt-8 flex items-center gap-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-whatsapp">check_circle</span>
                            <span>WhatsApp Ready</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-instagram">check_circle</span>
                            <span>Instagram Ready</span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Active Chat -->
            <template x-if="activeChat">
                <div class="flex-1 flex flex-col h-full z-10 relative">
                    
                    <!-- Chat Header -->
                    <div class="h-16 px-4 py-2 bg-gray-800/90 backdrop-blur-md flex items-center justify-between border-b border-gray-700 shrink-0 z-20">
                        <div class="flex items-center gap-3">
                            <button @click="activeChat = null" class="lg:hidden p-1 text-gray-400 hover:text-white">
                                <span class="material-symbols-outlined">arrow_back</span>
                            </button>
                            
                            <!-- Avatar -->
                            <div class="relative">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold border border-gray-600"
                                     :class="activeChat.platform === 'instagram' ? 'bg-gradient-to-br from-purple-600 to-pink-600' : 'bg-gray-700'">
                                    <template x-if="activeChat.avatar">
                                        <img :src="activeChat.avatar" class="w-full h-full rounded-full object-cover">
                                    </template>
                                    <template x-if="!activeChat.avatar">
                                        <span x-text="getInitials(activeChat.name)"></span>
                                    </template>
                                </div>
                                <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full flex items-center justify-center"
                                     :class="activeChat.platform === 'whatsapp' ? 'bg-whatsapp' : 'bg-instagram'">
                                    <span class="material-symbols-outlined text-[10px] text-white" 
                                          x-text="activeChat.platform === 'whatsapp' ? 'chat' : 'photo_camera'"></span>
                                </div>
                            </div>
                            
                            <!-- Info -->
                            <div>
                                <h3 class="font-semibold text-white text-sm" x-text="activeChat.name"></h3>
                                <p class="text-xs text-gray-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full" 
                                          :class="{
                                              'bg-whatsapp': activeChat.platform === 'whatsapp' && activeChat.status === 'bot_active',
                                              'bg-amber-500': activeChat.status === 'agent_handling' || activeChat.status === 'human',
                                              'bg-instagram': activeChat.platform === 'instagram' && activeChat.status === 'bot_handling'
                                          }"></span>
                                    <span x-text="getStatusLabel(activeChat)"></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Platform-specific Actions -->
                            <template x-if="activeChat.platform === 'whatsapp'">
                                <div class="flex items-center gap-2">
                                    <button x-show="activeChat.status === 'bot_active'" @click="takeoverChat()" 
                                            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white rounded-md text-xs font-medium transition-colors border border-gray-600">
                                        <span class="material-symbols-outlined text-sm">pan_tool</span>
                                        Takeover
                                    </button>
                                    <button x-show="activeChat.status !== 'bot_active'" @click="handbackToBot()"
                                            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-whatsapp hover:bg-whatsapp-dark text-white rounded-md text-xs font-medium transition-colors">
                                        <span class="material-symbols-outlined text-sm">smart_toy</span>
                                        Auto-Reply
                                    </button>
                                </div>
                            </template>
                            
                            <template x-if="activeChat.platform === 'instagram'">
                                <div class="flex items-center gap-2">
                                    <button x-show="activeChat.status === 'bot_handling'" @click="takeoverIgChat()" 
                                            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white rounded-md text-xs font-medium transition-colors border border-gray-600">
                                        <span class="material-symbols-outlined text-sm">pan_tool</span>
                                        Takeover
                                    </button>
                                    <button x-show="activeChat.status !== 'bot_handling'" @click="handbackIgToBot()"
                                            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-instagram hover:bg-instagram-dark text-white rounded-md text-xs font-medium transition-colors">
                                        <span class="material-symbols-outlined text-sm">smart_toy</span>
                                        Auto-Reply
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div id="messages-container" class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-2">
                        <!-- Loading -->
                        <div x-show="isLoadingMessages" class="flex justify-center py-8">
                            <span class="material-symbols-outlined animate-spin text-primary text-3xl">sync</span>
                        </div>

                        <!-- Messages Loop -->
                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex flex-col w-full" :class="msg.is_from_me ? 'items-end' : 'items-start'">
                                <div class="message-bubble max-w-[85%] sm:max-w-[65%] px-3 py-1.5 shadow-sm text-sm"
                                     :class="msg.is_from_me ? 'bg-bubble-out text-white message-out' : 'bg-bubble-in text-white message-in'">
                                    
                                    <!-- Text Message -->
                                    <p class="whitespace-pre-wrap break-words leading-relaxed" x-text="msg.message || msg.content"></p>
                                    
                                    <!-- Metadata -->
                                    <div class="flex items-center justify-end gap-1 mt-1 select-none opacity-70">
                                        <span class="text-[10px]" x-text="msg.time"></span>
                                        <template x-if="msg.is_from_me && msg.platform === 'whatsapp'">
                                            <span class="material-symbols-outlined text-[12px]" 
                                                :class="msg.status === 'read' ? 'text-blue-300' : 'text-gray-300'"
                                                x-text="msg.status === 'read' ? 'done_all' : 'done'">
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                
                                <!-- Bot Reply Indicator -->
                                <template x-if="msg.is_bot_reply">
                                    <div class="text-[10px] text-gray-500 mt-0.5 flex items-center gap-1 px-1">
                                        <span class="material-symbols-outlined text-[10px]">smart_toy</span>
                                        <span>AI Reply</span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <!-- Typing Indicator -->
                        <div x-show="isTyping" class="flex items-start">
                             <div class="bg-bubble-in px-4 py-2 rounded-2xl rounded-tl-none typing-indicator flex gap-1">
                                 <span class="w-1.5 h-1.5 bg-gray-400 rounded-full block"></span>
                                 <span class="w-1.5 h-1.5 bg-gray-400 rounded-full block"></span>
                                 <span class="w-1.5 h-1.5 bg-gray-400 rounded-full block"></span>
                             </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="min-h-[64px] bg-gray-800/90 backdrop-blur-md px-4 py-3 flex items-end gap-3 border-t border-gray-700 shrink-0 z-20">
                        <!-- Attachment -->
                        <button @click="$refs.fileInput.click()" class="p-2 text-gray-400 hover:text-gray-300 rounded-full hover:bg-gray-700 transition-colors mb-0.5">
                            <span class="material-symbols-outlined text-xl">attach_file</span>
                        </button>
                        <input type="file" x-ref="fileInput" class="hidden" @change="selectedFile = $event.target.files[0]">

                        <!-- Input Field -->
                        <div class="flex-1 bg-gray-700/50 rounded-xl flex items-center border border-gray-600 focus-within:border-gray-500 focus-within:bg-gray-700 transition-all">
                            <!-- Selected File Preview -->
                            <template x-if="selectedFile">
                                <div class="px-3 py-1 flex items-center gap-2 border-r border-gray-600 mr-2">
                                    <span class="text-xs text-white truncate max-w-[100px]" x-text="selectedFile.name"></span>
                                    <button @click="clearFile()" class="text-gray-400 hover:text-red-400">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                            </template>
                            
                            <textarea 
                                x-model="newMessage" 
                                @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                                :placeholder="'Message ' + (activeChat?.name || '')" 
                                class="w-full bg-transparent border-0 focus:ring-0 text-white placeholder-gray-400 text-sm py-3 px-4 resize-none max-h-32 custom-scrollbar"
                                rows="1"
                                style="min-height: 44px;"
                            ></textarea>
                        </div>

                        <!-- Send Button -->
                        <button @click="sendMessage()" 
                                :disabled="!newMessage.trim() && !selectedFile || isSending"
                                class="p-3 disabled:opacity-50 disabled:hover:bg-primary text-white rounded-full transition-all shadow-lg mb-0.5 group"
                                :class="activeChat?.platform === 'instagram' ? 'bg-instagram hover:bg-instagram-dark' : 'bg-whatsapp hover:bg-whatsapp-dark'">
                            <span x-show="!isSending" class="material-symbols-outlined text-xl group-hover:translate-x-0.5 transition-transform">send</span>
                            <span x-show="isSending" class="material-symbols-outlined animate-spin text-xl">sync</span>
                        </button>
                    </div>

                </div>
            </template>
        </div>

    </main>

</div>

<!-- ALPINE JS LOGIC -->
<script>
    function unifiedInbox() {
        return {
            conversations: [],
            activeChat: null,
            messages: [],
            search: '',
            platformFilter: 'all', // all, whatsapp, instagram
            statusFilter: 'all', // all, unread, human
            
            isLoadingConversations: false,
            isLoadingMessages: false,
            isSending: false,
            isTyping: false,
            
            newMessage: '',
            selectedFile: null,
            
            unreadCounts: {
                whatsapp: 0,
                instagram: 0,
                total: 0
            },

            // Computed: Filtered conversations
            get filteredConversations() {
                let filtered = this.conversations;

                // Platform Filter
                if (this.platformFilter !== 'all') {
                    filtered = filtered.filter(c => c.platform === this.platformFilter);
                }
                
                // Status Filter
                if (this.statusFilter === 'unread') {
                    filtered = filtered.filter(c => c.unread_count > 0);
                } else if (this.statusFilter === 'human') {
                    filtered = filtered.filter(c => 
                        c.status === 'agent_handling' || 
                        c.status === 'human' || 
                        c.status === 'needs_attention'
                    );
                }

                return filtered;
            },

            // Initialize component
            init() {
                this.fetchConversations();
                this.fetchUnreadCounts();
                
                // Real-time listeners
                this.initRealtimeListeners();
                
                // Poll for updates every 30 seconds
                setInterval(() => {
                    this.fetchConversations(false);
                    this.fetchUnreadCounts();
                }, 30000);
            },

            // Initialize real-time listeners (Echo/Pusher)
            initRealtimeListeners() {
                // WhatsApp listener
                if (window.Echo) {
                    window.Echo.private('whatsapp')
                        .listen('NewWhatsAppMessage', (e) => {
                            console.log('New WhatsApp Message:', e.message);
                            this.handleNewMessage(e.message, 'whatsapp');
                        });
                    
                    // Instagram listener
                    window.Echo.private('instagram.' + (window.userId || ''))
                        .listen('NewInstagramMessage', (e) => {
                            console.log('New Instagram Message:', e.message);
                            this.handleNewMessage(e.message, 'instagram');
                        });
                }
            },

            // Fetch conversations from API
            async fetchConversations(showLoading = true) {
                if (showLoading) this.isLoadingConversations = true;
                
                try {
                    let url = '{{ route("chat.api.conversations") }}';
                    const params = new URLSearchParams();
                    
                    if (this.platformFilter !== 'all') {
                        params.append('platform', this.platformFilter);
                    }
                    if (this.search) {
                        params.append('search', this.search);
                    }
                    
                    if (params.toString()) {
                        url += '?' + params.toString();
                    }
                    
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    // Simple diff to avoid redraw if same
                    if (JSON.stringify(this.conversations) !== JSON.stringify(data)) {
                        this.conversations = data;
                    }
                } catch (error) {
                    console.error('Error fetching conversations:', error);
                } finally {
                    if (showLoading) this.isLoadingConversations = false;
                }
            },

            // Fetch unread counts
            async fetchUnreadCounts() {
                try {
                    const response = await fetch('{{ route("chat.api.unread-counts") }}');
                    const data = await response.json();
                    this.unreadCounts = data;
                } catch (error) {
                    console.error('Error fetching unread counts:', error);
                }
            },

            // Handle new incoming message
            handleNewMessage(msg, platform) {
                // Refresh conversations list
                this.fetchConversations(false);
                
                // If chat is open and matches the message, append to messages
                if (this.activeChat) {
                    const matches = platform === 'whatsapp' 
                        ? this.activeChat.identifier === msg.phone_number
                        : this.activeChat.identifier == msg.conversation_id;
                    
                    if (matches) {
                        // Check if message already exists
                        const exists = this.messages.find(m => m.id === msg.id);
                        if (!exists) {
                            this.messages.push({
                                ...msg,
                                platform: platform,
                                is_from_me: msg.direction === 'outgoing' || msg.sender_type === 'agent'
                            });
                            this.scrollToBottom();
                        }
                    }
                }
                
                // Update unread counts
                this.fetchUnreadCounts();
            },

            // Set platform filter
            setPlatformFilter(filter) {
                this.platformFilter = filter;
                this.fetchConversations();
            },

            // Select a chat
            async selectChat(chat) {
                if (this.isActiveChat(chat)) return;
                
                this.activeChat = chat;
                this.messages = [];
                this.clearFile();
                
                await this.fetchMessages(chat);
                this.scrollToBottom();
            },

            // Check if chat is currently active
            isActiveChat(chat) {
                if (!this.activeChat) return false;
                return this.activeChat.platform === chat.platform && 
                       this.activeChat.identifier === chat.identifier;
            },

            // Fetch messages for a conversation
            async fetchMessages(chat, showLoading = true) {
                if (showLoading) this.isLoadingMessages = true;
                
                try {
                    const url = `{{ url('/chat/api/messages') }}/${chat.platform}/${chat.identifier}`;
                    const response = await fetch(url);
                    const data = await response.json();
                    this.messages = data;
                } catch (error) {
                    console.error('Error fetching messages:', error);
                } finally {
                    if (showLoading) this.isLoadingMessages = false;
                }
            },

            // Send message
            async sendMessage() {
                if (!this.newMessage.trim() && !this.selectedFile) return;
                if (!this.activeChat) return;
                
                this.isSending = true;

                try {
                    const response = await fetch('{{ route("chat.api.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            platform: this.activeChat.platform,
                            identifier: this.activeChat.identifier,
                            message: this.newMessage
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.newMessage = '';
                        this.clearFile();
                        await this.fetchMessages(this.activeChat, false);
                        await this.fetchConversations(false);
                    } else {
                        alert('Failed: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error sending:', error);
                    alert('Error sending message');
                } finally {
                    this.isSending = false;
                }
            },

            // Scroll to bottom of messages
            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },

            // Get initials from name
            getInitials(name) {
                if (!name) return '?';
                return name.substring(0, 2).toUpperCase();
            },

            // Get status label
            getStatusLabel(chat) {
                if (chat.platform === 'whatsapp') {
                    if (chat.status === 'bot_active') return 'Bot Active';
                    if (chat.status === 'agent_handling') return 'Human Support';
                    if (chat.status === 'idle') return 'Idle';
                } else {
                    if (chat.status === 'bot_handling') return 'Bot Active';
                    if (chat.status === 'agent_handling') return 'Human Support';
                    if (chat.status === 'open') return 'Open';
                }
                return chat.status;
            },

            // Clear selected file
            clearFile() {
                this.selectedFile = null;
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            },

            // WhatsApp: Takeover chat
            async takeoverChat() {
                if (!this.activeChat || this.activeChat.platform !== 'whatsapp') return;
                
                try {
                    const response = await fetch(`/takeover/wa/${this.activeChat.identifier}/takeover`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.activeChat.status = 'agent_handling';
                        this.fetchConversations(false);
                    }
                } catch (e) { console.error(e); }
            },

            // WhatsApp: Handback to bot
            async handbackToBot() {
                if (!this.activeChat || this.activeChat.platform !== 'whatsapp') return;
                
                try {
                    const response = await fetch(`/takeover/wa/${this.activeChat.identifier}/handback`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.activeChat.status = 'bot_active';
                        this.fetchConversations(false);
                    }
                } catch (e) { console.error(e); }
            },

            // Instagram: Takeover chat
            async takeoverIgChat() {
                if (!this.activeChat || this.activeChat.platform !== 'instagram') return;
                
                try {
                    const response = await fetch(`/takeover/ig/${this.activeChat.id}/takeover`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.activeChat.status = 'agent_handling';
                        this.fetchConversations(false);
                    }
                } catch (e) { console.error(e); }
            },

            // Instagram: Handback to bot
            async handbackIgToBot() {
                if (!this.activeChat || this.activeChat.platform !== 'instagram') return;
                
                try {
                    const response = await fetch(`/takeover/ig/${this.activeChat.id}/handback`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.activeChat.status = 'bot_handling';
                        this.fetchConversations(false);
                    }
                } catch (e) { console.error(e); }
            }
        }
    }
</script>

</body>
</html>
