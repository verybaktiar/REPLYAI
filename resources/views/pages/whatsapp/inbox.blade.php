<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Inbox - REPLYAI</title>
    <link rel="manifest" href="/manifest.json?v=2">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
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
                        "background-light": "#f6f6f8",
                        "background-dark": "#0f172a",
                        "surface-dark": "#1e293b",
                        "border-dark": "#334155",
                        "text-secondary": "#94a3b8",
                        "bubble-in": "#334155",
                        "bubble-out": "#059669",
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
    </style>
</head>
<body class="bg-gray-950 font-display text-white antialiased overflow-hidden flex flex-col h-[100dvh]">
    
<!-- ROOT CONTAINER -->
<div class="flex-1 min-h-0 bg-gray-950 flex overflow-hidden" x-data="whatsappInbox()" x-init="init()">

    <!-- SIDEBAR (Dynamic Impersonation Padding) -->
    @include('components.sidebar')
    
    <!-- MAIN CONTENT -->
    <main class="flex-1 min-w-0 flex flex-row h-full overflow-hidden">

        <!-- COLUMN 1: CHAT LIST (Fixed width 360px on desktop) -->
        <div :class="activeChat ? 'hidden lg:flex' : 'flex'" class="w-full lg:w-[360px] flex-col border-r border-gray-800 bg-gray-900 shrink-0 {{ session()->has('impersonating_from_admin') ? 'mt-11' : '' }}">

            <!-- Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 shrink-0 bg-gray-900">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-white tracking-tight">Chats</h2>
                    <div class="bg-blue-600/20 text-blue-400 text-xs px-2 py-0.5 rounded-full font-medium" x-text="conversations.length"></div>
                </div>
                <div class="flex gap-1">
                    <button @click="fetchConversations()" class="p-2 hover:bg-gray-800 rounded-lg text-gray-400 hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-xl">refresh</span>
                    </button>
                    <!-- Add Contact Button (Future) -->
                    <!-- <button class="p-2 hover:bg-gray-800 rounded-lg text-gray-400 hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-xl">add_comment</span>
                    </button> -->
                </div>
            </div>
            
            <!-- Search & Filters -->
            <div class="p-3 border-b border-gray-800 space-y-3 shrink-0 bg-gray-900 z-10">
                <!-- Search -->
                <div class="relative group">
                    <input 
                        type="text" 
                        x-model="search"
                        placeholder="Search or start new chat" 
                        class="w-full bg-gray-800/50 border border-gray-700 text-white rounded-lg pl-9 pr-4 py-2 text-sm focus:ring-1 focus:ring-whatsapp focus:border-whatsapp placeholder-gray-500 transition-all"
                    >
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-gray-500 text-lg group-focus-within:text-whatsapp transition-colors">search</span>
                </div>
                
                <!-- Filter Tabs -->
                <div class="flex gap-2">
                    <button @click="activeTab = 'all'" 
                            class="flex-1 py-1.5 text-xs font-medium rounded-md transition-colors"
                            :class="activeTab === 'all' ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-400 hover:bg-gray-800/50 hover:text-gray-300'">
                        All
                    </button>
                    <button @click="activeTab = 'unread'" 
                            class="flex-1 py-1.5 text-xs font-medium rounded-md transition-colors relative"
                            :class="activeTab === 'unread' ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-400 hover:bg-gray-800/50 hover:text-gray-300'">
                        Unread
                        <span x-show="unreadCount > 0" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-whatsapp rounded-full border-2 border-gray-900"></span>
                    </button>
                    <button @click="activeTab = 'human'" 
                            class="flex-1 py-1.5 text-xs font-medium rounded-md transition-colors"
                            :class="activeTab === 'human' ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-400 hover:bg-gray-800/50 hover:text-gray-300'">
                        Human
                    </button>
                </div>

                <!-- Device Filter (Horizontal Scroll) -->
                <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide" x-show="devices.length > 0">
                    <button @click="filterDevice = null; fetchConversations()" 
                            class="px-2.5 py-1 rounded-md text-[11px] font-medium whitespace-nowrap transition-colors border border-transparent"
                            :class="!filterDevice ? 'bg-whatsapp/10 text-whatsapp border-whatsapp/20' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'">
                        All Devices
                    </button>
                    <template x-for="device in devices" :key="device.session_id">
                        <button @click="filterDevice = device.session_id; fetchConversations()"
                                class="px-2.5 py-1 rounded-md text-[11px] font-medium whitespace-nowrap transition-colors flex items-center gap-1.5 border border-transparent"
                                :class="filterDevice === device.session_id ? 'bg-gray-700 text-white border-gray-600' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'">
                            <span class="w-1.5 h-1.5 rounded-full" :style="'background-color:' + device.color"></span>
                            <span x-text="device.device_name" class="truncate max-w-[80px]"></span>
                        </button>
                    </template>
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
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">forum</span>
                        <p class="text-sm">No conversations found</p>
                    </div>
                </template>

                <!-- List -->
                <template x-for="chat in filteredConversations" :key="chat.phone_number">
                    <div 
                        @click="selectChat(chat)"
                        class="p-3 cursor-pointer transition-colors relative hover:bg-gray-800/50 group border-b border-gray-800/50"
                        :class="activeChat?.phone_number === chat.phone_number ? 'bg-gray-800 border-l-2 border-l-whatsapp' : 'border-l-2 border-l-transparent'"
                    >
                        <div class="flex gap-3">
                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gray-700 flex items-center justify-center text-gray-300 font-bold text-lg border border-gray-600">
                                    <span x-text="getInitials(chat.name)"></span>
                                </div>
                                <!-- Status Badge (Bot/Human) -->
                                <div class="absolute -bottom-1 -right-1 bg-gray-900 rounded-full p-0.5">
                                    <span class="block w-3.5 h-3.5 rounded-full border-2 border-gray-900 flex items-center justify-center text-[8px]"
                                          :class="{
                                              'bg-whatsapp text-white': chat.status === 'bot_active',
                                              'bg-amber-500 text-white': chat.status === 'agent_handling',
                                              'bg-red-500 text-white': chat.status === 'idle'
                                          }">
                                          <span class="material-symbols-outlined text-[8px]" 
                                                x-text="chat.status === 'bot_active' ? 'smart_toy' : 'person'"></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <div class="flex justify-between items-center mb-0.5">
                                    <span class="font-medium text-white text-sm truncate" x-text="chat.name"></span>
                                    <span class="text-[10px] text-gray-500 shrink-0 ml-2" x-text="chat.last_message_time"></span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs text-gray-400 truncate flex-1" :class="chat.unread > 0 ? 'font-semibold text-white' : ''" x-text="chat.last_message"></p>
                                    
                                    <!-- Unread Badge -->
                                    <template x-if="chat.unread > 0">
                                        <span class="bg-whatsapp text-gray-900 text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center" x-text="chat.unread"></span>
                                    </template>
                                    
                                    <!-- Device Dot -->
                                    <template x-if="chat.unread === 0">
                                        <span class="w-2 h-2 rounded-full shrink-0" 
                                              :style="'background-color:' + (chat.device_color || '#666')"
                                              :title="chat.device_name"></span>
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

            <template x-if="!activeChat">
                <div class="flex-1 flex flex-col items-center justify-center z-10 text-center p-8">
                    <div class="w-32 h-32 bg-gray-800/50 rounded-full flex items-center justify-center mb-6 backdrop-blur-sm">
                        <span class="material-symbols-outlined text-6xl text-gray-500">forum</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-200 mb-2">WhatsApp for Web</h2>
                    <p class="text-gray-400 max-w-md">Send and receive messages without keeping your phone online.<br>Use WhatsApp on up to 4 linked devices and 1 phone.</p>
                    <div class="mt-8 flex items-center gap-2 text-xs text-gray-500">
                        <span class="material-symbols-outlined text-sm">lock</span>
                        End-to-end encrypted
                    </div>
                </div>
            </template>

            <template x-if="activeChat">
                <div class="flex-1 flex flex-col h-full z-10 relative">
                    
                    <!-- Chat Header -->
                    <div class="h-16 px-4 py-2 bg-gray-800/90 backdrop-blur-md flex items-center justify-between border-b border-gray-700 shrink-0 z-20">
                        <div class="flex items-center gap-3">
                            <button @click="activeChat = null" class="lg:hidden p-1 text-gray-400 hover:text-white">
                                <span class="material-symbols-outlined">arrow_back</span>
                            </button>
                            <div class="h-10 w-10 rounded-full bg-gray-700 flex items-center justify-center text-white font-bold border border-gray-600">
                                <span x-text="getInitials(activeChat.name)"></span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white text-sm" x-text="activeChat.name"></h3>
                                <p class="text-xs text-gray-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="activeChat.status === 'bot_active' ? 'bg-whatsapp' : 'bg-amber-500'"></span>
                                    <span x-text="activeChat.status === 'bot_active' ? 'Bot Active' : 'Human Support'"></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Takeover/Handback Actions -->
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
                            
                            <!-- Toggle CRM Panel -->
                            <button @click="showDetailsPanel = !showDetailsPanel" 
                                    class="p-2 rounded-lg transition-colors"
                                    :class="showDetailsPanel ? 'bg-whatsapp text-white' : 'hover:bg-gray-700 text-gray-400'">
                                <span class="material-symbols-outlined">dock_to_left</span>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div id="messages-container" class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-2">
                        <!-- Loading -->
                        <div x-show="isLoadingMessages" class="flex justify-center py-8">
                            <span class="material-symbols-outlined animate-spin text-whatsapp text-3xl">sync</span>
                        </div>

                        <!-- Messages Loop -->
                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex flex-col w-full" :class="msg.is_from_me ? 'items-end' : 'items-start'">
                                <div class="message-bubble max-w-[85%] sm:max-w-[65%] px-3 py-1.5 shadow-sm text-sm"
                                     :class="msg.is_from_me ? 'bg-bubble-out text-white message-out' : 'bg-bubble-in text-white message-in'">
                                    
                                    <!-- Sender Name (Group context) -->
                                    <template x-if="!msg.is_from_me && msg.push_name">
                                        <div class="text-[10px] font-bold text-orange-400 mb-0.5" x-text="msg.push_name"></div>
                                    </template>

                                    <!-- Media -->
                                    <template x-if="msg.media_url">
                                        <div class="mb-1 mt-1">
                                            <template x-if="msg.message_type === 'image'">
                                                <img :src="msg.media_url" class="rounded-lg max-h-64 object-cover border border-white/10" @click="window.open(msg.media_url, '_blank')">
                                            </template>
                                            <template x-if="msg.message_type !== 'image'">
                                                <a :href="msg.media_url" target="_blank" class="flex items-center gap-2 p-2 bg-black/20 rounded-lg hover:bg-black/30 transition">
                                                    <span class="material-symbols-outlined">attach_file</span>
                                                    <span class="truncate">View Attachment</span>
                                                </a>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Text Message -->
                                    <p class="whitespace-pre-wrap break-words leading-relaxed" x-text="msg.message"></p>
                                    
                                    <!-- Metadata -->
                                    <div class="flex items-center justify-end gap-1 mt-1 select-none opacity-70">
                                        <span class="text-[10px]" x-text="msg.time"></span>
                                        <template x-if="msg.is_from_me">
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
                                placeholder="Type a message" 
                                class="w-full bg-transparent border-0 focus:ring-0 text-white placeholder-gray-400 text-sm py-3 px-4 resize-none max-h-32 custom-scrollbar"
                                rows="1"
                                style="min-height: 44px;"
                            ></textarea>
                        </div>

                        <!-- Send Button -->
                        <button @click="sendMessage()" 
                                :disabled="!newMessage.trim() && !selectedFile || isSending"
                                class="p-3 bg-whatsapp hover:bg-whatsapp-dark disabled:opacity-50 disabled:hover:bg-whatsapp text-white rounded-full transition-all shadow-lg mb-0.5 group">
                            <span x-show="!isSending" class="material-symbols-outlined text-xl group-hover:translate-x-0.5 transition-transform">send</span>
                            <span x-show="isSending" class="material-symbols-outlined animate-spin text-xl">sync</span>
                        </button>
                    </div>

                </div>
            </template>
        </div>

        <!-- COLUMN 3: CRM / DETAILS PANEL (Right Sidebar) -->
        <div x-show="activeChat && showDetailsPanel" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             class="w-[320px] bg-gray-900 border-l border-gray-800 flex flex-col shrink-0 {{ session()->has('impersonating_from_admin') ? 'mt-11' : '' }}">
             
             <!-- CRM Header -->
            <div class="h-16 flex items-center px-4 border-b border-gray-800 shrink-0 bg-gray-900">
                <span class="font-bold text-gray-200">Contact Info</span>
                <button @click="showDetailsPanel = false" class="ml-auto text-gray-400 hover:text-white lg:hidden">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-6">
                
                <!-- Profile Card -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-gray-700 rounded-full mx-auto flex items-center justify-center text-2xl font-bold text-gray-300 mb-3 border-2 border-gray-800">
                        <span x-text="getInitials(activeChat.name)"></span>
                    </div>
                    <h3 class="font-bold text-white text-lg" x-text="activeChat.name"></h3>
                    <p class="text-gray-400 text-sm" x-text="activeChat.formatted_phone || activeChat.phone_number"></p>
                    <div class="mt-2 flex justify-center gap-2">
                         <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-800 border border-gray-700 text-gray-400" x-text="activeChat.device_name"></span>
                    </div>
                </div>

                <!-- Tabs -->
                <div x-data="{ tab: 'details' }" class="flex flex-col h-full">
                    <div class="flex border-b border-gray-800 mb-4">
                        <button @click="tab = 'details'" :class="tab === 'details' ? 'border-whatsapp text-whatsapp' : 'border-transparent text-gray-400 hover:text-gray-300'" class="flex-1 pb-2 text-xs font-medium border-b-2 transition-colors">Details</button>
                        <button @click="tab = 'notes'" :class="tab === 'notes' ? 'border-whatsapp text-whatsapp' : 'border-transparent text-gray-400 hover:text-gray-300'" class="flex-1 pb-2 text-xs font-medium border-b-2 transition-colors">Notes</button>
                        <button @click="tab = 'ai'" :class="tab === 'ai' ? 'border-whatsapp text-whatsapp' : 'border-transparent text-gray-400 hover:text-gray-300'" class="flex-1 pb-2 text-xs font-medium border-b-2 transition-colors">AI Insight</button>
                    </div>

                    <!-- DETAILS TAB -->
                    <div x-show="tab === 'details'" class="space-y-4">
                        <!-- Tags Section -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-bold text-gray-500 uppercase">Tags</h4>
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="text-xs text-whatsapp hover:underline">+ Add</button>
                                    
                                    <!-- Tag Dropdown -->
                                    <div x-show="open" @click.outside="open = false" class="absolute right-0 top-6 w-48 bg-gray-800 border border-gray-700 rounded shadow-lg z-50 p-1">
                                        <template x-for="tag in availableTags" :key="tag.id">
                                            <button @click="attachTag(tag.id); open = false" class="w-full text-left px-3 py-1.5 text-xs text-gray-300 hover:bg-gray-700 rounded block">
                                                <span x-text="tag.name"></span>
                                            </button>
                                        </template>
                                        <div x-show="availableTags.length === 0" class="px-3 py-2 text-xs text-gray-500 text-center">No tags available</div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="tag in tags" :key="tag.id">
                                    <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-400 text-xs border border-blue-500/30 flex items-center gap-1">
                                        <span x-text="tag.name"></span>
                                        <button @click="detachTag(tag.id)" class="hover:text-white"><span class="material-symbols-outlined text-[10px]">close</span></button>
                                    </span>
                                </template>
                                <span x-show="tags.length === 0" class="text-xs text-gray-600 italic">No tags assigned</span>
                            </div>
                        </div>
                    </div>

                    <!-- NOTES TAB -->
                    <div x-show="tab === 'notes'" class="space-y-4">
                         <div class="space-y-2">
                             <textarea x-model="newNote" placeholder="Add a note..." class="w-full bg-gray-800 border-gray-700 rounded text-xs text-white p-2 focus:ring-whatsapp focus:border-whatsapp resize-none h-20"></textarea>
                             <button @click="storeNote()" :disabled="isSubmittingNote || !newNote.trim()" class="w-full py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded text-xs text-white transition-colors">
                                 Save Note
                             </button>
                         </div>
                         
                         <div class="space-y-3 mt-4 max-h-[300px] overflow-y-auto pr-1 custom-scrollbar">
                             <template x-if="isLoadingNotes">
                                 <div class="text-center text-gray-500 text-xs py-2">Loading notes...</div>
                             </template>
                             <template x-for="note in notes" :key="note.id">
                                 <div class="bg-gray-800/50 p-3 rounded border border-gray-800">
                                     <p class="text-xs text-gray-300 mb-2 whitespace-pre-wrap" x-text="note.content"></p>
                                     <div class="flex justify-between items-center text-[10px] text-gray-500">
                                         <span x-text="note.author?.name || 'System'"></span>
                                         <span x-text="new Date(note.created_at).toLocaleDateString()"></span>
                                     </div>
                                 </div>
                             </template>
                             <template x-if="!isLoadingNotes && notes.length === 0">
                                 <div class="text-center text-gray-600 text-xs italic">No notes yet</div>
                             </template>
                         </div>
                    </div>

                    <!-- AI TAB -->
                    <div x-show="tab === 'ai'" class="space-y-4">
                        <button @click="fetchAiInsight()" class="w-full py-2 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600/30 rounded-lg text-xs font-medium border border-indigo-500/30 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">psychology</span>
                            Analyze Conversation
                        </button>

                        <div x-show="isAiLoading" class="text-center py-4">
                            <span class="material-symbols-outlined animate-spin text-indigo-500">sync</span>
                        </div>

                        <div x-show="aiSummary" class="bg-gray-800/50 rounded-lg p-3 border border-gray-800">
                            <h4 class="text-xs font-bold text-gray-400 mb-2 uppercase">Summary</h4>
                            <p class="text-xs text-gray-300 leading-relaxed" x-text="aiSummary"></p>
                        </div>
                        
                        <div x-show="aiSuggestions.length > 0" class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase">Suggested Replies</h4>
                            <template x-for="sug in aiSuggestions">
                                <button @click="newMessage = sug; $refs.fileInput.focus()" class="w-full text-left p-2 bg-gray-800 hover:bg-gray-700 rounded border border-gray-700 text-xs text-gray-300 transition-colors">
                                    <span x-text="sug"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </main>

</div>

<!-- ALPINE JS LOGIC -->
<script>
    function whatsappInbox() {
        return {
            conversations: [],
            activeChat: null,
            messages: [],
            search: '',
            filterDevice: null,
            activeTab: 'all', // all, unread, human
            
            isLoadingConversations: false,
            isLoadingMessages: false,
            isSending: false,
            isTyping: false,
            
            newMessage: '',
            selectedFile: null,
            
            // CRM
            showDetailsPanel: true,
            notes: [],
            newNote: '',
            isLoadingNotes: false,
            isSubmittingNote: false,
            tags: [],
            availableTags: [],
            isLoadingTags: false,
            
            // AI
            aiSummary: '',
            aiSuggestions: [],
            isAiLoading: false,

            // Computed
            get filteredConversations() {
                let filtered = this.conversations;

                // Search Filter
                if (this.search) {
                    const q = this.search.toLowerCase();
                    filtered = filtered.filter(c => 
                        c.name.toLowerCase().includes(q) || 
                        c.phone_number.includes(q) ||
                        (c.last_message && c.last_message.toLowerCase().includes(q))
                    );
                }
                
                // Tab Filter
                if (this.activeTab === 'unread') {
                    filtered = filtered.filter(c => c.unread > 0);
                } else if (this.activeTab === 'human') {
                    filtered = filtered.filter(c => c.status === 'agent_handling' || c.status === 'idle');
                }

                return filtered;
            },

            get unreadCount() {
                return this.conversations.filter(c => c.unread > 0).length;
            },

            init() {
                this.fetchConversations();
                this.fetchAvailableTags();
                
                // Real-time listener
                window.Echo.private('whatsapp')
                    .listen('NewWhatsAppMessage', (e) => {
                        console.log('New Message:', e.message);
                        this.handleNewMessage(e.message);
                    });
            },

            async fetchConversations(showLoading = true) {
                if (showLoading) this.isLoadingConversations = true;
                try {
                    let url = '{{ route("whatsapp.api.conversations") }}';
                    if (this.filterDevice) {
                        url += '?device=' + encodeURIComponent(this.filterDevice);
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

            handleNewMessage(msg) {
                // Ensure is_from_me property exists for UI logic
                if (msg.direction && typeof msg.is_from_me === 'undefined') {
                    msg.is_from_me = msg.direction === 'outgoing';
                }

                // 1. Refresh conversations list to update order/last message
                this.fetchConversations(false);
                
                // 2. If chat is open, append message
                if (this.activeChat && 
                    (this.activeChat.phone_number === msg.phone_number || 
                     this.activeChat.session_id === msg.session_id && this.activeChat.phone_number === msg.remote_jid.split('@')[0])) {
                    
                    // Check if message already exists
                    if (!this.messages.find(m => m.id === msg.id || m.wa_message_id === msg.wa_message_id)) {
                        this.messages.push(msg);
                        this.scrollToBottom();
                        
                        // Mark as read immediately if viewing
                        // fetch(`/whatsapp/mark-read/${msg.id}`); 
                    }
                }
            },

            getInitials(name) {
                if (!name) return '?';
                return name.substring(0, 2).toUpperCase();
            },

            formatTime(timestamp) {
                if (!timestamp) return '';
                const date = new Date(timestamp);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            // ... (Copy existing methods: selectChat, fetchMessages, sendMessage, etc.)
            
            clearFile() {
                this.selectedFile = null;
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            },

            async selectChat(chat) {
                if (this.activeChat?.phone_number === chat.phone_number) return;
                this.activeChat = chat;
                this.messages = [];
                this.clearFile();
                await this.fetchMessages(chat.phone_number);
                this.scrollToBottom();
                
                // Auto fetch CRM data if panel is open
                if (this.showDetailsPanel) {
                    this.fetchNotes();
                    this.fetchTags();
                    this.fetchAvailableTags();
                }
            },

            async fetchMessages(phone, showLoading = true) {
                if (showLoading) this.isLoadingMessages = true;
                try {
                    const response = await fetch(`/whatsapp/api/messages/${phone}`);
                    const data = await response.json();
                    this.messages = data;
                    this.scrollToBottom();
                } catch (error) {
                    console.error('Error fetching messages:', error);
                } finally {
                    if (showLoading) this.isLoadingMessages = false;
                }
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },

            async sendMessage() {
                if (!this.newMessage.trim() && !this.selectedFile) return;
                
                const phone = this.activeChat.phone_number;
                this.isSending = true;

                try {
                    const formData = new FormData();
                    formData.append('phone', phone);
                    if (this.activeChat.session_id) {
                        formData.append('session_id', this.activeChat.session_id);
                    }
                    
                    if (this.newMessage.trim()) formData.append('message', this.newMessage);
                    if (this.selectedFile) formData.append('file', this.selectedFile);

                    const response = await fetch('{{ route("whatsapp.send") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.newMessage = '';
                        this.clearFile();
                        await this.fetchMessages(phone, false);
                        
                        // Simulate bot typing if active
                        if (this.activeChat?.status === 'bot_active') {
                            this.isTyping = true;
                            setTimeout(() => { this.isTyping = false; }, 3000);
                        }
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

            // Takeover / Handback
            async takeoverChat() {
                if (!this.activeChat) return;
                try {
                    const response = await fetch(`/takeover/wa/${this.activeChat.phone_number}/takeover`, {
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

            async handbackToBot() {
                if (!this.activeChat) return;
                try {
                    const response = await fetch(`/takeover/wa/${this.activeChat.phone_number}/handback`, {
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

            // CRM Methods
            async fetchNotes() {
                if (!this.activeChat) return;
                this.isLoadingNotes = true;
                try {
                    const response = await fetch(`/whatsapp/api/conversations/${this.activeChat.phone_number}/notes`);
                    this.notes = await response.json();
                } catch (e) { console.error(e); }
                finally { this.isLoadingNotes = false; }
            },

            async storeNote() {
                if (!this.newNote.trim() || !this.activeChat) return;
                this.isSubmittingNote = true;
                try {
                    const response = await fetch(`/whatsapp/api/conversations/${this.activeChat.phone_number}/notes`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ content: this.newNote })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.notes.unshift(data.note);
                        this.newNote = '';
                    }
                } catch (e) { console.error(e); }
                finally { this.isSubmittingNote = false; }
            },

            async fetchTags() {
                if (!this.activeChat) return;
                try {
                    const response = await fetch(`/whatsapp/api/conversations/${this.activeChat.phone_number}/tags`);
                    this.tags = await response.json();
                } catch (e) { console.error(e); }
            },

            async fetchAvailableTags() {
                try {
                    const response = await fetch('{{ route("whatsapp.api.tags.index") }}');
                    this.availableTags = await response.json();
                } catch (e) { console.error(e); }
            },

            async attachTag(tagId) {
                if (!this.activeChat) return;
                try {
                    const response = await fetch(`/whatsapp/api/conversations/${this.activeChat.phone_number}/tags`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ tag_id: tagId })
                    });
                    if ((await response.json()).success) this.fetchTags();
                } catch (e) { console.error(e); }
            },

            async detachTag(tagId) {
                if (!this.activeChat) return;
                try {
                    const response = await fetch(`/whatsapp/api/conversations/${this.activeChat.phone_number}/tags`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ tag_id: tagId })
                    });
                    if ((await response.json()).success) this.fetchTags();
                } catch (e) { console.error(e); }
            },
            
            async fetchAiInsight() {
                if (!this.activeChat) return;
                this.isAiLoading = true;
                this.aiSummary = '';
                this.aiSuggestions = [];
                try {
                    const response = await fetch(`/whatsapp/api/conversations/${this.activeChat.phone_number}/summary`);
                    const data = await response.json();
                    this.aiSummary = data.summary;
                    
                    const resSug = await fetch(`/whatsapp/api/conversations/${this.activeChat.phone_number}/suggestions`);
                    const dataSug = await resSug.json();
                    this.aiSuggestions = dataSug.suggestions || [];
                } catch (e) { console.error(e); }
                finally { this.isAiLoading = false; }
            },

            // Device info for filter
            get devices() {
                const uniqueSessions = [...new Set(this.conversations.map(c => c.session_id))];
                return uniqueSessions.map(sid => {
                    const c = this.conversations.find(x => x.session_id === sid);
                    return {
                        session_id: sid,
                        device_name: c.device_name || 'Unknown',
                        color: c.device_color || '#888888'
                    };
                });
            }
        }
    }
</script>

</body>
</html>