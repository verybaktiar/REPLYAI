<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);
        
        $plan = $user->getPlan();
        $limits = $plan->features ?? [];
        $limit = $limits[$feature] ?? 0;
        
        // -1 means unlimited
        if ($limit === -1) return $next($request);
        
        $usage = 0;
        $message = "Maaf, Anda telah mencapai batas penggunaan untuk paket ini.";
        $redirect = route('pricing');

        switch ($feature) {
            case 'ai_messages':
                $usage = \App\Models\WaMessage::where('user_id', $user->id)
                    ->where('direction', 'outgoing')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->count();
                $message = "Maaf, kuota pesan AI bulanan Anda ({$limit}) sudah habis. Silakan upgrade paket Anda untuk terus menggunakan AI!";
                break;
                
            case 'kb_articles':
                $usage = \App\Models\KbArticle::where('user_id', $user->id)->count();
                $message = "Maaf, Anda sudah mencapai batas maksimal Info Produk ({$limit}) untuk paket ini. Silakan upgrade paket Anda untuk menambah lebih banyak!";
                break;
                
            case 'auto_reply_rules':
                $usage = \App\Models\AutoReplyRule::where('user_id', $user->id)->count();
                $message = "Maaf, Anda sudah mencapai batas maksimal Aturan Bot ({$limit}) untuk paket ini. Silakan upgrade paket Anda untuk menambah lebih banyak!";
                break;
        }

        if ($usage >= $limit) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message, 'limit_reached' => true], 403);
            }
            return redirect($redirect)->with('warning', $message);
        }

        return $next($request);
    }
}
