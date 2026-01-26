<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'endpoint', 'method', 'response_code', 
        'response_time_ms', 'ip_address', 'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public static function log($userId, $endpoint, $method, $responseCode = null, $responseTime = null, $ip = null)
    {
        return self::create([
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'method' => $method,
            'response_code' => $responseCode,
            'response_time_ms' => $responseTime,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }

    public static function getUsageForUser($userId, $days = 30)
    {
        return self::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }
}
