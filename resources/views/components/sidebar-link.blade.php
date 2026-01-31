@props(['icon', 'label', 'active' => false, 'href' => '#'])

<a 
    href="{{ $href }}" 
    @click="sidebarOpen = false"
    {{ $attributes->merge(['class' => 'flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 ' . ($active ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-400 hover:bg-gray-800/50 hover:text-white group')]) }}
>
    <span class="material-symbols-outlined text-[20px] transition-transform group-hover:scale-110 {{ $active ? 'filled' : '' }}">
        {{ $icon }}
    </span>
    <span>{{ $label }}</span>
</a>
