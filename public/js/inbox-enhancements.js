/**
 * ReplyAI Inbox Enhancements
 * - Search conversations
 * - Typing indicator
 * - Real-time updates
 */

// Conversation Search
class ConversationSearch {
    constructor(listSelector, inputSelector) {
        this.list = document.querySelector(listSelector);
        this.input = document.querySelector(inputSelector);
        this.items = [];

        if (this.list && this.input) {
            this.init();
        }
    }

    init() {
        // Cache all conversation items
        this.items = Array.from(this.list.querySelectorAll('[data-conversation]'));

        // Listen to input
        this.input.addEventListener('input', (e) => this.search(e.target.value));

        // Add keyboard shortcut (Ctrl+K or Cmd+K)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.input.focus();
            }
            if (e.key === 'Escape') {
                this.input.value = '';
                this.search('');
                this.input.blur();
            }
        });
    }

    search(query) {
        const searchTerm = query.toLowerCase().trim();

        this.items.forEach(item => {
            const name = item.querySelector('[data-name]')?.textContent?.toLowerCase() || '';
            const preview = item.querySelector('[data-preview]')?.textContent?.toLowerCase() || '';

            const matches = name.includes(searchTerm) || preview.includes(searchTerm);
            item.style.display = matches ? '' : 'none';
        });

        // Update no results message
        const noResults = this.list.querySelector('.no-search-results');
        const visibleItems = this.items.filter(i => i.style.display !== 'none');

        if (visibleItems.length === 0 && searchTerm) {
            if (!noResults) {
                const msg = document.createElement('div');
                msg.className = 'no-search-results text-center py-8 text-slate-500';
                msg.innerHTML = `<p>Tidak ada hasil untuk "<strong>${query}</strong>"</p>`;
                this.list.appendChild(msg);
            }
        } else if (noResults) {
            noResults.remove();
        }
    }
}

// Typing Indicator Component
class TypingIndicator {
    constructor() {
        this.element = null;
        this.timeout = null;
    }

    create() {
        const indicator = document.createElement('div');
        indicator.className = 'typing-indicator-container flex items-center gap-2 px-4 py-2';
        indicator.innerHTML = `
            <div class="typing-indicator flex items-center gap-1 px-3 py-2 bg-slate-700 rounded-2xl rounded-bl-sm">
                <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
            </div>
            <span class="text-xs text-slate-500">sedang mengetik...</span>
        `;
        this.element = indicator;
        return indicator;
    }

    show(container) {
        if (!this.element) {
            this.create();
        }

        // Remove existing
        this.hide();

        // Add to container
        container.appendChild(this.element);

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;

        // Auto-hide after 5 seconds
        this.timeout = setTimeout(() => this.hide(), 5000);
    }

    hide() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        this.element?.remove();
    }
}

// Initialize search on page load
document.addEventListener('DOMContentLoaded', function () {
    // Initialize conversation search
    new ConversationSearch('.conversation-list', '#conversation-search');

    // Initialize typing indicator (global instance)
    window.typingIndicator = new TypingIndicator();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ConversationSearch, TypingIndicator };
}
