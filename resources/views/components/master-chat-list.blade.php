<div 
    class="w-full lg:w-80 flex-shrink-0 flex flex-col border-r border-gray-800 bg-gray-950"
    :class="activeView === 'master' ? 'flex' : 'hidden lg:flex'"
>
    <!-- Header (h-16 consistent) -->
    <div class="h-16 flex-shrink-0 flex items-center justify-between px-4 border-b border-gray-800">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="p-2 -ml-2 text-gray-400 hover:text-white lg:hidden">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h2 class="text-lg font-black tracking-tight text-white italic">Inbox</h2>
        </div>
        <div class="flex items-center gap-1">
            <button class="p-2 text-gray-500 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">search</span>
            </button>
            <button class="p-2 text-gray-500 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">more_vert</span>
            </button>
        </div>
    </div>

    <!-- Search/Filter Bar (Optional but common in Enterprise) -->
    <div class="p-4 border-b border-gray-800/50">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide py-1">
            <span class="px-3 py-1 bg-blue-600 text-white text-[10px] font-bold rounded-full whitespace-nowrap cursor-pointer">All</span>
            <span class="px-3 py-1 bg-gray-900 border border-gray-800 text-gray-500 text-[10px] font-bold rounded-full whitespace-nowrap cursor-pointer hover:border-gray-700 hover:text-gray-300 transition-all">Unread</span>
            <span class="px-3 py-1 bg-gray-900 border border-gray-800 text-gray-500 text-[10px] font-bold rounded-full whitespace-nowrap cursor-pointer hover:border-gray-700 hover:text-gray-300 transition-all">WhatsApp</span>
        </div>
    </div>

    <!-- Chat List Area (Scrollable) -->
    <div class="flex-1 overflow-y-auto divide-y divide-gray-800/30 scroll-smooth">
        {{ $slot }}
    </div>
</div>
