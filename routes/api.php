<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstagramWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/instagram/webhook', [InstagramWebhookController::class, 'verify']);
Route::post('/instagram/webhook', [InstagramWebhookController::class, 'handle']);

// WhatsApp Webhooks (dari Node.js service)
Route::prefix('whatsapp/webhook')->group(function () {
    Route::post('/message', [WhatsAppWebhookController::class, 'handleMessage']);
    Route::post('/status', [WhatsAppWebhookController::class, 'handleStatus']);
    Route::post('/qr', [WhatsAppWebhookController::class, 'handleQr']);
});
