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
        // Jangan filter jika dijalankan dari console atau queue
        if (app()->runningInConsole()) {
            return;
        }

        // Jangan filter jika Super Admin (guard admin) sedang login
        // Ini memastikan Admin Panel bisa melihat semua data
        if (Auth::guard('admin')->check()) {
            return;
        }

        // Filter berdasarkan user_id jika user biasa (guard web) sedang login
        if (Auth::guard('web')->check()) {
            $builder->where($model->getTable() . '.user_id', Auth::guard('web')->id());
        }
    }
}
