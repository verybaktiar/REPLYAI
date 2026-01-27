@props(['user', 'cols' => 3])

@php
    $plan = $user->getPlan();
    $limits = $plan->features ?? [];
    
    // Fetch current usage
    $usage = [
        'messages' => [
            'used' => (\App\Models\WaMessage::where('user_id', $user->id)
                        ->where('direction', 'outgoing')
                        ->where('created_at', '>=', now()->startOfMonth())
                        ->count()) + 
                      (\App\Models\Message::whereHas('conversation', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      })
                        ->where('sender_type', '!=', 'contact')
                        ->where('created_at', '>=', now()->startOfMonth())
                        ->count()),
            'limit' => $limits['ai_messages'] ?? 100,
            'label' => __('dashboard.monthly_messages'),
            'icon' => 'chat'
        ],
        'kb_articles' => [
            'used' => \App\Models\KbArticle::where('user_id', $user->id)->count(),
            'limit' => $limits['kb_articles'] ?? 5,
            'label' => __('dashboard.knowledge_base'),
            'icon' => 'menu_book'
        ],
        'rules' => [
            'used' => \App\Models\AutoReplyRule::where('user_id', $user->id)->count(),
            'limit' => $limits['auto_reply_rules'] ?? 10,
            'label' => __('dashboard.bot_rules'),
            'icon' => 'smart_toy'
        ]
    ];
    
    $gridCols = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3'
    ][$cols] ?? 'grid-cols-1 md:grid-cols-3';
@endphp

<div class="grid {{ $gridCols }} gap-6">
    @foreach($usage as $key => $item)
        @php
            $percent = $item['limit'] > 0 ? min(100, round(($item['used'] / $item['limit']) * 100)) : 0;
            $isNearLimit = $percent >= 80;
            $isOverLimit = $percent >= 100;
            $strokeDashOffset = 251 - (251 * $percent / 100);
            
            // Usage color logic
            $colorClass = $isOverLimit ? 'text-red-500' : ($isNearLimit ? 'text-yellow-500' : 'text-primary');
            $ringClass = $isOverLimit ? 'stroke-red-500' : ($isNearLimit ? 'stroke-yellow-500' : 'stroke-primary');
        @endphp
        
        <div class="bg-surface-dark border border-slate-800 rounded-[2rem] p-6 hover:border-slate-700 transition-all group relative overflow-hidden">
            {{-- Background Glow --}}
            <div class="absolute -right-4 -top-4 size-24 rounded-full blur-3xl opacity-0 group-hover:opacity-10 transition-opacity {{ $isOverLimit ? 'bg-red-500' : ($isNearLimit ? 'bg-yellow-500' : 'bg-primary') }}"></div>
            
            <div class="flex items-center gap-6 relative">
                {{-- Radial Progress --}}
                <div class="relative size-20 flex-shrink-0">
                    <svg class="size-full -rotate-90" viewBox="0 0 100 100">
                        <circle class="stroke-slate-800 fill-none" cx="50" cy="50" r="40" stroke-width="8"></circle>
                        <circle class="{{ $ringClass }} fill-none transition-all duration-1000 ease-out" 
                                cx="50" cy="50" r="40" stroke-width="8" 
                                stroke-dasharray="251.2" 
                                stroke-dashoffset="{{ $strokeDashOffset }}" 
                                stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-sm font-black {{ $colorClass }}">{{ $percent }}%</span>
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-lg text-slate-500">{{ $item['icon'] }}</span>
                        <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest truncate">{{ $item['label'] }}</h4>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-xl font-black text-white">{{ number_format($item['used']) }}</span>
                        <span class="text-xs text-slate-600 font-medium">/ {{ $item['limit'] == -1 ? '∞' : number_format($item['limit']) }}</span>
                    </div>
                    
                    @if($isOverLimit)
                        <p class="text-[9px] font-bold text-red-500 mt-1 uppercase italic">{{ __('dashboard.limit_reached') }}</p>
                    @elseif($isNearLimit)
                        <p class="text-[9px] font-bold text-yellow-500 mt-1 uppercase italic">{{ __('dashboard.near_limit') }}</p>
                    @else
                        <p class="text-[9px] text-slate-600 mt-1 italic">{{ __('dashboard.normal_usage') }}</p>
                    @endif
                </div>
            </div>
            
            @if($isNearLimit && !($user->is_vip ?? false))
                <div class="mt-4 pt-4 border-t border-slate-800/50">
                    <a href="{{ route('pricing', ['ref' => $key]) }}" class="flex items-center justify-center gap-2 py-2.5 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-xl text-[10px] font-black transition-all group/btn tracking-widest">
                        {{ __('dashboard.upgrade_limit') }}
                        <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">bolt</span>
                    </a>
                </div>
            @endif
        </div>
    @endforeach
</div>

