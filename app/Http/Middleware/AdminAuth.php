<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Session lifetime in minutes (15 minutes)
     */
    const SESSION_LIFETIME = 15;
    
    /**
     * Handle an incoming request.
     * Middleware untuk memastikan user yang mengakses adalah admin yang sudah login
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user login dengan guard admin
        if (!Auth::guard('admin')->check()) {
            // Jika request dari API/AJAX, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.'
                ], 401);
            }

            // Redirect ke halaman login admin
            return redirect()->route('admin.login')
                ->with('error', 'Silakan login sebagai admin terlebih dahulu.');
        }
        
        $admin = Auth::guard('admin')->user();
        
        // Cek apakah admin masih aktif
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            
            return redirect()->route('admin.login')
                ->with('error', 'Akun Anda telah dinonaktifkan.');
        }
        
        // Session Security: Regenerate session setiap 15 menit
        $sessionKey = 'admin_session_started_at';
        $lastActivity = session($sessionKey);
        
        if (!$lastActivity) {
            // First activity in this session
            session([$sessionKey => now()->timestamp]);
            session()->regenerate();
        } else {
            $elapsed = now()->timestamp - $lastActivity;
            
            // Regenerate session token every 15 minutes
            if ($elapsed > (self::SESSION_LIFETIME * 60)) {
                session()->regenerate();
                session([$sessionKey => now()->timestamp]);
            }
            
            // Check for session timeout (60 minutes of inactivity)
            $lastActivityKey = 'admin_last_activity_at';
            $lastActivityTime = session($lastActivityKey);
            
            if ($lastActivityTime && (now()->timestamp - $lastActivityTime) > (60 * 60)) {
                // Session timeout due to inactivity
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                
                \App\Models\AdminActivityLog::log(
                    $admin,
                    'session_timeout',
                    'Session expired due to inactivity'
                );
                
                return redirect()->route('admin.login')
                    ->with('error', 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.');
            }
            
            // Update last activity
            session([$lastActivityKey => now()->timestamp]);
        }
        
        // Check 2FA if enabled
        if ($admin->two_factor_enabled && !session('admin_2fa_verified')) {
            // Allow access to 2FA verification routes
            if (!$request->routeIs('admin.2fa*') && !$request->routeIs('admin.logout')) {
                return redirect()->route('admin.2fa');
            }
        }
        
        // Log suspicious activity detection
        $this->detectSuspiciousActivity($request, $admin);

        return $next($request);
    }
    
    /**
     * Detect and log suspicious admin activity.
     */
    private function detectSuspiciousActivity(Request $request, $admin): void
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        // Check for unusual time (between 00:00 - 05:00)
        $hour = now()->hour;
        $isUnusualTime = $hour >= 0 && $hour < 5;
        
        // Check if IP changed during session
        $sessionIp = session('admin_login_ip');
        if ($sessionIp && $sessionIp !== $ip) {
            // IP changed - could be suspicious
            \App\Models\AdminActivityLog::log(
                $admin,
                'ip_changed',
                'IP address changed during session',
                [
                    'old_ip' => $sessionIp,
                    'new_ip' => $ip,
                    'user_agent' => $userAgent,
                ],
                null,
                5 // risk score
            );
        }
        
        // Log unusual time access
        if ($isUnusualTime && !session('admin_unusual_time_alerted')) {
            \App\Models\AdminActivityLog::log(
                $admin,
                'unusual_time_access',
                'Admin access during unusual hours',
                [
                    'hour' => $hour,
                    'ip' => $ip,
                ],
                null,
                3 // risk score
            );
            session(['admin_unusual_time_alerted' => true]);
        }
    }
}
