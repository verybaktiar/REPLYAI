<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    /**
     * Show active sessions for current user
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all sessions from database for this user
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                // Parse user agent to get device info
                $userAgent = $session->user_agent ?? '';
                $device = $this->parseUserAgent($userAgent);
                
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'device' => $device,
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'is_current' => $session->id === Session::getId(),
                ];
            });
        
        return view('pages.account.sessions', [
            'sessions' => $sessions,
            'user' => $user,
        ]);
    }

    /**
     * Logout from a specific session
     */
    public function destroy(Request $request, string $sessionId)
    {
        $user = Auth::user();
        
        // Only allow deleting own sessions
        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();
        
        if ($deleted) {
            return back()->with('success', 'Sesi berhasil diakhiri');
        }
        
        return back()->with('error', 'Gagal mengakhiri sesi');
    }

    /**
     * Logout from all other sessions
     */
    public function destroyAll(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = Session::getId();
        
        // Delete all sessions except current
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
        
        return back()->with('success', 'Semua sesi lain berhasil diakhiri');
    }

    /**
     * Parse user agent to get device info
     */
    private function parseUserAgent(string $userAgent): array
    {
        $browser = 'Unknown Browser';
        $platform = 'Unknown Device';
        $icon = 'devices';

        // Detect browser
        if (stripos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        }

        // Detect platform
        if (stripos($userAgent, 'Windows') !== false) {
            $platform = 'Windows';
            $icon = 'computer';
        } elseif (stripos($userAgent, 'Mac') !== false) {
            $platform = 'Mac';
            $icon = 'laptop_mac';
        } elseif (stripos($userAgent, 'iPhone') !== false) {
            $platform = 'iPhone';
            $icon = 'phone_iphone';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $platform = 'Android';
            $icon = 'phone_android';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $platform = 'Linux';
            $icon = 'computer';
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
            'icon' => $icon,
        ];
    }
}
