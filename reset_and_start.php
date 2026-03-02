<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Reset device
$device = \App\Models\WhatsAppDevice::first();
if ($device) {
    $device->update([
        'status' => 'disconnected',
        'phone_number' => null,
        'profile_name' => null
    ]);
    echo "Device reset: {$device->session_id}\n";
    echo "Status: {$device->fresh()->status}\n";
} else {
    echo "No device found\n";
}

echo "\n=== Clear caches ===\n";
 Artisan::call('cache:clear');
 Artisan::call('config:clear');
 Artisan::call('route:clear');
 Artisan::call('view:clear');
echo "Caches cleared\n";
