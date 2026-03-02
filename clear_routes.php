<?php
// Script untuk clear route cache
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "Clearing route cache...\n";
Artisan::call('route:clear');
echo Artisan::output();

echo "Clearing view cache...\n";
Artisan::call('view:clear');
echo Artisan::output();

echo "Clearing config cache...\n";
Artisan::call('config:clear');
echo Artisan::output();

echo "Done!\n";
