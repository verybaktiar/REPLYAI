@props(['user'])

@php
    // User-scoped queries
    $hasKb = \App\Models\KbArticle::where('user_id', $user->id)->exists();
    $hasRules = \App\Models\AutoReplyRule::where('user_id', $user->id)->exists();
    $hasWa = \App\Models\WaSession::where('user_id', $user->id)->where('status', 'connected')->exists();
    $hasIg = \App\Models\InstagramAccount::where('user_id', $user->id)->where('is_active', true)->exists();
    $hasProfile = \App\Models\BusinessProfile::where('user_id', $user->id)->exists();
    
    $checks = [
        'profile' => [
            'done' => $hasProfile,
            'label' => 'Setup profil bisnis',
            'url' => route('settings.business'),
            'icon' => 'business'
        ],
        'kb' => [
            'done' => $hasKb,
            'label' => 'Tambahkan Knowledge Base',
            'url' => route('kb.index'),
            'icon' => 'menu_book'
        ],
        'rules' => [
            'done' => $hasRules,
            'label' => 'Buat aturan auto reply',
            'url' => route('rules.index'),
            'icon' => 'rule'
        ],
        'wa' => [
            'done' => $hasWa,
            'label' => 'Hubungkan WhatsApp',
            'url' => route('whatsapp.settings'),
            'icon' => 'chat'
        ],
        'ig' => [
            'done' => $hasIg,
            'label' => 'Hubungkan Instagram',
            'url' => route('instagram.connect'),
            'icon' => 'photo_camera'
        ],
    ];
    
    $completed = collect($checks)->filter(fn($c) => $c['done'])->count();
    $total = count($checks);
    $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
@endphp

@if($completed < $total)
<div class="bg-gradient-to-br from-[#1a2230] to-[#111722] rounded-2xl border border-slate-800 p-6 mb-8 shadow-xl">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-4">
            <div class="size-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[28px]">rocket_launch</span>
            </div>
            <div>
                <h3 class="text-lg font-black text-white leading-tight">Mulai Bisnis Anda</h3>
                <p class="text-xs text-slate-500 mt-1">Selesaikan langkah-langkah di bawah untuk hasil maksimal</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">PROGRES</p>
            <div class="flex items-end gap-1 justify-end">
                <span class="text-2xl font-black text-white leading-none">{{ $completed }}</span>
                <span class="text-sm text-slate-500 font-bold mb-0.5">/ {{ $total }}</span>
            </div>
        </div>
    </div>
    
    {{-- High-End Progress Bar --}}
    <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden mb-6">
        <div class="h-full bg-gradient-to-r from-primary via-blue-400 to-cyan-400 transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(19,91,236,0.3)]" 
             style="width: {{ $progress }}%"></div>
    </div>
    
    {{-- Checklist Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($checks as $key => $check)
        <a href="{{ $check['url'] }}" 
           class="group flex items-center gap-4 p-4 rounded-xl border {{ $check['done'] ? 'bg-green-500/5 border-green-500/20' : 'bg-slate-800/30 border-slate-800 hover:border-slate-700 hover:bg-slate-800/50' }} transition-all">
            <div class="size-8 rounded-lg flex items-center justify-center transition-colors {{ $check['done'] ? 'bg-green-500/20 text-green-400' : 'bg-slate-800 text-slate-500 group-hover:bg-primary/20 group-hover:text-primary' }}">
                @if($check['done'])
                    <span class="material-symbols-outlined text-[18px]">done_all</span>
                @else
                    <span class="material-symbols-outlined text-[18px]">{{ $check['icon'] }}</span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <span class="block text-sm font-semibold truncate {{ $check['done'] ? 'text-green-300/60 line-through' : 'text-slate-200' }}">
                    {{ $check['label'] }}
                </span>
            </div>
            @if(!$check['done'])
                <span class="material-symbols-outlined text-slate-600 transition-transform group-hover:translate-x-1 text-sm">arrow_forward</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif
