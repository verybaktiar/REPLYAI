<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model: Pembayaran (Payment)
 * 
 * Model ini menyimpan riwayat semua pembayaran.
 * Setiap kali user bayar, akan dibuat 1 record baru.
 * 
 * Penggunaan:
 * - Payment::where('status', 'pending')->get() // Pembayaran pending
 * - $user->payments()->latest()->first() // Pembayaran terakhir user
 */
class Payment extends Model
{
    use HasFactory, BelongsToUser;

    /**
     * Kolom yang bisa diisi secara massal
     */
    protected $fillable = [
        'invoice_number',
        'user_id',
        'subscription_id',
        'plan_id',
        'amount',
        'discount',
        'total',
        'payment_method',
        'status',
        'payment_reference',
        'proof_url',
        'paid_at',
        'expires_at',
        'duration_months',
        'promo_code',
        'metadata',
        'admin_notes',
        'approved_by',
    ];

    /**
     * Kolom yang harus di-cast ke tipe tertentu
     */
    protected $casts = [
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Konstanta untuk status pembayaran
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Konstanta untuk metode pembayaran
     */
    const METHOD_MANUAL = 'manual_transfer';
    const METHOD_MIDTRANS = 'midtrans';
    const METHOD_XENDIT = 'xendit';

    // ==========================================
    // RELASI (HUBUNGAN DENGAN TABEL LAIN)
    // ==========================================

    /**
     * Relasi: Pembayaran dimiliki oleh 1 user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Pembayaran untuk 1 langganan
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Relasi: Pembayaran untuk 1 paket
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relasi: Pembayaran diapprove oleh 1 admin
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    /**
     * Scope: Pembayaran pending (menunggu)
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Pembayaran sudah dibayar
     */
    public function scopeDibayar($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope: Pembayaran manual transfer yang perlu diapprove
     */
    public function scopePerluApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('payment_method', self::METHOD_MANUAL)
                     ->whereNotNull('proof_url');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Generate nomor invoice baru
     * Format: INV-2024-00001
     * 
     * @return string
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        // Cari invoice terakhir yang menggunakan prefix tahun ini (GLOBAL, abaikan scope user)
        $lastPayment = self::withoutGlobalScopes()
                          ->where('invoice_number', 'LIKE', $prefix . '%')
                          ->orderBy('invoice_number', 'desc')
                          ->first();
        
        $nextNumber = 1;

        if ($lastPayment) {
            // Ambil 5 digit terakhir
            $lastInvoice = $lastPayment->invoice_number;
            $parts = explode('-', $lastInvoice);
            $lastSequence = (int) end($parts);
            $nextNumber = $lastSequence + 1;
        }
        
        return sprintf('%s%05d', $prefix, $nextNumber);
    }

    /**
     * Format jumlah ke Rupiah
     * 
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Format total ke Rupiah
     * 
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Format diskon ke Rupiah
     * 
     * @return string
     */
    public function getFormattedDiscountAttribute(): string
    {
        return 'Rp ' . number_format($this->discount, 0, ',', '.');
    }

    /**
     * Cek apakah pembayaran ini pending
     * 
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Cek apakah pembayaran ini sudah dibayar
     * 
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Ambil label status dalam bahasa Indonesia
     * 
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_PAID => 'Sudah Dibayar',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_REFUNDED => 'Dikembalikan',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Ambil warna badge status
     * 
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_PAID => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_REFUNDED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Ambil label metode pembayaran
     * 
     * @return string
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_MANUAL => 'Transfer Bank',
            self::METHOD_MIDTRANS => 'Midtrans',
            self::METHOD_XENDIT => 'Xendit',
            default => $this->payment_method,
        };
    }
}
