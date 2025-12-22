@extends('layouts.app')

@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
        Auto Reply Logs
      </h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Riwayat bot membaca pesan dan mengirim balasan.
      </p>
    </div>
    <div class="text-xs text-gray-500">
      Total: {{ $logs->count() }}
    </div>
  </div>

  {{-- ✅ FILTER BAR (AUTO SEARCH) --}}
  <form id="logFilterForm" method="GET" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm p-4">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">

      {{-- source --}}
      <div>
        <label class="text-xs text-gray-500">Source</label>
        <select
          name="source"
          class="w-full mt-1 rounded-lg border-gray-200 dark:border-gray-800 dark:bg-gray-900 text-sm"
          data-auto-submit
        >
          <option value="">All</option>
          <option value="manual" @selected(($filters['source'] ?? '')==='manual')>Manual</option>
          <option value="ai" @selected(($filters['source'] ?? '')==='ai')>AI</option>
        </select>
      </div>

      {{-- status --}}
      <div>
        <label class="text-xs text-gray-500">Status</label>
        <select
          name="status"
          class="w-full mt-1 rounded-lg border-gray-200 dark:border-gray-800 dark:bg-gray-900 text-sm"
          data-auto-submit
        >
          <option value="">All</option>
          <option value="sent" @selected(($filters['status'] ?? '')==='sent')>sent</option>
          <option value="sent_ai" @selected(($filters['status'] ?? '')==='sent_ai')>sent_ai</option>
          <option value="skipped" @selected(($filters['status'] ?? '')==='skipped')>skipped</option>
          <option value="skipped_ai" @selected(($filters['status'] ?? '')==='skipped_ai')>skipped_ai</option>
          <option value="failed" @selected(($filters['status'] ?? '')==='failed')>failed</option>
          <option value="failed_ai" @selected(($filters['status'] ?? '')==='failed_ai')>failed_ai</option>
        </select>
      </div>

      {{-- min confidence --}}
      <div>
        <label class="text-xs text-gray-500">Min Confidence (AI)</label>
        <input
          type="number"
          name="min_conf"
          step="0.01"
          min="0"
          max="1"
          value="{{ $filters['min_conf'] ?? '' }}"
          placeholder="contoh: 0.55"
          class="w-full mt-1 rounded-lg border-gray-200 dark:border-gray-800 dark:bg-gray-900 text-sm"
          data-auto-submit
        />
      </div>

      {{-- search --}}
      <div class="md:col-span-2">
        <label class="text-xs text-gray-500">Search</label>
        <input
          type="text"
          name="search"
          id="searchInput"
          value="{{ $filters['search'] ?? '' }}"
          placeholder="cari trigger / response / error..."
          class="w-full mt-1 rounded-lg border-gray-200 dark:border-gray-800 dark:bg-gray-900 text-sm"
          autocomplete="off"
        />
      </div>

      {{-- limit --}}
      <div>
        <label class="text-xs text-gray-500">Limit</label>
        <select
          name="limit"
          class="w-full mt-1 rounded-lg border-gray-200 dark:border-gray-800 dark:bg-gray-900 text-sm"
          data-auto-submit
        >
          @foreach([50,100,200,500,1000] as $l)
            <option value="{{ $l }}" @selected(($filters['limit'] ?? 200)==$l)>{{ $l }}</option>
          @endforeach
        </select>
      </div>

    </div>

    {{-- hanya reset --}}
    <div class="flex items-center gap-2 mt-4">
      <a href="{{ url()->current() }}"
         class="px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-sm">
        Reset
      </a>
      <span class="text-xs text-gray-400">Filter otomatis diterapkan</span>
    </div>
  </form>


  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800">
            <th class="px-5 py-3 w-14">#</th>
            <th class="px-5 py-3 w-40">Conversation</th>
            <th class="px-5 py-3">Trigger Text (User)</th>
            <th class="px-5 py-3">Matched Rule</th>
            <th class="px-5 py-3">Response Text (Bot)</th>

            <th class="px-5 py-3 w-20">Source</th>
            <th class="px-5 py-3 w-24">Confidence</th>
            <th class="px-5 py-3 w-28">KB Sources</th>

            <th class="px-5 py-3 w-28">Status</th>
            <th class="px-5 py-3 w-40">Time</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

          @forelse($logs as $i => $log)
            @php
              $convName = data_get($log, 'conversation.display_name')
                        ?? data_get($log, 'conversation.ig_username')
                        ?? 'Conversation #' . ($log->conversation_id ?? '-');

              $ruleTrigger = data_get($log, 'rule.trigger')
                            ?? data_get($log, 'rule.trigger_keyword')
                            ?? '-';

              $rulePriority = data_get($log, 'rule.priority', 0);

              $status = $log->status ?? 'unknown';
              $time = $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-';

              $responseSource = $log->response_source ?: ($log->ai_used ? 'ai' : 'manual');
              $confidence = $log->ai_confidence;
              $sources = $log->ai_sources;
            @endphp

            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
              <td class="px-5 py-4 text-gray-500 dark:text-gray-400">
                {{ $i + 1 }}
              </td>

              <td class="px-5 py-4 font-medium text-gray-900 dark:text-white">
                {{ $convName }}
              </td>

              <td class="px-5 py-4 text-gray-800 dark:text-gray-200 whitespace-pre-line">
                {{ $log->trigger_text ?? '-' }}
              </td>

              <td class="px-5 py-4">
                <div class="font-medium text-gray-900 dark:text-white">
                  {{ $ruleTrigger }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  priority: {{ $rulePriority }}
                </div>
              </td>

              <td class="px-5 py-4 text-gray-800 dark:text-gray-200 whitespace-pre-line">
                {{ $log->response_text ?? '-' }}
              </td>

              {{-- Source --}}
              <td class="px-5 py-4">
                @if($responseSource === 'ai')
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                    AI
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                    Manual
                  </span>
                @endif
              </td>

              {{-- Confidence --}}
              <td class="px-5 py-4 text-xs text-gray-700 dark:text-gray-200">
                @if($responseSource === 'ai' && $confidence !== null)
                  {{ number_format((float)$confidence, 2) }}
                @else
                  -
                @endif
              </td>

              {{-- KB Sources --}}
              <td class="px-5 py-4 text-xs">
                @if($responseSource === 'ai' && !empty($sources))
                  <button
                    class="text-indigo-600 hover:underline"
                    data-sources='@json($sources)'
                    onclick="openSourcesModal(this)"
                    type="button"
                  >
                    Lihat
                  </button>
                @else
                  -
                @endif
              </td>

              {{-- status --}}
              <td class="px-5 py-4">
                @if($status === 'sent' || $status === 'sent_ai')
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    {{ $status }}
                  </span>

                @elseif($status === 'skipped' || $status === 'skipped_ai')
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                    {{ $status }}
                  </span>

                @elseif($status === 'failed' || $status === 'failed_ai')
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    {{ $status }}
                  </span>
                  @if($log->error_message)
                    <div class="text-[10px] text-red-600 mt-1 whitespace-pre-line">
                      {{ \Illuminate\Support\Str::limit($log->error_message, 80) }}
                    </div>
                  @endif

                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-200">
                    {{ $status }}
                  </span>
                @endif
              </td>

              {{-- time --}}
              <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                {{ $time }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="px-5 py-8 text-center text-gray-500">
                Log masih kosong. Jalankan bot dulu.
              </td>
            </tr>
          @endforelse

        </tbody>
      </table>
    </div>
  </div>

</div>


{{-- MODAL KB SOURCES --}}
<div id="sourcesModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" onclick="closeSourcesModal()"></div>

  <div class="relative mx-auto mt-20 w-full max-w-xl bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-5">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">KB Sources</h2>
      <button class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-200" onclick="closeSourcesModal()">✕</button>
    </div>

    <div id="sourcesContent" class="space-y-3 max-h-[60vh] overflow-auto text-sm"></div>
  </div>
</div>

<script>
/* =========================
   Auto-submit filter form
   ========================= */
(function () {
  const form = document.getElementById('logFilterForm');
  if (!form) return;

  let debounceTimer = null;

  // select + number auto submit (debounced)
  form.querySelectorAll('[data-auto-submit]').forEach(el => {
    const evt = el.tagName === 'INPUT' ? 'input' : 'change';
    el.addEventListener(evt, () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => form.submit(), 300);
    });
  });

  // search input debounce submit
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => form.submit(), 450);
    });
  }
})();

/* =========================
   Modal sources
   ========================= */
function openSourcesModal(btn) {
  const modal = document.getElementById('sourcesModal');
  const content = document.getElementById('sourcesContent');

  let sources = [];
  try { sources = JSON.parse(btn.dataset.sources || '[]'); } catch(e) {}

  if (!sources.length) {
    content.innerHTML = `<div class="text-gray-500">Tidak ada sources.</div>`;
  } else {
    content.innerHTML = sources.map((s, i) => {
      const title = s.title || '(Tanpa judul)';
      const url = s.source_url || '-';
      return `
        <div class="p-3 border border-gray-200 dark:border-gray-800 rounded-xl">
          <div class="font-medium text-gray-900 dark:text-white">${i+1}. ${title}</div>
          <div class="text-xs text-gray-500 break-all mt-1">${url}</div>
        </div>
      `;
    }).join('');
  }

  modal.classList.remove('hidden');
}
function closeSourcesModal() {
  document.getElementById('sourcesModal').classList.add('hidden');
}
</script>
@endsection
