@php
  // fallback field
  $trigger = $rule->trigger ?? $rule->trigger_keyword ?? '';
  $reply   = $rule->reply ?? $rule->response_text ?? '';
  $priority = $rule->priority ?? 0;
  $active = (bool)($rule->is_active ?? false);
  $matches = $rule->match_type ?? 'contains';
  $created = $rule->created_at ? $rule->created_at->diffForHumans() : '-';
@endphp

<!-- Card Row -->
<div 
  class="group flex flex-col md:grid md:grid-cols-12 gap-4 items-center bg-[#232f48] hover:bg-[#2a3652] transition-colors p-4 rounded-xl border border-transparent hover:border-primary/20 shadow-sm"
  id="rule-row-{{ $rule->id }}"
  data-id="{{ $rule->id }}"
  data-trigger="{{ e($trigger) }}"
  data-reply="{{ e($reply) }}"
  data-priority="{{ $priority }}"
  data-active="{{ $active ? '1' : '0' }}"
  data-match-type="{{ $matches }}"
  data-created-at="{{ $rule->created_at?->toIso8601String() }}"
>
    <!-- Info -->
    <div class="col-span-4 flex items-center gap-4 w-full">
        <div class="size-12 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0 text-blue-500">
            <span class="material-symbols-outlined">smart_toy</span>
        </div>
        <div class="flex flex-col min-w-0">
            <h3 class="text-white font-semibold text-base truncate" title="{{ $trigger }}">{{ Str::limit($trigger, 25) }}</h3>
            <p class="text-[#92a4c9] text-xs truncate">ID: #RULE-{{ $rule->id }} • {{ $created }}</p>
        </div>
    </div>

    <!-- Platform -->
    <div class="col-span-2 w-full flex items-center gap-2">
        <div class="flex items-center gap-1.5 px-2 py-1 rounded bg-[#111722] border border-[#232f48]">
            <span class="size-2 rounded-full bg-green-500"></span>
            <span class="text-xs font-medium text-gray-300">WA</span>
        </div>
        <div class="flex items-center gap-1.5 px-2 py-1 rounded bg-[#111722] border border-[#232f48]">
            <span class="size-2 rounded-full bg-pink-500"></span>
            <span class="text-xs font-medium text-gray-300">IG</span>
        </div>
    </div>

    <!-- Dept (Match Type) -->
    <div class="col-span-2 w-full flex items-center">
        <span class="px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-400 text-xs font-medium border border-purple-500/20 uppercase">
            {{ $matches }}
        </span>
    </div>

    <!-- Status Toggle -->
    <div class="col-span-2 w-full flex items-center">
        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in cursor-pointer" data-action="toggle">
            <input type="checkbox" name="toggle" id="toggle-{{ $rule->id }}" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer border-[#232f48] checked:border-primary transition-all duration-300" {{ $active ? 'checked' : '' }}/>
            <label for="toggle-{{ $rule->id }}" class="toggle-label block overflow-hidden h-5 rounded-full bg-[#111722] cursor-pointer border border-[#232f48]"></label>
        </div>
        <span class="text-xs font-medium {{ $active ? 'text-white' : 'text-[#92a4c9]' }} ml-2">
            {{ $active ? __('rules.status_active', ['default' => 'Aktif']) : __('rules.status_inactive', ['default' => 'Nonaktif']) }}
        </span>
    </div>

    <!-- Actions -->
    <div class="col-span-2 w-full flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
        <button class="p-2 rounded-lg text-[#92a4c9] hover:text-white hover:bg-[#111722] transition-colors" data-action="edit" title="Edit Rule">
            <span class="material-symbols-outlined text-[20px]">edit</span>
        </button>
        <button class="p-2 rounded-lg text-[#92a4c9] hover:text-red-400 hover:bg-[#111722] transition-colors" data-action="delete" title="Hapus Rule">
            <span class="material-symbols-outlined text-[20px]">delete</span>
        </button>
    </div>
</div>
