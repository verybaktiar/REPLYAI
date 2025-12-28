<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Knowledge Base - REPLYAI</title>
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
                        "brand": { 500: "#135bec", 600: "#0f4bc2" }, // Alias for KB script
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
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111722; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #324467; border-radius: 10px; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex">

<!-- Sidebar Navigation -->
<aside class="hidden lg:flex flex-col w-72 h-full bg-[#111722] border-r border-[#232f48] shrink-0 fixed lg:static top-0 bottom-0 left-0 z-40">
    <!-- Brand -->
    <div class="flex items-center gap-3 px-6 py-6 mb-2">
        <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 shadow-lg relative" style='background-image: url("https://ui-avatars.com/api/?name=Reply+AI&background=0D8ABC&color=fff");'></div>
        <div>
            <h1 class="text-base font-bold leading-none text-white">ReplyAI Admin</h1>
            <p class="text-xs text-[#92a4c9] mt-1">RS PKU Solo Bot</p>
        </div>
    </div>
    <!-- Navigation Links -->
    <nav class="flex flex-col gap-1 flex-1 overflow-y-auto px-4">
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('dashboard') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('dashboard') }}">
            <span class="material-symbols-outlined text-[24px]">grid_view</span>
            <span class="text-sm font-medium">Dashboard</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('analytics*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('analytics.index') }}">
            <span class="material-symbols-outlined text-[24px]">pie_chart</span>
            <span class="text-sm font-medium">Analisis & Laporan</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('contacts*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('contacts.index') }}">
            <span class="material-symbols-outlined text-[24px]">groups</span>
            <span class="text-sm font-medium">Data Kontak (CRM)</span>
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('inbox*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('inbox') }}">
            <span class="material-symbols-outlined text-[24px]">chat_bubble</span>
            <span class="text-sm font-medium">Kotak Masuk</span>
            @if(isset($conversations) && $conversations instanceof \Illuminate\Database\Eloquent\Collection && $conversations->count() > 0)
                <span class="ml-auto bg-white/10 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md text-center min-w-[20px]">{{ $conversations->count() }}</span>
            @elseif(isset($stats['pending_inbox']) && $stats['pending_inbox'] > 0)
                 <span class="ml-auto bg-white/10 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md text-center min-w-[20px]">{{ $stats['pending_inbox'] }}</span>
            @endif
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('rules*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('rules.index') }}">
            <span class="material-symbols-outlined text-[24px]">smart_toy</span>
            <span class="text-sm font-medium">Manajemen Bot</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('kb*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('kb.index') }}">
            <span class="material-symbols-outlined text-[24px]">menu_book</span>
            <span class="text-sm font-medium">Knowledge Base</span>
        </a>

        <!-- New Links -->
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('simulator*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('simulator.index') }}">
            <span class="material-symbols-outlined text-[24px]">science</span>
            <span class="text-sm font-medium">Simulator</span>
        </a>
        
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('settings*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('settings.index') }}">
            <span class="material-symbols-outlined text-[24px]">settings</span>
            <span class="text-sm font-medium">Settings (Hours)</span>
        </a>

        <div class="mt-4 mb-2 px-3">
            <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider">System</p>
        </div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('logs*') ? 'bg-[#135bec] text-white shadow-lg shadow-blue-900/20' : 'text-[#92a4c9] hover:text-white hover:bg-[#232f48]' }}" href="{{ route('logs.index') }}">
            <span class="material-symbols-outlined text-[24px]">history</span>
            <span class="text-sm font-medium">Log Aktivitas</span>
        </a>
    </nav>
    <!-- User Profile (Bottom) -->
    <div class="border-t border-[#232f48] p-4">
            <div class="p-3 rounded-lg bg-[#232f48]/50 flex items-center gap-3">
            <div class="size-8 rounded-full bg-gradient-to-tr from-purple-500 to-primary flex items-center justify-center text-xs font-bold text-white">DM</div>
            <div class="flex flex-col overflow-hidden">
                <p class="text-white text-sm font-medium truncate">Admin</p>
                <p class="text-[#92a4c9] text-xs truncate">admin@rspkusolo.com</p>
            </div>
            <button class="ml-auto text-[#92a4c9] hover:text-white">
                <span class="material-symbols-outlined text-[20px]">logout</span>
            </button>
        </div>
    </div>
</aside>

<main class="flex-1 flex flex-col h-full overflow-hidden relative">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6 lg:p-10 pb-20">
        <div class="max-w-[1200px] mx-auto flex flex-col gap-6">
            
            <!-- Header KB -->
           <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <h1 class="text-3xl font-black text-white">Knowledge Base</h1>
              <p class="text-text-secondary mt-1">Sumber jawaban AI fallback. Input manual atau import URL.</p>
            </div>
             <!-- Tombol Test AI -->
             <button
                type="button"
                data-modal-open="ai-test-modal"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#135bec] text-white text-sm font-bold shadow-lg shadow-blue-900/20 hover:bg-blue-600 transition"
              >
                <span class="material-symbols-outlined text-[20px]">smart_toy</span> Test AI
              </button>
          </div>

          <!-- Import URL Box -->
          <div class="rounded-xl border border-border-dark bg-surface-dark p-6 space-y-4">
            <h3 class="font-bold text-white text-lg">Import dari URL</h3>

            <div class="grid grid-cols-12 gap-3">
              <div class="col-span-12 sm:col-span-7">
                <input id="kb-url" type="text" placeholder="https://web-resmi.com/jadwal"
                  class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
              </div>
              <div class="col-span-12 sm:col-span-3">
                <input id="kb-title" type="text" placeholder="Judul (opsional)"
                  class="w-full rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
              </div>
              <div class="col-span-12 sm:col-span-2">
                <button id="btn-import-url"
                  class="w-full px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition">
                  Import
                </button>
              </div>
            </div>
            <p class="text-xs text-text-secondary">Gunakan URL resmi untuk akurasi data AI.</p>
          </div>

          <!-- List KB -->
          <div class="rounded-xl border border-border-dark bg-surface-dark overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between bg-[#1f2b40]/50">
              <p class="font-bold text-white">Daftar Sumber Pengetahuan</p>
              <div class="text-xs text-text-secondary font-bold bg-[#111722] px-2 py-1 rounded">Total: {{ $articles->count() }}</div>
            </div>

            <div class="divide-y divide-border-dark">
              @forelse($articles as $a)
                <div id="kb-{{ $a->id }}" class="p-6 hover:bg-[#1f2b40] transition-colors group">
                  <div class="flex flex-col gap-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                          <h4 class="font-bold text-white text-lg group-hover:text-primary transition-colors">
                            {{ $a->title ?? 'Untitled' }}
                          </h4>
                          @if($a->source_url)
                            <a href="{{ $a->source_url }}" target="_blank" class="text-xs text-blue-400 hover:underline break-all flex items-center gap-1 mt-1">
                                <span class="material-symbols-outlined text-[14px]">link</span> {{ $a->source_url }}
                            </a>
                          @endif
                           @if($a->tags)
                             <div class="flex gap-2 mt-2">
                                @foreach(explode(',', $a->tags) as $tag)
                                    <span class="text-[10px] bg-[#111722] text-text-secondary px-2 py-0.5 rounded border border-border-dark">{{ trim($tag) }}</span>
                                @endforeach
                             </div>
                          @endif
                        </div>

                        <div class="flex items-center gap-2">
                          <button
                            data-action="detail"
                            data-title="{{ e($a->title ?? 'Untitled') }}"
                            data-url="{{ e($a->source_url ?? '-') }}"
                            data-tags="{{ e($a->tags ?? '-') }}"
                            data-content="{{ e($a->content) }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#111722] text-text-secondary hover:text-white border border-border-dark transition">
                            Detail
                          </button>

                          <button data-action="toggle" data-id="{{ $a->id }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold border border-transparent
                              {{ $a->is_active ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-gray-700/50 text-gray-400' }}">
                            {{ $a->is_active ? 'Active' : 'Inactive' }}
                          </button>

                          <button data-action="delete" data-id="{{ $a->id }}"
                            class="p-1.5 rounded-lg text-red-400 hover:bg-red-500/10 transition" title="Delete">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                          </button>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-400 line-clamp-2 mt-2 font-mono bg-[#111722]/50 p-2 rounded border border-border-dark/50">
                        {{ \Illuminate\Support\Str::limit($a->content, 220) }}
                    </p>
                  </div>
                </div>
              @empty
                <div class="p-12 text-center flex flex-col items-center justify-center text-text-secondary">
                    <span class="material-symbols-outlined text-[48px] mb-2 opacity-50">menu_book</span>
                    <p>Belum ada artikel Knowledge Base.</p>
                </div>
              @endforelse
            </div>
          </div>

        </div>
    </div>
</main>


{{-- ================= MODAL DETAIL KB ================= --}}
<div id="kb-detail-modal" class="hidden fixed inset-0 z-50">
  <div data-modal-close="kb-detail-modal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-3xl rounded-2xl bg-[#1e2634] border border-[#324467] shadow-2xl flex flex-col max-h-[90vh]">
      <div class="flex items-center justify-between px-6 py-4 border-b border-[#324467]">
        <div>
          <h3 id="kb-detail-title" class="text-lg font-bold text-white">Detail KB</h3>
          <p id="kb-detail-url" class="text-xs text-[#92a4c9] mt-1 break-all"></p>
        </div>
        <button type="button" data-modal-close="kb-detail-modal" class="text-[#92a4c9] hover:text-white">
            <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="px-6 py-6 overflow-y-auto custom-scrollbar">
        <pre id="kb-detail-content" class="whitespace-pre-wrap text-sm text-gray-300 font-mono bg-[#111722] p-4 rounded-lg border border-[#324467]"></pre>
      </div>
      <div class="flex items-center justify-end px-6 py-4 border-t border-[#324467]">
        <button type="button" data-modal-close="kb-detail-modal" class="px-4 py-2 rounded-lg bg-[#324467] text-white text-sm font-semibold hover:bg-[#405580]">Tutup</button>
      </div>
    </div>
  </div>
</div>

{{-- ================= MODAL TEST AI ================= --}}
<div id="ai-test-modal" class="hidden fixed inset-0 z-50">
  <div data-modal-close="ai-test-modal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-[#1e2634] border border-[#324467] shadow-2xl">
      <div class="flex items-center justify-between px-6 py-4 border-b border-[#324467]">
        <div>
          <h3 class="text-lg font-bold text-white">Test AI Simulator</h3>
          <p class="text-xs text-[#92a4c9]">Uji respon AI berdasarkan data Knowledge Base.</p>
        </div>
        <button type="button" data-modal-close="ai-test-modal" class="text-[#92a4c9] hover:text-white">
            <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <div class="px-6 py-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-white mb-2">Pertanyaan User</label>
            <textarea id="ai-question" rows="3" placeholder="Contoh: Apakah ada dokter mata hari ini?"
              class="w-full rounded-lg border border-[#324467] bg-[#111722] px-4 py-3 text-sm text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
        </div>

        <div class="flex justify-end">
          <button id="btn-ai-test" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-bold hover:bg-blue-600 shadow-lg shadow-blue-900/20 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">play_arrow</span> Test Response
          </button>
        </div>

        <!-- Result Area -->
        <div id="ai-result-wrap" class="hidden mt-4 pt-4 border-t border-[#324467] space-y-4">
           <div class="flex items-center gap-2">
               <span class="text-xs font-bold text-[#92a4c9] uppercase tracking-wider">AI Confidence</span>
               <span id="ai-confidence" class="text-xs font-mono bg-[#111722] px-2 py-0.5 rounded text-white border border-[#324467]"></span>
           </div>
           
           <div class="bg-[#111722] p-4 rounded-lg border border-[#324467] relative">
               <div class="absolute top-2 left-2 text-[#92a4c9]">
                   <span class="material-symbols-outlined text-[20px]">smart_toy</span>
               </div>
               <div id="ai-answer" class="text-sm text-gray-300 pl-8 whitespace-pre-wrap"></div>
           </div>

           <div>
              <p class="text-xs font-bold text-[#92a4c9] mb-2 uppercase tracking-wider">Sumber Referensi:</p>
              <ul id="ai-sources" class="text-xs text-gray-400 list-disc pl-5 space-y-1"></ul>
           </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // ===== IMPORT URL
  const btnImport = document.getElementById('btn-import-url');
  btnImport?.addEventListener('click', async () => {
    const url = document.getElementById('kb-url')?.value?.trim();
    const title = document.getElementById('kb-title')?.value?.trim();

    if(!url){ alert('URL wajib diisi'); return; }

    btnImport.disabled = true;
    btnImport.innerHTML = 'Loading...';

    try{
      const res = await fetch('/kb/import-url', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ url, title }),
      });

      const data = await res.json();
      if(!data.ok) throw data;

      alert('Import sukses. Refresh halaman.');
      window.location.reload();

    }catch(err){
      alert(err.message || 'Gagal import URL');
      console.error(err);
    }finally{
      btnImport.disabled = false;
      btnImport.innerHTML = 'Import';
    }
  });

  // ===== Utilities
  function openModal(id){ 
      const el = document.getElementById(id);
      if(el) {
          el.classList.remove('hidden');
          setTimeout(() => {
              el.querySelector('div[class*="transform"]')?.classList.remove('scale-95', 'opacity-0');
          }, 10);
      }
  }
  function closeModal(id){ document.getElementById(id)?.classList.add('hidden'); }

  document.addEventListener('click', async (e) => {
    const openBtn  = e.target.closest('[data-modal-open]');
    const closeBtn = e.target.closest('[data-modal-close]');
    const tgl = e.target.closest('[data-action="toggle"]');
    const del = e.target.closest('[data-action="delete"]');
    const det = e.target.closest('[data-action="detail"]');

    if(openBtn){
      openModal(openBtn.getAttribute('data-modal-open'));
      return;
    }
    if(closeBtn){
      closeModal(closeBtn.getAttribute('data-modal-close'));
      return;
    }

    if(det){
      document.getElementById('kb-detail-title').textContent = det.dataset.title || 'Detail KB';
      document.getElementById('kb-detail-url').textContent   = det.dataset.url || '-';
      document.getElementById('kb-detail-content').textContent = det.dataset.content || '';
      openModal('kb-detail-modal');
      return;
    }

    if(tgl){
      const id = tgl.dataset.id;
      const res = await fetch(`/kb/${id}/toggle`, {
        method: 'PATCH',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });
      const data = await res.json();
      if(data.ok) window.location.reload();
      return;
    }

    if(del){
      if(!confirm('Hapus KB ini?')) return;
      const id = del.dataset.id;
      const res = await fetch(`/kb/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });
      const data = await res.json();
      if(data.ok) document.getElementById(`kb-${id}`)?.remove();
      return;
    }
  });

  // ===== TEST AI
  const btnTest = document.getElementById('btn-ai-test');
  const qEl = document.getElementById('ai-question');
  const wrap = document.getElementById('ai-result-wrap');
  const confEl = document.getElementById('ai-confidence');
  const ansEl = document.getElementById('ai-answer');
  const srcEl = document.getElementById('ai-sources');

  btnTest?.addEventListener('click', async () => {
    const q = (qEl.value || '').trim();
    if(q.length < 3){
      alert('Pertanyaan minimal 3 karakter');
      return;
    }

    btnTest.disabled = true;
    btnTest.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">sync</span> Testing...';
    wrap.classList.add('hidden');

    try{
      const res = await fetch(`/kb/test-ai`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ question: q }),
      });

      const data = await res.json();
      if(!data.ok){
        alert('Gagal test AI');
        return;
      }

      const r = data.result;

      if(!r){
        confEl.textContent = '0.00';
        confEl.className = 'text-xs font-mono bg-red-900/50 px-2 py-0.5 rounded text-red-200 border border-red-800';
        ansEl.textContent = 'AI tidak menemukan jawaban di KB.';
        srcEl.innerHTML = '';
        wrap.classList.remove('hidden');
        return;
      }

      confEl.textContent = Number(r.confidence || 0).toFixed(2);
      if(r.confidence > 0.7) {
          confEl.className = 'text-xs font-mono bg-green-900/50 px-2 py-0.5 rounded text-green-200 border border-green-800';
      } else {
          confEl.className = 'text-xs font-mono bg-yellow-900/50 px-2 py-0.5 rounded text-yellow-200 border border-yellow-800';
      }
      
      ansEl.textContent = r.answer || '-';

      srcEl.innerHTML = '';
      (r.sources || []).forEach(s => {
        const li = document.createElement('li');
        li.innerHTML = `<span class="text-white font-medium">${s.title || 'KB'}</span> ${s.source_url ? `<a href="${s.source_url}" target="_blank" class="text-blue-400 hover:underline">(${s.source_url})</a>` : ''}`;
        srcEl.appendChild(li);
      });

      wrap.classList.remove('hidden');

    }catch(err){
      console.error(err);
      alert('Error koneksi');
    }finally{
      btnTest.disabled = false;
      btnTest.innerHTML = '<span class="material-symbols-outlined text-[18px]">play_arrow</span> Test Response';
    }
  });

})();
</script>
</body>
</html>
