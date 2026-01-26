{{-- Impersonation Banner - Shows when admin is impersonating a user --}}
@if(session()->has('impersonating_from_admin'))
<div class="fixed top-0 left-0 right-0 z-[200] bg-gradient-to-r from-yellow-600 to-orange-500 text-white px-4 py-2">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined animate-pulse">person_play</span>
            <span class="font-medium">
                Anda sedang login sebagai <strong>{{ Auth::user()->name }}</strong> 
                <span class="text-yellow-200">({{ Auth::user()->email }})</span>
            </span>
        </div>
        <a href="{{ route('admin.stop-impersonate') }}" 
           class="flex items-center gap-2 px-4 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg font-medium transition">
            <span class="material-symbols-outlined text-sm">logout</span>
            Kembali ke Admin
        </a>
    </div>
</div>
{{-- Add padding to body to account for banner --}}
<style>
    body { padding-top: 44px !important; }
</style>
@endif
