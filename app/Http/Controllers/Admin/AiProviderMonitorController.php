<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiProviderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiProviderMonitorController extends Controller
{
    public function __construct(
        protected AiProviderService $providerService
    ) {}

    /**
     * Tampilkan halaman monitoring AI Providers
     */
    public function index()
    {
        $status = $this->providerService->getProviderStatus();
        
        return view('admin.ai-providers.index', [
            'providers' => $status,
            'config' => $this->providerService->getConfig(),
        ]);
    }

    /**
     * Get provider status (API)
     */
    public function status(): JsonResponse
    {
        $status = $this->providerService->getProviderStatus();
        
        return response()->json([
            'success' => true,
            'providers' => $status,
            'config' => $this->providerService->getConfig(),
        ]);
    }

    /**
     * Test provider connectivity
     */
    public function test(string $provider): JsonResponse
    {
        $result = $this->providerService->testProvider($provider);
        
        return response()->json([
            'success' => $result['success'],
            'provider' => $provider,
            'result' => $result,
        ]);
    }

    /**
     * Reset provider failure count
     */
    public function reset(string $provider): JsonResponse
    {
        \Illuminate\Support\Facades\Cache::forget('ai_provider_failures_' . $provider);
        \Illuminate\Support\Facades\Cache::forget('ai_provider_last_failure_' . $provider);
        \Illuminate\Support\Facades\Cache::forget('ai_provider_last_error_' . $provider);
        
        Log::info('AI Provider reset manually', ['provider' => $provider, 'by' => auth()->user()?->email]);
        
        return response()->json([
            'success' => true,
            'message' => "Provider {$provider} reset successfully",
        ]);
    }

    /**
     * Switch primary provider
     */
    public function switchProvider(Request $request): JsonResponse
    {
        $request->validate([
            'primary' => 'required|in:megallm,sumopod',
            'secondary' => 'required|in:megallm,sumopod|different:primary',
        ]);

        // Save to cache for realtime application-wide effect
        Cache::put('ai_provider_primary', $request->primary, 86400); // 24 hours
        Cache::put('ai_provider_secondary', $request->secondary, 86400);
        
        // Also update config for current request
        config(['services.ai_failover.primary' => $request->primary]);
        config(['services.ai_failover.secondary' => $request->secondary]);
        
        Log::info('AI Provider switched manually', [
            'primary' => $request->primary,
            'secondary' => $request->secondary,
            'by' => auth()->user()?->email
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Primary provider switched to {$request->primary}",
            'config' => [
                'primary' => $request->primary,
                'secondary' => $request->secondary,
            ],
        ]);
    }
}
