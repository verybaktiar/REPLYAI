<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;

class WebWidget extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'name',
        'api_key',
        'domain',
        'welcome_message',
        'bot_name',
        'bot_avatar',
        'primary_color',
        'position',
        'is_active',
        'settings',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Boot method untuk auto-generate API key
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($widget) {
            if (empty($widget->api_key)) {
                $widget->api_key = 'rw_' . Str::random(32);
            }
        });
    }

    /**
     * Get all conversations for this widget
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(WebConversation::class, 'widget_id');
    }

    /**
     * Get embed script HTML
     */
    public function getEmbedCodeAttribute(): string
    {
        $baseUrl = config('app.url');
        return "<script src=\"{$baseUrl}/widget/replyai-widget.js\" data-api-key=\"{$this->api_key}\"></script>";
    }
}
