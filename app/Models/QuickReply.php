<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;

class QuickReply extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'shortcut',
        'message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
