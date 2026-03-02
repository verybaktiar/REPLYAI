<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Update device to empty session
$device = \App\Models\WhatsAppDevice::first();
if ($device) {
    $device->update([
        'session_id' => '',
        'status' => 'disconnected',
        'phone_number' => null
    ]);
    echo "Device reset successful. Please scan QR from dashboard.\n";
} else {
    echo "No device found.\n";
}
