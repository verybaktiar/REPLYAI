{{-- 
    Page Help Component - Modal Style (tidak terpotong)
    Usage: @include('components.page-help', ['title' => 'Judul', 'description' => 'Deskripsi', 'tips' => ['Tip 1', 'Tip 2']])
--}}

@props(['title' => 'Bantuan', 'description' => '', 'tips' => []])

<div x-data="{ showHelp: false }" class="inline-block">
    {{-- Help Button --}}
    <button 
        @click="showHelp = true"
        class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 hover:text-blue-300 transition-all duration-200"
        title="Bantuan"
    >
        <span class="text-xs font-bold">?</span>
    </button>
    
    {{-- Modal Overlay --}}
    <div 
        x-show="showHelp"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/50"
        @click="showHelp = false"
        x-cloak
    >
        {{-- Modal Box --}}
        <div 
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="w-full max-w-md bg-gray-800 border border-gray-600 rounded-2xl shadow-2xl overflow-hidden"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 bg-gray-700/50 border-b border-gray-600">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400">
                        <span class="material-symbols-outlined">help</span>
                    </span>
                    <h3 class="text-white font-bold">{{ $title }}</h3>
                </div>
                <button @click="showHelp = false" class="text-gray-400 hover:text-white p-1 hover:bg-gray-600 rounded-lg transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            {{-- Body --}}
            <div class="px-5 py-4 max-h-[60vh] overflow-y-auto">
                {{-- Description --}}
                @if($description)
                <div class="mb-4 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                    <p class="text-gray-200 text-sm leading-relaxed">{{ $description }}</p>
                </div>
                @endif
                
                {{-- Tips --}}
                @if(count($tips) > 0)
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">📝 Cara Penggunaan:</p>
                    <div class="space-y-2">
                        @foreach($tips as $index => $tip)
                        <div class="flex items-start gap-3 text-sm">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-green-500/20 text-green-400 text-xs font-bold shrink-0">{{ $index + 1 }}</span>
                            <span class="text-gray-300">{{ $tip }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            
            {{-- Footer --}}
            <div class="px-5 py-3 bg-gray-700/30 border-t border-gray-600">
                <button @click="showHelp = false" class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors">
                    Mengerti 👍
                </button>
            </div>
        </div>
    </div>
</div>
