/**
 * ReplyAI Chat Widget for Landing Page
 * Simple, elegant, and fully functional
 */
(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        apiUrl: '/api/webhook/landing-chat',
        botName: 'ReplyAI Assistant',
        botAvatar: 'https://api.dicebear.com/7.x/bottts/svg?seed=ReplyAI',
        welcomeMessage: 'Halo! 👋 Selamat datang di ReplyAI. Saya bisa membantu menjelaskan fitur atau menjawab pertanyaan Anda.',
        primaryColor: '#6366f1',
        position: 'right'
    };
    
    // Create Widget HTML
    function createWidget() {
        const widget = document.createElement('div');
        widget.id = 'replyai-widget';
        widget.innerHTML = `
            <style>
                #replyai-widget {
                    position: fixed;
                    bottom: 20px;
                    ${CONFIG.position}: 20px;
                    z-index: 9999;
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                }
                
                .rw-chat-button {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, ${CONFIG.primaryColor} 0%, #8b5cf6 100%);
                    border: none;
                    cursor: pointer;
                    box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                    position: relative;
                }
                
                .rw-chat-button:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 30px rgba(99, 102, 241, 0.5);
                }
                
                .rw-chat-button svg {
                    width: 28px;
                    height: 28px;
                    color: white;
                }
                
                .rw-notification {
                    position: absolute;
                    top: -2px;
                    right: -2px;
                    width: 20px;
                    height: 20px;
                    background: #ef4444;
                    border-radius: 50%;
                    border: 2px solid white;
                    animation: rw-pulse 2s infinite;
                }
                
                @keyframes rw-pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
                
                .rw-chat-window {
                    position: absolute;
                    bottom: 80px;
                    ${CONFIG.position}: 0;
                    width: 360px;
                    max-width: calc(100vw - 40px);
                    height: 500px;
                    max-height: calc(100vh - 120px);
                    background: #0f172a;
                    border-radius: 20px;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    opacity: 0;
                    transform: scale(0.9) translateY(20px);
                    visibility: hidden;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
                
                .rw-chat-window.open {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                    visibility: visible;
                }
                
                .rw-header {
                    padding: 20px;
                    background: linear-gradient(135deg, ${CONFIG.primaryColor} 0%, #8b5cf6 100%);
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                
                .rw-avatar {
                    width: 44px;
                    height: 44px;
                    border-radius: 50%;
                    border: 2px solid white;
                    object-fit: cover;
                }
                
                .rw-header-info {
                    flex: 1;
                }
                
                .rw-header-info h3 {
                    color: white;
                    font-size: 16px;
                    font-weight: 600;
                    margin: 0;
                }
                
                .rw-header-info p {
                    color: rgba(255, 255, 255, 0.8);
                    font-size: 12px;
                    margin: 2px 0 0 0;
                }
                
                .rw-close-btn {
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.2s;
                }
                
                .rw-close-btn:hover {
                    background: rgba(255, 255, 255, 0.3);
                }
                
                .rw-close-btn svg {
                    width: 20px;
                    height: 20px;
                    color: white;
                }
                
                .rw-messages {
                    flex: 1;
                    overflow-y: auto;
                    padding: 20px;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }
                
                .rw-message {
                    max-width: 80%;
                    padding: 12px 16px;
                    border-radius: 16px;
                    font-size: 14px;
                    line-height: 1.5;
                    animation: rw-fade-in 0.3s ease;
                }
                
                @keyframes rw-fade-in {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                .rw-message.bot {
                    align-self: flex-start;
                    background: rgba(99, 102, 241, 0.15);
                    color: #e2e8f0;
                    border-bottom-left-radius: 4px;
                }
                
                .rw-message.user {
                    align-self: flex-end;
                    background: ${CONFIG.primaryColor};
                    color: white;
                    border-bottom-right-radius: 4px;
                }
                
                .rw-input-area {
                    padding: 16px 20px;
                    border-top: 1px solid rgba(255, 255, 255, 0.1);
                    display: flex;
                    gap: 10px;
                }
                
                .rw-input {
                    flex: 1;
                    padding: 12px 16px;
                    border-radius: 12px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    background: rgba(255, 255, 255, 0.05);
                    color: white;
                    font-size: 14px;
                    outline: none;
                    transition: border-color 0.2s;
                }
                
                .rw-input:focus {
                    border-color: ${CONFIG.primaryColor};
                }
                
                .rw-input::placeholder {
                    color: #64748b;
                }
                
                .rw-send-btn {
                    width: 44px;
                    height: 44px;
                    border-radius: 12px;
                    background: ${CONFIG.primaryColor};
                    border: none;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s;
                }
                
                .rw-send-btn:hover {
                    background: #5558e3;
                    transform: scale(1.05);
                }
                
                .rw-send-btn svg {
                    width: 20px;
                    height: 20px;
                    color: white;
                }
                
                .rw-typing {
                    display: flex;
                    gap: 4px;
                    padding: 12px 16px;
                }
                
                .rw-typing span {
                    width: 8px;
                    height: 8px;
                    background: ${CONFIG.primaryColor};
                    border-radius: 50%;
                    animation: rw-typing 1.4s infinite;
                }
                
                .rw-typing span:nth-child(2) { animation-delay: 0.2s; }
                .rw-typing span:nth-child(3) { animation-delay: 0.4s; }
                
                @keyframes rw-typing {
                    0%, 60%, 100% { transform: translateY(0); }
                    30% { transform: translateY(-10px); }
                }
                
                @media (max-width: 480px) {
                    .rw-chat-window {
                        width: calc(100vw - 40px);
                        height: calc(100vh - 120px);
                    }
                }
            </style>
            
            <button class="rw-chat-button" id="rwToggleBtn" aria-label="Open chat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="rw-notification"></span>
            </button>
            
            <div class="rw-chat-window" id="rwChatWindow">
                <div class="rw-header">
                    <img src="${CONFIG.botAvatar}" alt="${CONFIG.botName}" class="rw-avatar">
                    <div class="rw-header-info">
                        <h3>${CONFIG.botName}</h3>
                        <p>Online</p>
                    </div>
                    <button class="rw-close-btn" id="rwCloseBtn" aria-label="Close chat">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                
                <div class="rw-messages" id="rwMessages"></div>
                
                <div class="rw-input-area">
                    <input type="text" class="rw-input" id="rwInput" placeholder="Tulis pesan..." maxlength="500">
                    <button class="rw-send-btn" id="rwSendBtn" aria-label="Send message">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(widget);
        return widget;
    }
    
    // Initialize Chat
    function init() {
        const widget = createWidget();
        const toggleBtn = document.getElementById('rwToggleBtn');
        const closeBtn = document.getElementById('rwCloseBtn');
        const chatWindow = document.getElementById('rwChatWindow');
        const messagesContainer = document.getElementById('rwMessages');
        const input = document.getElementById('rwInput');
        const sendBtn = document.getElementById('rwSendBtn');
        
        let isOpen = false;
        let sessionId = localStorage.getItem('rw_session_id') || generateSessionId();
        localStorage.setItem('rw_session_id', sessionId);
        
        // Toggle chat
        toggleBtn.addEventListener('click', () => {
            isOpen = !isOpen;
            chatWindow.classList.toggle('open', isOpen);
            if (isOpen && messagesContainer.children.length === 0) {
                addMessage('bot', CONFIG.welcomeMessage);
            }
        });
        
        closeBtn.addEventListener('click', () => {
            isOpen = false;
            chatWindow.classList.remove('open');
        });
        
        // Send message
        function sendMessage() {
            const text = input.value.trim();
            if (!text) return;
            
            addMessage('user', text);
            input.value = '';
            
            // Show typing indicator
            showTyping();
            
            // Simulate bot response (replace with actual API call)
            setTimeout(() => {
                hideTyping();
                const response = getBotResponse(text);
                addMessage('bot', response);
            }, 1000 + Math.random() * 1000);
        }
        
        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
        
        // Add message to chat
        function addMessage(type, text) {
            const message = document.createElement('div');
            message.className = `rw-message ${type}`;
            message.textContent = text;
            messagesContainer.appendChild(message);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Show typing indicator
        function showTyping() {
            const typing = document.createElement('div');
            typing.className = 'rw-message bot rw-typing';
            typing.id = 'rwTypingIndicator';
            typing.innerHTML = '<span></span><span></span><span></span>';
            messagesContainer.appendChild(typing);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Hide typing indicator
        function hideTyping() {
            const typing = document.getElementById('rwTypingIndicator');
            if (typing) typing.remove();
        }
        
        // Generate session ID
        function generateSessionId() {
            return 'rw_' + Math.random().toString(36).substring(2, 15);
        }
        
        // Simple bot responses (replace with AI integration)
        function getBotResponse(text) {
            const lower = text.toLowerCase();
            
            if (lower.includes('harga') || lower.includes('paket') || lower.includes('biaya')) {
                return 'Kami memiliki beberapa paket: Gratis, Hemat (Rp 99.000/bulan), dan Business. Mau saya jelaskan detailnya?';
            }
            if (lower.includes('demo') || lower.includes('coba')) {
                return 'Anda bisa daftar gratis dan langsung coba 7 hari tanpa biaya! Klik tombol "Minta Demo" di atas.';
            }
            if (lower.includes('whatsapp') || lower.includes('wa')) {
                return 'ReplyAI bisa terhubung ke WhatsApp Business API dan bisa balas otomatis 24/7. Mau tahu cara setupnya?';
            }
            if (lower.includes('instagram') || lower.includes('ig')) {
                return 'Kami juga support Instagram DM! Semua chat dari IG dan WA bisa dikelola dalam satu dashboard.';
            }
            if (lower.includes('terima kasih') || lower.includes('thanks') || lower.includes('makasih')) {
                return 'Sama-sama! 😊 Ada yang lain bisa saya bantu?';
            }
            if (lower.includes('partner') || lower.includes('inds')) {
                return 'Kami bekerja sama dengan Inds.id dan Inovas sebagai Official Channel Partners. Mau info lebih lanjut?';
            }
            
            return 'Terima kasih atas pertanyaannya! Tim kami akan menghubungi Anda segera. Atau Anda bisa coba daftar gratis dulu untuk eksplore fitur kami. 🚀';
        }
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
