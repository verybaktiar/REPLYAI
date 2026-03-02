<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Payment;
use App\Notifications\NewDeviceLoginNotification;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        // 🛡️ SECURITY: Cek apakah akun di-suspend
        if ($user->is_suspended) {
            $email = $user->email;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('suspended')->with([
                'error' => 'Akun Anda telah ditangguhkan.',
                'suspended_email' => $email
            ]);
        }

        // ✅ SECURITY: Check for new device login
        $this->checkNewDeviceLogin($user, $request);

        // 💳 Check for pending payments
        $pendingPaymentCheck = $this->checkPendingPayment($user);

        ActivityLogService::logLogin();

        // 💳 Jika ada pending payment, redirect ke halaman pembayaran
        if ($pendingPaymentCheck['has_pending']) {
            $payment = $pendingPaymentCheck['payment'];
            
            Log::info('User has pending payment, redirecting to payment page', [
                'user_id' => $user->id,
                'invoice' => $payment->invoice_number,
                'total' => $payment->total,
            ]);
            
            return redirect()->route('checkout.payment', $payment->invoice_number)
                ->with('warning', "Anda memiliki pembayaran pending ({$payment->invoice_number}) sebesar Rp " . number_format($payment->total, 0, ',', '.') . ". Silakan selesaikan pembayaran.");
        }

        // Cek apakah ada selected plan di session
        if ($request->session()->has('selected_plan')) {
            $planSlug = $request->session()->get('selected_plan');
            $request->session()->forget('selected_plan'); // Clear session
            
            // Redirect ke checkout dengan plan yang dipilih
            return redirect()->route('checkout.index', ['plan' => $planSlug]);
        }

        // Cek subscription status untuk redirect yang tepat
        if (!$user->subscription) {
            // Belum punya subscription, ke pricing
            return redirect()->route('pricing')
                ->with('info', 'Silakan pilih paket langganan untuk memulai.');
        }
        
        if ($user->subscription->status === 'pending') {
            // Menunggu approval
            return redirect()->route('subscription.pending');
        }
        
        if (!in_array($user->subscription->status, ['active', 'trial', 'past_due'])) {
            // Expired/cancelled, ke pricing
            return redirect()->route('pricing')
                ->with('warning', 'Langganan Anda sudah tidak aktif. Silakan perpanjang.');
        }

        // Active subscription, ke dashboard
        // Don't redirect to API endpoints after login
        $intended = session()->get('url.intended');
        if ($intended && str_starts_with($intended, '/api/')) {
            session()->forget('url.intended');
            return redirect()->route('dashboard');
        }
        
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Check if user has pending payment
     * 
     * @param \App\Models\User $user
     * @return array ['has_pending' => bool, 'payment' => Payment|null]
     */
    private function checkPendingPayment($user): array
    {
        try {
            // Cari pembayaran pending yang belum expired
            $pendingPayment = Payment::where('user_id', $user->id)
                ->where('status', Payment::STATUS_PENDING)
                ->where('expires_at', '>', now())
                ->with('plan')
                ->latest()
                ->first();
            
            if ($pendingPayment) {
                return [
                    'has_pending' => true,
                    'payment' => $pendingPayment,
                ];
            }
            
            return [
                'has_pending' => false,
                'payment' => null,
            ];
            
        } catch (\Exception $e) {
            Log::error('Error checking pending payment: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
            
            return [
                'has_pending' => false,
                'payment' => null,
            ];
        }
    }

    /**
     * Check if this is a new device login and notify user
     *
     * @param \App\Models\User $user
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function checkNewDeviceLogin($user, Request $request): void
    {
        try {
            $currentFingerprint = $this->generateDeviceFingerprint($request);
            $lastLoginInfo = session('last_login_info');
            
            Log::info('Checking new device login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'has_last_login' => !empty($lastLoginInfo),
                'current_ip' => $request->ip(),
                'current_fingerprint' => $currentFingerprint,
            ]);
            
            // Jika ada data login sebelumnya
            if ($lastLoginInfo) {
                // Jika fingerprint berbeda, berarti device baru
                if ($lastLoginInfo['fingerprint'] !== $currentFingerprint) {
                    Log::info('New device detected, sending notification', [
                        'user_id' => $user->id,
                        'old_ip' => $lastLoginInfo['ip'] ?? 'unknown',
                        'new_ip' => $request->ip(),
                    ]);
                    
                    // Kirim notifikasi (async via queue)
                    $user->notify(new NewDeviceLoginNotification($request));
                } else {
                    Log::info('Same device, no notification needed', [
                        'user_id' => $user->id,
                    ]);
                }
            } else {
                // Login pertama kali (belum ada data login sebelumnya)
                // Optional: Kirim notifikasi welcome/first login
                Log::info('First login detected, skipping new device notification', [
                    'user_id' => $user->id,
                ]);
                
                // Uncomment baris di bawah jika ingin kirim notifikasi untuk login pertama juga
                // $user->notify(new NewDeviceLoginNotification($request));
            }

            // Simpan info login sekarang untuk pengecekan berikutnya
            session([
                'last_login_info' => [
                    'fingerprint' => $currentFingerprint,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'time' => now()->toDateTimeString(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking new device login: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Generate device fingerprint dari request
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    private function generateDeviceFingerprint(Request $request): string
    {
        // Kombinasi User Agent + IP (simplified)
        // Untuk lebih akurat, bisa tambah canvas fingerprinting di frontend
        $data = [
            substr($request->userAgent(), 0, 100),
            $request->ip(),
        ];

        return hash('sha256', implode('|', $data));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Clear last login info saat logout
        session()->forget('last_login_info');
        
        ActivityLogService::logLogout();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
