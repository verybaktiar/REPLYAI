<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToUser;

class WebConversation extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'widget_id',
        'visitor_id',
        'visitor_name',
        'visitor_email',
        'visitor_ip',
        'visitor_user_agent',
        'page_url',
        'last_message',
        'status',
        'last_activity_at',
        'tags',
        'user_id',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'tags' => 'array',
    ];

    /**
     * Get the widget that owns this conversation
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(WebWidget::class, 'widget_id');
    }

    /**
     * Get all messages for this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WebMessage::class);
    }

    /**
     * Get display name for the visitor
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->visitor_name) {
            return $this->visitor_name;
        }
        return 'Visitor #' . substr($this->visitor_id, 0, 8);
    }

    /**
     * Check if conversation is handled by bot
     */
    public function isHandledByBot(): bool
    {
        return $this->status === 'bot';
    }

    /**
     * Check if conversation is handled by CS
     */
    public function isHandledByCs(): bool
    {
        return in_array($this->status, ['cs', 'escalated']);
    }
}
