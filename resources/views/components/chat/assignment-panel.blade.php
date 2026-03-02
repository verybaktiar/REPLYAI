@props([
    'conversationType' => 'instagram',
    'conversationId' => null,
    'currentAssignment' => null,
])

<div 
    x-data="assignmentPanel({{ $conversationId ? "'{$conversationType}', {$conversationId}" : 'null, null' }})"
    x-init="init()"
    class="relative"
>
    <!-- Assignment Button / Dropdown Trigger -->
    <div class="relative">
        @if($currentAssignment)
            <!-- Currently Assigned - Show Agent Info -->
            <button 
                @click="openDropdown = !openDropdown"
                class="flex items-center gap-2 px-3 py-1.5 bg-blue-600/20 border border-blue-500/30 rounded-lg hover:bg-blue-600/30 transition-all group"
                :class="{ 'ring-2 ring-blue-500/50': openDropdown }"
            >
                <div class="relative">
                    <img 
                        src="{{ $currentAssignment['agent']['avatar'] ?? '' }}" 
                        alt="{{ $currentAssignment['agent']['name'] ?? 'Agent' }}"
                        class="w-6 h-6 rounded-full bg-gray-800 object-cover"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSIjM2I4MmY2Ij48Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSIxMiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkeT0iLjNlbSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiIGZvbnQtc2l6ZT0iMTAiIGZvbnQtd2VpZ2h0PSJib2xkIj5BPC90ZXh0Pjwvc3ZnPg=='"
                    >
                    <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 bg-green-500 rounded-full border border-gray-900"></span>
                </div>
                <span class="text-xs font-medium text-blue-400">{{ $currentAssignment['agent']['name'] ?? 'Assigned' }}</span>
                <svg class="w-3.5 h-3.5 text-blue-400/70 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        @else
            <!-- Not Assigned - Show Assign Button -->
            <button 
                @click="openDropdown = !openDropdown"
                class="flex items-center gap-2 px-3 py-1.5 bg-gray-800/50 border border-gray-700 rounded-lg hover:bg-gray-800 hover:border-gray-600 transition-all group"
                :class="{ 'ring-2 ring-blue-500/50': openDropdown }"
            >
                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="text-xs font-medium text-gray-400 group-hover:text-gray-300">Assign</span>
                <svg class="w-3.5 h-3.5 text-gray-500 group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        @endif

        <!-- Dropdown Menu -->
        <div 
            x-show="openDropdown"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
            @click.away="openDropdown = false"
            class="absolute right-0 top-full mt-2 w-72 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl shadow-black/50 z-50 overflow-hidden"
        >
            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-800 bg-gray-900/50">
                <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Assign Conversation
                </h3>
            </div>

            <!-- Search Agents -->
            <div class="px-3 py-2 border-b border-gray-800/50">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input 
                        x-model="searchQuery"
                        type="text" 
                        placeholder="Search agents..."
                        class="w-full pl-9 pr-3 py-2 bg-gray-950 border border-gray-800 rounded-lg text-sm text-gray-300 placeholder-gray-600 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30"
                    >
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loading" x-cloak class="px-4 py-6 text-center">
                <svg class="animate-spin w-5 h-5 mx-auto text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs text-gray-500 mt-2 block">Loading agents...</span>
            </div>

            <!-- Agents List -->
            <div x-show="!loading" x-cloak class="max-h-60 overflow-y-auto py-1">
                <template x-for="agent in filteredAgents" :key="agent.id">
                    <button 
                        @click="assignToAgent(agent)"
                        :disabled="isAssignedTo(agent.id)"
                        class="w-full px-3 py-2.5 flex items-center gap-3 hover:bg-gray-800/50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed group"
                    >
                        <!-- Agent Avatar -->
                        <div class="relative flex-shrink-0">
                            <img 
                                :src="agent.avatar" 
                                :alt="agent.name"
                                class="w-8 h-8 rounded-full bg-gray-800 object-cover"
                                onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIiBmaWxsPSIjM2I4MmY2Ij48Y2lyY2xlIGN4PSIxNiIgY3k9IjE2IiByPSIxNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkeT0iLjNlbSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiIGZvbnQtc2l6ZT0iMTIiIGZvbnQtd2VpZ2h0PSJib2xkIj5BPC90ZXh0Pjwvc3ZnPg=='"
                            >
                            <span x-show="agent.active_assignments > 0" 
                                  x-text="agent.active_assignments"
                                  class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 bg-amber-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center border border-gray-900">
                            </span>
                        </div>

                        <!-- Agent Info -->
                        <div class="flex-1 min-w-0 text-left">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-300 group-hover:text-white truncate" x-text="agent.name"></span>
                                <span x-show="isAssignedTo(agent.id)" 
                                      class="px-1.5 py-0.5 bg-blue-500/20 text-blue-400 text-[10px] font-medium rounded">
                                    Current
                                </span>
                            </div>
                            <span class="text-xs text-gray-500 truncate block" x-text="agent.email"></span>
                        </div>

                        <!-- Assignment Count -->
                        <div class="flex-shrink-0 text-right">
                            <span class="text-xs text-gray-500" x-text="agent.active_assignments + ' active'"></span>
                        </div>
                    </button>
                </template>

                <!-- Empty State -->
                <div x-show="filteredAgents.length === 0" x-cloak class="px-4 py-6 text-center">
                    <svg class="w-8 h-8 mx-auto text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="text-xs text-gray-500">No agents found</span>
                </div>
            </div>

            <!-- Current Assignment Actions -->
            <template x-if="currentAssignment">
                <div class="border-t border-gray-800 px-3 py-2 space-y-1">
                    <button 
                        @click="unassign()"
                        :disabled="processing"
                        class="w-full px-3 py-2 flex items-center gap-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors text-sm"
                    >
                        <svg x-show="!processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <svg x-show="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Unassign
                    </button>
                </div>
            </template>

            <!-- My Assignments Link -->
            <div class="border-t border-gray-800 px-3 py-2">
                <a href="/my-assignments" class="flex items-center justify-between px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition-colors">
                    <span>My Assignments</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Collision Warning Banner -->
    <div 
        x-show="collisionWarning && othersTyping.length > 0"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute top-full mt-2 right-0 w-80 bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 z-50"
    >
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-medium text-amber-400 mb-1">Agent Collision Warning</h4>
                <p class="text-xs text-amber-300/70 mb-2">
                    <span x-text="othersTyping.map(u => u.user_name).join(', ')"></span>
                    <span x-text="othersTyping.length === 1 ? ' is' : ' are'"></span>
                    also typing in this conversation.
                </p>
                <div class="flex items-center gap-1.5">
                    <template x-for="user in othersTyping.slice(0, 3)" :key="user.user_id">
                        <img 
                            :src="user.avatar || getInitialsAvatar(user.user_name)" 
                            :alt="user.user_name"
                            class="w-6 h-6 rounded-full bg-gray-800 border border-gray-700"
                        >
                    </template>
                    <span x-show="othersTyping.length > 3" 
                          class="text-xs text-amber-400/70"
                          x-text="'+' + (othersTyping.length - 3) + ' more'"></span>
                </div>
            </div>
            <button @click="collisionWarning = false" class="flex-shrink-0 text-amber-500/50 hover:text-amber-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Typing Indicator -->
    <div 
        x-show="othersTyping.length > 0 && !collisionWarning"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute top-full mt-2 right-0 flex items-center gap-2 px-3 py-1.5 bg-gray-800/90 border border-gray-700 rounded-full z-40"
    >
        <div class="flex items-center gap-1">
            <template x-for="user in othersTyping.slice(0, 2)" :key="user.user_id">
                <img 
                    :src="user.avatar || getInitialsAvatar(user.user_name)" 
                    :alt="user.user_name"
                    class="w-5 h-5 rounded-full bg-gray-700 border border-gray-600"
                >
            </template>
            <span x-show="othersTyping.length > 2" 
                  class="w-5 h-5 rounded-full bg-gray-700 border border-gray-600 flex items-center justify-center text-[9px] text-gray-400"
                  x-text="'+' + (othersTyping.length - 2)"></span>
        </div>
        <span class="text-xs text-gray-400">
            <span x-text="othersTyping.length === 1 ? othersTyping[0].user_name : othersTyping.length + ' agents'"></span>
            typing...
        </span>
        <div class="flex gap-0.5 ml-1">
            <span class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
            <span class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
            <span class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
        </div>
    </div>
</div>

<script>
function assignmentPanel(conversationType, conversationId) {
    return {
        conversationType: conversationType,
        conversationId: conversationId,
        openDropdown: false,
        loading: false,
        processing: false,
        agents: [],
        searchQuery: '',
        currentAssignment: @json($currentAssignment),
        othersTyping: [],
        collisionWarning: false,
        typingInterval: null,
        collisionCheckInterval: null,
        isTyping: false,

        get filteredAgents() {
            if (!this.searchQuery) return this.agents;
            const query = this.searchQuery.toLowerCase();
            return this.agents.filter(agent => 
                agent.name.toLowerCase().includes(query) || 
                agent.email.toLowerCase().includes(query)
            );
        },

        init() {
            if (this.conversationId) {
                this.loadAgents();
                this.startCollisionCheck();
                this.setupTypingListener();
            }
        },

        loadAgents() {
            this.loading = true;
            fetch('/api/chat/agents')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.agents = data.data;
                    }
                    this.loading = false;
                })
                .catch(err => {
                    console.error('Failed to load agents:', err);
                    this.loading = false;
                });
        },

        isAssignedTo(agentId) {
            return this.currentAssignment && this.currentAssignment.agent.id === agentId;
        },

        assignToAgent(agent) {
            if (this.isAssignedTo(agent.id)) {
                this.openDropdown = false;
                return;
            }

            this.processing = true;
            const url = this.currentAssignment 
                ? `/api/chat/${this.conversationType}/${this.conversationId}/transfer`
                : `/api/chat/${this.conversationType}/${this.conversationId}/assign`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ user_id: agent.id })
            })
            .then(res => res.json())
            .then(data => {
                this.processing = false;
                if (data.success) {
                    this.currentAssignment = data.assignment;
                    this.openDropdown = false;
                    this.showToast('Conversation assigned to ' + agent.name, 'success');
                    window.dispatchEvent(new CustomEvent('conversation-assigned', { 
                        detail: { assignment: data.assignment }
                    }));
                } else {
                    this.showToast(data.error || 'Failed to assign', 'error');
                }
            })
            .catch(err => {
                this.processing = false;
                console.error('Assignment failed:', err);
                this.showToast('Failed to assign conversation', 'error');
            });
        },

        unassign() {
            this.processing = true;
            fetch(`/api/chat/${this.conversationType}/${this.conversationId}/assign`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(res => res.json())
            .then(data => {
                this.processing = false;
                if (data.success) {
                    this.currentAssignment = null;
                    this.openDropdown = false;
                    this.showToast('Conversation unassigned', 'success');
                    window.dispatchEvent(new CustomEvent('conversation-unassigned'));
                } else {
                    this.showToast(data.error || 'Failed to unassign', 'error');
                }
            })
            .catch(err => {
                this.processing = false;
                console.error('Unassign failed:', err);
                this.showToast('Failed to unassign conversation', 'error');
            });
        },

        startCollisionCheck() {
            // Check typing status every 3 seconds
            this.collisionCheckInterval = setInterval(() => {
                this.checkCollision();
            }, 3000);
        },

        checkCollision() {
            if (!this.conversationId) return;

            fetch(`/api/chat/${this.conversationType}/${this.conversationId}/collision-check`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.othersTyping = data.data.others_typing;
                        this.collisionWarning = data.data.collision_warning;
                    }
                })
                .catch(err => console.error('Collision check failed:', err));
        },

        setupTypingListener() {
            // Listen for input events on the chat input
            const chatInput = document.querySelector('[data-chat-input]');
            if (chatInput) {
                let typingTimeout;
                chatInput.addEventListener('input', () => {
                    if (!this.isTyping) {
                        this.broadcastTyping(true);
                        this.isTyping = true;
                    }
                    clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(() => {
                        this.broadcastTyping(false);
                        this.isTyping = false;
                    }, 3000);
                });

                chatInput.addEventListener('blur', () => {
                    if (this.isTyping) {
                        this.broadcastTyping(false);
                        this.isTyping = false;
                    }
                });
            }
        },

        broadcastTyping(isTyping) {
            if (!this.conversationId) return;

            fetch('/api/chat/typing', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    conversation_type: this.conversationType,
                    conversation_id: this.conversationId,
                    is_typing: isTyping
                })
            }).catch(err => console.error('Failed to broadcast typing:', err));
        },

        getInitialsAvatar(name) {
            const initials = name.split(' ').map(w => w[0]?.toUpperCase()).filter(Boolean).slice(0, 2).join('');
            const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
            const color = colors[name.length % colors.length];
            const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">` +
                `<circle cx="16" cy="16" r="16" fill="${color}"/>` +
                `<text x="50%" y="50%" dy=".3em" text-anchor="middle" fill="white" font-size="12" font-weight="bold">${initials}</text>` +
                `</svg>`;
            return 'data:image/svg+xml;base64,' + btoa(svg);
        },

        showToast(message, type = 'info') {
            // Dispatch toast event
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }
}
</script>
