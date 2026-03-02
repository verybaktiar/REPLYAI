<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Hapus device B436D9 yang lama
$device = App\Models\WhatsAppDevice::where('session_id', 'grafisatu-B436D9')->first();
if ($device) {
    echo "Menghapus device lama: " . $device->session_id . "\n";
    $device->delete();
    echo "✅ Device lama dihapus\n";
}

// Tampilkan device aktif
$devices = App\Models\WhatsAppDevice::all();
echo "\n📱 Device aktif sekarang:\n";
foreach ($devices as $d) {
    echo "- " . $d->session_id . " | Status: " . $d->status . " | User: " . $d->user_id . "\n";
}
