<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToUser;

class CsatRating extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'platform',
        'conversation_id',
        'wa_conversation_id',
        'contact_identifier',
        'contact_name',
        'rating',
        'feedback',
        'handled_by',
        'requested_at',
        'responded_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    /**
     * Owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Instagram Conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * WhatsApp Conversation
     */
    public function waConversation(): BelongsTo
    {
        return $this->belongsTo(WaConversation::class);
    }

    /**
     * Scope for platform
     */
    public function scopeInstagram($query)
    {
        return $query->where('platform', 'instagram');
    }

    public function scopeWhatsapp($query)
    {
        return $query->where('platform', 'whatsapp');
    }

    /**
     * Get average rating for a user
     */
    public static function averageForUser(int $userId, ?string $platform = null, int $days = 30): float
    {
        $query = self::where('user_id', $userId)
            ->whereNotNull('rating')
            ->where('created_at', '>=', now()->subDays($days));

        if ($platform) {
            $query->where('platform', $platform);
        }

        return round($query->avg('rating') ?? 0, 1);
    }

    /**
     * Get rating distribution for a user
     */
    public static function distributionForUser(int $userId, ?string $platform = null, int $days = 30): array
    {
        $query = self::where('user_id', $userId)
            ->whereNotNull('rating')
            ->where('created_at', '>=', now()->subDays($days));

        if ($platform) {
            $query->where('platform', $platform);
        }

        $total = $query->count();
        if ($total === 0) {
            return [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        }

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = (clone $query)->where('rating', $i)->count();
            $distribution[$i] = round(($count / $total) * 100);
        }

        return $distribution;
    }
}
