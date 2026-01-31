@props(['type' => 'received', 'text', 'time'])

<div class="flex {{ $type === 'sent' ? 'justify-end' : 'justify-start' }} w-full group animate-in fade-in slide-in-from-bottom-2 duration-300">
    <div class="flex flex-col max-w-[85%] lg:max-w-[70%] {{ $type === 'sent' ? 'items-end' : 'items-start' }}">
        <!-- Bubble (Strict rounded-xl as requested) -->
        <div class="p-4 {{ $type === 'sent' ? 'bg-blue-600 text-white rounded-2xl rounded-tr-none shadow-lg shadow-blue-600/10' : 'bg-gray-800 text-gray-200 rounded-2xl rounded-tl-none border border-gray-700/50' }}">
            <p class="text-[13px] leading-relaxed whitespace-pre-wrap">{{ $text }}</p>
        </div>
        
        <!-- Metadata -->
        <div class="mt-1.5 px-1 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">{{ $time }}</span>
            @if($type === 'sent')
                <span class="material-symbols-outlined text-blue-500 text-xs">done_all</span>
            @endif
        </div>
    </div>
</div>
