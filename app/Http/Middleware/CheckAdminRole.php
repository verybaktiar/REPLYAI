<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: CheckAdminRole
 * 
 * Memastikan admin yang mengakses memiliki role yang diizinkan.
 * Digunakan untuk membatasi akses ke fitur berdasarkan role:
 * - superadmin: Akses penuh
 * - finance: Manage payments, refunds, revenue
 * - support: View users, tickets, tidak bisa modify critical data
 * 
 * Usage:
 * Route::middleware(['admin', 'admin.role:superadmin']) // Superadmin only
 * Route::middleware(['admin', 'admin.role:superadmin,finance']) // Multiple roles
 */
class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Role yang diizinkan (superadmin, finance, support)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = Auth::guard('admin')->user();
        
        // Pastikan admin sudah login (seharusnya sudah dihandle middleware 'admin')
        if (!$admin) {
            return $this->unauthorized($request, 'Admin authentication required.');
        }
        
        // Jika tidak ada role yang dispesifikasikan, izinkan semua admin
        if (empty($roles)) {
            return $next($request);
        }
        
        // Cek apakah admin memiliki salah satu role yang diizinkan
        if (!in_array($admin->role, $roles, true)) {
            // Log unauthorized access attempt untuk security audit
            \App\Models\AdminActivityLog::log(
                $admin,
                'unauthorized_access_attempt',
                "Attempted to access restricted route. Required roles: " . implode(', ', $roles),
                [
                    'required_roles' => $roles,
                    'admin_role' => $admin->role,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ],
                null,
                7 // High risk score
            );
            
            return $this->unauthorized($request, 'You do not have permission to access this resource.');
        }
        
        return $next($request);
    }
    
    /**
     * Return unauthorized response.
     */
    private function unauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'code' => 'INSUFFICIENT_PRIVILEGES'
            ], 403);
        }
        
        return redirect()->route('admin.dashboard')
            ->with('error', $message);
    }
}
