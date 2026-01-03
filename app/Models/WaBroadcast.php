<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaBroadcast extends Model
{
    protected $fillable = [
        'title',
        'message',
        'media_path',
        'status',
        'scheduled_at',
        'filters'
    ];

    protected $casts = [
        'filters' => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function targets(): HasMany
    {
        return $this->hasMany(WaBroadcastTarget::class);
    }
}
