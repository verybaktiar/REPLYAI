@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
        Auto Reply Rules
      </h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Daftar keyword pemicu dan balasan otomatis bot.
      </p>
    </div>

    <div class="flex items-center gap-2">
      <div class="px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
        Tambah rule via modal (no redirect)
      </div>

      {{-- tombol pemicu modal --}}
      <button
        type="button"
        data-modal-open="rule-modal"
        id="btn-open-create"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-500 text-white text-sm font-medium hover:bg-brand-600 transition"
      >
        + Tambah Rule
      </button>
    </div>
  </div>

  {{-- Card Table --}}
  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">

    {{-- Table Header --}}
    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <p class="font-medium text-gray-900 dark:text-white">
        Rules Aktif & Nonaktif
      </p>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        {{-- search input --}}
        <div class="relative w-full sm:w-72">
          <input
            id="rules-search"
            type="text"
            placeholder="Cari trigger / response..."
            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 pl-9 pr-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/40"
          >
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        </div>

        {{-- total badge --}}
        <div class="text-xs text-gray-500 whitespace-nowrap">
          Total: <span id="rules-total">{{ $rules->count() }}</span>
        </div>
      </div>
    </div>


    {{-- Table --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800">
            <th class="px-5 py-3 w-14">#</th>
            <th class="px-5 py-3">Trigger Keyword</th>
            <th class="px-5 py-3">Response Text</th>
            <th class="px-5 py-3 w-28">Priority</th>
            <th class="px-5 py-3 w-28">Status</th>
            <th class="px-5 py-3 w-40">Created</th>
          </tr>
        </thead>

        <tbody id="rules-tbody" class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse($rules as $i => $rule)
            @include('pages.rules._row', ['rule' => $rule, 'i' => $i])
          @empty
            <tr id="rules-empty">
              <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                Belum ada rule. Tambahkan dulu via tombol "Tambah Rule".
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Footer note --}}
    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
      * Urutan eksekusi bot mengikuti <b>priority tertinggi</b> lalu waktu dibuat terbaru.
    </div>
  </div>

</div>

{{-- ================= MODAL CREATE / EDIT RULE ================= --}}
<div id="rule-modal" class="hidden fixed inset-0 z-50">
  {{-- backdrop --}}
  <div data-modal-close="rule-modal" class="absolute inset-0 bg-black/40"></div>

  {{-- modal box --}}
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xl">
      {{-- header --}}
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800">
        <div>
          <h3 id="rule-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Auto Reply Rule</h3>
          <p class="text-xs text-gray-500 mt-1">Rule baru akan langsung dipakai bot jika aktif.</p>
        </div>
        <button type="button" data-modal-close="rule-modal"
          class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500">
          ✕
        </button>
      </div>

      {{-- body --}}
      <form id="rule-form" class="px-5 py-4 space-y-4">
        @csrf
        <input type="hidden" id="rule-id" value="">

        <div>
          <label class="text-sm font-medium text-gray-800 dark:text-gray-200">Trigger Keyword</label>
          <input id="rule-trigger" name="trigger" type="text" required
            placeholder="contoh: pelayanan, biaya, jadwal dokter"
            class="mt-2 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/40">
            <p class="text-[11px] text-gray-500 mt-1">
              Bot akan reply jika pesan user match keyword ini.
              Bisa isi banyak keyword pisahkan dengan <b>|</b>, contoh: <code>biaya|harga|tarif</code>
            </p>
          <p id="err-trigger" class="text-[11px] text-red-500 mt-1 hidden"></p>
        </div>

        {{-- ✅ TAMBAHAN: MATCH TYPE --}}
        <div>
          <label class="text-sm font-medium text-gray-800 dark:text-gray-200">Match Type</label>
          <select id="rule-match-type" name="match_type"
            class="mt-2 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/40">
            <option value="contains">Contains (default)</option>
            <option value="exact">Exact (persis)</option>
            <option value="regex">Regex (pola)</option>
          </select>
          <p class="text-[11px] text-gray-500 mt-1">
            Contains: pesan mengandung keyword. Exact: harus sama persis. Regex: pakai pola regex.
          </p>
          <p id="err-match-type" class="text-[11px] text-red-500 mt-1 hidden"></p>
        </div>
        {{-- ✅ END TAMBAHAN MATCH TYPE --}}

        <div>
          <label class="text-sm font-medium text-gray-800 dark:text-gray-200">Response Text</label>
          <textarea id="rule-reply" name="reply" rows="5" required
            placeholder="Tulis balasan otomatis bot..."
            class="mt-2 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/40"></textarea>
          <p id="err-reply" class="text-[11px] text-red-500 mt-1 hidden"></p>
        </div>

        <div class="grid grid-cols-12 gap-3">
          <div class="col-span-6">
            <label class="text-sm font-medium text-gray-800 dark:text-gray-200">Priority</label>
            <input id="rule-priority" name="priority" type="number" min="0" value="0"
              class="mt-2 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/40">
            <p class="text-[11px] text-gray-500 mt-1">Semakin tinggi, semakin didahulukan.</p>
            <p id="err-priority" class="text-[11px] text-red-500 mt-1 hidden"></p>
          </div>

          <div class="col-span-6 flex items-end">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
              <input id="rule-active" name="is_active" type="checkbox" value="1" checked
                class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
              <span class="text-sm text-gray-800 dark:text-gray-200 font-medium">Aktifkan rule</span>
            </label>
          </div>
        </div>

        {{-- ✅ TAMBAHAN: TEST / PREVIEW --}}
        <hr class="border-gray-200 dark:border-gray-800">

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-800 dark:text-gray-200">
            Test Pesan (Preview)
          </label>

          <textarea
            id="test-text"
            rows="3"
            placeholder="Tulis contoh pesan user di sini..."
            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/40"
          ></textarea>

          <div class="flex items-center justify-between">
            <p class="text-[11px] text-gray-500">
              Klik Test untuk lihat rule mana yang akan kepakai bot.
            </p>

            <button
              type="button"
              id="btn-test-rule"
              class="px-3 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-medium hover:opacity-90"
            >
              Test
            </button>
          </div>

          <div id="test-result" class="hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 text-sm">
            <div id="test-result-meta" class="text-xs text-gray-500 mb-2"></div>
            <div id="test-result-reply" class="whitespace-pre-line text-gray-900 dark:text-gray-100"></div>
          </div>
        </div>
        {{-- ✅ END TAMBAHAN TEST/PREVIEW --}}

      </form>

      {{-- footer --}}
      <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-800">
        <button type="button" data-modal-close="rule-modal"
          class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 hover:opacity-80">
          Batal
        </button>
        <button type="submit" form="rule-form" id="btn-save-rule"
          class="px-4 py-2 rounded-lg bg-brand-500 text-white text-sm font-medium hover:bg-brand-600">
          Simpan Rule
        </button>
      </div>
    </div>
  </div>
</div>


{{-- ================= MODAL DELETE ================= --}}
<div id="delete-modal" class="hidden fixed inset-0 z-50">
  <div data-modal-close="delete-modal" class="absolute inset-0 bg-black/40"></div>

  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xl">
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Rule?</h3>
          <p class="text-xs text-gray-500 mt-1">Tindakan ini tidak bisa dibatalkan.</p>
        </div>
        <button type="button" data-modal-close="delete-modal"
          class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500">✕</button>
      </div>

      <input type="hidden" id="delete-id" value="">

      <div class="flex items-center justify-end gap-2 px-5 py-4">
        <button type="button" data-modal-close="delete-modal"
          class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 hover:opacity-80">
          Batal
        </button>
        <button type="button" id="btn-confirm-delete"
          class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
          Hapus
        </button>
      </div>
    </div>
  </div>
</div>


{{-- ================= TOAST (simple) ================= --}}
<div id="toast"
     class="hidden fixed bottom-5 right-5 z-[60] rounded-xl bg-gray-900 px-4 py-3 text-sm text-white shadow-lg">
</div>


{{-- ================= SCRIPT MODAL + CRUD AJAX + SEARCH + SORT + INLINE PRIORITY + MATCH TYPE + TEST PREVIEW ================= --}}
<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  const tbody = document.getElementById('rules-tbody');
  const totalEl = document.getElementById('rules-total');
  const emptyRow = document.getElementById('rules-empty');

  // ===== Toast
  const toastEl = document.getElementById('toast');
  let toastTimer = null;
  function toast(msg){
    toastEl.textContent = msg;
    toastEl.classList.remove('hidden');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 1800);
  }

  // ===== modal open/close
  document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[data-modal-open]');
    if (openBtn) {
      const id = openBtn.getAttribute('data-modal-open');
      const modal = document.getElementById(id);
      if (modal) modal.classList.remove('hidden');
      return;
    }

    const closeBtn = e.target.closest('[data-modal-close]');
    if (closeBtn) {
      const id = closeBtn.getAttribute('data-modal-close');
      const modal = document.getElementById(id);
      if (modal) modal.classList.add('hidden');
    }
  });

  function openModal(id){ document.getElementById(id)?.classList.remove('hidden'); }
  function closeModal(id){ document.getElementById(id)?.classList.add('hidden'); }

  // ===== Form refs
  const form = document.getElementById('rule-form');
  const modalTitle = document.getElementById('rule-modal-title');
  const ruleId = document.getElementById('rule-id');
  const fTrigger = document.getElementById('rule-trigger');
  const fReply = document.getElementById('rule-reply');
  const fPriority = document.getElementById('rule-priority');
  const fActive = document.getElementById('rule-active');
  const btnSave = document.getElementById('btn-save-rule');

  // ✅ TAMBAHAN REFS match type
  const fMatchType = document.getElementById('rule-match-type');

  const errTrigger = document.getElementById('err-trigger');
  const errReply = document.getElementById('err-reply');
  const errPriority = document.getElementById('err-priority');
  // ✅ TAMBAHAN error match type
  const errMatchType = document.getElementById('err-match-type');

  // ✅ TAMBAHAN REFS test preview
  const testText = document.getElementById('test-text');
  const btnTestRule = document.getElementById('btn-test-rule');
  const testResult = document.getElementById('test-result');
  const testResultMeta = document.getElementById('test-result-meta');
  const testResultReply = document.getElementById('test-result-reply');

  function hideTestResult(){
    testResult?.classList.add('hidden');
    if(testResultMeta) testResultMeta.textContent = '';
    if(testResultReply) testResultReply.textContent = '';
  }

  function clearErrors(){
    [errTrigger, errReply, errPriority, errMatchType].forEach(x => { x.classList.add('hidden'); x.textContent=''; });
  }
  function setError(el, msg){
    el.textContent = msg;
    el.classList.remove('hidden');
  }

  function resetForm(){
    ruleId.value = '';
    fTrigger.value = '';
    fReply.value = '';
    fPriority.value = 0;
    fActive.checked = true;
    // ✅ default match type
    if (fMatchType) fMatchType.value = 'contains';
    // ✅ reset preview area
    if (testText) testText.value = '';
    hideTestResult();
    clearErrors();
  }

  // open create
  document.getElementById('btn-open-create').addEventListener('click', () => {
    resetForm();
    modalTitle.textContent = 'Tambah Auto Reply Rule';
    btnSave.textContent = 'Simpan Rule';
  });

  // ===== helper update numbering + total
  function renumber(){
    const visibleRows = Array.from(tbody.querySelectorAll('tr[data-id]'))
      .filter(tr => tr.style.display !== 'none');

    visibleRows.forEach((tr, idx) => {
      const numCell = tr.querySelector('[data-cell="num"]');
      if (numCell) numCell.textContent = idx + 1;
    });
  }

  function setTotal(n){ if(totalEl) totalEl.textContent = n; }

  function sortRowsByPriorityAndCreated(){
    const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));

    rows.sort((a, b) => {
      const pa = Number(a.dataset.priority || 0);
      const pb = Number(b.dataset.priority || 0);

      if (pb !== pa) return pb - pa;

      const ca = a.dataset.createdAt ? new Date(a.dataset.createdAt).getTime() : 0;
      const cb = b.dataset.createdAt ? new Date(b.dataset.createdAt).getTime() : 0;
      return cb - ca;
    });

    rows.forEach(r => tbody.appendChild(r));
    renumber();
  }

  function flashRow(tr){
    tr.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20');
    setTimeout(() => tr.classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20'), 1200);
  }

  // ===== Search filter
  const searchInput = document.getElementById('rules-search');
  let searchTimer = null;

  function applySearchFilter(q){
    q = (q || '').toLowerCase().trim();

    const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
    let visibleCount = 0;

    rows.forEach(tr => {
      const trigger = (tr.dataset.trigger || '').toLowerCase();
      const reply   = (tr.dataset.reply || '').toLowerCase();
      const active  = (tr.dataset.active === '1') ? 'active' : 'inactive';

      const haystack = `${trigger} ${reply} ${active}`;
      const match = q === '' || haystack.includes(q);

      tr.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    setTotal(visibleCount);

    const empty = document.getElementById('rules-empty');
    if (!visibleCount) {
      if (!empty) {
        const tr = document.createElement('tr');
        tr.id = 'rules-empty';
        tr.innerHTML = `
          <td colspan="6" class="px-5 py-8 text-center text-gray-500">
            Tidak ada rule yang cocok dengan pencarian.
          </td>
        `;
        tbody.appendChild(tr);
      }
    } else {
      empty?.remove();
    }

    renumber();
  }

  searchInput?.addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applySearchFilter(e.target.value), 120);
  });

  // ===== Delegation actions (edit/delete/toggle)
  tbody.addEventListener('click', async (e) => {
    const editBtn = e.target.closest('[data-action="edit"]');
    const deleteBtn = e.target.closest('[data-action="delete"]');
    const toggleBtn = e.target.closest('[data-action="toggle"]');

    if (editBtn){
      const tr = editBtn.closest('tr');
      resetForm();
      modalTitle.textContent = 'Edit Auto Reply Rule';
      btnSave.textContent = 'Update Rule';

      ruleId.value = tr.dataset.id;
      fTrigger.value = tr.dataset.trigger;
      fReply.value = tr.dataset.reply;
      fPriority.value = tr.dataset.priority;
      fActive.checked = tr.dataset.active === '1';
      // ✅ isi match type dari dataset row
      if (fMatchType) fMatchType.value = tr.dataset.matchType || 'contains';

      openModal('rule-modal');
    }

    if (deleteBtn){
      const tr = deleteBtn.closest('tr');
      document.getElementById('delete-id').value = tr.dataset.id;
      openModal('delete-modal');
    }

    if (toggleBtn){
      const tr = toggleBtn.closest('tr');
      const id = tr.dataset.id;
      toggleBtn.disabled = true;

      try{
        const res = await fetch(`/rules/${id}/toggle`, {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          }
        });
        const data = await res.json();
        if(!data.ok) throw data;

        const temp = document.createElement('tbody');
        temp.innerHTML = data.rowHtml.trim();
        const newRow = temp.firstElementChild;
        tr.replaceWith(newRow);

        sortRowsByPriorityAndCreated();
        flashRow(newRow);
        applySearchFilter(searchInput?.value || '');
        toast('Status rule diubah');
      }catch(err){
        console.error(err);
        toast('Gagal toggle rule');
      }finally{
        toggleBtn.disabled = false;
      }
    }
  });

  // ===== Inline edit priority
  tbody.addEventListener('click', (e) => {
    const badge = e.target.closest('[data-action="edit-priority"]');
    if (!badge) return;

    const tr = badge.closest('tr');
    if (!tr) return;

    if (tr.querySelector('input[data-priority-input]')) return;

    const currentVal = Number(tr.dataset.priority || 0);

    const input = document.createElement('input');
    input.type = 'number';
    input.min = '0';
    input.value = currentVal;
    input.setAttribute('data-priority-input', '1');
    input.className = `
      w-16 px-2 py-1 rounded-md text-xs font-semibold
      border border-gray-200 dark:border-gray-700
      bg-white dark:bg-gray-800 text-gray-900 dark:text-white
      focus:outline-none focus:ring-2 focus:ring-brand-500/40
    `.trim();

    badge.replaceWith(input);
    input.focus();
    input.select();

    async function savePriority(){
      const newVal = Number(input.value ?? 0);
      if (Number.isNaN(newVal) || newVal < 0) {
        toast('Priority tidak valid');
        input.value = currentVal;
        input.focus();
        return;
      }

      if (newVal === currentVal) {
        input.replaceWith(badge);
        return;
      }

      input.disabled = true;
      const id = tr.dataset.id;

      const payload = {
        trigger_keyword: tr.dataset.trigger || '',
        response_text: tr.dataset.reply || '',
        // ✅ ikut simpan match type biar validasi update lolos
        match_type: tr.dataset.matchType || 'contains',
        priority: newVal,
        is_active: tr.dataset.active === '1' ? 1 : 0,
      };

      try{
        const res = await fetch(`/rules/${id}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(payload),
        });

        if (res.status === 422){
          toast('Validasi gagal');
          input.disabled = false;
          return;
        }

        const data = await res.json();
        if(!data.ok) throw data;

        const temp = document.createElement('tbody');
        temp.innerHTML = data.rowHtml.trim();
        const newRow = temp.firstElementChild;

        tr.replaceWith(newRow);

        sortRowsByPriorityAndCreated();
        flashRow(newRow);
        applySearchFilter(searchInput?.value || '');
        toast('Priority diupdate');

      }catch(err){
        console.error(err);
        toast('Gagal update priority');
        input.disabled = false;
        input.value = currentVal;
        input.focus();
      }
    }

    input.addEventListener('keydown', (ev) => {
      if (ev.key === 'Enter') {
        ev.preventDefault();
        savePriority();
      }
      if (ev.key === 'Escape') {
        input.replaceWith(badge);
      }
    });

    input.addEventListener('blur', () => savePriority());
  });

  // ===== Submit create/edit via fetch
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    clearErrors();

    const id = ruleId.value;
    const isEdit = !!id;

    const payload = {
      trigger_keyword: fTrigger.value.trim(),
      response_text: fReply.value.trim(),
      // ✅ kirim match type
      match_type: fMatchType?.value || 'contains',
      priority: Number(fPriority.value ?? 0),
      is_active: fActive.checked ? 1 : 0,
    };

    const url = isEdit ? `/rules/${id}` : `/rules`;
    const method = isEdit ? 'PATCH' : 'POST';

    btnSave.disabled = true;

    try{
      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
      });

      if (res.status === 422){
        const v = await res.json();
        if(v.errors?.trigger_keyword) setError(errTrigger, v.errors.trigger_keyword[0]);
        if(v.errors?.response_text) setError(errReply, v.errors.response_text[0]);
        if(v.errors?.match_type) setError(errMatchType, v.errors.match_type[0]);
        if(v.errors?.priority) setError(errPriority, v.errors.priority[0]);
        return;
      }

      const data = await res.json();
      if(!data.ok) throw data;

      const temp = document.createElement('tbody');
      temp.innerHTML = data.rowHtml.trim();
      const newRow = temp.firstElementChild;

      if (isEdit){
        document.getElementById(`rule-row-${id}`)?.replaceWith(newRow);
        toast('Rule diupdate');
      } else {
        if (emptyRow) emptyRow.remove();
        tbody.appendChild(newRow);
        toast('Rule dibuat');
        setTotal(Number(totalEl.textContent || 0) + 1);
      }

      closeModal('rule-modal');

      sortRowsByPriorityAndCreated();
      flashRow(newRow);
      applySearchFilter(searchInput?.value || '');

    }catch(err){
      console.error(err);
      toast('Gagal simpan rule');
    }finally{
      btnSave.disabled = false;
    }
  });

  // ===== Confirm delete
  document.getElementById('btn-confirm-delete').addEventListener('click', async () => {
    const id = document.getElementById('delete-id').value;
    if(!id) return;

    const btn = document.getElementById('btn-confirm-delete');
    btn.disabled = true;

    try{
      const res = await fetch(`/rules/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });
      const data = await res.json();
      if(!data.ok) throw data;

      document.getElementById(`rule-row-${id}`)?.remove();

      setTotal(Math.max(0, Number(totalEl.textContent || 0) - 1));

      sortRowsByPriorityAndCreated();
      applySearchFilter(searchInput?.value || '');

      if (!tbody.querySelector('tr[data-id]')){
        const tr = document.createElement('tr');
        tr.id = 'rules-empty';
        tr.innerHTML = `
          <td colspan="6" class="px-5 py-8 text-center text-gray-500">
            Belum ada rule. Tambahkan dulu via tombol "Tambah Rule".
          </td>
        `;
        tbody.appendChild(tr);
      }

      closeModal('delete-modal');
      toast('Rule dihapus');

    }catch(err){
      console.error(err);
      toast('Gagal hapus rule');
    }finally{
      btn.disabled = false;
    }
  });

  // ===== ✅ TAMBAHAN: TEST PREVIEW HANDLER
  btnTestRule?.addEventListener('click', async () => {
    const text = testText?.value?.trim();
    if (!text) {
      toast('Isi contoh pesan dulu');
      return;
    }

    btnTestRule.disabled = true;
    hideTestResult();

    try {
      const res = await fetch('/rules/test', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ text }),
      });

      if (res.status === 422) {
        toast('Pesan test wajib diisi');
        return;
      }

      const data = await res.json();
      if (!data.ok) throw data;

      testResult.classList.remove('hidden');

      if (!data.matched) {
        testResultMeta.textContent = 'No match';
        testResultReply.textContent = data.message || 'Tidak ada rule yang cocok.';
        return;
      }

      const r = data.rule;
      testResultMeta.textContent =
        `Match: Rule #${r.id} | trigger: "${r.trigger_keyword}" | type: ${r.match_type} | priority: ${r.priority}`;

      testResultReply.textContent = data.reply || '-';

    } catch (err) {
      console.error(err);
      toast('Gagal test rule');
    } finally {
      btnTestRule.disabled = false;
    }
  });
  // ===== END TEST PREVIEW

})();
</script>
@endsection
