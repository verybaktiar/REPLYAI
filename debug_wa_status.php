<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\WhatsAppDevice;

echo "--- DEBUG WA STATUS ---\n";

// Disable global scopes to see all data
$devices = WhatsAppDevice::withoutGlobalScopes()->get();

foreach ($devices as $device) {
    echo "ID: {$device->id} | UserID: {$device->user_id} | Status: '{$device->status}' | isConnected: " . ($device->isConnected() ? 'TRUE' : 'FALSE') . "\n";
}

echo "\n--- RAW SQL CHECK ---\n";
// Check unique status values
$statuses = \DB::table('whatsapp_devices')->distinct()->pluck('status');
echo "Unique Statuses in DB: " . json_encode($statuses) . "\n";
