<?php

namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * Class SecurityHelper
 * 
 * Helper functions untuk keamanan aplikasi.
 * 
 * @package App\Helpers
 */
class SecurityHelper
{
    /**
     * Sanitize HTML content untuk ditampilkan dengan aman.
     * Hanya mengizinkan tag dan atribut yang aman.
     *
     * @param string|null $content
     * @return string
     */
    public static function sanitizeHtml(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // Decode HTML entities dulu
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Hapus script tags dan event handlers
        $content = self::removeScripts($content);
        
        // Hapus style tags
        $content = self::removeStyles($content);
        
        // Hapus iframe, object, embed
        $content = self::removeDangerousTags($content);
        
        // Escape sisa content
        $content = e($content);
        
        // Convert URLs menjadi clickable links (opsional, dengan sanitasi)
        $content = self::linkify($content);
        
        // Convert line breaks
        $content = nl2br($content, false);

        return $content;
    }

    /**
     * Simple sanitize untuk text only (tanpa HTML)
     *
     * @param string|null $text
     * @return string
     */
    public static function sanitizeText(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Escape HTML entities
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $text;
    }

    /**
     * Sanitize untuk WhatsApp message content
     * Support formatting WhatsApp: *bold*, _italic_, ~strikethrough~, `code`
     *
     * @param string|null $content
     * @return string
     */
    public static function sanitizeWhatsAppMessage(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // Escape HTML dulu
        $content = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Convert WhatsApp formatting (dengan hati-hati)
        // Bold: *text* -> <strong>text</strong>
        $content = preg_replace('/\*([^\*]+)\*/', '<strong>$1</strong>', $content);
        
        // Italic: _text_ -> <em>text</em>
        $content = preg_replace('/_([^_]+)_/', '<em>$1</em>', $content);
        
        // Strikethrough: ~text~ -> <del>text</del>
        $content = preg_replace('/~([^~]+)~/', '<del>$1</del>', $content);
        
        // Code: `text` -> <code>text</code>
        $content = preg_replace('/`([^`]+)`/', '<code>$1</code>', $content);

        // Line breaks
        $content = nl2br($content, false);

        return $content;
    }

    /**
     * Remove script tags dan event handlers
     *
     * @param string $content
     * @return string
     */
    private static function removeScripts(string $content): string
    {
        // Remove script tags
        $content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);
        
        // Remove javascript: pseudo-protocol
        $content = preg_replace('/javascript:/i', '', $content);
        
        // Remove event handlers (onload, onclick, etc)
        $content = preg_replace('/\s+on\w+\s*=\s*["\']?[^"\']*["\']?/i', '', $content);
        
        return $content;
    }

    /**
     * Remove style tags
     *
     * @param string $content
     * @return string
     */
    private static function removeStyles(string $content): string
    {
        return preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);
    }

    /**
     * Remove dangerous tags (iframe, object, embed, etc)
     *
     * @param string $content
     * @return string
     */
    private static function removeDangerousTags(string $content): string
    {
        $dangerousTags = ['iframe', 'object', 'embed', 'form', 'input', 'textarea', 'select'];
        
        foreach ($dangerousTags as $tag) {
            $content = preg_replace('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', '', $content);
            $content = preg_replace('/<' . $tag . '[^>]*\/?>/is', '', $content);
        }
        
        return $content;
    }

    /**
     * Convert URLs to clickable links dengan sanitasi
     *
     * @param string $content
     * @return string
     */
    private static function linkify(string $content): string
    {
        // Pattern untuk URL
        $pattern = '/(https?:\/\/[^\s<]+)/i';
        $replacement = '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:underline">$1</a>';
        
        return preg_replace($pattern, $replacement, $content);
    }

    /**
     * Validate dan sanitize phone number
     *
     * @param string|null $phone
     * @return string|null
     */
    public static function sanitizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Hanya izinkan angka dan +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Validasi panjang
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            return null;
        }

        return $phone;
    }

    /**
     * Sanitize filename untuk upload
     *
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Hapus path traversal
        $filename = basename($filename);
        
        // Hapus karakter berbahaya
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Pastikan tidak ada double extension
        $filename = preg_replace('/\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)/i', '.txt', $filename);
        
        return $filename;
    }

    /**
     * Generate CSRF token untuk API calls (additional protection)
     *
     * @return string
     */
    public static function generateApiToken(): string
    {
        return hash('sha256', uniqid(rand(), true));
    }

    /**
     * Cek apakah string mengandung potential XSS payload
     *
     * @param string $content
     * @return bool
     */
    public static function containsXss(string $content): bool
    {
        $xssPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}
