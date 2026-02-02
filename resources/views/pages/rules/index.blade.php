<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('rules.title') }} - ReplyAI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
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
                  "background-dark": "#101622",
                  "surface-dark": "#1e293b", 
                  "surface-lighter": "#232f48", 
                  "text-secondary": "#92a4c9", 
                },
                fontFamily: {
                  "display": ["Inter", "sans-serif"]
                },
                borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
              },
            },
          }
    </script>
    <style>
            /* Custom scrollbar for dark theme */
            ::-webkit-scrollbar { width: 8px; height: 8px; }
            ::-webkit-scrollbar-track { background: #101622; }
            ::-webkit-scrollbar-thumb { background: #232f48; border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: #334155; }
            
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
            }
            .material-symbols-outlined.filled {
                font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24
            }
            
            /* Toggle Switch Styling */
            .toggle-checkbox:checked { right: 0; border-color: #135bec; }
            .toggle-checkbox:checked + .toggle-label { background-color: #135bec; }

            @media (max-width: 640px) {
                .modal-content-mobile {
                    position: fixed;
                    bottom: 0;
                    width: 100%;
                    max-width: none !important;
                    border-radius: 1.5rem 1.5rem 0 0 !important;
                    margin: 0 !important;
                    padding-bottom: env(safe-area-inset-bottom);
                }
            }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden">
<div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- SIDEBAR -->
<!-- Sidebar Navigation -->
@include('components.sidebar')

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Top Header for Rules -->
        <header class="h-14 border-b border-border-dark bg-background-dark/80 backdrop-blur-md flex items-center justify-between px-6 z-20 shrink-0">
            <div class="flex items-center gap-2 text-text-secondary text-xs font-bold uppercase tracking-widest">
                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1 bg-whatsapp/10 rounded-full border border-whatsapp/20">
                    <div class="size-1.5 bg-whatsapp rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold text-whatsapp uppercase tracking-widest">{{ __('common.system_online', ['default' => 'System Online']) }}</span>
                </div>
                @include('components.language-switcher')
            </div>
        </header>
        <!-- Remove redundant mobile header - sidebar already provides it -->

        <div class="flex-1 overflow-y-auto p-4 md:p-8 lg:px-12">
            <div class="max-w-[1200px] mx-auto flex flex-col gap-6">
                <!-- Page Heading -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div class="flex flex-col gap-2 max-w-2xl">
                        <div class="flex items-center gap-3">
                            <h1 class="text-white text-3xl md:text-4xl font-black leading-tight tracking-tight">{{ __('rules.title') }}</h1>
                            @include('components.page-help', [
                                'title' => __('rules.help_title'),
                                'description' => __('rules.help_description'),
                                'tips' => [
                                    __('rules.tip_1'),
                                    __('rules.tip_2'),
                                    __('rules.tip_3'),
                                    __('rules.tip_4'),
                                    __('rules.tip_5')
                                ]
                            ])
                        </div>
                        <p class="text-text-secondary text-base font-normal">
                            {{ __('rules.subtitle') }}
                        </p>
                    </div>
                    <button id="btn-open-create" class="flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-lg h-12 px-6 bg-primary hover:bg-blue-600 transition-colors text-white text-sm font-bold shadow-lg shadow-blue-900/20">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        <span>{{ __('rules.create_button') }}</span>
                    </button>
                </div>

                <!-- Filters & Search Toolbar -->
                <div class="bg-surface-lighter rounded-xl p-2 flex flex-col lg:flex-row gap-2">
                    <!-- Search -->
                    <div class="flex-1 min-w-[280px]">
                        <div class="relative h-10 w-full group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-text-secondary group-focus-within:text-white transition-colors">search</span>
                            </div>
                            <input id="rules-search" class="block w-full h-full pl-10 pr-3 py-2 border-none rounded-lg bg-[#111722] text-white placeholder-text-secondary focus:ring-1 focus:ring-primary focus:bg-[#0f1520] transition-all text-sm" placeholder="{{ __('rules.search_placeholder') }}" type="text"/>
                        </div>
                    </div>
                    <div class="w-px h-6 bg-[#232f48] mx-2 self-center hidden lg:block"></div>
                     <div class="flex items-center px-2 text-text-secondary text-sm">
                        {{ __('rules.total_rules', ['count' => $rules->count()]) }}
                    </div>
                </div>

                <!-- Bot List / Grid -->
                <div class="flex flex-col gap-3">
                    <!-- Header (Desktop) -->
                    <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-2 text-xs font-semibold text-text-secondary uppercase tracking-wider">
                        <div class="col-span-4">{{ __('rules.info_trigger') }}</div>
                        <div class="col-span-2">{{ __('rules.platform') }}</div>
                        <div class="col-span-2">{{ __('rules.match_type') }}</div>
                        <div class="col-span-2">{{ __('rules.status') }}</div>
                        <div class="col-span-2 text-right">{{ __('rules.actions') }}</div>
                    </div>

                    <!-- Container for Loop -->
                    <div id="rules-container" class="flex flex-col gap-3">
                         @forelse($rules as $i => $rule)
                            @include('pages.rules._row', ['rule' => $rule, 'i' => $i])
                         @empty
                            <div id="rules-empty" class="py-12">
                                <x-empty-state 
                                    icon="smart_toy" 
                                    title="{{ __('rules.empty_title') }}" 
                                    description="{{ __('rules.empty_description') }}"
                                    actionLabel="{{ __('rules.empty_action') }}"
                                    actionUrl="#" {{-- This will be handled by existing JS modal trigger --}}
                                />
                            </div>
                         @endforelse
                    </div>
                </div>

                <!-- Pagination / Footer Info -->
                <div class="flex items-center justify-between mt-4 text-text-secondary text-sm">
                    <p>{{ __('rules.show_all') }}</p>
                </div>
            </div>
        </div>
    </main>
</div>

{{-- ================= MODAL CREATE / EDIT RULE ================= --}}
<div id="rule-modal" class="hidden fixed inset-0 z-50">
    <!-- backdrop -->
    <div data-modal-close="rule-modal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <!-- modal box -->
    <div class="absolute inset-0 flex items-center justify-center p-0 md:p-4">
        <div class="w-full max-w-xl rounded-2xl bg-[#1e293b] border border-[#232f48] shadow-2xl modal-content-mobile">
             <!-- header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-[#232f48]">
                <div>
                    <h3 id="rule-modal-title" class="text-xl font-bold text-white">{{ __('rules.modal_create_title') }}</h3>
                    <p class="text-sm text-text-secondary mt-1">{{ __('rules.modal_subtitle') }}</p>
                </div>
                <button type="button" data-modal-close="rule-modal" class="p-2 rounded-lg hover:bg-[#232f48] text-text-secondary hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
             <!-- body -->
            <form id="rule-form" class="px-6 py-5 space-y-5">
                <input type="hidden" id="rule-id" value="">
                <div>
                     <label class="block text-sm font-medium text-gray-200 mb-2">{{ __('rules.label_trigger') }}</label>
                     <input id="rule-trigger" type="text" placeholder="{{ __('rules.placeholder_trigger') }}" class="w-full rounded-lg border border-[#232f48] bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent placeholder-text-secondary/50">
                     <p class="text-xs text-text-secondary mt-1.5">{{ __('rules.help_trigger') }}</p>
                     <p id="err-trigger" class="text-xs text-red-400 mt-1 hidden"></p>
                </div>
                 
                 <div class="grid grid-cols-2 gap-4">
                     <div>
                         <label class="block text-sm font-medium text-gray-200 mb-2">{{ __('rules.label_match_type') }}</label>
                        <select id="rule-match-type" class="w-full rounded-lg border border-[#232f48] bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="contains">Contains (Default)</option>
                            <option value="exact">Exact Match</option>
                            <option value="regex">Regex Pattern</option>
                        </select>
                     </div>
                     <div>
                         <label class="block text-sm font-medium text-gray-200 mb-2">{{ __('rules.label_priority') }}</label>
                        <input id="rule-priority" type="number" value="0" min="0" class="w-full rounded-lg border border-[#232f48] bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary">
                     </div>
                 </div>

                 <div>
                      <label class="block text-sm font-medium text-gray-200 mb-2">{{ __('rules.label_reply') }}</label>
                      <textarea id="rule-reply" rows="5" placeholder="{{ __('rules.placeholder_reply') }}" class="w-full rounded-lg border border-[#232f48] bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary placeholder-text-secondary/50"></textarea>
                      <p id="err-reply" class="text-xs text-red-400 mt-1 hidden"></p>
                 </div>

                 <div class="flex items-center gap-2">
                     <input id="rule-active" type="checkbox" checked class="rounded bg-[#111722] border-[#232f48] text-primary focus:ring-primary">
                     <label for="rule-active" class="text-sm text-gray-200">{{ __('rules.label_active') }}</label>
                 </div>
            </form>
             <!-- footer -->
             <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-[#232f48] bg-[#111722]/30 rounded-b-2xl">
                 <button type="button" data-modal-close="rule-modal" class="px-5 py-2.5 rounded-lg border border-[#232f48] text-gray-300 hover:text-white hover:bg-[#232f48] text-sm font-medium transition-colors">{{ __('rules.button_cancel') }}</button>
                 <button type="submit" form="rule-form" id="btn-save-rule" class="px-5 py-2.5 rounded-lg bg-primary hover:bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-900/20 transition-all">{{ __('rules.button_save') }}</button>
             </div>
        </div>
    </div>
</div>

{{-- ================= MODAL DELETE ================= --}}
<div id="delete-modal" class="hidden fixed inset-0 z-50">
    <div data-modal-close="delete-modal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="absolute inset-0 flex items-center justify-center p-0 md:p-4">
        <div class="w-full max-w-sm rounded-2xl bg-[#1e293b] border border-[#232f48] shadow-2xl p-6 text-center modal-content-mobile">
            <div class="size-14 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">delete_forever</span>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">{{ __('rules.delete_title') }}</h3>
            <p class="text-sm text-text-secondary mb-6">{{ __('rules.delete_description') }}</p>
            <input type="hidden" id="delete-id" value="">
            <div class="flex gap-3 justify-center">
                <button type="button" data-modal-close="delete-modal" class="px-5 py-2.5 rounded-lg border border-[#232f48] text-gray-300 hover:text-white hover:bg-[#232f48] text-sm font-medium transition-colors">{{ __('rules.button_cancel') }}</button>
                <button type="button" id="btn-confirm-delete" class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-bold shadow-lg shadow-red-900/20 transition-all">{{ __('rules.delete_confirm') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="hidden fixed bottom-5 right-5 z-[60] rounded-xl bg-[#1e293b] border border-[#232f48] px-4 py-3 text-sm text-white shadow-xl flex items-center gap-3">
    <span class="material-symbols-outlined text-green-500">check_circle</span>
    <span id="toast-msg">Success</span>
</div>

<script>
const LANG = {
    modal_create_title: "{{ __('rules.modal_create_title') }}",
    modal_edit_title: "{{ __('rules.modal_edit_title') }}",
    button_save: "{{ __('rules.button_save') }}",
    button_update: "{{ __('rules.button_update') }}",
    button_saving: "{{ __('rules.button_saving') }}",
    success_status: "{{ __('rules.success_status') }}",
    success_created: "{{ __('rules.success_created') }}",
    success_updated: "{{ __('rules.success_updated') }}",
    success_deleted: "{{ __('rules.success_deleted') }}",
    error_status: "{{ __('rules.error_status') }}",
    error_generic: "{{ __('rules.error_generic') }}",
    error_delete: "{{ __('rules.error_delete') }}",
    deleting: "{{ __('rules.delete_cancelling') }}",
};

(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const container = document.getElementById('rules-container'); // Ganti tbody
  const totalEl = document.getElementById('rules-total');
  const emptyRow = document.getElementById('rules-empty');
  
  // Toast
  const toastEl = document.getElementById('toast');
  const toastMsg = document.getElementById('toast-msg');
  let toastTimer = null;
  function toast(msg){
    toastMsg.textContent = msg;
    toastEl.classList.remove('hidden');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 2000);
  }

  // Modal Handling
  document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('#btn-open-create, #btn-empty-create'); // Both create buttons
    const closeBtn = e.target.closest('[data-modal-close]');
    
    if (openBtn) {
        resetForm();
        document.getElementById('rule-modal').classList.remove('hidden');
    }
    if (closeBtn) {
        document.getElementById(closeBtn.getAttribute('data-modal-close')).classList.add('hidden');
    }
  });

  function openModal(id){ document.getElementById(id)?.classList.remove('hidden'); }
  function closeModal(id){ document.getElementById(id)?.classList.add('hidden'); }

  // Form Refs
  const form = document.getElementById('rule-form');
  const ruleId = document.getElementById('rule-id');
  const fTrigger = document.getElementById('rule-trigger');
  const fReply = document.getElementById('rule-reply');
  const fMatchType = document.getElementById('rule-match-type');
  const fPriority = document.getElementById('rule-priority');
  const fActive = document.getElementById('rule-active');
  const btnSave = document.getElementById('btn-save-rule');
  
  // Errors
  const errTrigger = document.getElementById('err-trigger');
  const errReply = document.getElementById('err-reply');

  function clearErrors(){
      [errTrigger, errReply].forEach(el => { el.classList.add('hidden'); el.textContent=''; });
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
      if(fMatchType) fMatchType.value = 'contains';
      clearErrors();
      document.getElementById('rule-modal-title').textContent = LANG.modal_create_title;
      btnSave.textContent = LANG.button_save;
  }

  // SEARCH FILTER
  const searchInput = document.getElementById('rules-search');
  searchInput?.addEventListener('input', (e) => {
      const q = e.target.value.toLowerCase();
      const rows = Array.from(container.querySelectorAll('.group[data-id]'));
      let visible = 0;
      
      rows.forEach(row => {
          const content = (row.dataset.trigger + ' ' + row.dataset.reply).toLowerCase();
          if(content.includes(q)){
              row.style.display = '';
              visible++;
          } else {
              row.style.display = 'none';
          }
      });
      if(totalEl) totalEl.textContent = visible;
  });

  // DELEGATION
  container.addEventListener('click', async (e) => {
      const editBtn = e.target.closest('[data-action="edit"]');
      const deleteBtn = e.target.closest('[data-action="delete"]');
      const toggleWrapper = e.target.closest('[data-action="toggle"]'); // Wrapper div for checkbox

      if(editBtn){
          const row = editBtn.closest('.group');
          resetForm();
          document.getElementById('rule-modal-title').textContent = LANG.modal_edit_title;
          btnSave.textContent = LANG.button_update;
          
          ruleId.value = row.dataset.id;
          fTrigger.value = row.dataset.trigger;
          fReply.value = row.dataset.reply;
          fPriority.value = row.dataset.priority;
          fActive.checked = row.dataset.active === '1';
          if(fMatchType) fMatchType.value = row.dataset.matchType;
          
          openModal('rule-modal');
      }

      if(deleteBtn){
          const row = deleteBtn.closest('.group');
          document.getElementById('delete-id').value = row.dataset.id;
          openModal('delete-modal');
      }
      
      // Handle Toggle Click (on the wrapper, to avoid double event with checkbox)
      if(toggleWrapper){
          const checkbox = toggleWrapper.querySelector('input[type="checkbox"]');
          // e.preventDefault(); // Don't prevent default, let checkbox change visually first or handle logic
          // Actually, checkbox inside label/div behavior is tricky.
          // Let's rely on change event of the checkbox itself if possible, but delegation is on container.
      }
  });

  // Better toggle handling with change event on container
  container.addEventListener('change', async (e) => {
      if(e.target.classList.contains('toggle-checkbox')){
          const checkbox = e.target;
          const row = checkbox.closest('.group');
          const id = row.dataset.id;
          
          checkbox.disabled = true;
          try {
             // AJAX TOGGLE
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
             
             // Replace Row (karena _row blade sekarang return Div Card)
             const temp = document.createElement('div');
             temp.innerHTML = data.rowHtml.trim();
             const newRow = temp.firstElementChild;
             row.replaceWith(newRow);
             toast(LANG.success_status);
          } catch(err){
              console.error(err);
              toast(LANG.error_status);
              checkbox.checked = !checkbox.checked; // Revert
              checkbox.disabled = false;
          }
      }
  });

  // SUBMIT FORM
  form.addEventListener('submit', async function(e){
      e.preventDefault();
      clearErrors();
      
      const id = ruleId.value;
      const isEdit = !!id;
      const url = isEdit ? `/rules/${id}` : `/rules`;
      const method = isEdit ? 'PATCH' : 'POST';
      
      const payload = {
        trigger_keyword: fTrigger.value.trim(),
        response_text: fReply.value.trim(),
        match_type: fMatchType.value,
        priority: fPriority.value,
        is_active: fActive.checked ? 1 : 0
      };

      btnSave.disabled = true;
      btnSave.textContent = LANG.button_saving;

      try {
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

          if(res.status === 422){
              const v = await res.json();
              if(v.errors?.trigger_keyword) setError(errTrigger, v.errors.trigger_keyword[0]);
              if(v.errors?.response_text) setError(errReply, v.errors.response_text[0]);
              btnSave.textContent = isEdit ? LANG.button_update : LANG.button_save;
              return;
          }

          const data = await res.json();
          if(!data.ok) throw data;

          const temp = document.createElement('div');
          temp.innerHTML = data.rowHtml.trim();
          const newRow = temp.firstElementChild;

          if(isEdit){
              document.getElementById(`rule-row-${id}`)?.replaceWith(newRow);
              toast(LANG.success_updated);
          } else {
              if(emptyRow) emptyRow.remove();
              // Prepend to make it look like newest first (if controller sorts that way)
              container.insertBefore(newRow, container.firstChild);
              toast(LANG.success_created);
              if(totalEl) totalEl.textContent = Number(totalEl.textContent) + 1;
          }
          closeModal('rule-modal');

      } catch(err){
          console.error(err);
          toast(LANG.error_generic);
      } finally {
          btnSave.disabled = false;
          btnSave.textContent = isEdit ? LANG.button_update : LANG.button_save;
      }
  });
  
  // CONFIRM DELETE
  document.getElementById('btn-confirm-delete').addEventListener('click', async () => {
      const id = document.getElementById('delete-id').value;
      const btn = document.getElementById('btn-confirm-delete');
      btn.disabled = true;
      btn.textContent = LANG.deleting;
      
      try {
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
          toast(LANG.success_deleted);
          if(totalEl) totalEl.textContent = Math.max(0, Number(totalEl.textContent) - 1);
          
      } catch(err){
          console.error(err);
          toast(LANG.error_delete);
      } finally {
          btn.disabled = false;
          btn.textContent = LANG.button_update; // fallback
          closeModal('delete-modal');
      }
  });

})();
</script>
</body>
</html>
