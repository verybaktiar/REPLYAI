<?php

namespace App\Console\Commands;

use App\Models\InstagramAccount;
use App\Services\InstagramOAuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshInstagramTokens extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'instagram:refresh-tokens';

    /**
     * The console command description.
     */
    protected $description = 'Refresh Instagram access tokens that are expiring within 7 days';

    public function __construct(
        protected InstagramOAuthService $oauthService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expiring Instagram tokens...');

        // Find accounts with tokens expiring in the next 7 days
        $expiringAccounts = InstagramAccount::where('is_active', true)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', now()->addDays(7))
            ->where('token_expires_at', '>', now()) // Not yet expired
            ->get();

        if ($expiringAccounts->isEmpty()) {
            $this->info('No tokens need refreshing.');
            return self::SUCCESS;
        }

        $this->info("Found {$expiringAccounts->count()} token(s) to refresh.");

        $refreshed = 0;
        $failed = 0;

        foreach ($expiringAccounts as $account) {
            $this->line("Refreshing token for: {$account->username} (User ID: {$account->user_id})");

            try {
                $newTokenData = $this->oauthService->refreshToken($account->access_token);

                if ($newTokenData) {
                    $account->update([
                        'access_token' => $newTokenData['access_token'],
                        'token_expires_at' => now()->addSeconds($newTokenData['expires_in'] ?? 5184000),
                    ]);

                    $refreshed++;
                    $this->info("✅ Token refreshed successfully for {$account->username}");
                    
                    Log::info('Instagram token refreshed', [
                        'account_id' => $account->id,
                        'username' => $account->username,
                        'new_expires_at' => $account->token_expires_at,
                    ]);
                } else {
                    $failed++;
                    $this->error("❌ Failed to refresh token for {$account->username}");
                    
                    Log::error('Instagram token refresh failed', [
                        'account_id' => $account->id,
                        'username' => $account->username,
                    ]);
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("❌ Exception refreshing token for {$account->username}: {$e->getMessage()}");
                
                Log::error('Instagram token refresh exception', [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Refresh complete: {$refreshed} refreshed, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
