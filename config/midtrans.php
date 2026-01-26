<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Midtrans Payment Gateway.
    | Gunakan Sandbox untuk testing, Production untuk live.
    |
    */

    // Credentials dari Midtrans Dashboard
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    // Environment: true = Production, false = Sandbox
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Sanitize input untuk keamanan
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    // 3D Secure untuk kartu kredit
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    // Callback URLs
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false) 
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    // Notification URL (webhook)
    'notification_url' => env('APP_URL') . '/api/midtrans/notification',

    // Finish URL setelah payment
    'finish_url' => env('APP_URL') . '/checkout/midtrans/finish',

    // Error URL jika payment gagal
    'error_url' => env('APP_URL') . '/checkout/midtrans/error',

    // Pending URL jika payment pending
    'pending_url' => env('APP_URL') . '/checkout/midtrans/pending',
];
