<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Model: Pencatatan Penggunaan (UsageRecord)
 * 
 * Model ini mencatat penggunaan fitur oleh user.
 * Digunakan untuk membatasi penggunaan sesuai paket.
 * 
 * Contoh:
 * - User paket Hemat punya limit 500 pesan AI/bulan
 * - Setiap AI menjawab, counter bertambah
 * - UsageRecord::increment($userId, 'ai_messages')
 */
class UsageRecord extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara massal
     */
    protected $fillable = [
        'user_id',
        'feature_key',
        'used_count',
        'period_start',
        'period_end',
        'resets_monthly',
    ];

    /**
     * Kolom yang harus di-cast ke tipe tertentu
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'resets_monthly' => 'boolean',
    ];

    /**
     * Konstanta untuk feature keys
     */
    const FEATURE_AI_MESSAGES = 'ai_messages';
    const FEATURE_BROADCASTS = 'broadcasts';
    const FEATURE_CONTACTS = 'contacts';
    const FEATURE_KB_STORAGE = 'kb_storage';
    const FEATURE_SEQUENCES = 'sequences';
    const FEATURE_QUICK_REPLIES = 'quick_replies';
    const FEATURE_WEB_WIDGETS = 'web_widgets';
    const FEATURE_WA_DEVICES = 'wa_devices';
    const FEATURE_TEAM_MEMBERS = 'team_members';

    /**
     * Fitur yang reset bulanan
     */
    const MONTHLY_FEATURES = [
        self::FEATURE_AI_MESSAGES,
        self::FEATURE_BROADCASTS,
    ];

    /**
     * Fitur yang tidak reset (lifetime count)
     */
    const LIFETIME_FEATURES = [
        self::FEATURE_CONTACTS,
        self::FEATURE_KB_STORAGE,
        self::FEATURE_SEQUENCES,
        self::FEATURE_QUICK_REPLIES,
        self::FEATURE_WEB_WIDGETS,
        self::FEATURE_WA_DEVICES,
        self::FEATURE_TEAM_MEMBERS,
    ];

    // ==========================================
    // RELASI
    // ==========================================

    /**
     * Relasi: Record dimiliki oleh 1 user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Ambil atau buat record untuk user dan fitur tertentu
     * 
     * @param int $userId
     * @param string $featureKey
     * @return UsageRecord
     */
    public static function getOrCreate(int $userId, string $featureKey): self
    {
        $isMonthly = in_array($featureKey, self::MONTHLY_FEATURES);
        
        if ($isMonthly) {
            // Untuk fitur bulanan, cari record bulan ini
            $periodStart = Carbon::now()->startOfMonth()->toDateString();
            $periodEnd = Carbon::now()->endOfMonth()->toDateString();
            
            return self::firstOrCreate(
                [
                    'user_id' => $userId,
                    'feature_key' => $featureKey,
                    'period_start' => $periodStart,
                ],
                [
                    'period_end' => $periodEnd,
                    'used_count' => 0,
                    'resets_monthly' => true,
                ]
            );
        } else {
            // Untuk fitur lifetime, cari record tanpa periode
            return self::firstOrCreate(
                [
                    'user_id' => $userId,
                    'feature_key' => $featureKey,
                    'period_start' => null,
                ],
                [
                    'period_end' => null,
                    'used_count' => 0,
                    'resets_monthly' => false,
                ]
            );
        }
    }

    /**
     * Tambah penggunaan fitur
     * 
     * @param int $userId
     * @param string $featureKey
     * @param int $amount Jumlah yang ditambah (default 1)
     * @return UsageRecord
     * 
     * Contoh:
     * UsageRecord::incrementUsage($userId, 'ai_messages');
     * UsageRecord::incrementUsage($userId, 'kb_storage', 1024); // 1KB
     */
    public static function incrementUsage(int $userId, string $featureKey, int $amount = 1): self
    {
        $record = self::getOrCreate($userId, $featureKey);
        $record->increment('used_count', $amount);
        
        return $record;
    }

    /**
     * Kurangi penggunaan fitur (untuk fitur lifetime saat item dihapus)
     * 
     * @param int $userId
     * @param string $featureKey
     * @param int $amount
     * @return UsageRecord
     */
    public static function decrementUsage(int $userId, string $featureKey, int $amount = 1): self
    {
        $record = self::getOrCreate($userId, $featureKey);
        $record->decrement('used_count', min($amount, $record->used_count));
        
        return $record;
    }

    /**
     * Ambil jumlah yang sudah digunakan
     * 
     * @param int $userId
     * @param string $featureKey
     * @return int
     */
    public static function getUsage(int $userId, string $featureKey): int
    {
        $record = self::getOrCreate($userId, $featureKey);
        return $record->used_count;
    }

    /**
     * Set jumlah penggunaan (untuk fitur lifetime)
     * 
     * @param int $userId
     * @param string $featureKey
     * @param int $count
     * @return UsageRecord
     */
    public static function setUsage(int $userId, string $featureKey, int $count): self
    {
        $record = self::getOrCreate($userId, $featureKey);
        $record->update(['used_count' => $count]);
        
        return $record;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Ambil label fitur dalam bahasa Indonesia
     * 
     * @return string
     */
    public function getFeatureLabelAttribute(): string
    {
        return match($this->feature_key) {
            self::FEATURE_AI_MESSAGES => 'Pesan AI',
            self::FEATURE_BROADCASTS => 'Broadcast',
            self::FEATURE_CONTACTS => 'Kontak',
            self::FEATURE_KB_STORAGE => 'Penyimpanan KB',
            self::FEATURE_SEQUENCES => 'Sequence',
            self::FEATURE_QUICK_REPLIES => 'Quick Reply',
            self::FEATURE_WEB_WIDGETS => 'Web Widget',
            self::FEATURE_WA_DEVICES => 'WhatsApp Device',
            self::FEATURE_TEAM_MEMBERS => 'Anggota Tim',
            default => $this->feature_key,
        };
    }
}
