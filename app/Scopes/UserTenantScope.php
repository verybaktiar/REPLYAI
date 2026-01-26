<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Global Scope untuk Multi-Tenant
 * Secara otomatis filter semua query berdasarkan user yang login
 */
class UserTenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Hanya apply jika user sudah login dan bukan dari console/queue
        if (Auth::check() && !app()->runningInConsole()) {
            $builder->where($model->getTable() . '.user_id', Auth::id());
        }
    }
}
