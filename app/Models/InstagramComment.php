<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToUser;

class InstagramComment extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'instagram_account_id',
        'instagram_comment_id',
        'media_id',
        'from_username',
        'from_id',
        'text',
        'parent_comment_id',
        'is_replied',
        'reply_text',
        'replied_at',
        'commented_at',
    ];

    protected $casts = [
        'is_replied' => 'boolean',
        'replied_at' => 'datetime',
        'commented_at' => 'datetime',
    ];

    /**
     * Get the user who owns this comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Instagram account associated with this comment
     */
    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    /**
     * Get parent comment (if this is a reply)
     */
    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_comment_id');
    }

    /**
     * Get replies to this comment
     */
    public function replies()
    {
        return $this->hasMany(self::class, 'parent_comment_id');
    }

    /**
     * Scope for unreplied comments
     */
    public function scopeUnreplied($query)
    {
        return $query->where('is_replied', false);
    }

    /**
     * Scope for replied comments
     */
    public function scopeReplied($query)
    {
        return $query->where('is_replied', true);
    }

    /**
     * Scope for parent comments only (not replies)
     */
    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_comment_id');
    }

    /**
     * Scope for replies only
     */
    public function scopeRepliesOnly($query)
    {
        return $query->whereNotNull('parent_comment_id');
    }

    /**
     * Mark comment as replied
     */
    public function markAsReplied(string $replyText): void
    {
        $this->update([
            'is_replied' => true,
            'reply_text' => $replyText,
            'replied_at' => now(),
        ]);
    }

    /**
     * Get Instagram comment URL
     */
    public function getUrlAttribute(): string
    {
        return "https://www.instagram.com/p/{$this->media_id}/c/{$this->instagram_comment_id}";
    }
}
