<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaSession extends Model
{
    protected $fillable = [
        'phone_number',
        'session_id',
        'status',
        'credentials',
        'name',
        'last_connected_at',
        'auto_reply_enabled',
        'takeover_timeout_minutes',
        'idle_warning_minutes',
        'session_idle_timeout_minutes',
        'session_followup_timeout_minutes',
    ];

    protected $casts = [
        'last_connected_at' => 'datetime',
        'auto_reply_enabled' => 'boolean',
    ];

    /**
     * Get the messages for this session
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class, 'phone_number', 'phone_number');
    }

    /**
     * Check if session is connected
     */
    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    /**
     * Get or create default session
     */
    public static function getDefault(): self
    {
        return self::firstOrCreate(
            ['session_id' => 'default'],
            ['status' => 'disconnected']
        );
    }
}
