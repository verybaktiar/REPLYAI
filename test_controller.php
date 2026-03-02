<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Cek device yang ada
echo "=== Devices in DB ===\n";
$devices = \App\Models\WhatsAppDevice::all();
foreach ($devices as $d) {
    echo "ID: {$d->id}, Session: {$d->session_id}, Status: {$d->status}\n";
}

// Simulasi reconnect
echo "\n=== Test Reconnect ===\n";
$sessionId = 'test-device-123';
try {
    $device = \App\Models\WhatsAppDevice::where('session_id', $sessionId)->first();
    if (!$device) {
        echo "Device not found for session: $sessionId\n";
    } else {
        echo "Device found: {$device->id}\n";
        $waService = app(\App\Services\WhatsAppService::class);
        $result = $waService->createSession($sessionId);
        print_r($result);
        $device->update(['status' => 'scanning']);
        echo "Status updated to scanning\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
