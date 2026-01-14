<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppDevice extends Model
{
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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
    ];

    // Status constants
    const STATUS_CONNECTED = 'connected';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_SCANNING = 'scanning';
    const STATUS_UNKNOWN = 'unknown';

    /**
     * Get the business profile assigned to this device.
     */
    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }
}
