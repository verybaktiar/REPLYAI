<?php

use App\Services\Security\SsrfProtectionService;

/**
 * Test SSRF Protection - URL Validation
 * Security: CRITICAL-005
 */

describe('SSRF Protection', function () {
    
    test('private ip ranges are blocked', function () {
        $blockedUrls = [
            'http://127.0.0.1/admin',
            'http://192.168.1.1/config',
            'http://10.0.0.1/api',
            'http://172.16.0.1/data',
            'http://169.254.169.254/latest/meta-data/', // AWS metadata
        ];
        
        foreach ($blockedUrls as $url) {
            expect(SsrfProtectionService::isUrlSafe($url))->toBeFalse(
                "URL should be blocked: $url"
            );
        }
    });

    test('localhost is blocked', function () {
        expect(SsrfProtectionService::isUrlSafe('http://localhost:3306'))->toBeFalse();
        expect(SsrfProtectionService::isUrlSafe('http://localhost/api'))->toBeFalse();
    });

    test('public urls are allowed', function () {
        $allowedUrls = [
            'https://example.com',
            'https://google.com',
            'https://wikipedia.org/wiki/Test',
        ];
        
        foreach ($allowedUrls as $url) {
            expect(SsrfProtectionService::isUrlSafe($url))->toBeTrue(
                "URL should be allowed: $url"
            );
        }
    });

    test('invalid urls are rejected', function () {
        expect(SsrfProtectionService::isUrlSafe('not-a-url'))->toBeFalse();
        expect(SsrfProtectionService::isUrlSafe(''))->toBeFalse();
        expect(SsrfProtectionService::isUrlSafe('ftp://example.com'))->toBeFalse();
    });
});
