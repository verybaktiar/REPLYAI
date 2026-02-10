<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToUser;

class WhatsAppDevice extends Model
{
    use BelongsToUser;

    protected $table = 'whatsapp_devices';

    protected $fillable = [
        'session_id',
        'device_name',
        'phone_number',
        'profile_name',
        'status',
        'last_disconnect_reason',
        'last_connected_at',
        'is_active',
        'business_profile_id',
        'user_id',
    ];

    /**
     * Check if the device is currently connected.
     */
    public function isConnected(): bool
    {
        return strtolower($this->status) === self::STATUS_CONNECTED;
    }

    protected $casts = [
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
    ];

    protected $appends = ['color'];

    // Status constants
    const STATUS_CONNECTED = 'connected';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_SCANNING = 'scanning';
    const STATUS_UNKNOWN = 'unknown';

    // Device colors for inbox display
    const DEVICE_COLORS = [
        '#25D366', // WhatsApp Green
        '#3B82F6', // Blue
        '#F59E0B', // Amber
        '#EF4444', // Red
        '#8B5CF6', // Purple
        '#EC4899', // Pink
        '#14B8A6', // Teal
        '#F97316', // Orange
    ];

    /**
     * Get the business profile assigned to this device.
     */
    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    /**
     * Get messages for this device.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class, 'session_id', 'session_id');
    }

    /**
     * Get color for this device based on its ID.
     */
    public function getColorAttribute(): string
    {
        $index = ($this->id - 1) % count(self::DEVICE_COLORS);
        return self::DEVICE_COLORS[$index];
    }
}

