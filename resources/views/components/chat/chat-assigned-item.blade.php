@props([
    'name',
    'message',
    'time',
    'active' => false,
    'unread' => false,
    'assignedAgent' => null,
    'assignmentType' => null, // 'me', 'other', null
    'channel' => 'instagram', // instagram, whatsapp, web
    'channelIcon' => null,
    'aiSentiment' => null, // positive, neutral, negative
    'aiIntent' => null,
])

<div 
    {{ $attributes->merge(['class' => 'flex items-center gap-3 p-4 cursor-pointer transition-all duration-200 border-l-4 ' . ($active ? 'bg-gray-800/80 border-blue-600' : 'hover:bg-gray-800/30 border-transparent hover:border-gray-800') . ' group']) }}
>
    <!-- Avatar with Channel Badge -->
    <div class="relative flex-shrink-0">
        <div class="size-11 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center text-blue-500 font-bold group-hover:scale-105 transition-transform overflow-hidden">
            {{ substr($name, 0, 1) }}
        </div>
        
        <!-- Channel Icon Badge -->
        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-gray-800 border-2 border-gray-900 flex items-center justify-center">
            @if($channel === 'instagram')
                <svg class="w-3 h-3 text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            @elseif($channel === 'whatsapp')
                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            @else
                <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            @endif
        </div>
        
        @if($active)
            <div class="absolute -bottom-0.5 left-5 size-3 bg-green-500 rounded-full border-2 border-gray-900 shadow-sm"></div>
        @endif

        {{-- AI Sentiment Indicator --}}
        @if($aiSentiment)
            <div class="absolute -top-1 -left-1 w-4 h-4 rounded-full flex items-center justify-center border-2 border-gray-900
                @if($aiSentiment === 'positive') bg-green-500
                @elseif($aiSentiment === 'negative') bg-red-500
                @else bg-gray-400
                @endif"
                title="AI Sentiment: {{ ucfirst($aiSentiment) }}"
            >
                <span class="material-symbols-outlined text-[10px] text-white leading-none">
                    @if($aiSentiment === 'positive') sentiment_satisfied
                    @elseif($aiSentiment === 'negative') sentiment_dissatisfied
                    @else sentiment_neutral
                    @endif
                </span>
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between mb-1">
            <h4 class="text-sm font-bold text-white truncate flex items-center gap-2">
                {{ $name }}
                @if($assignedAgent)
                    <!-- Assignment Badge -->
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium {{ $assignmentType === 'me' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                        @if($assignmentType === 'me')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            You
                        @else
                            <img src="{{ $assignedAgent['avatar'] ?? '' }}" 
                                 alt="" 
                                 class="w-3 h-3 rounded-full bg-gray-700 object-cover"
                                 onerror="this.style.display='none'">
                            {{ $assignedAgent['name'] ?? 'Assigned' }}
                        @endif
                    </span>
                @endif

                {{-- AI Intent Badge --}}
                @if($aiIntent && $aiIntent !== 'general' && $aiIntent !== 'greeting')
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-500/10 text-purple-400 border border-purple-500/30">
                        <span class="material-symbols-outlined text-[10px] mr-0.5">auto_awesome</span>
                        @php
                            $intentLabels = [
                                'complaint' => 'Keluhan',
                                'inquiry' => 'Tanya',
                                'purchase' => 'Beli',
                                'support' => 'Bantuan',
                                'feedback' => 'Feedback',
                                'urgent' => 'Urgent',
                                'cancellation' => 'Batal',
                            ];
                        @endphp
                        {{ $intentLabels[$aiIntent] ?? ucfirst($aiIntent) }}
                    </span>
                @endif
            </h4>
            <span class="text-[10px] font-medium text-gray-500 transition-colors group-hover:text-gray-400">{{ $time }}</span>
        </div>
        <div class="flex items-center justify-between gap-2 text-xs">
            <p class="text-gray-400 truncate flex-1 leading-snug">{{ $message }}</p>
            @if($unread)
                <div class="size-2 rounded-full bg-blue-600 shrink-0 shadow-[0_0_8px_rgba(37,99,235,0.4)]"></div>
            @endif
        </div>
    </div>
</div>
