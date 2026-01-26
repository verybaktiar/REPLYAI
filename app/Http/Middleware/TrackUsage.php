<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\UsageTrackingService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: Tracking Penggunaan (TrackUsage)
 * 
 * Middleware ini otomatis mencatat penggunaan fitur tertentu.
 * Dipasang di route yang perlu ditrack.
 * 
 * Penggunaan di routes/web.php:
 * 
 * // Track penggunaan AI
 * Route::post('/ai/generate', ...)->middleware('track:ai_messages');
 * 
 * // Track broadcast dengan jumlah tertentu
 * Route::post('/broadcast/send', ...)->middleware('track:broadcasts,100');
 */
class TrackUsage
{
    protected UsageTrackingService $tracker;

    public function __construct(UsageTrackingService $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Handle incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature Fitur yang ditrack
     * @param  int  $amount Jumlah yang ditrack (default 1)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $feature, int $amount = 1): Response
    {
        // Jika belum login, skip
        if (!auth()->check()) {
            return $next($request);
        }

        $userId = auth()->id();

        // Cek apakah masih bisa digunakan
        if (!$this->tracker->canUse($userId, $feature, $amount)) {
            // Jika request adalah AJAX/API
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->tracker->getLimitReachedMessage($feature, $userId),
                    'feature' => $feature,
                    'limit_reached' => true,
                    'upgrade_url' => route('subscription.upgrade'),
                ], 403);
            }

            // Redirect dengan pesan error
            return redirect()
                ->back()
                ->with('error', $this->tracker->getLimitReachedMessage($feature, $userId))
                ->with('limit_reached', $feature);
        }

        // Jalankan request
        $response = $next($request);

        // Jika sukses (status 2xx), track penggunaan
        if ($response->isSuccessful()) {
            $this->tracker->track($userId, $feature, $amount);
        }

        return $response;
    }
}
