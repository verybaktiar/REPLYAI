<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Feature;

class CheckFeatureFlag
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $flag
     */
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        if (Feature::disabled($flag)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Feature ' . $flag . ' is currently disabled by administrator.',
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', '⚠️ Fitur ini sedang dinonaktifkan sementara oleh administrator untuk pemeliharaan.');
        }

        return $next($request);
    }
}
