<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReplyLog extends Model
{
    protected $fillable = [
        'conversation_id',
        'message_id',
        'rule_id',
        'trigger_text',
        'response_text',
        'status',
        'error_message',
        'response_source',
        'ai_confidence',
        'ai_sources',
    ];
    protected $casts = [
        'ai_confidence' => 'float',
        'ai_sources' => 'array',
    ];


    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function rule()
    {
        return $this->belongsTo(AutoReplyRule::class, 'rule_id');
    }
}
