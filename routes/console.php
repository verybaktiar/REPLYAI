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

});
