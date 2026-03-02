<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model: Log Aktivitas Admin
 * 
 * Model untuk mencatat semua aktivitas admin.
 * Semua aksi penting dicatat untuk audit trail.
 */
class AdminActivityLog extends Model
{
    use HasFactory;

    /**
     * Tidak ada updated_at (hanya created_at)
     */
    public $timestamps = false;

    /**
     * Kolom yang bisa diisi
     */
    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'details',
        'changes_before',
        'changes_after',
        'target_type',
        'target_id',
        'ip_address',
        'country_code',
        'city',
        'user_agent',
        'is_suspicious',
        'risk_score',
        'created_at',
    ];

    /**
     * Cast kolom
     */
    protected $casts = [
        'details' => 'array',
        'changes_before' => 'array',
        'changes_after' => 'array',
        'is_suspicious' => 'boolean',
        'risk_score' => 'integer',
        'created_at' => 'datetime',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Admin yang melakukan aksi
     */
    public function admin()
    {
        return $this->belongsTo(AdminUser::class, 'admin_id');
    }

    /**
     * Target polymorphic (bisa Payment, User, Plan, dll)
     */
    public function target()
    {
        return $this->morphTo();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope for suspicious activities.
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope for high risk activities.
     */
    public function scopeHighRisk($query, $minScore = 5)
    {
        return $query->where('risk_score', '>=', $minScore);
    }

    /**
     * Scope for recent activities.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for specific action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Log aktivitas admin
     * 
     * @param AdminUser $admin
     * @param string $action
     * @param string|null $description
     * @param array|null $details
     * @param Model|null $target
     * @param int $riskScore
     * @param array|null $changesBefore
     * @param array|null $changesAfter
     */
    public static function log(
        AdminUser $admin,
        string $action,
        ?string $description = null,
        ?array $details = null,
        ?Model $target = null,
        int $riskScore = 0,
        ?array $changesBefore = null,
        ?array $changesAfter = null
    ): self {
        $isSuspicious = $riskScore >= 3;
        
        return self::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'description' => $description ?? $action,
            'details' => $details,
            'changes_before' => $changesBefore,
            'changes_after' => $changesAfter,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target?->id,
            'ip_address' => request()->ip(),
            'country_code' => self::getCountryCode(request()->ip()),
            'city' => self::getCity(request()->ip()),
            'user_agent' => request()->userAgent(),
            'is_suspicious' => $isSuspicious,
            'risk_score' => $riskScore,
            'created_at' => now(),
        ]);
    }

    /**
     * Log change with before/after comparison.
     */
    public static function logChange(
        AdminUser $admin,
        string $action,
        Model $target,
        array $changesBefore,
        array $changesAfter,
        ?string $description = null,
        int $riskScore = 0
    ): self {
        return self::log(
            $admin,
            $action,
            $description ?? "Changed " . class_basename($target),
            ['target' => get_class($target), 'id' => $target->id],
            $target,
            $riskScore,
            $changesBefore,
            $changesAfter
        );
    }

    /**
     * Get country code from IP (placeholder - implement GeoIP).
     */
    private static function getCountryCode(?string $ip): ?string
    {
        // Implement GeoIP lookup if needed
        // For now, return null or use a simple library
        return null;
    }

    /**
     * Get city from IP (placeholder - implement GeoIP).
     */
    private static function getCity(?string $ip): ?string
    {
        // Implement GeoIP lookup if needed
        return null;
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Get changes diff.
     */
    public function getChangesDiff(): array
    {
        if (!$this->changes_before || !$this->changes_after) {
            return [];
        }

        $diff = [];
        $before = $this->changes_before;
        $after = $this->changes_after;

        foreach ($after as $key => $value) {
            if (!isset($before[$key]) || $before[$key] !== $value) {
                $diff[$key] = [
                    'before' => $before[$key] ?? null,
                    'after' => $value,
                ];
            }
        }

        return $diff;
    }

    /**
     * Get risk level label.
     */
    public function getRiskLevelAttribute(): string
    {
        return match(true) {
            $this->risk_score >= 8 => 'critical',
            $this->risk_score >= 5 => 'high',
            $this->risk_score >= 3 => 'medium',
            default => 'low',
        };
    }
}
