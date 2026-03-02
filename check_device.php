<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$device = App\Models\WhatsAppDevice::where('session_id', 'grafisatu-B436D9')->first();
if ($device) {
    echo 'ID: ' . $device->id . PHP_EOL;
    echo 'Session: ' . $device->session_id . PHP_EOL;
    echo 'Status: ' . $device->status . PHP_EOL;
    echo 'Updated: ' . $device->updated_at . PHP_EOL;
} else {
    echo 'Device not found' . PHP_EOL;
}
