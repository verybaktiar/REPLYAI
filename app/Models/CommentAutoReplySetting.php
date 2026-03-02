<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToUser;

class CommentAutoReplySetting extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'instagram_account_id',
        'is_active',
        'reply_message',
        'keywords',
        'match_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'keywords' => 'array',
    ];

    /**
     * Get the user who owns these settings
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if text matches keywords based on match type
     */
    public function matchesKeywords(string $text): bool
    {
        if (empty($this->keywords)) {
            return false;
        }

        $text = strtolower($text);

        foreach ($this->keywords as $keyword) {
            $keyword = strtolower(trim($keyword));
            
            if (empty($keyword)) continue;

            switch ($this->match_type) {
                case 'exact':
                    if ($text === $keyword) return true;
                    break;
                case 'starts_with':
                    if (str_starts_with($text, $keyword)) return true;
                    break;
                case 'contains':
                default:
                    if (str_contains($text, $keyword)) return true;
                    break;
            }
        }

        return false;
    }
}
