<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;

class AutoReplyRule extends Model
{
    use BelongsToUser;
    
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
