<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstagramWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\Api\WebChatController;

Route::get('/instagram/webhook', [InstagramWebhookController::class, 'verify']);
Route::post('/instagram/webhook', [InstagramWebhookController::class, 'handle']);

// WhatsApp Webhooks (dari Node.js service)
Route::prefix('whatsapp/webhook')->group(function () {
    Route::post('/message', [WhatsAppWebhookController::class, 'handleMessage']);
    Route::post('/status', [WhatsAppWebhookController::class, 'handleStatus']);
    Route::post('/qr', [WhatsAppWebhookController::class, 'handleQr']);
});

// Web Chat Widget API
Route::prefix('web')->group(function () {
    Route::get('/widget/{api_key}', [WebChatController::class, 'getWidget']);
    Route::post('/chat', [WebChatController::class, 'sendMessage']);
    Route::get('/conversation/{visitor_id}', [WebChatController::class, 'getConversation']);
    Route::post('/visitor', [WebChatController::class, 'updateVisitor']);
    Route::get('/poll', [WebChatController::class, 'poll']);
});

// Midtrans Payment Gateway Webhooks
Route::prefix('midtrans')->group(function () {
    Route::post('/notification', [App\Http\Controllers\Api\MidtransWebhookController::class, 'handleNotification']);
    Route::get('/status/{invoice}', [App\Http\Controllers\Api\MidtransWebhookController::class, 'checkStatus']);
});

// Public API (no auth required)
Route::get('/plans', function () {
    $plans = \App\Models\Plan::where('is_active', true)
        ->where('is_free', false) // Exclude free plan for landing page
        ->where('is_trial', false) // Exclude trial
        ->orderBy('price_monthly')
        ->get()
        ->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'price_monthly' => $plan->price_monthly,
                'price_yearly' => $plan->price_yearly,
                'features' => $plan->features,
                'is_popular' => $plan->slug === 'pro', // Mark Pro as popular
            ];
        });
    
    return response()->json($plans);
});

