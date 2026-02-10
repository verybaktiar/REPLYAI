<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\WhatsAppDevice;

echo "--- DEBUG USER & DEVICES ---\n";

$users = User::where('email', 'like', '%coba%')->orWhere('id', 24)->get();

foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id}, Email: {$user->email})\n";
    $devices = WhatsAppDevice::withoutGlobalScopes()->where('user_id', $user->id)->get();
    echo "  Devices (" . $devices->count() . "):\n";
    foreach ($devices as $device) {
        echo "    - ID: {$device->id}, Status: {$device->status}\n";
    }
}
