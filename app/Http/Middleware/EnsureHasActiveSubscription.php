<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Jika user tidak punya subscription sama sekali
        if (!$user->subscription) {
            return redirect()->route('pricing')
                ->with('warning', 'Silakan pilih paket langganan untuk melanjutkan.');
        }
        
        // Jika subscription tidak active (pending/expired/cancelled)
        if ($user->subscription->status !== 'active') {
            // Jika pending payment
            if ($user->subscription->status === 'pending') {
                return redirect()->route('subscription.pending')
                    ->with('info', 'Menunggu pembayaran Anda diverifikasi oleh admin.');
            }
            
            // Jika expired/cancelled
            return redirect()->route('pricing')
                ->with('warning', 'Subscription Anda sudah tidak aktif. Silakan perpanjang atau pilih paket baru.');
        }
        
        // Subscription active, lanjutkan request
        return $next($request);
    }
}
