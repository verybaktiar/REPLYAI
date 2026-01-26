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
        'target_type',
        'target_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Cast kolom
     */
    protected $casts = [
        'details' => 'array',
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
     */
    public static function log(
        AdminUser $admin,
        string $action,
        ?string $description = null,
        ?array $details = null,
        ?Model $target = null
    ): self {
        return self::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'description' => $description ?? $action,
            'details' => $details,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
