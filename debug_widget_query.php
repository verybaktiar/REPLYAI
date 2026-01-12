<?php

use App\Models\WebWidget;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = 'rw_ou4CdK55ChwcpOVD6ArxEb0RtzBmFFy5';
$w = WebWidget::where('api_key', $key)->first();

if (!$w) {
    echo "Query by key FAILED. Key not found.\n";
    // List all keys
    echo "Available keys:\n";
    foreach(WebWidget::all() as $widget) {
        echo "- '" . $widget->api_key . "'\n";
    }
} else {
    echo "Query by key SUCCESS.\n";
    echo "ID: " . $w->id . "\n";
    echo "Is Active: " . var_export($w->is_active, true) . "\n";
    
    // Check specific query
    $w2 = WebWidget::where('api_key', $key)->where('is_active', true)->first();
    if ($w2) {
        echo "Query with is_active=true SUCCESS.\n";
    } else {
        echo "Query with is_active=true FAILED.\n";
    }
}
