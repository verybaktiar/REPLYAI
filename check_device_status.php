<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$device = \App\Models\WhatsAppDevice::first();
if ($device) {
    echo "Device ID: {$device->id}\n";
    echo "Session ID: {$device->session_id}\n";
    echo "Status: {$device->status}\n";
    echo "Device Name: {$device->device_name}\n";
    echo "Phone: {$device->phone_number}\n";
    echo "Profile: {$device->profile_name}\n";
    echo "Updated: {$device->updated_at}\n";
} else {
    echo "No device found!\n";
}
