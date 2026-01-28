<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\WhatsAppDevice;
use Illuminate\Support\Facades\DB;

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$devices = WhatsAppDevice::whereIn('session_id', ['hp-utama-a0FYNY', '6289652046618'])
    ->orWhere('phone_number', '6289652046618')
    ->get();

foreach ($devices as $d) {
    echo "--- Device ID: {$d->id} ---\n";
    echo "Session ID: {$d->session_id}\n";
    echo "Phone Number: {$d->phone_number}\n";
    echo "Business Profile ID: " . ($d->business_profile_id ?? 'NULL') . "\n";
    
    $profile = DB::table('business_profiles')->where('id', $d->business_profile_id)->first();
    if ($profile) {
        echo "Profile Found! User ID in Profile: {$profile->user_id}\n";
        $user = DB::table('users')->where('id', $profile->user_id)->first();
        if ($user) {
            echo "User Found! Name: {$user->name}, Email: {$user->email}\n";
        } else {
            echo "USER NOT FOUND in users table for ID: {$profile->user_id}\n";
        }
    } else {
        echo "BUSINESS PROFILE NOT FOUND in business_profiles table for ID: {$d->business_profile_id}\n";
    }
    echo "\n";
}
