<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\WhatsAppDevice;
use App\Models\User;
use App\Models\WaSession;

echo "=== DEBUG DB CHECK ===\n";

$users = User::all();
echo "Users count: " . $users->count() . "\n";
foreach ($users as $user) {
    echo "User: {$user->id} - {$user->email}\n";
}

echo "\n--- WhatsApp Devices (Raw) ---\n";
$devices = WhatsAppDevice::withoutGlobalScopes()->get();
echo "Devices count: " . $devices->count() . "\n";
foreach ($devices as $device) {
    echo "Device ID: {$device->id}, Session: {$device->session_id}, Status: {$device->status}, User: {$device->user_id}\n";
}

echo "\n--- WaSessions (Legacy?) ---\n";
try {
    $sessions = WaSession::all();
    echo "WaSessions count: " . $sessions->count() . "\n";
    foreach ($sessions as $session) {
        echo "Session: {$session->session_id}, Status: {$session->status}\n";
    }
} catch (\Exception $e) {
    echo "WaSession table not found or error: " . $e->getMessage() . "\n";
}
