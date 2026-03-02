<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // If user is not logged in, let them through (auth middleware should handle this)
        if (!$user) {
            return $next($request);
        }
        
        // If 2FA is not enabled, allow access
        if (!$user->two_factor_enabled) {
            return $next($request);
        }
        
        // If 2FA is verified, allow access
        if (session('2fa_verified')) {
            // Check if verification is still valid (e.g., within session lifetime)
            $verifiedAt = session('2fa_verified_at');
            if ($verifiedAt && (now()->timestamp - $verifiedAt) < (60 * 60 * 24)) { // 24 hours
                return $next($request);
            }
            // Expired, clear and require re-verification
            session()->forget(['2fa_verified', '2fa_verified_at']);
        }
        
        // Allow access to 2FA routes
        if ($request->routeIs('2fa*') || $request->routeIs('logout')) {
            return $next($request);
        }
        
        // Redirect to 2FA verification
        return redirect()->route('2fa');
    }
}
