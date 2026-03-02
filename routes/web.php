<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AutoReplyRuleController;
use App\Http\Controllers\AutoReplyLogController;
use App\Http\Controllers\ChatMediaController;
use App\Http\Controllers\KbArticleController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppInboxController;
use App\Http\Controllers\WhatsAppAnalyticsController;
use App\Http\Controllers\WhatsAppBroadcastController;
use App\Http\Controllers\InstagramOAuthController;
use App\Http\Controllers\InstagramCommentController;
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
use App\Http\Controllers\ChatAssignmentController;
use App\Http\Controllers\ChatAutomationController;
use App\Http\Controllers\ContactSegmentController;

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
// TWO-FACTOR AUTHENTICATION (User)
// ============================
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa', [\App\Http\Controllers\TwoFactorController::class, 'showVerify'])->name('2fa');
    Route::post('/2fa', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/recovery', [\App\Http\Controllers\TwoFactorController::class, 'verifyRecoveryCode'])->name('2fa.recovery');
});

// ============================
// API DOCUMENTATION (Public)
// ============================
Route::get('/docs/api', [App\Http\Controllers\ApiDocumentationController::class, 'index'])->name('api.docs');
Route::get('/docs/api/openapi.json', [App\Http\Controllers\ApiDocumentationController::class, 'openapi'])->name('api.openapi');

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

// Pricing Page
Route::get('/pricing', function (Illuminate\Http\Request $request) {
    // Jika user sudah login dan punya subscription aktif
    if (auth()->check()) {
        $user = auth()->user();
        
        // Cek apakah ada payment pending
        $pendingPayment = \App\Models\Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();
        
        // Jika ada plan subscription aktif, tampilkan pricing dengan upgrade options
        $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('pages.pricing.index', compact('plans', 'pendingPayment'));
    }
    
    // Jika belum login dan tidak ada plan, tampilkan pricing page
    $plans = \App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get();
    $pendingPayment = null;
    return view('pages.pricing.index', compact('plans', 'pendingPayment'));
})->name('pricing');

// ============================
// PROTECTED USER FEATURES (Auth + Verified + Not Suspended + 2FA)
// ============================
Route::middleware(['auth', 'verified', 'suspended', '2fa'])->group(function () {
    
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
    
    // Two-Factor Authentication (User)
    Route::get('/2fa/setup', [\App\Http\Controllers\TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('/2fa/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('/2fa/regenerate-recovery', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('2fa.regenerate-recovery');

    // Upgrade & Pending Subscription
    Route::get('/upgrade', function (Illuminate\Http\Request $request) {
        $feature = $request->get('feature');
        $featureNames = [
            'broadcasts' => 'Broadcast Messages',
            'sequences' => 'Drip Sequences',
            'web_widgets' => 'Web Chat Widget',
            'api_access' => 'Akses API',
        ];
        $featureName = $featureNames[$feature] ?? 'Fitur';
        
        $user = auth()->user();
        
        // Cek payment pending
        $pendingPayment = \App\Models\Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();
        
        $plans = \App\Models\Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        return view('pages.subscription.upgrade', compact('plans', 'feature', 'featureName', 'pendingPayment'));
    })->name('upgrade');

    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
    Route::get('/subscription/pending', function () {
        return view('subscription.pending');
    })->name('subscription.pending');

    Route::get('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');

    // Checkout & Payment (untuk user yang sudah login)
    Route::controller(CheckoutController::class)->group(function() {
        Route::get('/checkout/{plan}', 'checkout')->name('checkout.index');
        Route::post('/checkout/{plan}', 'processCheckout')->name('checkout.process');
        Route::get('/checkout/success/{invoiceNumber}', 'success')->name('checkout.success');
        Route::get('/checkout/midtrans/finish', 'midtransFinish')->name('checkout.midtrans.finish');
        Route::get('/payment/history', 'history')->name('checkout.history');
        Route::get('/payment/{invoiceNumber}', 'payment')->name('checkout.payment');
        Route::get('/payment/{invoiceNumber}/midtrans', 'payWithMidtrans')->name('checkout.midtrans.pay');
        Route::post('/payment/{payment}/upload-proof', 'uploadProof')->name('checkout.upload-proof');
        Route::post('/payment/{payment}/apply-promo', 'applyPromo')->name('checkout.apply-promo');
    });

    // Simulator
    Route::get('/simulator', [SimulatorController::class, 'index'])->name('simulator.index');
    Route::post('/simulator/send', [SimulatorController::class, 'sendMessage'])->name('simulator.send');

    // CSAT Settings
    Route::get('/csat/settings', function() { return view('pages.csat.settings'); })->name('csat.settings');
    Route::post('/csat/settings', function(Illuminate\Http\Request $request) {
        auth()->user()->update([
            'csat_enabled' => $request->boolean('csat_enabled'),
            'csat_whatsapp_enabled' => $request->boolean('csat_whatsapp_enabled'),
            'csat_delay_minutes' => (int) $request->csat_delay_minutes,
            'csat_message' => $request->csat_message,
        ]);
        return back()->with('success', 'Pengaturan CSAT berhasil disimpan!');
    })->name('csat.settings.update');
    Route::get('/csat', function() { return view('pages.csat.index'); })->name('csat.index');

    // Analytics & Reports
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/logs', [AutoReplyLogController::class, 'index'])->name('logs.index');
    
    // AI Performance
    Route::get('/ai-performance', [App\Http\Controllers\AiPerformanceController::class, 'index'])->name('ai-performance.index');
    Route::get('/ai-performance/intent-accuracy', [App\Http\Controllers\AiPerformanceController::class, 'getIntentAccuracy'])->name('ai-performance.intent-accuracy');
    Route::get('/ai-performance/response-relevance', [App\Http\Controllers\AiPerformanceController::class, 'getResponseRelevance'])->name('ai-performance.response-relevance');
    Route::get('/ai-performance/knowledge-gaps', [App\Http\Controllers\AiPerformanceController::class, 'getKnowledgeGaps'])->name('ai-performance.knowledge-gaps');
    Route::get('/ai-performance/training-improvement', [App\Http\Controllers\AiPerformanceController::class, 'getTrainingImprovement'])->name('ai-performance.training-improvement');
    Route::get('/ai-performance/confidence-distribution', [App\Http\Controllers\AiPerformanceController::class, 'getConfidenceDistribution'])->name('ai-performance.confidence-distribution');
    Route::get('/ai-performance/popular-intents', [App\Http\Controllers\AiPerformanceController::class, 'getPopularIntents'])->name('ai-performance.popular-intents');

    // Reports Module - All tiers
    Route::get('/reports/csat', function() { return view('pages.reports.csat.index'); })->name('reports.csat');
    Route::get('/reports/export', function() { return view('pages.reports.export.index'); })->name('reports.export');
    Route::get('/reports/export/history', function() { return view('pages.reports.export.history'); })->name('reports.export.history');

    // Reports Module - Business & Enterprise only
    Route::middleware(['tier:business'])->group(function () {
        Route::get('/reports/realtime', [App\Http\Controllers\RealtimeDashboardController::class, 'index'])->name('reports.realtime');
        Route::get('/reports/quality', [App\Http\Controllers\ConversationQualityController::class, 'index'])->name('reports.quality');
    });

    // Reports Module - Enterprise only
    Route::middleware(['tier:enterprise'])->group(function () {
        Route::get('/reports/comparative', [App\Http\Controllers\ComparativeAnalyticsController::class, 'index'])->name('reports.comparative');
        
        // Scheduled Reports
        Route::resource('reports/scheduled', App\Http\Controllers\ScheduledReportController::class)->names([
            'index' => 'reports.scheduled.index',
            'create' => 'reports.scheduled.create',
            'store' => 'reports.scheduled.store',
            'show' => 'reports.scheduled.show',
            'edit' => 'reports.scheduled.edit',
            'update' => 'reports.scheduled.update',
            'destroy' => 'reports.scheduled.destroy',
        ]);
        Route::post('reports/scheduled/{id}/toggle', [App\Http\Controllers\ScheduledReportController::class, 'toggleStatus'])->name('reports.scheduled.toggle');
        Route::post('reports/scheduled/{id}/send-now', [App\Http\Controllers\ScheduledReportController::class, 'sendNow'])->name('reports.scheduled.send-now');

        // Report Templates
        Route::resource('reports/templates', App\Http\Controllers\ReportTemplateController::class)->names([
            'index' => 'reports.templates.index',
            'create' => 'reports.templates.create',
            'store' => 'reports.templates.store',
            'show' => 'reports.templates.show',
            'edit' => 'reports.templates.edit',
            'update' => 'reports.templates.update',
            'destroy' => 'reports.templates.destroy',
        ]);
        Route::post('reports/templates/{id}/duplicate', [App\Http\Controllers\ReportTemplateController::class, 'duplicate'])->name('reports.templates.duplicate');
        Route::post('reports/templates/{id}/set-default', [App\Http\Controllers\ReportTemplateController::class, 'setDefault'])->name('reports.templates.set-default');
    });

    // AI Analytics Dashboard (User facing)
    Route::prefix('analytics')->name('user.analytics.')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('index');
        Route::get('/chart-data', [App\Http\Controllers\AnalyticsController::class, 'getChartData'])->name('chart-data');
    });

    // KB Web Scraping
    Route::prefix('kb/scrape')->name('kb.scrape.')->group(function () {
        Route::post('/start', [App\Http\Controllers\KbScrapeController::class, 'start'])->name('start')->middleware(['auth']);
        Route::get('/jobs', [App\Http\Controllers\KbScrapeController::class, 'list'])->name('list')->middleware(['auth']);
        Route::get('/status/{jobId}', [App\Http\Controllers\KbScrapeController::class, 'status'])->name('status')->middleware(['auth']);
        Route::post('/save/{jobId}', [App\Http\Controllers\KbScrapeController::class, 'saveToKb'])->name('save')->middleware(['auth']);
        Route::delete('/{jobId}', [App\Http\Controllers\KbScrapeController::class, 'destroy'])->name('destroy')->middleware(['auth']);
        Route::get('/process/{jobId}', [App\Http\Controllers\KbScrapeController::class, 'process'])->name('process');
    });
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');

    // Instagram
    Route::middleware(['global_feature:enable_instagram'])->controller(InboxController::class)->prefix('inbox')->name('inbox')->group(function () {
        Route::get('/', 'index'); // name: inbox
        Route::get('/enterprise', function () {
            return view('pages.inbox.enterprise');
        })->name('.enterprise'); // inbox.enterprise
    });
    // Instagram Comments Management
    Route::middleware(['global_feature:enable_instagram'])->group(function () {
        Route::get('/instagram/comments', [InstagramCommentController::class, 'index'])->name('instagram.comments');
        Route::post('/instagram/comments/fetch', [InstagramCommentController::class, 'fetchFromApi'])->name('instagram.comments.fetch');
        Route::post('/instagram/comments/{id}/reply', [InstagramCommentController::class, 'reply'])->name('instagram.comments.reply');
        Route::post('/instagram/comments/bulk-reply', [InstagramCommentController::class, 'bulkReply'])->name('instagram.comments.bulk-reply');
        Route::put('/instagram/comments/settings', [InstagramCommentController::class, 'autoReplySettings'])->name('instagram.comments.settings');
        Route::delete('/instagram/comments/{id}', [InstagramCommentController::class, 'destroy'])->name('instagram.comments.destroy');
    });
    Route::middleware(['global_feature:enable_instagram'])->group(function () {
        Route::get('/settings/instagram', [App\Http\Controllers\InstagramOAuthController::class, 'settings'])->name('instagram.settings');
        Route::get('/instagram/connect', [InstagramOAuthController::class, 'connect'])->name('instagram.connect');
        Route::post('/instagram/disconnect', [InstagramOAuthController::class, 'disconnect'])->name('instagram.disconnect');
        Route::post('/instagram/refresh-token', [InstagramOAuthController::class, 'refreshToken'])->name('instagram.refresh-token');
    });

    // WhatsApp
    Route::prefix('whatsapp')->name('whatsapp.')->middleware(['global_feature:enable_whatsapp'])->group(function () {
        Route::get('/analytics', [WhatsAppAnalyticsController::class, 'index'])->name('analytics');
        Route::middleware(['tier:business', 'feature:broadcasts', 'global_feature:enable_broadcasts'])->group(function () {
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
                
                // Tags Routes
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

    // Sequences - Enterprise only
    Route::middleware(['tier:enterprise'])->controller(SequenceController::class)->prefix('sequences')->name('sequences.')->group(function () {
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
        Route::post('/cancel', 'cancel')->name('cancel');
        Route::get('/upgrade', 'upgrade')->name('upgrade');
        Route::post('/reactivate', 'reactivate')->name('reactivate');
    });

    // AI Reply & Analysis
    Route::prefix('api/ai')->name('api.ai.')->group(function () {
        Route::post('/analyze-sentiment', [App\Http\Controllers\AiReplyController::class, 'analyzeSentiment'])->name('analyze-sentiment');
        Route::post('/detect-intent', [App\Http\Controllers\AiReplyController::class, 'detectIntent'])->name('detect-intent');
        Route::post('/suggest-replies', [App\Http\Controllers\AiReplyController::class, 'suggest'])->name('suggest-replies');
        Route::post('/summarize', [App\Http\Controllers\AiReplyController::class, 'summarizeConversation'])->name('summarize');
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

    // Settings & Quick Replies
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::resource('settings/quick-replies', QuickReplyController::class)->names([
        'index' => 'quick-replies.index',
        'store' => 'quick-replies.store',
        'update' => 'quick-replies.update',
        'destroy' => 'quick-replies.destroy',
    ]);
    
    // Quick Reply API Routes
    Route::prefix('api/quick-replies')->name('api.quick-replies.')->group(function() {
        Route::get('/', [QuickReplyController::class, 'fetch'])->name('fetch');
        Route::get('/categories', [QuickReplyController::class, 'categories'])->name('categories');
        Route::get('/search', [QuickReplyController::class, 'search'])->name('search');
        Route::post('/{quickReply}/track', [QuickReplyController::class, 'trackUsage'])->name('track');
    });

    // Business Profiles
    Route::get('/settings/business', [BusinessProfileController::class, 'index'])->name('settings.business');
    Route::post('/settings/business', [BusinessProfileController::class, 'store'])->name('settings.business.store');
    Route::put('/settings/business/{id?}', [BusinessProfileController::class, 'update'])->name('settings.business.update');
    Route::delete('/settings/business/{id}', [BusinessProfileController::class, 'destroy'])->name('settings.business.destroy');
    Route::post('/settings/business/{id}/default', [BusinessProfileController::class, 'setDefault'])->name('settings.business.setDefault');

    // Chat Media Gallery Routes
    Route::get('/chat/{type}/{conversationId}/media', [ChatMediaController::class, 'index'])
        ->name('chat.media.index')
        ->middleware(['auth']);

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

    // Chat Automation Routes
    Route::controller(ChatAutomationController::class)->prefix('automations')->name('automations.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{automation}/edit', 'edit')->name('edit');
        Route::put('/{automation}', 'update')->name('update');
        Route::delete('/{automation}', 'destroy')->name('destroy');
        Route::post('/{automation}/toggle', 'toggleStatus')->name('toggle');
    });

    // Contact Segments Routes
    Route::controller(ContactSegmentController::class)->prefix('segments')->name('segments.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{segment}', 'show')->name('show');
        Route::get('/{segment}/edit', 'edit')->name('edit');
        Route::put('/{segment}', 'update')->name('update');
        Route::delete('/{segment}', 'destroy')->name('destroy');
        
        // Contact management
        Route::post('/{segment}/contacts', 'addContact')->name('contacts.add');
        Route::delete('/{segment}/contacts/{contactType}/{contactId}', 'removeContact')->name('contacts.remove');
        Route::post('/{segment}/bulk-add', 'bulkAddContacts')->name('bulk-add');
        
        // API routes
        Route::get('/{segment}/contacts-data', 'getSegmentContacts')->name('contacts-data');
        Route::get('/{segment}/available-contacts', 'getAvailableContacts')->name('available-contacts');
        Route::post('/preview-filters', 'previewFilters')->name('preview-filters');
    });

    // Chat Assignment Routes
    Route::prefix('api/chat')->name('api.chat.')->group(function () {
        Route::get('/agents', [ChatAssignmentController::class, 'getAgents'])->name('agents');
        Route::get('/{type}/{id}/assignment', [ChatAssignmentController::class, 'getAssignment'])->name('assignment.get');
        Route::post('/{type}/{id}/assign', [ChatAssignmentController::class, 'assign'])->name('assign');
        Route::delete('/{type}/{id}/assign', [ChatAssignmentController::class, 'unassign'])->name('unassign');
        Route::post('/{type}/{id}/transfer', [ChatAssignmentController::class, 'transfer'])->name('transfer');
        Route::post('/typing', [ChatAssignmentController::class, 'typing'])->name('typing');
        Route::get('/{type}/{id}/typing-status', [ChatAssignmentController::class, 'typingStatus'])->name('typing-status');
        Route::get('/{type}/{id}/collision-check', [ChatAssignmentController::class, 'collisionCheck'])->name('collision-check');
    });

    // Chat Media Routes
    Route::prefix('api/chat-media')->name('api.chat-media.')->group(function () {
        Route::get('/{id}', [ChatMediaController::class, 'show'])->name('show');
        Route::delete('/{id}', [ChatMediaController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [ChatMediaController::class, 'download'])->name('download');
    });

    // Contact Panel Routes
    Route::prefix('api/contacts')->name('api.contacts.')->group(function () {
        Route::get('/{type}/{id}/details', [App\Http\Controllers\ContactPanelController::class, 'getContactDetails'])->name('details');
        Route::post('/{type}/{id}/notes', [App\Http\Controllers\ContactPanelController::class, 'addNote'])->name('notes.add');
        Route::delete('/notes/{id}', [App\Http\Controllers\ContactPanelController::class, 'deleteNote'])->name('notes.delete');
        Route::put('/{type}/{id}/tags', [App\Http\Controllers\ContactPanelController::class, 'updateTags'])->name('tags.update');
        Route::put('/{type}/{id}/custom-fields', [App\Http\Controllers\ContactPanelController::class, 'updateCustomField'])->name('custom-fields.update');
        Route::post('/{type}/{id}/block', [App\Http\Controllers\ContactPanelController::class, 'blockContact'])->name('block');
        Route::delete('/{type}/{id}/conversation', [App\Http\Controllers\ContactPanelController::class, 'deleteConversation'])->name('conversation.delete');
    });

    // Unified Inbox Routes
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/inbox', [App\Http\Controllers\UnifiedInboxController::class, 'index'])->name('inbox');
        
        // API Routes
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/conversations', [App\Http\Controllers\UnifiedInboxController::class, 'getConversations'])->name('conversations');
            Route::get('/messages/{platform}/{identifier}', [App\Http\Controllers\UnifiedInboxController::class, 'getMessages'])->name('messages');
            Route::post('/send', [App\Http\Controllers\UnifiedInboxController::class, 'sendMessage'])->name('send');
            Route::get('/search', [App\Http\Controllers\UnifiedInboxController::class, 'search'])->name('search');
            Route::get('/unread-counts', [App\Http\Controllers\UnifiedInboxController::class, 'getUnreadCounts'])->name('unread-counts');
        });
    });

    // Documentation
    Route::get('/docs', [DocumentationController::class, 'index'])->name('documentation.index');

    // My Assignments
    Route::get('/api/my-assignments', [ChatAssignmentController::class, 'myAssignments'])->name('api.my-assignments');
    Route::get('/my-assignments', function() {
        return view('pages.assignments.index');
    })->name('my-assignments');

    // Reports API - Business & Enterprise only
    Route::prefix('api/reports')->name('api.reports.')->middleware(['tier:business'])->group(function () {
        // Realtime
        Route::get('/realtime/stats', [App\Http\Controllers\RealtimeDashboardController::class, 'getOverview'])->name('realtime.stats');
        Route::get('/realtime/active-conversations', [App\Http\Controllers\RealtimeDashboardController::class, 'getActiveConversations'])->name('realtime.active-conversations');
        Route::get('/realtime/queue', [App\Http\Controllers\RealtimeDashboardController::class, 'getQueueLength'])->name('realtime.queue');
        Route::get('/realtime/agents', [App\Http\Controllers\RealtimeDashboardController::class, 'getAgentStatus'])->name('realtime.agents');
        Route::get('/realtime/sentiment', [App\Http\Controllers\RealtimeDashboardController::class, 'getSentimentTrend'])->name('realtime.sentiment');
        Route::get('/realtime/activity', [App\Http\Controllers\RealtimeDashboardController::class, 'getRecentActivity'])->name('realtime.activity');
        
        // Quality
        Route::get('/quality/sentiment', [App\Http\Controllers\ConversationQualityController::class, 'getSentimentAnalysis'])->name('quality.sentiment');
        Route::get('/quality/response-time', [App\Http\Controllers\ConversationQualityController::class, 'getResponseTimeMetrics'])->name('quality.response-time');
        Route::get('/quality/escalation', [App\Http\Controllers\ConversationQualityController::class, 'getEscalationRate'])->name('quality.escalation');
        Route::get('/quality/scores', [App\Http\Controllers\ConversationQualityController::class, 'getConversationScores'])->name('quality.scores');
    });
    
    // Reports API - Enterprise only
    Route::prefix('api/reports')->name('api.reports.')->middleware(['tier:enterprise'])->group(function () {
        // Comparative
        Route::get('/comparative/compare', [App\Http\Controllers\ComparativeAnalyticsController::class, 'comparePeriods'])->name('comparative.compare');
        Route::get('/comparative/week-over-week', [App\Http\Controllers\ComparativeAnalyticsController::class, 'getWeekOverWeek'])->name('comparative.week-over-week');
        Route::get('/comparative/month-over-month', [App\Http\Controllers\ComparativeAnalyticsController::class, 'getMonthOverMonth'])->name('comparative.month-over-month');
        Route::get('/comparative/benchmark', [App\Http\Controllers\ComparativeAnalyticsController::class, 'getBenchmarkData'])->name('comparative.benchmark');
        Route::get('/comparative/trends', [App\Http\Controllers\ComparativeAnalyticsController::class, 'getTrendAnalysis'])->name('comparative.trends');
    });

    // AI Performance API
    Route::prefix('api/ai-performance')->name('api.ai-performance.')->group(function () {
        Route::get('/intent-accuracy', [App\Http\Controllers\AiPerformanceController::class, 'getIntentAccuracy'])->name('intent-accuracy');
        Route::get('/response-relevance', [App\Http\Controllers\AiPerformanceController::class, 'getResponseRelevance'])->name('response-relevance');
        Route::get('/knowledge-gaps', [App\Http\Controllers\AiPerformanceController::class, 'getKnowledgeGaps'])->name('knowledge-gaps');
        Route::get('/training-improvement', [App\Http\Controllers\AiPerformanceController::class, 'getTrainingImprovement'])->name('training-improvement');
        Route::get('/confidence-distribution', [App\Http\Controllers\AiPerformanceController::class, 'getConfidenceDistribution'])->name('confidence-distribution');
        Route::get('/popular-intents', [App\Http\Controllers\AiPerformanceController::class, 'getPopularIntents'])->name('popular-intents');
    });

    // API: Tags only (Plans moved to api.php)
    Route::get('/api/tags', function () {
        return response()->json([
            'tags' => \App\Models\Tag::where('user_id', auth()->id())->orWhereNull('user_id')->get()
        ]);
    })->name('api.tags');

});
