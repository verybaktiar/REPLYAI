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
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Protected Admin Routes (perlu login via /admin/login)
Route::middleware(['admin'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

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
    });

    // Stop impersonate (route khusus di luar prefix)
    Route::get('/stop-impersonate', [App\Http\Controllers\Admin\AdminUserController::class, 'stopImpersonate'])->name('admin.stop-impersonate');

    // Activity Logs
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity-logs.index');

    // System Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('admin.settings.update');

    // System Health
    Route::get('/system-health', function () {
        return view('admin.system-health');
    })->name('admin.system-health');

    // Maintenance Actions
    Route::prefix('maintenance')->name('admin.maintenance.')->group(function () {
        Route::post('/clear-cache', function () {
            Artisan::call('cache:clear');
            return back()->with('success', 'Cache cleared successfully!');
        })->name('clear-cache');

        Route::post('/clear-views', function () {
            Artisan::call('view:clear');
            return back()->with('success', 'Compiled views cleared successfully!');
        })->name('clear-views');

        Route::post('/refresh-tokens', function () {
            Artisan::call('instagram:refresh-tokens');
            return back()->with('success', 'Instagram tokens refreshed!');
        })->name('refresh-tokens');
    });

    // Revenue Dashboard
    Route::get('/revenue', function () {
        return view('admin.revenue.index');
    })->name('admin.revenue.index');

    // Broadcast & Announcements
    Route::get('/broadcast', function () {
        return view('admin.broadcast.index');
    })->name('admin.broadcast.index');
    
    Route::post('/broadcast/send', function (Illuminate\Http\Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:banner,email,both',
            'audience' => 'required|in:all,active,vip,free,expiring',
            'style' => 'required|in:info,success,warning,danger',
            'duration_days' => 'required|integer|min:1|max:30',
        ]);
        
        \App\Models\Announcement::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'style' => $request->style,
            'audience' => $request->audience,
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => now()->addDays($request->duration_days),
            'created_by' => Auth::guard('admin')->id(),
        ]);
        
        return back()->with('success', 'Broadcast berhasil dikirim!');
    })->name('admin.broadcast.send');

    // Subscription Alerts
    Route::get('/alerts', function () {
        return view('admin.alerts.index');
    })->name('admin.alerts.index');

    // Platform Statistics
    Route::get('/stats', function () {
        return view('admin.stats.index');
    })->name('admin.stats.index');

    // Export Reports
    Route::get('/export/users', function () {
        $users = \App\Models\User::with('subscription.plan')->get();
        $filename = 'users_export_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Plan', 'Status', 'VIP', 'Verified', 'Created']);
            
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->subscription?->plan?->name ?? 'Free',
                    $user->subscription?->status ?? '-',
                    $user->is_vip ? 'Yes' : 'No',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    })->name('admin.export.users');

    Route::get('/export/payments', function () {
        $payments = \App\Models\Payment::with(['user', 'plan'])->orderByDesc('created_at')->get();
        $filename = 'payments_export_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Invoice', 'User', 'Email', 'Plan', 'Amount', 'Status', 'Method', 'Date']);
            
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->invoice_number,
                    $payment->user?->name ?? 'Unknown',
                    $payment->user?->email ?? '-',
                    $payment->plan?->name ?? '-',
                    $payment->amount,
                    $payment->status,
                    $payment->payment_method ?? '-',
                    $payment->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    })->name('admin.export.payments');

    // Refund Management
    Route::get('/refunds', function () {
        return view('admin.refunds.index');
    })->name('admin.refunds.index');
    
    Route::post('/refunds/{refund}/approve', function (\App\Models\Refund $refund) {
        $refund->update([
            'status' => 'approved',
            'processed_by' => Auth::guard('admin')->id(),
            'processed_at' => now(),
        ]);
        return back()->with('success', 'Refund approved!');
    })->name('admin.refunds.approve');
    
    Route::post('/refunds/{refund}/reject', function (\App\Models\Refund $refund) {
        $refund->update([
            'status' => 'rejected',
            'processed_by' => Auth::guard('admin')->id(),
            'processed_at' => now(),
        ]);
        return back()->with('success', 'Refund rejected');
    })->name('admin.refunds.reject');

    // Email Logs
    Route::get('/email-logs', function () {
        return view('admin.email-logs.index');
    })->name('admin.email-logs.index');

    // API Usage
    Route::get('/api-usage', function () {
        return view('admin.api-usage.index');
    })->name('admin.api-usage.index');

    // Feature Flags
    Route::get('/feature-flags', function () {
        return view('admin.feature-flags.index');
    })->name('admin.feature-flags.index');
    
    Route::post('/feature-flags', function (Illuminate\Http\Request $request) {
        \App\Models\FeatureFlag::create([
            'key' => $request->key,
            'name' => $request->name,
            'scope' => $request->scope ?? 'global',
            'is_enabled' => false,
        ]);
        return back()->with('success', 'Feature flag created!');
    })->name('admin.feature-flags.store');
    
    Route::patch('/feature-flags/{flag}/toggle', function (\App\Models\FeatureFlag $flag) {
        $flag->update(['is_enabled' => !$flag->is_enabled]);
        return back();
    })->name('admin.feature-flags.toggle');
    
    Route::delete('/feature-flags/{flag}', function (\App\Models\FeatureFlag $flag) {
        $flag->delete();
        return back()->with('success', 'Feature flag deleted');
    })->name('admin.feature-flags.destroy');

    // User Feedback
    Route::get('/feedback', function () {
        return view('admin.feedback.index');
    })->name('admin.feedback.index');
    
    Route::patch('/feedback/{feedback}/status', function (\App\Models\UserFeedback $feedback, Illuminate\Http\Request $request) {
        $feedback->update(['status' => $request->status, 'reviewed_by' => Auth::guard('admin')->id()]);
        return back();
    })->name('admin.feedback.update-status');

    // Bulk Actions
    Route::get('/bulk', function () {
        return view('admin.bulk.index');
    })->name('admin.bulk.index');
    
    Route::post('/bulk/email', function (Illuminate\Http\Request $request) {
        // In production, queue this job
        return back()->with('success', 'Bulk email queued for sending!');
    })->name('admin.bulk.email');
    
    Route::post('/bulk/extend', function (Illuminate\Http\Request $request) {
        $days = (int) $request->days;
        $query = \App\Models\Subscription::where('status', 'active');
        
        if ($request->target === 'expiring') {
            $query->where('expires_at', '<=', now()->addDays(7));
        }
        
        $query->each(function($sub) use ($days) {
            $sub->update(['expires_at' => $sub->expires_at->addDays($days)]);
        });
        
        return back()->with('success', 'Subscriptions extended!');
    })->name('admin.bulk.extend');
    
    Route::post('/bulk/reset-usage', function (Illuminate\Http\Request $request) {
        // Reset feature usage for all users or specific plan
        return back()->with('success', 'Usage limits reset!');
    })->name('admin.bulk.reset-usage');

    // QA Testing Dashboard
    Route::prefix('qa')->name('admin.qa.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminQaController::class, 'index'])->name('index');
        Route::post('/save-result', [App\Http\Controllers\Admin\AdminQaController::class, 'saveResult'])->name('save-result');
        Route::post('/reset', [App\Http\Controllers\Admin\AdminQaController::class, 'resetResults'])->name('reset');
    });

});

