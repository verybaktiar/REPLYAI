<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service: Manajemen Langganan (SubscriptionService)
 * 
 * Service ini menangani semua operasi terkait langganan:
 * - Buat langganan baru
 * - Upgrade/downgrade paket
 * - Perpanjang langganan
 * - Batalkan langganan
 * - Cek & update status expired
 * 
 * Penggunaan:
 * $service = app(SubscriptionService::class);
 * $service->create($user, $plan);
 * $service->upgrade($user, $newPlan);
 */
class SubscriptionService
{
    /**
     * Grace period dalam hari (setelah expired, user masih bisa akses)
     */
    const GRACE_PERIOD_DAYS = 3;

    /**
     * Default trial days untuk user baru
     */
    const DEFAULT_TRIAL_DAYS = 7;

    // ==========================================
    // OPERASI UTAMA
    // ==========================================

    /**
     * Buat langganan baru untuk user
     * 
     * @param User $user
     * @param Plan $plan
     * @param int $durationMonths Durasi dalam bulan (1 atau 12)
     * @param bool $withTrial Apakah mulai dengan trial
     * @return Subscription
     * 
     * Contoh:
     * $subscription = $service->create($user, $plan, 1, true);
     */
    public function create(User $user, Plan $plan, int $durationMonths = 1, bool $withTrial = false): Subscription
    {
        // Cek apakah user sudah punya langganan aktif
        $existing = $this->getActiveSubscription($user);
        if ($existing) {
            throw new Exception('User sudah memiliki langganan aktif. Gunakan upgrade() untuk mengganti paket.');
        }

        // Hitung tanggal mulai dan berakhir
        $startsAt = now();
        $expiresAt = now()->addMonths($durationMonths);
        $trialEndsAt = null;

        // Jika paket gratis, tidak ada expired
        if ($plan->is_free) {
            $expiresAt = null;
            $status = Subscription::STATUS_ACTIVE;
        }
        // Jika dengan trial
        elseif ($withTrial) {
            $trialDays = $plan->trial_days ?: self::DEFAULT_TRIAL_DAYS;
            $trialEndsAt = now()->addDays($trialDays);
            $expiresAt = $trialEndsAt; // Trial expired = subscription expired
            $status = Subscription::STATUS_TRIAL;
        }
        // Langganan berbayar biasa
        else {
            $status = Subscription::STATUS_ACTIVE;
        }

        // Buat subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => $status,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'trial_ends_at' => $trialEndsAt,
            'payment_method' => null,
        ]);

        return $subscription;
    }

    /**
     * Upgrade langganan ke paket yang lebih tinggi
     * 
     * @param User $user
     * @param Plan $newPlan
     * @param int $durationMonths
     * @param Payment|null $payment Pembayaran terkait (jika sudah bayar)
     * @return Subscription
     */
    public function upgrade(User $user, Plan $newPlan, int $durationMonths = 1, ?Payment $payment = null): Subscription
    {
        $subscription = $this->getActiveSubscription($user);

        if (!$subscription) {
            // Jika belum ada langganan, buat baru
            return $this->create($user, $newPlan, $durationMonths, false);
        }

        // Update subscription
        $subscription->update([
            'plan_id' => $newPlan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'expires_at' => now()->addMonths($durationMonths),
            'trial_ends_at' => null, // Clear trial
            'canceled_at' => null,
            'grace_period_ends_at' => null,
        ]);

        return $subscription->fresh();
    }

    /**
     * Downgrade langganan ke paket yang lebih rendah
     * Akan berlaku setelah periode saat ini berakhir
     * 
     * @param User $user
     * @param Plan $newPlan
     * @return Subscription
     */
    public function downgrade(User $user, Plan $newPlan): Subscription
    {
        $subscription = $this->getActiveSubscription($user);

        if (!$subscription) {
            throw new Exception('Tidak ada langganan aktif untuk di-downgrade.');
        }

        // Simpan info downgrade (akan diproses saat expired)
        $subscription->update([
            'notes' => json_encode([
                'pending_downgrade' => true,
                'downgrade_to_plan_id' => $newPlan->id,
                'scheduled_at' => $subscription->expires_at,
            ]),
        ]);

        return $subscription->fresh();
    }

    /**
     * Perpanjang langganan
     * 
     * @param User $user
     * @param int $durationMonths
     * @param Payment|null $payment
     * @return Subscription
     */
    public function renew(User $user, int $durationMonths = 1, ?Payment $payment = null): Subscription
    {
        $subscription = $this->getSubscription($user);

        if (!$subscription) {
            throw new Exception('Tidak ada langganan untuk diperpanjang.');
        }

        // Hitung tanggal expired baru
        // Jika masih aktif, tambahkan dari tanggal expired saat ini
        // Jika sudah expired, mulai dari sekarang
        $newExpiresAt = $subscription->isActive()
            ? $subscription->expires_at->addMonths($durationMonths)
            : now()->addMonths($durationMonths);

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'expires_at' => $newExpiresAt,
            'canceled_at' => null,
            'grace_period_ends_at' => null,
        ]);

        return $subscription->fresh();
    }

    /**
     * Batalkan langganan
     * User masih bisa akses sampai tanggal expired
     * 
     * @param User $user
     * @param string|null $reason Alasan pembatalan
     * @return Subscription
     */
    public function cancel(User $user, ?string $reason = null): Subscription
    {
        $subscription = $this->getActiveSubscription($user);

        if (!$subscription) {
            throw new Exception('Tidak ada langganan aktif untuk dibatalkan.');
        }

        $subscription->update([
            'status' => Subscription::STATUS_CANCELED,
            'canceled_at' => now(),
            'notes' => $reason ? json_encode(['cancel_reason' => $reason]) : null,
        ]);

        return $subscription->fresh();
    }

    /**
     * Aktifkan kembali langganan yang dibatalkan
     * 
     * @param User $user
     * @return Subscription
     */
    public function reactivate(User $user): Subscription
    {
        $subscription = $this->getSubscription($user);

        if (!$subscription) {
            throw new Exception('Tidak ada langganan untuk diaktifkan.');
        }

        // Hanya bisa reactivate jika belum expired
        if ($subscription->expires_at && $subscription->expires_at < now()) {
            throw new Exception('Langganan sudah expired. Silakan perpanjang.');
        }

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'canceled_at' => null,
        ]);

        return $subscription->fresh();
    }

    // ==========================================
    // PROSES EXPIRED & GRACE PERIOD
    // ==========================================

    /**
     * Proses langganan yang akan expired
     * Dipanggil oleh cron job setiap hari
     * 
     * @return array Statistik proses
     */
    public function processExpiringSubscriptions(): array
    {
        $stats = [
            'expired' => 0,
            'grace_period' => 0,
            'locked' => 0,
        ];

        // 1. Tandai langganan yang baru expired (masuk grace period)
        $newlyExpired = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('expires_at', '<=', now())
            ->whereNull('grace_period_ends_at')
            ->get();

        foreach ($newlyExpired as $subscription) {
            $subscription->update([
                'status' => Subscription::STATUS_PAST_DUE,
                'grace_period_ends_at' => now()->addDays(self::GRACE_PERIOD_DAYS),
            ]);
            $stats['grace_period']++;
        }

        // 2. Kunci langganan yang grace period-nya sudah habis
        $gracePeriodEnded = Subscription::where('status', Subscription::STATUS_PAST_DUE)
            ->where('grace_period_ends_at', '<=', now())
            ->get();

        foreach ($gracePeriodEnded as $subscription) {
            $subscription->update([
                'status' => Subscription::STATUS_EXPIRED,
            ]);
            $stats['locked']++;

            // Cek apakah ada pending downgrade
            $this->processPendingDowngrade($subscription);
        }

        // 3. Proses trial yang expired
        $expiredTrials = Subscription::where('status', Subscription::STATUS_TRIAL)
            ->where('trial_ends_at', '<=', now())
            ->get();

        foreach ($expiredTrials as $subscription) {
            // Downgrade ke paket gratis
            $freePlan = Plan::where('slug', 'gratis')->first();
            if ($freePlan) {
                $subscription->update([
                    'plan_id' => $freePlan->id,
                    'status' => Subscription::STATUS_ACTIVE,
                    'trial_ends_at' => null,
                    'expires_at' => null, // Gratis tidak expired
                ]);
            } else {
                $subscription->update([
                    'status' => Subscription::STATUS_EXPIRED,
                ]);
            }
            $stats['expired']++;
        }

        return $stats;
    }

    /**
     * Proses pending downgrade setelah subscription expired
     * 
     * @param Subscription $subscription
     * @return void
     */
    private function processPendingDowngrade(Subscription $subscription): void
    {
        if (!$subscription->notes) {
            return;
        }

        $notes = json_decode($subscription->notes, true);
        
        if (!isset($notes['pending_downgrade']) || !$notes['pending_downgrade']) {
            return;
        }

        $newPlanId = $notes['downgrade_to_plan_id'] ?? null;
        if (!$newPlanId) {
            return;
        }

        $newPlan = Plan::find($newPlanId);
        if (!$newPlan) {
            return;
        }

        // Update ke paket baru (gratis)
        $subscription->update([
            'plan_id' => $newPlan->id,
            'status' => $newPlan->is_free ? Subscription::STATUS_ACTIVE : Subscription::STATUS_EXPIRED,
            'expires_at' => $newPlan->is_free ? null : $subscription->expires_at,
            'notes' => null,
        ]);
    }

    // ==========================================
    // QUERY HELPERS
    // ==========================================

    /**
     * Ambil langganan aktif user
     * 
     * @param User $user
     * @return Subscription|null
     */
    public function getActiveSubscription(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)
            ->whereIn('status', [
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_TRIAL,
                Subscription::STATUS_PAST_DUE,
            ])
            ->latest()
            ->first();
    }

    /**
     * Ambil langganan terakhir user (apapun statusnya)
     * 
     * @param User $user
     * @return Subscription|null
     */
    public function getSubscription(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)
            ->latest()
            ->first();
    }

    /**
     * Ambil langganan yang akan expired dalam X hari
     * Untuk kirim reminder email
     * 
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpiringIn(int $days)
    {
        return Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->with(['user', 'plan'])
            ->get();
    }

    /**
     * Buat langganan gratis default untuk user baru
     * Dipanggil saat user register
     * 
     * @param User $user
     * @param bool $withTrial Apakah mulai dengan trial Pro
     * @return Subscription
     */
    public function createDefaultSubscription(User $user, bool $withTrial = true): Subscription
    {
        if ($withTrial) {
            // Mulai dengan trial Pro
            $trialPlan = Plan::where('slug', 'trial')->first();
            if ($trialPlan) {
                return $this->create($user, $trialPlan, 1, true);
            }
        }

        // Fallback ke paket gratis
        $freePlan = Plan::where('slug', 'gratis')->first();
        if (!$freePlan) {
            throw new Exception('Paket gratis tidak ditemukan. Jalankan PlanSeeder terlebih dahulu.');
        }

        return $this->create($user, $freePlan, 1, false);
    }
}
