<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controller: Checkout
 * 
 * Controller untuk proses checkout dan pembayaran.
 * Menangani: pilih paket, apply promo, upload bukti transfer, Midtrans.
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

        try {
            $payment = $this->paymentService->createPayment(
                $user,
                $plan,
                $duration,
                $promoCode
            );

            // Jika pilih Midtrans, langsung redirect ke Midtrans payment
            if ($paymentMethod === 'midtrans') {
                return redirect()->route('checkout.midtrans.pay', $payment->invoice_number);
            }

            return redirect()->route('checkout.payment', $payment->invoice_number)
                ->with('success', 'Invoice berhasil dibuat. Silakan lakukan pembayaran.');

        } catch (\Exception $e) {
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
     */
    public function payWithMidtrans(string $invoiceNumber)
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

        try {
            $snapData = $this->midtransService->createSnapTransaction($payment);
            
            $midtransClientKey = $this->midtransService->getClientKey();
            $midtransSnapUrl = $this->midtransService->getSnapUrl();

            return view('pages.checkout.midtrans', compact(
                'payment', 
                'snapData', 
                'midtransClientKey',
                'midtransSnapUrl'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat transaksi Midtrans: ' . $e->getMessage());
        }
    }

    /**
     * Callback setelah payment Midtrans selesai
     */
    public function midtransFinish(Request $request)
    {
        $invoiceNumber = $request->get('invoice') ?? $request->get('order_id');
        
        // Clean up order_id jika ada suffix timestamp
        if ($invoiceNumber && str_contains($invoiceNumber, '-17')) {
            // Format: INV-2026-00001-1769316510 -> INV-2026-00001
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

        // Cek parameter dari URL (Midtrans redirect params)
        $transactionStatus = $request->get('transaction_status');
        
        // Jika status settlement dari URL, langsung proses
        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            if ($payment->status !== Payment::STATUS_PAID) {
                // Update payment status
                $payment->update([
                    'status' => Payment::STATUS_PAID,
                    'paid_at' => now(),
                    'payment_method' => 'midtrans',
                ]);
                
                // Aktivasi subscription
                $this->paymentService->activateSubscription($payment);
            }
            
            return redirect()->route('checkout.success', $invoiceNumber)
                ->with('success', 'Pembayaran berhasil! Subscription Anda sudah aktif.');
        }

        // Fallback: Cek status dari Midtrans API
        try {
            $status = $this->midtransService->getTransactionStatus($invoiceNumber);
            
            if (in_array($status->transaction_status, ['settlement', 'capture'])) {
                if ($payment->status !== Payment::STATUS_PAID) {
                    $payment->update([
                        'status' => Payment::STATUS_PAID,
                        'paid_at' => now(),
                        'payment_method' => 'midtrans',
                    ]);
                    $this->paymentService->activateSubscription($payment);
                }
                
                return redirect()->route('checkout.success', $invoiceNumber)
                    ->with('success', 'Pembayaran berhasil! Subscription Anda sudah aktif.');
            }
            
            if ($status->transaction_status === 'pending') {
                return redirect()->route('subscription.pending')
                    ->with('info', 'Menunggu pembayaran dikonfirmasi. Silakan selesaikan pembayaran Anda.');
            }

        } catch (\Exception $e) {
            // Fallback jika gagal cek status
        }

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
     */
    public function uploadProof(Request $request, Payment $payment)
    {
        $request->validate([
            'proof' => 'required|image|mimes:jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        // Cek kepemilikan
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        // Cek status
        if ($payment->status !== Payment::STATUS_PENDING) {
            return back()->with('error', 'Pembayaran sudah diproses.');
        }

        try {
            // Upload file
            $path = $request->file('proof')->store('payment-proofs', 'public');
            
            // Generate full URL yang benar
            $proofUrl = asset('storage/' . $path);

            // Update payment
            $this->paymentService->uploadProof($payment, $proofUrl);

            return redirect()->route('checkout.success', $payment->invoice_number)
                ->with('success', 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload bukti: ' . $e->getMessage());
        }
    }

    /**
     * Halaman sukses setelah pembayaran
     * - Untuk Midtrans (status paid): tampilkan halaman celebrasi
     * - Untuk manual transfer: tampilkan halaman menunggu verifikasi
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
}
