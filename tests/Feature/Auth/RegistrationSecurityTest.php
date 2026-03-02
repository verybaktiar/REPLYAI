<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Test Security Features untuk Registration
 * - Disposable email blocking
 * - CAPTCHA validation
 * - Rate limiting
 */

describe('Registration Security Features', function () {
    
    beforeEach(function () {
        // Disable CAPTCHA untuk testing (kecuali test CAPTCHA itu sendiri)
        config(['services.captcha.enabled' => false]);
    });

    test('registration rejects disposable email addresses', function () {
        $disposableEmails = [
            'test@tempmail.com',
            'user@10minutemail.com',
            'admin@guerrillamail.com',
            'test@yopmail.com',
            'user@mailinator.com',
            'admin@throwawaymail.com',
        ];

        foreach ($disposableEmails as $email) {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertSessionHasErrors(['email']);
            
            // Pastikan user tidak dibuat
            expect(User::where('email', $email)->exists())->toBeFalse();
        }
    });

    test('registration accepts valid email addresses', function () {
        $validEmails = [
            'test@gmail.com',
            'user@yahoo.com',
            'admin@outlook.com',
            'test@company.co.id',
            'user@business.com',
        ];

        foreach ($validEmails as $index => $email) {
            $response = $this->post('/register', [
                'name' => 'Test User ' . $index,
                'email' => $email,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertRedirect('/login');
            
            // Pastikan user dibuat
            expect(User::where('email', $email)->exists())->toBeTrue();
        }
    });

    test('registration has rate limiting', function () {
        // Make 10 requests (the limit)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/register', [
                'name' => 'Test User',
                'email' => "test{$i}@gmail.com",
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);
        }

        // 11th request should be rate limited
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test11@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(429); // Too Many Requests
    });

    test('captcha validation fails with invalid token', function () {
        // Enable CAPTCHA
        config(['services.captcha.enabled' => true]);
        config(['services.captcha.secret' => 'test_secret']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'h-captcha-response' => 'invalid_token',
        ]);

        $response->assertSessionHasErrors(['captcha']);
    });

    test('captcha is required when enabled', function () {
        // Enable CAPTCHA
        config(['services.captcha.enabled' => true]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            // Missing CAPTCHA response
        ]);

        $response->assertSessionHasErrors(['h-captcha-response']);
    });
});

describe('Login Security Features', function () {
    
    test('login has rate limiting', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        // Make 10 failed login attempts
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 11th request should be rate limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429); // Too Many Requests
    });
});
