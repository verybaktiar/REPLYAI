<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ✅ Laravel Scheduler (tanpa Kernel.php)
app()->booted(function () {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);

    $schedule->command('sync:chatwoot --messages')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sync-chatwoot.log'));

    $schedule->command('bot:run-auto-reply')
        ->everyMinute()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/autoreply.log'));

    // WhatsApp session timeout check
    $schedule->command('wa:session-timeout')
        ->everyMinute()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/wa-session-timeout.log'));

    // Sequence/Drip Campaign processor
    $schedule->command('sequence:process')
        ->everyMinute()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/sequence-process.log'));

    // ==========================================
    // SUBSCRIPTION SYSTEM SCHEDULERS
    // ==========================================

    // Cek langganan expired setiap hari jam 00:01
    // Menandai expired, proses grace period, kirim reminder
    $schedule->command('subscription:check-expired --send-reminders')
        ->dailyAt('00:01')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/subscription-expired.log'));

    // Kirim reminder untuk langganan yang akan expire
    // Jalan setiap hari jam 09:00 pagi
    $schedule->command('subscriptions:send-expiry-reminders')
        ->dailyAt('09:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/subscription-reminders.log'));

    // Expired subscriptions handler (grace period)
    // Jalan setiap hari jam 01:00
    $schedule->command('subscriptions:expire')
        ->dailyAt('01:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/subscription-expire.log'));

    // Reset penggunaan bulanan setiap tanggal 1 jam 00:00
    // Reset counter AI messages, broadcasts, dll
    $schedule->command('usage:reset-monthly')
        ->monthlyOn(1, '00:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/usage-reset.log'));

    // ==========================================
    // INSTAGRAM TOKEN REFRESH
    // ==========================================

    // Refresh Instagram tokens yang akan expired dalam 7 hari
    // Jalan setiap hari jam 02:00

    $schedule->command('instagram:refresh-tokens')
        ->dailyAt('02:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/instagram-token-refresh.log'));

    // ==========================================
    // AI AUTOMATION JOBS
    // ==========================================

    // Auto-Follow Up (24h Inactivity) - Check every hour
    $schedule->job(new \App\Jobs\SendAutoFollowupJob)
        ->hourly()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/ai-followup.log'));

    // Daily Admin Summary - Every day at 22:00
    $schedule->job(new \App\Jobs\SendDailyAdminSummaryJob)
        ->dailyAt('22:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/ai-daily-summary.log'));

    // System Health Self-Healing
    $schedule->command('system:health-check')
        ->everyFiveMinutes()
        ->withoutOverlapping();

    // ==========================================
    // WHATSAPP SESSION MONITORING
    // ==========================================
    
    // Check WhatsApp sessions every 2 minutes
    $schedule->command('whatsapp:check-sessions')
        ->everyTwoMinutes()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/whatsapp-session-check.log'));

    // ==========================================
    // CLEANUP JOBS
    // ==========================================
    
    // Cleanup old logs and data - daily at 03:00
    $schedule->command('system:cleanup')
        ->dailyAt('03:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/cleanup.log'));

});
