<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'chatwoot_id',
        'instagram_user_id',      // BARU - untuk Meta API
        'ig_username',
        'display_name',
        'avatar',
        'last_message',
        'source',                 // BARU - 'chatwoot' atau 'meta_direct'
        'last_activity_at',       // BARU
        'status',                 // BARU
        'waiting_for',
        'greeted_at',
        'has_sent_welcome',       // FIX - agar flag tersimpan
        'last_menu_sent_at',      // FIX - agar timestamp tersimpan
        'tags',                   // BARU - JSON
        'agent_replied_at',       // BARU - untuk handoff timeout
    ];

    protected $casts = [
        'last_activity_at' => 'integer', // unix timestamp dari chatwoot
        'tags' => 'array',
        'agent_replied_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
    public function logs()
    {
        return $this->hasMany(AutoReplyLog::class);
    }

}
