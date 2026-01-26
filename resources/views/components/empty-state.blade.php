@props([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'description' => 'Data akan muncul di sini setelah Anda menambahkannya.',
    'actionUrl' => null,
    'actionText' => 'Buat Baru',
    'actionIcon' => 'add',
])

<div class="flex flex-col items-center justify-center py-16 px-4">
    {{-- Icon --}}
    <div class="w-24 h-24 rounded-full bg-slate-800/50 flex items-center justify-center mb-6">
        <span class="material-symbols-outlined text-5xl text-slate-500">{{ $icon }}</span>
    </div>
    
    {{-- Title --}}
    <h3 class="text-xl font-semibold text-white mb-2">{{ $title }}</h3>
    
    {{-- Description --}}
    <p class="text-slate-400 text-center max-w-md mb-6">{{ $description }}</p>
    
    {{-- Action Button --}}
    @if($actionUrl)
    <a href="{{ $actionUrl }}" 
       class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-blue-600 text-white font-semibold rounded-xl transition-colors shadow-lg shadow-primary/25">
        <span class="material-symbols-outlined text-xl">{{ $actionIcon }}</span>
        {{ $actionText }}
    </a>
    @endif
    
    {{-- Slot for extra content --}}
    {{ $slot }}
</div>
