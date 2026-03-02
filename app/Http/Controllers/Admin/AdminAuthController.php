<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminActivityLog;
use App\Models\SecurityAlert;

class AdminAuthController extends Controller
{
    /**
     * Tampilkan form login admin
     */
    public function showLoginForm()
    {
        // Jika sudah login, redirect ke dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Process login admin
     */
    public function login(AdminLoginRequest $request)
    {
        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $remember)) {
            $request->session()->regenerate();
            
            $admin = Auth::guard('admin')->user();
            
            // Check if account is active
            if (!$admin->is_active) {
                Auth::guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan.',
                ]);
            }
            
            // Record login details
            $admin->recordLogin($request->ip());
            
            // Store session info for security tracking
            session([
                'admin_login_ip' => $request->ip(),
                'admin_session_started_at' => now()->timestamp,
                'admin_last_activity_at' => now()->timestamp,
            ]);

            // Log aktivitas login
            AdminActivityLog::log(
                $admin,
                'login',
                'Admin login ke sistem'
            );

            return redirect()->intended(route('admin.dashboard'));
        }
        
        // Log failed login attempt
        $this->logFailedLogin($request);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }
    
    /**
     * Log failed login attempt.
     */
    private function logFailedLogin(Request $request): void
    {
        $ip = $request->ip();
        $email = $request->input('email');
        
        // Check if this is a suspicious number of failed attempts
        $recentFailures = AdminActivityLog::where('action', 'failed_login')
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHours(1))
            ->count();
        
        if ($recentFailures >= 5) {
            // Create security alert
            SecurityAlert::create([
                'type' => 'brute_force_attempt',
                'ip_address' => $ip,
                'email_attempted' => $email,
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
        }
        
        // Log the failed attempt
        AdminActivityLog::create([
            'admin_id' => null, // No admin identified
            'action' => 'failed_login',
            'description' => "Failed login attempt: {$email}",
            'details' => ['email' => $email, 'attempt' => $recentFailures + 1],
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'is_suspicious' => $recentFailures >= 3,
            'risk_score' => min($recentFailures, 10),
            'created_at' => now(),
        ]);
    }

    /**
     * Logout admin
     */
    public function logout(Request $request)
    {
        // Log aktivitas logout
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'logout',
            'Admin logout dari sistem'
        );

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
