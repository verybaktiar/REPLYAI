<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminIpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('admin.allowed_ips', []);
        
        // Jika tidak ada whitelist yang dikonfigurasi, izinkan semua
        if (empty($allowedIps)) {
            return $next($request);
        }
        
        $clientIp = $request->ip();
        $allowed = false;
        
        foreach ($allowedIps as $ip) {
            if (str_contains($ip, '/')) {
                // CIDR notation check
                if ($this->ipInRange($clientIp, $ip)) {
                    $allowed = true;
                    break;
                }
            } elseif ($clientIp === $ip) {
                $allowed = true;
                break;
            }
        }
        
        if (!$allowed) {
            Log::warning('Admin access denied from unauthorized IP', [
                'ip' => $clientIp,
                'email' => $request->input('email'),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
            
            // Kirim alert ke admin jika ada upaya akses dari IP tidak dikenal
            $this->sendSecurityAlert($clientIp, $request);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. IP Anda tidak terdaftar dalam whitelist.'
                ], 403);
            }
            
            abort(403, 'Akses ditolak dari IP ini. Silakan hubungi administrator.');
        }
        
        return $next($request);
    }
    
    /**
     * Check if IP is in CIDR range.
     */
    private function ipInRange(string $ip, string $range): bool
    {
        list($range, $netmask) = explode('/', $range, 2);
        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;
        
        return (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
    }
    
    /**
     * Send security alert for unauthorized access attempt.
     */
    private function sendSecurityAlert(string $ip, Request $request): void
    {
        // Catat dalam database untuk review
        \App\Models\SecurityAlert::create([
            'type' => 'unauthorized_admin_access',
            'ip_address' => $ip,
            'email_attempted' => $request->input('email'),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'country' => $this->getCountryFromIp($ip),
        ]);
    }
    
    /**
     * Get country from IP (simplified - in production use GeoIP library).
     */
    private function getCountryFromIp(string $ip): ?string
    {
        // Placeholder - implement GeoIP lookup if needed
        return null;
    }
}
