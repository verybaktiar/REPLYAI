<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'chatwoot_id',
        'instagram_message_id',   // BARU - untuk Meta API
        'sender_type',            // 'contact' atau 'agent'
        'conversation_id',
        'sender_type',
        'content',
        'source',                 // BARU - 'chatwoot' atau 'meta_direct'
        'message_created_at',
        'is_replied_by_bot',
        'sent_at',                // BARU - timestamp fleksibel
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
