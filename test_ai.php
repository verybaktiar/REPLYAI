<?php

use App\Services\AiAnswerService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing AiAnswerService...\n";

try {
    $service = app(AiAnswerService::class);
    $response = $service->answerWithContext("Halo, jam besuk kapan ya?");
    echo "Response: " . $response . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
