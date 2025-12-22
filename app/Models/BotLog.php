<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotLog extends Model
{
    protected $fillable = [
        'conversation_id',
        'message_id',
        'bot_rule_id',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(BotRule::class, 'bot_rule_id');
    }
}
