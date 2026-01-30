<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;

class TakeoverLog extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'platform',
        'conversation_id',
        'customer_name',
        'action',
        'actor',
        'idle_duration_minutes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Platform constants
     */
    const PLATFORM_WHATSAPP = 'whatsapp';
    const PLATFORM_INSTAGRAM = 'instagram';

    /**
     * Action constants
     */
    const ACTION_TAKEOVER = 'takeover';
    const ACTION_HANDBACK = 'handback';
    const ACTION_AUTO_HANDBACK = 'auto_handback';
    const ACTION_CS_REPLY = 'cs_reply';

    /**
     * Log a takeover action
     */
    public static function logTakeover(string $platform, string $conversationId, ?string $customerName, string $actor = 'Admin', ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'platform' => $platform,
            'conversation_id' => $conversationId,
            'customer_name' => $customerName,
            'action' => self::ACTION_TAKEOVER,
            'actor' => $actor,
        ]);
    }

    /**
     * Log a handback action
     */
    public static function logHandback(string $platform, string $conversationId, ?string $customerName, string $actor = 'Admin', ?int $idleMinutes = null, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'platform' => $platform,
            'conversation_id' => $conversationId,
            'customer_name' => $customerName,
            'action' => self::ACTION_HANDBACK,
            'actor' => $actor,
            'idle_duration_minutes' => $idleMinutes,
        ]);
    }

    /**
     * Log an auto-handback action
     */
    public static function logAutoHandback(string $platform, string $conversationId, ?string $customerName, int $idleMinutes, ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'platform' => $platform,
            'conversation_id' => $conversationId,
            'customer_name' => $customerName,
            'action' => self::ACTION_AUTO_HANDBACK,
            'actor' => 'system',
            'idle_duration_minutes' => $idleMinutes,
        ]);
    }

    /**
     * Log a CS reply action
     */
    public static function logCsReply(string $platform, string $conversationId, ?string $customerName, string $actor = 'Admin', ?int $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'platform' => $platform,
            'conversation_id' => $conversationId,
            'customer_name' => $customerName,
            'action' => self::ACTION_CS_REPLY,
            'actor' => $actor,
        ]);
    }

    /**
     * Get action label in Indonesian
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_TAKEOVER => 'Ambil Alih',
            self::ACTION_HANDBACK => 'Kembalikan ke Bot',
            self::ACTION_AUTO_HANDBACK => 'Auto Handback',
            self::ACTION_CS_REPLY => 'CS Balas',
            default => $this->action,
        };
    }

    /**
     * Get platform label
     */
    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform) {
            self::PLATFORM_WHATSAPP => 'WhatsApp',
            self::PLATFORM_INSTAGRAM => 'Instagram',
            default => $this->platform,
        };
    }

    /**
     * Scope for platform
     */
    public function scopeForPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for WhatsApp
     */
    public function scopeWhatsApp($query)
    {
        return $query->where('platform', self::PLATFORM_WHATSAPP);
    }

    /**
     * Scope for Instagram
     */
    public function scopeInstagram($query)
    {
        return $query->where('platform', self::PLATFORM_INSTAGRAM);
    }
}
