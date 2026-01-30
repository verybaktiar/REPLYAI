<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToUser;

class WaBroadcastTarget extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
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
