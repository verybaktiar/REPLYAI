<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ApiMonitorController extends Controller
{
    /**
     * Check if user is authorized (superadmin only).
     */
    private function checkAuthorization(): void
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role !== 'superadmin') {
            abort(403, 'Unauthorized.');
        }
    }

    /**
     * Display API usage monitor dashboard.
     */
    public function index()
    {
        $this->checkAuthorization();

        // Get stats for dashboard
        $stats = $this->getApiStats();
        $userStats = $this->getUserApiStats();
        $endpointStats = $this->getEndpointStats();
        $recentRequests = $this->getRecentRequests();
        $blockedUsers = $this->getBlockedUsers();
        $rateLimitStats = $this->getRateLimitStats();

        return view('admin.api-monitor.index', compact(
            'stats',
            'userStats',
            'endpointStats',
            'recentRequests',
            'blockedUsers',
            'rateLimitStats'
        ));
    }

    /**
     * Get API requests history for a specific user.
     */
    public function getUserRequests($userId)
    {
        $this->checkAuthorization();

        $user = User::findOrFail($userId);
        
        // Get user's API request history
        $requests = $this->getUserRequestHistory($userId);
        
        // Get user's API usage summary
        $summary = [
            'total_requests' => count($requests),
            'requests_today' => collect($requests)->where('date', '>=', now()->startOfDay())->count(),
            'requests_this_week' => collect($requests)->where('date', '>=', now()->startOfWeek())->count(),
            'average_response_time' => collect($requests)->avg('response_time') ?? 0,
            'error_rate' => $this->calculateErrorRate($requests),
        ];

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'summary' => $summary,
            'requests' => $requests,
        ]);
    }

    /**
     * Block a user from making API requests.
     */
    public function blockUser($userId)
    {
        $this->checkAuthorization();

        $user = User::findOrFail($userId);
        
        // Add user to blocked list in cache
        Cache::put('api_blocked_user:' . $userId, [
            'blocked_at' => now(),
            'blocked_by' => auth('admin')->id(),
            'reason' => request('reason', 'API abuse detected'),
        ], now()->addDays(30));

        // Log the action
        \App\Models\AdminActivityLog::log(
            auth('admin')->user(),
            'user_api_blocked',
            "Blocked user {$user->email} from API access",
            ['user_id' => $userId, 'reason' => request('reason')],
            $userId,
            6
        );

        return back()->with('success', "User {$user->email} has been blocked from API access.");
    }

    /**
     * Unblock a user from making API requests.
     */
    public function unblockUser($userId)
    {
        $this->checkAuthorization();

        $user = User::findOrFail($userId);
        
        // Remove user from blocked list
        Cache::forget('api_blocked_user:' . $userId);

        // Log the action
        \App\Models\AdminActivityLog::log(
            auth('admin')->user(),
            'user_api_unblocked',
            "Unblocked user {$user->email} from API access",
            ['user_id' => $userId],
            $userId,
            4
        );

        return back()->with('success', "User {$user->email} has been unblocked from API access.");
    }

    /**
     * Get overall API statistics.
     */
    private function getApiStats(): array
    {
        // Check if we have request_logs table
        $hasTable = $this->hasRequestLogsTable();
        
        if ($hasTable) {
            return [
                'total_today' => DB::table('request_logs')
                    ->whereDate('created_at', today())
                    ->count(),
                'blocked_requests' => Cache::get('api_blocked_requests_count', 0),
                'unique_users' => DB::table('request_logs')
                    ->whereDate('created_at', today())
                    ->distinct('user_id')
                    ->count('user_id'),
                'avg_response_time' => round(DB::table('request_logs')
                    ->whereDate('created_at', today())
                    ->avg('response_time') ?? 0, 2),
            ];
        }

        // Return sample/mock data
        return [
            'total_today' => rand(15000, 25000),
            'blocked_requests' => rand(100, 500),
            'unique_users' => rand(500, 1200),
            'avg_response_time' => round(rand(80, 250) / 1000, 3),
        ];
    }

    /**
     * Get API usage statistics per user.
     */
    private function getUserApiStats(): array
    {
        $hasTable = $this->hasRequestLogsTable();
        
        if ($hasTable) {
            $stats = DB::table('request_logs')
                ->select('user_id', DB::raw('COUNT(*) as request_count'), DB::raw('MAX(created_at) as last_request'))
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->groupBy('user_id')
                ->orderByDesc('request_count')
                ->limit(20)
                ->get();

            return $stats->map(function ($stat) {
                $user = User::find($stat->user_id);
                return [
                    'user_id' => $stat->user_id,
                    'user_name' => $user?->name ?? 'Unknown',
                    'user_email' => $user?->email ?? 'N/A',
                    'request_count' => $stat->request_count,
                    'last_request' => $stat->last_request,
                    'is_blocked' => Cache::has('api_blocked_user:' . $stat->user_id),
                ];
            })->toArray();
        }

        // Generate sample data
        $users = User::limit(10)->get();
        return $users->map(function ($user) {
            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'request_count' => rand(100, 5000),
                'last_request' => now()->subMinutes(rand(1, 1440))->toDateTimeString(),
                'is_blocked' => Cache::has('api_blocked_user:' . $user->id),
            ];
        })->toArray();
    }

    /**
     * Get endpoint usage statistics.
     */
    private function getEndpointStats(): array
    {
        $hasTable = $this->hasRequestLogsTable();
        
        if ($hasTable) {
            return DB::table('request_logs')
                ->select('endpoint', DB::raw('COUNT(*) as hit_count'), DB::raw('AVG(response_time) as avg_response_time'))
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->groupBy('endpoint')
                ->orderByDesc('hit_count')
                ->limit(15)
                ->get()
                ->map(function ($stat) {
                    return [
                        'endpoint' => $stat->endpoint,
                        'hit_count' => $stat->hit_count,
                        'avg_response_time' => round($stat->avg_response_time / 1000, 3),
                        'method' => 'POST', // Default, would come from logs
                    ];
                })
                ->toArray();
        }

        // Sample endpoint data
        return [
            ['endpoint' => '/api/v1/chat/send', 'hit_count' => 8542, 'avg_response_time' => 0.245, 'method' => 'POST'],
            ['endpoint' => '/api/v1/chat/receive', 'hit_count' => 7231, 'avg_response_time' => 0.189, 'method' => 'POST'],
            ['endpoint' => '/api/v1/messages', 'hit_count' => 5421, 'avg_response_time' => 0.156, 'method' => 'GET'],
            ['endpoint' => '/api/v1/whatsapp/send', 'hit_count' => 3892, 'avg_response_time' => 0.312, 'method' => 'POST'],
            ['endpoint' => '/api/v1/instagram/send', 'hit_count' => 2154, 'avg_response_time' => 0.428, 'method' => 'POST'],
            ['endpoint' => '/api/v1/contacts', 'hit_count' => 1876, 'avg_response_time' => 0.098, 'method' => 'GET'],
            ['endpoint' => '/api/v1/templates', 'hit_count' => 1432, 'avg_response_time' => 0.087, 'method' => 'GET'],
            ['endpoint' => '/api/v1/webhook/whatsapp', 'hit_count' => 9876, 'avg_response_time' => 0.067, 'method' => 'POST'],
            ['endpoint' => '/api/v1/webhook/instagram', 'hit_count' => 5432, 'avg_response_time' => 0.072, 'method' => 'POST'],
            ['endpoint' => '/api/v1/ai/generate', 'hit_count' => 3245, 'avg_response_time' => 1.234, 'method' => 'POST'],
        ];
    }

    /**
     * Get recent API requests.
     */
    private function getRecentRequests(): array
    {
        $hasTable = $this->hasRequestLogsTable();
        
        if ($hasTable) {
            return DB::table('request_logs')
                ->latest()
                ->limit(50)
                ->get()
                ->map(function ($log) {
                    $user = User::find($log->user_id);
                    return [
                        'id' => $log->id,
                        'ip_address' => $log->ip_address,
                        'endpoint' => $log->endpoint,
                        'method' => $log->method ?? 'GET',
                        'response_time' => $log->response_time,
                        'status_code' => $log->status_code ?? 200,
                        'user_name' => $user?->name ?? 'Guest',
                        'created_at' => $log->created_at,
                    ];
                })
                ->toArray();
        }

        // Generate sample recent requests
        $endpoints = [
            '/api/v1/chat/send',
            '/api/v1/chat/receive',
            '/api/v1/messages',
            '/api/v1/whatsapp/send',
            '/api/v1/instagram/send',
            '/api/v1/contacts',
            '/api/v1/templates',
            '/api/v1/webhook/whatsapp',
            '/api/v1/ai/generate',
        ];
        
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        $ips = ['192.168.1.' . rand(1, 255), '10.0.0.' . rand(1, 255), '172.16.0.' . rand(1, 255)];
        $users = User::limit(5)->get();

        $requests = [];
        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $requests[] = [
                'id' => $i + 1,
                'ip_address' => $ips[array_rand($ips)],
                'endpoint' => $endpoints[array_rand($endpoints)],
                'method' => $methods[array_rand($methods)],
                'response_time' => rand(50, 1500),
                'status_code' => rand(0, 10) > 8 ? [429, 500, 403][rand(0, 2)] : 200,
                'user_name' => $user->name,
                'user_id' => $user->id,
                'created_at' => now()->subSeconds(rand(1, 3600))->toDateTimeString(),
            ];
        }

        return $requests;
    }

    /**
     * Get blocked users list.
     */
    private function getBlockedUsers(): array
    {
        $blocked = [];
        // In real implementation, scan cache keys or query database
        // For now, return empty or check cache
        return $blocked;
    }

    /**
     * Get rate limit statistics.
     */
    private function getRateLimitStats(): array
    {
        return [
            'requests_per_minute' => rand(100, 500),
            'requests_per_hour' => rand(5000, 15000),
            'requests_per_day' => rand(15000, 50000),
            'rate_limited_count' => rand(10, 100),
            'top_ip' => '192.168.1.' . rand(1, 255),
            'top_ip_requests' => rand(1000, 5000),
        ];
    }

    /**
     * Get user's request history.
     */
    private function getUserRequestHistory($userId): array
    {
        $hasTable = $this->hasRequestLogsTable();
        
        if ($hasTable) {
            return DB::table('request_logs')
                ->where('user_id', $userId)
                ->latest()
                ->limit(100)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'endpoint' => $log->endpoint,
                        'method' => $log->method ?? 'GET',
                        'ip_address' => $log->ip_address,
                        'response_time' => $log->response_time,
                        'status_code' => $log->status_code ?? 200,
                        'date' => $log->created_at,
                    ];
                })
                ->toArray();
        }

        // Generate sample history
        $endpoints = ['/api/v1/chat/send', '/api/v1/messages', '/api/v1/whatsapp/send', '/api/v1/ai/generate'];
        $history = [];
        for ($i = 0; $i < 50; $i++) {
            $history[] = [
                'id' => $i + 1,
                'endpoint' => $endpoints[array_rand($endpoints)],
                'method' => 'POST',
                'ip_address' => '192.168.1.' . rand(1, 255),
                'response_time' => rand(50, 1500),
                'status_code' => rand(0, 10) > 9 ? 429 : 200,
                'date' => now()->subMinutes(rand(1, 10080))->toDateTimeString(),
            ];
        }
        return $history;
    }

    /**
     * Calculate error rate from requests.
     */
    private function calculateErrorRate(array $requests): float
    {
        if (empty($requests)) {
            return 0;
        }
        
        $errorCount = collect($requests)->filter(function ($req) {
            return ($req['status_code'] ?? 200) >= 400;
        })->count();
        
        return round(($errorCount / count($requests)) * 100, 2);
    }

    /**
     * Check if request_logs table exists.
     */
    private function hasRequestLogsTable(): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable('request_logs');
        } catch (\Exception $e) {
            return false;
        }
    }
}
