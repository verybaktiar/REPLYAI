<?php

namespace App\Services;

use App\Models\UsageRecord;
use App\Helpers\SubscriptionHelper;
use Carbon\Carbon;

/**
 * Service: Pencatatan Penggunaan (UsageTrackingService)
 * 
 * Service ini menangani pencatatan dan pengecekan penggunaan fitur.
 * Digunakan untuk membatasi penggunaan sesuai paket langganan.
 * 
 * Penggunaan:
 * $tracker = app(UsageTrackingService::class);
 * if ($tracker->canUse($userId, 'ai_messages')) {
 *     // Proses AI
 *     $tracker->track($userId, 'ai_messages');
 * }
 */
class UsageTrackingService
{
    // ==========================================
    // TRACKING METHODS
    // ==========================================

    /**
     * Catat penggunaan fitur
     * 
     * @param int $userId
     * @param string $feature Nama fitur (ai_messages, broadcasts, dll)
     * @param int $amount Jumlah yang digunakan (default 1)
     * @return bool Berhasil atau tidak
     * 
     * Contoh:
     * $tracker->track($userId, 'ai_messages'); // Tambah 1
     * $tracker->track($userId, 'kb_storage', 1024); // Tambah 1KB
     */
    public function track(int $userId, string $feature, int $amount = 1): bool
    {
        // Cek dulu apakah bisa digunakan
        if (!$this->canUse($userId, $feature, $amount)) {
            return false;
        }

        UsageRecord::incrementUsage($userId, $feature, $amount);
        return true;
    }

    /**
     * Kurangi penggunaan (untuk fitur yang bisa dihapus)
     * 
     * @param int $userId
     * @param string $feature
     * @param int $amount
     * @return void
     */
    public function untrack(int $userId, string $feature, int $amount = 1): void
    {
        UsageRecord::decrementUsage($userId, $feature, $amount);
    }

    /**
     * Set nilai penggunaan (untuk sinkronisasi)
     * 
     * @param int $userId
     * @param string $feature
     * @param int $count
     * @return void
     */
    public function setUsage(int $userId, string $feature, int $count): void
    {
        UsageRecord::setUsage($userId, $feature, $count);
    }

    // ==========================================
    // CHECK METHODS
    // ==========================================

    /**
     * Cek apakah user bisa menggunakan fitur (belum mencapai limit)
     * 
     * @param int $userId
     * @param string $feature
     * @param int $amount Jumlah yang akan digunakan
     * @return bool
     */
    public function canUse(int $userId, string $feature, int $amount = 1): bool
    {
        return SubscriptionHelper::canUse($feature, $amount, $userId);
    }

    /**
     * Ambil jumlah yang sudah digunakan
     * 
     * @param int $userId
     * @param string $feature
     * @return int
     */
    public function getUsage(int $userId, string $feature): int
    {
        return UsageRecord::getUsage($userId, $feature);
    }

    /**
     * Ambil limit dari paket user
     * 
     * @param int $userId
     * @param string $feature
     * @return int|null (null = unlimited)
     */
    public function getLimit(int $userId, string $feature): ?int
    {
        return SubscriptionHelper::getLimit($feature, $userId);
    }

    /**
     * Ambil sisa kuota
     * 
     * @param int $userId
     * @param string $feature
     * @return int|null (null = unlimited)
     */
    public function getRemaining(int $userId, string $feature): ?int
    {
        return SubscriptionHelper::getRemaining($feature, $userId);
    }

    /**
     * Ambil persentase penggunaan
     * 
     * @param int $userId
     * @param string $feature
     * @return int 0-100
     */
    public function getUsagePercent(int $userId, string $feature): int
    {
        return SubscriptionHelper::getUsagePercent($feature, $userId);
    }

    // ==========================================
    // SYNC METHODS (untuk fitur lifetime)
    // ==========================================

    /**
     * Sinkronisasi jumlah kontak
     * Dipanggil setelah import atau tambah kontak
     * 
     * @param int $userId
     * @return int Jumlah kontak
     */
    public function syncContacts(int $userId): int
    {
        // Hitung total kontak dari database
        $count = \App\Models\Contact::where('user_id', $userId)->count();
        
        $this->setUsage($userId, UsageRecord::FEATURE_CONTACTS, $count);
        
        return $count;
    }

    /**
     * Sinkronisasi ukuran Knowledge Base
     * 
     * @param int $userId
     * @return int Ukuran dalam bytes
     */
    public function syncKbStorage(int $userId): int
    {
        // Hitung total ukuran file KB
        $totalBytes = \App\Models\KnowledgeBase::where('user_id', $userId)
            ->sum('file_size');
        
        $this->setUsage($userId, UsageRecord::FEATURE_KB_STORAGE, (int) $totalBytes);
        
        return (int) $totalBytes;
    }

    /**
     * Sinkronisasi jumlah WhatsApp device
     * 
     * @param int $userId
     * @return int
     */
    public function syncWaDevices(int $userId): int
    {
        $count = \App\Models\WhatsappDevice::where('user_id', $userId)->count();
        
        $this->setUsage($userId, UsageRecord::FEATURE_WA_DEVICES, $count);
        
        return $count;
    }

    /**
     * Sinkronisasi jumlah sequence
     * 
     * @param int $userId
     * @return int
     */
    public function syncSequences(int $userId): int
    {
        $count = \App\Models\Sequence::where('user_id', $userId)
            ->where('is_active', true)
            ->count();
        
        $this->setUsage($userId, UsageRecord::FEATURE_SEQUENCES, $count);
        
        return $count;
    }

    /**
     * Sinkronisasi jumlah quick replies
     * 
     * @param int $userId
     * @return int
     */
    public function syncQuickReplies(int $userId): int
    {
        $count = \App\Models\QuickReply::where('user_id', $userId)->count();
        
        $this->setUsage($userId, UsageRecord::FEATURE_QUICK_REPLIES, $count);
        
        return $count;
    }

    /**
     * Sinkronisasi semua fitur lifetime untuk user
     * 
     * @param int $userId
     * @return array
     */
    public function syncAll(int $userId): array
    {
        return [
            'contacts' => $this->syncContacts($userId),
            'kb_storage' => $this->syncKbStorage($userId),
            'wa_devices' => $this->syncWaDevices($userId),
            'sequences' => $this->syncSequences($userId),
            'quick_replies' => $this->syncQuickReplies($userId),
        ];
    }

    // ==========================================
    // RESET METHODS
    // ==========================================

    /**
     * Reset counter bulanan untuk semua user
     * Dipanggil oleh cron job setiap awal bulan
     * 
     * @return int Jumlah record yang direset
     */
    public function resetMonthlyCounters(): int
    {
        $lastMonth = Carbon::now()->subMonth();
        
        // Ambil semua record bulan lalu yang belum ada record bulan ini
        $count = UsageRecord::where('resets_monthly', true)
            ->where('period_start', '<', Carbon::now()->startOfMonth())
            ->count();

        // Record baru akan otomatis dibuat dengan count 0 saat diakses
        // Jadi kita tidak perlu melakukan apa-apa, cukup log saja
        
        return $count;
    }

    // ==========================================
    // PESAN ERROR
    // ==========================================

    /**
     * Ambil pesan error untuk fitur yang sudah mencapai limit
     * 
     * @param string $feature
     * @param int $userId
     * @return string
     */
    public function getLimitReachedMessage(string $feature, int $userId): string
    {
        $limit = $this->getLimit($userId, $feature);
        $used = $this->getUsage($userId, $feature);

        $labels = [
            UsageRecord::FEATURE_AI_MESSAGES => 'pesan AI',
            UsageRecord::FEATURE_BROADCASTS => 'broadcast',
            UsageRecord::FEATURE_CONTACTS => 'kontak',
            UsageRecord::FEATURE_KB_STORAGE => 'penyimpanan Knowledge Base',
            UsageRecord::FEATURE_SEQUENCES => 'sequence',
            UsageRecord::FEATURE_QUICK_REPLIES => 'quick reply',
            UsageRecord::FEATURE_WEB_WIDGETS => 'web widget',
            UsageRecord::FEATURE_WA_DEVICES => 'WhatsApp device',
            UsageRecord::FEATURE_TEAM_MEMBERS => 'anggota tim',
        ];

        $label = $labels[$feature] ?? $feature;

        if ($limit === null) {
            return "Fitur unlimited.";
        }

        return "Anda sudah menggunakan {$used} dari {$limit} {$label} bulan ini. " .
               "Upgrade paket untuk menambah limit.";
    }
}
