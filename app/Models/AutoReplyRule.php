<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReplyRule extends Model
{
    protected $table = 'auto_reply_rules';

    protected $fillable = [
        'trigger_keyword',
        'response_text',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];
}
