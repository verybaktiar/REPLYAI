<?php
// Script untuk clear cache routes
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "Membersihkan route cache...\n";
Artisan::call('route:clear');
echo "Selesai!\n";

echo "\nMengecek route whatsapp.api.tags.index...\n";
$routes = collect(Artisan::call('route:list'));
