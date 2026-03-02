{{--
    AI Suggestions Component
    
    Usage:
    <x-chat.ai-suggestions 
        :conversation-id="$conversationId"
        :conversation-type="'instagram'"
        :input-selector="'#message-input'"
        :on-insert="'insertSuggestion'"
    />
    
    Props:
    - conversationId: int (required) - ID of the conversation
    - conversationType: string (required) - 'instagram', 'whatsapp', or 'web'
    - inputSelector: string (required) - CSS selector for the input field
    - onInsert: string (optional) - Callback function name when suggestion is inserted
--}}

@props([
    'conversationId' => null,
    'conversationType' => 'instagram',
    'inputSelector' => '#message-input',
    'onInsert' => null,
])

<div 
    x-data="aiSuggestions({{ $conversationId }}, '{{ $conversationType }}', '{{ $inputSelector }}', '{{ $onInsert }}')"
    x-init="init()"
    x-show="showComponent"
    x-cloak
    class="w-full"
>
    {{-- AI Suggestions Container --}}
    <div class="px-4 py-2 bg-gradient-to-r from-blue-500/5 via-purple-500/5 to-blue-500/5 border-t border-blue-500/10">
        
        {{-- Header with Sentiment Badge --}}
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                {{-- AI Icon --}}
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-blue-400">auto_awesome</span>
                    <span class="text-xs font-medium text-blue-400">AI Suggestions</span>
                </div>

                {{-- Sentiment Indicator --}}
                <template x-if="sentiment.sentiment">
                    <span 
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium border"
                        :class="getSentimentClass()"
                    >
                        <span class="material-symbols-outlined text-[10px]" x-text="getSentimentIcon()"></span>
                        <span x-text="getSentimentLabel()"></span>
                    </span>
                </template>

                {{-- Intent Badge --}}
                <template x-if="intent.intent && intent.intent !== 'general'">
                    <span 
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-500/10 text-gray-400 border border-gray-500/20"
                    >
                        <span x-text="getIntentLabel()"></span>
                    </span>
                </template>
            </div>

            {{-- Refresh Button --}}
            <button 
                @click="refreshSuggestions()"
                :disabled="loading"
                class="p-1 rounded hover:bg-white/5 text-gray-500 hover:text-blue-400 transition-colors disabled:opacity-50"
                title="Refresh suggestions"
            >
                <span class="material-symbols-outlined text-sm" :class="{ 'animate-spin': loading }">refresh</span>
            </button>
        </div>

        {{-- Suggestion Chips --}}
        <div class="flex flex-wrap gap-2" x-show="!loading && suggestions.length > 0">
            <template x-for="(suggestion, index) in suggestions" :key="index">
                <button
                    @click="insertSuggestion(suggestion)"
                    @mouseenter="hoveredIndex = index"
                    @mouseleave="hoveredIndex = null"
                    class="group relative px-3 py-1.5 text-xs text-left bg-[#1a2332] hover:bg-blue-500/20 border border-[#2a3544] hover:border-blue-500/40 rounded-lg transition-all duration-200 max-w-[280px]"
                    :class="{ 'ring-1 ring-blue-500/50': hoveredIndex === index }"
                >
                    <span class="text-gray-300 group-hover:text-gray-200 line-clamp-2" x-text="suggestion"></span>
                    
                    {{-- Insert Hint --}}
                    <span 
                        class="absolute -top-2 -right-1 opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                        <span class="material-symbols-outlined text-sm text-blue-400">arrow_forward</span>
                    </span>
                </button>
            </template>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="flex items-center gap-3 py-1">
            <div class="flex gap-1">
                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
            </div>
            <span class="text-xs text-gray-500">AI sedang menganalisis percakapan...</span>
        </div>

        {{-- Empty/Error State --}}
        <div x-show="!loading && suggestions.length === 0 && error" class="flex items-center gap-2 py-1">
            <span class="material-symbols-outlined text-sm text-gray-500">error_outline</span>
            <span class="text-xs text-gray-500" x-text="error"></span>
            <button @click="refreshSuggestions()" class="text-xs text-blue-400 hover:text-blue-300">Coba lagi</button>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function aiSuggestions(conversationId, conversationType, inputSelector, onInsertCallback) {
    return {
        // Data
        conversationId: conversationId,
        conversationType: conversationType,
        inputSelector: inputSelector,
        onInsertCallback: onInsertCallback,
        
        suggestions: [],
        sentiment: {},
        intent: {},
        loading: false,
        error: null,
        showComponent: false,
        hoveredIndex: null,
        
        // Settings
        autoLoad: true,
        refreshInterval: null,
        
        init() {
            if (!this.conversationId) {
                this.showComponent = false;
                return;
            }
            
            this.showComponent = true;
            
            if (this.autoLoad) {
                this.loadSuggestions();
            }
            
            // Listen for conversation changes
            window.addEventListener('conversation-changed', (e) => {
                if (e.detail && e.detail.conversationId) {
                    this.conversationId = e.detail.conversationId;
                    this.conversationType = e.detail.conversationType || this.conversationType;
                    this.loadSuggestions();
                }
            });
            
            // Listen for new messages
            window.addEventListener('new-message-received', () => {
                this.loadSuggestions();
            });
        },
        
        async loadSuggestions() {
            if (!this.conversationId || this.loading) return;
            
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch('/api/ai/suggest-replies', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: this.conversationId,
                        conversation_type: this.conversationType,
                    }),
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.suggestions = data.data.suggestions || [];
                    this.sentiment = data.data.sentiment || {};
                    this.intent = data.data.intent || {};
                    this.error = null;
                } else {
                    this.error = data.message || 'Gagal memuat saran';
                    this.suggestions = [];
                }
            } catch (err) {
                console.error('Failed to load AI suggestions:', err);
                this.error = 'Terjadi kesalahan';
                this.suggestions = [];
            } finally {
                this.loading = false;
            }
        },
        
        refreshSuggestions() {
            this.suggestions = [];
            this.loadSuggestions();
        },
        
        insertSuggestion(suggestion) {
            const input = document.querySelector(this.inputSelector);
            if (!input) return;
            
            // Set value
            input.value = suggestion;
            
            // Trigger events
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            
            // Focus input
            input.focus();
            
            // Callback if provided
            if (this.onInsertCallback && typeof window[this.onInsertCallback] === 'function') {
                window[this.onInsertCallback](suggestion);
            }
            
            // Track usage
            this.trackUsage(suggestion);
        },
        
        async trackUsage(suggestion) {
            try {
                await fetch('/api/ai/track-usage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    },
                    body: JSON.stringify({
                        conversation_id: this.conversationId,
                        conversation_type: this.conversationType,
                        suggestion: suggestion,
                    }),
                });
            } catch (e) {
                // Silent fail
            }
        },
        
        // Sentiment helpers
        getSentimentClass() {
            const sentiment = this.sentiment.sentiment;
            const classes = {
                'positive': 'bg-green-500/10 text-green-400 border-green-500/20',
                'negative': 'bg-red-500/10 text-red-400 border-red-500/20',
                'neutral': 'bg-gray-500/10 text-gray-400 border-gray-500/20',
            };
            return classes[sentiment] || classes['neutral'];
        },
        
        getSentimentIcon() {
            const icons = {
                'positive': 'sentiment_satisfied',
                'negative': 'sentiment_dissatisfied',
                'neutral': 'sentiment_neutral',
            };
            return icons[this.sentiment.sentiment] || 'sentiment_neutral';
        },
        
        getSentimentLabel() {
            const labels = {
                'positive': 'Positif',
                'negative': 'Negatif',
                'neutral': 'Netral',
            };
            return labels[this.sentiment.sentiment] || 'Netral';
        },
        
        // Intent helpers
        getIntentLabel() {
            const labels = {
                'complaint': 'Keluhan',
                'inquiry': 'Pertanyaan',
                'purchase': 'Pembelian',
                'support': 'Bantuan',
                'feedback': 'Feedback',
                'greeting': 'Sapaan',
                'urgent': 'Urgent',
                'cancellation': 'Pembatalan',
                'general': 'Umum',
            };
            return labels[this.intent.intent] || this.intent.intent;
        },
    };
}
</script>
@endpush
@endonce
