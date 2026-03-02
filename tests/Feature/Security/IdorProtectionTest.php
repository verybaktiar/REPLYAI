<?php

use App\Models\User;
use App\Models\WaConversation;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Test IDOR Protection - User A tidak bisa akses data User B
 * Security: CRITICAL-001
 */

describe('IDOR Protection Tests', function () {
    
    beforeEach(function () {
        $this->userA = User::factory()->create(['name' => 'User A']);
        $this->userB = User::factory()->create(['name' => 'User B']);
        
        // Buat conversation untuk User A
        $this->conversationA = WaConversation::create([
            'phone_number' => '6281234567890',
            'display_name' => 'Customer A',
            'user_id' => $this->userA->id,
            'status' => 'bot_active',
        ]);
        
        // Buat conversation untuk User B
        $this->conversationB = WaConversation::create([
            'phone_number' => '6289876543210',
            'display_name' => 'Customer B',
            'user_id' => $this->userB->id,
            'status' => 'bot_active',
        ]);
    });

    test('user cannot takeover another users whatsapp conversation', function () {
        $this->actingAs($this->userA);
        
        $response = $this->postJson("/takeover/wa/{$this->conversationB->phone_number}/takeover");
        
        $response->assertStatus(404)
            ->assertJson(['error' => 'Conversation not found']);
    });

    test('user can takeover their own whatsapp conversation', function () {
        $this->actingAs($this->userA);
        
        $response = $this->postJson("/takeover/wa/{$this->conversationA->phone_number}/takeover");
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        
        // Verify status changed
        $this->conversationA->refresh();
        expect($this->conversationA->status)->toBe('agent_handling');
    });

    test('user cannot handback another users conversation', function () {
        // Set conversation B as taken over
        $this->conversationB->update([
            'status' => 'agent_handling',
            'assigned_cs' => 'User B',
        ]);
        
        $this->actingAs($this->userA);
        
        $response = $this->postJson("/takeover/wa/{$this->conversationB->phone_number}/handback");
        
        $response->assertStatus(404);
    });

    test('user cannot get status of another users conversation', function () {
        $this->actingAs($this->userA);
        
        $response = $this->getJson("/takeover/wa/{$this->conversationB->phone_number}/status");
        
        // Should return default status (not found for this user)
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'bot_active',
                'assigned_cs' => null,
            ]);
    });
});

describe('Instagram IDOR Protection', function () {
    
    beforeEach(function () {
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();
        
        $this->igConversationA = Conversation::create([
            'instagram_user_id' => 'ig_user_a',
            'display_name' => 'IG Customer A',
            'user_id' => $this->userA->id,
            'status' => 'bot_handling',
        ]);
        
        $this->igConversationB = Conversation::create([
            'instagram_user_id' => 'ig_user_b',
            'display_name' => 'IG Customer B',
            'user_id' => $this->userB->id,
            'status' => 'bot_handling',
        ]);
    });

    test('user cannot takeover another users instagram conversation', function () {
        $this->actingAs($this->userA);
        
        $response = $this->postJson("/takeover/ig/{$this->igConversationB->id}/takeover");
        
        $response->assertStatus(404)
            ->assertJson(['error' => 'Conversation not found']);
    });

    test('user can takeover their own instagram conversation', function () {
        $this->actingAs($this->userA);
        
        $response = $this->postJson("/takeover/ig/{$this->igConversationA->id}/takeover");
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    });
});
