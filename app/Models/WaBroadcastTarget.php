<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaBroadcastTarget extends Model
{
    protected $fillable = [
        'wa_broadcast_id',
        'phone_number',
        'status',
        'sent_at',
        'error_message'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(WaBroadcast::class, 'wa_broadcast_id');
    }
}
