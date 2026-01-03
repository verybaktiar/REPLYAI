<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    protected $fillable = [
        'wa_message_id',
        'remote_jid',
        'phone_number',
        'push_name',
        'direction',
        'message',
        'message_type',
        'status',
        'bot_reply',
        'metadata',
        'wa_timestamp',
    ];

    protected $casts = [
        'metadata' => 'array',
        'wa_timestamp' => 'datetime',
    ];

    /**
     * Scope for incoming messages
     */
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    /**
     * Scope for outgoing messages
     */
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    /**
     * Scope for messages from a specific phone number
     */
    public function scopeFromPhone($query, string $phone)
    {
        return $query->where('phone_number', $phone);
    }

    /**
     * Get formatted phone number (with +)
     */
    public function getFormattedPhoneAttribute(): string
    {
        return '+' . $this->phone_number;
    }

    /**
     * Check if message is from user (incoming)
     */
    public function isFromUser(): bool
    {
        return $this->direction === 'incoming';
    }

    /**
     * Check if message is from bot (outgoing)
     */
    public function isFromBot(): bool
    {
        return $this->direction === 'outgoing';
    }
}
