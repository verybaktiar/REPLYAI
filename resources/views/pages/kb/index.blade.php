@extends('layouts.app')

@section('content')
<div class="space-y-6">
  {{-- tombol test AI --}}
  <button
    type="button"
    data-modal-open="ai-test-modal"
    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-semibold hover:bg-gray-800 transition"
  >
    🤖 Test AI
  </button>

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Knowledge Base</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Sumber jawaban AI fallback. Bisa input manual atau import dari URL resmi.
      </p>
    </div>
  </div>

  {{-- Import URL --}}
  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-3">
    <h3 class="font-semibold text-gray-900 dark:text-white">Import dari URL</h3>

    <div class="grid grid-cols-12 gap-3">
      <div class="col-span-12 sm:col-span-7">
        <input id="kb-url" type="text" placeholder="https://web-resmi/jadwal-dokter"
          class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white">
      </div>
      <div class="col-span-12 sm:col-span-3">
        <input id="kb-title" type="text" placeholder="Judul (opsional)"
          class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white">
      </div>
      <div class="col-span-12 sm:col-span-2">
        <button id="btn-import-url"
          class="w-full px-4 py-2 rounded-lg bg-brand-500 text-white text-sm font-medium hover:bg-brand-600">
          Import
        </button>
      </div>
    </div>

    <p class="text-xs text-gray-500">Gunakan URL resmi RS/klinik agar AI aman.</p>
  </div>

  {{-- List --}}
  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
      <p class="font-medium text-gray-900 dark:text-white">Daftar KB</p>
      <div class="text-xs text-gray-500">Total: {{ $articles->count() }}</div>
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-800">
      @forelse($articles as $a)
        <div id="kb-{{ $a->id }}" class="p-5 space-y-1">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">
                {{ $a->title ?? 'Untitled' }}
              </p>
              @if($a->source_url)
                <p class="text-xs text-gray-500 break-all">{{ $a->source_url }}</p>
              @endif
              @if($a->tags)
                <p class="text-xs text-gray-500">Tags: {{ $a->tags }}</p>
              @endif
            </div>

            <div class="flex items-center gap-2">
              {{-- DETAIL BUTTON --}}
              <button
                data-action="detail"
                data-title="{{ e($a->title ?? 'Untitled') }}"
                data-url="{{ e($a->source_url ?? '-') }}"
                data-tags="{{ e($a->tags ?? '-') }}"
                data-content="{{ e($a->content) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200 hover:opacity-80">
                Detail
              </button>

              <button data-action="toggle" data-id="{{ $a->id }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium
                  {{ $a->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200'
                                   : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
                {{ $a->is_active ? 'Active' : 'Inactive' }}
              </button>

              <button data-action="delete" data-id="{{ $a->id }}"
                class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">
                Delete
              </button>
            </div>
          </div>

          <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
            {{ \Illuminate\Support\Str::limit($a->content, 220) }}
          </p>
        </div>
      @empty
        <div class="p-8 text-center text-gray-500">Belum ada KB.</div>
      @endforelse
    </div>
  </div>
</div>

{{-- ================= MODAL DETAIL KB ================= --}}
<div id="kb-detail-modal" class="hidden fixed inset-0 z-50">
  <div data-modal-close="kb-detail-modal" class="absolute inset-0 bg-black/40"></div>

  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xl">
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800">
        <div>
          <h3 id="kb-detail-title" class="text-lg font-semibold text-gray-900 dark:text-white">Detail KB</h3>
          <p id="kb-detail-url" class="text-xs text-gray-500 mt-1 break-all"></p>
          <p id="kb-detail-tags" class="text-xs text-gray-500 mt-1"></p>
        </div>
        <button type="button" data-modal-close="kb-detail-modal"
          class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500">✕</button>
      </div>

      <div class="px-5 py-4">
        <pre id="kb-detail-content"
             class="whitespace-pre-wrap text-sm text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg max-h-[60vh] overflow-y-auto"></pre>
      </div>

      <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-800">
        <button type="button" data-modal-close="kb-detail-modal"
          class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 hover:opacity-80">
          Tutup
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================= MODAL TEST AI ================= --}}
<div id="ai-test-modal" class="hidden fixed inset-0 z-50">
  <div data-modal-close="ai-test-modal" class="absolute inset-0 bg-black/40"></div>

  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xl">
      {{-- header --}}
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Test AI dari Knowledge Base</h3>
          <p class="text-xs text-gray-500 mt-1">Coba pertanyaan user, lihat jawaban AI + confidence.</p>
        </div>
        <button type="button" data-modal-close="ai-test-modal"
          class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500">
          ✕
        </button>
      </div>

      {{-- body --}}
      <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto">
        <label class="text-sm font-medium text-gray-800 dark:text-gray-200">Pertanyaan</label>
        <textarea id="ai-question" rows="3"
          placeholder="contoh: jadwal poli anak hari ini?"
          class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/40"></textarea>

        <div class="flex items-center justify-end gap-2">
          <button id="btn-ai-test"
            class="px-4 py-2 rounded-lg bg-brand-500 text-white text-sm font-medium hover:bg-brand-600">
            Test
          </button>
        </div>

        {{-- result --}}
        <div id="ai-result-wrap" class="hidden mt-3 border-t border-gray-200 dark:border-gray-800 pt-3 space-y-2">
          <div class="text-xs text-gray-500">
            Confidence: <span id="ai-confidence" class="font-semibold"></span>
          </div>

          <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Jawaban AI</div>
            <div id="ai-answer"
            class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 max-h-[40vh] overflow-y-auto"></div>
          

          <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Sumber KB</div>
            <ul id="ai-sources" class="text-sm text-gray-700 dark:text-gray-300 list-disc pl-5 space-y-1"></ul>
          </div>
        </div>
      </div>

      {{-- footer --}}
      <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-800">
        <button type="button" data-modal-close="ai-test-modal"
          class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 hover:opacity-80">
          Tutup
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================= SCRIPT (IMPORT + MODAL + CRUD + TEST AI) ================= --}}
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
    }
  });

  // ===== modal open/close generic
  function openModal(id){ document.getElementById(id)?.classList.remove('hidden'); }
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
      document.getElementById('kb-detail-tags').textContent  = 'Tags: ' + (det.dataset.tags || '-');
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
        confEl.textContent = '0.00 (tidak yakin)';
        ansEl.textContent = 'AI tidak menemukan jawaban di KB.';
        srcEl.innerHTML = '';
        wrap.classList.remove('hidden');
        return;
      }

      confEl.textContent = Number(r.confidence || 0).toFixed(2);
      ansEl.textContent = r.answer || '-';

      srcEl.innerHTML = '';
      (r.sources || []).forEach(s => {
        const li = document.createElement('li');
        li.innerHTML = `${s.title || 'KB'} ${s.source_url ? `<span class="text-xs text-gray-400">(${s.source_url})</span>` : ''}`;
        srcEl.appendChild(li);
      });

      wrap.classList.remove('hidden');

    }catch(err){
      console.error(err);
      alert('Error koneksi');
    }finally{
      btnTest.disabled = false;
    }
  });

})();
</script>
@endsection
