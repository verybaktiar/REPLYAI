<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToUser;

class KbMissedQuery extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'business_profile_id',
        'question',
        'count',
        'status',
        'resolved_by_kb_id',
        'last_asked_at',
    ];

    protected $casts = [
        'count' => 'integer',
        'last_asked_at' => 'datetime',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function resolvedByKb(): BelongsTo
    {
        return $this->belongsTo(KbArticle::class, 'resolved_by_kb_id');
    }

    /**
     * Increment count dan update last_asked_at
     */
    public function incrementCount(): void
    {
        $this->increment('count');
        $this->update(['last_asked_at' => now()]);
    }

    /**
     * Scope untuk pertanyaan yang sering ditanyakan (top missed queries)
     */
    public function scopeTopMissed($query, int $limit = 10)
    {
        return $query->orderByDesc('count')->limit($limit);
    }

    /**
     * Scope untuk status pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
