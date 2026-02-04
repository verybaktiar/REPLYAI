<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;

class WaConversation extends Model
{
    use BelongsToUser;
    
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
        'user_id',
    ];

    protected $casts = [
        'takeover_at' => 'datetime',
        'last_cs_reply_at' => 'datetime',
        'last_user_reply_at' => 'datetime',
        'followup_sent_at' => 'datetime',
    ];

    /**
     * Session status constants
     */
    const SESSION_ACTIVE = 'active';
    const SESSION_FOLLOWUP_SENT = 'followup_sent';
    const SESSION_CLOSED = 'closed';

    /**
     * Status constants
     */
    const STATUS_BOT_ACTIVE = 'bot_active';
    const STATUS_AGENT_HANDLING = 'agent_handling';
    const STATUS_IDLE = 'idle';

    /**
     * Get the tags for the conversation.
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Get the notes for the conversation.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(WaConversationNote::class, 'wa_conversation_id');
    }

    /**
     * Check if bot is active
     */
    public function isBotActive(): bool
    {
        return $this->status === self::STATUS_BOT_ACTIVE;
    }

    /**
     * Check if agent is handling
     */
    public function isAgentHandling(): bool
    {
        return $this->status === self::STATUS_AGENT_HANDLING;
    }

    /**
     * Check if idle
     */
    public function isIdle(): bool
    {
        return $this->status === self::STATUS_IDLE;
    }

    /**
     * Get remaining minutes before auto-handback
     */
    public function getRemainingMinutesAttribute(): ?int
    {
        if (!$this->last_cs_reply_at || $this->status === self::STATUS_BOT_ACTIVE) {
            return null;
        }

        $session = WaSession::getDefault();
        $timeout = $session->takeover_timeout_minutes ?? 60;
        $elapsed = now()->diffInMinutes($this->last_cs_reply_at);
        
        return max(0, $timeout - $elapsed);
    }

    /**
     * Get idle duration in minutes
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
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class, 'phone_number', 'phone_number');
    }

    /**
     * Get or create conversation for phone number
     */
    public static function getOrCreate(string $phoneNumber, ?string $displayName = null): self
    {
        return self::firstOrCreate(
            ['phone_number' => $phoneNumber],
            [
                'display_name' => $displayName,
                'status' => self::STATUS_BOT_ACTIVE,
            ]
        );
    }

    /**
     * Take over conversation
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
     */
    public function markCsReply(): void
    {
        $this->update(['last_cs_reply_at' => now()]);
    }

    /**
     * Scope for agent handling conversations
     */
    public function scopeAgentHandling($query)
    {
        return $query->where('status', self::STATUS_AGENT_HANDLING);
    }

    /**
     * Scope for conversations needing handback
     */
    public function scopeNeedsHandback($query, int $timeoutMinutes)
    {
        return $query->where('status', self::STATUS_AGENT_HANDLING)
            ->where('last_cs_reply_at', '<', now()->subMinutes($timeoutMinutes));
    }
}
