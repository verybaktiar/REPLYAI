<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing WA Service connection...\n";

try {
    $url = config('services.whatsapp.url', 'http://127.0.0.1:3001');
    echo "URL: $url\n";
    
    $response = Illuminate\Support\Facades\Http::timeout(5)->get("$url/health");
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTesting /connect endpoint...\n";
try {
    $url = config('services.whatsapp.url', 'http://127.0.0.1:3001');
    $response = Illuminate\Support\Facades\Http::timeout(10)->post("$url/connect", [
        'sessionId' => 'test-session-id'
    ]);
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
