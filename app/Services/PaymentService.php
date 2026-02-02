<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\PromoCode;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service: Pembayaran (PaymentService)
 * 
 * Service ini menangani semua operasi pembayaran:
 * - Buat invoice/pembayaran baru
 * - Apply promo code
 * - Upload bukti transfer
 * - Approve/reject pembayaran
 * - Aktivasi langganan setelah bayar
 * 
 * Penggunaan:
 * $service = app(PaymentService::class);
 * $payment = $service->createPayment($user, $plan, 1, 'PROMO50');
 */
class PaymentService
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    // ==========================================
    // MEMBUAT PEMBAYARAN
    // ==========================================

    /**
     * Buat pembayaran baru (invoice)
     * 
     * @param User $user
     * @param Plan $plan
     * @param int $durationMonths 1 atau 12
     * @param string|null $promoCode
     * @return Payment
     */
    public function createPayment(
        User $user,
        Plan $plan,
        int $durationMonths = 1,
        ?string $promoCode = null
    ): Payment {
        // Hitung harga
        $amount = $durationMonths === 12 
            ? $plan->price_yearly 
            : $plan->price_monthly;

        $discount = 0;
        $promoCodeModel = null;

        // Cek promo code jika ada
        if ($promoCode) {
            $promoCodeModel = PromoCode::where('code', strtoupper($promoCode))
                ->active()
                ->valid()
                ->first();

            if ($promoCodeModel) {
                $validation = $promoCodeModel->canBeUsedBy($user->id, $plan->id, $amount);
                
                if ($validation['valid']) {
                    $discount = $promoCodeModel->calculateDiscount($amount);
                }
            }
        }

        $total = max(0, $amount - $discount);

        // Buat payment record dengan retry logic (untuk handle race condition nomor invoice)
        $attempts = 0;
        $maxAttempts = 3;
        $payment = null;

        do {
            try {
                $payment = Payment::create([
                    'invoice_number' => Payment::generateInvoiceNumber(),
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'amount' => $amount,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_method' => Payment::METHOD_MANUAL,
                    'status' => Payment::STATUS_PENDING,
                    'duration_months' => $durationMonths,
                    'promo_code' => $promoCodeModel?->code,
                    'expires_at' => now()->addHours(24), // Expired dalam 24 jam
                    'metadata' => [
                        'plan_name' => $plan->name,
                        'plan_slug' => $plan->slug,
                        'created_from' => 'checkout',
                    ],
                ]);
                
                break; // Berhasil, keluar dari loop

            } catch (\Illuminate\Database\QueryException $e) {
                $errorCode = $e->errorInfo[1] ?? 0;
                
                // Jika error duplicate entry, kita retry
                if ($errorCode == 1062) {
                    $attempts++;
                    if ($attempts >= $maxAttempts) throw $e;
                    usleep(100000); // Wait 100ms before retry
                    continue;
                }
                
                throw $e;
            }
        } while ($attempts < $maxAttempts);

        return $payment;
    }

    /**
     * Apply promo code ke pembayaran yang sudah ada
     * 
     * @param Payment $payment
     * @param string $promoCode
     * @return array ['success' => bool, 'message' => string, 'discount' => int]
     */
    public function applyPromoCode(Payment $payment, string $promoCode): array
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return [
                'success' => false,
                'message' => 'Tidak bisa apply promo ke pembayaran yang sudah diproses.',
                'discount' => 0,
            ];
        }

        $promo = PromoCode::where('code', strtoupper($promoCode))
            ->active()
            ->valid()
            ->first();

        if (!$promo) {
            return [
                'success' => false,
                'message' => 'Kode promo tidak ditemukan atau tidak valid.',
                'discount' => 0,
            ];
        }

        $validation = $promo->canBeUsedBy(
            $payment->user_id,
            $payment->plan_id,
            $payment->amount
        );

        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'],
                'discount' => 0,
            ];
        }

        $discount = $promo->calculateDiscount($payment->amount);
        $total = max(0, $payment->amount - $discount);

        $payment->update([
            'discount' => $discount,
            'total' => $total,
            'promo_code' => $promo->code,
        ]);

        return [
            'success' => true,
            'message' => 'Kode promo berhasil diterapkan!',
            'discount' => $discount,
        ];
    }

    // ==========================================
    // UPLOAD BUKTI TRANSFER
    // ==========================================

    /**
     * Upload bukti transfer
     * 
     * @param Payment $payment
     * @param string $proofUrl URL file bukti transfer
     * @return Payment
     */
    public function uploadProof(Payment $payment, string $proofUrl): Payment
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            throw new Exception('Pembayaran sudah diproses.');
        }

        $payment->update([
            'proof_url' => $proofUrl,
            'metadata' => array_merge($payment->metadata ?? [], [
                'proof_uploaded_at' => now()->toDateTimeString(),
            ]),
        ]);

        // TODO: Kirim notifikasi ke admin (email/telegram)
        // $this->notifyAdminNewProof($payment);

        return $payment->fresh();
    }

    // ==========================================
    // APPROVAL (ADMIN)
    // ==========================================

    /**
     * Approve pembayaran (oleh admin)
     * 
     * @param Payment $payment
     * @param int $adminId
     * @param string|null $notes
     * @return Payment
     */
    public function approve(Payment $payment, int $adminId, ?string $notes = null): Payment
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            throw new Exception('Pembayaran sudah diproses sebelumnya.');
        }

        return DB::transaction(function () use ($payment, $adminId, $notes) {
            // Update payment status
            $payment->update([
                'status' => Payment::STATUS_PAID,
                'paid_at' => now(),
                'approved_by' => $adminId,
                'admin_notes' => $notes,
            ]);

            // Aktivasi/perpanjang langganan
            $subscription = $this->activateSubscription($payment);
            
            // Link payment ke subscription
            $payment->update(['subscription_id' => $subscription->id]);

            // Update promo code usage jika ada
            if ($payment->promo_code) {
                $promo = PromoCode::where('code', $payment->promo_code)->first();
                if ($promo) {
                    $promo->markAsUsed(
                        $payment->user_id,
                        $payment->id,
                        $payment->discount
                    );
                }
            }

            // TODO: Kirim email konfirmasi ke user
            // $this->sendConfirmationEmail($payment);

            // Log aktivitas admin
            Log::info('Payment approved', [
                'payment_id' => $payment->id,
                'invoice' => $payment->invoice_number,
                'user_id' => $payment->user_id,
                'approved_by' => $adminId,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Reject pembayaran (oleh admin)
     * 
     * @param Payment $payment
     * @param int $adminId
     * @param string $reason
     * @return Payment
     */
    public function reject(Payment $payment, int $adminId, string $reason): Payment
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            throw new Exception('Pembayaran sudah diproses sebelumnya.');
        }

        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'approved_by' => $adminId,
            'admin_notes' => "Ditolak: {$reason}",
            'metadata' => array_merge($payment->metadata ?? [], [
                'rejected_at' => now()->toDateTimeString(),
                'reject_reason' => $reason,
            ]),
        ]);

        // TODO: Kirim email pemberitahuan ke user
        // $this->sendRejectionEmail($payment, $reason);

        Log::info('Payment rejected', [
            'payment_id' => $payment->id,
            'invoice' => $payment->invoice_number,
            'user_id' => $payment->user_id,
            'rejected_by' => $adminId,
            'reason' => $reason,
        ]);

        return $payment->fresh();
    }

    // ==========================================
    // AKTIVASI LANGGANAN
    // ==========================================

    /**
     * Aktivasi/perpanjang langganan setelah pembayaran sukses
     * 
     * @param Payment $payment
     * @return Subscription
     */
    public function activateSubscription(Payment $payment): Subscription
    {
        $user = $payment->user;
        $plan = $payment->plan;

        // Cek apakah sudah punya langganan
        $existingSubscription = $this->subscriptionService->getActiveSubscription($user);

        if ($existingSubscription && $existingSubscription->plan_id === $plan->id) {
            // Perpanjang langganan yang sama
            return $this->subscriptionService->renew($user, $payment->duration_months, $payment);
        } else {
            // Upgrade ke paket baru
            return $this->subscriptionService->upgrade($user, $plan, $payment->duration_months, $payment);
        }
    }

    // ==========================================
    // QUERY HELPERS
    // ==========================================

    /**
     * Ambil pembayaran pending yang perlu approval
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingApprovals()
    {
        return Payment::with(['user', 'plan'])
            ->pending()
            ->perluApproval()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Ambil riwayat pembayaran user
     * 
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPayments(int $userId)
    {
        return Payment::with('plan')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil ringkasan revenue
     * 
     * @return array
     */
    public function getRevenueStats(): array
    {
        return [
            'today' => Payment::dibayar()
                ->whereDate('paid_at', today())
                ->sum('total'),
            'this_month' => Payment::dibayar()
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total'),
            'total' => Payment::dibayar()->sum('total'),
            'pending_count' => Payment::pending()->count(),
        ];
    }

    // ==========================================
    // BANK INFO (untuk manual transfer)
    // ==========================================

    /**
     * Ambil info rekening bank untuk transfer
     * 
     * @return array
     */
    public static function getBankInfo(): array
    {
        return [
            [
                'bank' => 'BCA',
                'account_number' => env('BANK_BCA_NUMBER', '1234567890'),
                'account_name' => env('BANK_BCA_NAME', 'PT ReplyAI Indonesia'),
            ],
            [
                'bank' => 'Mandiri',
                'account_number' => env('BANK_MANDIRI_NUMBER', '0987654321'),
                'account_name' => env('BANK_MANDIRI_NAME', 'PT ReplyAI Indonesia'),
            ],
            // Tambah bank lain sesuai kebutuhan
        ];
    }
}
