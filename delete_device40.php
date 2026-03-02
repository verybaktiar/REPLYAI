<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Delete device 40
$device = App\Models\WhatsAppDevice::find(40);
if ($device) {
    echo "Deleting: " . $device->session_id . " (User: " . $device->user_id . ")\n";
    $device->delete();
    echo "Deleted!\n";
}

// Show remaining
$devices = App\Models\WhatsAppDevice::all();
echo "\nRemaining devices:\n";
foreach ($devices as $d) {
    echo $d->id . ': ' . $d->session_id . ' - ' . $d->status . ' (User: ' . $d->user_id . ')' . PHP_EOL;
}
