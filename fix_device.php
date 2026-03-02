<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$device = \App\Models\WhatsAppDevice::first();
if ($device) {
    // Generate new session_id
    $newSessionId = 'grafisatu-' . substr(md5(uniqid()), 0, 6);
    $device->update([
        'session_id' => $newSessionId,
        'status' => 'disconnected'
    ]);
    echo "Device updated with new session_id: $newSessionId\n";
    echo "Please scan QR from dashboard for session: $newSessionId\n";
} else {
    echo "No device found. Please create device from dashboard.\n";
}
