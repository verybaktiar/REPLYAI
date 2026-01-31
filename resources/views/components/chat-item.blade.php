@props(['name', 'message', 'time', 'active' => false, 'unread' => false])

<div 
    @click="activeView = 'detail'"
    {{ $attributes->merge(['class' => 'flex items-center gap-4 p-4 cursor-pointer transition-all duration-200 border-l-4 ' . ($active ? 'bg-gray-800/80 border-blue-600' : 'hover:bg-gray-800/30 border-transparent hover:border-gray-800') . ' group']) }}
>
    <!-- Avatar (z-index implicit, bg-gray-900 elevated) -->
    <div class="relative flex-shrink-0">
        <div class="size-11 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center text-blue-500 font-bold group-hover:scale-105 transition-transform">
            {{ substr($name, 0, 1) }}
        </div>
        @if($active)
            <div class="absolute -bottom-0.5 -right-0.5 size-3.5 bg-green-500 rounded-full border-2 border-gray-900 shadow-sm"></div>
        @endif
    </div>

    <!-- Content (min-w-0 for truncate stability) -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between mb-1">
            <h4 class="text-sm font-bold text-white truncate">{{ $name }}</h4>
            <span class="text-[10px] font-medium text-gray-500 transition-colors group-hover:text-gray-400">{{ $time }}</span>
        </div>
        <div class="flex items-center justify-between gap-2 text-xs">
            <p class="text-gray-400 truncate flex-1 leading-snug">{{ $message }}</p>
            @if($unread)
                <div class="size-2 rounded-full bg-blue-600 shrink-0 shadow-[0_0_8px_rgba(37,99,235,0.4)]"></div>
            @endif
        </div>
    </div>
</div>
