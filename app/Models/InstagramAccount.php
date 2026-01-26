<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;

class InstagramAccount extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'instagram_user_id',
        'username',
        'name',
        'profile_picture_url',
        'page_id',
        'access_token',
        'token_expires_at',
        'is_active',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
    ];

    /**
     * Get conversations for this Instagram account
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_id', 'user_id');
    }

    /**
     * Check if token is expired or will expire soon (within 7 days)
     */
    public function isTokenExpiringSoon(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isBefore(now()->addDays(7));
    }

    /**
     * Check if token is already expired
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Get decrypted access token
     */
    public function getDecryptedToken(): string
    {
        return $this->access_token;
    }

    /**
     * Find account by Instagram User ID
     */
    public static function findByInstagramId(string $instagramUserId): ?self
    {
        return static::where('instagram_user_id', $instagramUserId)
            ->where('is_active', true)
            ->first();
    }
}
