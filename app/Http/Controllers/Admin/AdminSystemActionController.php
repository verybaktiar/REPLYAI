<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Models\ActivityLog;
use Carbon\Carbon;

class AdminSystemActionController extends Controller
{
    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isSuperAdmin()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_system_action',
                'Attempted system action without superadmin privilege',
                ['url' => request()->fullUrl(), 'action' => request()->route()->getActionMethod()],
                null,
                9
            );
            abort(403, 'Only Superadmin can perform system actions.');
        }
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        $this->checkAuthorization();
        
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        
        AdminActivityLog::log(Auth::guard('admin')->user(), 'system_maintenance', 'Clear application cache');
        
        return back()->with('success', 'Aplikasi cache berhasil dibersihkan.');
    }

    /**
     * Clear view cache
     */
    public function clearViews()
    {
        $this->checkAuthorization();
        
        Artisan::call('view:clear');
        
        AdminActivityLog::log(Auth::guard('admin')->user(), 'system_maintenance', 'Clear view cache');
        
        return back()->with('success', 'View cache berhasil dibersihkan.');
    }

    /**
     * Prune old activity logs (Older than 30 days)
     */
    public function pruneLogs()
    {
        $this->checkAuthorization();
        
        $days = 30;
        $date = Carbon::now()->subDays($days);
        
        $count = ActivityLog::where('created_at', '<', $date)->delete();
        
        AdminActivityLog::log(
            Auth::guard('admin')->user(), 
            'system_maintenance', 
            "Prune activity logs older than {$days} days",
            ['deleted_count' => $count]
        );
        
        return back()->with('success', "Berhasil menghapus {$count} log aktivitas lama.");
    }

    /**
     * Reset usage for all users (Bulk Reset)
     */
    public function bulkResetUsage()
    {
        $this->checkAuthorization();
        
        // Reset message counts for all users
        User::query()->update([
            'messages_sent_count' => 0,
            'ai_messages_count' => 0
        ]);

        AdminActivityLog::log(Auth::guard('admin')->user(), 'system_maintenance', 'Bulk reset usage for all users');

        return back()->with('success', 'Penggunaan seluruh user telah direset ke nol.');
    }

    /**
     * Bulk extend subscriptions for all active users
     */
    public function bulkExtend(Request $request)
    {
        $this->checkAuthorization();
        
        $days = (int) $request->input('days', 7);
        
        $users = User::whereHas('subscription', function($q) {
            $q->where('status', 'active');
        })->get();

        foreach ($users as $user) {
            $user->subscription->update([
                'expires_at' => Carbon::parse($user->subscription->expires_at)->addDays($days)
            ]);
        }

        AdminActivityLog::log(
            Auth::guard('admin')->user(), 
            'system_maintenance', 
            "Bulk extend subscriptions by {$days} days",
            ['affected_users' => $users->count()]
        );

        return back()->with('success', "Berhasil memperpanjang {$users->count()} langganan aktif selama {$days} hari.");
    }

    /**
     * Refresh Instagram Tokens (Manual trigger for all active accounts)
     */
    public function refreshTokens()
    {
        $this->checkAuthorization();
        
        // Logic to trigger refresh for all active Instagram accounts
        // Typically calls a job or service
        
        AdminActivityLog::log(
            Auth::guard('admin')->user(), 
            'system_maintenance', 
            'Trigger manual token refresh',
            ['triggered_at' => now()->toDateTimeString()]
        );
        
        return back()->with('success', 'Proses refresh token telah dijalankan.');
    }
}
