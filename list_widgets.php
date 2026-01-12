<?php

use App\Models\WebWidget;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Widgets in DB:\n";
$widgets = WebWidget::all();

if ($widgets->isEmpty()) {
    echo "No widgets found!\n";
} else {
    foreach ($widgets as $w) {
        echo "ID: {$w->id} | Name: {$w->name} | API Key: {$w->api_key} | Active: {$w->is_active}\n";
    }
}
