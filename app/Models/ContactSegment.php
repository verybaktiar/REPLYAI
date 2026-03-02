<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;

class ContactSegment extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'description',
        'filters',
        'is_auto_update',
        'contacts_count',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_auto_update' => 'boolean',
        'contacts_count' => 'integer',
    ];

    /**
     * Default colors
     */
    const COLORS = [
        'slate' => '#64748b',
        'red' => '#ef4444',
        'orange' => '#f97316',
        'amber' => '#f59e0b',
        'green' => '#22c55e',
        'emerald' => '#10b981',
        'teal' => '#14b8a6',
        'cyan' => '#06b6d4',
        'blue' => '#3b82f6',
        'indigo' => '#6366f1',
        'violet' => '#8b5cf6',
        'purple' => '#a855f7',
        'fuchsia' => '#d946ef',
        'pink' => '#ec4899',
        'rose' => '#f43f5e',
    ];

    /**
     * Get the user who owns this segment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all members of this segment
     */
    public function members(): HasMany
    {
        return $this->hasMany(ContactSegmentMember::class);
    }

    /**
     * Scope for auto-updating segments
     */
    public function scopeAutoUpdate($query)
    {
        return $query->where('is_auto_update', true);
    }

    /**
     * Scope for manual segments
     */
    public function scopeManual($query)
    {
        return $query->where('is_auto_update', false);
    }

    /**
     * Scope by color
     */
    public function scopeColor($query, string $color)
    {
        return $query->where('color', $color);
    }

    /**
     * Get color hex code
     */
    public function getColorHexAttribute(): string
    {
        return self::COLORS[$this->color] ?? self::COLORS['slate'];
    }

    /**
     * Update contacts count
     */
    public function updateContactsCount(): void
    {
        $this->update(['contacts_count' => $this->members()->count()]);
    }

    /**
     * Add contact to segment
     */
    public function addContact(Model $contact): ContactSegmentMember
    {
        return $this->members()->firstOrCreate([
            'contact_type' => get_class($contact),
            'contact_id' => $contact->getKey(),
        ]);
    }

    /**
     * Remove contact from segment
     */
    public function removeContact(Model $contact): bool
    {
        return $this->members()
            ->where('contact_type', get_class($contact))
            ->where('contact_id', $contact->getKey())
            ->delete() > 0;
    }

    /**
     * Check if contact is in segment
     */
    public function hasContact(Model $contact): bool
    {
        return $this->members()
            ->where('contact_type', get_class($contact))
            ->where('contact_id', $contact->getKey())
            ->exists();
    }
}
