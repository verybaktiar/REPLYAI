<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'properties',
        'ip_address',
        'user_agent',
        'is_impersonated',
        'severity',
    ];

    protected $casts = [
        'properties' => 'array',
        'is_impersonated' => 'boolean',
    ];

    /**
     * Action Categories
     */
    const CATEGORY_AUTH = 'auth';
    const CATEGORY_USER = 'user';
    const CATEGORY_SUBSCRIPTION = 'subscription';
    const CATEGORY_CHANNEL = 'channel';
    const CATEGORY_AI = 'ai';
    const CATEGORY_KB = 'kb';
    const CATEGORY_RULE = 'rule';
    const CATEGORY_ADMIN = 'admin';

    /**
     * Common Actions
     */
    const ACTION_LOGIN = 'auth.login';
    const ACTION_LOGOUT = 'auth.logout';
    const ACTION_PASSWORD_RESET = 'auth.password_reset';
    const ACTION_PLAN_UPGRADED = 'subscription.upgraded';
    const ACTION_PLAN_DOWNGRADED = 'subscription.downgraded';
    const ACTION_PAYMENT_MADE = 'subscription.payment_made';
    const ACTION_WA_CONNECTED = 'channel.wa_connected';
    const ACTION_WA_DISCONNECTED = 'channel.wa_disconnected';
    const ACTION_IG_CONNECTED = 'channel.ig_connected';
    const ACTION_AI_MESSAGE = 'ai.message_sent';
    const ACTION_AI_LIMIT_REACHED = 'ai.limit_reached';
    const ACTION_KB_CREATED = 'kb.created';
    const ACTION_KB_UPDATED = 'kb.updated';
    const ACTION_KB_DELETED = 'kb.deleted';
    const ACTION_RULE_CREATED = 'rule.created';
    const ACTION_RULE_TOGGLED = 'rule.toggled';
    const ACTION_IMPERSONATION_START = 'admin.impersonation_start';
    const ACTION_IMPERSONATION_END = 'admin.impersonation_end';
    const ACTION_USER_SUSPENDED = 'admin.user_suspended';
    const ACTION_USER_ACTIVATED = 'admin.user_activated';

    /**
     * User who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin who performed the action (for admin actions)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * The model that was affected
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Scope by action type
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('action', 'like', $category . '.%');
    }

    /**
     * Scope for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for admin actions only
     */
    public function scopeAdminActions($query)
    {
        return $query->whereNotNull('admin_id');
    }

    /**
     * Get action icon
     */
    public function getIconAttribute(): string
    {
        return match(explode('.', $this->action)[0] ?? '') {
            'auth' => 'login',
            'subscription' => 'credit_card',
            'channel' => 'phone_android',
            'ai' => 'smart_toy',
            'kb' => 'menu_book',
            'rule' => 'rule',
            'admin' => 'admin_panel_settings',
            default => 'history',
        };
    }

    /**
     * Get severity color
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'error' => 'red',
            'warning' => 'yellow',
            'success' => 'green',
            default => 'slate',
        };
    }
}
