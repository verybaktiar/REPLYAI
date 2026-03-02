<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class CaptchaService
 * 
 * Service untuk validasi CAPTCHA (hCaptcha atau reCAPTCHA).
 * Default menggunakan hCaptcha (privacy-friendly, gratis 1 juta/bulan).
 * 
 * @package App\Services\Security
 */
class CaptchaService
{
    /**
     * Provider CAPTCHA yang digunakan
     * 
     * @var string
     */
    private string $provider;

    /**
     * Secret key untuk verifikasi
     * 
     * @var string
     */
    private string $secretKey;

    /**
     * Site key untuk frontend
     * 
     * @var string
     */
    private string $siteKey;

    /**
     * Verify URL
     * 
     * @var string
     */
    private string $verifyUrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->provider = config('services.captcha.provider', 'hcaptcha');
        $this->secretKey = config('services.captcha.secret');
        $this->siteKey = config('services.captcha.site_key');
        
        $this->verifyUrl = match($this->provider) {
            'hcaptcha' => 'https://api.hcaptcha.com/siteverify',
            'recaptcha' => 'https://www.google.com/recaptcha/api/siteverify',
            'turnstile' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            default => 'https://api.hcaptcha.com/siteverify',
        };
    }

    /**
     * Validasi CAPTCHA response dari frontend
     *
     * @param string $response Token dari CAPTCHA widget
     * @param string|null $remoteIp IP address user (opsional)
     * @return bool
     */
    public function verify(string $response, ?string $remoteIp = null): bool
    {
        // Jika CAPTCHA tidak diaktifkan, anggap valid
        if (!config('services.captcha.enabled', false)) {
            return true;
        }

        // Jika secret key tidak di-set, anggap valid (dev mode)
        if (empty($this->secretKey)) {
            Log::warning('CAPTCHA secret key not configured, skipping validation');
            return true;
        }

        if (empty($response)) {
            return false;
        }

        try {
            $payload = [
                'secret' => $this->secretKey,
                'response' => $response,
            ];

            if ($remoteIp) {
                $payload['remoteip'] = $remoteIp;
            }

            $result = Http::asForm()->post($this->verifyUrl, $payload);

            if (!$result->successful()) {
                Log::error('CAPTCHA verification request failed', [
                    'status' => $result->status(),
                    'provider' => $this->provider,
                ]);
                return false;
            }

            $data = $result->json();

            // Log failed attempts untuk debugging
            if (!($data['success'] ?? false)) {
                Log::warning('CAPTCHA verification failed', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'provider' => $this->provider,
                ]);
            }

            return $data['success'] ?? false;

        } catch (\Exception $e) {
            Log::error('CAPTCHA verification error: ' . $e->getMessage());
            // Fail open atau fail close? Untuk UX, kita fail open di production
            // tapi log error untuk monitoring
            return app()->environment('local');
        }
    }

    /**
     * Get site key untuk frontend
     *
     * @return string
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Cek apakah CAPTCHA enabled
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return config('services.captcha.enabled', false) 
            && !empty(config('services.captcha.secret'));
    }

    /**
     * Get JavaScript URL untuk load CAPTCHA
     *
     * @return string
     */
    public function getScriptUrl(): string
    {
        return match($this->provider) {
            'hcaptcha' => 'https://js.hcaptcha.com/1/api.js',
            'recaptcha' => 'https://www.google.com/recaptcha/api.js',
            'turnstile' => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
            default => 'https://js.hcaptcha.com/1/api.js',
        };
    }

    /**
     * Get HTML attribute name untuk form field
     *
     * @return string
     */
    public function getResponseFieldName(): string
    {
        return match($this->provider) {
            'hcaptcha' => 'h-captcha-response',
            'recaptcha' => 'g-recaptcha-response',
            'turnstile' => 'cf-turnstile-response',
            default => 'h-captcha-response',
        };
    }
}
