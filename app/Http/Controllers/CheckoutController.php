<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;

/**
 * Controller: Checkout
 * 
 * Controller untuk proses checkout dan pembayaran dengan keamanan tinggi.
 * 
 * SECURITY FIXES:
 * - FIX-003: Midtrans callback verify ke API (tidak trust URL params)
 * - FIX-004: Secure file upload dengan random filename
 * - FIX-001: Price validation di backend via PaymentService
 */
class CheckoutController extends Controller
{
    protected PaymentService $paymentService;
    protected MidtransService $midtransService;

    public function __construct(PaymentService $paymentService, MidtransService $midtransService)
    {
        $this->paymentService = $paymentService;
        $this->midtransService = $midtransService;
    }

    /**
     * Halaman pricing (public)
     */
    public function pricing()
    {
        $plans = Plan::aktif()->urut()->get();
        
        return view('pages.pricing.index', compact('plans'));
    }

    /**
     * Halaman checkout untuk paket tertentu
     */
    public function checkout(Plan $plan)
    {
        if (!$plan->is_active) {
            return redirect()->route('pricing')
                ->with('error', 'Paket tidak tersedia.');
        }

        // Gratis tidak perlu checkout
        if ($plan->is_free) {
            return redirect()->route('pricing')
                ->with('info', 'Paket gratis tidak perlu checkout.');
        }

        $bankInfo = PaymentService::getBankInfo();

        return view('pages.checkout.index', compact('plan', 'bankInfo'));
    }

    /**
     * Proses checkout - buat invoice
     */
    public function processCheckout(Request $request, Plan $plan)
    {
        $request->validate([
            'duration' => 'required|in:1,12',
            'promo_code' => 'nullable|string|max:50',
            'payment_method' => 'nullable|in:manual,midtrans',
        ]);

        $user = Auth::user();
        $duration = (int) $request->duration;
        $promoCode = $request->promo_code;
        $paymentMethod = $request->payment_method ?? 'manual';

        // DEBUG: Log request data
        Log::info('Checkout process started', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'duration' => $duration,
            'price_monthly' => $plan->price_monthly,
            'price_yearly' => $plan->price_yearly,
        ]);

        try {
            // 🔒 FIX-001 & FIX-002: PaymentService akan handle:
            // - Recalculate harga dari database
            // - Prevent multiple pending payments
            $payment = $this->paymentService->createPayment(
                $user,
                $plan,
                $duration,
                $promoCode
            );

            // DEBUG: Log created payment
            Log::info('Payment created', [
                'payment_id' => $payment->id,
                'invoice' => $payment->invoice_number,
                'amount' => $payment->amount,
                'total' => $payment->total,
                'duration_months' => $payment->duration_months,
                'is_existing' => $payment->created_at < now()->subMinutes(1),
            ]);

            // Jika existing payment dikembalikan, redirect ke payment page
            if ($payment->created_at < now()->subMinutes(1)) {
                return redirect()->route('checkout.payment', $payment->invoice_number)
                    ->with('info', 'Anda sudah memiliki invoice pending. Silakan selesaikan pembayaran.');
            }

            // Jika pilih Midtrans, langsung redirect ke Midtrans payment
            if ($paymentMethod === 'midtrans') {
                return redirect()->route('checkout.midtrans.pay', $payment->invoice_number);
            }

            return redirect()->route('checkout.payment', $payment->invoice_number)
                ->with('success', 'Invoice berhasil dibuat. Silakan lakukan pembayaran.');

        } catch (\Exception $e) {
            Log::error('Checkout failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()
                ->with('error', 'Gagal membuat invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Halaman detail pembayaran (invoice)
     */
    public function payment(string $invoiceNumber)
    {
        $payment = Payment::where('invoice_number', $invoiceNumber)
            ->where('user_id', Auth::id())
            ->with('plan')
            ->firstOrFail();

        $bankInfo = PaymentService::getBankInfo();
        
        // Get Midtrans client key untuk Snap
        $midtransClientKey = $this->midtransService->getClientKey();
        $midtransSnapUrl = $this->midtransService->getSnapUrl();

        return view('pages.checkout.payment', compact('payment', 'bankInfo', 'midtransClientKey', 'midtransSnapUrl'));
    }

    /**
     * Bayar dengan Midtrans - Generate Snap token
     * 
     * @param string $invoiceNumber
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function payWithMidtrans(string $invoiceNumber, Request $request)
    {
        $payment = Payment::where('invoice_number', $invoiceNumber)
            ->where('user_id', Auth::id())
            ->with(['plan', 'user'])
            ->firstOrFail();

        // Cek status payment
        if ($payment->status !== Payment::STATUS_PENDING) {
            return redirect()->route('checkout.success', $invoiceNumber)
                ->with('info', 'Pembayaran sudah diproses.');
        }

        // Cek apakah payment sudah expired
        if ($payment->expires_at < now()) {
            return redirect()->route('pricing')
                ->with('error', 'Invoice sudah expired. Silakan buat invoice baru.');
        }

        // 🔥 NEW: Force create new token untuk ganti metode pembayaran
        $forceNew = $request->has('new');

        try {
            $snapData = $this->midtransService->createSnapTransaction($payment, $forceNew);
            
            $midtransClientKey = $this->midtransService->getClientKey();
            $midtransSnapUrl = $this->midtransService->getSnapUrl();

            return view('pages.checkout.midtrans', compact(
                'payment', 
                'snapData', 
                'midtransClientKey',
                'midtransSnapUrl',
                'forceNew'
            ));

        } catch (\Exception $e) {
            Log::error('Failed to create Midtrans snap', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'force_new' => $forceNew,
            ]);
            
            return back()->with('error', 'Gagal membuat transaksi Midtrans: ' . $e->getMessage());
        }
    }

    /**
     * Callback setelah payment Midtrans selesai
     * 
     * 🔒 FIX-003: Verify ke Midtrans API sebelum update status
     * Tidak trust URL parameters dari client
     */
    public function midtransFinish(Request $request)
    {
        $invoiceNumber = $request->get('invoice') ?? $request->get('order_id');
        
        // Clean up order_id jika ada suffix timestamp
        if ($invoiceNumber && str_contains($invoiceNumber, '-17')) {
            $invoiceNumber = preg_replace('/-\d{10}$/', '', $invoiceNumber);
        }
        
        if (!$invoiceNumber) {
            return redirect()->route('account.index')
                ->with('info', 'Menunggu konfirmasi pembayaran.');
        }

        $payment = Payment::where('invoice_number', $invoiceNumber)
            ->where('user_id', Auth::id())
            ->with('plan')
            ->first();

        if (!$payment) {
            return redirect()->route('account.index')
                ->with('info', 'Menunggu konfirmasi pembayaran.');
        }

        // 🔒 FIX-003: SELALU verify ke Midtrans API
        // Tidak trust URL parameters
        try {
            $status = $this->midtransService->getTransactionStatus($invoiceNumber);
            
            Log::info('Midtrans finish callback - status check', [
                'invoice' => $invoiceNumber,
                'status' => $status->transaction_status ?? 'unknown',
            ]);

            if (in_array($status->transaction_status, ['settlement', 'capture'])) {
                if ($payment->status !== Payment::STATUS_PAID) {
                    $payment->update([
                        'status' => Payment::STATUS_PAID,
                        'paid_at' => now(),
                        'payment_method' => 'midtrans',
                    ]);
                    
                    // Aktivasi subscription
                    $this->paymentService->activateSubscription($payment);
                    
                    // Kirim email konfirmasi
                    $this->sendPaymentSuccessEmail($payment);
                }
                
                return redirect()->route('checkout.success', $invoiceNumber)
                    ->with('success', 'Pembayaran berhasil! Subscription Anda sudah aktif.');
            }
            
            if ($status->transaction_status === 'pending') {
                return redirect()->route('subscription.pending')
                    ->with('info', 'Menunggu pembayaran dikonfirmasi. Silakan selesaikan pembayaran Anda.');
            }

            if (in_array($status->transaction_status, ['cancel', 'deny', 'expire'])) {
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'admin_notes' => "Midtrans status: {$status->transaction_status}",
                ]);
                
                return redirect()->route('checkout.payment', $invoiceNumber)
                    ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
            }

        } catch (\Exception $e) {
            Log::error('Midtrans status check failed', [
                'invoice' => $invoiceNumber,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback: Redirect ke payment page
        return redirect()->route('checkout.payment', $invoiceNumber)
            ->with('info', 'Silakan selesaikan pembayaran Anda.');
    }

    /**
     * Apply promo code ke pembayaran
     */
    public function applyPromo(Request $request, Payment $payment)
    {
        $request->validate([
            'promo_code' => 'required|string|max:50',
        ]);

        // Cek kepemilikan
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        $result = $this->paymentService->applyPromoCode($payment, $request->promo_code);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Upload bukti transfer
     * 
     * 🔒 FIX-004: Secure file upload dengan random filename
     */
    public function uploadProof(Request $request, Payment $payment)
    {
        // 🔒 Validasi file dengan aturan ketat
        $validator = Validator::make($request->all(), [
            'proof' => [
                'required',
                File::types(['jpg', 'jpeg', 'png'])
                    ->min(10)
                    ->max(5 * 1024), // 5MB
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Cek kepemilikan
        if ($payment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Cek status
        if ($payment->status !== Payment::STATUS_PENDING) {
            return back()->with('error', 'Pembayaran sudah diproses.');
        }

        // Cek expired
        if ($payment->expires_at < now()) {
            return redirect()->route('pricing')
                ->with('error', 'Invoice sudah expired. Silakan buat invoice baru.');
        }

        try {
            $file = $request->file('proof');
            
            // 🔒 FIX-004: Gunakan PaymentService untuk secure upload
            $payment = $this->paymentService->uploadProof($payment, $file);

            return redirect()->route('checkout.success', $payment->invoice_number)
                ->with('success', 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.');

        } catch (\Exception $e) {
            Log::error('Upload proof failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Gagal upload bukti: ' . $e->getMessage());
        }
    }

    /**
     * Halaman sukses setelah pembayaran
     */
    public function success(string $invoiceNumber)
    {
        $payment = Payment::where('invoice_number', $invoiceNumber)
            ->where('user_id', Auth::id())
            ->with('plan')
            ->firstOrFail();

        // Get user's active subscription for display
        $subscription = Auth::user()->subscriptions()
            ->where('status', 'active')
            ->orderBy('expires_at', 'desc')
            ->first();

        // Jika pembayaran sudah paid (Midtrans), tampilkan halaman celebrasi
        if ($payment->status === Payment::STATUS_PAID) {
            return view('pages.checkout.midtrans-success', compact('payment', 'subscription'));
        }

        // Jika masih pending/waiting (manual transfer), tampilkan halaman menunggu verifikasi
        return view('pages.checkout.success', compact('payment'));
    }

    /**
     * Riwayat pembayaran user
     */
    public function history()
    {
        $payments = $this->paymentService->getUserPayments(Auth::id());

        return view('pages.checkout.history', compact('payments'));
    }

    /**
     * Kirim email sukses pembayaran
     * 
     * @param Payment $payment
     * @return void
     */
    private function sendPaymentSuccessEmail(Payment $payment): void
    {
        try {
            $user = $payment->user;
            
            if (!$user || !$user->email) {
                return;
            }

            // Gunakan Mailable
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->queue(new \App\Mail\PaymentSuccessMail($payment));

            Log::info('Payment success email queued', [
                'payment_id' => $payment->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment success email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
