<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Controller: Midtrans Webhook
 * 
 * Handle server-to-server notifications dari Midtrans.
 * URL ini harus di-set di Midtrans Dashboard -> Settings -> Payment Notification URL
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
     */
    public function handleNotification(Request $request)
    {
        try {
            $notification = $request->all();
            
            Log::info('Midtrans Notification Received', $notification);

            // Verify signature
            $orderId = $notification['order_id'] ?? null;
            $statusCode = $notification['status_code'] ?? null;
            $grossAmount = $notification['gross_amount'] ?? null;
            $serverKey = config('services.midtrans.server_key');
            
            $signatureKey = $notification['signature_key'] ?? '';
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            
            if ($signatureKey !== $expectedSignature) {
                Log::warning('Midtrans: Invalid signature', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            }

            // Find payment by invoice number (order_id)
            $payment = Payment::where('invoice_number', $orderId)->first();
            
            if (!$payment) {
                Log::warning('Midtrans: Payment not found', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            // Process based on transaction status
            $transactionStatus = $notification['transaction_status'] ?? '';
            $fraudStatus = $notification['fraud_status'] ?? '';

            if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                // Payment success
                if ($fraudStatus === 'accept' || empty($fraudStatus)) {
                    $this->activatePayment($payment);
                }
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                // Payment failed
                $payment->update([
                    'status' => 'failed',
                    'admin_notes' => "Midtrans status: {$transactionStatus}",
                ]);
            } elseif ($transactionStatus === 'pending') {
                // Still pending - do nothing
                Log::info('Midtrans: Payment still pending', ['order_id' => $orderId]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Activate payment and create subscription
     */
    protected function activatePayment(Payment $payment)
    {
        if ($payment->status === 'paid') {
            return; // Already processed
        }

        DB::beginTransaction();
        try {
            // Update payment
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'midtrans',
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
     * Send payment success email
     */
    protected function sendPaymentSuccessEmail(Payment $payment)
    {
        try {
            $user = $payment->user;
            if ($user && $user->email) {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->queue(new \App\Mail\PaymentSuccessMail($payment));
                
                // Log email
                \App\Models\EmailLog::log(
                    $user->id,
                    $user->email,
                    'Pembayaran Berhasil - ' . $payment->invoice_number,
                    'payment_success'
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payment success email: ' . $e->getMessage());
        }
    }
}
