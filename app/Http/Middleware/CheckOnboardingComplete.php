<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingComplete
{
    /**
     * Routes yang dikecualikan dari pengecekan onboarding
     */
    protected $except = [
        'onboarding',
        'onboarding/*',
        'logout',
        'login',
        'register',
        'verify-email',
        'verify-email/*',
        'email/verify',
        'email/verify/*',
        'email/verification-notification',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip jika user belum login
        if (!Auth::check()) {
            return $next($request);
        }

        // Skip jika route dikecualikan
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        $user = Auth::user();

        // Redirect ke onboarding jika belum selesai
        if (!$user->onboarding_completed_at && $user->email_verified_at) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
