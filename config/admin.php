<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin IP Whitelist
    |--------------------------------------------------------------------------
    |
    | Configure IP addresses or CIDR ranges that are allowed to access
    | the admin panel. Leave empty to allow all IPs.
    |
    | Example: ['203.190.53.0/24', '192.168.1.1']
    |
    */
    'allowed_ips' => env('ADMIN_ALLOWED_IPS', '') 
        ? explode(',', env('ADMIN_ALLOWED_IPS', '')) 
        : [],
    
    /*
    |--------------------------------------------------------------------------
    | Admin Session Configuration
    |--------------------------------------------------------------------------
    |
    | Configure session timeout and regeneration settings for admin users.
    |
    */
    'session' => [
        'lifetime' => 60, // Session lifetime in minutes
        'regenerate_interval' => 15, // Regenerate token every X minutes
        'timeout_warning' => 5, // Show warning X minutes before timeout
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    |
    | Enable or require 2FA for admin users.
    |
    */
    '2fa' => [
        'enabled' => env('ADMIN_2FA_ENABLED', true),
        'required' => env('ADMIN_2FA_REQUIRED', false), // Force all admins to use 2FA
        'issuer' => env('ADMIN_2FA_ISSUER', config('app.name')),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    |
    | Configure password requirements for admin users.
    |
    */
    'password_policy' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special' => true,
        'expire_days' => 90, // 0 = never expire
        'prevent_reuse' => 5, // Prevent last X passwords
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for admin operations.
    |
    */
    'rate_limit' => [
        'login_attempts' => 5,
        'login_decay_minutes' => 5,
        'api_requests_per_minute' => 60,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure notification settings for security events.
    |
    */
    'notifications' => [
        'telegram' => [
            'enabled' => env('ADMIN_TELEGRAM_ENABLED', false),
            'bot_token' => env('ADMIN_TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('ADMIN_TELEGRAM_CHAT_ID'),
        ],
        'email' => [
            'enabled' => env('ADMIN_EMAIL_ALERTS_ENABLED', true),
            'recipients' => explode(',', env('ADMIN_ALERT_EMAILS', '')),
        ],
        'events' => [
            'failed_login' => true,
            'suspicious_activity' => true,
            'high_risk_action' => true,
            'ip_changed' => true,
            'new_device' => true,
        ],
    ],
];
