<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactSegmentMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_segment_id',
        'contact_type',
        'contact_id',
    ];

    /**
     * Get the segment this member belongs to
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(ContactSegment::class, 'contact_segment_id');
    }

    /**
     * Get the contact (polymorphic)
     */
    public function contact(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope by segment
     */
    public function scopeForSegment($query, int $segmentId)
    {
        return $query->where('contact_segment_id', $segmentId);
    }

    /**
     * Scope by contact type
     */
    public function scopeContactType($query, string $type)
    {
        return $query->where('contact_type', $type);
    }

    /**
     * Get contact type label
     */
    public function getContactTypeLabelAttribute(): string
    {
        return class_basename($this->contact_type);
    }
}
