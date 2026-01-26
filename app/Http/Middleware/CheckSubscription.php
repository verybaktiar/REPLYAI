<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\SubscriptionHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: Cek Langganan (CheckSubscription)
 * 
 * Middleware ini mengecek apakah user punya langganan aktif.
 * Jika langganan expired, user akan diarahkan ke halaman perpanjang.
 * 
 * Penggunaan di routes/web.php:
 * Route::middleware('subscription')->group(function () {
 *     // Routes yang butuh langganan aktif
 * });
 * 
 * Atau per route:
 * Route::get('/broadcast', ...)->middleware('subscription');
 */
class CheckSubscription
{
    /**
     * Handle incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login, biarkan middleware auth yang handle
        if (!auth()->check()) {
            return $next($request);
        }

        // Cek apakah langganan aktif
        if (!SubscriptionHelper::isActive()) {
            // Jika request adalah AJAX/API
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Langganan Anda sudah tidak aktif. Silakan perpanjang langganan.',
                    'redirect' => route('subscription.index'),
                ], 403);
            }

            // Simpan pesan error di session
            session()->flash('subscription_expired', true);
            
            // Redirect ke halaman subscription
            return redirect()
                ->route('subscription.index')
                ->with('error', 'Langganan Anda sudah tidak aktif. Silakan perpanjang untuk melanjutkan.');
        }

        return $next($request);
    }
}
