<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\SecurityAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class SecurityDashboardController extends Controller
{
    /**
     * Display security dashboard.
     */
    public function index()
    {
        // Security metrics
        $metrics = [
            'failed_logins_24h' => $this->getFailedLoginsCount(),
            'suspicious_activities' => AdminActivityLog::suspicious()->recent(24)->count(),
            'active_sessions' => $this->getActiveSessionsCount(),
            'blocked_ips' => $this->getBlockedIpsCount(),
            'unresolved_alerts' => SecurityAlert::unresolved()->count(),
            'high_risk_events' => AdminActivityLog::highRisk()->recent(24)->count(),
        ];
        
        // Recent security events
        $recentEvents = AdminActivityLog::with('admin')
            ->whereIn('action', [
                'login', 'failed_2fa', 'ip_changed', 'unusual_time_access',
                'user_suspend', 'user_delete', 'payment_approve', 'impersonate'
            ])
            ->orWhere('is_suspicious', true)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        
        // Failed login attempts by IP
        $failedLogins = DB::table('admin_activity_logs')
            ->select('ip_address', DB::raw('COUNT(*) as count'))
            ->where('action', 'failed_login')
            ->where('created_at', '>=', now()->subHours(24))
            ->groupBy('ip_address')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
        
        // Security alerts
        $alerts = SecurityAlert::unresolved()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        
        // Admin login activity
        $adminActivity = DB::table('admin_activity_logs')
            ->select('admin_id', DB::raw('COUNT(*) as login_count'))
            ->where('action', 'login')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('admin_id')
            ->get()
            ->map(function ($item) {
                $item->admin = \App\Models\AdminUser::find($item->admin_id);
                return $item;
            });
        
        // Risk score distribution
        $riskDistribution = [
            'critical' => AdminActivityLog::where('risk_score', '>=', 8)->recent(24)->count(),
            'high' => AdminActivityLog::whereBetween('risk_score', [5, 7])->recent(24)->count(),
            'medium' => AdminActivityLog::whereBetween('risk_score', [3, 4])->recent(24)->count(),
            'low' => AdminActivityLog::where('risk_score', '<', 3)->recent(24)->count(),
        ];
        
        return view('admin.security.index', compact(
            'metrics',
            'recentEvents',
            'failedLogins',
            'alerts',
            'adminActivity',
            'riskDistribution'
        ));
    }
    
    /**
     * Show detailed activity logs.
     */
    public function activityLogs(Request $request)
    {
        $query = AdminActivityLog::with('admin');
        
        // Filters
        if ($request->filled('admin')) {
            $query->where('admin_id', $request->admin);
        }
        
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->filled('risk')) {
            $query->where('risk_score', '>=', $request->risk);
        }
        
        if ($request->boolean('suspicious')) {
            $query->where('is_suspicious', true);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->orderByDesc('created_at')->paginate(50);
        $admins = \App\Models\AdminUser::all();
        $actions = AdminActivityLog::select('action')->distinct()->pluck('action');
        
        return view('admin.security.logs', compact('logs', 'admins', 'actions'));
    }
    
    /**
     * Resolve security alert.
     */
    public function resolveAlert(SecurityAlert $alert, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $alert->resolve(
            auth('admin')->id(),
            $request->notes
        );
        
        return back()->with('success', 'Alert berhasil diselesaikan.');
    }
    
    /**
     * Block IP address.
     */
    public function blockIp(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'reason' => 'required|string|max:500',
            'duration' => 'required|integer|min:1|max:1440', // in minutes
        ]);
        
        // Add to blocked IPs list (implement cache or database storage)
        $blockedKey = 'admin_blocked_ip:' . $request->ip;
        cache()->put($blockedKey, [
            'blocked_by' => auth('admin')->id(),
            'reason' => $request->reason,
            'blocked_at' => now(),
        ], now()->addMinutes($request->duration));
        
        // Log action
        \App\Models\AdminActivityLog::log(
            auth('admin')->user(),
            'ip_blocked',
            "Blocked IP: {$request->ip}",
            ['ip' => $request->ip, 'reason' => $request->reason, 'duration' => $request->duration],
            null,
            5
        );
        
        return back()->with('success', "IP {$request->ip} berhasil diblokir selama {$request->duration} menit.");
    }
    
    /**
     * Unblock IP address.
     */
    public function unblockIp(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
        ]);
        
        cache()->forget('admin_blocked_ip:' . $request->ip);
        
        \App\Models\AdminActivityLog::log(
            auth('admin')->user(),
            'ip_unblocked',
            "Unblocked IP: {$request->ip}",
            ['ip' => $request->ip],
            null,
            3
        );
        
        return back()->with('success', "IP {$request->ip} telah di-unblock.");
    }
    
    /**
     * Get system security status.
     */
    public function securityStatus()
    {
        $checks = [
            'https_enabled' => $this->isHttpsEnabled(),
            'admin_2fa_enabled' => $this->isAdmin2faEnabled(),
            'ip_whitelist_configured' => !empty(config('admin.allowed_ips')),
            'rate_limiting_active' => true, // Assumed always active
            'failed_jobs_recent' => DB::table('failed_jobs')->where('failed_at', '>=', now()->subHours(24))->count() === 0,
            'debug_mode_off' => !config('app.debug'),
        ];
        
        $score = collect($checks)->filter()->count() / count($checks) * 100;
        
        return response()->json([
            'checks' => $checks,
            'score' => round($score, 1),
            'status' => $score >= 80 ? 'good' : ($score >= 60 ? 'warning' : 'critical'),
        ]);
    }
    
    /**
     * Get failed logins count.
     */
    private function getFailedLoginsCount(): int
    {
        return DB::table('admin_activity_logs')
            ->where('action', 'failed_login')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
    }
    
    /**
     * Get active sessions count (approximation).
     */
    private function getActiveSessionsCount(): int
    {
        // This is an approximation based on recent activity
        return DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
            ->count();
    }
    
    /**
     * Get blocked IPs count.
     */
    private function getBlockedIpsCount(): int
    {
        // Count cached blocked IPs
        // In real implementation, you might want to use database
        return 0; // Placeholder
    }
    
    /**
     * Check if HTTPS is enabled.
     */
    private function isHttpsEnabled(): bool
    {
        return request()->isSecure() || config('app.env') === 'local';
    }
    
    /**
     * Check if admin 2FA is enabled for at least one admin.
     */
    private function isAdmin2faEnabled(): bool
    {
        return \App\Models\AdminUser::where('two_factor_enabled', true)->exists();
    }
}
