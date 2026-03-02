<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;

/**
 * Class WaConversation
 * 
 * Model untuk mengelola conversation WhatsApp.
 * 
 * SECURITY NOTES:
 * - user_id TIDAK ADA di $fillable
 * - user_id di-set otomatis via BelongsToUser trait
 * - Status hanya bisa nilai yang valid
 */
class WaConversation extends Model
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
        'phone_number',
        'display_name',
        'status',
        'session_status',
        'assigned_cs',
        'takeover_at',
        'last_cs_reply_at',
        'last_user_reply_at',
        'followup_sent_at',
        'followup_count',
        'stop_autofollowup',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'takeover_at' => 'datetime',
        'last_cs_reply_at' => 'datetime',
        'last_user_reply_at' => 'datetime',
        'followup_sent_at' => 'datetime',
        'stop_autofollowup' => 'boolean',
    ];

    /**
     * Session status constants
     */
    const SESSION_ACTIVE = 'active';
    const SESSION_FOLLOWUP_SENT = 'followup_sent';
    const SESSION_CLOSED = 'closed';

    /**
     * Valid session statuses
     */
    public const VALID_SESSION_STATUSES = [
        self::SESSION_ACTIVE,
        self::SESSION_FOLLOWUP_SENT,
        self::SESSION_CLOSED,
    ];

    /**
     * Status constants
     */
    const STATUS_BOT_ACTIVE = 'bot_active';
    const STATUS_AGENT_HANDLING = 'agent_handling';
    const STATUS_IDLE = 'idle';

    /**
     * Valid statuses
     */
    public const VALID_STATUSES = [
        self::STATUS_BOT_ACTIVE,
        self::STATUS_AGENT_HANDLING,
        self::STATUS_IDLE,
    ];

    /**
     * Get the tags for the conversation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Get the notes for the conversation.
     *
     * @return HasMany
     */
    public function notes(): HasMany
    {
        return $this->hasMany(WaConversationNote::class, 'wa_conversation_id');
    }

    /**
     * Check if bot is active
     *
     * @return bool
     */
    public function isBotActive(): bool
    {
        return $this->status === self::STATUS_BOT_ACTIVE;
    }

    /**
     * Check if agent is handling
     *
     * @return bool
     */
    public function isAgentHandling(): bool
    {
        return $this->status === self::STATUS_AGENT_HANDLING;
    }

    /**
     * Check if idle
     *
     * @return bool
     */
    public function isIdle(): bool
    {
        return $this->status === self::STATUS_IDLE;
    }

    /**
     * Get remaining minutes before auto-handback
     *
     * @return int|null
     */
    public function getRemainingMinutesAttribute(): ?int
    {
        if (!$this->last_cs_reply_at || $this->status === self::STATUS_BOT_ACTIVE) {
            return null;
        }

        $session = WaSession::where('user_id', $this->user_id)->first();
        $timeout = $session?->takeover_timeout_minutes ?? 60;
        $elapsed = now()->diffInMinutes($this->last_cs_reply_at);
        
        return max(0, $timeout - $elapsed);
    }

    /**
     * Get idle duration in minutes
     *
     * @return int|null
     */
    public function getIdleMinutesAttribute(): ?int
    {
        if (!$this->last_cs_reply_at) {
            return $this->takeover_at ? now()->diffInMinutes($this->takeover_at) : null;
        }
        
        return now()->diffInMinutes($this->last_cs_reply_at);
    }

    /**
     * Get messages for this conversation
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class, 'phone_number', 'phone_number');
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
     * Get or create conversation for phone number
     *
     * @param string $phoneNumber
     * @param string|null $displayName
     * @return self
     */
    public static function getOrCreate(string $phoneNumber, ?string $displayName = null): self
    {
        return self::firstOrCreate(
            [
                'phone_number' => $phoneNumber,
                'user_id' => auth()->id(), // SECURITY: Ensure user_id is set
            ],
            [
                'display_name' => $displayName,
                'status' => self::STATUS_BOT_ACTIVE,
            ]
        );
    }

    /**
     * Take over conversation
     *
     * @param string $csName
     * @return void
     */
    public function takeover(string $csName = 'Admin'): void
    {
        $this->update([
            'status' => self::STATUS_AGENT_HANDLING,
            'assigned_cs' => $csName,
            'takeover_at' => now(),
            'last_cs_reply_at' => now(),
        ]);
    }

    /**
     * Handback to bot
     *
     * @return void
     */
    public function handback(): void
    {
        $this->update([
            'status' => self::STATUS_BOT_ACTIVE,
            'assigned_cs' => null,
            'takeover_at' => null,
            'last_cs_reply_at' => null,
        ]);
    }

    /**
     * Update last CS reply time
     *
     * @return void
     */
    public function markCsReply(): void
    {
        $this->update(['last_cs_reply_at' => now()]);
    }

    /**
     * Scope for agent handling conversations
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAgentHandling($query)
    {
        return $query->where('status', self::STATUS_AGENT_HANDLING);
    }

    /**
     * Scope for conversations needing handback
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $timeoutMinutes
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedsHandback($query, int $timeoutMinutes)
    {
        return $query->where('status', self::STATUS_AGENT_HANDLING)
            ->where('last_cs_reply_at', '<', now()->subMinutes($timeoutMinutes));
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // SECURITY: Validate status values
        static::creating(function ($model) {
            if (!in_array($model->status, self::VALID_STATUSES)) {
                $model->status = self::STATUS_BOT_ACTIVE;
            }

            if (!in_array($model->session_status, self::VALID_SESSION_STATUSES)) {
                $model->session_status = self::SESSION_ACTIVE;
            }
        });

        static::updating(function ($model) {
            // Validate status changes
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
