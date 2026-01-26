<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model: Admin User
 * 
 * Model untuk user admin/super admin yang mengelola sistem.
 * Login terpisah dari user biasa (pelanggan).
 * 
 * Role:
 * - superadmin: Akses penuh ke semua fitur
 * - support: Bisa lihat tenant & handle tiket
 * - finance: Bisa lihat revenue & approve pembayaran
 */
class AdminUser extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Guard yang digunakan
     */
    protected $guard = 'admin';

    /**
     * Kolom yang bisa diisi
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'telegram_chat_id',
        'is_active',
        'two_factor_secret',
        'two_factor_enabled',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * Kolom yang disembunyikan
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Cast kolom
     */
    protected $casts = [
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Konstanta role
     */
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_SUPPORT = 'support';
    const ROLE_FINANCE = 'finance';

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Activity logs dari admin ini
     */
    public function activityLogs()
    {
        return $this->hasMany(AdminActivityLog::class, 'admin_id');
    }

    // ==========================================
    // PERMISSIONS
    // ==========================================

    /**
     * Cek apakah admin ini adalah superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /**
     * Cek apakah admin bisa manage tenants
     */
    public function canManageTenants(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_SUPPORT]);
    }

    /**
     * Cek apakah admin bisa manage payments
     */
    public function canManagePayments(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_FINANCE]);
    }

    /**
     * Cek apakah admin bisa manage plans
     */
    public function canManagePlans(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /**
     * Cek apakah admin bisa login sebagai user
     */
    public function canLoginAsUser(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_SUPPORT]);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Hanya admin aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Catat login admin
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);

        // Log aktivitas
        AdminActivityLog::create([
            'admin_id' => $this->id,
            'action' => 'login',
            'description' => 'Admin login',
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Ambil label role dalam bahasa Indonesia
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_SUPPORT => 'Support',
            self::ROLE_FINANCE => 'Finance',
            default => $this->role,
        };
    }
}
