<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$devices = \App\Models\WhatsAppDevice::all();
foreach ($devices as $device) {
    echo "ID: {$device->id} | Session: {$device->session_id} | Name: {$device->device_name} | Status: {$device->status}\n";
}
