<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ActivityLogService;

$sessionId = 'grafisatu-8f708b';

echo "=== Testing Reconnect ===\n";
try {
    $device = \App\Models\WhatsAppDevice::where('session_id', $sessionId)->firstOrFail();
    echo "Device found: {$device->id}\n";
    
    $waService = app(\App\Services\WhatsAppService::class);
    $result = $waService->createSession($sessionId);
    echo "Create session result: " . json_encode($result) . "\n";
    
    $device->update(['status' => 'scanning']);
    echo "Status updated to scanning\n";
    echo "SUCCESS!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Delete ===\n";
try {
    $device = \App\Models\WhatsAppDevice::withoutGlobalScopes()->where('session_id', $sessionId)->first();
    if ($device) {
        echo "Device found: {$device->id}\n";
        
        // Test disconnect
        $waService = app(\App\Services\WhatsAppService::class);
        $result = $waService->disconnect($sessionId);
        echo "Disconnect result: " . json_encode($result) . "\n";
        
        // Test log delete
        echo "Logging delete...\n";
        ActivityLogService::logDeleted($device, "Menghapus perangkat WhatsApp: {$device->device_name}");
        echo "Log created\n";
        
        $device->delete();
        echo "Device deleted\n";
        echo "SUCCESS!\n";
    } else {
        echo "Device not found!\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
