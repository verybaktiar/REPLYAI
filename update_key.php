<?php

use App\Models\WebWidget;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$targetKey = 'rw_ou4CdK55ChwcpOVD6ArxEb0RtzBmFFy5'; // Key from user's HTML
$widget = WebWidget::first();

if ($widget) {
    echo "Old Key: " . $widget->api_key . "\n";
    $widget->api_key = $targetKey;
    $widget->is_active = true; // Ensure it's active
    $widget->save();
    echo "New Key: " . $widget->api_key . "\n";
    echo "Widget Updated Successfully!\n";
} else {
    echo "No widget found to update.\n";
}
