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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "whatsapp": "#25D366",
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
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #324467; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475a80; }
        
        /* Hide Alpine.js elements until initialized */
        [x-cloak] { display: none !important; }
        
        /* Typing Indicator Animation */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
        }
        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #94a3b8;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }
        .typing-indicator span:nth-child(1) { animation-delay: 0s; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-6px); opacity: 1; }
        }
        
        /* Message hover actions */
        .message-bubble-wrapper:hover .message-actions {
            opacity: 1;
        }
        .message-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        /* Smooth chat item transitions */
        .chat-item {
            transition: all 0.2s ease;
        }
        .chat-item:hover {
            transform: translateX(4px);
        }
        
        /* Message animation */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-enter {
            animation: slideIn 0.3s ease-out;
        }
        
        /* Read receipt blue checkmark */
        .read-check {
            color: #3b82f6;
        }
        
        /* Pinned chat highlight */
        .chat-item.pinned {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.1) 0%, transparent 100%);
        }
    </style>
</head>
<body class="bg-gray-950 font-display text-white antialiased overflow-hidden">
    
<!-- ============================================= -->
<!-- THE ROOT CAGE: h-[100dvh] + flex + overflow-hidden -->
<!-- 100dvh fixes iOS address bar bug, overflow-hidden prevents double scroll -->
<!-- ============================================= -->
<div class="h-[100dvh] bg-gray-950 flex overflow-hidden" x-data="whatsappInbox()" x-init="init()">

    <!-- SIDEBAR (Left) - z-50 for mobile, w-64 fixed, flex-shrink-0 prevents compression -->
    @include('components.sidebar')
    
    <!-- MAIN WRAPPER (Chat List + Content) - flex-1 + min-w-0 for proper flex behavior -->
    <main class="flex-1 min-w-0 flex flex-row h-full overflow-hidden">

        <!-- CHAT LIST (Middle) - w-80 fixed on desktop, full on mobile, flex-shrink-0 -->
        <!-- Chat List (Master) -->
        <div :class="activeChat ? 'hidden lg:flex' : 'flex'" class="w-full lg:w-96 flex-col border-r border-[#232f48] bg-[#0f172a] shrink-0 pt-14 lg:pt-0">

            <!-- HEADER (h-16 fixed, flex-shrink-0) -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 bg-gray-900 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-white">WhatsApp</h2>
                    @include('components.page-help', [
                        'title' => 'WhatsApp',
                        'description' => 'Lihat dan balas pesan pelanggan dari WhatsApp.',
                        'tips' => [
                            'Pilih percakapan dari daftar di kiri',
                            'Hijau = Bot aktif menjawab',
                            'Merah = CS menangani manual',
                            'Klik "Ambil Alih" untuk membalas sendiri',
                            'Klik "Aktifkan Bot" untuk mengembalikan ke bot'
                        ]
                    ])
                </div>
                <button @click="fetchConversations()" class="p-2 hover:bg-gray-800 rounded-lg text-gray-400 hover:text-white transition-colors" title="Refresh">
                    <span class="material-symbols-outlined text-xl">refresh</span>
                </button>
            </div>
            
            <!-- SEARCH & FILTER (p-4, flex-shrink-0) -->
            <div class="p-4 border-b border-gray-800 flex-shrink-0 space-y-3">
                <div class="relative">
                    <input 
                        type="text" 
                        x-model="search"
                        placeholder="Cari percakapan..." 
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 placeholder-gray-500"
                    >
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-500 text-lg">search</span>
                </div>
                
                <!-- Device Filter Pills -->
                <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide" x-show="devices.length > 0">
                    <button @click="filterDevice = null; fetchConversations()" 
                            class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors flex items-center gap-1.5"
                            :class="!filterDevice ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'">
                        <span class="material-symbols-outlined text-sm">devices</span>
                        Semua
                    </button>
                    <template x-for="device in devices" :key="device.session_id">
                        <button @click="filterDevice = device.session_id; fetchConversations()"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors flex items-center gap-1.5"
                                :class="filterDevice === device.session_id ? 'text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"
                                :style="filterDevice === device.session_id ? 'background-color:' + device.color : ''">
                            <span class="w-2 h-2 rounded-full" :style="'background-color:' + device.color"></span>
                            <span x-text="device.device_name" class="truncate max-w-24"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- CHAT LIST (flex-1 overflow-y-auto - ONLY this scrolls) -->
            <div class="flex-1 overflow-y-auto">

                <template x-if="isLoadingConversations && conversations.length === 0">
                    <div class="p-4 space-y-4">
                        <template x-for="i in 5">
                            <div class="animate-pulse flex space-x-4">
                                <div class="rounded-full bg-white/5 h-12 w-12"></div>
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-2 bg-white/5 rounded w-3/4"></div>
                                    <div class="h-2 bg-white/5 rounded w-1/2"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-for="chat in filteredConversations" :key="chat.phone_number">
                    <div 
                        @click="selectChat(chat)"
                        class="chat-item p-4 border-b border-border-dark cursor-pointer transition-colors relative"
                        :class="activeChat?.phone_number === chat.phone_number ? 'bg-white/10' : 'hover:bg-white/5'"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex space-x-3 items-center overflow-hidden">
                                <div class="flex-shrink-0 h-12 w-12 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center text-text-secondary font-bold relative">
                                    <span x-text="getInitials(chat.name)" class="text-lg"></span>
                                    <!-- Status Indicator Dot -->
                                    <span class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#111722]"
                                          :class="{
                                              'bg-green-500': chat.status === 'bot_active',
                                              'bg-red-500': chat.status === 'agent_handling',
                                              'bg-yellow-500 animate-pulse': chat.status === 'idle'
                                          }"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex justify-between items-baseline mb-1">
                                        <p class="text-sm font-semibold text-white truncate" x-text="chat.name"></p>
                                        <span class="text-[10px] text-text-secondary ml-2 whitespace-nowrap" x-text="chat.last_message_time"></span>
                                    </div>
                                    <!-- Status Badge & Device Badge -->
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <!-- Device Badge -->
                                        <span class="text-[10px] px-1.5 py-0.5 rounded flex items-center gap-1" 
                                              :style="'background-color:' + (chat.device_color || '#888888') + '20; color:' + (chat.device_color || '#888888')">
                                            <span class="w-1.5 h-1.5 rounded-full" :style="'background-color:' + (chat.device_color || '#888888')"></span>
                                            <span x-text="chat.device_name || 'Unknown'" class="truncate max-w-[80px]"></span>
                                        </span>
                                        <template x-if="chat.status === 'agent_handling'">
                                            <span class="text-[10px] bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[10px]">headset_mic</span>
                                                CS
                                            </span>
                                        </template>
                                        <template x-if="chat.status === 'idle' && chat.remaining_minutes">
                                            <span class="text-[10px] bg-yellow-500/20 text-yellow-400 px-1.5 py-0.5 rounded flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[10px]">schedule</span>
                                                <span x-text="chat.remaining_minutes + 'm'"></span>
                                            </span>
                                        </template>
                                        <template x-if="chat.status === 'bot_active'">
                                            <span class="text-[10px] bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[10px]">smart_toy</span>
                                                Bot
                                            </span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-text-secondary truncate" x-text="chat.last_message"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <template x-if="conversations.length === 0 && !isLoadingConversations">
                    <div class="p-8 text-center text-text-secondary">
                        <p>Belum ada percakapan</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- CONTENT AREA (Right) - flex-1 + min-w-0 CRITICAL for text overflow -->
        <!-- Chat Conversation (Detail) -->
        <div :class="activeChat ? 'flex' : 'hidden lg:flex'" class="flex-1 flex-col bg-[#101622] pt-14 lg:pt-0">
             <!-- Chat Background Pattern -->
             <div class="absolute inset-0 z-0 opacity-[0.03]" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');"></div>

            <template x-if="!activeChat">
                <!-- EMPTY STATE: Centered content with icon -->
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 z-10">
                    <div class="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-5xl text-gray-500">chat</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white">WhatsApp Inbox</h3>
                    <p class="text-gray-400 mt-2 max-w-sm">Pilih percakapan dari daftar di sebelah kiri untuk mulai chat dan melihat riwayat pesan.</p>
                </div>
            </template>

            <template x-if="activeChat">
                <div class="flex-1 flex flex-col h-full z-10 relative min-w-0">
                    <!-- CHAT HEADER (h-16 fixed, flex-shrink-0) -->
                    <div class="h-16 flex items-center justify-between px-4 bg-gray-900 border-b border-gray-800 flex-shrink-0">
                        <div class="flex items-center space-x-3 md:space-x-4">
                            <!-- Back Button (Mobile Only) -->
                            <button @click="activeChat = null; messages = []" 
                                    class="md:hidden p-2 -ml-1 hover:bg-white/5 rounded-full text-text-secondary"
                                    title="Kembali ke daftar chat">
                                <span class="material-symbols-outlined">arrow_back</span>
                            </button>
                            <div class="h-10 w-10 rounded-full bg-white/10 flex items-center justify-center text-white font-bold">
                                <span x-text="getInitials(activeChat.name)"></span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-sm" x-text="activeChat.name"></h3>
                                <p class="text-xs text-text-secondary" x-text="activeChat.formatted_phone"></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 md:space-x-2">
                            <!-- Takeover Button (when bot is active) -->
                            <button x-show="activeChat?.status === 'bot_active'" @click="takeoverChat()"
                                    class="flex items-center gap-1 px-2 md:px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs md:text-sm font-medium transition-colors">
                                <span class="material-symbols-outlined text-base">headset_mic</span>
                                <span class="hidden sm:inline">Ambil Alih</span>
                            </button>
                            <!-- Handback Button (when CS is handling) -->
                            <button x-show="activeChat?.status !== 'bot_active'" @click="handbackToBot()"
                                    class="flex items-center gap-1 px-2 md:px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-xs md:text-sm font-medium transition-colors">
                                <span class="material-symbols-outlined text-base">replay</span>
                                <span class="hidden sm:inline">Aktifkan Bot</span>
                            </button>
                            <button class="p-2 hover:bg-white/5 rounded-full text-text-secondary">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Agent Handling Banner -->
                    <div x-show="activeChat?.status !== 'bot_active'" 
                         class="bg-amber-500/10 border-b border-amber-500/30 px-3 md:px-4 py-2 flex items-center justify-between shrink-0 z-20 flex-wrap gap-2">
                        <span class="text-xs md:text-sm text-amber-400 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">support_agent</span>
                            <span class="hidden sm:inline">Bot saat ini <strong class="ml-1">nonaktif</strong> untuk percakapan ini.</span>
                            <span class="sm:hidden">Bot nonaktif</span>
                            <template x-if="activeChat?.remaining_minutes">
                                <span class="text-[10px] md:text-xs opacity-75">(<span x-text="activeChat.remaining_minutes"></span>m)</span>
                            </template>
                        </span>
                        <button @click="handbackToBot()" 
                                class="bg-green-500 hover:bg-green-600 text-white text-[10px] md:text-xs px-2 md:px-3 py-1 md:py-1.5 rounded-lg flex items-center gap-1 transition-colors font-medium">
                            <span class="material-symbols-outlined text-sm">replay</span>
                            <span class="hidden sm:inline">Aktifkan Bot Kembali</span>
                            <span class="sm:hidden">Bot</span>
                        </button>
                    </div>

                    <!-- Messages Area -->
                    <div 
                        class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar"
                        id="messages-container"
                    >
                        <!-- AI Insight Section -->
                        <template x-if="aiSummary || isAiLoading">
                            <div class="mb-4 bg-primary/5 border border-primary/20 rounded-2xl p-4 flex gap-4 transition-all duration-500">
                                <div class="w-10 h-10 bg-primary/20 rounded-full flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-primary" :class="isAiLoading ? 'animate-spin' : ''">
                                        <template x-if="isAiLoading">sync</template>
                                        <template x-if="!isAiLoading">auto_awesome</template>
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xs font-black text-primary uppercase tracking-widest mb-1 flex items-center gap-2">
                                        AI Insight 
                                        <span class="text-[9px] bg-primary text-white px-1.5 py-0.5 rounded">Pro</span>
                                    </h4>
                                    <template x-if="isAiLoading">
                                        <div class="space-y-2">
                                            <div class="h-2 bg-primary/10 rounded w-full animate-pulse"></div>
                                            <div class="h-2 bg-primary/10 rounded w-2/3 animate-pulse"></div>
                                        </div>
                                    </template>
                                    <template x-if="!isAiLoading && aiSummary">
                                        <p class="text-xs text-slate-300 leading-relaxed italic" x-text="'&ldquo;' + aiSummary + '&rdquo;'"></p>
                                    </template>
                                </div>
                                <button @click="fetchAiInsight()" class="p-1 hover:bg-primary/10 rounded-full text-primary/50 hover:text-primary shrink-0" title="Refresh Insight">
                                    <span class="material-symbols-outlined text-sm">refresh</span>
                                </button>
                            </div>
                        </template>

                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex flex-col" :class="msg.direction === 'incoming' ? 'items-start' : 'items-end'">
                                <div class="max-w-[85%] sm:max-w-[75%] md:max-w-[70%]">
                                    <!-- Bubble Message -->
                                    <div 
                                        class="relative px-4 py-2.5 rounded-2xl shadow-sm"
                                        :class="msg.direction === 'incoming' 
                                            ? 'bg-gray-800 text-white rounded-tl-none border border-gray-700' 
                                            : 'bg-primary text-white rounded-tr-none'"
                                    >
                                        <!-- Image content support -->
                                        <template x-if="msg.type === 'image'">
                                            <div class="mb-2">
                                                <img :src="msg.message" class="rounded-lg max-w-full h-auto cursor-pointer" @click="window.open(msg.message, '_blank')">
                                            </div>
                                        </template>
                                        <template x-if="msg.type !== 'image'">
                                            <p class="text-sm md:text-base whitespace-pre-wrap break-words leading-relaxed" x-text="msg.message"></p>
                                        </template>

                                        <div class="flex justify-end items-center gap-1 mt-1 opacity-50">
                                            <span class="text-[9px] font-medium" x-text="msg.time"></span>
                                            <template x-if="msg.direction === 'outgoing'">
                                                <span class="material-symbols-outlined text-[14px]" :class="msg.status === 'read' ? 'text-blue-400 filled' : ''" x-text="msg.status === 'read' ? 'done_all' : 'done'"></span>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <!-- Bot Badge (under bubble) -->
                                    <template x-if="msg.is_bot_reply">
                                        <div class="flex items-center gap-1 mt-1 opacity-40 px-2" :class="msg.direction === 'incoming' ? 'justify-start' : 'justify-end'">
                                            <span class="material-symbols-outlined text-[10px]">smart_toy</span>
                                            <span class="text-[9px] uppercase tracking-tighter font-bold">Respon AI</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Typing Indicator (shown when isTyping is true) -->
                        <div x-show="isTyping" x-cloak class="flex items-start">
                            <div class="bg-surface-dark rounded-2xl rounded-tl-none border border-border-dark">
                                <div class="typing-indicator">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-4 bg-surface-dark border-t border-border-dark shrink-0 z-20 pb-safe md:pb-4">
                        <div class="flex flex-col space-y-3 max-w-4xl mx-auto">
                            <!-- AI Suggestions -->
                            <template x-if="aiSuggestions.length > 0">
                                <div class="flex gap-2 overflow-x-auto pb-1 custom-scrollbar">
                                    <template x-for="sug in aiSuggestions" :key="sug">
                                        <button @click="newMessage = sug; aiSuggestions = []" 
                                                class="px-3 py-1.5 bg-white/5 border border-white/10 hover:border-primary/50 hover:bg-primary/10 text-[11px] text-slate-300 hover:text-primary rounded-xl transition-all whitespace-nowrap flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-sm opacity-50">magic_button</span>
                                            <span x-text="sug"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>

                            <!-- File Preview -->
                            <template x-if="selectedFile">
                                <div class="p-3 bg-white/5 border border-white/10 rounded-xl flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <template x-if="filePreview">
                                            <img :src="filePreview" class="w-10 h-10 object-cover rounded-lg">
                                        </template>
                                        <template x-if="!filePreview">
                                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                                                <span class="material-symbols-outlined text-gray-400">description</span>
                                            </div>
                                        </template>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-white truncate" x-text="selectedFile.name"></p>
                                            <p class="text-[10px] text-gray-500" x-text="(selectedFile.size / 1024).toFixed(1) + ' KB'"></p>
                                        </div>
                                    </div>
                                    <button @click="clearFile()" class="p-2 hover:bg-white/10 rounded-full text-red-400 transition-colors">
                                        <span class="material-symbols-outlined">close</span>
                                    </button>
                                </div>
                            </template>

                            <div class="flex items-end gap-3 w-full">
                                <!-- Hidden File Input -->
                                <input 
                                    type="file" 
                                    x-ref="fileInput" 
                                    class="hidden" 
                                    @change="handleFileSelect"
                                    accept="image/*,video/*,application/pdf"
                                >
                                
                                <!-- Attach Button -->
                                <button 
                                    @click="$refs.fileInput.click()"
                                    class="p-3 text-text-secondary hover:text-white rounded-full hover:bg-white/5 transition-colors shrink-0"
                                    title="Attach File"
                                >
                                    <span class="material-symbols-outlined">attach_file</span>
                                </button>
                                
                                <!-- Text Input -->
                                <div class="flex-1 bg-[#111722] rounded-2xl border border-border-dark focus-within:border-whatsapp transition-colors min-w-0">
                                    <textarea 
                                        x-model="newMessage"
                                        @keydown.enter.prevent="sendMessage()"
                                        rows="1" 
                                        placeholder="Ketik pesan..." 
                                        class="w-full bg-transparent border-none focus:ring-0 text-white placeholder-text-secondary py-3 px-4 text-sm max-h-32 resize-none"
                                        style="min-height: 48px;"
                                    ></textarea>
                                </div>
                                
                                <!-- Send Button -->
                                <button 
                                    @click="sendMessage()"
                                    :disabled="(!newMessage.trim() && !selectedFile) || isSending"
                                    class="p-3 bg-whatsapp text-white rounded-full hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg shrink-0 flex items-center justify-center w-12 h-12"
                                >
                                    <template x-if="!isSending">
                                        <span class="material-symbols-outlined filled">send</span>
                                    </template>
                                    <template x-if="isSending">
                                        <span class="material-symbols-outlined animate-spin text-xl">sync</span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </main>
    
    <!-- MODAL: Idle Notification Popup (z-50 highest z-index) -->
    <div x-show="showIdleWarning" x-cloak
         class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 max-w-md mx-4 shadow-2xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-yellow-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-yellow-400 text-2xl">schedule</span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">⏰ Peringatan Idle</h3>
                    <p class="text-sm text-gray-400">Chat belum dibalas</p>
                </div>
            </div>
            <p class="text-gray-400 mb-6">
                Kamu belum membalas chat dari <strong class="text-white" x-text="idleChat?.name"></strong> 
                selama <span class="text-yellow-400 font-semibold" x-text="idleMinutes"></span> menit. 
                Kembalikan ke Bot sekarang atau teruskan secara manual.
            </p>
            <div class="flex gap-3">
                <button @click="handbackIdleChat()" 
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">replay</span>
                    Kembalikan ke Bot
                </button>
                <button @click="dismissIdleWarning()" 
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-white py-2.5 rounded-lg font-medium transition-colors">
                    Lanjutkan
                </button>
            </div>
        </div>
    </div>
    
</div><!-- END ROOT CAGE -->

<script>
    function whatsappInbox() {
        return {
            conversations: [],
            messages: [],
            activeChat: null,
            search: '',
            newMessage: '',
            selectedFile: null,
            filePreview: null,
            isLoadingConversations: false,
            isLoadingMessages: false,
            isSending: false,
            isTyping: false,
            pollInterval: null,
            // AI Pro state
            aiSummary: '',
            aiSuggestions: [],
            isAiLoading: false,
            // Device filter state
            devices: @json($devices ?? []),
            filterDevice: null,
            // Idle notification state
            showIdleWarning: false,
            idleChat: null,
            idleMinutes: 0,
            idleWarningThreshold: {{ $idleWarning ?? 30 }},
            takeoverTimeout: {{ $takeoverTimeout ?? 60 }},

            init() {
                this.fetchConversations();
                this.pollInterval = setInterval(() => {
                    this.fetchConversations(false);
                    if (this.activeChat) {
                        this.fetchMessages(this.activeChat.phone_number, false);
                        // Update active chat status from conversations
                        const updated = this.conversations.find(c => c.phone_number === this.activeChat.phone_number);
                        if (updated) {
                            this.activeChat = {...this.activeChat, ...updated};
                        }
                    }
                    // Check for idle chats
                    this.checkIdleStatus();
                }, 5000);
            },

            get filteredConversations() {
                if (!this.search) return this.conversations;
                const lower = this.search.toLowerCase();
                return this.conversations.filter(c => 
                    c.name.toLowerCase().includes(lower) || 
                    c.phone_number.includes(lower) ||
                    (c.last_message && c.last_message.toLowerCase().includes(lower))
                );
            },

            getInitials(name) {
                return name ? name.substring(0, 2).toUpperCase() : '?';
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.selectedFile = file;
                
                // Create preview for images
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.filePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.filePreview = null;
                }
            },

            clearFile() {
                this.selectedFile = null;
                this.filePreview = null;
                this.$refs.fileInput.value = '';
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
                    if (JSON.stringify(this.conversations) !== JSON.stringify(data)) {
                         this.conversations = data;
                    }
                } catch (error) {
                    console.error('Error fetching conversations:', error);
                } finally {
                    if (showLoading) this.isLoadingConversations = false;
                }
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
                } catch (error) {
                    console.error('Error fetching AI insight:', error);
                } finally {
                    this.isAiLoading = false;
                }
            },

            async selectChat(chat) {
                if (this.activeChat?.phone_number === chat.phone_number) return;
                this.activeChat = chat;
                this.messages = [];
                this.clearFile(); // Clear file when switching chats
                await this.fetchMessages(chat.phone_number);
                this.scrollToBottom();
                this.fetchAiInsight();
            },

            async fetchMessages(phone, showLoading = true) {
                if (showLoading) this.isLoadingMessages = true;
                try {
                    const response = await fetch(`/whatsapp/api/messages/${phone}`);
                    const data = await response.json();
                    const shouldScroll = this.messages.length !== data.length;
                    
                    if (JSON.stringify(this.messages) !== JSON.stringify(data)) {
                        this.messages = data;
                        if (shouldScroll && !showLoading) {
                            this.scrollToBottom();
                        }
                    }
                } catch (error) {
                    console.error('Error fetching messages:', error);
                } finally {
                    if (showLoading) this.isLoadingMessages = false;
                }
            },

            async sendMessage() {
                if (!this.newMessage.trim() && !this.selectedFile) return;
                
                const phone = this.activeChat.phone_number;
                this.isSending = true;

                try {
                    const formData = new FormData();
                    formData.append('phone', phone);
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
                        this.scrollToBottom();
                        
                        // Show typing indicator if bot is active (simulating bot response)
                        if (this.activeChat?.status === 'bot_active') {
                            this.isTyping = true;
                            this.scrollToBottom();
                            // Hide after a few seconds (bot will respond)
                            setTimeout(() => {
                                this.isTyping = false;
                            }, 3000);
                        }
                    } else {
                        alert('Gagal mengirim pesan: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('Terjadi kesalahan saat mengirim pesan');
                } finally {
                    this.isSending = false;
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

            // Takeover Methods
            async takeoverChat() {
                if (!this.activeChat) return;
                try {
                    const response = await fetch(`/takeover/wa/${this.activeChat.phone_number}/takeover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.activeChat.status = 'agent_handling';
                        await this.fetchConversations(false);
                    } else {
                        alert('Gagal mengambil alih: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error taking over:', error);
                    alert('Terjadi kesalahan saat mengambil alih chat');
                }
            },

            async handbackToBot() {
                if (!this.activeChat) return;
                try {
                    const response = await fetch(`/takeover/wa/${this.activeChat.phone_number}/handback`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.activeChat.status = 'bot_active';
                        await this.fetchConversations(false);
                    } else {
                        alert('Gagal mengembalikan ke bot: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error handing back:', error);
                    alert('Terjadi kesalahan saat mengembalikan ke bot');
                }
            },

            // Idle check
            checkIdleStatus() {
                const agentChats = this.conversations.filter(c => c.status === 'agent_handling' || c.status === 'idle');
                for (const chat of agentChats) {
                    const warningThreshold = this.takeoverTimeout - this.idleWarningThreshold;
                    if (chat.remaining_minutes !== null && chat.remaining_minutes <= warningThreshold) {
                        this.showIdleWarning = true;
                        this.idleChat = chat;
                        this.idleMinutes = this.takeoverTimeout - chat.remaining_minutes;
                        break;
                    }
                }
            },

            async handbackIdleChat() {
                if (!this.idleChat) return;
                try {
                    const response = await fetch(`/takeover/wa/${this.idleChat.phone_number}/handback`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        await this.fetchConversations(false);
                        if (this.activeChat?.phone_number === this.idleChat.phone_number) {
                            this.activeChat.status = 'bot_active';
                        }
                    }
                } catch (error) {
                    console.error('Error handing back idle chat:', error);
                }
                this.showIdleWarning = false;
                this.idleChat = null;
            },

            dismissIdleWarning() {
                this.showIdleWarning = false;
                this.idleChat = null;
            }
        }
    }
</script>

</body>
</html>
