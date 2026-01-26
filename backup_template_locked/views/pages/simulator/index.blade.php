<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bot Simulator - REPLYAI</title>
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
        .typing-indicator span {
            animation: blink 1.4s infinite both;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink {
            0%, 60%, 100% { opacity: 0.3; }
            30% { opacity: 1; }
        }
    </style>
</head>
<body class="bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row">

<!-- Sidebar (Full) -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
    <!-- Header -->
    <header class="hidden lg:flex h-16 border-b border-white/5 items-center justify-between px-6 bg-[#111722] shrink-0">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-3xl">science</span>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-white">Test Bot</h2>
                    @include('components.page-help', [
                        'title' => 'Test Bot (Simulator)',
                        'description' => 'Tempat mencoba respons bot sebelum digunakan oleh pelanggan.',
                        'tips' => [
                            'Ketik pertanyaan seperti yang biasa ditanyakan pelanggan',
                            'Lihat dari mana sumber jawaban bot (MENU, AI, atau FALLBACK)',
                            'Gunakan untuk testing sebelum bot aktif',
                            'Klik "Clear Chat" untuk reset percakapan'
                        ]
                    ])
                </div>
                <p class="text-xs text-text-secondary">Test respons bot tanpa perlu WhatsApp/Instagram</p>
            </div>
        </div>
        <button onclick="clearChat()" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm bg-white/5 text-text-secondary hover:bg-white/10 hover:text-white transition-colors">
            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
            Clear Chat
        </button>
    </header>

    <!-- Chat Area -->
    <div id="chat-area" class="flex-1 overflow-y-auto p-6 flex flex-col gap-4 bg-background-dark bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-[#1e2634] to-background-dark">
        
        <!-- Welcome Message -->
        <div class="flex gap-3 items-start">
            <div class="size-9 rounded-full bg-gradient-to-br from-primary to-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-white" style="font-size: 20px;">smart_toy</span>
            </div>
            <div class="max-w-[70%]">
                <div class="bg-surface-dark border border-border-dark rounded-2xl rounded-tl-none px-4 py-3">
                    <p class="text-sm text-white">👋 Halo! Ini adalah Bot Simulator. Ketik pesan apapun untuk menguji respons bot.</p>
                </div>
                <p class="text-xs text-text-secondary mt-1">System</p>
            </div>
        </div>

    </div>

    <!-- Input Area -->
    <div class="border-t border-white/5 bg-[#111722] p-4">
        <form id="chat-form" class="flex gap-3">
            <input 
                type="text" 
                id="message-input"
                placeholder="Ketik pesan untuk di-test..."
                class="flex-1 bg-surface-dark border border-border-dark text-white text-sm rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-primary placeholder-text-secondary"
                autocomplete="off"
            >
            <button type="submit" id="send-btn" class="bg-primary hover:bg-blue-600 text-white font-medium rounded-xl px-6 py-3 flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined" style="font-size: 20px;">send</span>
                Kirim
            </button>
        </form>
    </div>
</main>

<script>
const chatArea = document.getElementById('chat-area');
const chatForm = document.getElementById('chat-form');
const messageInput = document.getElementById('message-input');
const sendBtn = document.getElementById('send-btn');

function addMessage(text, isUser, meta = null) {
    const wrapper = document.createElement('div');
    wrapper.className = `flex gap-3 items-start ${isUser ? 'flex-row-reverse' : ''}`;
    
    const avatar = isUser ? `
        <div class="size-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-white" style="font-size: 20px;">person</span>
        </div>
    ` : `
        <div class="size-9 rounded-full bg-gradient-to-br from-primary to-blue-400 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-white" style="font-size: 20px;">smart_toy</span>
        </div>
    `;

    const bubbleClass = isUser 
        ? 'bg-primary text-white rounded-2xl rounded-tr-none' 
        : 'bg-surface-dark border border-border-dark text-white rounded-2xl rounded-tl-none';
    
    let metaBadge = '';
    if (meta && meta.source) {
        const sourceColors = {
            'menu': 'bg-green-500/20 text-green-400',
            'manual': 'bg-blue-500/20 text-blue-400',
            'ai': 'bg-purple-500/20 text-purple-400',
            'fallback': 'bg-orange-500/20 text-orange-400',
            'no_response': 'bg-red-500/20 text-red-400',
        };
        const colorClass = sourceColors[meta.source] || 'bg-gray-500/20 text-gray-400';
        metaBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium ${colorClass}">${meta.source.toUpperCase()}</span>`;
        if (meta.ai_used && meta.ai_confidence) {
            metaBadge += ` <span class="text-[10px] text-text-secondary">confidence: ${(meta.ai_confidence * 100).toFixed(0)}%</span>`;
        }
    }

    wrapper.innerHTML = `
        ${avatar}
        <div class="max-w-[70%]">
            <div class="${bubbleClass} px-4 py-3">
                <p class="text-sm whitespace-pre-wrap">${escapeHtml(text)}</p>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <p class="text-xs text-text-secondary">${isUser ? 'You' : 'Bot'}</p>
                ${metaBadge}
            </div>
        </div>
    `;
    
    chatArea.appendChild(wrapper);
    chatArea.scrollTop = chatArea.scrollHeight;
}

function addTypingIndicator() {
    const wrapper = document.createElement('div');
    wrapper.id = 'typing-indicator';
    wrapper.className = 'flex gap-3 items-start';
    wrapper.innerHTML = `
        <div class="size-9 rounded-full bg-gradient-to-br from-primary to-blue-400 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-white" style="font-size: 20px;">smart_toy</span>
        </div>
        <div class="bg-surface-dark border border-border-dark rounded-2xl rounded-tl-none px-4 py-3">
            <div class="typing-indicator flex gap-1">
                <span class="size-2 bg-text-secondary rounded-full"></span>
                <span class="size-2 bg-text-secondary rounded-full"></span>
                <span class="size-2 bg-text-secondary rounded-full"></span>
            </div>
        </div>
    `;
    chatArea.appendChild(wrapper);
    chatArea.scrollTop = chatArea.scrollHeight;
}

function removeTypingIndicator() {
    document.getElementById('typing-indicator')?.remove();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function clearChat() {
    chatArea.innerHTML = `
        <div class="flex gap-3 items-start">
            <div class="size-9 rounded-full bg-gradient-to-br from-primary to-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-white" style="font-size: 20px;">smart_toy</span>
            </div>
            <div class="max-w-[70%]">
                <div class="bg-surface-dark border border-border-dark rounded-2xl rounded-tl-none px-4 py-3">
                    <p class="text-sm text-white">👋 Chat cleared. Ketik pesan untuk memulai testing baru.</p>
                </div>
                <p class="text-xs text-text-secondary mt-1">System</p>
            </div>
        </div>
    `;
}

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const message = messageInput.value.trim();
    if (!message) return;
    
    // Add user message
    addMessage(message, true);
    messageInput.value = '';
    
    // Disable input
    messageInput.disabled = true;
    sendBtn.disabled = true;
    
    // Show typing
    addTypingIndicator();
    
    try {
        const response = await fetch('{{ route("simulator.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ message }),
        });
        
        const data = await response.json();
        
        removeTypingIndicator();
        
        if (data.success) {
            addMessage(data.response, false, {
                source: data.source,
                ai_used: data.ai_used,
                ai_confidence: data.ai_confidence,
            });
        } else {
            addMessage('❌ Error: ' + (data.error || 'Unknown error'), false);
        }
    } catch (err) {
        removeTypingIndicator();
        addMessage('❌ Network error: ' + err.message, false);
    } finally {
        // Re-enable input
        messageInput.disabled = false;
        sendBtn.disabled = false;
        messageInput.focus();
    }
});
</script>

</body>
</html>
