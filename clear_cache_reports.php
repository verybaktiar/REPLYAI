<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "Membersihkan cache...\n\n";

Artisan::call('view:clear');
echo "✅ View cache cleared\n";

Artisan::call('cache:clear');
echo "✅ Application cache cleared\n";

Artisan::call('route:clear');
echo "✅ Route cache cleared\n";

echo "\n✨ Selesai! Silakan refresh halaman laporan.";
