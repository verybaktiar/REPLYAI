<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\SubscriptionHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: Cek Akses Fitur (CheckFeatureAccess)
 * 
 * Middleware ini mengecek apakah user punya akses ke fitur tertentu.
 * Jika tidak punya akses (paket tidak mendukung), tampilkan halaman upgrade.
 * 
 * Penggunaan di routes/web.php:
 * 
 * // Cek akses ke broadcast
 * Route::get('/broadcast', ...)->middleware('feature:broadcasts');
 * 
 * // Cek akses ke sequences
 * Route::get('/sequences', ...)->middleware('feature:sequences');
 * 
 * // Cek akses ke web widget
 * Route::get('/web-widget', ...)->middleware('feature:web_widgets');
 */
class CheckFeatureAccess
{
    /**
     * Handle incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature Nama fitur yang dicek
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Jika belum login, biarkan middleware auth yang handle
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // VIP users bypass semua feature check
        if ($user->is_vip ?? false) {
            return $next($request);
        }

        // Cek apakah punya akses ke fitur ini
        if (!SubscriptionHelper::hasFeature($feature)) {
            // Jika request adalah AJAX/API
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->getFeatureBlockedMessage($feature),
                    'feature' => $feature,
                    'upgrade_url' => route('upgrade', ['feature' => $feature]),
                ], 403);
            }

            // Redirect ke halaman upgrade dengan info fitur
            return redirect()
                ->route('upgrade', ['feature' => $feature])
                ->with('upgrade_prompt', $this->getFeatureBlockedMessage($feature));
        }

        return $next($request);
    }

    /**
     * Ambil pesan yang sesuai untuk fitur yang diblokir
     * 
     * @param string $feature
     * @return string
     */
    private function getFeatureBlockedMessage(string $feature): string
    {
        $messages = [
            'broadcasts' => 'Fitur Broadcast hanya tersedia di paket Hemat ke atas. Upgrade sekarang!',
            'sequences' => 'Fitur Sequence/Follow-up Otomatis hanya tersedia di paket Hemat ke atas. Upgrade sekarang!',
            'web_widgets' => 'Fitur Web Widget hanya tersedia di paket Pro ke atas. Upgrade sekarang!',
            'analytics_export' => 'Fitur Export Analytics hanya tersedia di paket Pro ke atas. Upgrade sekarang!',
            'api_access' => 'Akses API hanya tersedia di paket Pro ke atas. Upgrade sekarang!',
            'remove_branding' => 'Fitur menghilangkan branding hanya tersedia di paket Pro ke atas.',
        ];

        return $messages[$feature] ?? 'Fitur ini tidak tersedia di paket Anda. Silakan upgrade untuk menggunakan fitur ini.';
    }
}
