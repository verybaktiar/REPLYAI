<?php

use App\Models\User;
use App\Models\WaBroadcast;
use App\Models\WaMessage;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Test Mass Assignment Protection - User ID Injection
 * Security: CRITICAL-002
 */

describe('Mass Assignment Protection', function () {
    
    beforeEach(function () {
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
    });

    test('wa broadcast ignores injected user_id', function () {
        $this->actingAs($this->userA);
        
        // Attempt to inject user_id
        $broadcast = WaBroadcast::create([
            'title' => 'Test Broadcast',
            'message' => 'Test message',
            'user_id' => $this->userB->id, // Injection attempt
        ]);
        
        // Should be assigned to logged in user (User A), not User B
        expect($broadcast->user_id)->toBe($this->userA->id);
        expect($broadcast->user_id)->not->toBe($this->userB->id);
    });

    test('wa message ignores injected user_id', function () {
        $this->actingAs($this->userA);
        
        $message = WaMessage::create([
            'phone_number' => '6281234567890',
            'message' => 'Test',
            'direction' => 'outgoing',
            'user_id' => $this->userB->id, // Injection attempt
        ]);
        
        // Should be assigned to logged in user
        expect($message->user_id)->toBe($this->userA->id);
    });

    test('conversation ignores injected user_id', function () {
        $this->actingAs($this->userA);
        
        $conversation = Conversation::create([
            'instagram_user_id' => 'test_ig_id',
            'display_name' => 'Test',
            'user_id' => $this->userB->id, // Injection attempt
        ]);
        
        // Should be assigned to logged in user
        expect($conversation->user_id)->toBe($this->userA->id);
    });

    test('user_id cannot be changed on update', function () {
        $this->actingAs($this->userA);
        
        $broadcast = WaBroadcast::create([
            'title' => 'Test',
            'message' => 'Test',
        ]);
        
        expect($broadcast->user_id)->toBe($this->userA->id);
        
        // Attempt to change user_id
        $broadcast->update([
            'title' => 'Updated',
            'user_id' => $this->userB->id, // Should be ignored
        ]);
        
        $broadcast->refresh();
        
        // User ID should remain unchanged
        expect($broadcast->user_id)->toBe($this->userA->id);
        expect($broadcast->title)->toBe('Updated');
    });
});
