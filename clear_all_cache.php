<?php
// Script untuk clear semua cache
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "🧹 Clearing route cache...\n";
Artisan::call('route:clear');
echo Artisan::output();

echo "🧹 Clearing view cache...\n";
Artisan::call('view:clear');
echo Artisan::output();

echo "🧹 Clearing config cache...\n";
Artisan::call('config:clear');
echo Artisan::output();

echo "🧹 Clearing application cache...\n";
Artisan::call('cache:clear');
echo Artisan::output();

echo "✅ All caches cleared!\n";
echo "\n💡 Silakan refresh halaman dan coba checkout lagi.\n";
