<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\WaMessage;
use App\Models\WaConversation;
use Carbon\Carbon;

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
    }
}
