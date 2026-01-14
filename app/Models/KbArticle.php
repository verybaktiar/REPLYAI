<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbArticle extends Model
{
    protected $table = 'kb_articles';

    protected $fillable = [
        'title',
        'content',
        'source_url',
        'tags',
        'is_active',
        'business_profile_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the business profile this KB article belongs to.
     * NULL = available to all profiles
     */
    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }
}
