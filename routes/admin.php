<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Routes untuk Super Admin Panel dengan Role-Based Access Control (RBAC)
|
| Role Hierarchy:
| - superadmin: Akses penuh ke semua fitur
| - finance: Manage payments, refunds, revenue (view only untuk users)
| - support: View users, tickets, activity logs (tidak bisa modify data)
|
| Security:
| - All routes: middleware 'admin' (autentikasi) + 'admin.ip' (IP whitelist)
| - Role middleware: 'admin.role:role1,role2' untuk membatasi akses
 */

// ==========================================
// AUTH ROUTES (Public - No middleware required)
// ==========================================
Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// ==========================================
// PROTECTED ROUTES - Base Authentication
// ==========================================
Route::middleware(['admin', 'admin.ip'])->group(function () {
    
    // --------------------------------------
    // ALL ADMIN ACCESS (superadmin, finance, support)
    // --------------------------------------
    
    // Dashboard - All roles
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    
    // Activity Logs - View only (All roles)
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])
        ->name('admin.activity-logs.index');
    
    // User Management - VIEW ONLY (All roles)
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('index');
        // CREATE & STORE - Superadmin only (ditaruh di atas route dengan parameter)
        Route::middleware(['admin.role:superadmin'])->group(function () {
            Route::get('/create', [App\Http\Controllers\Admin\AdminUserController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('store');
        });
        Route::get('/{user}', [App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('show');
    });
    
    // 2FA Management - Personal (All roles untuk akun sendiri)
    Route::get('/2fa', [App\Http\Controllers\Admin\TwoFactorController::class, 'show'])->name('admin.2fa');
    Route::post('/2fa', [App\Http\Controllers\Admin\TwoFactorController::class, 'verify'])->name('admin.2fa.verify');
    Route::post('/2fa/recovery', [App\Http\Controllers\Admin\TwoFactorController::class, 'verifyRecoveryCode'])->name('admin.2fa.recovery');
    Route::get('/2fa/setup', [App\Http\Controllers\Admin\TwoFactorController::class, 'showSetup'])->name('admin.2fa.setup');
    Route::post('/2fa/enable', [App\Http\Controllers\Admin\TwoFactorController::class, 'enable'])->name('admin.2fa.enable');
    Route::post('/2fa/disable', [App\Http\Controllers\Admin\TwoFactorController::class, 'disable'])->name('admin.2fa.disable');
    
    // Stop impersonate - Available when impersonating
    Route::get('/stop-impersonate', [App\Http\Controllers\Admin\AdminUserController::class, 'stopImpersonate'])
        ->name('admin.stop-impersonate');
    
    // --------------------------------------
    // FINANCE + SUPERADMIN ONLY
    // --------------------------------------
    Route::middleware(['admin.role:superadmin,finance'])->group(function () {
        
        // Payments Management - Finance can view and approve/reject
        Route::prefix('payments')->name('admin.payments.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminPaymentController::class, 'index'])->name('index');
            Route::post('/{payment}/approve', [App\Http\Controllers\Admin\AdminPaymentController::class, 'approve'])->name('approve');
            Route::post('/{payment}/reject', [App\Http\Controllers\Admin\AdminPaymentController::class, 'reject'])->name('reject');
        });
        
        // Refund Management
        Route::prefix('refunds')->name('admin.refunds.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminRefundController::class, 'index'])->name('index');
            Route::post('/{refund}/approve', [App\Http\Controllers\Admin\AdminRefundController::class, 'approve'])->name('approve');
            Route::post('/{refund}/reject', [App\Http\Controllers\Admin\AdminRefundController::class, 'reject'])->name('reject');
        });
        
        // Revenue Dashboard
        Route::get('/revenue', [App\Http\Controllers\Admin\AdminRevenueController::class, 'index'])
            ->name('admin.revenue.index');
        
        // Export Reports
        Route::prefix('export')->name('admin.export.')->group(function () {
            Route::get('/users', [App\Http\Controllers\Admin\AdminExportController::class, 'users'])->name('users');
            Route::get('/payments', [App\Http\Controllers\Admin\AdminExportController::class, 'payments'])->name('payments');
        });
    });
    
    // --------------------------------------
    // SUPPORT + SUPERADMIN ONLY
    // --------------------------------------
    Route::middleware(['admin.role:superadmin,support'])->group(function () {
        
        // Support Tickets Management
        Route::prefix('support')->name('admin.support.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminSupportController::class, 'index'])->name('index');
            Route::get('/{ticket}', [App\Http\Controllers\Admin\AdminSupportController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [App\Http\Controllers\Admin\AdminSupportController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/close', [App\Http\Controllers\Admin\AdminSupportController::class, 'close'])->name('close');
            Route::post('/{ticket}/reopen', [App\Http\Controllers\Admin\AdminSupportController::class, 'reopen'])->name('reopen');
            Route::post('/{ticket}/resolve', [App\Http\Controllers\Admin\AdminSupportController::class, 'resolve'])->name('resolve');
            Route::post('/{ticket}/assign', [App\Http\Controllers\Admin\AdminSupportController::class, 'assign'])->name('assign');
            Route::post('/{ticket}/priority', [App\Http\Controllers\Admin\AdminSupportController::class, 'updatePriority'])->name('priority');
            Route::post('/{ticket}/notes', [App\Http\Controllers\Admin\AdminSupportController::class, 'updateInternalNotes'])->name('notes');
        });
        
        // User Feedback
        Route::prefix('feedback')->name('admin.feedback.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminUserFeedbackController::class, 'index'])->name('index');
            Route::patch('/{feedback}/status', [App\Http\Controllers\Admin\AdminUserFeedbackController::class, 'updateStatus'])->name('update-status');
        });
    });
    
    // --------------------------------------
    // SUPERADMIN ONLY
    // --------------------------------------
    Route::middleware(['admin.role:superadmin'])->group(function () {
        
        // User Management - FULL CONTROL (edit, update, destroy, dll)
        Route::prefix('users')->name('admin.users.')->group(function () {
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
        
        // Failed Jobs Monitoring
        Route::prefix('failed-jobs')->name('admin.failed-jobs.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\FailedJobController::class, 'index'])->name('index');
            Route::post('/{id}/retry', [\App\Http\Controllers\Admin\FailedJobController::class, 'retry'])->name('retry');
            Route::post('/retry-all', [\App\Http\Controllers\Admin\FailedJobController::class, 'retryAll'])->name('retry-all');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\FailedJobController::class, 'destroy'])->name('destroy');
            Route::post('/flush', [\App\Http\Controllers\Admin\FailedJobController::class, 'flush'])->name('flush');
        });
        
        // Log Viewer - View all, clear logs
        Route::prefix('logs')->name('admin.logs.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LogViewerController::class, 'index'])->name('index');
            Route::post('/clear', [\App\Http\Controllers\Admin\LogViewerController::class, 'clear'])->name('clear');
        });
        
        // System Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('admin.settings.update');
        
        // System Health - View only (actions require superadmin)
        Route::get('/system-health', [App\Http\Controllers\Admin\SystemHealthController::class, 'index'])->name('admin.system-health.index');
        Route::get('/system-health/logs', [App\Http\Controllers\Admin\SystemHealthController::class, 'viewLogs'])->name('admin.system-health.logs');
        Route::post('/system-health/cleanup-orphans', [App\Http\Controllers\Admin\SystemHealthController::class, 'cleanupOrphans'])->name('admin.system-health.cleanup-orphans');
        Route::post('/system-health/service-action', [App\Http\Controllers\Admin\SystemHealthController::class, 'manageService'])->name('admin.system-health.service-action');
        Route::post('/system-health/start-system-service', [App\Http\Controllers\Admin\SystemHealthController::class, 'startSystemService'])->name('admin.system-health.start-system-service');
        
        // Schedule Monitor
        Route::prefix('schedule')->name('admin.schedule.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ScheduleMonitorController::class, 'index'])->name('index');
            Route::post('/run', [App\Http\Controllers\Admin\ScheduleMonitorController::class, 'runTask'])->name('run');
            Route::get('/queue-status', [App\Http\Controllers\Admin\ScheduleMonitorController::class, 'getQueueStatus'])->name('queue-status');
        });
        
        // AI Provider Monitor
        Route::prefix('ai-providers')->name('admin.ai-providers.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AiProviderMonitorController::class, 'index'])->name('index');
            Route::get('/test-view', function() { return view('admin.ai-providers.test'); })->name('test-view');
            Route::get('/status', [App\Http\Controllers\Admin\AiProviderMonitorController::class, 'status'])->name('status');
            Route::get('/{provider}/test', [App\Http\Controllers\Admin\AiProviderMonitorController::class, 'test'])->name('test');
            Route::post('/{provider}/reset', [App\Http\Controllers\Admin\AiProviderMonitorController::class, 'reset'])->name('reset');
            Route::post('/switch', [App\Http\Controllers\Admin\AiProviderMonitorController::class, 'switchProvider'])->name('switch');
        });
        
        // API Monitor
        Route::prefix('api-monitor')->name('admin.api-monitor.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ApiMonitorController::class, 'index'])->name('index');
            Route::get('/user/{userId}', [App\Http\Controllers\Admin\ApiMonitorController::class, 'getUserRequests'])->name('user');
            Route::post('/user/{userId}/block', [App\Http\Controllers\Admin\ApiMonitorController::class, 'blockUser'])->name('block');
            Route::post('/user/{userId}/unblock', [App\Http\Controllers\Admin\ApiMonitorController::class, 'unblockUser'])->name('unblock');
        });
        
        // Device Management (WA & IG)
        Route::prefix('devices')->name('admin.devices.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\DeviceManagementController::class, 'index'])->name('index');
            Route::post('/bulk', [App\Http\Controllers\Admin\DeviceManagementController::class, 'bulkAction'])->name('bulk');
            Route::post('/cleanup-orphaned', [App\Http\Controllers\Admin\DeviceManagementController::class, 'cleanupOrphaned'])->name('cleanup-orphaned');
            
            // WhatsApp routes
            Route::post('/wa/{sessionId}/reconnect', [App\Http\Controllers\Admin\DeviceManagementController::class, 'waReconnect'])->name('wa.reconnect');
            Route::post('/wa/{sessionId}/disconnect', [App\Http\Controllers\Admin\DeviceManagementController::class, 'waDisconnect'])->name('wa.disconnect');
            Route::delete('/wa/{sessionId}', [App\Http\Controllers\Admin\DeviceManagementController::class, 'waDestroy'])->name('wa.destroy');
            Route::get('/wa/{sessionId}/qr', [App\Http\Controllers\Admin\DeviceManagementController::class, 'waGetQrCode'])->name('wa.qr');
            
            // Instagram routes
            Route::get('/ig/{id}/reconnect', [App\Http\Controllers\Admin\DeviceManagementController::class, 'igReconnect'])->name('ig.reconnect');
            Route::post('/ig/{id}/disconnect', [App\Http\Controllers\Admin\DeviceManagementController::class, 'igDisconnect'])->name('ig.disconnect');
            Route::delete('/ig/{id}', [App\Http\Controllers\Admin\DeviceManagementController::class, 'igDestroy'])->name('ig.destroy');
        });
        
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
        
        // Email Logs
        Route::get('/email-logs', function () {
            return view('admin.email-logs.index');
        })->name('admin.email-logs.index');
        
        // API Usage
        Route::get('/api-usage', function () {
            return view('admin.api-usage.index');
        })->name('admin.api-usage.index');
        
        // Webhook Logs
        Route::prefix('webhook-logs')->name('admin.webhook-logs.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\WebhookLogController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\Admin\WebhookLogController::class, 'show'])->name('show');
            Route::post('/{id}/retry', [App\Http\Controllers\Admin\WebhookLogController::class, 'retry'])->name('retry');
            Route::post('/retry-all', [App\Http\Controllers\Admin\WebhookLogController::class, 'retryAll'])->name('retry-all');
        });
        
        // Feature Flags
        Route::prefix('feature-flags')->name('admin.feature-flags.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'index'])->name('index');
            Route::post('/update', [App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'update'])->name('update');
            Route::post('/{setting}/toggle', [App\Http\Controllers\Admin\AdminFeatureFlagController::class, 'toggle'])->name('toggle');
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
        
        // Advanced Analytics
        Route::get('/analytics', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'index'])->name('admin.analytics.index');
        Route::get('/analytics/realtime', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'realtime'])->name('admin.analytics.realtime');
        
        // Security Dashboard
        Route::prefix('security')->name('admin.security.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\SecurityDashboardController::class, 'index'])->name('index');
            Route::get('/logs', [App\Http\Controllers\Admin\SecurityDashboardController::class, 'activityLogs'])->name('logs');
            Route::get('/status', [App\Http\Controllers\Admin\SecurityDashboardController::class, 'securityStatus'])->name('status');
            Route::post('/resolve-alert/{alert}', [App\Http\Controllers\Admin\SecurityDashboardController::class, 'resolveAlert'])->name('resolve-alert');
            Route::post('/block-ip', [App\Http\Controllers\Admin\SecurityDashboardController::class, 'blockIp'])->name('block-ip');
            Route::post('/unblock-ip', [App\Http\Controllers\Admin\SecurityDashboardController::class, 'unblockIp'])->name('unblock-ip');
        });
        
        // Admin Management
        Route::prefix('admins')->name('admin.admins.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdminManagementController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\AdminManagementController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\AdminManagementController::class, 'store'])->name('store');
            Route::get('/{admin}/edit', [App\Http\Controllers\Admin\AdminManagementController::class, 'edit'])->name('edit');
            Route::put('/{admin}', [App\Http\Controllers\Admin\AdminManagementController::class, 'update'])->name('update');
            Route::delete('/{admin}', [App\Http\Controllers\Admin\AdminManagementController::class, 'destroy'])->name('destroy');
            Route::patch('/{admin}/toggle-status', [App\Http\Controllers\Admin\AdminManagementController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{admin}/reset-password', [App\Http\Controllers\Admin\AdminManagementController::class, 'resetPassword'])->name('reset-password');
            Route::get('/{admin}/activity', [App\Http\Controllers\Admin\AdminManagementController::class, 'activity'])->name('activity');
        });
        
        // Notifications
        Route::prefix('notifications')->name('admin.notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\NotificationController::class, 'showAll'])->name('index');
            Route::get('/ajax', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('ajax');
            Route::get('/unread-count', [App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/{notification}/read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::delete('/{notification}', [App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('destroy');
        });
        
        // Advanced User Analytics
        Route::prefix('user-analytics')->name('admin.user-analytics.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\AdvancedUserAnalyticsController::class, 'index'])->name('index');
            Route::get('/{userId}', [App\Http\Controllers\Admin\AdvancedUserAnalyticsController::class, 'userDetail'])->name('show');
            Route::get('/api/realtime', [App\Http\Controllers\Admin\AdvancedUserAnalyticsController::class, 'realtime'])->name('realtime');
        });
        
        // System Alerts
        Route::prefix('system-alerts')->name('admin.system-alerts.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\SystemAlertController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\SystemAlertController::class, 'store'])->name('store');
            Route::post('/{id}/toggle', [App\Http\Controllers\Admin\SystemAlertController::class, 'toggle'])->name('toggle');
            Route::delete('/{id}', [App\Http\Controllers\Admin\SystemAlertController::class, 'destroy'])->name('destroy');
            Route::post('/test', [App\Http\Controllers\Admin\SystemAlertController::class, 'test'])->name('test');
            Route::get('/api/metrics', [App\Http\Controllers\Admin\SystemAlertController::class, 'metrics'])->name('metrics');
        });
        
        // Maintenance Mode
        Route::prefix('maintenance-mode')->name('admin.maintenance-mode.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\MaintenanceModeController::class, 'index'])->name('index');
            Route::post('/enable', [App\Http\Controllers\Admin\MaintenanceModeController::class, 'enable'])->name('enable');
            Route::post('/disable', [App\Http\Controllers\Admin\MaintenanceModeController::class, 'disable'])->name('disable');
            Route::post('/whitelist', [App\Http\Controllers\Admin\MaintenanceModeController::class, 'whitelistIp'])->name('whitelist');
            Route::delete('/whitelist/{ip}', [App\Http\Controllers\Admin\MaintenanceModeController::class, 'removeWhitelistIp'])->name('remove-whitelist');
        });
        
        // Data Import/Export
        Route::prefix('data-transfer')->name('admin.data-transfer.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\DataTransferController::class, 'index'])->name('index');
            Route::post('/export', [App\Http\Controllers\Admin\DataTransferController::class, 'export'])->name('export');
            Route::post('/import', [App\Http\Controllers\Admin\DataTransferController::class, 'import'])->name('import');
            Route::get('/template/{type}', [App\Http\Controllers\Admin\DataTransferController::class, 'downloadTemplate'])->name('template');
            Route::post('/backup', [App\Http\Controllers\Admin\DataTransferController::class, 'backup'])->name('backup');
            Route::post('/restore', [App\Http\Controllers\Admin\DataTransferController::class, 'restore'])->name('restore');
            Route::get('/backup/download', [App\Http\Controllers\Admin\DataTransferController::class, 'downloadBackup'])->name('backup.download');
            Route::delete('/backup', [App\Http\Controllers\Admin\DataTransferController::class, 'deleteBackup'])->name('backup.delete');
        });
        
        // Database Query Runner (Readonly)
        Route::prefix('query-runner')->name('admin.query-runner.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\QueryRunnerController::class, 'index'])->name('index');
            Route::post('/execute', [App\Http\Controllers\Admin\QueryRunnerController::class, 'execute'])->name('execute');
            Route::get('/tables', [App\Http\Controllers\Admin\QueryRunnerController::class, 'getTables'])->name('tables');
            Route::get('/schema/{table}', [App\Http\Controllers\Admin\QueryRunnerController::class, 'getTableSchema'])->name('schema');
        });
        
        // Message Templates Management
        Route::prefix('templates')->name('admin.templates.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\MessageTemplateController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\MessageTemplateController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\MessageTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [App\Http\Controllers\Admin\MessageTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [App\Http\Controllers\Admin\MessageTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [App\Http\Controllers\Admin\MessageTemplateController::class, 'destroy'])->name('destroy');
        });
    });
    
});
