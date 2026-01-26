<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $fillable = [
        'flag_key', 'name', 'description', 'is_enabled', 
        'scope', 'allowed_plans', 'allowed_users', 'rollout_percentage'
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'allowed_plans' => 'array',
        'allowed_users' => 'array',
        'rollout_percentage' => 'integer',
    ];

    /**
     * Check if a feature is enabled for a user
     */
    public static function isEnabled(string $key, ?User $user = null): bool
    {
        $flag = Cache::remember("feature_flag:{$key}", 300, function() use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$flag || !$flag->is_enabled) {
            return false;
        }

        if ($flag->scope === 'global') {
            return $flag->rollout_percentage >= rand(1, 100);
        }

        if (!$user) {
            return false;
        }

        if ($flag->scope === 'user' && $flag->allowed_users) {
            return in_array($user->id, $flag->allowed_users);
        }

        if ($flag->scope === 'plan' && $flag->allowed_plans) {
            $userPlan = $user->subscription?->plan?->slug;
            return $userPlan && in_array($userPlan, $flag->allowed_plans);
        }

        return false;
    }
}
