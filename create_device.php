<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WhatsAppDevice;

$device = WhatsAppDevice::create([
    'user_id' => 28,
    'session_id' => 'grafisatu-' . strtoupper(substr(md5(uniqid()), 0, 6)),
    'device_name' => 'WhatsApp Grafisatu',
    'phone_number' => '6285168842886',
    'status' => 'scanning',
    'is_active' => true
]);

echo "Created device: " . $device->session_id . "\n";
echo "ID: " . $device->id . "\n";
