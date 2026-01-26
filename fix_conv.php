<?php
use App\Models\InstagramAccount;
use App\Models\Conversation;

$account = InstagramAccount::first();
if ($account) {
    echo "Found user ID: " . $account->user_id . "\n";
    $updated = Conversation::whereNull('user_id')->update(['user_id' => $account->user_id]);
    echo "Updated $updated conversations.\n";
} else {
    echo "No Instagram Account found.\n";
}
