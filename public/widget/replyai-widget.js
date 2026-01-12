/**
 * ReplyAI Chat Widget
 * Embeddable chat widget for WordPress and other websites
 * 
 * Usage:
 * <script src="https://your-domain.com/widget/replyai-widget.js" data-api-key="YOUR_API_KEY"></script>
 */

(function () {
    'use strict';

    // Get configuration from script tag
    const scriptTag = document.currentScript;
    const API_KEY = scriptTag?.getAttribute('data-api-key');
    const API_BASE = scriptTag?.src.replace('/widget/replyai-widget.js', '') || '';

    if (!API_KEY) {
        console.error('[ReplyAI Widget] Missing data-api-key attribute');
        return;
    }

    // Widget State
    let widgetConfig = null;
    let visitorId = localStorage.getItem('replyai_visitor_id');
    let messages = [];
    let isOpen = false;
    let isLoading = false;
    let lastMessageId = 0;

    // Generate visitor ID if not exists
    if (!visitorId) {
        visitorId = 'v_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
        localStorage.setItem('replyai_visitor_id', visitorId);
    }

    // Inject styles
    const styles = `
        #replyai-widget-container * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        
        #replyai-widget-bubble {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--replyai-primary, #4F46E5);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
            z-index: 999999;
        }
        
        #replyai-widget-bubble:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.35);
        }
        
        #replyai-widget-bubble svg {
            width: 28px;
            height: 28px;
            transition: transform 0.3s ease;
        }
        
        #replyai-widget-bubble.open svg {
            transform: rotate(90deg);
        }
        
        #replyai-widget-panel {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 380px;
            height: 550px;
            max-height: calc(100vh - 120px);
            background: #111722;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 999998;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        #replyai-widget-panel.open {
            display: flex;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .replyai-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, var(--replyai-primary, #4F46E5) 0%, #6366f1 100%);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .replyai-header-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .replyai-header-info h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: white;
        }
        
        .replyai-header-info p {
            margin: 2px 0 0;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .replyai-header-close {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        
        .replyai-header-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .replyai-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #101622;
        }
        
        .replyai-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        .replyai-messages::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .replyai-messages::-webkit-scrollbar-thumb {
            background: #2d3748;
            border-radius: 3px;
        }
        
        .replyai-message {
            display: flex;
            gap: 8px;
            max-width: 85%;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .replyai-message.visitor {
            flex-direction: row-reverse;
            margin-left: auto;
        }
        
        .replyai-message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #2a3446;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .replyai-message.visitor .replyai-message-avatar {
            background: #374151;
        }
        
        .replyai-message-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .replyai-message-bubble {
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }
        
        .replyai-message.bot .replyai-message-bubble {
            background: #2a3446;
            color: #e2e8f0;
            border-top-left-radius: 4px;
        }
        
        .replyai-message.visitor .replyai-message-bubble {
            background: var(--replyai-primary, #4F46E5);
            color: white;
            border-top-right-radius: 4px;
        }
        
        .replyai-message-time {
            font-size: 10px;
            color: #64748b;
            padding: 0 4px;
        }
        
        .replyai-message.visitor .replyai-message-time {
            text-align: right;
        }
        
        .replyai-typing {
            display: flex;
            gap: 4px;
            padding: 12px 16px;
            background: #2a3446;
            border-radius: 16px;
            border-top-left-radius: 4px;
            width: fit-content;
        }
        
        .replyai-typing span {
            width: 8px;
            height: 8px;
            background: #64748b;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }
        
        .replyai-typing span:nth-child(1) { animation-delay: 0s; }
        .replyai-typing span:nth-child(2) { animation-delay: 0.2s; }
        .replyai-typing span:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-6px); opacity: 1; }
        }
        
        .replyai-input-area {
            padding: 12px 16px;
            background: #111722;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .replyai-input-form {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        
        .replyai-input {
            flex: 1;
            padding: 12px 16px;
            background: #1e2634;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
            resize: none;
            max-height: 120px;
            outline: none;
            transition: border-color 0.2s;
        }
        
        .replyai-input:focus {
            border-color: var(--replyai-primary, #4F46E5);
        }
        
        .replyai-input::placeholder {
            color: #64748b;
        }
        
        .replyai-send-btn {
            width: 44px;
            height: 44px;
            background: var(--replyai-primary, #4F46E5);
            border: none;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .replyai-send-btn:hover {
            transform: scale(1.05);
        }
        
        .replyai-send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .replyai-welcome {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        
        .replyai-welcome-icon {
            width: 64px;
            height: 64px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: var(--replyai-primary, #4F46E5);
        }
        
        .replyai-welcome h4 {
            margin: 0 0 8px;
            color: white;
            font-size: 18px;
        }
        
        .replyai-welcome p {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .replyai-powered {
            padding: 8px;
            text-align: center;
            font-size: 10px;
            color: #475569;
            background: #0d1117;
        }
        
        .replyai-powered a {
            color: #6366f1;
            text-decoration: none;
        }
        
        /* Mobile responsive */
        @media (max-width: 480px) {
            #replyai-widget-panel {
                width: 100%;
                height: 100%;
                max-height: 100%;
                bottom: 0;
                right: 0;
                border-radius: 0;
            }
            
            #replyai-widget-bubble {
                bottom: 16px;
                right: 16px;
            }
        }
        
        /* Position variants */
        #replyai-widget-container.bottom-left #replyai-widget-bubble,
        #replyai-widget-container.bottom-left #replyai-widget-panel {
            right: auto;
            left: 20px;
        }
    `;

    // Create widget container
    const container = document.createElement('div');
    container.id = 'replyai-widget-container';

    // Inject styles
    const styleEl = document.createElement('style');
    styleEl.textContent = styles;
    container.appendChild(styleEl);

    // Create chat bubble
    const bubble = document.createElement('button');
    bubble.id = 'replyai-widget-bubble';
    bubble.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    `;
    container.appendChild(bubble);

    // Create chat panel
    const panel = document.createElement('div');
    panel.id = 'replyai-widget-panel';
    panel.innerHTML = `
        <div class="replyai-header">
            <div class="replyai-header-avatar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/>
                    <circle cx="8" cy="14" r="2"/>
                    <circle cx="16" cy="14" r="2"/>
                </svg>
            </div>
            <div class="replyai-header-info">
                <h3 id="replyai-bot-name">Bot ReplyAI</h3>
                <p>Online • Siap membantu</p>
            </div>
            <button class="replyai-header-close" id="replyai-close-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="replyai-messages" id="replyai-messages">
            <div class="replyai-welcome">
                <div class="replyai-welcome-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <h4>Halo! 👋</h4>
                <p id="replyai-welcome-msg">Ada yang bisa kami bantu?</p>
            </div>
        </div>
        <div class="replyai-input-area">
            <form class="replyai-input-form" id="replyai-form">
                <textarea class="replyai-input" id="replyai-input" placeholder="Ketik pesan..." rows="1"></textarea>
                <button type="submit" class="replyai-send-btn" id="replyai-send-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </form>
        </div>
        <div class="replyai-powered">
            Powered by <a href="#" target="_blank">ReplyAI</a>
        </div>
    `;
    container.appendChild(panel);

    // Append to body
    document.body.appendChild(container);

    // Initialize widget
    async function initWidget() {
        try {
            const response = await fetch(`${API_BASE}/api/web/widget/${API_KEY}`);
            const data = await response.json();

            if (!data.success) {
                console.error('[ReplyAI Widget]', data.error);
                return;
            }

            widgetConfig = data.widget;

            // Apply configuration
            document.documentElement.style.setProperty('--replyai-primary', widgetConfig.primary_color);
            document.getElementById('replyai-bot-name').textContent = widgetConfig.bot_name;
            document.getElementById('replyai-welcome-msg').textContent = widgetConfig.welcome_message;

            if (widgetConfig.position === 'bottom-left') {
                container.classList.add('bottom-left');
            }

            // Load conversation history
            await loadConversation();

        } catch (error) {
            console.error('[ReplyAI Widget] Failed to initialize:', error);
        }
    }

    // Load conversation history
    async function loadConversation() {
        try {
            const response = await fetch(`${API_BASE}/api/web/conversation/${visitorId}?api_key=${API_KEY}`);
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                messages = data.messages;
                renderMessages();
                lastMessageId = messages[messages.length - 1].id;
            }
        } catch (error) {
            console.error('[ReplyAI Widget] Failed to load conversation:', error);
        }
    }

    // Render messages
    function renderMessages() {
        const container = document.getElementById('replyai-messages');

        if (messages.length === 0) {
            return; // Keep welcome message
        }

        // Clear welcome message
        container.innerHTML = '';

        messages.forEach(msg => {
            const msgEl = document.createElement('div');
            msgEl.className = `replyai-message ${msg.sender_type === 'visitor' ? 'visitor' : 'bot'}`;

            const time = new Date(msg.created_at).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            msgEl.innerHTML = `
                <div class="replyai-message-avatar">
                    ${msg.sender_type === 'visitor' ?
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="#64748b" stroke="none"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>' :
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/></svg>'
                }
                </div>
                <div class="replyai-message-content">
                    <div class="replyai-message-bubble">${escapeHtml(msg.content)}</div>
                    <span class="replyai-message-time">${time}</span>
                </div>
            `;

            container.appendChild(msgEl);
        });

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    // Show typing indicator
    function showTyping() {
        const container = document.getElementById('replyai-messages');
        const typingEl = document.createElement('div');
        typingEl.id = 'replyai-typing';
        typingEl.className = 'replyai-message bot';
        typingEl.innerHTML = `
            <div class="replyai-message-avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/></svg>
            </div>
            <div class="replyai-typing">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        container.appendChild(typingEl);
        container.scrollTop = container.scrollHeight;
    }

    // Hide typing indicator
    function hideTyping() {
        const typingEl = document.getElementById('replyai-typing');
        if (typingEl) typingEl.remove();
    }

    // Send message
    async function sendMessage(content) {
        if (!content.trim() || isLoading) return;

        isLoading = true;
        const sendBtn = document.getElementById('replyai-send-btn');
        sendBtn.disabled = true;

        // Add visitor message to UI immediately
        const visitorMsg = {
            id: Date.now(),
            sender_type: 'visitor',
            content: content,
            created_at: new Date().toISOString()
        };
        messages.push(visitorMsg);
        renderMessages();

        // Show typing indicator
        showTyping();

        try {
            const response = await fetch(`${API_BASE}/api/web/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    api_key: API_KEY,
                    visitor_id: visitorId,
                    message: content,
                    page_url: window.location.href
                })
            });

            const data = await response.json();

            hideTyping();

            if (data.success && data.bot_response) {
                const botMsg = {
                    id: data.message_id + 1,
                    sender_type: 'bot',
                    content: data.bot_response,
                    created_at: new Date().toISOString()
                };
                messages.push(botMsg);
                lastMessageId = botMsg.id;
                renderMessages();
            }

        } catch (error) {
            console.error('[ReplyAI Widget] Failed to send message:', error);
            hideTyping();
        } finally {
            isLoading = false;
            sendBtn.disabled = false;
        }
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    }

    // Event listeners
    bubble.addEventListener('click', () => {
        isOpen = !isOpen;
        panel.classList.toggle('open', isOpen);
        bubble.classList.toggle('open', isOpen);

        if (isOpen) {
            document.getElementById('replyai-input').focus();
        }
    });

    document.getElementById('replyai-close-btn').addEventListener('click', () => {
        isOpen = false;
        panel.classList.remove('open');
        bubble.classList.remove('open');
    });

    document.getElementById('replyai-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const input = document.getElementById('replyai-input');
        sendMessage(input.value);
        input.value = '';
        input.style.height = 'auto';
    });

    // Auto-resize textarea
    document.getElementById('replyai-input').addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Enter to send, Shift+Enter for new line
    document.getElementById('replyai-input').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('replyai-form').dispatchEvent(new Event('submit'));
        }
    });

    // Polling for new messages (when CS replies)
    setInterval(async () => {
        if (!isOpen || isLoading) return;

        try {
            const response = await fetch(`${API_BASE}/api/web/poll?api_key=${API_KEY}&visitor_id=${visitorId}&last_message_id=${lastMessageId}`);
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    messages.push(msg);
                    if (msg.id > lastMessageId) lastMessageId = msg.id;
                });
                renderMessages();
            }
        } catch (error) {
            // Silent fail for polling
        }
    }, 5000);

    // Initialize
    initWidget();

})();
