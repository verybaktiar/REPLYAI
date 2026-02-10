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
use App\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Language Switcher
Route::get('lang/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('lang.switch');

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
// Note: Definisi route dashboard dipindahkan ke dalam group 'PROTECTED USER FEATURES' di bawah untuk menghindari duplikasi

// Suspended Page
Route::get('/suspended', function () {
    return view('errors.suspended');
})->name('suspended');

// ============================
// PRICING PAGE
// ============================
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

// ============================
// PROTECTED USER FEATURES (Auth + Verified + Not Suspended)
// ============================
Route::middleware(['auth', 'verified', 'suspended'])->group(function () {
    
    // Dashboard
    Route::controller(App\Http\Controllers\DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->middleware(['has_subscription'])->name('dashboard');
        Route::get('/dashboard/roadmap', 'roadmap')->name('dashboard.roadmap');
    });

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
    
    // AI Analytics Dashboard
    Route::prefix('admin/analytics')->name('admin.analytics.')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('index');
        Route::get('/chart-data', [App\Http\Controllers\Admin\AnalyticsController::class, 'getChartData'])->name('chart-data');
        Route::patch('/missed-query/{query}/resolve', [App\Http\Controllers\Admin\AnalyticsController::class, 'resolveMissedQuery'])->name('missed-query.resolve');
        Route::patch('/missed-query/{query}/ignore', [App\Http\Controllers\Admin\AnalyticsController::class, 'ignoreMissedQuery'])->name('missed-query.ignore');
    });
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
    Route::middleware(['global_feature:enable_instagram'])->controller(InboxController::class)->prefix('inbox')->name('inbox')->group(function () {
        Route::get('/', 'index'); // name: inbox
        Route::get('/enterprise', function () {
            return view('pages.inbox.enterprise');
        })->name('.enterprise'); // inbox.enterprise
        Route::post('/send', 'send')->name('.send');
        Route::get('/poll-messages', 'pollMessages')->name('.poll.messages');
        Route::get('/poll-conversations', 'pollConversations')->name('.poll.conversations');
        Route::get('/check-latest', 'checkLatest')->name('.checkLatest');
        Route::get('/check-new', 'checkNew')->name('.checkNew');
        Route::post('/{conversation}/handback', 'handbackToBot')->name('.handback');
    });

    // Rules
    Route::controller(AutoReplyRuleController::class)->prefix('rules')->name('rules.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'storeAjax')->middleware(['quota:auto_reply_rules'])->name('store');
        Route::patch('/{rule}', 'updateAjax')->name('update');
        Route::delete('/{rule}', 'destroyAjax')->name('destroy');
        Route::patch('/{rule}/toggle', 'toggleAjax')->name('toggle');
        Route::post('/test', 'testAjax')->name('test');
    });
    Route::get('/logs', [AutoReplyLogController::class, 'index'])->name('logs.index');

    // KB
    Route::controller(KbArticleController::class)->prefix('kb')->name('kb.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->middleware(['quota:kb_articles'])->name('store');
        Route::post('/import-url', 'importUrl')->middleware(['quota:kb_articles'])->name('importUrl');
        Route::post('/import-file', 'importFile')->middleware(['quota:kb_articles'])->name('importFile');
        Route::patch('/{kb}/toggle', 'toggle')->name('toggle');
        Route::patch('/{kb}/profile', 'updateProfile')->name('updateProfile');
        Route::post('/{kb}/update', 'update')->name('update');
        Route::delete('/{kb}', 'destroy')->name('destroy');
        Route::post('/test-ai', 'testAi')->name('testAi');
    });

    // WhatsApp
    Route::prefix('whatsapp')->name('whatsapp.')->middleware(['global_feature:enable_whatsapp'])->group(function () {
        Route::get('/analytics', [WhatsAppAnalyticsController::class, 'index'])->name('analytics');
        Route::middleware(['feature:broadcasts', 'global_feature:enable_broadcasts'])->group(function () {
            Route::resource('/broadcast', WhatsAppBroadcastController::class)->only(['index', 'create', 'store', 'show']);
        });

        Route::controller(WhatsAppInboxController::class)->group(function() {
            Route::get('/inbox', 'index')->name('inbox');
            
            // API Routes
            Route::prefix('api')->name('api.')->group(function() {
                Route::get('/conversations', 'getConversations')->name('conversations');
                Route::get('/messages/{phone}', 'getMessages')->name('messages');
                Route::get('/conversations/{phone}/summary', 'getSummary')->name('conversations.summary');
                Route::get('/conversations/{phone}/suggestions', 'getSuggestions')->name('conversations.suggestions');
                Route::post('/messages/rate', 'rateMessage')->name('messages.rate');
                Route::post('/conversations/{phone}/toggle-followup', 'toggleFollowup')->name('conversations.toggle-followup');
                
                // CRM Routes
                Route::get('/conversations/{phone}/notes', 'getNotes')->name('conversations.notes.index');
                Route::post('/conversations/{phone}/notes', 'storeNote')->name('conversations.notes.store');
                Route::get('/conversations/{phone}/tags', 'getTags')->name('conversations.tags.index');
                Route::post('/conversations/{phone}/tags', 'attachTag')->name('conversations.tags.attach');
                Route::delete('/conversations/{phone}/tags', 'detachTag')->name('conversations.tags.detach');
                Route::get('/tags', 'getAvailableTags')->name('tags.index');
            });
        });

        Route::controller(WhatsAppController::class)->group(function() {
            Route::get('/settings', 'settings')->name('settings');
            Route::post('/store', 'store')->name('store');
            Route::delete('/device/{sessionId}', 'destroy')->name('destroy');
            Route::get('/status/{sessionId}', 'status')->name('status');
            Route::get('/qr/{sessionId}', 'qr')->name('qr');
            Route::post('/device/{sessionId}/reconnect', 'reconnect')->name('reconnect');
            Route::put('/device/{sessionId}/profile', 'updateProfile')->name('updateProfile');
            Route::post('/send', 'send')->middleware(['quota:ai_messages'])->name('send');
            Route::post('/toggle-auto-reply', 'toggleAutoReply')->name('toggle-auto-reply');
            Route::get('/messages', 'messages')->name('messages');
        });

        Route::get('/api/training/export/csv', [\App\Http\Controllers\AiTrainingExportController::class, 'exportCSV'])->name('api.training.export.csv');
        Route::get('/api/training/export/json', [\App\Http\Controllers\AiTrainingExportController::class, 'exportJSON'])->name('api.training.export.json');
    });

    // Takeover
    Route::controller(TakeoverController::class)->prefix('takeover')->name('takeover.')->group(function () {
        Route::post('/wa/{phone}/takeover', 'takeoverWa')->name('wa.takeover');
        Route::post('/wa/{phone}/handback', 'handbackWa')->name('wa.handback');
        Route::get('/wa/{phone}/status', 'getWaConversationStatus')->name('wa.status');
        Route::post('/ig/{id}/takeover', 'takeoverIg')->name('ig.takeover');
        Route::post('/ig/{id}/handback', 'handbackIg')->name('ig.handback');
        Route::get('/logs', 'logsPage')->name('logs');
        Route::get('/logs/data', 'getLogs')->name('logs.data');
        Route::get('/settings', 'getSettings')->name('settings.get');
        Route::post('/settings', 'updateSettings')->name('settings.update');
    });

    // Web Widgets
    Route::controller(WebWidgetController::class)->prefix('web-widgets')->name('web-widgets.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{webWidget}/edit', 'edit')->name('edit');
        Route::put('/{webWidget}', 'update')->name('update');
        Route::delete('/{webWidget}', 'destroy')->name('destroy');
        Route::patch('/{webWidget}/toggle', 'toggle')->name('toggle');
        Route::post('/{webWidget}/regenerate-key', 'regenerateKey')->name('regenerate-key');
        Route::get('/{webWidget}/embed-code', 'getEmbedCode')->name('embed-code');
    });

    // Sequences
    Route::controller(SequenceController::class)->prefix('sequences')->name('sequences.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{sequence}/edit', 'edit')->name('edit');
        Route::put('/{sequence}', 'update')->name('update');
        Route::delete('/{sequence}', 'destroy')->name('destroy');
        Route::patch('/{sequence}/toggle', 'toggle')->name('toggle');
        Route::get('/{sequence}/enrollments', 'enrollments')->name('enrollments');
        Route::post('/{sequence}/enroll', 'manualEnroll')->name('enroll');
        Route::post('/enrollments/{enrollment}/cancel', 'cancelEnrollment')->name('enrollment.cancel');
    });

    // Subscription Management
    Route::controller(SubscriptionController::class)->prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/upgrade', 'upgrade')->name('upgrade');
        Route::post('/cancel', 'cancel')->name('cancel');
        Route::post('/reactivate', 'reactivate')->name('reactivate');
    });

    // Support
    Route::controller(SupportController::class)->prefix('support')->name('support.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{ticket}', 'show')->name('show');
        Route::post('/{ticket}/reply', 'reply')->name('reply');
        Route::post('/{ticket}/rate', 'rate')->name('rate');
        Route::post('/{ticket}/reopen', 'reopen')->name('reopen');
    });

    // Documentation
    Route::get('/docs', [App\Http\Controllers\DocumentationController::class, 'index'])->name('documentation.index');

});



