@extends('layouts.dark')

@section('title', 'Instagram Comments')

@section('content')
<main class="flex-1 flex flex-col h-full overflow-hidden" x-data="instagramComments()" x-init="init()">
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-800 bg-gray-900/50 backdrop-blur-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 flex items-center justify-center shadow-lg shadow-pink-500/20">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Instagram Comments</h1>
                        <p class="text-sm text-gray-400">Manage and reply to your Instagram comments</p>
                    </div>
                    @include('components.page-help', [
                        'title' => 'Komentar Instagram',
                        'description' => 'Kelola dan balas komentar di postingan Instagram secara otomatis.',
                        'tips' => ['Pantau komentar masuk dari semua postingan', 'Balas komentar dengan cepat menggunakan template', 'Filter komentar berdasarkan status (Baru/Dibalas)', 'Gunakan AI untuk generate balasan otomatis']
                    ])
                </div>

                <div class="flex items-center gap-3">
                    {{-- Connection Status --}}
                    @if($instagramAccount)
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-green-500/10 border border-green-500/30 rounded-lg">
                        <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                        <span class="text-xs font-medium text-green-400">Connected as {{ '@' . $instagramAccount->username }}</span>
                    </div>
                    @else
                    <a href="{{ route('instagram.settings') }}" class="flex items-center gap-2 px-4 py-2 bg-amber-500/10 border border-amber-500/30 rounded-lg text-amber-400 hover:bg-amber-500/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="text-sm font-medium">Connect Instagram</span>
                    </a>
                    @endif

                    {{-- Fetch Comments Button --}}
                    <button 
                        @click="fetchComments()"
                        :disabled="isFetching"
                        class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-lg text-white transition-all disabled:opacity-50"
                    >
                        <svg class="w-4 h-4" :class="isFetching ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="text-sm font-medium" x-text="isFetching ? 'Fetching...' : 'Fetch New'"></span>
                    </button>

                    {{-- Settings Button --}}
                    <button 
                        @click="showSettingsModal = true"
                        class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-lg text-white transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-sm font-medium hidden sm:inline">Auto-Reply</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Bar --}}
        <div class="px-6 py-4 border-b border-gray-800 bg-gray-900/30">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                    <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-400">Total Comments</div>
                </div>
                <div class="p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                    <div class="text-2xl font-bold text-amber-400">{{ $stats['unreplied'] }}</div>
                    <div class="text-sm text-gray-400">Unreplied</div>
                </div>
                <div class="p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                    <div class="text-2xl font-bold text-green-400">{{ $stats['replied'] }}</div>
                    <div class="text-sm text-gray-400">Replied</div>
                </div>
                <div class="p-4 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-xl border border-purple-500/30">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-lg font-bold text-white">
                                {{ $stats['total'] > 0 ? round(($stats['replied'] / $stats['total']) * 100) : 0 }}%
                            </div>
                            <div class="text-sm text-gray-400">Response Rate</div>
                        </div>
                        <div class="w-12 h-12 rounded-full border-4 border-purple-500/30 border-t-purple-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters & Bulk Actions --}}
        <div class="px-6 py-3 border-b border-gray-800 bg-gray-900/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            {{-- Filter Tabs --}}
            <div class="flex items-center gap-2">
                <a href="?filter=all" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filter === 'all' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-300' }}">
                    All
                    <span class="ml-1.5 px-1.5 py-0.5 bg-gray-600 rounded-full text-xs">{{ $stats['total'] }}</span>
                </a>
                <a href="?filter=unreplied" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filter === 'unreplied' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-300' }}">
                    Unreplied
                    <span class="ml-1.5 px-1.5 py-0.5 bg-amber-500/30 rounded-full text-xs">{{ $stats['unreplied'] }}</span>
                </a>
                <a href="?filter=replied" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filter === 'replied' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-300' }}">
                    Replied
                    <span class="ml-1.5 px-1.5 py-0.5 bg-green-500/30 rounded-full text-xs">{{ $stats['replied'] }}</span>
                </a>
            </div>

            {{-- Bulk Actions --}}
            <div class="flex items-center gap-3" x-show="selectedComments.length > 0" x-transition>
                <span class="text-sm text-gray-400"><span x-text="selectedComments.length"></span> selected</span>
                <button 
                    @click="openBulkReplyModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white text-sm font-medium rounded-lg transition-all shadow-lg shadow-purple-500/20"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    Bulk Reply
                </button>
                <button 
                    @click="selectedComments = []"
                    class="px-3 py-2 text-gray-400 hover:text-white text-sm transition-colors"
                >
                    Clear
                </button>
            </div>
        </div>

        {{-- Comments List --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            @if($comments->count() > 0)
                <div class="space-y-3 max-w-4xl">
                    @foreach($comments as $comment)
                        @include('components.chat.comment-item', ['comment' => $comment])
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $comments->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center h-full text-center py-12">
                    <div class="w-24 h-24 rounded-full bg-gray-800 flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">No comments yet</h3>
                    <p class="text-gray-400 max-w-md mb-6">
                        @if($instagramAccount)
                            No {{ $filter !== 'all' ? $filter : '' }} comments found. Click "Fetch New" to get the latest comments from your Instagram account.
                        @else
                            Connect your Instagram account to start managing comments.
                        @endif
                    </p>
                    @if($instagramAccount)
                    <button 
                        @click="fetchComments()"
                        :disabled="isFetching"
                        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-medium rounded-xl transition-all shadow-lg shadow-purple-500/20"
                    >
                        <svg class="w-5 h-5" :class="isFetching ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span x-text="isFetching ? 'Fetching Comments...' : 'Fetch Comments'"></span>
                    </button>
                    @else
                    <a href="{{ route('instagram.settings') }}" class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-medium rounded-xl transition-all shadow-lg shadow-purple-500/20">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                        </svg>
                        Connect Instagram
                    </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Reply Modal --}}
    <div 
        x-show="showReplyModal" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
        x-cloak
    >
        <div 
            x-show="showReplyModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="showReplyModal = false"
            class="w-full max-w-lg bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl overflow-hidden"
        >
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between bg-gradient-to-r from-purple-500/10 to-pink-500/10">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">Reply to Comment</h3>
                </div>
                <button @click="showReplyModal = false" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Original Comment --}}
                <div class="p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold">
                            <span x-text="replyToUsername ? replyToUsername.charAt(0).toUpperCase() : ''"></span>
                        </div>
                        <span class="text-sm font-medium text-white" x-text="'@' + replyToUsername"></span>
                    </div>
                    <p class="text-sm text-gray-400" x-text="replyToCommentText"></p>
                </div>

                {{-- Reply Input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Your Reply</label>
                    <textarea 
                        x-model="replyMessage"
                        rows="4"
                        placeholder="Type your reply here..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 p-4 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 resize-none transition-all"
                        maxlength="2200"
                    ></textarea>
                    <div class="mt-2 flex justify-between text-xs text-gray-500">
                        <span>Max 2200 characters</span>
                        <span x-text="replyMessage.length + '/2200'"></span>
                    </div>
                </div>

                {{-- Quick Replies --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Quick Replies</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="template in quickReplyTemplates" :key="template">
                            <button 
                                @click="replyMessage = template"
                                class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-lg text-xs text-gray-300 transition-colors"
                                x-text="template.length > 30 ? template.substring(0, 30) + '...' : template"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                <button 
                    @click="showReplyModal = false"
                    class="px-4 py-2 text-gray-400 hover:text-white font-medium transition-colors"
                >
                    Cancel
                </button>
                <button 
                    @click="sendReply()"
                    :disabled="!replyMessage.trim() || isSendingReply"
                    class="flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl transition-all shadow-lg shadow-purple-500/20"
                >
                    <svg x-show="!isSendingReply" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <svg x-show="isSendingReply" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span x-text="isSendingReply ? 'Sending...' : 'Send Reply'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Bulk Reply Modal --}}
    <div 
        x-show="showBulkReplyModal" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
        x-cloak
    >
        <div 
            x-show="showBulkReplyModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="showBulkReplyModal = false"
            class="w-full max-w-lg bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl overflow-hidden"
        >
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between bg-gradient-to-r from-purple-500/10 to-pink-500/10">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">Bulk Reply</h3>
                </div>
                <button @click="showBulkReplyModal = false" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-400">You are about to reply to <span x-text="selectedComments.length"></span> comments</p>
                            <p class="text-xs text-gray-400 mt-1">The same message will be sent to all selected comments.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Reply Message</label>
                    <textarea 
                        x-model="bulkReplyMessage"
                        rows="4"
                        placeholder="Type your reply here..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 p-4 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 resize-none transition-all"
                        maxlength="2200"
                    ></textarea>
                    <div class="mt-2 flex justify-between text-xs text-gray-500">
                        <span>Max 2200 characters</span>
                        <span x-text="bulkReplyMessage.length + '/2200'"></span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                <button 
                    @click="showBulkReplyModal = false"
                    class="px-4 py-2 text-gray-400 hover:text-white font-medium transition-colors"
                >
                    Cancel
                </button>
                <button 
                    @click="sendBulkReply()"
                    :disabled="!bulkReplyMessage.trim() || isSendingBulkReply"
                    class="flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl transition-all shadow-lg shadow-purple-500/20"
                >
                    <svg x-show="!isSendingBulkReply" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <svg x-show="isSendingBulkReply" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span x-text="isSendingBulkReply ? 'Sending...' : 'Send to All'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Auto-Reply Settings Modal --}}
    <div 
        x-show="showSettingsModal" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
        x-cloak
    >
        <div 
            x-show="showSettingsModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="showSettingsModal = false"
            class="w-full max-w-lg bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl overflow-hidden"
        >
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between bg-gradient-to-r from-purple-500/10 to-pink-500/10">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">Auto-Reply Settings</h3>
                </div>
                <button @click="showSettingsModal = false" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">
                {{-- Enable Toggle --}}
                <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                    <div>
                        <p class="font-medium text-white">Enable Auto-Reply</p>
                        <p class="text-sm text-gray-400">Automatically reply to comments matching your keywords</p>
                    </div>
                    <button 
                        @click="autoReplySettings.is_enabled = !autoReplySettings.is_enabled"
                        :class="autoReplySettings.is_enabled ? 'bg-pink-500' : 'bg-gray-600'"
                        class="relative w-12 h-6 rounded-full transition-colors"
                    >
                        <span 
                            :class="autoReplySettings.is_enabled ? 'translate-x-6' : 'translate-x-1'"
                            class="absolute top-1 w-4 h-4 bg-white rounded-full transition-transform"
                        ></span>
                    </button>
                </div>

                {{-- Keywords --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Keywords</label>
                    <p class="text-xs text-gray-500 mb-2">Comments containing these keywords will trigger auto-reply</p>
                    <div class="space-y-2">
                        <template x-for="(keyword, index) in autoReplySettings.keywords" :key="index">
                            <div class="flex gap-2">
                                <input 
                                    type="text"
                                    x-model="autoReplySettings.keywords[index]"
                                    placeholder="Enter keyword"
                                    class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                >
                                <button 
                                    @click="autoReplySettings.keywords.splice(index, 1)"
                                    class="px-3 py-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <button 
                            @click="autoReplySettings.keywords.push('')"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-pink-400 hover:text-pink-300 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Keyword
                        </button>
                    </div>
                </div>

                {{-- Match Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Match Type</label>
                    <select 
                        x-model="autoReplySettings.match_type"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                    >
                        <option value="contains">Contains (keyword anywhere in comment)</option>
                        <option value="starts_with">Starts With (comment begins with keyword)</option>
                        <option value="exact">Exact Match (comment equals keyword)</option>
                    </select>
                </div>

                {{-- Reply Template --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Reply Template</label>
                    <textarea 
                        x-model="autoReplySettings.reply_template"
                        rows="4"
                        placeholder="Enter your auto-reply message..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 p-4 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 resize-none transition-all"
                        maxlength="2200"
                    ></textarea>
                    <div class="mt-2 flex justify-between text-xs text-gray-500">
                        <span>This message will be sent automatically</span>
                        <span x-text="autoReplySettings.reply_template.length + '/2200'"></span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-800 flex justify-end gap-3">
                <button 
                    @click="showSettingsModal = false"
                    class="px-4 py-2 text-gray-400 hover:text-white font-medium transition-colors"
                >
                    Cancel
                </button>
                <button 
                    @click="saveSettings()"
                    :disabled="isSavingSettings || !autoReplySettings.reply_template.trim()"
                    class="flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl transition-all shadow-lg shadow-purple-500/20"
                >
                    <svg x-show="!isSavingSettings" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="isSavingSettings" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span x-text="isSavingSettings ? 'Saving...' : 'Save Settings'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Toast Notifications --}}
    <div class="fixed bottom-4 right-4 z-50 space-y-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div 
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 min-w-[300px]"
                :class="toast.type === 'success' ? 'bg-green-500/90 text-white' : toast.type === 'error' ? 'bg-red-500/90 text-white' : 'bg-gray-800 text-white border border-gray-700'"
            >
                <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span x-text="toast.message" class="text-sm font-medium"></span>
            </div>
        </template>
    </div>
</main>

@push('scripts')
<script>
function instagramComments() {
    return {
        selectedComments: [],
        showReplyModal: false,
        showBulkReplyModal: false,
        showSettingsModal: false,
        isFetching: false,
        isSendingReply: false,
        isSendingBulkReply: false,
        isSavingSettings: false,
        
        replyToId: null,
        replyToUsername: '',
        replyToCommentText: '',
        replyMessage: '',
        bulkReplyMessage: '',
        
        quickReplyTemplates: [
            'Terima kasih atas komentarnya! 😊',
            'Halo! Terima kasih sudah menghubungi kami. Ada yang bisa kami bantu?',
            'Maaf, kami sedang offline. Kami akan membalas secepatnya.',
            '🙏 Terima kasih atas dukungannya!',
        ],
        
        autoReplySettings: {{ json_encode($autoReplySettings ?? ['is_enabled' => false, 'keywords' => [], 'reply_template' => 'Terima kasih atas komentarnya! 😊', 'match_type' => 'contains']) }},
        
        toasts: [],
        
        init() {
            // Initialize
        },
        
        selectComment(id) {
            // Handle single comment selection
            const index = this.selectedComments.indexOf(id.toString());
            if (index === -1) {
                this.selectedComments.push(id.toString());
            } else {
                this.selectedComments.splice(index, 1);
            }
        },
        
        openReplyModal(id, username, text) {
            this.replyToId = id;
            this.replyToUsername = username;
            this.replyToCommentText = text;
            this.replyMessage = '';
            this.showReplyModal = true;
        },
        
        openBulkReplyModal() {
            this.bulkReplyMessage = '';
            this.showBulkReplyModal = true;
        },
        
        async fetchComments() {
            this.isFetching = true;
            try {
                const response = await fetch('{{ route("instagram.comments.fetch") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                });
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast('error', data.error || 'Failed to fetch comments');
                }
            } catch (error) {
                this.showToast('error', 'An error occurred while fetching comments');
            } finally {
                this.isFetching = false;
            }
        },
        
        async sendReply() {
            if (!this.replyMessage.trim()) return;
            
            this.isSendingReply = true;
            try {
                const response = await fetch(`/instagram/comments/${this.replyToId}/reply`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: this.replyMessage }),
                });
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('success', data.message);
                    this.showReplyModal = false;
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast('error', data.error || 'Failed to send reply');
                }
            } catch (error) {
                this.showToast('error', 'An error occurred while sending reply');
            } finally {
                this.isSendingReply = false;
            }
        },
        
        async sendBulkReply() {
            if (!this.bulkReplyMessage.trim() || this.selectedComments.length === 0) return;
            
            this.isSendingBulkReply = true;
            try {
                const response = await fetch('{{ route("instagram.comments.bulk-reply") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        comment_ids: this.selectedComments,
                        message: this.bulkReplyMessage,
                    }),
                });
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('success', data.message);
                    this.showBulkReplyModal = false;
                    this.selectedComments = [];
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast('error', data.error || 'Failed to send bulk reply');
                }
            } catch (error) {
                this.showToast('error', 'An error occurred while sending bulk reply');
            } finally {
                this.isSendingBulkReply = false;
            }
        },
        
        async saveSettings() {
            this.isSavingSettings = true;
            try {
                const response = await fetch('{{ route("instagram.comments.settings") }}', {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.autoReplySettings),
                });
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('success', data.message);
                    this.showSettingsModal = false;
                } else {
                    this.showToast('error', data.error || 'Failed to save settings');
                }
            } catch (error) {
                this.showToast('error', 'An error occurred while saving settings');
            } finally {
                this.isSavingSettings = false;
            }
        },
        
        async deleteComment(id) {
            if (!confirm('Are you sure you want to delete this comment?')) return;
            
            try {
                const response = await fetch(`/instagram/comments/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast('error', data.error || 'Failed to delete comment');
                }
            } catch (error) {
                this.showToast('error', 'An error occurred while deleting comment');
            }
        },
        
        showToast(type, message) {
            const id = Date.now();
            this.toasts.push({ id, type, message, visible: true });
            setTimeout(() => {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) toast.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 200);
            }, 3000);
        },
    };
}
</script>
@endpush
@endsection
