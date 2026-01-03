<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\WaConversation;
use App\Models\WaSession;

$session = WaSession::getDefault();
echo "Idle timeout: " . ($session->session_idle_timeout_minutes ?? 30) . " min\n";
echo "Followup timeout: " . ($session->session_followup_timeout_minutes ?? 15) . " min\n\n";

$conversations = WaConversation::all();
echo "Total conversations: " . $conversations->count() . "\n\n";

foreach ($conversations as $conv) {
    echo "Phone: " . $conv->phone_number . "\n";
    echo "  session_status: " . $conv->session_status . "\n";
    echo "  last_user_reply_at: " . ($conv->last_user_reply_at ?? 'NULL') . "\n";
    echo "  followup_sent_at: " . ($conv->followup_sent_at ?? 'NULL') . "\n";
    
    if ($conv->last_user_reply_at) {
        $minutes = now()->diffInMinutes($conv->last_user_reply_at);
        echo "  Minutes since last reply: " . $minutes . "\n";
        echo "  Should trigger (>= 1 min): " . ($minutes >= 1 ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
}
