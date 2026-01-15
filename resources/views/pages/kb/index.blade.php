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
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex flex-col lg:flex-row">

<!-- Sidebar Navigation -->
@include('components.sidebar')

<main class="flex-1 flex flex-col h-full overflow-hidden relative pt-14 lg:pt-0">
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 lg:p-10 pb-20">
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

          <!-- Import Section (Tabs) -->
          <div class="rounded-xl border border-border-dark bg-surface-dark overflow-hidden">
              <div class="flex border-b border-border-dark">
                  <button id="tab-url" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-[#1f2b40] border-b-2 border-primary transition-colors">
                      <span class="material-symbols-outlined align-middle mr-1 text-[18px]">link</span> Import URL
                  </button>
                  <button id="tab-file" class="flex-1 px-4 py-3 text-sm font-bold text-gray-400 hover:text-white hover:bg-[#1f2b40] border-b-2 border-transparent transition-colors">
                      <span class="material-symbols-outlined align-middle mr-1 text-[18px]">upload_file</span> Upload Dokumen
                  </button>
              </div>

              <!-- Content URL -->
              <div id="panel-url" class="p-6 space-y-4">
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

              <!-- Content File -->
              <div id="panel-file" class="hidden p-6 space-y-4">
                  <div class="border-2 border-dashed border-border-dark rounded-xl p-8 text-center hover:bg-[#111722]/50 transition-colors relative" id="drop-zone">
                      <input type="file" id="kb-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.txt">
                      <div class="flex flex-col items-center gap-2 pointer-events-none">
                          <span class="material-symbols-outlined text-[40px] text-text-secondary">cloud_upload</span>
                          <p class="text-sm text-gray-300 font-medium">Klik atau drag file PDF / TXT ke sini</p>
                          <p class="text-xs text-text-secondary">Maksimal 5MB. PDF akan diparsing otomatis.</p>
                          <p id="file-name" class="text-sm text-primary font-bold mt-2 hidden"></p>
                      </div>
                  </div>
                  
                  <div class="flex items-center gap-3">
                      <input id="kb-file-tags" type="text" placeholder="Tags (koma dipisah, misal: ukm, panduan)"
                        class="flex-1 rounded-lg border border-border-dark bg-[#111722] px-3 py-2.5 text-sm text-white focus:outline-none focus:border-primary">
                      
                      <button id="btn-import-file" disabled
                        class="px-6 py-2.5 rounded-lg bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Upload & Process
                      </button>
                  </div>
              </div>
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
                          <!-- Profile Selector -->
                          <select onchange="updateKbProfile({{ $a->id }}, this.value)" 
                                  class="px-2 py-1.5 rounded-lg text-xs bg-[#111722] text-text-secondary border border-border-dark focus:outline-none focus:border-primary cursor-pointer">
                              <option value="">📋 Semua Profile</option>
                              @foreach($businessProfiles as $bp)
                                  <option value="{{ $bp->id }}" {{ $a->business_profile_id == $bp->id ? 'selected' : '' }}>
                                      {{ $bp->getIndustryIcon() }} {{ $bp->business_name }}
                                  </option>
                              @endforeach
                          </select>

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
// Global function for profile update
async function updateKbProfile(kbId, profileId) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    try {
        const res = await fetch(`/kb/${kbId}/profile`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ business_profile_id: profileId || null })
        });
        const data = await res.json();
        if (data.ok) {
            // Show brief success indicator
            console.log('Profile updated for KB', kbId);
        }
    } catch (e) {
        console.error('Error updating KB profile:', e);
        alert('Gagal update profil KB');
    }
}

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

  // ===== TAB SWITCHING
  const tabUrl = document.getElementById('tab-url');
  const tabFile = document.getElementById('tab-file');
  const panelUrl = document.getElementById('panel-url');
  const panelFile = document.getElementById('panel-file');

  function switchTab(mode){
      if(mode === 'url'){
          tabUrl.className = "flex-1 px-4 py-3 text-sm font-bold text-white bg-[#1f2b40] border-b-2 border-primary transition-colors";
          tabFile.className = "flex-1 px-4 py-3 text-sm font-bold text-gray-400 hover:text-white hover:bg-[#1f2b40] border-b-2 border-transparent transition-colors";
          panelUrl.classList.remove('hidden');
          panelFile.classList.add('hidden');
      } else {
          tabFile.className = "flex-1 px-4 py-3 text-sm font-bold text-white bg-[#1f2b40] border-b-2 border-primary transition-colors";
          tabUrl.className = "flex-1 px-4 py-3 text-sm font-bold text-gray-400 hover:text-white hover:bg-[#1f2b40] border-b-2 border-transparent transition-colors";
          panelFile.classList.remove('hidden');
          panelUrl.classList.add('hidden');
      }
  }

  tabUrl?.addEventListener('click', () => switchTab('url'));
  tabFile?.addEventListener('click', () => switchTab('file'));

  // ===== FILE UPLOAD LOGIC
  const fileInput = document.getElementById('kb-file');
  const fileNameDisplay = document.getElementById('file-name');
  const btnUpload = document.getElementById('btn-import-file');
  
  fileInput?.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if(file){
          fileNameDisplay.textContent = "Selected: " + file.name + " (" + (file.size/1024).toFixed(1) + " KB)";
          fileNameDisplay.classList.remove('hidden');
          btnUpload.disabled = false;
      } else {
          fileNameDisplay.classList.add('hidden');
          btnUpload.disabled = true;
      }
  });

  btnUpload?.addEventListener('click', async () => {
      const file = fileInput.files[0];
      if(!file) return;

      const tags = document.getElementById('kb-file-tags').value;
      const formData = new FormData();
      formData.append('file', file);
      if(tags) formData.append('tags', tags);

      btnUpload.disabled = true;
      btnUpload.innerHTML = 'Uploading & Parsing...';

      try {
          const res = await fetch('/kb/import-file', {
              method: 'POST',
              headers: {
                  'X-CSRF-TOKEN': csrf,
                  'Accept': 'application/json',
              },
              body: formData
          });

          const data = await res.json();
          if(!res.ok) throw new Error(data.message || 'Gagal upload file');

          alert('File berhasil diupload dan diparsing!');
          window.location.reload();
      } catch (e) {
          alert('Error: ' + e.message);
          console.error(e);
      } finally {
          btnUpload.disabled = false;
          btnUpload.innerHTML = 'Upload & Process';
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
