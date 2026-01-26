<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
 * Mark the authenticated user's email address as verified.
 * 
 * SECURITY MODE: EXTRA STRICT
 * - Verify email → Success
 * - Logout user (no auto-login)
 * - Redirect to login page with success message
 */
public function __invoke(EmailVerificationRequest $request): RedirectResponse
{
    // Jika sudah verified sebelumnya
    if ($request->user()->hasVerifiedEmail()) {
        // Logout dan redirect ke login
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('status', 'Email Anda sudah terverifikasi sebelumnya. Silakan login.');
    }

    // Mark email sebagai verified
    if ($request->user()->markEmailAsVerified()) {
        event(new Verified($request->user()));
    }

    // SECURITY: Logout user setelah verifikasi (extra strict)
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Redirect ke login dengan success message
    return redirect()->route('login')
        ->with('status', 'Email berhasil diverifikasi! Silakan login untuk melanjutkan.');
}
}
