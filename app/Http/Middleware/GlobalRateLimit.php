<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class GlobalRateLimit
 * 
 * Middleware untuk global rate limiting per IP address.
 * Melindungi dari brute force, credential stuffing, dan DDoS.
 * 
 * @package App\Http\Middleware
 */
class GlobalRateLimit
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): SymfonyResponse
    {
        // Skip rate limiting untuk route tertentu (webhook, health check)
        if ($this->shouldSkipRateLimit($request)) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts, $decayMinutes);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $maxAttempts, $this->calculateRemainingAttempts($key, $maxAttempts));
    }

    /**
     * Resolve request signature untuk rate limiting key.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Kombinasi IP + route untuk granular rate limiting
        $ip = $request->ip() ?? 'unknown';
        $route = $request->route()?->getName() ?? $request->path();
        
        return sha1("{$ip}|{$route}");
    }

    /**
     * Cek apakah request harus di-skip dari rate limiting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkipRateLimit(Request $request): bool
    {
        $skipPaths = [
            'up',                    // Health check
            'health',                // Health check
            'webhook',               // Webhooks
            'api/webhook',           // API webhooks
            'instagram/webhook',     // Instagram webhook
            'whatsapp/webhook',      // WhatsApp webhook
        ];

        $path = $request->path();

        foreach ($skipPaths as $skip) {
            if (str_contains($path, $skip)) {
                return true;
            }
        }

        // Skip untuk admin yang sudah terautentikasi
        if ($request->user()?->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Build response ketika rate limit exceeded.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse(string $key, int $maxAttempts, int $decayMinutes): SymfonyResponse
    {
        $retryAfter = $this->limiter->availableIn($key);

        $message = sprintf(
            'Terlalu banyak request. Silakan tunggu %d detik sebelum mencoba lagi.',
            $retryAfter
        );

        // JSON response untuk AJAX/API
        if (request()->expectsJson()) {
            return new JsonResponse([
                'success' => false,
                'message' => $message,
                'retry_after' => $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Retry-After' => $retryAfter,
            ]);
        }

        // HTML response
        return new Response(
            view('errors.429', [
                'message' => $message,
                'retryAfter' => $retryAfter,
            ])->render(),
            429,
            [
                'Retry-After' => $retryAfter,
                'Content-Type' => 'text/html',
            ]
        );
    }

    /**
     * Add rate limit headers ke response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addRateLimitHeaders(SymfonyResponse $response, int $maxAttempts, int $remainingAttempts): SymfonyResponse
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remainingAttempts),
        ]);

        return $response;
    }

    /**
     * Calculate remaining attempts.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return int
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - $this->limiter->attempts($key);
    }
}
