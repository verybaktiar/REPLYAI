@extends('admin.layouts.app')

@section('title', 'Watchtower - Live Logs')
@section('page_title', 'Watchtower')

@section('content')

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
        <p class="text-slate-400">Real-time system monitoring & health inspection</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <!-- Status Indicator -->
        <div id="live-indicator" class="flex items-center gap-2 px-3 py-1.5 bg-green-500/10 border border-green-500/20 rounded-lg">
            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
            <span class="text-[10px] font-black text-green-500 uppercase tracking-widest">Live Monitoring Active</span>
        </div>

        <div class="h-8 w-px bg-slate-800 hidden lg:block"></div>

        <form action="{{ route('admin.logs.clear') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-red-500/20 text-red-400 hover:bg-red-500/30 rounded-xl font-medium transition border border-red-500/30 text-sm" onclick="return confirm('Hapus semua isi log?')">
                <span class="material-symbols-outlined text-lg">delete_sweep</span>
                Clear
            </button>
        </form>
    </div>
</div>

<!-- Header Stats & Filters -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
    <!-- Stats Cards -->
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-surface-dark border border-slate-800 rounded-2xl p-5">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">File Metadata</div>
            <div class="space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-400">File Name</span>
                    <span class="font-mono text-white">laravel.log</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-400">Size</span>
                    <span id="log-size" class="font-mono text-white">Calculating...</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-400">Last Write</span>
                    <span id="log-modified" class="font-mono text-white text-[10px]">Calculating...</span>
                </div>
            </div>
        </div>

        <div class="bg-surface-dark border border-slate-800 rounded-2xl p-5">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Display Options</div>
            <label class="flex items-center cursor-pointer group">
                <input type="checkbox" id="auto-scroll" checked class="hidden">
                <div class="w-10 h-6 bg-slate-700 rounded-full p-1 transition-colors duration-200 peer-checked:bg-primary relative">
                    <div class="dot w-4 h-4 bg-white rounded-full transition-transform duration-200"></div>
                </div>
                <span class="ml-3 text-sm text-slate-400 group-hover:text-white transition">Auto-scroll</span>
            </label>
        </div>
    </div>

    <!-- Main Terminal & Filters -->
    <div class="lg:col-span-3">
        <div class="bg-surface-dark border border-slate-800 rounded-2xl overflow-hidden flex flex-col h-[70vh] shadow-2xl">
            <!-- Terminal Header -->
            <div class="px-5 py-4 bg-slate-800/40 border-b border-slate-800 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                    </div>
                    <!-- Level Filter -->
                    <div class="flex items-center bg-slate-900/50 rounded-lg p-1 border border-slate-700">
                        <button onclick="filterLogs('all')" class="level-btn active px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest transition">All</button>
                        <button onclick="filterLogs('error')" class="level-btn px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest text-red-400 transition">Error</button>
                        <button onclick="filterLogs('warning')" class="level-btn px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest text-yellow-500 transition">Warning</button>
                    </div>
                </div>

                <div class="relative w-full lg:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-lg">search</span>
                    <input type="text" id="log-search" placeholder="Cari dalam log..." 
                           class="w-full pl-10 pr-4 py-2 bg-slate-900/50 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:border-primary focus:ring-1 focus:ring-primary transition">
                </div>
            </div>

            <!-- Terminal Body -->
            <div id="terminal-container" class="flex-1 overflow-auto bg-[#0a0e17] p-6 font-mono text-xs leading-relaxed space-y-1">
                <div id="log-content-area" class="space-y-1">
                    <!-- Logs will be injected here -->
                    <div class="text-slate-500 italic py-10 text-center">Inisialisasi Watchtower Monitoring...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #terminal-container::-webkit-scrollbar { width: 8px; }
    #terminal-container::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
    #terminal-container::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
    #terminal-container::-webkit-scrollbar-thumb:hover { background: #334155; }
    
    .level-btn.active { background: #135bec; color: white; }
    .dot { transform: translateX(0); }
    input:checked + .w-10 .dot { transform: translateX(16px); }
    
    .log-line { border-left: 2px solid transparent; padding-left: 8px; }
    .log-line.error { border-left-color: #ef4444; background: rgba(239, 68, 68, 0.05); color: #fecaca; }
    .log-line.warning { border-left-color: #f59e0b; background: rgba(245, 158, 11, 0.05); color: #fef3c7; }
</style>

<script>
    let activeFilter = 'all';
    let searchQuery = '';
    const container = document.getElementById('terminal-container');
    const contentArea = document.getElementById('log-content-area');
    const autoScrollToggle = document.getElementById('auto-scroll');

    function filterLogs(level) {
        activeFilter = level;
        document.querySelectorAll('.level-btn').forEach(btn => {
            btn.classList.toggle('active', btn.innerText.toLowerCase() === level);
        });
        updateDisplay();
    }

    document.getElementById('log-search').addEventListener('input', (e) => {
        searchQuery = e.target.value.toLowerCase();
        updateDisplay();
    });

    let currentLogs = [];

    async function fetchLogs() {
        try {
            const response = await fetch('{{ route('admin.logs.index') }}?json=1');
            const data = await response.json();
            
            // Check if data changed
            if (JSON.stringify(data.logs) !== JSON.stringify(currentLogs)) {
                currentLogs = data.logs;
                updateDisplay();
                
                // Update Metadata
                document.getElementById('log-size').innerText = data.metadata.size;
                document.getElementById('log-modified').innerText = data.metadata.last_modified;
            }
        } catch (error) {
            console.error('Failed to fetch logs:', error);
            document.getElementById('live-indicator').classList.replace('bg-green-500/10', 'bg-red-500/10');
            document.getElementById('live-indicator').querySelector('.bg-green-500').classList.replace('bg-green-500', 'bg-red-500');
            document.getElementById('live-indicator').querySelector('span').innerText = 'Connection Lost';
        }
    }

    function updateDisplay() {
        const filtered = currentLogs.filter(log => {
            const matchesLevel = activeFilter === 'all' || log.level === activeFilter;
            const matchesSearch = !searchQuery || log.content.toLowerCase().includes(searchQuery);
            return matchesLevel && matchesSearch;
        });

        contentArea.innerHTML = filtered.map(log => `
            <div class="log-line ${log.level} py-1 hover:bg-slate-800/20 group">
                <pre class="whitespace-pre-wrap break-words">${escapeHtml(log.content)}</pre>
            </div>
        `).join('') || '<div class="text-slate-500 italic py-10 text-center">Tidak ada log yang sesuai dengan filter.</div>';

        if (autoScrollToggle.checked) {
            container.scrollTop = container.scrollHeight;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initial fetch
    fetchLogs();
    
    // Polling every 3 seconds
    setInterval(fetchLogs, 3000);
</script>

@endsection
