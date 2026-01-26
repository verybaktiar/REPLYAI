<?php

namespace App\Helpers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UsageRecord;
use Illuminate\Support\Facades\Auth;

/**
 * Helper: Langganan (SubscriptionHelper)
 * 
 * Kelas helper untuk mengecek langganan dan batasan fitur.
 * Bisa dipanggil dari mana saja: Controller, View, Middleware, dll.
 * 
 * Penggunaan di Controller:
 * - if (SubscriptionHelper::canUse('broadcast')) { ... }
 * - $remaining = SubscriptionHelper::getRemaining('ai_messages');
 * 
 * Penggunaan di Blade:
 * - @if(App\Helpers\SubscriptionHelper::canUse('broadcast'))
 */
class SubscriptionHelper
{
    /**
     * Ambil langganan user saat ini
     * 
     * @param int|null $userId
     * @return Subscription|null
     */
    public static function getSubscription(?int $userId = null): ?Subscription
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return null;
        }

        return Subscription::where('user_id', $userId)
                          ->with('plan')
                          ->latest()
                          ->first();
    }

    /**
     * Ambil paket user saat ini
     * 
     * @param int|null $userId
     * @return Plan|null
     */
    public static function getPlan(?int $userId = null): ?Plan
    {
        $subscription = self::getSubscription($userId);
        
        if (!$subscription) {
            // Jika belum ada langganan, kembalikan paket Gratis
            return Plan::where('slug', 'gratis')->first();
        }

        return $subscription->plan;
    }

    /**
     * Cek apakah langganan aktif
     * 
     * @param int|null $userId
     * @return bool
     */
    public static function isActive(?int $userId = null): bool
    {
        $subscription = self::getSubscription($userId);
        
        if (!$subscription) {
            // Paket gratis selalu aktif
            return true;
        }

        return $subscription->isActive();
    }

    /**
     * Cek apakah user punya akses ke fitur tertentu
     * 
     * @param string $feature Nama fitur (contoh: 'broadcast', 'sequences')
     * @param int|null $userId
     * @return bool
     * 
     * Contoh:
     * if (SubscriptionHelper::hasFeature('broadcast')) { ... }
     */
    public static function hasFeature(string $feature, ?int $userId = null): bool
    {
        // Cek apakah langganan aktif dulu
        if (!self::isActive($userId)) {
            return false;
        }

        $plan = self::getPlan($userId);
        
        if (!$plan) {
            return false;
        }

        return $plan->hasFeature($feature);
    }

    /**
     * Ambil limit fitur dari paket
     * 
     * @param string $feature
     * @param int|null $userId
     * @return int|null
     * 
     * Contoh:
     * $limit = SubscriptionHelper::getLimit('ai_messages'); // 500
     */
    public static function getLimit(string $feature, ?int $userId = null): ?int
    {
        $plan = self::getPlan($userId);
        
        if (!$plan) {
            return 0;
        }

        $limit = $plan->getLimit($feature, 0);
        
        // -1 berarti unlimited
        if ($limit === -1) {
            return null; // null = unlimited
        }

        return $limit;
    }

    /**
     * Ambil jumlah yang sudah digunakan
     * 
     * @param string $feature
     * @param int|null $userId
     * @return int
     */
    public static function getUsage(string $feature, ?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return 0;
        }

        return UsageRecord::getUsage($userId, $feature);
    }

    /**
     * Ambil sisa kuota yang bisa digunakan
     * 
     * @param string $feature
     * @param int|null $userId
     * @return int|null (null = unlimited)
     * 
     * Contoh:
     * $remaining = SubscriptionHelper::getRemaining('ai_messages'); // 350
     */
    public static function getRemaining(string $feature, ?int $userId = null): ?int
    {
        $limit = self::getLimit($feature, $userId);
        
        // Jika unlimited, return null
        if ($limit === null) {
            return null;
        }

        $used = self::getUsage($feature, $userId);
        
        return max(0, $limit - $used);
    }

    /**
     * Cek apakah user masih bisa menggunakan fitur (belum mencapai limit)
     * 
     * @param string $feature
     * @param int $amount Jumlah yang akan digunakan (default 1)
     * @param int|null $userId
     * @return bool
     * 
     * Contoh:
     * if (SubscriptionHelper::canUse('ai_messages')) { ... }
     * if (SubscriptionHelper::canUse('broadcasts', 100)) { ... }
     */
    public static function canUse(string $feature, int $amount = 1, ?int $userId = null): bool
    {
        // Cek akses dulu
        if (!self::hasFeature($feature, $userId)) {
            return false;
        }

        $remaining = self::getRemaining($feature, $userId);
        
        // Jika unlimited
        if ($remaining === null) {
            return true;
        }

        return $remaining >= $amount;
    }

    /**
     * Gunakan fitur (tambah counter penggunaan)
     * 
     * @param string $feature
     * @param int $amount
     * @param int|null $userId
     * @return bool Success atau tidak
     * 
     * Contoh:
     * SubscriptionHelper::use('ai_messages'); // Tambah 1
     * SubscriptionHelper::use('kb_storage', 1024); // Tambah 1KB
     */
    public static function use(string $feature, int $amount = 1, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        // Cek dulu apakah bisa digunakan
        if (!self::canUse($feature, $amount, $userId)) {
            return false;
        }

        UsageRecord::incrementUsage($userId, $feature, $amount);
        
        return true;
    }

    /**
     * Ambil persentase penggunaan fitur
     * 
     * @param string $feature
     * @param int|null $userId
     * @return int Persentase 0-100
     * 
     * Contoh: SubscriptionHelper::getUsagePercent('ai_messages'); // 70
     */
    public static function getUsagePercent(string $feature, ?int $userId = null): int
    {
        $limit = self::getLimit($feature, $userId);
        
        // Jika unlimited
        if ($limit === null || $limit === 0) {
            return 0;
        }

        $used = self::getUsage($feature, $userId);
        
        return (int) round(($used / $limit) * 100);
    }

    /**
     * Cek apakah penggunaan hampir mencapai limit (>80%)
     * 
     * @param string $feature
     * @param int|null $userId
     * @return bool
     */
    public static function isNearLimit(string $feature, ?int $userId = null): bool
    {
        return self::getUsagePercent($feature, $userId) >= 80;
    }

    /**
     * Cek apakah paket saat ini adalah gratis
     * 
     * @param int|null $userId
     * @return bool
     */
    public static function isFreePlan(?int $userId = null): bool
    {
        $plan = self::getPlan($userId);
        return $plan ? $plan->is_free : true;
    }

    /**
     * Ambil semua statistik penggunaan untuk dashboard
     * 
     * @param int|null $userId
     * @return array
     */
    public static function getUsageStats(?int $userId = null): array
    {
        $features = [
            UsageRecord::FEATURE_AI_MESSAGES,
            UsageRecord::FEATURE_BROADCASTS,
            UsageRecord::FEATURE_CONTACTS,
            UsageRecord::FEATURE_SEQUENCES,
            UsageRecord::FEATURE_WA_DEVICES,
        ];

        $stats = [];

        foreach ($features as $feature) {
            $limit = self::getLimit($feature, $userId);
            $used = self::getUsage($feature, $userId);
            
            $stats[$feature] = [
                'used' => $used,
                'limit' => $limit,
                'remaining' => $limit !== null ? max(0, $limit - $used) : null,
                'percent' => $limit !== null && $limit > 0 
                    ? (int) round(($used / $limit) * 100) 
                    : 0,
                'unlimited' => $limit === null,
            ];
        }

        return $stats;
    }
}
