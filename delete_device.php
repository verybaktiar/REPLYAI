<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Delete device 39
$device = App\Models\WhatsAppDevice::find(39);
if ($device) {
    echo "Deleting: " . $device->session_id . PHP_EOL;
    $device->delete();
    echo "Deleted!\n";
} else {
    echo "Device not found\n";
}

// Show remaining
$devices = App\Models\WhatsAppDevice::all();
echo "\nRemaining devices:\n";
foreach ($devices as $d) {
    echo $d->id . ': ' . $d->session_id . ' - ' . $d->status . PHP_EOL;
}
