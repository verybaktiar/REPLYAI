<?php

use App\Services\AiAnswerService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing AI Service to trigger log...\n";

try {
    $service = app(AiAnswerService::class);
    // Mimic the failing request
    $history = [
        ['role' => 'user', 'content' => 'halo'],
        ['role' => 'assistant', 'content' => 'Halo kak!'],
    ];
    $response = $service->answerWithContext("saya mau daftar ke poli gigi bisa kak", $history);
    echo "Response: " . $response . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
