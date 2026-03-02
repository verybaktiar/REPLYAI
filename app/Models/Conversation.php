<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;

/**
 * Class Conversation
 * 
 * Model untuk menyimpan conversation Instagram.
 * 
 * SECURITY NOTES:
 * - user_id TIDAK ADA di $fillable
 * - user_id di-set otomatis via BelongsToUser trait
 * - instagram_user_id harus unique per user
 */
class Conversation extends Model
{
    use BelongsToUser;

    /**
     * The attributes that are mass assignable.
     * 
     * SECURITY: user_id dihapus dari fillable
     *
     * @var array<string>
     */
    protected $fillable = [
        'chatwoot_id',
        'instagram_account_id',
        'instagram_user_id',
        'ig_username',
        'display_name',
        'avatar',
        'last_message',
        'source',
        'last_activity_at',
        'status',
        'waiting_for',
        'greeted_at',
        'has_sent_welcome',
        'last_menu_sent_at',
        'tags',
        'agent_replied_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_activity_at' => 'integer',
        'tags' => 'array',
        'agent_replied_at' => 'datetime',
        'has_sent_welcome' => 'boolean',
    ];

    /**
     * Valid status values
     *
     * @var array<string>
     */
    public const VALID_STATUSES = [
        'open',
        'resolved',
        'pending',
        'snoozed',
        'bot_handling',
        'agent_handling',
        'needs_attention',
    ];

    /**
     * Get messages for this conversation
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get logs for this conversation
     *
     * @return HasMany
     */
    public function logs()
    {
        return $this->hasMany(AutoReplyLog::class);
    }

    /**
     * Get the Instagram account this conversation belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instagramAccount()
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    /**
     * Get media attachments for this conversation
     */
    public function media()
    {
        return $this->morphMany(ChatMedia::class, 'conversation');
    }

    /**
     * Get conversation media attachments
     */
    public function conversationMedia()
    {
        return $this->morphMany(ChatMedia::class, 'conversation');
    }

    /**
     * Scope for active conversations
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'bot_handling', 'agent_handling']);
    }

    /**
     * Scope for conversations needing attention
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedsAttention($query)
    {
        return $query->where('status', 'needs_attention');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // SECURITY: Validate status
        static::creating(function ($model) {
            if (!in_array($model->status, self::VALID_STATUSES)) {
                $model->status = 'open';
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('status') && !in_array($model->status, self::VALID_STATUSES)) {
                $model->status = $model->getOriginal('status');
            }

            // SECURITY: Prevent user_id changes
            if ($model->isDirty('user_id')) {
                $model->user_id = $model->getOriginal('user_id');
            }
        });
    }
}
