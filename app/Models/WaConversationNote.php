<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToUser;

class WaConversationNote extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = ['wa_conversation_id', 'user_id', 'content', 'is_internal'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WaConversation::class, 'wa_conversation_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
