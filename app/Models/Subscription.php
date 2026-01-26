<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Model: Langganan (Subscription)
 * 
 * Model ini merepresentasikan langganan setiap user/tenant.
 * Setiap user hanya punya 1 langganan aktif.
 * 
 * Penggunaan:
 * - $user->subscription // Ambil langganan user
 * - $user->subscription->isActive() // Cek apakah masih aktif
 * - $user->subscription->plan // Ambil paket yang dibeli
 */
class Subscription extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara massal
     */
    protected $fillable = [
        'user_id',              // ID user
        'plan_id',              // ID paket
        'status',               // Status langganan
        'starts_at',            // Tanggal mulai
        'expires_at',           // Tanggal berakhir
        'trial_ends_at',        // Tanggal trial berakhir
        'canceled_at',          // Tanggal dibatalkan
        'grace_period_ends_at', // Tanggal grace period berakhir
        'payment_method',       // Metode pembayaran
        'notes',                // Catatan
    ];

    /**
     * Kolom yang harus di-cast ke tipe tertentu
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
    ];

    /**
     * Konstanta untuk status langganan
     */
    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAST_DUE = 'past_due';
    const STATUS_CANCELED = 'canceled';
    const STATUS_EXPIRED = 'expired';

    // ==========================================
    // RELASI (HUBUNGAN DENGAN TABEL LAIN)
    // ==========================================

    /**
     * Relasi: Langganan dimiliki oleh 1 user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Langganan menggunakan 1 paket
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relasi: Langganan bisa punya banyak pembayaran
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // ==========================================
    // QUERY SCOPES (FILTER YANG SERING DIPAKAI)
    // ==========================================

    /**
     * Scope: Hanya langganan aktif
     * Penggunaan: Subscription::aktif()->get()
     */
    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Langganan yang akan expired dalam X hari
     * Penggunaan: Subscription::akanExpired(7)->get()
     */
    public function scopeAkanExpired($query, int $days = 7)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Scope: Langganan yang sudah expired
     * Penggunaan: Subscription::sudahExpired()->get()
     */
    public function scopeSudahExpired($query)
    {
        return $query->where('expires_at', '<', now())
                     ->where('status', '!=', self::STATUS_EXPIRED);
    }

    // ==========================================
    // STATUS CHECKING METHODS
    // ==========================================

    /**
     * Cek apakah langganan aktif (bisa pakai semua fitur)
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        // Aktif jika status active dan belum expired
        if ($this->status === self::STATUS_ACTIVE && $this->expires_at > now()) {
            return true;
        }
        
        // Aktif jika dalam masa trial
        if ($this->isOnTrial()) {
            return true;
        }
        
        // Aktif jika dalam grace period
        if ($this->isInGracePeriod()) {
            return true;
        }
        
        return false;
    }

    /**
     * Cek apakah sedang dalam masa trial
     * 
     * @return bool
     */
    public function isOnTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL 
               && $this->trial_ends_at 
               && $this->trial_ends_at > now();
    }

    /**
     * Cek apakah sedang dalam grace period
     * 
     * @return bool
     */
    public function isInGracePeriod(): bool
    {
        return $this->status === self::STATUS_PAST_DUE
               && $this->grace_period_ends_at
               && $this->grace_period_ends_at > now();
    }

    /**
     * Cek apakah sudah expired (tidak bisa pakai fitur)
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
               || ($this->expires_at && $this->expires_at <= now() && !$this->isInGracePeriod());
    }

    /**
     * Cek apakah dibatalkan
     * 
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Hitung berapa hari lagi sampai expired
     * 
     * @return int
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        if (!$this->expires_at) {
            return 0;
        }
        
        $diff = now()->diffInDays($this->expires_at, false);
        return max(0, $diff);
    }

    /**
     * Format tanggal expired ke format Indonesia
     * 
     * @return string
     */
    public function getFormattedExpiresAtAttribute(): string
    {
        if (!$this->expires_at) {
            return '-';
        }
        
        return $this->expires_at->translatedFormat('d F Y');
    }

    /**
     * Ambil label status dalam bahasa Indonesia
     * 
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_TRIAL => 'Masa Percobaan',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_PAST_DUE => 'Menunggu Pembayaran',
            self::STATUS_CANCELED => 'Dibatalkan',
            self::STATUS_EXPIRED => 'Kadaluarsa',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Ambil warna badge status untuk UI
     * 
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_TRIAL => 'blue',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_PAST_DUE => 'yellow',
            self::STATUS_CANCELED => 'gray',
            self::STATUS_EXPIRED => 'red',
            default => 'gray',
        };
    }
}
