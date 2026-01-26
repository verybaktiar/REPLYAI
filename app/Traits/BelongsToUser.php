<?php

namespace App\Traits;

use App\Scopes\UserTenantScope;
use Illuminate\Support\Facades\Auth;

/**
 * Trait untuk Multi-Tenant Models
 * Gunakan trait ini di model yang perlu difilter per user
 */
trait BelongsToUser
{
    /**
     * Boot the trait - apply global scope
     */
    protected static function bootBelongsToUser(): void
    {
        // Auto-filter query berdasarkan user yang login
        static::addGlobalScope(new UserTenantScope);

        // Auto-set user_id saat create
        static::creating(function ($model) {
            if (Auth::check() && empty($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });
    }

    /**
     * Get the user that owns this model
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scope query ke user tertentu (bypass global scope)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->withoutGlobalScope(UserTenantScope::class)
                     ->where('user_id', $userId);
    }

    /**
     * Scope untuk semua data (bypass global scope) - untuk admin
     */
    public function scopeAllTenants($query)
    {
        return $query->withoutGlobalScope(UserTenantScope::class);
    }
}
