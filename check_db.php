<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'Count: ' . \App\Models\WhatsAppDevice::count() . PHP_EOL;
$d = \App\Models\WhatsAppDevice::first();
if ($d) {
    echo 'ID: ' . $d->id . PHP_EOL;
    echo 'Session: ' . ($d->session_id ?: '(empty)') . PHP_EOL;
}
