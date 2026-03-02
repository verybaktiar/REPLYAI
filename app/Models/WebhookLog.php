<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'provider',
        'url',
        'payload',
        'response',
        'status',
        'status_code',
        'headers',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'headers' => 'array',
        'processed_at' => 'datetime',
    ];

    public static function getStatuses(): array
    {
        return ['pending', 'processing', 'success', 'failed'];
    }
}
