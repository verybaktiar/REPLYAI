<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'chatwoot_id',
        'ig_username',
        'display_name',
        'avatar',
        'last_message',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'integer', // unix timestamp dari chatwoot
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
