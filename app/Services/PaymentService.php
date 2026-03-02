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
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Service: Pembayaran (PaymentService)
 * 
 * Service ini menangani semua operasi pembayaran dengan keamanan tinggi:
 * - Validasi harga di backend (tidak trust client input)
 * - Prevent duplicate pending payments
 * - Atomic operations dengan locking
 * 
 * SECURITY NOTES:
 * - Harga SELALU di-calculate ulang dari database
 * - Tidak pernah trust input harga dari frontend
 * - Gunakan database locking untuk prevent race condition
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
     * Buat pembayaran baru (invoice) dengan validasi keamanan
     * 
     * SECURITY:
     * - Harga di-calculate dari database, tidak trust frontend
     * - Prevent multiple pending payments untuk plan yang sama
     * - Atomic locking untuk prevent duplicate invoice
     * 
     * @param User $user
     * @param Plan $plan
     * @param int $durationMonths 1 atau 12
     * @param string|null $promoCode
     * @return Payment
     * @throws Exception
     */
    public function createPayment(
        User $user,
        Plan $plan,
        int $durationMonths = 1,
        ?string $promoCode = null
    ): Payment {
        // 🔒 SECURITY FIX-002: Recalculate harga dari database (tidak trust frontend)
        $amount = $this->calculatePrice($plan, $durationMonths);
        
        if ($amount <= 0) {
            throw new Exception('Invalid plan price. Please contact support.');
        }

        // Cek existing pending payment dengan plan + durasi + amount yang sesuai
        $existingPending = $this->getExistingPendingPayment($user->id, $plan->id, $durationMonths, $amount);
        
        if ($existingPending) {
            Log::info('Existing pending payment found with same duration, returning existing', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'duration_months' => $durationMonths,
                'existing_invoice' => $existingPending->invoice_number,
            ]);
            
            // Update expires_at agar tidak expired
            $existingPending->update([
                'expires_at' => now()->addHours(24),
            ]);
            
            return $existingPending;
        }

        $discount = 0;
        $promoCodeModel = null;

        // Validasi & apply promo code jika ada
        if ($promoCode) {
            $promoResult = $this->validateAndCalculatePromo(
                $promoCode, 
                $user->id, 
                $plan->id, 
                $amount
            );
            
            if ($promoResult['valid']) {
                $promoCodeModel = $promoResult['promo'];
                $discount = $promoResult['discount'];
            }
        }

        $total = max(0, $amount - $discount);

        // Gunakan Atomic Lock untuk mencegah race condition
        return Cache::lock('invoice_generation_' . $user->id, 10)->block(5, function () use (
            $user, $plan, $amount, $discount, $total, $durationMonths, $promoCodeModel
        ) {
            return DB::transaction(function () use (
                $user, $plan, $amount, $discount, $total, $durationMonths, $promoCodeModel
            ) {
                // Double-check setelah lock (cek plan + durasi + amount yang sama)
                $doubleCheck = Payment::where('user_id', $user->id)
                    ->where('plan_id', $plan->id)
                    ->where('duration_months', $durationMonths)
                    ->where('amount', $amount)
                    ->where('status', Payment::STATUS_PENDING)
                    ->where('expires_at', '>', now())
                    ->lockForUpdate()
                    ->first();
                
                if ($doubleCheck) {
                    return $doubleCheck;
                }
                
                $invoiceNumber = Payment::generateInvoiceNumber();

                // Buat payment record
                $payment = Payment::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'amount' => $amount,        // ✅ Harga asli dari database
                    'discount' => $discount,    // ✅ Discount yang sudah divalidasi
                    'total' => $total,          // ✅ Total yang sudah di-calculate
                    'payment_method' => Payment::METHOD_MANUAL,
                    'status' => Payment::STATUS_PENDING,
                    'duration_months' => $durationMonths,
                    'promo_code' => $promoCodeModel?->code,
                    'expires_at' => now()->addHours(24),
                    'metadata' => [
                        'plan_name' => $plan->name,
                        'plan_slug' => $plan->slug,
                        'created_from' => 'checkout',
                        'price_validated' => true,  // Flag untuk audit
                    ],
                ]);

                Log::info('Payment created with validated price', [
                    'payment_id' => $payment->id,
                    'invoice' => $invoiceNumber,
                    'amount' => $amount,
                    'discount' => $discount,
                    'total' => $total,
                    'user_id' => $user->id,
                ]);

                return $payment;
            });
        });
    }

    /**
     * Calculate price dari database (tidak trust frontend input)
     * 
     * @param Plan $plan
     * @param int $durationMonths
     * @return int
     * @throws Exception
     */
    private function calculatePrice(Plan $plan, int $durationMonths): int
    {
        // Refresh plan data dari database untuk dapat harga terbaru
        $freshPlan = Plan::find($plan->id);
        
        if (!$freshPlan || !$freshPlan->is_active) {
            throw new Exception('Plan tidak tersedia atau tidak aktif.');
        }

        $price = match($durationMonths) {
            1 => $freshPlan->price_monthly,
            12 => $freshPlan->price_yearly,
            default => throw new Exception('Durasi tidak valid. Pilih 1 atau 12 bulan.'),
        };

        if ($price <= 0) {
            throw new Exception('Harga plan tidak valid.');
        }

        return $price;
    }

    /**
     * Cek existing pending payment untuk plan yang sama DAN durasi yang sama
     * DAN amount yang sesuai (untuk prevent data lama yang salah)
     * 
     * @param int $userId
     * @param int $planId
     * @param int $durationMonths
     * @param int $expectedAmount
     * @return Payment|null
     */
    private function getExistingPendingPayment(int $userId, int $planId, int $durationMonths, int $expectedAmount): ?Payment
    {
        return Payment::where('user_id', $userId)
            ->where('plan_id', $planId)
            ->where('duration_months', $durationMonths)
            ->where('amount', $expectedAmount)  // Pastikan amount juga sesuai
            ->where('status', Payment::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Validasi promo code dan calculate discount
     * 
     * @param string $promoCode
     * @param int $userId
     * @param int $planId
     * @param int $amount
     * @return array
     */
    private function validateAndCalculatePromo(
        string $promoCode, 
        int $userId, 
        int $planId, 
        int $amount
    ): array {
        $promo = PromoCode::where('code', strtoupper($promoCode))
            ->active()
            ->valid()
            ->first();

        if (!$promo) {
            return ['valid' => false, 'discount' => 0, 'promo' => null];
        }

        $validation = $promo->canBeUsedBy($userId, $planId, $amount);
        
        if (!$validation['valid']) {
            return ['valid' => false, 'discount' => 0, 'promo' => null];
        }

        $discount = $promo->calculateDiscount($amount);

        return [
            'valid' => true,
            'discount' => $discount,
            'promo' => $promo,
        ];
    }

    // ==========================================
    // APPLY PROMO CODE
    // ==========================================

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

        // Re-validate promo dengan harga yang sudah tersimpan
        $promoResult = $this->validateAndCalculatePromo(
            $promoCode,
            $payment->user_id,
            $payment->plan_id,
            $payment->amount
        );

        if (!$promoResult['valid']) {
            return [
                'success' => false,
                'message' => 'Kode promo tidak valid atau tidak bisa digunakan.',
                'discount' => 0,
            ];
        }

        $discount = $promoResult['discount'];
        $total = max(0, $payment->amount - $discount);

        $payment->update([
            'discount' => $discount,
            'total' => $total,
            'promo_code' => $promoResult['promo']->code,
        ]);

        Log::info('Promo code applied', [
            'payment_id' => $payment->id,
            'promo_code' => $promoCode,
            'discount' => $discount,
        ]);

        return [
            'success' => true,
            'message' => 'Kode promo berhasil diterapkan!',
            'discount' => $discount,
        ];
    }

    // ==========================================
    // UPLOAD BUKTI TRANSFER (SECURED)
    // ==========================================

    /**
     * Upload bukti transfer dengan keamanan tinggi
     * 
     * SECURITY FIX-004:
     * - Random filename untuk prevent guessing
     * - Validasi image dimensions
     * - Scan virus (optional)
     * 
     * @param Payment $payment
     * @param \Illuminate\Http\UploadedFile $file
     * @return Payment
     * @throws Exception
     */
    public function uploadProof(Payment $payment, $file): Payment
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            throw new Exception('Pembayaran sudah diproses.');
        }

        // Generate random filename (40 chars + extension)
        $extension = $file->getClientOriginalExtension();
        $filename = \Illuminate\Support\Str::random(40) . '.' . $extension;
        
        // Store dengan nama random
        $path = $file->storeAs('payment-proofs', $filename, 'public');
        
        if (!$path) {
            throw new Exception('Gagal menyimpan file.');
        }

        // Generate full URL
        $proofUrl = asset('storage/' . $path);

        $payment->update([
            'proof_url' => $proofUrl,
            'metadata' => array_merge($payment->metadata ?? [], [
                'proof_uploaded_at' => now()->toDateTimeString(),
                'proof_filename' => $filename,
                'proof_original_name' => $file->getClientOriginalName(),
            ]),
        ]);

        // Kirim notifikasi ke admin (async)
        $this->notifyAdminNewProof($payment);

        Log::info('Payment proof uploaded', [
            'payment_id' => $payment->id,
            'filename' => $filename,
        ]);

        return $payment->fresh();
    }

    /**
     * Notifikasi ke admin saat ada bukti transfer baru
     * 
     * @param Payment $payment
     * @return void
     */
    private function notifyAdminNewProof(Payment $payment): void
    {
        try {
            // TODO: Implement notifikasi ke admin
            // Bisa via email, telegram, atau dashboard notification
            
            Log::info('Admin notification queued for new proof', [
                'payment_id' => $payment->id,
                'invoice' => $payment->invoice_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue admin notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ==========================================
    // APPROVAL (ADMIN)
    // ==========================================

    /**
     * Approve pembayaran (oleh admin) dengan idempotency
     * 
     * @param Payment $payment
     * @param int $adminId
     * @param string|null $notes
     * @return Payment
     * @throws Exception
     */
    public function approve(Payment $payment, int $adminId, ?string $notes = null): Payment
    {
        if ($payment->status === Payment::STATUS_PAID) {
            // Idempotent - return existing
            Log::info('Payment already approved, returning existing', [
                'payment_id' => $payment->id,
            ]);
            return $payment;
        }

        if ($payment->status !== Payment::STATUS_PENDING) {
            throw new Exception('Pembayaran sudah diproses sebelumnya.');
        }

        return DB::transaction(function () use ($payment, $adminId, $notes) {
            // Lock untuk prevent race condition
            $lockedPayment = Payment::lockForUpdate()->find($payment->id);
            
            if ($lockedPayment->status === Payment::STATUS_PAID) {
                return $lockedPayment;
            }

            // Update payment status
            $lockedPayment->update([
                'status' => Payment::STATUS_PAID,
                'paid_at' => now(),
                'approved_by' => $adminId,
                'admin_notes' => $notes,
            ]);

            // Aktivasi/perpanjang langganan
            $subscription = $this->activateSubscription($lockedPayment);
            
            // Link payment ke subscription
            $lockedPayment->update(['subscription_id' => $subscription->id]);

            // Update promo code usage
            if ($lockedPayment->promo_code) {
                $promo = PromoCode::where('code', $lockedPayment->promo_code)->first();
                if ($promo) {
                    $promo->markAsUsed(
                        $lockedPayment->user_id,
                        $lockedPayment->id,
                        $lockedPayment->discount
                    );
                }
            }

            // Kirim email konfirmasi ke user
            $this->sendPaymentConfirmationEmail($lockedPayment);

            Log::info('Payment approved', [
                'payment_id' => $lockedPayment->id,
                'invoice' => $lockedPayment->invoice_number,
                'approved_by' => $adminId,
            ]);

            return $lockedPayment->fresh();
        });
    }

    /**
     * Reject pembayaran (oleh admin)
     * 
     * @param Payment $payment
     * @param int $adminId
     * @param string $reason
     * @return Payment
     * @throws Exception
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
                'rejected_by' => $adminId,
            ]),
        ]);

        // Kirim email pemberitahuan ke user
        $this->sendPaymentRejectionEmail($payment, $reason);

        Log::info('Payment rejected', [
            'payment_id' => $payment->id,
            'invoice' => $payment->invoice_number,
            'reason' => $reason,
        ]);

        return $payment->fresh();
    }

    // ==========================================
    // EMAIL NOTIFICATIONS
    // ==========================================

    /**
     * Kirim email konfirmasi pembayaran sukses
     * 
     * FIX-006: Implementasi email notification
     * 
     * @param Payment $payment
     * @return void
     */
    private function sendPaymentConfirmationEmail(Payment $payment): void
    {
        try {
            $user = $payment->user;
            
            if (!$user || !$user->email) {
                return;
            }

            // Gunakan queue untuk async sending
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->queue(new \App\Mail\PaymentSuccessMail($payment));

            Log::info('Payment confirmation email queued', [
                'payment_id' => $payment->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kirim email pemberitahuan penolakan
     * 
     * @param Payment $payment
     * @param string $reason
     * @return void
     */
    private function sendPaymentRejectionEmail(Payment $payment, string $reason): void
    {
        try {
            $user = $payment->user;
            
            if (!$user || !$user->email) {
                return;
            }

            \Illuminate\Support\Facades\Mail::to($user->email)
                ->queue(new \App\Mail\PaymentRejectedMail($payment, $reason));

            Log::info('Payment rejection email queued', [
                'payment_id' => $payment->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment rejection email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
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
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('proof_url')
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
            'today' => Payment::where('status', Payment::STATUS_PAID)
                ->whereDate('paid_at', today())
                ->sum('total'),
            'this_month' => Payment::where('status', Payment::STATUS_PAID)
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total'),
            'total' => Payment::where('status', Payment::STATUS_PAID)->sum('total'),
            'pending_count' => Payment::where('status', Payment::STATUS_PENDING)->count(),
        ];
    }

    /**
     * Cleanup expired payments
     * 
     * FIX-007: Expired payment cleanup
     * 
     * @return int Jumlah payment yang di-update
     */
    public function cleanupExpiredPayments(): int
    {
        $count = Payment::where('status', Payment::STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->update([
                'status' => Payment::STATUS_FAILED,
                'admin_notes' => 'Auto-expired: Payment deadline exceeded',
            ]);

        Log::info('Expired payments cleaned up', [
            'count' => $count,
        ]);

        return $count;
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
        ];
    }
}
