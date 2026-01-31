<div 
    class="flex-1 min-w-0 flex flex-col bg-gray-950 h-full"
    :class="activeView === 'detail' ? 'flex' : 'hidden lg:flex'"
>
    <!-- Header (h-16 consistent with sidebar and master) -->
    <!-- backdrop-blur for premium feel, sticky top-0 ensures it stays -->
    <div class="h-16 flex-shrink-0 flex items-center justify-between px-6 border-b border-gray-800 bg-gray-950/50 backdrop-blur-md sticky top-0 z-20">
        <div class="flex items-center gap-4 min-w-0">
            <!-- Mobile Back Button: Toggles back to master view -->
            <button @click="activeView = 'master'" class="p-2 -ml-2 text-gray-400 hover:text-white lg:hidden">
                <span class="material-symbols-outlined text-[24px]">arrow_back</span>
            </button>
            
            <div class="size-10 rounded-full bg-gray-900 flex-shrink-0 border border-gray-800 flex items-center justify-center text-blue-500 font-bold">
                {{ $avatar ?? 'C' }}
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-white truncate">{{ $name ?? 'Pilih Percakapan' }}</h3>
                @if(!($empty ?? false))
                    <div class="flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="text-[10px] text-gray-500 font-medium">Aktif</span>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button class="p-2 text-gray-500 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">videocam</span>
            </button>
            <button class="p-2 text-gray-500 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">call</span>
            </button>
            <div class="w-px h-4 bg-gray-800 mx-1"></div>
            <button class="p-2 text-gray-500 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">more_vert</span>
            </button>
        </div>
    </div>

    <!-- Message Area (Scrollable: flex-1 + overflow-y-auto) -->
    <div class="flex-1 overflow-y-auto p-4 lg:p-6 space-y-4 scroll-smooth">
        @if($empty ?? false)
            {{-- Empty State Illustration --}}
            <div class="h-full flex flex-col items-center justify-center text-center p-8">
                <div class="size-20 rounded-full bg-gray-900 flex items-center justify-center mb-4 text-gray-700 border border-gray-800">
                    <span class="material-symbols-outlined text-[40px]">forum</span>
                </div>
                <h3 class="text-sm font-bold text-gray-400">Pilih pesan untuk memulai</h3>
                <p class="text-[11px] text-gray-600 mt-1 max-w-[200px]">Pilih chat dari daftar di sebelah kiri untuk melihat detail percakapan.</p>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>

    <!-- Input Area (Fixed/Sticky at the bottom) -->
    <div class="flex-shrink-0 p-4 border-t border-gray-800 bg-gray-950">
        <div class="max-w-4xl mx-auto flex items-end gap-3">
            <div class="flex-1 bg-gray-900 border border-gray-800 rounded-xl px-4 py-2 flex items-end gap-3 focus-within:border-blue-600/50 focus-within:ring-1 focus-within:ring-blue-600/20 transition-all group">
                <button class="p-1 mb-1 text-gray-500 hover:text-blue-500 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                </button>
                <textarea 
                    rows="1" 
                    placeholder="Tulis pesan..."
                    class="flex-1 bg-transparent border-none focus:ring-0 text-sm text-white resize-none py-1 scrollbar-hide min-h-[36px] max-h-[120px] placeholder:text-gray-600"
                    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                ></textarea>
                <button class="p-1 mb-1 text-gray-500 hover:text-yellow-500 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">mood</span>
                </button>
            </div>
            <button class="shrink-0 size-11 bg-blue-600 text-white rounded-xl flex items-center justify-center hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                <span class="material-symbols-outlined text-[22px] filled">send</span>
            </button>
        </div>
    </div>
</div>
