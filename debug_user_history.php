<?php

use App\Models\WebConversation;
use App\Models\WebWidget;
use App\Services\AiAnswerService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$visitorId = 'v_3xdtuyveemk2pe6kp';
$apiKey = 'rw_ou4CdK55ChwcpOVD6ArxEb0RtzBmFFy5';

echo "Debugging history for visitor: $visitorId\n";

$widget = WebWidget::where('api_key', $apiKey)->first();
if (!$widget) die("Widget not found\n");

$conversation = WebConversation::where('widget_id', $widget->id)
    ->where('visitor_id', $visitorId)
    ->first();

if (!$conversation) die("Conversation not found\n");

$recentMessages = $conversation->messages()
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get()
    ->reverse()
    ->map(function ($msg) {
        return [
            'role' => $msg->sender_type === 'visitor' ? 'user' : 'assistant',
            'content' => $msg->content, // Check if this is null/empty
        ];
    })
    ->values()
    ->toArray();

echo "History Count: " . count($recentMessages) . "\n";
print_r($recentMessages);

echo "\nAttempting AI call with this history...\n";
$service = app(AiAnswerService::class);
try {
    $response = $service->answerWithContext("informasi poli gigi", $recentMessages);
    echo "Response: " . $response . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
