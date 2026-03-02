<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstagramWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\Api\WebChatController;
use App\Http\Controllers\ContactPanelController;
use App\Http\Controllers\ChatMediaController;
use App\Http\Controllers\AiReplyController;

// Instagram Webhooks - Exclude from rate limiting
Route::withoutMiddleware([
    \Illuminate\Routing\Middleware\ThrottleRequests::class,
])->group(function () {
    Route::get('/instagram/webhook', [InstagramWebhookController::class, 'verify']);
    Route::post('/instagram/webhook', [InstagramWebhookController::class, 'handle']);
});

// WhatsApp Webhooks (dari Node.js service) - Exclude from rate limiting
Route::withoutMiddleware([
    \Illuminate\Routing\Middleware\ThrottleRequests::class,
])->prefix('whatsapp/webhook')->group(function () {
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

// Midtrans Payment Gateway Webhooks - Exclude from rate limiting
Route::withoutMiddleware([
    \Illuminate\Routing\Middleware\ThrottleRequests::class,
])->prefix('midtrans')->group(function () {
    Route::post('/notification', [App\Http\Controllers\Api\MidtransWebhookController::class, 'handleNotification']);
    Route::get('/status/{invoice}', [App\Http\Controllers\Api\MidtransWebhookController::class, 'checkStatus']);
});

// Chat Media API Routes (Protected)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/chat-media/{id}', [ChatMediaController::class, 'show'])->name('api.chat-media.show');
    Route::get('/chat-media/{id}/download', [ChatMediaController::class, 'download'])->name('api.chat-media.download');
    Route::delete('/chat-media/{id}', [ChatMediaController::class, 'destroy'])->name('api.chat-media.destroy');
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
                'price_monthly_original' => $plan->price_monthly_original,
                'price_monthly_display' => $plan->price_monthly_display,
                'price_monthly_original_display' => $plan->price_monthly_original_display,
                'price_yearly' => $plan->price_yearly,
                'price_yearly_display' => $plan->price_yearly_display,
                'features' => $plan->features,
                'features_list' => $plan->features_list,
                'is_popular' => (bool) $plan->is_popular,
            ];
        });
    
    return response()->json($plans);
});

// Contact Panel API (requires auth)
Route::middleware(['auth'])->group(function () {
    // Get contact details
    Route::get('/contacts/{type}/{id}/details', [ContactPanelController::class, 'getContactDetails']);
    
    // Notes
    Route::post('/contacts/{type}/{id}/notes', [ContactPanelController::class, 'addNote']);
    Route::delete('/notes/{id}', [ContactPanelController::class, 'deleteNote']);
    
    // Tags
    Route::put('/contacts/{type}/{id}/tags', [ContactPanelController::class, 'updateTags']);
    
    // Custom Fields
    Route::put('/contacts/{type}/{id}/custom-fields', [ContactPanelController::class, 'updateCustomField']);
    
    // Actions
    Route::post('/contacts/{type}/{id}/block', [ContactPanelController::class, 'blockContact']);
    Route::delete('/contacts/{type}/{id}/conversation', [ContactPanelController::class, 'deleteConversation']);
});

// Tags API (public for auth users)
Route::middleware(['auth'])->get('/tags', function () {
    return \App\Models\Tag::where('user_id', auth()->id())
        ->select('id', 'name', 'color')
        ->orderBy('name')
        ->get();
});

// AI Reply Suggestions API (requires auth)
Route::middleware(['auth'])->prefix('ai')->group(function () {
    // Generate reply suggestions
    Route::post('/suggest-replies', [AiReplyController::class, 'suggest']);
    
    // Analyze sentiment
    Route::post('/analyze-sentiment', [AiReplyController::class, 'analyzeSentiment']);
    
    // Summarize conversation
    Route::post('/summarize', [AiReplyController::class, 'summarizeConversation']);
    
    // Detect intent
    Route::post('/detect-intent', [AiReplyController::class, 'detectIntent']);
});

// AI Performance Analytics API (requires auth)
Route::middleware(['auth'])->prefix('ai-performance')->group(function () {
    Route::get('/intent-accuracy', [App\Http\Controllers\AiPerformanceController::class, 'getIntentAccuracy']);
    Route::get('/response-relevance', [App\Http\Controllers\AiPerformanceController::class, 'getResponseRelevance']);
    Route::get('/knowledge-gaps', [App\Http\Controllers\AiPerformanceController::class, 'getKnowledgeGaps']);
    Route::get('/training-improvement', [App\Http\Controllers\AiPerformanceController::class, 'getTrainingImprovement']);
    Route::get('/confidence-distribution', [App\Http\Controllers\AiPerformanceController::class, 'getConfidenceDistribution']);
    Route::get('/popular-intents', [App\Http\Controllers\AiPerformanceController::class, 'getPopularIntents']);
});

