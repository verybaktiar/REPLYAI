<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$device = App\Models\WhatsAppDevice::where('session_id', 'grafisatu-B436D9')->first();
if ($device) {
    echo "Before: " . $device->status . "\n";
    
    // Simulate status update from webhook
    $data = [
        'sessionId' => 'grafisatu-B436D9',
        'status' => 'disconnected',
        'reason' => 'QR refs attempts ended',
        'shouldReconnect' => true
    ];
    
    $service = app(App\Services\WhatsAppService::class);
    $service->handleStatusUpdate($data);
    
    // Refresh device
    $device->refresh();
    echo "After: " . $device->status . "\n";
    echo "Last disconnect reason: " . ($device->last_disconnect_reason ?? 'null') . "\n";
} else {
    echo "Device not found\n";
}
