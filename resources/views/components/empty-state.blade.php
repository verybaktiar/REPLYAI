@props([
    'icon' => 'folder_open',
    'title' => 'Belum ada data',
    'description' => 'Silakan tambahkan data baru untuk memulai.',
    'actionLabel' => null,
    'actionUrl' => null,
    'isPro' => false
])

<div class="flex flex-col items-center justify-center py-16 px-4 text-center">
    <div class="relative mb-6">
        <div class="size-24 rounded-3xl bg-slate-800/50 flex items-center justify-center border border-slate-700/50 shadow-inner rotate-3 transition-transform hover:rotate-0">
            <span class="material-symbols-outlined text-[48px] text-slate-500">{{ $icon }}</span>
        </div>
        @if($isPro)
            <div class="absolute -top-1 -right-1 px-2 py-0.5 bg-yellow-500 rounded-full border-4 border-[#111722]">
                <span class="text-[9px] font-black text-[#111722] uppercase tracking-tighter">PRO</span>
            </div>
        @endif
    </div>
    
    <h3 class="text-xl font-black text-white mb-2 tracking-tight">{{ $title }}</h3>
    <p class="text-slate-500 text-sm max-w-xs mx-auto mb-8 leading-relaxed">
        {{ $description }}
    </p>
    
    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-blue-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 hover:scale-105 active:scale-95">
            <span class="material-symbols-outlined text-[20px]">add</span>
            {{ $actionLabel }}
        </a>
    @endif
</div>
