<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToUser;

class AiTrainingSuggestion extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'business_profile_id',
        'conversation_id',
        'question',
        'cs_answer',
        'status',
        'kb_article_id',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function kbArticle(): BelongsTo
    {
        return $this->belongsTo(KbArticle::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Approve suggestion
     */
    public function approve(int $userId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark as added to KB
     */
    public function markAsAddedToKb(int $kbArticleId): void
    {
        $this->update([
            'status' => 'added_to_kb',
            'kb_article_id' => $kbArticleId,
        ]);
    }

    /**
     * Scope untuk status pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk status approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
