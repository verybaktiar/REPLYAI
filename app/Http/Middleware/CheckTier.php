<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $tier  Minimum tier required: 'business' or 'enterprise'
     */
    public function handle(Request $request, Closure $next, string $tier): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $userTier = $user->getPlanTier();
        
        // Define tier levels for comparison
        $tierLevels = [
            'umkm' => 1,
            'business' => 2,
            'enterprise' => 3,
        ];
        
        $requiredLevel = $tierLevels[$tier] ?? 1;
        $userLevel = $tierLevels[$userTier] ?? 1;
        
        // Check if user meets the required tier
        if ($userLevel < $requiredLevel) {
            // Redirect with informative message
            $tierName = match($tier) {
                'business' => 'Business',
                'enterprise' => 'Enterprise',
                default => 'yang lebih tinggi'
            };
            
            return redirect()->route('dashboard')
                ->with('error', "Fitur ini memerlukan paket {$tierName}. Silakan upgrade paket Anda untuk mengakses fitur ini.");
        }
        
        return $next($request);
    }
}
