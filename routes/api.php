<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstagramWebhookController;

Route::get('/instagram/webhook', [InstagramWebhookController::class, 'verify']);
Route::post('/instagram/webhook', [InstagramWebhookController::class, 'handle']);
