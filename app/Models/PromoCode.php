<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Model: Kode Promo (PromoCode)
 * 
 * Model untuk mengelola kode promo/kupon diskon.
 * User bisa memasukkan kode saat checkout untuk dapat diskon.
 * 
 * Contoh:
 * - LAUNCH50 = diskon 50%
 * - HEMAT20K = potongan Rp 20.000
 */
class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_purchase',
        'usage_count',
        'usage_limit',
        'valid_from',
        'valid_until',
        'applicable_plans',
        'is_active',
        'new_users_only',
        'single_use_per_user',
    ];

    protected $casts = [
        'applicable_plans' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'new_users_only' => 'boolean',
        'single_use_per_user' => 'boolean',
    ];

    const TYPE_PERCENT = 'percent';
    const TYPE_FIXED = 'fixed';

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Penggunaan kode promo
     */
    public function usages()
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Hanya promo aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Promo yang masih valid (belum expired)
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', now());
        });
    }

    // ==========================================
    // VALIDASI
    // ==========================================

    /**
     * Cek apakah kode promo bisa digunakan
     * 
     * @param int $userId
     * @param int $planId
     * @param int $amount
     * @return array ['valid' => bool, 'message' => string]
     */
    public function canBeUsedBy(int $userId, int $planId, int $amount): array
    {
        // Cek apakah aktif
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Kode promo tidak aktif.'];
        }

        // Cek tanggal valid
        if ($this->valid_from && $this->valid_from > now()) {
            return ['valid' => false, 'message' => 'Kode promo belum berlaku.'];
        }

        if ($this->valid_until && $this->valid_until < now()) {
            return ['valid' => false, 'message' => 'Kode promo sudah kadaluarsa.'];
        }

        // Cek limit penggunaan
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Kode promo sudah habis terpakai.'];
        }

        // Cek minimum pembelian
        if ($this->min_purchase && $amount < $this->min_purchase) {
            $minFormatted = 'Rp ' . number_format($this->min_purchase, 0, ',', '.');
            return ['valid' => false, 'message' => "Minimum pembelian {$minFormatted}."];
        }

        // Cek applicable plans
        if ($this->applicable_plans && !in_array($planId, $this->applicable_plans)) {
            return ['valid' => false, 'message' => 'Kode promo tidak berlaku untuk paket ini.'];
        }

        // Cek new users only
        if ($this->new_users_only) {
            $existingPayments = Payment::where('user_id', $userId)
                ->where('status', Payment::STATUS_PAID)
                ->exists();
            
            if ($existingPayments) {
                return ['valid' => false, 'message' => 'Kode promo hanya untuk user baru.'];
            }
        }

        // Cek single use per user
        if ($this->single_use_per_user) {
            $alreadyUsed = PromoCodeUsage::where('promo_code_id', $this->id)
                ->where('user_id', $userId)
                ->exists();
            
            if ($alreadyUsed) {
                return ['valid' => false, 'message' => 'Anda sudah pernah menggunakan kode ini.'];
            }
        }

        return ['valid' => true, 'message' => 'Kode promo valid!'];
    }

    /**
     * Hitung diskon
     * 
     * @param int $amount
     * @return int
     */
    public function calculateDiscount(int $amount): int
    {
        if ($this->discount_type === self::TYPE_PERCENT) {
            $discount = (int) round($amount * ($this->discount_value / 100));
            
            // Terapkan max discount jika ada
            if ($this->max_discount && $discount > $this->max_discount) {
                return $this->max_discount;
            }
            
            return $discount;
        }

        // Fixed discount
        return min($this->discount_value, $amount);
    }

    /**
     * Gunakan kode promo (increment usage)
     * 
     * @param int $userId
     * @param int $paymentId
     * @param int $discountAmount
     */
    public function markAsUsed(int $userId, int $paymentId, int $discountAmount): void
    {
        // Increment counter
        $this->increment('usage_count');

        // Catat penggunaan
        PromoCodeUsage::create([
            'promo_code_id' => $this->id,
            'user_id' => $userId,
            'payment_id' => $paymentId,
            'discount_amount' => $discountAmount,
        ]);
    }
}
