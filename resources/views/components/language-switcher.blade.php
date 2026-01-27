<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 bg-surface-dark border border-slate-700 rounded-full hover:bg-slate-700 transition group">
        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 group-hover:text-white">
            {{ App::getLocale() == 'id' ? 'ID' : 'EN' }}
        </span>
        <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-white">language</span>
    </button>
    
    <div x-show="open" @click.away="open = false" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="absolute right-0 mt-2 w-48 bg-surface-dark border border-slate-700 rounded-xl shadow-2xl z-50 overflow-hidden">
        <a href="{{ route('lang.switch', 'id') }}" class="flex items-center justify-between px-4 py-3 text-xs font-bold uppercase tracking-widest hover:bg-white/5 transition {{ App::getLocale() == 'id' ? 'text-primary' : 'text-slate-400' }}">
            Bahasa Indonesia
            @if(App::getLocale() == 'id')
            <span class="material-symbols-outlined text-[16px]">done</span>
            @endif
        </a>
        <a href="{{ route('lang.switch', 'en') }}" class="flex items-center justify-between px-4 py-3 text-xs font-bold uppercase tracking-widest hover:bg-white/5 transition {{ App::getLocale() == 'en' ? 'text-primary' : 'text-slate-400' }}">
            English
            @if(App::getLocale() == 'en')
            <span class="material-symbols-outlined text-[16px]">done</span>
            @endif
        </a>
    </div>
</div>
