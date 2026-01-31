<div class="flex-1 flex flex-col min-h-0 bg-gray-900">
    <!-- Sidebar Header (h-16 consistent with all headers) -->
    <div class="h-16 flex-shrink-0 flex items-center px-6 border-b border-gray-800">
        <div class="flex items-center gap-3">
            <div class="size-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-600/20">
                <span class="text-white font-black text-lg">R</span>
            </div>
            <h1 class="text-lg font-bold tracking-tight text-white">ReplyAI</h1>
        </div>
    </div>

    <!-- Navigation Area (Scrollable) -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-1 scrollbar-hide">
        <x-sidebar-link icon="chat" label="All Chats" active="true" />
        <x-sidebar-link icon="smart_toy" label="AI Configuration" />
        <x-sidebar-link icon="campaign" label="Broadcasts" />
        <x-sidebar-link icon="group" label="Contacts" />
        
        <div class="pt-4 pb-2 px-4">
            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Automation</span>
        </div>
        
        <x-sidebar-link icon="rule" label="Auto Reply Rules" />
        <x-sidebar-link icon="menu_book" label="Knowledge Base" />
        
        <div class="pt-4 pb-2 px-4">
            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">System</span>
        </div>
        
        <x-sidebar-link icon="monitoring" label="Analytics" />
        <x-sidebar-link icon="settings" label="Settings" />
    </nav>

    <!-- Sidebar Footer -->
    <div class="flex-shrink-0 p-4 border-t border-gray-800">
        <div class="p-3 rounded-lg bg-gray-800/50 flex items-center gap-3 group cursor-pointer hover:bg-gray-800 transition-colors">
            <div class="size-8 rounded-full bg-blue-600 flex items-center justify-center text-xs font-bold text-white uppercase">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-[11px] text-gray-500 truncate">Pro Plan</p>
            </div>
            <span class="material-symbols-outlined text-gray-600 group-hover:text-red-400 transition-colors text-lg">logout</span>
        </div>
    </div>
</div>

{{-- Inline Blade Component for Sidebar Link for simplicity in this demo --}}
@php
if (!function_exists('sidebar_link')) {
    function sidebar_link($icon, $label, $active = false) {
        $activeClasses = $active ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-400 hover:bg-gray-800/50 hover:text-white';
        return '
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 '.$activeClasses.' group">
                <span class="material-symbols-outlined text-[20px] transition-transform group-hover:scale-110 ' . ($active ? 'filled' : '') . '">'.$icon.'</span>
                <span>'.$label.'</span>
            </a>
        ';
    }
}
@endphp
