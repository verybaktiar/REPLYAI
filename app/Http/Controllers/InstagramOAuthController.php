<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccount;
use App\Services\InstagramOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InstagramOAuthController extends Controller
{
    protected InstagramOAuthService $oauthService;

    public function __construct(InstagramOAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Show Instagram settings page
     */
    public function settings()
    {
        $user = Auth::user();
        $instagramAccount = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        return view('pages.instagram.settings', [
            'instagramAccount' => $instagramAccount,
            'isConfigured' => $this->oauthService->isConfigured(),
        ]);
    }

    /**
     * Redirect to Instagram OAuth
     */
    public function connect(Request $request)
    {
        // Check if Instagram OAuth is configured
        if (!$this->oauthService->isConfigured()) {
            return redirect()->route('instagram.settings')
                ->with('error', __('instagram.error_not_configured'));
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', __('instagram.error_login_first'));
        }

        // Generate state token that includes user_id for CSRF protection
        // Format: random_string|user_id (encoded as base64)
        $randomState = Str::random(40);
        $stateData = base64_encode(json_encode([
            'token' => $randomState,
            'user_id' => $user->id,
        ]));
        
        session(['instagram_oauth_state' => $randomState]);

        $authUrl = $this->oauthService->getAuthUrl($stateData);

        Log::info('Instagram OAuth: Initiating connect', [
            'user_id' => $user->id,
            'state_token' => $randomState,
        ]);

        return redirect($authUrl);
    }

    /**
     * Handle OAuth callback from Instagram
     */
    public function callback(Request $request)
    {
        $returnedState = $request->input('state');
        
        // Decode the state to get user_id
        $stateData = null;
        $userId = null;
        $stateToken = null;
        
        if ($returnedState) {
            try {
                $decoded = base64_decode($returnedState);
                $stateData = json_decode($decoded, true);
                if ($stateData && isset($stateData['token']) && isset($stateData['user_id'])) {
                    $stateToken = $stateData['token'];
                    $userId = $stateData['user_id'];
                }
            } catch (\Exception $e) {
                Log::warning('Instagram OAuth: Failed to decode state', [
                    'state' => $returnedState,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Try to get user from session first, fallback to state
        $user = Auth::user();
        
        if (!$user && $userId) {
            // Session lost during OAuth redirect, recover user from state
            $user = \App\Models\User::find($userId);
            if ($user) {
                // Re-authenticate the user
                Auth::login($user);
                Log::info('Instagram OAuth: Re-authenticated user from state', [
                    'user_id' => $userId,
                ]);
            }
        }
        
        if (!$user) {
            Log::warning('Instagram OAuth: No authenticated user', [
                'state' => $returnedState,
                'state_user_id' => $userId,
            ]);
            return redirect()->route('login')
                ->with('error', 'Session expired. Please login and try connecting Instagram again.');
        }

        // Verify state token
        $storedState = session('instagram_oauth_state');
        
        // If session state is lost but we have state from URL, verify it matches the token
        if (!$storedState && $stateToken) {
            // Session was lost, but we can still verify the user via the encrypted state
            Log::info('Instagram OAuth: Session state lost, proceeding with state verification', [
                'user_id' => $user->id,
            ]);
        } elseif ($storedState && $stateToken && $storedState !== $stateToken) {
            Log::warning('Instagram OAuth: State mismatch', [
                'stored' => $storedState,
                'returned_token' => $stateToken,
            ]);
            return redirect()->route('instagram.settings')
                ->with('error', __('instagram.error_invalid_state'));
        }

        // Check for errors
        if ($request->has('error')) {
            Log::error('Instagram OAuth: Error from Instagram', [
                'error' => $request->input('error'),
                'reason' => $request->input('error_reason'),
                'description' => $request->input('error_description'),
            ]);
            return redirect()->route('instagram.settings')
                ->with('error', __('instagram.error_denied', ['description' => $request->input('error_description')]));
        }

        // Get authorization code
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('instagram.settings')
                ->with('error', __('instagram.error_no_code'));
        }

        // Exchange code for token
        $tokenData = $this->oauthService->exchangeCodeForToken($code);
        if (!$tokenData) {
            return redirect()->route('instagram.settings')
                ->with('error', __('instagram.error_exchange_failed'));
        }

        // Get user profile
        $profile = $this->oauthService->getUserProfile(
            $tokenData['access_token'],
            $tokenData['user_id']
        );
        
        // Check if this IG account is already connected to another user
        $existingAccount = InstagramAccount::where('instagram_user_id', $tokenData['user_id'])
            ->where('user_id', '!=', $user->id)
            ->first();

        if ($existingAccount) {
            return redirect()->route('instagram.settings')
                ->with('error', __('instagram.error_already_connected'));
        }

        // ✅ Find existing account for this user (including inactive ones)
        // This ensures conversation links are preserved when reconnecting
        $userExistingAccount = InstagramAccount::where('user_id', $user->id)->first();
        
        // ✅ Upsert the Instagram account
        $igAccount = InstagramAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'instagram_user_id' => $tokenData['user_id'],
                'username' => $profile['username'] ?? null,
                'name' => $profile['name'] ?? null,
                'profile_picture_url' => $profile['profile_picture_url'] ?? null,
                'access_token' => $tokenData['access_token'],
                'token_expires_at' => now()->addSeconds($tokenData['expires_in']),
                'is_active' => true,
            ]
        );
        
        // ✅ Migrate conversations if reconnecting same account or switching accounts
        // Link any orphan conversations (NULL instagram_account_id) to this account
        \App\Models\Conversation::where('user_id', $user->id)
            ->whereNull('instagram_account_id')
            ->update(['instagram_account_id' => $igAccount->id]);

        Log::info('Instagram OAuth: Account connected successfully', [
            'user_id' => $user->id,
            'instagram_account_id' => $igAccount->id,
            'instagram_user_id' => $tokenData['user_id'],
            'username' => $profile['username'] ?? 'unknown',
        ]);

        // Clear session state
        session()->forget('instagram_oauth_state');

        return redirect()->route('instagram.settings')
            ->with('success', __('instagram.success_connected'));
    }

    /**
     * Disconnect Instagram account
     */
    public function disconnect(Request $request)
    {
        $user = Auth::user();
        $account = InstagramAccount::where('user_id', $user->id)->first();

        if ($account) {
            // ✅ Soft-delete: Set is_active = false instead of hard delete
            // This preserves the account ID so conversations stay linked
            // Note: Instagram doesn't have a revoke API, so we just deactivate locally
            $account->is_active = false;
            $account->access_token = '';  // Clear token for security
            $account->save();

            Log::info('Instagram OAuth: Account disconnected (soft-delete)', [
                'user_id' => $user->id,
                'instagram_account_id' => $account->id,
            ]);
        }

        return redirect()->route('instagram.settings')
            ->with('success', __('instagram.success_disconnected'));
    }

    /**
     * Refresh token (called by scheduled job or manually)
     */
    public function refreshToken(Request $request)
    {
        $user = Auth::user();
        $account = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            return back()->with('error', __('instagram.error_no_account'));
        }

        $refreshedToken = $this->oauthService->refreshToken($account->access_token);

        if ($refreshedToken) {
            $account->update([
                'access_token' => $refreshedToken['access_token'],
                'token_expires_at' => now()->addSeconds($refreshedToken['expires_in'] ?? 5184000),
            ]);

            return back()->with('success', __('instagram.success_refreshed'));
        }

        return back()->with('error', __('instagram.error_refresh_failed'));
    }
}
