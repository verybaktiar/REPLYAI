<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$devices = App\Models\WhatsAppDevice::all();
foreach ($devices as $d) {
    echo $d->id . ': ' . $d->session_id . ' - ' . $d->status . ' (User: ' . $d->user_id . ')' . PHP_EOL;
}
