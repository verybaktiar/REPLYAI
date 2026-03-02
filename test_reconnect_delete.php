<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$waService = app(\App\Services\WhatsAppService::class);
$sessionId = 'grafisatu-8f708b';

echo "=== Test Reconnect (Create Session) ===\n";
try {
    $result = $waService->createSession($sessionId);
    echo "Success!\n";
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Device ===\n";
$device = \App\Models\WhatsAppDevice::where('session_id', $sessionId)->first();
if ($device) {
    echo "Device found: ID={$device->id}, Session={$device->session_id}\n";
} else {
    echo "Device NOT found!\n";
}
