<?php

use App\Models\User;
use App\Models\InstagramAccount;
use App\Models\Conversation;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEBUG START ---\n";

$users = User::all();

foreach ($users as $user) {
    echo "User: [{$user->id}] {$user->name} ({$user->email})\n";
    
    $igAccount = InstagramAccount::where('user_id', $user->id)->first();
    if ($igAccount) {
        echo "  - Instagram Account Found:\n";
        echo "    - ID: {$igAccount->id}\n";
        echo "    - IG User ID: {$igAccount->instagram_user_id}\n";
        echo "    - Username: {$igAccount->username}\n";
        echo "    - Is Active: " . ($igAccount->is_active ? 'YES' : 'NO') . "\n";
    } else {
        echo "  - No Instagram Account Linked.\n";
    }

    $conversations = Conversation::where('user_id', $user->id)->get();
    echo "  - Conversations count: {$conversations->count()}\n";
    
    if ($conversations->count() > 0) {
        $sample = $conversations->first();
        echo "    - Sample Conversation ID: {$sample->id}\n";
        echo "    - Sample IG User ID (in convo): {$sample->instagram_user_id}\n";
        echo "    - Created At: {$sample->created_at}\n";
    }
    
    echo "--------------------------\n";
}

echo "--- DEBUG END ---\n";
