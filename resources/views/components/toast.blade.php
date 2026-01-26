@props([
    'type' => 'success', // success, error, warning, info
    'message' => '',
    'duration' => 3000
])

@php
    $icons = [
        'success' => 'check_circle',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
    ];
    
    $colors = [
        'success' => 'text-green-500',
        'error' => 'text-red-500',
        'warning' => 'text-yellow-500',
        'info' => 'text-blue-500',
    ];
@endphp

<div x-data="{ 
    show: false, 
    message: '', 
    type: 'success',
    init() {
        window.toast = (msg, t = 'success') => {
            this.message = msg;
            this.type = t;
            this.show = true;
            setTimeout(() => this.show = false, {{ $duration }});
        };
        
        @if(session('success'))
            this.message = '{{ session('success') }}';
            this.type = 'success';
            this.show = true;
            setTimeout(() => this.show = false, {{ $duration }});
        @endif
        
        @if(session('error'))
            this.message = '{{ session('error') }}';
            this.type = 'error';
            this.show = true;
            setTimeout(() => this.show = false, {{ $duration }});
        @endif
    }
}"
x-show="show"
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0 translate-y-2"
x-transition:enter-end="opacity-100 translate-y-0"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100 translate-y-0"
x-transition:leave-end="opacity-0 translate-y-2"
class="fixed bottom-5 right-5 z-[100] rounded-xl bg-surface-dark border border-slate-700 px-4 py-3 text-sm text-white shadow-xl flex items-center gap-3"
style="display: none;">
    <span class="material-symbols-outlined" 
          :class="{
              'text-green-500': type === 'success',
              'text-red-500': type === 'error',
              'text-yellow-500': type === 'warning',
              'text-blue-500': type === 'info'
          }"
          x-text="type === 'success' ? 'check_circle' : type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'info'"></span>
    <span x-text="message"></span>
    <button @click="show = false" class="ml-2 text-slate-400 hover:text-white">
        <span class="material-symbols-outlined text-sm">close</span>
    </button>
</div>
