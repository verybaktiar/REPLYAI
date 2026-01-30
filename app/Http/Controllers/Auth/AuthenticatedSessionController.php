<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\ActivityLogService;

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

        ActivityLogService::logLogin();

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
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        ActivityLogService::logLogout();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
