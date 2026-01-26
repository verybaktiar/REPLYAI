<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;
use Illuminate\Support\Facades\Auth;

class WaSession extends Model
{
    use BelongsToUser;
    
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
        'user_id',
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
     * Get or create default session for current user
     * Fixed for multi-tenant: each user has their own default session
     */
    public static function getDefault(): ?self
    {
        // Ambil session pertama user, atau null jika belum ada
        return self::first();
    }
    
    /**
     * Create new session for user
     */
    public static function createForUser(array $data = []): self
    {
        return self::create(array_merge([
            'session_id' => 'user_' . Auth::id() . '_' . time(),
            'status' => 'disconnected',
            'user_id' => Auth::id(),
        ], $data));
    }
}

