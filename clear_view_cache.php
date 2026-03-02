<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "Membersihkan view cache...\n";
Artisan::call('view:clear');
echo "✅ View cache cleared\n\n";

echo "Membersihkan cache browser...\n";
echo "👉 Silakan refresh halaman dengan Ctrl+F5\n";
