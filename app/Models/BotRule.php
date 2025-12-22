<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotRule extends Model
{
    protected $fillable = [
        'keyword',
        'match_type',
        'reply_text',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(BotLog::class, 'bot_rule_id');
    }
}
