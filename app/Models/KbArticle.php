<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    protected $table = 'kb_articles';

    protected $fillable = [
        'title',
        'content',
        'source_url',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
