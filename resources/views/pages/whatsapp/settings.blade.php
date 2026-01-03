<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Settings - REPLYAI</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
        .pulse-green {
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(37, 211, 102, 0); }
        }
    </style>
</head>
<body class="bg-background-dark font-display text-white overflow-hidden h-screen flex">

<!-- Sidebar -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden relative">
    <!-- Header -->
    <header class="h-16 border-b border-white/5 flex items-center justify-between px-6 bg-[#111722] shrink-0">
        <div class="flex items-center gap-3">
            <svg class="w-8 h-8 text-whatsapp" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <div>
                <h2 class="text-lg font-bold text-white">WhatsApp Integration</h2>
                <p class="text-xs text-text-secondary">Kelola koneksi dan pengaturan WhatsApp Bot</p>
            </div>
        </div>
        <div id="status-badge" class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium">
            <!-- Status will be injected here -->
        </div>
    </header>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6 bg-background-dark bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-[#1e2634] to-background-dark">
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Connection Card -->
            <div class="bg-surface-dark border border-border-dark rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-whatsapp">link</span>
                    Koneksi WhatsApp
                </h3>
                
                <!-- QR Code Area -->
                <div id="qr-area" class="mb-6">
                    <div id="qr-placeholder" class="w-full aspect-square max-w-[280px] mx-auto bg-white/5 rounded-xl border-2 border-dashed border-border-dark flex flex-col items-center justify-center gap-4">
                        <span class="material-symbols-outlined text-6xl text-text-secondary">qr_code_2</span>
                        <p class="text-sm text-text-secondary text-center px-4">Klik "Connect" untuk menampilkan QR Code</p>
                    </div>
                    <div id="qr-image" class="hidden w-full max-w-[280px] mx-auto">
                        <img id="qr-img" src="" alt="QR Code" class="w-full rounded-xl bg-white p-2">
                        <p class="text-sm text-text-secondary text-center mt-2">Scan dengan WhatsApp di HP Anda</p>
                    </div>
                    <div id="connected-info" class="hidden text-center">
                        <div class="w-24 h-24 mx-auto bg-whatsapp/20 rounded-full flex items-center justify-center mb-4 pulse-green">
                            <span class="material-symbols-outlined text-whatsapp text-5xl">check_circle</span>
                        </div>
                        <p class="text-xl font-bold text-white" id="phone-display">-</p>
                        <p class="text-sm text-text-secondary" id="name-display">-</p>
                        <p class="text-xs text-whatsapp mt-2">● Terhubung</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button id="connect-btn" onclick="connectWhatsApp()" class="flex-1 bg-whatsapp hover:bg-green-600 text-white font-medium rounded-xl px-4 py-3 flex items-center justify-center gap-2 transition-colors">
                        <span class="material-symbols-outlined">power</span>
                        Connect
                    </button>
                    <button id="disconnect-btn" onclick="disconnectWhatsApp()" class="flex-1 bg-red-500/20 hover:bg-red-500/30 text-red-400 font-medium rounded-xl px-4 py-3 flex items-center justify-center gap-2 transition-colors hidden">
                        <span class="material-symbols-outlined">power_off</span>
                        Disconnect
                    </button>
                    <button id="refresh-btn" onclick="refreshStatus()" class="bg-white/5 hover:bg-white/10 text-text-secondary hover:text-white rounded-xl px-4 py-3 transition-colors">
                        <span class="material-symbols-outlined">refresh</span>
                    </button>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="bg-surface-dark border border-border-dark rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">settings</span>
                    Pengaturan Bot
                </h3>
                
                <!-- Auto Reply Toggle -->
                <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl mb-4">
                    <div>
                        <p class="font-medium text-white">Auto Reply</p>
                        <p class="text-sm text-text-secondary">Bot akan membalas pesan secara otomatis</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="auto-reply-toggle" class="sr-only peer" {{ $session->auto_reply_enabled ? 'checked' : '' }} onchange="toggleAutoReply(this.checked)">
                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-whatsapp"></div>
                    </label>
                </div>

                <!-- Session Info -->
                <div class="space-y-3">
                    <div class="flex justify-between p-3 bg-white/5 rounded-lg">
                        <span class="text-text-secondary">Status</span>
                        <span id="session-status" class="font-medium">{{ ucfirst($session->status) }}</span>
                    </div>
                    <div class="flex justify-between p-3 bg-white/5 rounded-lg">
                        <span class="text-text-secondary">Nomor HP</span>
                        <span id="session-phone" class="font-medium">{{ $session->phone_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between p-3 bg-white/5 rounded-lg">
                        <span class="text-text-secondary">Nama WA</span>
                        <span id="session-name" class="font-medium">{{ $session->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between p-3 bg-white/5 rounded-lg">
                        <span class="text-text-secondary">Terakhir Online</span>
                        <span class="font-medium">{{ $session->last_connected_at ? $session->last_connected_at->diffForHumans() : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Test Message Card -->
            <div class="bg-surface-dark border border-border-dark rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-400">send</span>
                    Kirim Pesan Test
                </h3>
                
                <form id="send-form" onsubmit="sendTestMessage(event)" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Nomor Tujuan</label>
                        <input type="text" id="test-phone" placeholder="08123456789 atau 628123456789" 
                            class="w-full bg-white/5 border border-border-dark text-white text-sm rounded-xl px-4 py-3 focus:ring-2 focus:ring-whatsapp focus:border-whatsapp placeholder-text-secondary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-2">Pesan</label>
                        <textarea id="test-message" rows="3" placeholder="Ketik pesan test..."
                            class="w-full bg-white/5 border border-border-dark text-white text-sm rounded-xl px-4 py-3 focus:ring-2 focus:ring-whatsapp focus:border-whatsapp placeholder-text-secondary resize-none"></textarea>
                    </div>
                    <button type="submit" id="send-test-btn" class="w-full bg-primary hover:bg-blue-600 text-white font-medium rounded-xl px-4 py-3 flex items-center justify-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined">send</span>
                        Kirim Test Message
                    </button>
                </form>
                <p id="send-result" class="mt-3 text-sm text-center hidden"></p>
            </div>

            <!-- Recent Messages Card -->
            <div class="bg-surface-dark border border-border-dark rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-400">history</span>
                    Pesan Terakhir
                </h3>
                
                <div class="space-y-3 max-h-[300px] overflow-y-auto">
                    @forelse($recentMessages as $msg)
                    <div class="p-3 bg-white/5 rounded-xl {{ $msg->direction === 'incoming' ? 'border-l-4 border-whatsapp' : 'border-l-4 border-primary' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium {{ $msg->direction === 'incoming' ? 'text-whatsapp' : 'text-primary' }}">
                                {{ $msg->direction === 'incoming' ? '← Masuk' : '→ Keluar' }}
                            </span>
                            <span class="text-xs text-text-secondary">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-text-secondary mb-1">{{ $msg->push_name ?? $msg->phone_number }}</p>
                        <p class="text-sm text-white truncate">{{ $msg->message }}</p>
                    </div>
                    @empty
                    <div class="text-center py-8">
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Takeover Settings Card -->
            <div class="bg-surface-dark border border-border-dark rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-yellow-400">timer</span>
                    Pengaturan Takeover Otomatis
                </h3>
                
                <p class="text-sm text-text-secondary mb-4">
                    Bot akan takeover kembali jika CS tidak membalas selama:
                </p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4" id="timeout-options">
                    <button onclick="setTakeoverTimeout(60)" 
                            class="timeout-btn p-3 rounded-xl text-sm font-medium border transition-colors {{ ($session->takeover_timeout_minutes ?? 60) == 60 ? 'bg-yellow-500/20 border-yellow-500/50 text-yellow-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                            data-value="60">
                        1 jam
                    </button>
                    <button onclick="setTakeoverTimeout(120)" 
                            class="timeout-btn p-3 rounded-xl text-sm font-medium border transition-colors {{ ($session->takeover_timeout_minutes ?? 60) == 120 ? 'bg-yellow-500/20 border-yellow-500/50 text-yellow-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                            data-value="120">
                        2 jam
                    </button>
                    <button onclick="setTakeoverTimeout(240)" 
                            class="timeout-btn p-3 rounded-xl text-sm font-medium border transition-colors {{ ($session->takeover_timeout_minutes ?? 60) == 240 ? 'bg-yellow-500/20 border-yellow-500/50 text-yellow-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                            data-value="240">
                        4 jam
                    </button>
                    <button onclick="toggleCustomTimeout()" 
                            class="timeout-btn p-3 rounded-xl text-sm font-medium border bg-white/5 border-border-dark text-text-secondary hover:bg-white/10 transition-colors"
                            data-value="custom">
                        Custom
                    </button>
                </div>
                
                <!-- Custom Input -->
                <div id="custom-timeout-area" class="hidden mt-4 flex gap-3">
                    <input type="number" id="custom-timeout-value" min="15" max="1440" placeholder="Menit (15-1440)"
                           class="flex-1 bg-white/5 border border-border-dark text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    <button onclick="saveCustomTimeout()" 
                            class="bg-yellow-500 hover:bg-yellow-600 text-black font-medium px-6 py-3 rounded-xl transition-colors">
                        Simpan
                    </button>
                </div>
                
                <p id="timeout-result" class="text-xs text-green-400 mt-3 hidden"></p>
                
                <div class="mt-4 pt-4 border-t border-border-dark">
                    <a href="{{ route('takeover.logs') }}" class="flex items-center gap-2 text-sm text-primary hover:text-blue-400 transition-colors">
                        <span class="material-symbols-outlined text-base">history</span>
                        Lihat Activity Logs
                    </a>
                </div>
            </div>

            <!-- Session Timeout Settings Card -->
            <div class="bg-surface-dark border border-border-dark rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-cyan-400">schedule</span>
                    Pengaturan Session Timeout
                </h3>
                
                <p class="text-sm text-text-secondary mb-4">
                    Bot akan kirim follow-up jika user tidak membalas:
                </p>
                
                <!-- Idle Timeout -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-text-secondary mb-2">Waktu Idle (menit)</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button onclick="setSessionTimeout('idle', 15)" 
                                class="idle-timeout-btn p-2 rounded-lg text-sm border transition-colors {{ ($session->session_idle_timeout_minutes ?? 30) == 15 ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                                data-value="15">15</button>
                        <button onclick="setSessionTimeout('idle', 30)" 
                                class="idle-timeout-btn p-2 rounded-lg text-sm border transition-colors {{ ($session->session_idle_timeout_minutes ?? 30) == 30 ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                                data-value="30">30</button>
                        <button onclick="setSessionTimeout('idle', 60)" 
                                class="idle-timeout-btn p-2 rounded-lg text-sm border transition-colors {{ ($session->session_idle_timeout_minutes ?? 30) == 60 ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                                data-value="60">60</button>
                        <button onclick="setSessionTimeout('idle', 120)" 
                                class="idle-timeout-btn p-2 rounded-lg text-sm border transition-colors {{ ($session->session_idle_timeout_minutes ?? 30) == 120 ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                                data-value="120">120</button>
                    </div>
                </div>

                <!-- Follow-up Timeout -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-text-secondary mb-2">Tutup Session Setelah Follow-up (menit)</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button onclick="setSessionTimeout('followup', 10)" 
                                class="followup-timeout-btn p-2 rounded-lg text-sm border transition-colors {{ ($session->session_followup_timeout_minutes ?? 15) == 10 ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                                data-value="10">10</button>
                        <button onclick="setSessionTimeout('followup', 15)" 
                                class="followup-timeout-btn p-2 rounded-lg text-sm border transition-colors {{ ($session->session_followup_timeout_minutes ?? 15) == 15 ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                                data-value="15">15</button>
                        <button onclick="setSessionTimeout('followup', 30)" 
                                class="followup-timeout-btn p-2 rounded-lg text-sm border transition-colors {{ ($session->session_followup_timeout_minutes ?? 15) == 30 ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                                data-value="30">30</button>
                        <button onclick="setSessionTimeout('followup', 60)" 
                                class="followup-timeout-btn p-2 rounded-lg text-sm border transition-colors {{ ($session->session_followup_timeout_minutes ?? 15) == 60 ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-border-dark text-text-secondary hover:bg-white/10' }}"
                                data-value="60">60</button>
                    </div>
                </div>
                
                <p id="session-timeout-result" class="text-xs text-green-400 mt-3 hidden"></p>
                
                <div class="mt-4 p-3 bg-cyan-500/10 border border-cyan-500/30 rounded-lg">
                    <p class="text-xs text-cyan-400">
                        💡 Jika user idle <strong>{{ $session->session_idle_timeout_minutes ?? 30 }} menit</strong>, 
                        bot kirim follow-up. Jika tidak dibalas <strong>{{ $session->session_followup_timeout_minutes ?? 15 }} menit</strong>, 
                        session ditutup otomatis.
                    </p>
                </div>
            </div>
        </div>

        <!-- Service Info -->
        <div class="max-w-6xl mx-auto mt-6">
            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-yellow-400 shrink-0">info</span>
                <div>
                    <p class="text-sm font-medium text-yellow-400">Perhatian</p>
                    <p class="text-sm text-yellow-400/80 mt-1">
                        Pastikan Node.js WhatsApp Service berjalan di port 3001 sebelum mencoba connect.
                        Jalankan <code class="bg-black/30 px-1 rounded">npm start</code> di folder <code class="bg-black/30 px-1 rounded">wa-service</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Update status badge
function updateStatusBadge(status) {
    const badge = document.getElementById('status-badge');
    const statusMap = {
        'connected': { bg: 'bg-whatsapp/20', text: 'text-whatsapp', icon: 'wifi', label: 'Terhubung' },
        'waiting_qr': { bg: 'bg-yellow-500/20', text: 'text-yellow-400', icon: 'qr_code_2', label: 'Menunggu Scan' },
        'connecting': { bg: 'bg-blue-500/20', text: 'text-blue-400', icon: 'sync', label: 'Menghubungkan...' },
        'disconnected': { bg: 'bg-red-500/20', text: 'text-red-400', icon: 'wifi_off', label: 'Tidak Terhubung' },
        'offline': { bg: 'bg-gray-500/20', text: 'text-gray-400', icon: 'cloud_off', label: 'Service Offline' },
    };
    const s = statusMap[status] || statusMap['disconnected'];
    badge.className = `flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium ${s.bg} ${s.text}`;
    badge.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px">${s.icon}</span>${s.label}`;
}

// Update UI based on status
function updateUI(data) {
    const status = data.status || 'disconnected';
    updateStatusBadge(status);
    
    const qrPlaceholder = document.getElementById('qr-placeholder');
    const qrImage = document.getElementById('qr-image');
    const connectedInfo = document.getElementById('connected-info');
    const connectBtn = document.getElementById('connect-btn');
    const disconnectBtn = document.getElementById('disconnect-btn');
    
    // Hide all first
    qrPlaceholder.classList.add('hidden');
    qrImage.classList.add('hidden');
    connectedInfo.classList.add('hidden');
    
    if (status === 'connected') {
        connectedInfo.classList.remove('hidden');
        document.getElementById('phone-display').textContent = '+' + (data.phoneNumber || '-');
        document.getElementById('name-display').textContent = data.name || 'WhatsApp User';
        document.getElementById('session-status').textContent = 'Connected';
        document.getElementById('session-phone').textContent = data.phoneNumber || '-';
        document.getElementById('session-name').textContent = data.name || '-';
        connectBtn.classList.add('hidden');
        disconnectBtn.classList.remove('hidden');
    } else if (status === 'waiting_qr') {
        // Fetch QR code
        fetchQR();
        connectBtn.classList.add('hidden');
        disconnectBtn.classList.remove('hidden');
    } else {
        qrPlaceholder.classList.remove('hidden');
        document.getElementById('session-status').textContent = status === 'offline' ? 'Service Offline' : 'Disconnected';
        connectBtn.classList.remove('hidden');
        disconnectBtn.classList.add('hidden');
    }
}

// Fetch QR code
async function fetchQR() {
    try {
        const res = await fetch('{{ route("whatsapp.qr") }}');
        const data = await res.json();
        if (data.success && data.qr) {
            document.getElementById('qr-placeholder').classList.add('hidden');
            document.getElementById('qr-image').classList.remove('hidden');
            document.getElementById('qr-img').src = data.qr;
        }
    } catch (e) {
        console.error('Failed to fetch QR:', e);
    }
}

// Refresh status
async function refreshStatus() {
    try {
        const res = await fetch('{{ route("whatsapp.status") }}');
        const data = await res.json();
        updateUI(data);
    } catch (e) {
        updateUI({ status: 'offline' });
    }
}

// Connect WhatsApp
async function connectWhatsApp() {
    const btn = document.getElementById('connect-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span>Connecting...';
    
    try {
        const res = await fetch('{{ route("whatsapp.connect") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        
        // Start polling for QR/status
        setTimeout(refreshStatus, 1000);
        setTimeout(refreshStatus, 3000);
        setTimeout(refreshStatus, 5000);
    } catch (e) {
        alert('Failed to connect: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined">power</span>Connect';
    }
}

// Disconnect WhatsApp
async function disconnectWhatsApp() {
    if (!confirm('Yakin ingin disconnect? Anda perlu scan QR ulang untuk connect lagi.')) return;
    
    const btn = document.getElementById('disconnect-btn');
    btn.disabled = true;
    
    try {
        const res = await fetch('{{ route("whatsapp.disconnect") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        refreshStatus();
    } catch (e) {
        alert('Failed to disconnect: ' + e.message);
    } finally {
        btn.disabled = false;
    }
}

// Toggle auto reply
async function toggleAutoReply(enabled) {
    try {
        await fetch('{{ route("whatsapp.toggle-auto-reply") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ enabled })
        });
    } catch (e) {
        alert('Failed to update: ' + e.message);
    }
}

// Send test message
async function sendTestMessage(e) {
    e.preventDefault();
    
    const phone = document.getElementById('test-phone').value.trim();
    const message = document.getElementById('test-message').value.trim();
    const resultEl = document.getElementById('send-result');
    const btn = document.getElementById('send-test-btn');
    
    if (!phone || !message) {
        resultEl.textContent = '❌ Nomor dan pesan harus diisi';
        resultEl.className = 'mt-3 text-sm text-center text-red-400';
        resultEl.classList.remove('hidden');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span>Mengirim...';
    
    try {
        const res = await fetch('{{ route("whatsapp.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ phone, message })
        });
        const data = await res.json();
        
        if (data.success) {
            resultEl.textContent = '✅ Pesan berhasil dikirim!';
            resultEl.className = 'mt-3 text-sm text-center text-whatsapp';
            document.getElementById('test-message').value = '';
        } else {
            resultEl.textContent = '❌ ' + (data.error || 'Gagal mengirim');
            resultEl.className = 'mt-3 text-sm text-center text-red-400';
        }
        resultEl.classList.remove('hidden');
    } catch (e) {
        resultEl.textContent = '❌ Network error: ' + e.message;
        resultEl.className = 'mt-3 text-sm text-center text-red-400';
        resultEl.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined">send</span>Kirim Test Message';
    }
}

// Initial load
refreshStatus();

// Poll status every 5 seconds when waiting for QR
setInterval(() => {
    const status = document.getElementById('session-status').textContent.toLowerCase();
    if (status.includes('waiting') || status.includes('connecting')) {
        refreshStatus();
    }
}, 5000);

// Takeover Timeout Functions
function setTakeoverTimeout(minutes) {
    saveTakeoverTimeout(minutes);
}

function toggleCustomTimeout() {
    const area = document.getElementById('custom-timeout-area');
    area.classList.toggle('hidden');
}

function saveCustomTimeout() {
    const input = document.getElementById('custom-timeout-value');
    const minutes = parseInt(input.value);
    
    if (isNaN(minutes) || minutes < 15 || minutes > 1440) {
        alert('Masukkan nilai antara 15-1440 menit');
        return;
    }
    
    saveTakeoverTimeout(minutes);
    document.getElementById('custom-timeout-area').classList.add('hidden');
}

async function saveTakeoverTimeout(minutes) {
    const resultEl = document.getElementById('timeout-result');
    
    try {
        const res = await fetch('{{ route("takeover.settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ 
                takeover_timeout_minutes: minutes,
                idle_warning_minutes: Math.floor(minutes * 0.5) // 50% of timeout as warning
            })
        });
        
        const data = await res.json();
        
        if (data.success) {
            // Update button styles
            document.querySelectorAll('.timeout-btn').forEach(btn => {
                const val = btn.dataset.value;
                if (val == minutes) {
                    btn.className = 'timeout-btn p-3 rounded-xl text-sm font-medium border transition-colors bg-yellow-500/20 border-yellow-500/50 text-yellow-400';
                } else if (val !== 'custom') {
                    btn.className = 'timeout-btn p-3 rounded-xl text-sm font-medium border transition-colors bg-white/5 border-border-dark text-text-secondary hover:bg-white/10';
                }
            });
            
            resultEl.textContent = `✅ Timeout diset ke ${minutes} menit`;
            resultEl.className = 'text-xs text-green-400 mt-3';
            resultEl.classList.remove('hidden');
            
            setTimeout(() => resultEl.classList.add('hidden'), 3000);
        } else {
            resultEl.textContent = '❌ ' + (data.error || 'Gagal menyimpan');
            resultEl.className = 'text-xs text-red-400 mt-3';
            resultEl.classList.remove('hidden');
        }
    } catch (e) {
        resultEl.textContent = '❌ Network error';
        resultEl.className = 'text-xs text-red-400 mt-3';
        resultEl.classList.remove('hidden');
    }
}

// Session Timeout Functions
async function setSessionTimeout(type, minutes) {
    const resultEl = document.getElementById('session-timeout-result');
    const field = type === 'idle' ? 'session_idle_timeout_minutes' : 'session_followup_timeout_minutes';
    const btnClass = type === 'idle' ? 'idle-timeout-btn' : 'followup-timeout-btn';
    
    try {
        const res = await fetch('{{ route("takeover.settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ [field]: minutes })
        });
        
        const data = await res.json();
        
        if (data.success) {
            // Update button styles
            document.querySelectorAll('.' + btnClass).forEach(btn => {
                const val = btn.dataset.value;
                if (val == minutes) {
                    btn.className = btnClass + ' p-2 rounded-lg text-sm border transition-colors bg-cyan-500/20 border-cyan-500/50 text-cyan-400';
                } else {
                    btn.className = btnClass + ' p-2 rounded-lg text-sm border transition-colors bg-white/5 border-border-dark text-text-secondary hover:bg-white/10';
                }
            });
            
            resultEl.textContent = `✅ ${type === 'idle' ? 'Idle' : 'Follow-up'} timeout diset ke ${minutes} menit`;
            resultEl.classList.remove('hidden');
            setTimeout(() => resultEl.classList.add('hidden'), 3000);
        } else {
            resultEl.textContent = '❌ Gagal menyimpan';
            resultEl.classList.remove('hidden');
        }
    } catch (e) {
        resultEl.textContent = '❌ Network error';
        resultEl.classList.remove('hidden');
    }
}
</script>

</body>
</html>
