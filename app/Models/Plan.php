<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model: Paket Langganan (Plan)
 * 
 * Model ini merepresentasikan paket langganan yang tersedia.
 * Contoh: Gratis, Hemat, Pro, Enterprise
 * 
 * Penggunaan:
 * - Plan::where('is_active', true)->get() // Ambil semua paket aktif
 * - Plan::where('slug', 'hemat')->first() // Ambil paket Hemat
 */
class Plan extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara massal
     */
    protected $fillable = [
        'name',           // Nama paket
        'slug',           // Slug untuk URL
        'description',    // Deskripsi
        'price_monthly',           // Harga bulanan
        'price_monthly_original',  // Harga bulanan asli (sebelum diskon)
        'price_monthly_display',   // Tampilan harga bulanan (misal: 500rb)
        'price_monthly_original_display', // Tampilan harga asli bulanan (misal: 2jt)
        'price_yearly',            // Harga tahunan
        'price_yearly_original',   // Harga tahunan asli (sebelum diskon)
        'price_yearly_display',    // Tampilan harga tahunan (misal: 5jt)
        'price_yearly_original_display', // Tampilan harga asli tahunan (misal: 20jt)
        'features',       // Batasan fitur (JSON)
        'features_list',  // Daftar fitur deskriptif (JSON)
        'is_active',      // Aktif atau tidak
        'sort_order',     // Urutan tampilan
        'is_popular',     // Apakah ini paket yang paling laris
        'is_free',        // Apakah ini paket gratis
        'is_trial',       // Apakah ini paket trial
        'trial_days',     // Durasi trial
    ];

    /**
     * Konversi tipe data otomatis
     */
    protected $casts = [
        'features' => 'array',    // JSON jadi array PHP
        'features_list' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'is_free' => 'boolean',
        'is_trial' => 'boolean',
    ];

    // ==========================================
    // RELASI (HUBUNGAN DENGAN TABEL LAIN)
    // ==========================================

    /**
     * Relasi: Satu paket bisa punya banyak langganan
     * Contoh: 100 orang berlangganan paket Hemat
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // ==========================================
    // QUERY SCOPES (FILTER YANG SERING DIPAKAI)
    // ==========================================

    /**
     * Scope: Hanya ambil paket yang aktif
     * Penggunaan: Plan::aktif()->get()
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Hanya ambil paket berbayar (bukan gratis)
     * Penggunaan: Plan::berbayar()->get()
     */
    public function scopeBerbayar($query)
    {
        return $query->where('is_free', false);
    }

    /**
     * Scope: Urutkan berdasarkan sort_order
     * Penggunaan: Plan::urut()->get()
     */
    public function scopeUrut($query)
    {
        return $query->orderBy('sort_order');
    }

    // ==========================================
    // HELPER METHODS (FUNGSI PEMBANTU)
    // ==========================================

    /**
     * Ambil limit fitur tertentu
     * 
     * @param string $key Nama fitur (contoh: 'whatsapp_devices', 'ai_messages')
     * @param mixed $default Nilai default jika tidak ada
     * @return mixed
     * 
     * Contoh penggunaan:
     * $plan->getLimit('whatsapp_devices') // return 2
     * $plan->getLimit('ai_messages') // return 500
     */
    public function getLimit(string $key, $default = null)
    {
        return $this->features[$key] ?? $default;
    }

    /**
     * Cek apakah paket ini punya akses ke fitur tertentu
     * 
     * @param string $feature Nama fitur (contoh: 'broadcast', 'sequences')
     * @return bool
     * 
     * Contoh penggunaan:
     * $plan->hasFeature('broadcast') // true atau false
     */
    public function hasFeature(string $feature): bool
    {
        $limit = $this->getLimit($feature, 0);
        
        // Jika limit adalah boolean, kembalikan langsung
        if (is_bool($limit)) {
            return $limit;
        }
        
        // Jika limit adalah angka, cek apakah > 0
        // -1 berarti unlimited
        return $limit !== 0;
    }

    /**
     * Format harga bulanan ke Rupiah
     * 
     * @return string
     * 
     * Contoh: Rp 99.000
     */
    public function getFormattedPriceMonthlyAttribute(): string
    {
        if ($this->price_monthly == 0) {
            return 'Gratis';
        }
        return 'Rp ' . number_format($this->price_monthly, 0, ',', '.');
    }

    /**
     * Format harga tahunan ke Rupiah
     * 
     * @return string
     */
    public function getFormattedPriceYearlyAttribute(): string
    {
        if ($this->price_yearly == 0) {
            return 'Gratis';
        }
        return 'Rp ' . number_format($this->price_yearly, 0, ',', '.');
    }

    /**
     * Hitung berapa persen hemat jika ambil tahunan
     * 
     * @return int Persentase hemat
     */
    public function getYearlySavingsPercentAttribute(): int
    {
        if ($this->price_monthly == 0) {
            return 0;
        }
        
        $monthlyTotal = $this->price_monthly * 12;
        $savings = $monthlyTotal - $this->price_yearly;
        
        return (int) round(($savings / $monthlyTotal) * 100);
    }
}
