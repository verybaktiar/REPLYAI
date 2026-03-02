<?php

namespace App\Services\Security;

/**
 * Class SsrfProtectionService
 * 
 * Service untuk mencegah Server-Side Request Forgery (SSRF) attacks.
 * Memvalidasi URL sebelum di-fetch untuk mencegah akses ke internal resources.
 *
 * @package App\Services\Security
 */
class SsrfProtectionService
{
    /**
     * Private IP ranges yang harus diblokir
     *
     * @var array<string>
     */
    private const BLOCKED_RANGES = [
        '127.0.0.0/8',      // Loopback
        '10.0.0.0/8',       // Private Class A
        '172.16.0.0/12',    // Private Class B
        '192.168.0.0/16',   // Private Class C
        '169.254.0.0/16',   // Link-local (AWS metadata)
        '0.0.0.0/8',        // Current network
        '::1/128',          // IPv6 loopback
        'fc00::/7',         // IPv6 unique local
        'fe80::/10',        // IPv6 link-local
    ];

    /**
     * Hostnames yang harus diblokir
     *
     * @var array<string>
     */
    private const BLOCKED_HOSTS = [
        'localhost',
        'localhost.localdomain',
        'ip6-localhost',
        'ip6-loopback',
    ];

    /**
     * URL schemes yang diizinkan
     *
     * @var array<string>
     */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * Cek apakah URL aman untuk di-fetch
     *
     * @param string $url
     * @return bool
     */
    public static function isUrlSafe(string $url): bool
    {
        // Parse URL
        $parsed = parse_url($url);
        
        if ($parsed === false) {
            return false;
        }

        // Cek scheme
        $scheme = strtolower($parsed['scheme'] ?? '');
        if (!in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            return false;
        }

        // Ambil host
        $host = strtolower($parsed['host'] ?? '');
        if (empty($host)) {
            return false;
        }

        // Cek blocked hosts
        if (in_array($host, self::BLOCKED_HOSTS, true)) {
            return false;
        }

        // Resolve IP address
        $ip = gethostbyname($host);
        
        // Jika host sama dengan IP (sudah dalam bentuk IP)
        if ($ip === $host) {
            if (self::isIpBlocked($ip)) {
                return false;
            }
        }

        // Cek jika IP yang di-resolve adalah private
        if (self::isIpBlocked($ip)) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah IP address termasuk dalam blocked ranges
     *
     * @param string $ip
     * @return bool
     */
    private static function isIpBlocked(string $ip): bool
    {
        foreach (self::BLOCKED_RANGES as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cek apakah IP berada dalam range CIDR
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private static function ipInRange(string $ip, string $range): bool
    {
        // Handle IPv6
        if (strpos($ip, ':') !== false || strpos($range, ':') !== false) {
            return self::ipv6InRange($ip, $range);
        }

        // IPv4
        list($range, $netmask) = explode('/', $range, 2);
        
        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;
        
        return (($ipDecimal & $netmaskDecimal) === ($rangeDecimal & $netmaskDecimal));
    }

    /**
     * Cek apakah IPv6 berada dalam range
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private static function ipv6InRange(string $ip, string $range): bool
    {
        // Simplified IPv6 check
        if ($range === '::1/128') {
            return $ip === '::1' || $ip === '0:0:0:0:0:0:0:1';
        }
        
        // For now, block all IPv6 private ranges
        $firstHex = substr($ip, 0, 2);
        if (in_array($firstHex, ['fc', 'fd', 'fe', 'ff'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Sanitize dan validasi URL
     *
     * @param string $url
     * @return string|null Returns null jika URL tidak aman
     */
    public static function sanitizeUrl(string $url): ?string
    {
        if (!self::isUrlSafe($url)) {
            return null;
        }

        // Additional sanitization
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        // Remove credentials from URL
        $parsed = parse_url($url);
        if (isset($parsed['user']) || isset($parsed['pass'])) {
            unset($parsed['user'], $parsed['pass']);
            $url = self::buildUrl($parsed);
        }

        return $url;
    }

    /**
     * Build URL dari parse_url array
     *
     * @param array $parts
     * @return string
     */
    private static function buildUrl(array $parts): string
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return "$scheme$host$port$path$query$fragment";
    }
}
