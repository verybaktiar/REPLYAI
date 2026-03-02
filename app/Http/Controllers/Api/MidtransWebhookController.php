<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Controller: Midtrans Webhook
 * 
 * Handle server-to-server notifications dari Midtrans.
 * URL ini harus di-set di Midtrans Dashboard -> Settings -> Payment Notification URL
 * 
 * SECURITY FEATURES:
 * - FIX-005: Signature verification (SHA512)
 * - FIX-006: Idempotency check (prevent double processing)
 * - FIX-007: Amount verification (prevent tampering)
 * - FIX-008: Double-check status via Midtrans API (optional)
 */
class MidtransWebhookController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handle notification dari Midtrans
     * POST /api/midtrans/notification
     * 
     * Security: Signature verified, idempotent
     */
    public function handleNotification(Request $request)
    {
        try {
            $notification = $request->all();
            
            Log::info('Midtrans Notification Received', [
                'order_id' => $notification['order_id'] ?? 'unknown',
                'status' => $notification['transaction_status'] ?? 'unknown',
            ]);

            // Verify required fields
            $orderId = $notification['order_id'] ?? null;
            $statusCode = $notification['status_code'] ?? null;
            $grossAmount = $notification['gross_amount'] ?? null;
            $signatureKey = $notification['signature_key'] ?? '';
            
            if (!$orderId || !$statusCode || !$grossAmount) {
                Log::warning('Midtrans: Missing required fields');
                return response()->json(['status' => 'error', 'message' => 'Missing fields'], 400);
            }

            // 🔒 FIX-005: Verify signature SHA512
            $serverKey = config('services.midtrans.server_key');
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            
            if (!hash_equals($expectedSignature, $signatureKey)) {
                Log::warning('Midtrans: Invalid signature', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            }

            // Extract invoice number dari order_id (format: INV-2026-00001-1234567890)
            // Order ID mungkin punya timestamp suffix
            $invoiceNumber = preg_replace('/-\d{10}$/', '', $orderId);
            
            // Cari payment dengan locking untuk prevent race condition
            $payment = Payment::where('invoice_number', $invoiceNumber)
                ->lockForUpdate()
                ->first();
            
            // Fallback: coba cari berdasarkan metadata order_id
            if (!$payment) {
                $payment = Payment::whereJsonContains('metadata->midtrans_order_id', $orderId)
                    ->lockForUpdate()
                    ->first();
            }
            
            if (!$payment) {
                Log::warning('Midtrans: Payment not found', [
                    'order_id' => $orderId,
                    'invoice' => $invoiceNumber,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            // 🔒 FIX-007: Verify amount matches
            $expectedAmount = (int) $payment->total;
            $receivedAmount = (int) $grossAmount;
            
            if ($receivedAmount !== $expectedAmount) {
                Log::error('Midtrans: Amount mismatch!', [
                    'payment_id' => $payment->id,
                    'expected' => $expectedAmount,
                    'received' => $receivedAmount,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Amount mismatch'], 400);
            }

            // 🔒 FIX-006: Idempotency check dengan distributed lock
            $lockKey = 'midtrans_webhook_' . $notification['transaction_id'] ?? $orderId;
            
            return Cache::lock($lockKey, 60)->block(5, function () use ($payment, $notification, $orderId) {
                // Re-check status after lock
                $payment->refresh();
                
                if ($payment->status === 'paid') {
                    Log::info('Midtrans: Payment already processed (idempotent)', [
                        'payment_id' => $payment->id,
                    ]);
                    return response()->json(['status' => 'already_processed']);
                }

                // Process based on transaction status
                $transactionStatus = $notification['transaction_status'] ?? '';
                $fraudStatus = $notification['fraud_status'] ?? '';

                if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                    // Payment success
                    if ($fraudStatus === 'accept' || empty($fraudStatus)) {
                        $this->activatePayment($payment, $notification);
                    }
                } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                    // Payment failed
                    $payment->update([
                        'status' => 'failed',
                        'admin_notes' => "Midtrans status: {$transactionStatus}",
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                            'midtrans_failed_at' => now()->toDateTimeString(),
                        ]),
                    ]);
                } elseif ($transactionStatus === 'pending') {
                    // Still pending - update metadata with VA number dll
                    $payment->update([
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                            'midtrans_va_number' => $notification['va_numbers'][0]['va_number'] ?? null,
                            'midtrans_payment_type' => $notification['payment_type'] ?? null,
                        ]),
                    ]);
                }

                return response()->json(['status' => 'success']);
            });

        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 500);
        }
    }

    /**
     * Activate payment and create subscription
     * 
     * @param Payment $payment
     * @param array $notification
     * @return void
     */
    protected function activatePayment(Payment $payment, array $notification): void
    {
        DB::beginTransaction();
        try {
            // Update payment
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'midtrans',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                    'midtrans_payment_type' => $notification['payment_type'] ?? null,
                    'midtrans_transaction_time' => $notification['transaction_time'] ?? null,
                ]),
            ]);

            // Calculate subscription dates
            $startDate = now();
            $endDate = $startDate->copy()->addMonths($payment->duration_months);

            // Create or update subscription
            $subscription = Subscription::where('user_id', $payment->user_id)->first();

            if ($subscription) {
                // If existing subscription is still active, extend it
                if ($subscription->status === 'active' && $subscription->expires_at > now()) {
                    $endDate = $subscription->expires_at->copy()->addMonths($payment->duration_months);
                }
                
                $subscription->update([
                    'plan_id' => $payment->plan_id,
                    'status' => 'active',
                    'starts_at' => $startDate,
                    'expires_at' => $endDate,
                    'grace_period_ends_at' => null,
                ]);
            } else {
                Subscription::create([
                    'user_id' => $payment->user_id,
                    'plan_id' => $payment->plan_id,
                    'status' => 'active',
                    'starts_at' => $startDate,
                    'expires_at' => $endDate,
                ]);
            }

            // Update payment dengan subscription_id
            $payment->update(['subscription_id' => $subscription->id ?? null]);

            DB::commit();

            // Send notification email
            $this->sendPaymentSuccessEmail($payment);

            Log::info('Midtrans: Payment activated', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans: Failed to activate payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check Midtrans transaction status (for debugging)
     * GET /api/midtrans/status/{invoice}
     */
    public function checkStatus(string $invoice)
    {
        try {
            $payment = Payment::where('invoice_number', $invoice)->firstOrFail();
            
            // Get transaction status from Midtrans
            $status = $this->midtransService->getTransactionStatus($invoice);
            
            return response()->json([
                'invoice' => $invoice,
                'payment_status' => $payment->status,
                'midtrans_status' => $status->transaction_status ?? 'unknown',
                'gross_amount' => $status->gross_amount ?? null,
                'payment_type' => $status->payment_type ?? null,
                'transaction_time' => $status->transaction_time ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send payment success email
     */
    protected function sendPaymentSuccessEmail(Payment $payment): void
    {
        try {
            $user = $payment->user;
            if ($user && $user->email) {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->queue(new \App\Mail\PaymentSuccessMail($payment));
                
                // Log email
                if (class_exists('\App\Models\EmailLog')) {
                    \App\Models\EmailLog::log(
                        $user->id,
                        $user->email,
                        'Pembayaran Berhasil - ' . $payment->invoice_number,
                        'payment_success'
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment success email: ' . $e->getMessage());
        }
    }
}
