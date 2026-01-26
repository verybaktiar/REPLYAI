<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AutoReplyRuleController;
use App\Http\Controllers\AutoReplyLogController;
use App\Http\Controllers\KbArticleController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppInboxController;
use App\Http\Controllers\WhatsAppAnalyticsController;
use App\Http\Controllers\WhatsAppBroadcastController;
use App\Http\Controllers\InstagramOAuthController;
use App\Http\Controllers\SequenceController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TakeoverController;
use App\Http\Controllers\SimulatorController;
use App\Http\Controllers\WebWidgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\QuickReplyController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\UserAnnouncementController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\DocumentationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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

// Instagram OAuth Callback (no auth required for callback)
Route::get('/instagram/callback', [App\Http\Controllers\InstagramOAuthController::class, 'callback'])->name('instagram.callback');

// API Business Template (no auth required for some calls)
Route::get('/api/business/template', [App\Http\Controllers\BusinessProfileController::class, 'getTemplate'])->name('api.business.template');

// Landing Page (untuk visitor/guest)
Route::get('/', function () {
    // Jika sudah login, redirect ke dashboard
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    // Mengambil data paket untuk ditampilkan secara dinamis
    $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
    return view('landingpage', compact('plans'));
})->name('home');

// Dashboard (untuk user yang sudah login DAN punya active subscription)
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'has_subscription', 'suspended'])
    ->name('dashboard');

// Suspended Page
Route::get('/suspended', function () {
    return view('errors.suspended');
})->name('suspended');

// ============================
// ONBOARDING WIZARD
// ============================
Route::middleware(['auth', 'verified', 'suspended'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
});

// ============================
// SESSION MANAGEMENT
// ============================
Route::middleware(['auth', 'verified', 'suspended'])->group(function () {
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
Route::middleware(['auth', 'verified', 'suspended'])->group(function () {
    Route::get('/account', function () {
        return view('account.index');
    })->name('account.index');
    
    // Announcements
    Route::post('/announcements/{announcement}/read', [App\Http\Controllers\UserAnnouncementController::class, 'markAsRead'])->name('announcements.read');
});

// ============================
// PROTECTED USER FEATURES (Auth + Verified + Not Suspended)
// ============================
Route::middleware(['auth', 'verified', 'suspended'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->middleware(['has_subscription'])
        ->name('dashboard');

    // My Account & Profile
    Route::get('/account', function () {
        return view('account.index');
    })->name('account.index');
    
    Route::get('/settings/account', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings/account', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings/account', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Announcements
    Route::post('/announcements/{announcement}/read', [App\Http\Controllers\UserAnnouncementController::class, 'markAsRead'])->name('announcements.read');

    // Onboarding
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');

    // Sessions
    Route::get('/account/sessions', [\App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index');
    Route::delete('/account/sessions/{session}', [\App\Http\Controllers\SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::delete('/account/sessions', [\App\Http\Controllers\SessionController::class, 'destroyAll'])->name('sessions.destroy-all');

    // Upgrade & Pending Subscription
    Route::get('/upgrade', function (Illuminate\Http\Request $request) {
        $feature = $request->get('feature');
        $featureNames = [
            'broadcasts' => 'Broadcast Messages',
            'sequences' => 'Drip Sequences',
            'web_widgets' => 'Web Chat Widget',
            'api_access' => 'Akses API',
        ];
        $featureName = $featureNames[$feature] ?? ucwords(str_replace('_', ' ', $feature ?? 'ini'));
        return view('pages.upgrade-required', compact('feature', 'featureName'));
    })->name('upgrade');

    Route::get('/subscription/pending', function () {
        return view('subscription.pending');
    })->name('subscription.pending');

    // Checkout & Payment
    Route::get('/checkout/{plan:slug}', [App\Http\Controllers\CheckoutController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout/{plan:slug}', [App\Http\Controllers\CheckoutController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/payment/{invoiceNumber}', [App\Http\Controllers\CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/payment/{payment}/apply-promo', [App\Http\Controllers\CheckoutController::class, 'applyPromo'])->name('checkout.apply-promo');
    Route::post('/payment/{payment}/upload-proof', [App\Http\Controllers\CheckoutController::class, 'uploadProof'])->name('checkout.upload-proof');
    Route::get('/payment/{invoiceNumber}/midtrans', [App\Http\Controllers\CheckoutController::class, 'payWithMidtrans'])->name('checkout.midtrans.pay');
    Route::get('/checkout/midtrans/finish', [App\Http\Controllers\CheckoutController::class, 'midtransFinish'])->name('checkout.midtrans.finish');
    Route::get('/checkout/success/{invoiceNumber}', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/payment/history', [App\Http\Controllers\CheckoutController::class, 'history'])->name('checkout.history');

    // Settings & Quick Replies
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::resource('settings/quick-replies', App\Http\Controllers\QuickReplyController::class)->names([
        'index' => 'quick-replies.index',
        'store' => 'quick-replies.store',
        'update' => 'quick-replies.update',
        'destroy' => 'quick-replies.destroy',
    ]);
    Route::get('/api/quick-replies', [App\Http\Controllers\QuickReplyController::class, 'fetch'])->name('api.quick-replies.fetch');

    // Business Profiles
    Route::get('/settings/business', [App\Http\Controllers\BusinessProfileController::class, 'index'])->name('settings.business');
    Route::post('/settings/business', [App\Http\Controllers\BusinessProfileController::class, 'store'])->name('settings.business.store');
    Route::put('/settings/business/{id?}', [App\Http\Controllers\BusinessProfileController::class, 'update'])->name('settings.business.update');
    Route::delete('/settings/business/{id}', [App\Http\Controllers\BusinessProfileController::class, 'destroy'])->name('settings.business.destroy');
    Route::post('/settings/business/{id}/default', [App\Http\Controllers\BusinessProfileController::class, 'setDefault'])->name('settings.business.setDefault');

    // Instagram
    Route::prefix('instagram')->group(function () {
        Route::get('/settings', [App\Http\Controllers\InstagramOAuthController::class, 'settings'])->name('instagram.settings');
        Route::get('/connect', [App\Http\Controllers\InstagramOAuthController::class, 'connect'])->name('instagram.connect');
        Route::post('/disconnect', [App\Http\Controllers\InstagramOAuthController::class, 'disconnect'])->name('instagram.disconnect');
        Route::post('/refresh-token', [App\Http\Controllers\InstagramOAuthController::class, 'refreshToken'])->name('instagram.refresh-token');
    });

    // Analytics & CSAT
    Route::get('/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export', [App\Http\Controllers\AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/export', [App\Http\Controllers\ContactController::class, 'export'])->name('contacts.export');
    
    Route::get('/csat', function() { return view('pages.csat.index'); })->name('csat.index');
    Route::get('/csat/settings', function() { return view('pages.csat.settings'); })->name('csat.settings');
    Route::post('/csat/settings', function(Illuminate\Http\Request $request) {
        auth()->user()->update([
            'csat_enabled' => $request->boolean('csat_enabled'),
            'csat_instagram_enabled' => $request->boolean('csat_instagram_enabled'),
            'csat_whatsapp_enabled' => $request->boolean('csat_whatsapp_enabled'),
            'csat_delay_minutes' => (int) $request->csat_delay_minutes,
            'csat_message' => $request->csat_message,
        ]);
        return back()->with('success', 'Pengaturan CSAT berhasil disimpan!');
    })->name('csat.settings.update');

    // AI Simulator
    Route::get('/simulator', [App\Http\Controllers\SimulatorController::class, 'index'])
        ->middleware(['global_feature:enable_ai_simulator'])
        ->name('simulator.index');
    
    Route::post('/simulator/send', [App\Http\Controllers\SimulatorController::class, 'sendMessage'])
        ->middleware(['quota:ai_messages', 'global_feature:enable_ai_simulator'])
        ->name('simulator.send');

    // Inbox
    Route::middleware(['global_feature:enable_instagram'])->group(function () {
        Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
        Route::post('/inbox/send', [InboxController::class, 'send'])->name('inbox.send');
        Route::get('/inbox/poll-messages', [InboxController::class, 'pollMessages'])->name('inbox.poll.messages');
        Route::get('/inbox/poll-conversations', [InboxController::class, 'pollConversations'])->name('inbox.poll.conversations');
        Route::get('/inbox/check-latest', [InboxController::class, 'checkLatest'])->name('inbox.checkLatest');
        Route::get('/inbox/check-new', [InboxController::class, 'checkNew'])->name('inbox.checkNew');
        Route::post('/inbox/{conversation}/handback', [InboxController::class, 'handbackToBot'])->name('inbox.handback');
    });

    // Rules
    Route::get('/rules', [AutoReplyRuleController::class, 'index'])->name('rules.index');
    Route::post('/rules', [AutoReplyRuleController::class, 'storeAjax'])->middleware(['quota:auto_reply_rules'])->name('rules.store');
    Route::patch('/rules/{rule}', [AutoReplyRuleController::class, 'updateAjax'])->name('rules.update');
    Route::delete('/rules/{rule}', [AutoReplyRuleController::class, 'destroyAjax'])->name('rules.destroy');
    Route::patch('/rules/{rule}/toggle', [AutoReplyRuleController::class, 'toggleAjax'])->name('rules.toggle');
    Route::post('/rules/test', [AutoReplyRuleController::class, 'testAjax'])->name('rules.test');
    Route::get('/logs', [AutoReplyLogController::class, 'index'])->name('logs.index');

    // KB
    Route::get('/kb', [KbArticleController::class, 'index'])->name('kb.index');
    Route::post('/kb', [KbArticleController::class, 'store'])->middleware(['quota:kb_articles'])->name('kb.store');
    Route::post('/kb/import-url', [KbArticleController::class, 'importUrl'])->middleware(['quota:kb_articles'])->name('kb.importUrl');
    Route::post('/kb/import-file', [KbArticleController::class, 'importFile'])->middleware(['quota:kb_articles'])->name('kb.importFile');
    Route::patch('/kb/{kb}/toggle', [KbArticleController::class, 'toggle'])->name('kb.toggle');
    Route::patch('/kb/{kb}/profile', [KbArticleController::class, 'updateProfile'])->name('kb.updateProfile');
    Route::delete('/kb/{kb}', [KbArticleController::class, 'destroy'])->name('kb.destroy');
    Route::post('/kb/test-ai', [KbArticleController::class, 'testAi'])->name('kb.testAi');

    // WhatsApp
    Route::prefix('whatsapp')->name('whatsapp.')->middleware(['global_feature:enable_whatsapp'])->group(function () {
        Route::get('/analytics', [WhatsAppAnalyticsController::class, 'index'])->name('analytics');
        Route::middleware(['feature:broadcasts', 'global_feature:enable_broadcasts'])->group(function () {
            Route::resource('/broadcast', WhatsAppBroadcastController::class)->only(['index', 'create', 'store', 'show']);
        });
        Route::get('/inbox', [WhatsAppInboxController::class, 'index'])->name('inbox');
        Route::get('/api/conversations', [WhatsAppInboxController::class, 'getConversations'])->name('api.conversations');
        Route::get('/api/messages/{phone}', [WhatsAppInboxController::class, 'getMessages'])->name('api.messages');
        Route::get('/settings', [WhatsAppController::class, 'settings'])->name('settings');
        Route::post('/store', [WhatsAppController::class, 'store'])->name('store');
        Route::delete('/device/{sessionId}', [WhatsAppController::class, 'destroy'])->name('destroy');
        Route::get('/status/{sessionId}', [WhatsAppController::class, 'status'])->name('status');
        Route::get('/qr/{sessionId}', [WhatsAppController::class, 'qr'])->name('qr');
        Route::put('/device/{sessionId}/profile', [WhatsAppController::class, 'updateProfile'])->name('updateProfile');
        Route::post('/send', [WhatsAppController::class, 'send'])->middleware(['quota:ai_messages'])->name('send');
        Route::post('/toggle-auto-reply', [WhatsAppController::class, 'toggleAutoReply'])->name('toggle-auto-reply');
        Route::get('/messages', [WhatsAppController::class, 'messages'])->name('messages');
    });

    // Takeover
    Route::prefix('takeover')->name('takeover.')->group(function () {
        Route::post('/wa/{phone}/takeover', [TakeoverController::class, 'takeoverWa'])->name('wa.takeover');
        Route::post('/wa/{phone}/handback', [TakeoverController::class, 'handbackWa'])->name('wa.handback');
        Route::get('/wa/{phone}/status', [TakeoverController::class, 'getWaConversationStatus'])->name('wa.status');
        Route::post('/ig/{id}/takeover', [TakeoverController::class, 'takeoverIg'])->name('ig.takeover');
        Route::post('/ig/{id}/handback', [TakeoverController::class, 'handbackIg'])->name('ig.handback');
        Route::get('/logs', [TakeoverController::class, 'logsPage'])->name('logs');
        Route::get('/logs/data', [TakeoverController::class, 'getLogs'])->name('logs.data');
        Route::get('/settings', [TakeoverController::class, 'getSettings'])->name('settings.get');
        Route::post('/settings', [TakeoverController::class, 'updateSettings'])->name('settings.update');
    });

    // Web Widgets
    Route::prefix('web-widgets')->name('web-widgets.')->group(function () {
        Route::get('/', [WebWidgetController::class, 'index'])->name('index');
        Route::get('/create', [WebWidgetController::class, 'create'])->name('create');
        Route::post('/', [WebWidgetController::class, 'store'])->name('store');
        Route::get('/{webWidget}/edit', [WebWidgetController::class, 'edit'])->name('edit');
        Route::put('/{webWidget}', [WebWidgetController::class, 'update'])->name('update');
        Route::delete('/{webWidget}', [WebWidgetController::class, 'destroy'])->name('destroy');
        Route::patch('/{webWidget}/toggle', [WebWidgetController::class, 'toggle'])->name('toggle');
        Route::post('/{webWidget}/regenerate-key', [WebWidgetController::class, 'regenerateKey'])->name('regenerate-key');
        Route::get('/{webWidget}/embed-code', [WebWidgetController::class, 'getEmbedCode'])->name('embed-code');
    });

    // Sequences
    Route::prefix('sequences')->name('sequences.')->group(function () {
        Route::get('/', [SequenceController::class, 'index'])->name('index');
        Route::get('/create', [SequenceController::class, 'create'])->name('create');
        Route::post('/', [SequenceController::class, 'store'])->name('store');
        Route::get('/{sequence}/edit', [SequenceController::class, 'edit'])->name('edit');
        Route::put('/{sequence}', [SequenceController::class, 'update'])->name('update');
        Route::delete('/{sequence}', [SequenceController::class, 'destroy'])->name('destroy');
        Route::patch('/{sequence}/toggle', [SequenceController::class, 'toggle'])->name('toggle');
        Route::get('/{sequence}/enrollments', [SequenceController::class, 'enrollments'])->name('enrollments');
        Route::post('/{sequence}/enroll', [SequenceController::class, 'manualEnroll'])->name('enroll');
        Route::post('/enrollments/{enrollment}/cancel', [SequenceController::class, 'cancelEnrollment'])->name('enrollment.cancel');
    });

    // Subscription Management
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/reactivate', [SubscriptionController::class, 'reactivate'])->name('reactivate');
    });

    // Support
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('/create', [SupportController::class, 'create'])->name('create');
        Route::post('/', [SupportController::class, 'store'])->name('store');
        Route::get('/{ticket}', [SupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/rate', [SupportController::class, 'rate'])->name('rate');
        Route::post('/{ticket}/reopen', [SupportController::class, 'reopen'])->name('reopen');
    });

    // Documentation
    Route::get('/docs', [App\Http\Controllers\DocumentationController::class, 'index'])->name('documentation.index');

});


