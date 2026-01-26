<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;

class Conversation extends Model
{
    use BelongsToUser;
    protected $fillable = [
        'chatwoot_id',
        'user_id',                // User owner
        'instagram_account_id',   // Link ke akun IG bisnis yang spesifik
        'instagram_user_id',      // BARU - untuk Meta API (contact ID)
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
    
    /**
     * Get the Instagram account this conversation belongs to
     */
    public function instagramAccount()
    {
        return $this->belongsTo(InstagramAccount::class);
    }

}
