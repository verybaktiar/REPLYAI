<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service: Midtrans Payment Gateway
 * 
 * Menangani integrasi dengan Midtrans untuk pembayaran otomatis.
 * Mendukung: VA, E-Wallet, Credit Card, QRIS, Convenience Store
 */
class MidtransService
{
    public function __construct()
    {
        // Configure Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Buat Snap transaction untuk popup payment
     * 
     * @param Payment $payment
     * @return array ['token' => string, 'redirect_url' => string]
     */
    public function createSnapTransaction(Payment $payment): array
    {
        // Cek apakah sudah ada snap token yang valid (dibuat dalam 24 jam terakhir)
        $metadata = $payment->metadata ?? [];
        if (isset($metadata['snap_token']) && isset($metadata['midtrans_created_at'])) {
            $createdAt = \Carbon\Carbon::parse($metadata['midtrans_created_at']);
            if ($createdAt->diffInHours(now()) < 23) {
                Log::info('Reusing existing Midtrans Snap token', [
                    'invoice' => $payment->invoice_number,
                ]);
                return [
                    'token' => $metadata['snap_token'],
                    'redirect_url' => null,
                ];
            }
        }

        $user = $payment->user;
        $plan = $payment->plan;

        // Gunakan order_id unik dengan timestamp untuk menghindari duplicate
        $orderId = $payment->invoice_number . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $payment->total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => $plan->slug,
                    'price' => (int) $payment->total,
                    'quantity' => 1,
                    'name' => $plan->name . ' (' . $payment->duration_months . ' bulan)',
                ],
            ],
            'callbacks' => [
                'finish' => config('midtrans.finish_url') . '?invoice=' . $payment->invoice_number,
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'hours',
                'duration' => 24,
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            // Simpan snap token dan order_id ke payment
            $payment->update([
                'payment_method' => Payment::METHOD_MIDTRANS,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'snap_token' => $snapToken,
                    'midtrans_order_id' => $orderId,
                    'midtrans_created_at' => now()->toDateTimeString(),
                ]),
            ]);

            Log::info('Midtrans Snap token created', [
                'invoice' => $payment->invoice_number,
                'order_id' => $orderId,
                'amount' => $payment->total,
            ]);

            return [
                'token' => $snapToken,
                'redirect_url' => null, // Snap uses token, not redirect
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Snap creation failed', [
                'invoice' => $payment->invoice_number,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle notification dari Midtrans webhook
     * 
     * @return Payment
     */
    public function handleNotification(): Payment
    {
        try {
            $notification = new \Midtrans\Notification();
            
            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? 'accept';
            $paymentType = $notification->payment_type;

            Log::info('Midtrans notification received', [
                'order_id' => $orderId,
                'status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType,
            ]);

            // Extract invoice number dari order_id (format: INV-2026-00001-1234567890)
            // Order ID mungkin punya timestamp suffix
            $invoiceNumber = preg_replace('/-\d{10}$/', '', $orderId);
            
            // Coba cari payment berdasarkan invoice number
            $payment = Payment::where('invoice_number', $invoiceNumber)->first();
            
            // Fallback: coba cari berdasarkan metadata order_id
            if (!$payment) {
                $payment = Payment::whereJsonContains('metadata->midtrans_order_id', $orderId)->first();
            }
            
            if (!$payment) {
                throw new Exception("Payment not found for order: {$orderId}");
            }

            // Update payment berdasarkan status
            switch ($transactionStatus) {
                case 'capture':
                case 'settlement':
                    if ($fraudStatus === 'accept') {
                        $this->markAsPaid($payment, $notification);
                    }
                    break;

                case 'pending':
                    $this->markAsPending($payment, $notification);
                    break;

                case 'deny':
                case 'expire':
                case 'cancel':
                    $this->markAsFailed($payment, $notification);
                    break;

                default:
                    Log::warning('Unknown Midtrans status', [
                        'order_id' => $orderId,
                        'status' => $transactionStatus,
                    ]);
            }

            return $payment->fresh();
        } catch (Exception $e) {
            Log::error('Midtrans notification handling failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark payment as paid dan aktivasi subscription
     */
    private function markAsPaid(Payment $payment, $notification): void
    {
        if ($payment->status === Payment::STATUS_PAID) {
            return; // Already processed
        }

        $payment->update([
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
            'metadata' => array_merge($payment->metadata ?? [], [
                'midtrans_transaction_id' => $notification->transaction_id,
                'midtrans_payment_type' => $notification->payment_type,
                'midtrans_transaction_time' => $notification->transaction_time,
            ]),
        ]);

        // Aktivasi subscription
        $this->activateSubscription($payment);

        Log::info('Payment marked as paid via Midtrans', [
            'invoice' => $payment->invoice_number,
            'midtrans_id' => $notification->transaction_id,
        ]);
    }

    /**
     * Mark payment as pending
     */
    private function markAsPending(Payment $payment, $notification): void
    {
        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'midtrans_transaction_id' => $notification->transaction_id,
                'midtrans_payment_type' => $notification->payment_type,
                'midtrans_va_number' => $notification->va_numbers[0]->va_number ?? null,
            ]),
        ]);
    }

    /**
     * Mark payment as failed
     */
    private function markAsFailed(Payment $payment, $notification): void
    {
        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'metadata' => array_merge($payment->metadata ?? [], [
                'midtrans_status' => $notification->transaction_status,
                'midtrans_failed_at' => now()->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Aktivasi subscription setelah payment sukses
     */
    private function activateSubscription(Payment $payment): void
    {
        $startDate = now();
        $endDate = $startDate->copy()->addMonths($payment->duration_months);

        $subscription = Subscription::where('user_id', $payment->user_id)->first();

        if ($subscription) {
            $subscription->update([
                'plan_id' => $payment->plan_id,
                'status' => 'active',
                'starts_at' => $startDate,
                'expires_at' => $endDate,
                'grace_period_ends_at' => null,
            ]);
        } else {
            $subscription = Subscription::create([
                'user_id' => $payment->user_id,
                'plan_id' => $payment->plan_id,
                'status' => 'active',
                'starts_at' => $startDate,
                'expires_at' => $endDate,
            ]);
        }

        // Update payment dengan subscription_id
        $payment->update(['subscription_id' => $subscription->id]);

        Log::info('Subscription activated via Midtrans payment', [
            'user_id' => $payment->user_id,
            'plan_id' => $payment->plan_id,
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Cek status transaksi langsung ke Midtrans
     * 
     * @param string $orderId Invoice number
     * @return object
     */
    public function getTransactionStatus(string $orderId): object
    {
        return \Midtrans\Transaction::status($orderId);
    }

    /**
     * Get client key untuk frontend
     */
    public function getClientKey(): string
    {
        return config('midtrans.client_key');
    }

    /**
     * Get Snap JS URL
     */
    public function getSnapUrl(): string
    {
        return config('midtrans.snap_url');
    }
}
