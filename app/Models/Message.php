<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'chatwoot_id',
        'conversation_id',
        'sender_type',
        'content',
        'message_created_at',
        'is_replied_by_bot',
    ];

    protected $casts = [
        'message_created_at' => 'integer',
        'is_replied_by_bot' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
