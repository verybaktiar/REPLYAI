@props([
    'comment' => null,
    'isSelected' => false,
])

@if($comment)
<div 
    class="group relative p-4 rounded-xl border transition-all duration-200 cursor-pointer hover:shadow-lg {{ $isSelected ? 'bg-gradient-to-r from-pink-500/10 via-purple-500/10 to-orange-500/10 border-pink-500/50 ring-1 ring-pink-500/30' : 'bg-gray-800/50 border-gray-700 hover:border-gray-600 hover:bg-gray-800' }}"
    data-comment-id="{{ $comment->id }}"
    x-on:click="selectComment({{ $comment->id }})"
>
    <div class="flex gap-4">
        {{-- Checkbox for bulk selection --}}
        <div class="flex items-start pt-1">
            <input 
                type="checkbox" 
                value="{{ $comment->id }}"
                x-model="selectedComments"
                @click.stop
                class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-pink-500 focus:ring-pink-500 focus:ring-offset-gray-900"
            >
        </div>

        {{-- Avatar --}}
        <div class="shrink-0">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-pink-500/20">
                {{ strtoupper(substr($comment->from_username, 0, 1)) }}
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-white truncate">
                        {{ '@' . $comment->from_username }}
                    </span>
                    @if($comment->is_replied)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-500/20 text-green-400 text-[10px] font-medium border border-green-500/30">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Replied
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 text-[10px] font-medium border border-amber-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            Unreplied
                        </span>
                    @endif
                </div>
                <span class="text-xs text-gray-500 shrink-0">
                    {{ $comment->commented_at->diffForHumans() }}
                </span>
            </div>

            {{-- Comment Text --}}
            <div class="mt-2">
                <p class="text-sm text-gray-300 leading-relaxed line-clamp-3">
                    {{ $comment->text }}
                </p>
            </div>

            {{-- Media Preview --}}
            @if($comment->media_id)
            <div class="mt-3 flex items-center gap-2">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-900/80 rounded-lg border border-gray-700 text-xs">
                    <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-gray-400">Post ID:</span>
                    <span class="text-gray-300 font-mono">{{ Str::limit($comment->media_id, 15) }}</span>
                    <a 
                        href="https://instagram.com/p/{{ $comment->media_id }}" 
                        target="_blank"
                        @click.stop
                        class="ml-2 text-pink-400 hover:text-pink-300 transition-colors"
                        title="View on Instagram"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </div>
            @endif

            {{-- Reply Preview (if replied) --}}
            @if($comment->is_replied && $comment->reply_text)
            <div class="mt-3 p-3 bg-green-500/5 border border-green-500/20 rounded-lg">
                <div class="flex items-center gap-1 mb-1">
                    <svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    <span class="text-xs text-green-400 font-medium">Your reply</span>
                    <span class="text-xs text-gray-500">• {{ $comment->replied_at?->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-gray-400">{{ $comment->reply_text }}</p>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="mt-3 flex items-center gap-2">
                @if(!$comment->is_replied)
                <button 
                    @click.stop="openReplyModal({{ $comment->id }}, '{{ addslashes($comment->from_username) }}', '{{ addslashes(Str::limit($comment->text, 100)) }}')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white text-xs font-medium rounded-lg transition-all shadow-lg shadow-purple-500/20"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    Reply
                </button>
                @endif
                
                <button 
                    @click.stop="deleteComment({{ $comment->id }})"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-700 hover:bg-red-600/20 hover:text-red-400 text-gray-400 text-xs font-medium rounded-lg transition-all"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endif
