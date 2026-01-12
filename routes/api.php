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
