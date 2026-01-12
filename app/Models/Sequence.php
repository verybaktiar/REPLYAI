<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Sequence
 * 
 * Merepresentasikan satu sequence/drip campaign.
 * Sequence adalah serangkaian pesan otomatis yang dikirim berdasarkan trigger tertentu.
 */
class Sequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'trigger_value',
        'platform',
        'is_active',
        'total_enrolled',
        'total_completed',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_enrolled' => 'integer',
        'total_completed' => 'integer',
    ];

    /**
     * Tipe trigger yang tersedia
     */
    public const TRIGGER_TYPES = [
        'manual' => 'Manual (Daftarkan Sendiri)',
        'first_message' => 'Pesan Pertama (User Baru)',
        'keyword' => 'Keyword Tertentu',
        'tag_added' => 'Tag Ditambahkan',
    ];

    /**
     * Platform yang tersedia
     */
    public const PLATFORMS = [
        'all' => 'Semua Platform',
        'whatsapp' => 'WhatsApp',
        'instagram' => 'Instagram',
        'web' => 'Web Widget',
    ];

    /**
     * Relasi: Sequence memiliki banyak Step
     */
    public function steps(): HasMany
    {
        return $this->hasMany(SequenceStep::class)->orderBy('order');
    }

    /**
     * Relasi: Sequence memiliki banyak Enrollment
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(SequenceEnrollment::class);
    }

    /**
     * Scope: Hanya sequence yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter berdasarkan trigger type
     */
    public function scopeByTrigger($query, string $triggerType, ?string $triggerValue = null)
    {
        $query->where('trigger_type', $triggerType);
        
        if ($triggerValue !== null) {
            $query->where('trigger_value', $triggerValue);
        }
        
        return $query;
    }

    /**
     * Scope: Filter berdasarkan platform
     */
    public function scopeForPlatform($query, string $platform)
    {
        return $query->whereIn('platform', [$platform, 'all']);
    }

    /**
     * Hitung jumlah enrollment aktif
     */
    public function getActiveEnrollmentsCountAttribute(): int
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    /**
     * Increment total enrolled
     */
    public function incrementEnrolled(): void
    {
        $this->increment('total_enrolled');
    }

    /**
     * Increment total completed
     */
    public function incrementCompleted(): void
    {
        $this->increment('total_completed');
    }

    /**
     * Get step pertama dari sequence
     */
    public function getFirstStep(): ?SequenceStep
    {
        return $this->steps()->where('is_active', true)->orderBy('order')->first();
    }

    /**
     * Get label trigger type
     */
    public function getTriggerTypeLabelAttribute(): string
    {
        return self::TRIGGER_TYPES[$this->trigger_type] ?? $this->trigger_type;
    }

    /**
     * Get label platform
     */
    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }
}
