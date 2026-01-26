<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspendedAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_suspended) {
            // Jika ini request AJAX/JSON (misal inbox polling)
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Your account has been suspended. Please contact support.',
                ], 403);
            }

            // Simpan email agar bisa ditampilkan di halaman suspended
            $email = Auth::user()->email;
            
            // Logout user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('suspended')->with([
                'error' => 'Akun Anda telah ditangguhkan. Silakan hubungi dukungan pelanggan.',
                'suspended_email' => $email
            ]);
        }

        return $next($request);
    }
}
