<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\WaMessage;
use App\Models\WaConversation;
use App\Models\Announcement;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS if APP_URL starts with https
        if (str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        // Define rate limiters
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Register Observers for automated activity logging
        \App\Models\KbArticle::observe(\App\Observers\ActivityObserver::class);
        \App\Models\AutoReplyRule::observe(\App\Observers\ActivityObserver::class);
        \App\Models\User::observe(\App\Observers\ActivityObserver::class);
        \App\Models\Subscription::observe(\App\Observers\ActivityObserver::class);

        // Share sidebar stats with all views that include the sidebar
        View::composer('components.sidebar', function ($view) {
            try {
                $today = Carbon::today();
                
                // Get today's message stats from WaMessage
                $messagesToday = WaMessage::whereDate('created_at', $today)->count();
                $botReplies = WaMessage::whereDate('created_at', $today)
                    ->where('direction', 'outgoing')
                    ->where('is_bot_reply', true)
                    ->count();
                $humanReplies = WaMessage::whereDate('created_at', $today)
                    ->where('direction', 'outgoing')
                    ->where('is_bot_reply', false)
                    ->count();
                
                $view->with('sidebar_stats', [
                    'messages_today' => $messagesToday,
                    'bot_replies' => $botReplies,
                    'human_replies' => $humanReplies,
                ]);
                
                // Check WhatsApp connection status
                $waConnected = WaConversation::where('updated_at', '>=', Carbon::now()->subHours(24))->exists();
                $view->with('wa_connected', $waConnected);
                
            } catch (\Exception $e) {
                // If tables don't exist or other errors, provide defaults
                $view->with('sidebar_stats', [
                    'messages_today' => 0,
                    'bot_replies' => 0,
                    'human_replies' => 0,
                ]);
                $view->with('wa_connected', false);
            }
        });

        // Share Announcements with User Layout
        View::composer('layouts.dark', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $readIds = $user->read_announcements ?? [];
                
                $announcements = Announcement::where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>=', now())
                    ->where(function($q) use ($user) {
                        $q->where('audience', 'all');
                        if ($user->is_vip) $q->orWhere('audience', 'vip');
                        if ($user->subscription && $user->subscription->status === 'active') $q->orWhere('audience', 'active');
                    })
                    ->whereNotIn('id', $readIds)
                    ->orderByDesc('created_at')
                    ->get();
                
                $view->with('unread_announcements', $announcements);
            }
        });
    }
}
