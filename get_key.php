<?php

use App\Models\WebWidget;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$widget = WebWidget::first();
if ($widget) {
    echo "CORRECT_KEY: " . $widget->api_key;
} else {
    echo "NO_WIDGET_FOUND";
}
