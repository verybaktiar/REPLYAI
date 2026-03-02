<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DATABASE STATUS ===\n";
$device = \App\Models\WhatsAppDevice::first();
if ($device) {
    echo "ID: " . $device->id . "\n";
    echo "Session: " . $device->session_id . "\n";
    echo "Status: " . $device->status . "\n";
    echo "Phone: " . ($device->phone_number ?: 'N/A') . "\n";
    echo "Last Updated: " . $device->updated_at . "\n\n";
}

echo "=== WA SERVICE STATUS ===\n";
try {
    $url = config('services.whatsapp.url', 'http://127.0.0.1:3001');
    $sessionId = $device->session_id ?? 'unknown';
    
    // Check sessions list
    $response = Illuminate\Support\Facades\Http::timeout(5)->get("$url/sessions");
    echo "All Sessions: " . $response->body() . "\n\n";
    
    // Check specific session
    $response2 = Illuminate\Support\Facades\Http::timeout(5)->get("$url/status", [
        'sessionId' => $sessionId
    ]);
    echo "Session '$sessionId' Status: " . $response2->body() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
