<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\BelongsToUser;

class ContactNote extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'contact_type',
        'contact_id',
        'content',
        'category',
        'created_by',
    ];

    protected $casts = [
        'created_by' => 'integer',
    ];

    /**
     * Category constants
     */
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_FOLLOW_UP = 'follow_up';
    const CATEGORY_COMPLAINT = 'complaint';
    const CATEGORY_FEEDBACK = 'feedback';
    const CATEGORY_PRIVATE = 'private';

    /**
     * Get the user who owns this note
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the contact (polymorphic)
     */
    public function contact(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this note
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for general category
     */
    public function scopeGeneral($query)
    {
        return $query->where('category', self::CATEGORY_GENERAL);
    }

    /**
     * Scope for follow-up category
     */
    public function scopeFollowUp($query)
    {
        return $query->where('category', self::CATEGORY_FOLLOW_UP);
    }

    /**
     * Scope for complaint category
     */
    public function scopeComplaint($query)
    {
        return $query->where('category', self::CATEGORY_COMPLAINT);
    }
}
