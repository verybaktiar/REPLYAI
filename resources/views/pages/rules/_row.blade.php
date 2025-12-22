@php
  // fallback field lama kalau field baru masih kosong
  $trigger = $rule->trigger
            ?? $rule->trigger_keyword
            ?? '';

  $reply   = $rule->reply
            ?? $rule->response_text
            ?? '';

  $priority = $rule->priority ?? 0;
  $active = (bool)($rule->is_active ?? false);
  $created = $rule->created_at ? $rule->created_at->format('d M Y H:i') : '-';
@endphp

<tr
  id="rule-row-{{ $rule->id }}"
  data-id="{{ $rule->id }}"
  data-trigger="{{ e($trigger) }}"
  data-reply="{{ e($reply) }}"
  data-priority="{{ $priority }}"
  data-active="{{ $active ? '1' : '0' }}"
  data-created-at="{{ $rule->created_at?->toIso8601String() }}"
  class="hover:bg-gray-50 dark:hover:bg-gray-800/40"
>
  <td class="px-5 py-4 text-gray-500 dark:text-gray-400" data-cell="num">
    {{ ($i ?? 0) + 1 }}
  </td>

  {{-- Trigger --}}
  <td class="px-5 py-4">
    <div class="font-medium text-gray-900 dark:text-white">
      {{ $trigger !== '' ? $trigger : '-' }}
    </div>
    <div class="text-xs text-gray-500 mt-1">
      Match: contains keyword
    </div>

    {{-- Actions --}}
    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs">
      <button
        type="button"
        data-action="toggle"
        class="px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        {{ $active ? 'Disable' : 'Enable' }}
      </button>

      <button
        type="button"
        data-action="edit"
        class="px-2 py-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Edit
      </button>

      <button
        type="button"
        data-action="delete"
        class="px-2 py-1 rounded-md border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-200 hover:bg-red-100 dark:hover:bg-red-900/30"
      >
        Delete
      </button>
    </div>
  </td>

  {{-- Reply --}}
  <td class="px-5 py-4">
    <div class="text-gray-800 dark:text-gray-200 whitespace-pre-line">
      {{ $reply !== '' ? \Illuminate\Support\Str::limit($reply, 180) : '-' }}
    </div>
  </td>

  {{-- Priority (INLINE EDIT ENABLED) --}}
  <td class="px-5 py-4">
    <span
      data-action="edit-priority"
      title="Klik untuk edit priority"
      class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold cursor-pointer
        {{ $priority >= 5 ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}"
    >
      {{ $priority }}
    </span>
  </td>

  {{-- Status --}}
  <td class="px-5 py-4">
    @if($active)
      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200">
        <span class="h-2 w-2 rounded-full bg-green-500"></span>
        Active
      </span>
    @else
      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
        <span class="h-2 w-2 rounded-full bg-gray-400"></span>
        Inactive
      </span>
    @endif
  </td>

  {{-- Created --}}
  <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
    {{ $created }}
  </td>
</tr>
