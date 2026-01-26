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
<div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 rounded-xl border border-slate-700 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">rocket_launch</span>
                Setup Bisnis Anda
            </h3>
            <p class="text-xs text-slate-400 mt-1">Lengkapi langkah-langkah berikut</p>
        </div>
        <div class="text-right">
            <span class="text-2xl font-bold text-white">{{ $completed }}/{{ $total }}</span>
            <p class="text-xs text-slate-400">Selesai</p>
        </div>
    </div>
    
    {{-- Progress Bar --}}
    <div class="h-2 bg-slate-700 rounded-full overflow-hidden mb-4">
        <div class="h-full bg-gradient-to-r from-primary to-blue-400 transition-all duration-500" 
             style="width: {{ $progress }}%"></div>
    </div>
    
    {{-- Checklist --}}
    <div class="space-y-2">
        @foreach($checks as $key => $check)
        <a href="{{ $check['url'] }}" 
           class="flex items-center gap-3 p-3 rounded-lg {{ $check['done'] ? 'bg-green-500/10' : 'bg-slate-800/50 hover:bg-slate-700/50' }} transition-colors">
            @if($check['done'])
                <span class="material-symbols-outlined text-green-400">check_circle</span>
            @else
                <span class="material-symbols-outlined text-slate-500">{{ $check['icon'] }}</span>
            @endif
            <span class="{{ $check['done'] ? 'text-green-300 line-through' : 'text-white' }} text-sm">
                {{ $check['label'] }}
            </span>
            @if(!$check['done'])
                <span class="material-symbols-outlined text-slate-500 ml-auto text-sm">chevron_right</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif
