<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$devices = \App\Models\WhatsAppDevice::all();
foreach ($devices as $device) {
    echo "Device ID: {$device->id}\n";
    echo "Session ID: '{$device->session_id}'\n";
    echo "Device Name: {$device->device_name}\n";
    echo "Status: {$device->status}\n";
    echo "\nHTML onclick attribute would be:\n";
    echo "onclick=\"reconnectDevice('{$device->session_id}')\"\n";
    echo "onclick=\"disconnectDevice('{$device->session_id}')\"\n";
    echo "\n";
}
