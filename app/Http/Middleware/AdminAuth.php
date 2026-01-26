<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
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

        return $next($request);
    }
}
