<?php

namespace App\Services;

use App\Models\InstagramAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramOAuthService
{
    protected ?string $appId;
    protected ?string $appSecret;
    protected ?string $redirectUri;

    public function __construct()
    {
        $this->appId = config('services.instagram.app_id');
        $this->appSecret = config('services.instagram.app_secret');
        $this->redirectUri = config('services.instagram.redirect_uri');
    }

    /**
     * Check if Instagram OAuth is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->appId) && !empty($this->appSecret) && !empty($this->redirectUri);
    }

    /**
     * Generate OAuth URL for Instagram Login
     * Using Instagram OAuth directly for Business accounts
     */
    public function getAuthUrl(string $state = null): string
    {
        $params = [
            'client_id' => $this->appId,
            'redirect_uri' => $this->redirectUri,
            // Scope untuk Instagram API with Login (Business) - full permissions
            'scope' => 'instagram_business_basic,instagram_business_manage_messages,instagram_business_manage_comments,instagram_business_content_publish,instagram_business_manage_insights',
            'response_type' => 'code',
        ];

        if ($state) {
            $params['state'] = $state;
        }

        // Instagram OAuth langsung
        return 'https://www.instagram.com/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code): ?array
    {
        try {
            // Step 1: Exchange code for short-lived token
            $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id' => $this->appId,
                'client_secret' => $this->appSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
                'code' => $code,
            ]);

            if ($response->failed()) {
                Log::error('Instagram OAuth: Failed to exchange code', [
                    'error' => $response->json(),
                ]);
                return null;
            }

            $shortLivedToken = $response->json();

            // Step 2: Exchange for long-lived token
            $longLivedResponse = Http::get('https://graph.instagram.com/access_token', [
                'grant_type' => 'ig_exchange_token',
                'client_secret' => $this->appSecret,
                'access_token' => $shortLivedToken['access_token'],
            ]);

            if ($longLivedResponse->failed()) {
                Log::error('Instagram OAuth: Failed to get long-lived token', [
                    'error' => $longLivedResponse->json(),
                ]);
                // Fallback to short-lived token
                return [
                    'access_token' => $shortLivedToken['access_token'],
                    'user_id' => $shortLivedToken['user_id'],
                    'expires_in' => 3600, // 1 hour for short-lived
                ];
            }

            $longLivedToken = $longLivedResponse->json();

            return [
                'access_token' => $longLivedToken['access_token'],
                'user_id' => $shortLivedToken['user_id'],
                'expires_in' => $longLivedToken['expires_in'] ?? 5184000, // ~60 days
            ];
        } catch (\Throwable $e) {
            Log::error('Instagram OAuth: Exception during token exchange', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get Instagram user profile information
     */
    public function getUserProfile(string $accessToken, string $userId): ?array
    {
        try {
            $response = Http::get("https://graph.instagram.com/v21.0/{$userId}", [
                'fields' => 'id,username,name,profile_picture_url',
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Instagram OAuth: Failed to get user profile', [
                'error' => $response->json(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Instagram OAuth: Exception getting user profile', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Refresh a long-lived token (must be done before expiry)
     */
    public function refreshToken(string $accessToken): ?array
    {
        try {
            $response = Http::get('https://graph.instagram.com/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Instagram OAuth: Failed to refresh token', [
                'error' => $response->json(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Instagram OAuth: Exception refreshing token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Revoke access (disconnect)
     */
    public function revokeAccess(InstagramAccount $account): bool
    {
        // Instagram doesn't have a direct revoke API
        // Just mark as inactive in our database
        $account->update(['is_active' => false]);
        return true;
    }
}
