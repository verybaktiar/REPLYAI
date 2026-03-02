<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memeriksa Maintenance Mode
 * 
 * - Allow superadmin dan whitelisted IPs
 * - Show maintenance page to others
 */
class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        $maintenanceEnabled = (bool) SystemSetting::get('maintenance_mode_enabled', false);

        if (!$maintenanceEnabled) {
            return $next($request);
        }

        // Always allow admin routes
        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        // Check if user is logged in as admin
        if (auth('admin')->check()) {
            return $next($request);
        }

        // Check if current IP is whitelisted
        $clientIp = $request->ip();
        $allowedIps = json_decode(SystemSetting::get('maintenance_allowed_ips', '[]'), true) ?: [];
        
        foreach ($allowedIps as $item) {
            $whitelistedIp = is_array($item) ? $item['ip'] : $item;
            if ($whitelistedIp === $clientIp) {
                return $next($request);
            }
        }

        // Get maintenance settings
        $message = SystemSetting::get('maintenance_mode_message', 'We are currently performing maintenance. Please check back soon.');
        $countdownEnabled = (bool) SystemSetting::get('maintenance_countdown_enabled', false);
        $countdownEnd = SystemSetting::get('maintenance_countdown_end', null);

        // Return maintenance page
        return response()->view('errors.maintenance', [
            'message' => $message,
            'countdownEnabled' => $countdownEnabled,
            'countdownEnd' => $countdownEnd,
        ], 503);
    }

    /**
     * Check if IP matches (supports wildcards)
     */
    private function ipMatches(string $clientIp, string $allowedIp): bool
    {
        // Direct match
        if ($clientIp === $allowedIp) {
            return true;
        }

        // Wildcard match (e.g., 192.168.1.*)
        if (str_contains($allowedIp, '*')) {
            $pattern = str_replace('.', '\.', $allowedIp);
            $pattern = str_replace('*', '.*', $pattern);
            return (bool) preg_match("/^$pattern$/", $clientIp);
        }

        // CIDR match (e.g., 192.168.1.0/24)
        if (str_contains($allowedIp, '/')) {
            return $this->cidrMatch($clientIp, $allowedIp);
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function cidrMatch(string $ip, string $range): bool
    {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) === $subnet;
    }
}
