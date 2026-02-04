<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Routes untuk Super Admin Panel
*/
// Auth Routes (tidak perlu middleware)
Route::get('/', function () {
    return redirect()->route('admin.login');
});
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Protected Admin Routes (perlu login via /admin/login)
Route::middleware(['admin'])->group(function () {   
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Payments Management
    Route::prefix('payments')->name('admin.payments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminPaymentController::class, 'index'])->name('index');
        Route::post('/{payment}/approve', [App\Http\Controllers\Admin\AdminPaymentController::class, 'approve'])->name('approve');
        Route::post('/{payment}/reject', [App\Http\Controllers\Admin\AdminPaymentController::class, 'reject'])->name('reject');
    });

    // Plans Management
    Route::prefix('plans')->name('admin.plans.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminPlanController::class, 'index'])->name('index');
        Route::get('/{plan}/edit', [App\Http\Controllers\Admin\AdminPlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [App\Http\Controllers\Admin\AdminPlanController::class, 'update'])->name('update');
    });

    // Promo Codes Management
    Route::prefix('promo-codes')->name('admin.promo-codes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminPromoCodeController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\AdminPromoCodeController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\AdminPromoCodeController::class, 'store'])->name('store');
        Route::get('/{promoCode}/edit', [App\Http\Controllers\Admin\AdminPromoCodeController::class, 'edit'])->name('edit');
        Route::put('/{promoCode}', [App\Http\Controllers\Admin\AdminPromoCodeController::class, 'update'])->name('update');
        Route::delete('/{promoCode}', [App\Http\Controllers\Admin\AdminPromoCodeController::class, 'destroy'])->name('destroy');
    });

    // Support Tickets Management
    Route::prefix('support')->name('admin.support.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminSupportController::class, 'index'])->name('index');
        Route::get('/{ticket}', [App\Http\Controllers\Admin\AdminSupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [App\Http\Controllers\Admin\AdminSupportController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/close', [App\Http\Controllers\Admin\AdminSupportController::class, 'close'])->name('close');
        Route::post('/{ticket}/reopen', [App\Http\Controllers\Admin\AdminSupportController::class, 'reopen'])->name('reopen');
    });

    // User Management
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\AdminUserController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}', [App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [App\Http\Controllers\Admin\AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/toggle-vip', [App\Http\Controllers\Admin\AdminUserController::class, 'toggleVip'])->name('toggle-vip');
        Route::patch('/{user}/toggle-verify', [App\Http\Controllers\Admin\AdminUserController::class, 'toggleVerify'])->name('toggle-verify');
        Route::post('/{user}/assign-subscription', [App\Http\Controllers\Admin\AdminUserController::class, 'assignSubscription'])->name('assign-subscription');
        Route::post('/{user}/reset-usage', [App\Http\Controllers\Admin\AdminUserController::class, 'resetUsage'])->name('reset-usage');
        Route::post('/{user}/impersonate', [App\Http\Controllers\Admin\AdminUserController::class, 'impersonate'])->name('impersonate');
        Route::post('/{user}/suspend', [App\Http\Controllers\Admin\AdminUserController::class, 'suspend'])->name('suspend');
        Route::post('/{user}/activate', [App\Http\Controllers\Admin\AdminUserController::class, 'activate'])->name('activate');
    });

    // Stop impersonate (route khusus di luar prefix)
    Route::get('/stop-impersonate', [App\Http\Controllers\Admin\AdminUserController::class, 'stopImpersonate'])->name('admin.stop-impersonate');

    // Failed Jobs Monitoring
    Route::prefix('failed-jobs')->name('admin.failed-jobs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FailedJobController::class, 'index'])->name('index');
        Route::post('/{id}/retry', [\App\Http\Controllers\Admin\FailedJobController::class, 'retry'])->name('retry');
        Route::post('/retry-all', [\App\Http\Controllers\Admin\FailedJobController::class, 'retryAll'])->name('retry-all');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\FailedJobController::class, 'destroy'])->name('destroy');
        Route::post('/flush', [\App\Http\Controllers\Admin\FailedJobController::class, 'flush'])->name('flush');
    });

    // Log Viewer
    Route::prefix('logs')->name('admin.logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LogViewerController::class, 'index'])->name('index');
        Route::post('/clear', [\App\Http\Controllers\Admin\LogViewerController::class, 'clear'])->name('clear');
    });

    // Activity Logs
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity-logs.index');

    // System Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('admin.settings.update');

    // System Health
    Route::get('/system-health', [App\Http\Controllers\Admin\SystemHealthController::class, 'index'])->name('admin.system-health.index');
    Route::post('/system-health/cleanup-orphans', [App\Http\Controllers\Admin\SystemHealthController::class, 'cleanupOrphans'])->name('admin.system-health.cleanup-orphans');
    Route::post('/system-health/service-action', [App\Http\Controllers\Admin\SystemHealthController::class, 'manageService'])->name('admin.system-health.service-action');
    Route::get('/system-health/logs', [App\Http\Controllers\Admin\SystemHealthController::class, 'viewLogs'])->name('admin.system-health.logs');

    // Backups
    Route::prefix('backups')->name('admin.backups.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminBackupController::class, 'index'])->name('index');
        Route::post('/generate', [App\Http\Controllers\Admin\AdminBackupController::class, 'create'])->name('create');
        Route::get('/download/{filename}', [App\Http\Controllers\Admin\AdminBackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [App\Http\Controllers\Admin\AdminBackupController::class, 'destroy'])->name('destroy');
    });

    // Maintenance Actions
    Route::prefix('maintenance')->name('admin.maintenance.')->group(function () {
        Route::post('/clear-cache', [App\Http\Controllers\Admin\AdminSystemActionController::class, 'clearCache'])->name('clear-cache');
        Route::post('/clear-views', [App\Http\Controllers\Admin\AdminSystemActionController::class, 'clearViews'])->name('clear-views');
        Route::post('/refresh-tokens', [App\Http\Controllers\Admin\AdminSystemActionController::class, 'refreshTokens'])->name('refresh-tokens');
    });

    // Revenue Dashboard
    Route::get('/revenue', [App\Http\Controllers\Admin\AdminRevenueController::class, 'index'])->name('admin.revenue.index');

    // Broadcast & Announcements
    Route::prefix('broadcast')->name('admin.broadcast.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminBroadcastController::class, 'index'])->name('index');
        Route::post('/send', [App\Http\Controllers\Admin\AdminBroadcastController::class, 'send'])->name('send');
    });

    // Subscription Alerts
    Route::get('/alerts', function () {
        return view('admin.alerts.index');
    })->name('admin.alerts.index');

    // Platform Statistics
    Route::get('/stats', [App\Http\Controllers\Admin\AdminPlatformStatsController::class, 'index'])->name('admin.stats.index');

    // Export Reports
    Route::prefix('export')->name('admin.export.')->group(function () {
        Route::get('/users', [App\Http\Controllers\Admin\AdminExportController::class, 'users'])->name('users');
        Route::get('/payments', [App\Http\Controllers\Admin\AdminExportController::class, 'payments'])->name('payments');
    });

    // Refund Management
    Route::prefix('refunds')->name('admin.refunds.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminRefundController::class, 'index'])->name('index');
        Route::post('/{refund}/approve', [App\Http\Controllers\Admin\AdminRefundController::class, 'approve'])->name('approve');
        Route::post('/{refund}/reject', [App\Http\Controllers\Admin\AdminRefundController::class, 'reject'])->name('reject');
    });

    // Email Logs
    Route::get('/email-logs', function () {
        return view('admin.email-logs.index');
    })->name('admin.email-logs.index');

    // API Usage
    Route::get('/api-usage', function () {
        return view('admin.api-usage.index');
    })->name('admin.api-usage.index');

    // Feature Flags
    Route::prefix('feature-flags')->name('admin.feature-flags.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'update'])->name('update');
        Route::post('/{setting}/toggle', [App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'toggle'])->name('toggle');
    });

    // User Feedback
    Route::prefix('feedback')->name('admin.feedback.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminUserFeedbackController::class, 'index'])->name('index');
        Route::patch('/{feedback}/status', [App\Http\Controllers\Admin\AdminUserFeedbackController::class, 'updateStatus'])->name('update-status');
    });

    // Bulk Actions
    Route::prefix('bulk')->name('admin.bulk.')->group(function () {
        Route::get('/', function () { return view('admin.bulk.index'); })->name('index');
        Route::post('/extend', [App\Http\Controllers\Admin\AdminSystemActionController::class, 'bulkExtend'])->name('extend');
        Route::post('/reset-usage', [App\Http\Controllers\Admin\AdminSystemActionController::class, 'bulkResetUsage'])->name('reset-usage');
    });

    // QA Testing Dashboard
    Route::prefix('qa')->name('admin.qa.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminQaController::class, 'index'])->name('index');
        Route::post('/save-result', [App\Http\Controllers\Admin\AdminQaController::class, 'saveResult'])->name('save-result');
        Route::post('/reset', [App\Http\Controllers\Admin\AdminQaController::class, 'resetResults'])->name('reset');
    });

});

