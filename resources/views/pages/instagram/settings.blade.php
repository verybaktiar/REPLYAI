@extends('layouts.dark')

@section('content')
<main class="flex-1 flex flex-col h-full overflow-hidden">
    <div class="p-6 lg:p-8 overflow-auto">
        <div class="max-w-2xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white mb-2">{{ __('instagram.title') }}</h1>
                <p class="text-gray-400">{{ __('instagram.subtitle') }}</p>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/50 text-green-300">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/20 border border-red-500/50 text-red-300">
                {{ session('error') }}
            </div>
            @endif

            <!-- Status Card -->
            <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
                @if($instagramAccount)
                    <!-- Connected State -->
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-6">
                            @if($instagramAccount->profile_picture_url)
                            <img src="{{ $instagramAccount->profile_picture_url }}" alt="Profile" class="w-16 h-16 rounded-full border-2 border-green-500">
                            @else
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-2xl font-bold text-white">
                                {{ strtoupper(substr($instagramAccount->username ?? 'I', 0, 1)) }}
                            </div>
                            @endif
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg font-bold text-white">{{ '@' . ($instagramAccount->username ?? 'Unknown') }}</span>
                                    <span class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs font-semibold">{{ __('instagram.connected') }}</span>
                                </div>
                                <p class="text-gray-400 text-sm">{{ $instagramAccount->name ?? 'Instagram Business Account' }}</p>
                                <p class="text-gray-500 text-xs mt-1">{{ __('instagram.asset_id') }}: {{ $instagramAccount->instagram_user_id }}</p>
                            </div>
                        </div>

                        <!-- Connected Asset Info (Clearer for Meta Reviewers) -->
                        <div class="mb-6 border-l-4 border-blue-500 bg-blue-500/10 p-4 rounded-r-xl">
                            <h4 class="text-sm font-bold text-blue-400 mb-1 uppercase tracking-wider">{{ __('instagram.asset_selection_title') }}</h4>
                            <p class="text-white text-sm font-medium">{{ __('instagram.asset_selection_text', ['username' => $instagramAccount->username]) }}</p>
                        </div>

                        <!-- Token Status -->
                        <div class="bg-gray-900 rounded-xl p-4 mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">{{ __('instagram.token_status') }}</p>
                                    @if($instagramAccount->isTokenExpired())
                                    <p class="text-red-400 font-medium">⚠️ {{ __('instagram.token_expired') }}</p>
                                    @elseif($instagramAccount->isTokenExpiringSoon())
                                    <p class="text-yellow-400 font-medium">⚠️ {{ __('instagram.token_expiring_soon', ['days' => 7]) }}</p>
                                    @else
                                    <p class="text-green-400 font-medium">✓ {{ __('instagram.token_active') }}</p>
                                    @endif
                                </div>
                                @if($instagramAccount->token_expires_at)
                                <div class="text-right">
                                    <p class="text-sm text-gray-400">{{ __('instagram.expiry_date') }}</p>
                                    <p class="text-white font-medium">{{ $instagramAccount->token_expires_at->format('d M Y') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            @if($instagramAccount->isTokenExpiringSoon() && !$instagramAccount->isTokenExpired())
                            <form action="{{ route('instagram.refresh-token') }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 rounded-xl font-semibold transition">
                                    🔄 {{ __('instagram.refresh_token') }}
                                </button>
                            </form>
                            @endif
                            
                            <form action="{{ route('instagram.disconnect') }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('instagram.confirm_disconnect') }}')">
                                @csrf
                                <button type="submit" class="w-full py-3 bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-600/50 rounded-xl font-semibold transition">
                                    {{ __('instagram.disconnect_button') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Disconnected State -->
                    <div class="p-8 text-center">
                        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">{{ __('instagram.title') }}</h3>
                        <p class="text-gray-400 mb-6 max-w-sm mx-auto">
                            {{ __('instagram.subtitle') }}
                        </p>
                        
                        @if($isConfigured ?? false)
                        <a href="{{ route('instagram.connect') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-purple-500 via-pink-500 to-orange-400 hover:from-purple-600 hover:via-pink-600 hover:to-orange-500 rounded-xl font-semibold text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                            </svg>
                            {{ __('instagram.connect_button') }}
                        </a>
                        @else
                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 text-left">
                            <p class="text-yellow-400 font-semibold mb-2">⚠️ {{ __('instagram.not_configured_title') }}</p>
                            <p class="text-gray-400 text-sm">{{ __('instagram.not_configured_text') }}</p>
                        </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Requirements Info -->
            <div class="mt-6 bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                <h4 class="text-sm font-semibold text-white mb-2">📋 {{ __('instagram.requirements_title') }}</h4>
                <ul class="text-sm text-gray-400 space-y-1">
                    <li>• {{ __('instagram.requirement_1') }}</li>
                    <li>• {{ __('instagram.requirement_2') }}</li>
                    <li>• {{ __('instagram.requirement_3') }}</li>
                </ul>
            </div>

            <!-- How it Works -->
            <div class="mt-6 bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                <h4 class="text-sm font-semibold text-white mb-3">🔄 {{ __('instagram.how_it_works_title') }}</h4>
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="p-2">
                        <div class="w-8 h-8 mx-auto mb-1 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">1</div>
                        <p class="text-gray-400">{{ __('instagram.step_1') }}</p>
                    </div>
                    <div class="p-2">
                        <div class="w-8 h-8 mx-auto mb-1 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">2</div>
                        <p class="text-gray-400">{{ __('instagram.step_2') }}</p>
                    </div>
                    <div class="p-2">
                        <div class="w-8 h-8 mx-auto mb-1 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">3</div>
                        <p class="text-gray-400">{{ __('instagram.step_3') }}</p>
                    </div>
                    <div class="p-2">
                        <div class="w-8 h-8 mx-auto mb-1 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">4</div>
                        <p class="text-gray-400">{{ __('instagram.step_4') }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection
