<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiProviderService
{
    protected array $providers = [];
    protected string $primaryProvider;
    protected string $secondaryProvider;
    protected bool $failoverEnabled;

    public function __construct()
    {
        $this->providers = [
            'megallm' => [
                'enabled' => config('services.megallm.enabled', true),
                'key' => config('services.megallm.key'),
                'url' => config('services.megallm.url', 'https://ai.megallm.io/v1'),
                'model' => config('services.megallm.model', 'moonshotai/kimi-k2-instruct-0905'),
                'timeout' => config('services.megallm.timeout', 90),
                'retries' => config('services.megallm.retries', 2),
                'retry_sleep_ms' => config('services.megallm.retry_sleep_ms', 700),
                'fallback_models' => explode(',', config('services.megallm.fallback_models', 'deepseek-ai/deepseek-v3.1,gemini-2.5-flash')),
            ],
            'sumopod' => [
                'enabled' => config('services.sumopod.enabled', true),
                'key' => config('services.sumopod.key'),
                'url' => config('services.sumopod.url', 'https://ai.sumopod.com/v1'),
                'model' => config('services.sumopod.model', 'kimi-k2-5-260127-free'),
                'timeout' => config('services.sumopod.timeout', 90),
                'retries' => config('services.sumopod.retries', 2),
                'retry_sleep_ms' => config('services.sumopod.retry_sleep_ms', 700),
                'fallback_models' => explode(',', config('services.sumopod.fallback_models', 'seed-2-0-mini-free,whisper-1')),
            ],
        ];

        $this->failoverEnabled = config('services.ai_failover.enabled', true);
        // Read from cache first (for manual switch), fallback to config
        $this->primaryProvider = Cache::get('ai_provider_primary', config('services.ai_failover.primary', 'megallm'));
        $this->secondaryProvider = Cache::get('ai_provider_secondary', config('services.ai_failover.secondary', 'sumopod'));
    }

    /**
     * Get current provider configuration
     */
    public function getConfig(): array
    {
        return [
            'primary' => $this->primaryProvider,
            'secondary' => $this->secondaryProvider,
            'failover_enabled' => $this->failoverEnabled,
        ];
    }

    /**
     * Get chat completion with automatic failover
     */
    public function chatCompletion(array $messages, ?string $systemPrompt = null, ?string $forcedProvider = null): array
    {
        $providersToTry = $this->getProvidersToTry($forcedProvider);
        $lastError = null;

        foreach ($providersToTry as $providerName) {
            if (!$this->isProviderAvailable($providerName)) {
                Log::info("AI Provider skipped (not available)", ['provider' => $providerName]);
                continue;
            }

            $result = $this->tryProvider($providerName, $messages, $systemPrompt);

            if ($result['success']) {
                $this->recordProviderSuccess($providerName);
                return [
                    'success' => true,
                    'answer' => $result['answer'],
                    'provider' => $providerName,
                    'model' => $result['model'],
                    'source' => 'ai',
                ];
            }

            $lastError = $result['error'];
            $this->recordProviderFailure($providerName, $lastError);
            Log::warning("AI Provider failed, trying next...", [
                'provider' => $providerName,
                'error' => $lastError,
            ]);
        }

        return [
            'success' => false,
            'error' => $lastError ?? 'All AI providers failed',
            'answer' => null,
        ];
    }

    /**
     * Try a specific provider
     */
    protected function tryProvider(string $providerName, array $messages, ?string $systemPrompt): array
    {
        $provider = $this->providers[$providerName];
        
        if (empty($provider['key'])) {
            return ['success' => false, 'error' => 'API key not configured'];
        }

        $modelsToTry = array_merge(
            [$provider['model']],
            $provider['fallback_models'] ?? []
        );

        foreach ($modelsToTry as $model) {
            try {
                $response = Http::timeout($provider['timeout'])
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $provider['key'],
                        'Content-Type' => 'application/json',
                    ])
                    ->post($provider['url'] . '/chat/completions', [
                        'model' => $model,
                        'messages' => $systemPrompt 
                            ? array_merge([['role' => 'system', 'content' => $systemPrompt]], $messages)
                            : $messages,
                        'max_tokens' => 600,
                        'temperature' => 0.7,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $answer = $data['choices'][0]['message']['content'] ?? null;

                    if ($answer) {
                        return [
                            'success' => true,
                            'answer' => $answer,
                            'model' => $model,
                        ];
                    }
                }

                // Handle specific error codes
                $status = $response->status();
                if ($status === 402) {
                    return ['success' => false, 'error' => 'Insufficient credits for ' . $providerName];
                }
                if ($status === 429) {
                    return ['success' => false, 'error' => 'Rate limited on ' . $providerName];
                }

                Log::error("AI Provider HTTP error", [
                    'provider' => $providerName,
                    'model' => $model,
                    'status' => $status,
                    'body' => $response->body(),
                ]);

                // Try next model
                usleep($provider['retry_sleep_ms'] * 1000);

            } catch (\Exception $e) {
                Log::error("AI Provider Exception", [
                    'provider' => $providerName,
                    'model' => $model,
                    'error' => $e->getMessage(),
                ]);
                
                usleep($provider['retry_sleep_ms'] * 1000);
            }
        }

        return ['success' => false, 'error' => 'All models failed for ' . $providerName];
    }

    /**
     * Get providers to try in order
     */
    protected function getProvidersToTry(?string $forcedProvider): array
    {
        if ($forcedProvider && isset($this->providers[$forcedProvider])) {
            return [$forcedProvider];
        }

        if (!$this->failoverEnabled) {
            return [$this->primaryProvider];
        }

        // Check if primary is healthy
        if ($this->isProviderHealthy($this->primaryProvider)) {
            return [$this->primaryProvider, $this->secondaryProvider];
        }

        // Primary is unhealthy, try secondary first
        return [$this->secondaryProvider, $this->primaryProvider];
    }

    /**
     * Check if provider is available (enabled and has key)
     */
    public function isProviderAvailable(string $providerName): bool
    {
        if (!isset($this->providers[$providerName])) {
            return false;
        }

        $provider = $this->providers[$providerName];
        return $provider['enabled'] && !empty($provider['key']);
    }

    /**
     * Check if provider is healthy (recent success rate)
     */
    public function isProviderHealthy(string $providerName): bool
    {
        $failures = Cache::get('ai_provider_failures_' . $providerName, 0);
        $lastFailure = Cache::get('ai_provider_last_failure_' . $providerName);

        // If more than 3 failures in last 5 minutes, consider unhealthy
        if ($failures >= 3) {
            if ($lastFailure && now()->diffInMinutes($lastFailure) < 5) {
                return false;
            }
            // Reset after cooldown
            Cache::forget('ai_provider_failures_' . $providerName);
        }

        return true;
    }

    /**
     * Record provider success
     */
    protected function recordProviderSuccess(string $providerName): void
    {
        Cache::forget('ai_provider_failures_' . $providerName);
        Cache::put('ai_provider_last_success_' . $providerName, now(), 3600);
    }

    /**
     * Record provider failure
     */
    protected function recordProviderFailure(string $providerName, string $error): void
    {
        $key = 'ai_provider_failures_' . $providerName;
        $failures = Cache::get($key, 0) + 1;
        Cache::put($key, $failures, 300); // 5 minutes
        Cache::put('ai_provider_last_failure_' . $providerName, now(), 300);
        Cache::put('ai_provider_last_error_' . $providerName, $error, 3600);
    }

    /**
     * Get provider status for monitoring
     */
    public function getProviderStatus(): array
    {
        $status = [];

        foreach ($this->providers as $name => $config) {
            $status[$name] = [
                'enabled' => $config['enabled'],
                'available' => $this->isProviderAvailable($name),
                'healthy' => $this->isProviderHealthy($name),
                'failures' => Cache::get('ai_provider_failures_' . $name, 0),
                'last_success' => Cache::get('ai_provider_last_success_' . $name),
                'last_failure' => Cache::get('ai_provider_last_failure_' . $name),
                'last_error' => Cache::get('ai_provider_last_error_' . $name),
                'model' => $config['model'],
                'has_key' => !empty($config['key']),
            ];
        }

        return $status;
    }

    /**
     * Test provider connectivity
     */
    public function testProvider(string $providerName): array
    {
        if (!$this->isProviderAvailable($providerName)) {
            return [
                'success' => false,
                'error' => 'Provider not available or not configured',
            ];
        }

        try {
            $provider = $this->providers[$providerName];
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $provider['key'],
                    'Content-Type' => 'application/json',
                ])
                ->post($provider['url'] . '/chat/completions', [
                    'model' => $provider['model'],
                    'messages' => [
                        ['role' => 'user', 'content' => 'Hi, respond with "OK" only']
                    ],
                    'max_tokens' => 10,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'latency_ms' => $response->handlerStats()['total_time'] ?? null,
                    'model' => $provider['model'],
                ];
            }

            return [
                'success' => false,
                'error' => 'HTTP ' . $response->status() . ': ' . $response->body(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
