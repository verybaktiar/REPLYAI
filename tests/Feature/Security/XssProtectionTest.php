<?php

use App\Helpers\SecurityHelper;

/**
 * Test XSS Protection - Output Sanitization
 * Security: CRITICAL-004
 */

describe('XSS Protection - SecurityHelper', function () {
    
    test('sanitizeHtml removes script tags', function () {
        $input = '<script>alert("xss")</script>Hello';
        $output = SecurityHelper::sanitizeHtml($input);
        
        expect($output)->not->toContain('<script');
        expect($output)->not->toContain('alert');
    });

    test('sanitizeHtml removes event handlers', function () {
        $input = '<img src="x" onerror="alert(\'xss\')">';
        $output = SecurityHelper::sanitizeHtml($input);
        
        expect($output)->not->toContain('onerror');
    });

    test('sanitizeHtml escapes html entities', function () {
        $input = '<div>Hello</div>';
        $output = SecurityHelper::sanitizeHtml($input);
        
        expect($output)->toContain('&lt;div&gt;');
    });

    test('sanitizeText escapes all html', function () {
        $input = '<script>alert("xss")</script>';
        $output = SecurityHelper::sanitizeText($input);
        
        expect($output)->toContain('&lt;script&gt;');
        expect($output)->not->toContain('<script>');
    });

    test('sanitizeWhatsAppMessage preserves formatting', function () {
        $input = '*bold* _italic_ ~strike~ `code`';
        $output = SecurityHelper::sanitizeWhatsAppMessage($input);
        
        expect($output)->toContain('<strong>bold</strong>');
        expect($output)->toContain('<em>italic</em>');
        expect($output)->toContain('<del>strike</del>');
        expect($output)->toContain('<code>code</code>');
    });

    test('sanitizeWhatsAppMessage removes scripts', function () {
        $input = '*bold* <script>alert("xss")</script> text';
        $output = SecurityHelper::sanitizeWhatsAppMessage($input);
        
        expect($output)->not->toContain('<script');
        expect($output)->toContain('<strong>bold</strong>');
    });

    test('containsXss detects xss payloads', function () {
        expect(SecurityHelper::containsXss('<script>alert(1)</script>'))->toBeTrue();
        expect(SecurityHelper::containsXss('javascript:alert(1)'))->toBeTrue();
        expect(SecurityHelper::containsXss('<img onerror=alert(1)>'))->toBeTrue();
        expect(SecurityHelper::containsXss('normal text'))->toBeFalse();
    });

    test('sanitizePhoneNumber removes non-numeric', function () {
        $input = '+62-812-3456-7890<script>alert(1)</script>';
        $output = SecurityHelper::sanitizePhoneNumber($input);
        
        expect($output)->toBe('+6281234567890');
        expect($output)->not->toContain('<script');
    });

    test('sanitizeFilename removes path traversal', function () {
        $input = '../../../etc/passwd.php';
        $output = SecurityHelper::sanitizeFilename($input);
        
        expect($output)->not->toContain('../');
        expect($output)->not->toBe('passwd.php');
    });
});
