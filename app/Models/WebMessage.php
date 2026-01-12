<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebMessage extends Model
{
    protected $fillable = [
        'web_conversation_id',
        'sender_type',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the conversation that owns this message
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WebConversation::class, 'web_conversation_id');
    }

    /**
     * Check if message is from visitor
     */
    public function isFromVisitor(): bool
    {
        return $this->sender_type === 'visitor';
    }

    /**
     * Check if message is from bot
     */
    public function isFromBot(): bool
    {
        return $this->sender_type === 'bot';
    }

    /**
     * Check if message is from agent
     */
    public function isFromAgent(): bool
    {
        return $this->sender_type === 'agent';
    }
}
