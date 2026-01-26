<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AutoReplyRuleController;
use App\Http\Controllers\AutoReplyLogController;
use App\Http\Controllers\KbArticleController;
use App\Http\Controllers\OnboardingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page (untuk visitor/guest)
Route::get('/', function () {
    // Jika sudah login, redirect ke dashboard
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    // Jika belum login, redirect ke landing page
    return redirect('/landingpage/index.html');
})->name('home');

// Dashboard (untuk user yang sudah login DAN punya active subscription)
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'has_subscription'])
    ->name('dashboard');

// ============================
// ONBOARDING WIZARD
// ============================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
});

// ============================
// SESSION MANAGEMENT
// ============================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/account/sessions', [\App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index');
    Route::delete('/account/sessions/{session}', [\App\Http\Controllers\SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::delete('/account/sessions', [\App\Http\Controllers\SessionController::class, 'destroyAll'])->name('sessions.destroy-all');
});

// Pricing Page dengan Plan Selection
Route::get('/pricing', function (Illuminate\Http\Request $request) {
    $planSlug = $request->get('plan');
    
    // Jika user sudah login
    if (auth()->check()) {
        // Jika ada plan dipilih, redirect ke checkout
        if ($planSlug) {
            return redirect()->route('checkout.index', ['plan' => $planSlug]);
        }
        
        // Cek apakah user punya pending invoice
        $pendingPayment = \App\Models\Payment::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Jika tidak ada plan, tampilkan pricing page
        $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('pages.pricing.index', compact('plans', 'pendingPayment'));
    }
    
    // Jika belum login dan ada plan dipilih, save ke session lalu redirect ke register
    if ($planSlug) {
        session(['selected_plan' => $planSlug]);
        return redirect()->route('register')->with('plan', $planSlug);
    }
    
    // Jika belum login dan tidak ada plan, tampilkan pricing page
    $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
    $pendingPayment = null;
    return view('pages.pricing.index', compact('plans', 'pendingPayment'));
})->name('pricing');

// Upgrade Required Page (ketika user tidak punya akses ke fitur)
Route::get('/upgrade', function (Illuminate\Http\Request $request) {
    $feature = $request->get('feature');
    
    // Mapping feature key ke nama yang user-friendly
    $featureNames = [
        'broadcasts' => 'Broadcast Messages',
        'broadcast' => 'Broadcast Messages',
        'sequences' => 'Drip Sequences',
        'web_widgets' => 'Web Chat Widget',
        'api_access' => 'Akses API',
        'analytics_export' => 'Export Laporan',
        'remove_branding' => 'Hapus Branding',
    ];
    
    $featureName = $featureNames[$feature] ?? ucwords(str_replace('_', ' ', $feature ?? 'ini'));
    
    return view('pages.upgrade-required', compact('feature', 'featureName'));
})->middleware(['auth'])->name('upgrade');

// Subscription Pending Page
Route::get('/subscription/pending', function () {
    return view('subscription.pending');
})->middleware(['auth'])->name('subscription.pending');

// My Account Page
Route::get('/account', function () {
    return view('account.index');
})->middleware(['auth', 'verified'])->name('account.index');

// ============================
// CHECKOUT & PAYMENT
// ============================
Route::middleware(['auth', 'verified'])->group(function () {
    // Checkout page untuk plan tertentu
    Route::get('/checkout/{plan:slug}', [App\Http\Controllers\CheckoutController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout/{plan:slug}', [App\Http\Controllers\CheckoutController::class, 'processCheckout'])->name('checkout.process');
    
    // Payment page (invoice)
    Route::get('/payment/{invoiceNumber}', [App\Http\Controllers\CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/payment/{payment}/apply-promo', [App\Http\Controllers\CheckoutController::class, 'applyPromo'])->name('checkout.apply-promo');
    Route::post('/payment/{payment}/upload-proof', [App\Http\Controllers\CheckoutController::class, 'uploadProof'])->name('checkout.upload-proof');
    
    // Midtrans Payment
    Route::get('/payment/{invoiceNumber}/midtrans', [App\Http\Controllers\CheckoutController::class, 'payWithMidtrans'])->name('checkout.midtrans.pay');
    Route::get('/checkout/midtrans/finish', [App\Http\Controllers\CheckoutController::class, 'midtransFinish'])->name('checkout.midtrans.finish');
    
    // Success & History
    Route::get('/checkout/success/{invoiceNumber}', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/payment/history', [App\Http\Controllers\CheckoutController::class, 'history'])->name('checkout.history');
});

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

// ============================
// INSTAGRAM OAUTH
// ============================
Route::middleware(['auth', 'verified'])->prefix('instagram')->group(function () {
    Route::get('/settings', [App\Http\Controllers\InstagramOAuthController::class, 'settings'])->name('instagram.settings');
    Route::get('/connect', [App\Http\Controllers\InstagramOAuthController::class, 'connect'])->name('instagram.connect');
    Route::post('/disconnect', [App\Http\Controllers\InstagramOAuthController::class, 'disconnect'])->name('instagram.disconnect');
    Route::post('/refresh-token', [App\Http\Controllers\InstagramOAuthController::class, 'refreshToken'])->name('instagram.refresh-token');
});
// Instagram OAuth Callback (no auth required for callback)
Route::get('/instagram/callback', [App\Http\Controllers\InstagramOAuthController::class, 'callback'])->name('instagram.callback');

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
Route::get('/csat', function() {
    return view('pages.csat.index');
})->middleware(['auth', 'verified'])->name('csat.index');

Route::get('/csat/settings', function() {
    return view('pages.csat.settings');
})->middleware(['auth', 'verified'])->name('csat.settings');

Route::post('/csat/settings', function(Illuminate\Http\Request $request) {
    $user = auth()->user();
    
    $user->update([
        'csat_enabled' => $request->boolean('csat_enabled'),
        'csat_instagram_enabled' => $request->boolean('csat_instagram_enabled'),
        'csat_whatsapp_enabled' => $request->boolean('csat_whatsapp_enabled'),
        'csat_delay_minutes' => (int) $request->csat_delay_minutes,
        'csat_message' => $request->csat_message,
    ]);
    
    return back()->with('success', 'Pengaturan CSAT berhasil disimpan!');
})->middleware(['auth', 'verified'])->name('csat.settings.update');

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
Route::patch('/kb/{kb}/profile', [KbArticleController::class, 'updateProfile'])->name('kb.updateProfile');
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

    // Broadcast - PRO Feature (memerlukan akses fitur broadcasts)
    Route::middleware(['feature:broadcasts'])->group(function () {
        Route::resource('/broadcast', WhatsAppBroadcastController::class, [
            'as' => 'whatsapp'
        ])->only(['index', 'create', 'store', 'show']);
    });

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

// ============================
// SUBSCRIPTION & CHECKOUT
// ============================
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SubscriptionController;

// Pricing route sudah didefinisikan di atas (line 31-54) dengan logic redirect
// JANGAN define ulang pricing route di sini!

// Checkout & Payment (perlu login) - routes ini sudah didefinisikan di atas
// Subscription Management (perlu login)

// Subscription Management (perlu login)
Route::prefix('subscription')->middleware('auth')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
});

// ============================
// SUPPORT TICKETS
// ============================
use App\Http\Controllers\SupportController;

Route::prefix('support')->middleware('auth')->group(function () {
    Route::get('/', [SupportController::class, 'index'])->name('support.index');
    Route::get('/create', [SupportController::class, 'create'])->name('support.create');
    Route::post('/', [SupportController::class, 'store'])->name('support.store');
    Route::get('/{ticket}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('support.reply');
    Route::post('/{ticket}/rate', [SupportController::class, 'rate'])->name('support.rate');
    Route::post('/{ticket}/reopen', [SupportController::class, 'reopen'])->name('support.reopen');
});

// ============================
// AUTHENTICATION (Laravel Breeze)
// ============================
require __DIR__.'/auth.php';

// ============================
// ADMIN ROUTES
// ============================
Route::prefix('admin')->group(function () {
    require __DIR__.'/admin.php';
});


