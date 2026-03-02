<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbScrapeJob extends Model
{
    protected $fillable = [
        'user_id',
        'business_profile_id',
        'status',
        'job_id',
        'url',
        'target_name',
        'progress_percent',
        'progress_step',
        'scraped_data',
        'extracted_entities',
        'error_message',
        'raw_html',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'scraped_data' => 'array',
        'extracted_entities' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percent' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    /**
     * Generate unique job ID
     */
    public static function generateJobId(): string
    {
        return 'scrape_' . uniqid() . '_' . time();
    }

    /**
     * Check if user has recent scrape job (rate limiting)
     */
    public static function hasRecentJob(int $userId, int $minutes = 5): bool
    {
        return static::where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_COMPLETED])
            ->exists();
    }

    /**
     * Get next pending job
     */
    public static function getNextPending(): ?self
    {
        return static::where('status', self::STATUS_PENDING)
            ->orderBy('created_at')
            ->first();
    }

    /**
     * Update progress
     */
    public function updateProgress(int $percent, string $step): void
    {
        $this->update([
            'progress_percent' => $percent,
            'progress_step' => $step,
        ]);
    }

    /**
     * Mark as completed
     */
    public function markCompleted(array $data, array $entities): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'progress_percent' => 100,
            'progress_step' => 'Selesai',
            'scraped_data' => $data,
            'extracted_entities' => $entities,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'progress_step' => 'Gagal',
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }
}
