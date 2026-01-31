@props(['user'])

@php
    $hasKb = \App\Models\KbArticle::where('user_id', $user->id)->exists();
    $hasRules = \App\Models\AutoReplyRule::where('user_id', $user->id)->exists();
    $hasWa = \App\Models\WaSession::where('user_id', $user->id)->where('status', 'connected')->exists();
    $hasIg = \App\Models\InstagramAccount::where('user_id', $user->id)->where('is_active', true)->exists();
    $hasProfile = \App\Models\BusinessProfile::where('user_id', $user->id)->exists();
    
    $steps = [
        ['done' => $hasProfile, 'label' => 'Lengkapi Profil', 'url' => route('settings.business')],
        ['done' => $hasWa, 'label' => 'Hubungkan WA', 'url' => route('whatsapp.settings')],
        ['done' => $hasKb, 'label' => 'Tambah Pengetahuan', 'url' => route('kb.index')],
        ['done' => $hasRules, 'label' => 'Atur Balasan', 'url' => route('rules.index')],
        ['done' => $hasIg, 'label' => 'Hubungkan IG', 'url' => route('instagram.connect')],
    ];
    
    $completed = collect($steps)->filter(fn($s) => $s['done'])->count();
    $total = count($steps);
@endphp

@if($completed < $total)
<div x-data="{ collapsed: false }" 
     class="bg-[#111722] border border-gray-800 rounded-xl overflow-hidden transition-all duration-300">
    
    <!-- Compact Header -->
    <div class="px-5 py-3 flex items-center justify-between border-b border-gray-800/50">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-white tracking-tight">Langkah Awal ({{ $completed }}/{{ $total }})</span>
            </div>
            
            <!-- Dots Progress Bar -->
            <div class="flex items-center gap-1.5 ml-2">
                @foreach($steps as $step)
                    <div class="size-2 rounded-full {{ $step['done'] ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]' : 'bg-gray-700' }}"></div>
                @endforeach
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ $steps[min($completed, $total - 1)]['url'] }}" 
               class="text-xs font-bold text-blue-400 hover:text-blue-300 transition-colors">
                Lanjutkan Setup
            </a>
            <button @click="collapsed = !collapsed" class="p-1 text-gray-500 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]" x-text="collapsed ? 'expand_more' : 'expand_less'">expand_less</span>
            </button>
        </div>
    </div>

    <!-- Collapsible Detail (Linear Progress Bar) -->
    <div x-show="!collapsed" x-collapse>
        <div class="p-5 flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex-1">
                <div class="h-1.5 w-full bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 transition-all duration-700" style="width: {{ ($completed / $total) * 100 }}%"></div>
                </div>
                <p class="text-[11px] text-gray-500 mt-2 italic">
                    @php
                        $nextStep = collect($steps)->first(fn($s) => !$s['done']);
                    @endphp
                    @if($nextStep)
                        Tugas berikutnya: <strong>{{ $nextStep['label'] }}</strong> untuk memaksimalkan chatbot Anda.
                    @endif
                </p>
            </div>
            
            <div class="hidden md:flex gap-2">
                 @foreach($steps as $step)
                    @if(!$step['done'])
                        <a href="{{ $step['url'] }}" class="px-3 py-1.5 bg-gray-900 border border-gray-800 rounded-lg text-[10px] font-bold text-gray-400 hover:text-white hover:border-gray-700 transition-all">
                            {{ $step['label'] }}
                        </a>
                        @break
                    @endif
                 @endforeach
            </div>
        </div>
    </div>
</div>
@endif
