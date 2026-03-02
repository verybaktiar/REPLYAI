<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToUser;

class ChatAutomation extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'message',
        'is_active',
        'away_start_time',
        'away_end_time',
        'away_days',
        'keywords',
        'match_type',
        'delay_hours',
        'trigger_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'away_start_time' => 'datetime',
        'away_end_time' => 'datetime',
        'away_days' => 'array',
        'keywords' => 'array',
        'delay_hours' => 'integer',
        'trigger_count' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * Type constants
     */
    const TYPE_AUTO_REPLY = 'auto_reply';
    const TYPE_AWAY_MESSAGE = 'away_message';
    const TYPE_WELCOME = 'welcome';
    const TYPE_KEYWORD = 'keyword';
    const TYPE_FOLLOW_UP = 'follow_up';

    /**
     * Match type constants
     */
    const MATCH_EXACT = 'exact';
    const MATCH_CONTAINS = 'contains';
    const MATCH_STARTS_WITH = 'starts_with';
    const MATCH_REGEX = 'regex';

    /**
     * Get the user who owns this automation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for active automations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive automations
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for auto reply type
     */
    public function scopeAutoReply($query)
    {
        return $query->where('type', self::TYPE_AUTO_REPLY);
    }

    /**
     * Scope for away message type
     */
    public function scopeAwayMessage($query)
    {
        return $query->where('type', self::TYPE_AWAY_MESSAGE);
    }

    /**
     * Scope for welcome message type
     */
    public function scopeWelcome($query)
    {
        return $query->where('type', self::TYPE_WELCOME);
    }

    /**
     * Scope for keyword type
     */
    public function scopeKeyword($query)
    {
        return $query->where('type', self::TYPE_KEYWORD);
    }

    /**
     * Increment trigger count
     */
    public function incrementTriggerCount(): void
    {
        $this->increment('trigger_count');
        $this->update(['last_triggered_at' => now()]);
    }

    /**
     * Check if automation matches given text
     */
    public function matches(string $text): bool
    {
        if (empty($this->keywords)) {
            return false;
        }

        $text = strtolower($text);

        foreach ($this->keywords as $keyword) {
            $keyword = strtolower($keyword);

            $matches = match ($this->match_type) {
                self::MATCH_EXACT => $text === $keyword,
                self::MATCH_STARTS_WITH => str_starts_with($text, $keyword),
                self::MATCH_REGEX => (bool) preg_match($keyword, $text),
                default => str_contains($text, $keyword),
            };

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if currently in away hours
     */
    public function isInAwayHours(): bool
    {
        if ($this->type !== self::TYPE_AWAY_MESSAGE || empty($this->away_days)) {
            return false;
        }

        $now = now();
        $currentDay = strtolower($now->format('l'));

        if (!in_array($currentDay, $this->away_days)) {
            return false;
        }

        $currentTime = $now->format('H:i');
        $startTime = $this->away_start_time?->format('H:i');
        $endTime = $this->away_end_time?->format('H:i');

        if ($startTime && $endTime) {
            return $currentTime >= $startTime && $currentTime <= $endTime;
        }

        return true;
    }
}
