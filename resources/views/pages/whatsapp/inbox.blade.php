<x-enterprise-layout title="WhatsApp Inbox">
    <div class="contents" x-data="whatsappInbox()" x-init="init()">

    
    <x-master-chat-list>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="p-2 -ml-2 text-gray-400 hover:text-white lg:hidden">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h2 class="text-lg font-black tracking-tight text-white italic">WhatsApp</h2>
            </div>
            <div class="flex items-center gap-1">
                <button @click="fetchConversations()" class="p-2 text-gray-500 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">refresh</span>
                </button>
                <div class="relative group">
                    <button class="p-2 text-gray-500 hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-[20px]">more_vert</span>
                    </button>
                    <!-- Simple Dropdown for Export -->
                    <div class="absolute right-0 top-full mt-2 w-48 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl hidden group-hover:block z-50 overflow-hidden">
                        <a :href="'{{ route('whatsapp.api.training.export.csv') }}'" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-all">
                            <span class="material-symbols-outlined text-sm">csv</span>
                            Export CSV
                        </a>
                        <a :href="'{{ route('whatsapp.api.training.export.json') }}'" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-all">
                            <span class="material-symbols-outlined text-sm">backup</span>
                            Export JSON
                        </a>
                    </div>
                </div>
            </div>
        </x-slot:header>

        <!-- Search & Device Filter Area -->
        <div class="p-4 border-b border-gray-800/50 bg-gray-950/50 sticky top-0 z-10 backdrop-blur-md">
            <div class="relative mb-3">
                <input 
                    type="text" 
                    x-model="search"
                    placeholder="Cari chat..." 
                    class="w-full bg-gray-900 border-none text-white text-xs rounded-xl pl-9 pr-4 py-2.5 focus:ring-1 focus:ring-blue-600 placeholder-gray-600 transition-all font-medium"
                >
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-600 text-sm">search</span>
            </div>
            
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide" x-show="devices.length > 0">
                <button @click="filterDevice = null; fetchConversations()" 
                        class="px-3 py-1.5 rounded-full text-[10px] font-bold whitespace-nowrap transition-all flex items-center gap-1.5 border"
                        :class="!filterDevice ? 'bg-blue-600 text-white border-blue-500' : 'bg-gray-900 text-gray-500 border-gray-800 hover:border-gray-700'">
                    Semua
                </button>
                <template x-for="device in devices" :key="device.session_id">
                    <button @click="filterDevice = device.session_id; fetchConversations()"
                            class="px-3 py-1.5 rounded-full text-[10px] font-bold whitespace-nowrap transition-all flex items-center gap-1.5 border"
                            :class="filterDevice === device.session_id ? 'text-white' : 'bg-gray-900 text-gray-500 border-gray-800 hover:border-gray-700'"
                            :style="filterDevice === device.session_id ? 'background-color:' + device.color + '; border-color:' + device.color : ''">
                        <span x-text="device.device_name" class="truncate max-w-[100px]"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Scrollable Conversation List -->
        <div class="flex-1 overflow-y-auto divide-y divide-gray-800/30">
            <template x-if="isLoadingConversations && conversations.length === 0">
                <div class="p-4 space-y-4">
                    <template x-for="i in 5">
                        <div class="animate-pulse flex space-x-4">
                            <div class="rounded-full bg-gray-900 h-10 w-10"></div>
                            <div class="flex-1 space-y-2 py-1">
                                <div class="h-2 bg-gray-900 rounded w-3/4"></div>
                                <div class="h-2 bg-gray-900 rounded w-1/2"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-for="chat in filteredConversations" :key="chat.phone_number">
                <div 
                    @click="selectChat(chat)"
                    class="group flex items-center gap-3 p-4 hover:bg-gray-900/50 cursor-pointer transition-all border-l-2"
                    :class="activeChat?.phone_number === chat.phone_number ? 'bg-gray-900/80 border-blue-600' : 'border-transparent active:scale-[0.98]'"
                >
                    <div class="relative flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 font-black border border-gray-700 group-hover:border-gray-500 transition-all overflow-hidden">
                            <span x-text="getInitials(chat.name)"></span>
                        </div>
                        <div class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full border-2 border-gray-950"
                             :class="{
                                 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]': chat.status === 'bot_active',
                                 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]': chat.status === 'agent_handling',
                                 'bg-yellow-500 animate-pulse': chat.status === 'idle'
                             }"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-1">
                            <h4 class="text-sm font-bold text-gray-200 truncate group-hover:text-white transition-all" x-text="chat.name"></h4>
                            <span class="text-[10px] font-bold text-gray-600 whitespace-nowrap" x-text="chat.last_message_time"></span>
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[9px] font-black uppercase tracking-tighter text-gray-500 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full" :style="'background-color:' + (chat.device_color || '#888888')"></span>
                                <span x-text="chat.device_name || 'WA'"></span>
                            </span>
                            <template x-if="chat.status === 'agent_handling'">
                                <span class="text-[9px] font-black bg-red-500/10 text-red-500 px-1 rounded">CS</span>
                            </template>
                            <template x-if="chat.status === 'bot_active'">
                                <span class="text-[9px] font-black bg-green-500/10 text-green-500 px-1 rounded">BOT</span>
                            </template>
                        </div>
                        <p class="text-xs text-gray-600 truncate group-hover:text-gray-400 transition-all" x-text="chat.last_message"></p>
                    </div>
                </div>
            </template>
            
            <template x-if="conversations.length === 0 && !isLoadingConversations">
                <div class="p-8 text-center">
                    <span class="material-symbols-outlined text-gray-800 text-4xl mb-2">chat_bubble</span>
                    <p class="text-xs font-bold text-gray-800 uppercase tracking-widest">No Conversations</p>
                </div>
            </template>
        </div>
    </x-master-chat-list>

    <x-message-detail 
        :name="activeChat ? activeChat.name : 'Pilih Percakapan'" 
        :avatar="activeChat ? getInitials(activeChat.name) : '?'"
        :empty="!activeChat"
    >
        <x-slot:header-actions>
            <div class="flex items-center gap-2" x-show="activeChat">
                <button x-show="activeChat?.status === 'bot_active'" @click="takeoverChat()"
                        class="flex items-center gap-2 px-3 py-1.5 bg-amber-500/10 border border-amber-500/30 text-amber-500 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-amber-500 hover:text-white transition-all">
                    <span class="material-symbols-outlined text-sm">headset_mic</span>
                    Ambil Alih
                </button>
                <button x-show="activeChat?.status !== 'bot_active'" @click="handbackToBot()"
                        class="flex items-center gap-2 px-3 py-1.5 bg-green-500/10 border border-green-500/30 text-green-500 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-green-500 hover:text-white transition-all">
                    <span class="material-symbols-outlined text-sm">replay</span>
                    Bot Aktif
                </button>
                <button @click="toggleFollowup()"
                        class="p-2 rounded-lg border transition-all"
                        :class="activeChat?.stop_autofollowup ? 'bg-red-500/10 border-red-500/50 text-red-500' : 'bg-gray-900 border-gray-800 text-gray-500 hover:text-white'">
                    <span class="material-symbols-outlined text-[18px]">notifications_off</span>
                </button>
            </div>
        </x-slot:header-actions>

        <!-- AI Insight Banner -->
        <template x-if="activeChat && (aiSummary || isAiLoading)">
            <div class="mb-6 bg-blue-600/5 border border-blue-600/20 rounded-2xl p-4 flex gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="w-10 h-10 bg-blue-600/20 rounded-full flex items-center justify-center shrink-0 border border-blue-600/30">
                    <span class="material-symbols-outlined text-blue-500" :class="isAiLoading ? 'animate-spin' : ''">
                        <template x-if="isAiLoading">sync</template>
                        <template x-if="!isAiLoading">auto_awesome</template>
                    </span>
                </div>
                <div class="flex-1">
                    <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] mb-1">AI Smart Analysis</h4>
                    <template x-if="isAiLoading">
                        <div class="space-y-2">
                            <div class="h-2 bg-blue-600/10 rounded w-full animate-pulse"></div>
                            <div class="h-2 bg-blue-600/10 rounded w-2/3 animate-pulse"></div>
                        </div>
                    </template>
                    <template x-if="!isAiLoading && aiSummary">
                        <p class="text-[11px] text-gray-400 leading-relaxed font-medium italic" x-text="'&ldquo;' + aiSummary + '&rdquo;'"></p>
                    </template>
                </div>
            </div>
        </template>

        <!-- Agent Handling Warning -->
        <template x-if="activeChat && activeChat.status !== 'bot_active'">
            <div class="mb-6 bg-amber-500/5 border border-amber-500/20 rounded-xl px-4 py-2 flex items-center justify-between">
                <span class="text-[10px] font-bold text-amber-500 flex items-center gap-2 uppercase tracking-wider">
                    <span class="material-symbols-outlined text-sm">warning</span>
                    Manual Handling Mode
                </span>
                <button @click="handbackToBot()" class="text-[9px] font-black text-amber-500 hover:underline">RE-ENABLE BOT</button>
            </div>
        </template>

        <!-- Dynamic Message List -->
        <div id="messages-container" class="space-y-6">
            <template x-for="msg in messages" :key="msg.id">
                <div class="flex flex-col group" :class="msg.direction === 'outgoing' ? 'items-end' : 'items-start'">
                    <div class="flex gap-3 max-w-[85%]" :class="msg.direction === 'outgoing' ? 'flex-row-reverse' : ''">
                        <div class="size-8 rounded-full flex-shrink-0 flex items-center justify-center text-[14px] border border-gray-800"
                             :class="msg.direction === 'outgoing' ? 
                                    (msg.is_bot_reply ? 'bg-blue-600 text-white border-blue-500' : 'bg-gray-800 text-gray-400') : 
                                    'bg-gray-900 text-gray-500'">
                            <span class="material-symbols-outlined text-sm" x-text="msg.is_bot_reply ? 'smart_toy' : (msg.direction === 'outgoing' ? 'person' : 'account_circle')"></span>
                        </div>
                        <div class="flex flex-col gap-1.5" :class="msg.direction === 'outgoing' ? 'items-end' : 'items-start'">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-gray-600" 
                                      x-text="msg.is_bot_reply ? 'AI AGENT' : (msg.direction === 'outgoing' ? 'ADMIN' : 'CUSTOMER')"></span>
                                <span class="text-[9px] text-gray-700 font-bold" x-text="msg.time"></span>
                            </div>
                            <div class="p-4 rounded-2xl text-[13px] font-medium leading-relaxed relative group/msg"
                                 :class="msg.direction === 'outgoing' ? 
                                        'bg-blue-600 text-white rounded-tr-none' : 
                                        'bg-gray-900 text-gray-300 border border-gray-800 rounded-tl-none'">
                                <div x-text="msg.message" class="whitespace-pre-wrap"></div>
                                
                                <template x-if="msg.is_bot_reply">
                                    <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between gap-6">
                                        <span class="text-[8px] font-black bg-white/20 px-1 rounded">AI POWERED</span>
                                        <div class="flex gap-2">
                                            <button @click="rateMessage(msg.id, 'good')" class="p-1 hover:bg-white/10 rounded transition-all" :class="msg.rated === 'good' ? 'text-green-300' : 'text-white/40'">
                                                <span class="material-symbols-outlined text-[14px]" :class="msg.rated === 'good' ? 'filled' : ''">thumb_up</span>
                                            </button>
                                            <button @click="rateMessage(msg.id, 'bad')" class="p-1 hover:bg-white/10 rounded transition-all" :class="msg.rated === 'bad' ? 'text-red-300' : 'text-white/40'">
                                                <span class="material-symbols-outlined text-[14px]" :class="msg.rated === 'bad' ? 'filled' : ''">thumb_down</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Quick Actions on Hover -->
                                <button class="absolute top-0 opacity-0 group-hover/msg:opacity-100 transition-all p-2 text-gray-500 hover:text-white"
                                        :class="msg.direction === 'outgoing' ? 'right-full' : 'left-full'">
                                    <span class="material-symbols-outlined text-[18px]">reply</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Typing State -->
            <div x-show="isTyping" x-cloak class="flex items-center gap-3">
                <div class="size-8 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm text-gray-500">smart_toy</span>
                </div>
                <div class="flex gap-1.5 p-4 bg-gray-900 border border-gray-800 rounded-2xl rounded-tl-none">
                    <span class="size-1.5 rounded-full bg-blue-600 animate-bounce [animation-delay:-0.3s]"></span>
                    <span class="size-1.5 rounded-full bg-blue-600 animate-bounce [animation-delay:-0.15s]"></span>
                    <span class="size-1.5 rounded-full bg-blue-600 animate-bounce"></span>
                </div>
            </div>
        </div>

        <!-- Custom Input Area Slot -->
        <x-slot:input>
            <div class="max-w-4xl mx-auto space-y-3">
                <!-- AI Suggestions -->
                <div class="flex gap-2 overflow-x-auto scrollbar-hide" x-show="aiSuggestions.length > 0">
                    <template x-for="sug in aiSuggestions" :key="sug">
                        <button @click="newMessage = sug; aiSuggestions = []" 
                                class="px-3 py-1.5 bg-gray-900 border border-gray-800 hover:border-blue-600 text-[10px] font-bold text-gray-400 hover:text-blue-500 rounded-xl transition-all whitespace-nowrap flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">magic_button</span>
                            <span x-text="sug"></span>
                        </button>
                    </template>
                </div>

                <div class="flex items-end gap-3">
                    <input type="file" x-ref="fileInput" class="hidden" @change="handleFileSelect" accept="image/*,video/*,application/pdf">
                    <button @click="$refs.fileInput.click()" class="size-11 flex items-center justify-center text-gray-500 hover:text-white bg-gray-900 border border-gray-800 rounded-xl transition-all">
                        <span class="material-symbols-outlined">attach_file</span>
                    </button>
                    
                    <div class="flex-1 bg-gray-900 border border-gray-800 rounded-xl focus-within:border-blue-600/50 transition-all p-2 flex items-end">
                        <textarea 
                            x-model="newMessage"
                            @keydown.enter.prevent="sendMessage()"
                            rows="1" 
                            placeholder="Tulis balasan..." 
                            class="w-full bg-transparent border-none focus:ring-0 text-white placeholder-gray-600 text-[13px] py-1 px-2 max-h-32 resize-none"
                            oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                        ></textarea>
                    </div>

                    <button 
                        @click="sendMessage()"
                        :disabled="(!newMessage.trim() && !selectedFile) || isSending"
                        class="size-11 bg-blue-600 text-white rounded-xl flex items-center justify-center hover:bg-blue-700 disabled:opacity-50 transition-all shadow-lg shadow-blue-600/20 active:scale-95"
                    >
                        <template x-if="!isSending">
                            <span class="material-symbols-outlined filled">send</span>
                        </template>
                        <template x-if="isSending">
                            <span class="material-symbols-outlined animate-spin text-lg">sync</span>
                        </template>
                    </button>
                </div>
            </div>
        </x-slot:input>
    </x-message-detail>
                </div>
            </template>
        </div>
    </div>
    
    <!-- Idle Notification Popup -->
    <div x-show="showIdleWarning" x-cloak
         class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-surface-dark border border-border-dark rounded-2xl p-6 max-w-md mx-4 shadow-2xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-yellow-500/20 rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-400 text-2xl">schedule</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">⏰ Peringatan Idle</h3>
                    <p class="text-sm text-text-secondary">Chat belum dibalas</p>
                </div>
            </div>
            <p class="text-text-secondary mb-6">
                Kamu belum membalas chat dari <strong class="text-white" x-text="idleChat?.name"></strong> 
                selama <span class="text-yellow-400 font-semibold" x-text="idleMinutes"></span> menit. 
                Kembalikan ke Bot sekarang atau teruskan secara manual.
            </p>
            <div class="flex gap-3">
                <button @click="handbackIdleChat()" 
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2.5 rounded-xl font-medium transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">replay</span>
                    Kembalikan ke Bot
                </button>
                <button @click="dismissIdleWarning()" 
                        class="flex-1 bg-white/10 hover:bg-white/20 text-white py-2.5 rounded-xl font-medium transition-colors">
                    Lanjutkan
                </button>
            </div>
        </div>
    </div>
</main>

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
            },

            // AI Style Training
            async rateMessage(messageId, rating) {
                try {
                    const response = await fetch('{{ route("whatsapp.api.messages.rate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ message_id: messageId, rating: rating })
                    });
                    const result = await response.json();
                    if (result.success) {
                        // Update UI locally
                        const msg = this.messages.find(m => m.id === messageId);
                        if (msg) msg.rated = rating;
                        
                        // Show simple toast or feedback? Logic below
                        console.log('AI Training Example Saved:', rating);
                    }
                } catch (error) {
                    console.error('Error rating message:', error);
                }
            },

            async toggleFollowup() {
                if (!this.activeChat) return;
                try {
                    const phone = this.activeChat.phone_number;
                    const response = await fetch(`/whatsapp/api/conversations/${phone}/toggle-followup`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.activeChat.stop_autofollowup = result.stop_autofollowup;
                        // Update in conversations list too
                        const conv = this.conversations.find(c => c.phone_number === phone);
                        if (conv) conv.stop_autofollowup = result.stop_autofollowup;
                        
                        alert(result.message);
                    }
                } catch (error) {
                    console.error('Error toggling follow-up:', error);
                }
            }
        }
    }
    </div>
</x-enterprise-layout>
