<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AutoReplyRuleController;
use App\Http\Controllers\AutoReplyLogController;
use App\Http\Controllers\KbArticleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard (ReplyAI)
Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
// ============================
// SETTINGS
// ============================
Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
Route::resource('settings/quick-replies', App\Http\Controllers\QuickReplyController::class)->names([
    'index' => 'quick-replies.index',
    'store' => 'quick-replies.store',
    'update' => 'quick-replies.update',
    'destroy' => 'quick-replies.destroy',
]);
Route::get('/api/quick-replies', [App\Http\Controllers\QuickReplyController::class, 'fetch'])->name('api.quick-replies.fetch');

// Business Profile Settings (Multi-Profile CRUD)
Route::get('/settings/business', [App\Http\Controllers\BusinessProfileController::class, 'index'])->name('settings.business');
Route::post('/settings/business', [App\Http\Controllers\BusinessProfileController::class, 'store'])->name('settings.business.store');
Route::put('/settings/business/{id?}', [App\Http\Controllers\BusinessProfileController::class, 'update'])->name('settings.business.update');
Route::delete('/settings/business/{id}', [App\Http\Controllers\BusinessProfileController::class, 'destroy'])->name('settings.business.destroy');
Route::post('/settings/business/{id}/default', [App\Http\Controllers\BusinessProfileController::class, 'setDefault'])->name('settings.business.setDefault');
Route::get('/api/business/template', [App\Http\Controllers\BusinessProfileController::class, 'getTemplate'])->name('api.business.template');

// Test route
Route::get('/settings/business-test', function() {
    $profile = \App\Models\BusinessProfile::first();
    if (!$profile) {
        $profile = new \App\Models\BusinessProfile();
        $profile->business_type = 'general';
    }
    $industries = \App\Models\BusinessProfile::INDUSTRIES;
    return view('settings.business-test', compact('profile', 'industries'));
});


// ============================
// ANALYTICS & CONTACTS
// ============================
Route::get('/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
Route::get('/analytics/export', [App\Http\Controllers\AnalyticsController::class, 'export'])->name('analytics.export');
Route::get('/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('contacts.index');
Route::get('/contacts/export', [App\Http\Controllers\ContactController::class, 'export'])->name('contacts.export');

// ============================
// SIMULATOR
// ============================
Route::get('/simulator', [App\Http\Controllers\SimulatorController::class, 'index'])->name('simulator.index');
Route::post('/simulator/send', [App\Http\Controllers\SimulatorController::class, 'sendMessage'])->name('simulator.send');

// ============================
// INBOX INSTAGRAM
// ============================
Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');

// kirim pesan manual
Route::post('/inbox/send', [InboxController::class, 'send'])->name('inbox.send');

// polling messages (auto refresh)
Route::get('/inbox/poll-messages', [InboxController::class, 'pollMessages'])
    ->name('inbox.poll.messages');

// polling conversations (opsional buat sidebar update)
Route::get('/inbox/poll-conversations', [InboxController::class, 'pollConversations'])
    ->name('inbox.poll.conversations');

// ✅ endpoint check pesan terbaru (AJAX)
Route::get('/inbox/check-latest', [InboxController::class, 'checkLatest'])
    ->name('inbox.checkLatest');

// ✅ NEW: endpoint untuk polling pesan baru (JSON)
Route::get('/inbox/check-new', [InboxController::class, 'checkNew'])
    ->name('inbox.checkNew');

// Handback
Route::post('/inbox/{conversation}/handback', [InboxController::class, 'handbackToBot'])->name('inbox.handback');


// route lain (tailadmin default) kalau mau tetap ada:




// Rules (modal CRUD)
Route::get('/rules', [AutoReplyRuleController::class, 'index'])->name('rules.index');
Route::post('/rules', [AutoReplyRuleController::class, 'store'])->name('rules.store');
Route::put('/rules/{rule}', [AutoReplyRuleController::class, 'update'])->name('rules.update');
Route::delete('/rules/{rule}', [AutoReplyRuleController::class, 'destroy'])->name('rules.destroy');
Route::patch('/rules/{rule}/toggle', [AutoReplyRuleController::class, 'toggle'])->name('rules.toggle');

Route::get('/logs', [AutoReplyLogController::class, 'index'])->name('logs');

// Rules (tabel rules yg tadi)
Route::get('/rules', [AutoReplyRuleController::class, 'index'])->name('rules.index');

// ✅ Logs
Route::get('/logs', [AutoReplyLogController::class, 'index'])->name('logs.index');

// AJAX CRUD (no redirect)
Route::post('/rules', [AutoReplyRuleController::class, 'storeAjax'])->name('rules.store');
Route::patch('/rules/{rule}', [AutoReplyRuleController::class, 'updateAjax'])->name('rules.update');
Route::delete('/rules/{rule}', [AutoReplyRuleController::class, 'destroyAjax'])->name('rules.destroy');
Route::patch('/rules/{rule}/toggle', [AutoReplyRuleController::class, 'toggleAjax'])->name('rules.toggle');
Route::post('/rules/test', [AutoReplyRuleController::class, 'testAjax'])
    ->name('rules.test');



// KB
Route::get('/kb', [KbArticleController::class, 'index'])->name('kb.index');
Route::post('/kb', [KbArticleController::class, 'store'])->name('kb.store');
Route::post('/kb/import-url', [KbArticleController::class, 'importUrl'])->name('kb.importUrl');
Route::patch('/kb/{kb}/toggle', [KbArticleController::class, 'toggle'])->name('kb.toggle');
Route::delete('/kb/{kb}', [KbArticleController::class, 'destroy'])->name('kb.destroy');

// AI preview test rules (punyamu tadi)
Route::post('/rules/test', [AutoReplyRuleController::class, 'testAjax'])->name('rules.test');



Route::get('/kb', [KbArticleController::class, 'index'])->name('kb.index');
Route::post('/kb/import-url', [KbArticleController::class, 'importUrl'])->name('kb.importUrl');
Route::patch('/kb/{kb}/toggle', [KbArticleController::class, 'toggle'])->name('kb.toggle');
Route::delete('/kb/{kb}', [KbArticleController::class, 'destroy'])->name('kb.destroy');

// ✅ NEW: test AI dari KB
Route::post('/kb/test-ai', [KbArticleController::class, 'testAi'])->name('kb.testAi');
Route::post('/kb/import-file', [KbArticleController::class, 'importFile'])->name('kb.importFile');



Route::get('/auto-reply-logs', [AutoReplyLogController::class, 'index'])
    ->name('auto-reply-logs.index');

// ============================
// WHATSAPP INTEGRATION
// ============================
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppInboxController;
use App\Http\Controllers\WhatsAppAnalyticsController;
use App\Http\Controllers\WhatsAppBroadcastController;

Route::prefix('whatsapp')->group(function () {
    // Analytics
    Route::get('/analytics', [WhatsAppAnalyticsController::class, 'index'])->name('whatsapp.analytics');

    // Broadcast
    Route::resource('/broadcast', WhatsAppBroadcastController::class, [
        'as' => 'whatsapp'
    ])->only(['index', 'create', 'store', 'show']);

    // Inbox
    Route::get('/inbox', [WhatsAppInboxController::class, 'index'])->name('whatsapp.inbox');
    Route::get('/api/conversations', [WhatsAppInboxController::class, 'getConversations'])->name('whatsapp.api.conversations');
    Route::get('/api/messages/{phone}', [WhatsAppInboxController::class, 'getMessages'])->name('whatsapp.api.messages');

    // Settings & Actions
    Route::get('/settings', [WhatsAppController::class, 'settings'])->name('whatsapp.settings');
    
    // Multi-Device Management
    Route::post('/store', [WhatsAppController::class, 'store'])->name('whatsapp.store');
    Route::delete('/device/{sessionId}', [WhatsAppController::class, 'destroy'])->name('whatsapp.destroy');
    Route::get('/status/{sessionId}', [WhatsAppController::class, 'status'])->name('whatsapp.status');
    Route::get('/qr/{sessionId}', [WhatsAppController::class, 'qr'])->name('whatsapp.qr');
    Route::put('/device/{sessionId}/profile', [WhatsAppController::class, 'updateProfile'])->name('whatsapp.updateProfile');
    
    // Actions
    Route::post('/send', [WhatsAppController::class, 'send'])->name('whatsapp.send');
    Route::post('/toggle-auto-reply', [WhatsAppController::class, 'toggleAutoReply'])->name('whatsapp.toggle-auto-reply');
    Route::get('/messages', [WhatsAppController::class, 'messages'])->name('whatsapp.messages');
});

// Documentation
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\TakeoverController;

Route::get('/docs', [DocumentationController::class, 'index'])->name('documentation.index');

// ============================
// TAKEOVER MANAGEMENT (Unified)
// ============================
Route::prefix('takeover')->group(function () {
    // WhatsApp
    Route::post('/wa/{phone}/takeover', [TakeoverController::class, 'takeoverWa'])->name('takeover.wa.takeover');
    Route::post('/wa/{phone}/handback', [TakeoverController::class, 'handbackWa'])->name('takeover.wa.handback');
    Route::get('/wa/{phone}/status', [TakeoverController::class, 'getWaConversationStatus'])->name('takeover.wa.status');
    
    // Instagram
    Route::post('/ig/{id}/takeover', [TakeoverController::class, 'takeoverIg'])->name('takeover.ig.takeover');
    Route::post('/ig/{id}/handback', [TakeoverController::class, 'handbackIg'])->name('takeover.ig.handback');
    
    // Settings & Logs
    Route::get('/logs', [TakeoverController::class, 'logsPage'])->name('takeover.logs');
    Route::get('/logs/data', [TakeoverController::class, 'getLogs'])->name('takeover.logs.data');
    Route::get('/settings', [TakeoverController::class, 'getSettings'])->name('takeover.settings.get');
    Route::post('/settings', [TakeoverController::class, 'updateSettings'])->name('takeover.settings.update');
});

// ============================
// WEB CHAT WIDGET MANAGEMENT
// ============================
use App\Http\Controllers\WebWidgetController;

Route::prefix('web-widgets')->group(function () {
    Route::get('/', [WebWidgetController::class, 'index'])->name('web-widgets.index');
    Route::get('/create', [WebWidgetController::class, 'create'])->name('web-widgets.create');
    Route::post('/', [WebWidgetController::class, 'store'])->name('web-widgets.store');
    Route::get('/{webWidget}/edit', [WebWidgetController::class, 'edit'])->name('web-widgets.edit');
    Route::put('/{webWidget}', [WebWidgetController::class, 'update'])->name('web-widgets.update');
    Route::delete('/{webWidget}', [WebWidgetController::class, 'destroy'])->name('web-widgets.destroy');
    Route::patch('/{webWidget}/toggle', [WebWidgetController::class, 'toggle'])->name('web-widgets.toggle');
    Route::post('/{webWidget}/regenerate-key', [WebWidgetController::class, 'regenerateKey'])->name('web-widgets.regenerate-key');
    Route::get('/{webWidget}/embed-code', [WebWidgetController::class, 'getEmbedCode'])->name('web-widgets.embed-code');
});

// ============================
// SEQUENCES / DRIP CAMPAIGN
// ============================
use App\Http\Controllers\SequenceController;

Route::prefix('sequences')->group(function () {
    Route::get('/', [SequenceController::class, 'index'])->name('sequences.index');
    Route::get('/create', [SequenceController::class, 'create'])->name('sequences.create');
    Route::post('/', [SequenceController::class, 'store'])->name('sequences.store');
    Route::get('/{sequence}/edit', [SequenceController::class, 'edit'])->name('sequences.edit');
    Route::put('/{sequence}', [SequenceController::class, 'update'])->name('sequences.update');
    Route::delete('/{sequence}', [SequenceController::class, 'destroy'])->name('sequences.destroy');
    Route::patch('/{sequence}/toggle', [SequenceController::class, 'toggle'])->name('sequences.toggle');
    Route::get('/{sequence}/enrollments', [SequenceController::class, 'enrollments'])->name('sequences.enrollments');
    Route::post('/{sequence}/enroll', [SequenceController::class, 'manualEnroll'])->name('sequences.enroll');
    Route::post('/enrollments/{enrollment}/cancel', [SequenceController::class, 'cancelEnrollment'])->name('sequences.enrollment.cancel');
});

