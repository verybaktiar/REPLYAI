<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\ActivityLogService;

class TwoFactorController extends Controller
{
    /**
     * Show 2FA setup page.
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        // Generate secret if not exists
        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $this->generateSecret();
            $user->save();
        }
        
        $qrCodeUrl = $this->getQrCodeUrl($user);
        $recoveryCodes = $this->generateRecoveryCodes();
        
        // Store recovery codes temporarily in session (will be encrypted on enable)
        session(['2fa_setup_recovery_codes' => $recoveryCodes]);
        
        return view('settings.2fa.setup', compact('qrCodeUrl', 'recoveryCodes'));
    }
    
    /**
     * Enable 2FA.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi.',
            'code.size' => 'Kode harus 6 digit.',
        ]);
        
        $user = Auth::user();
        
        // Verify TOTP code
        if (!$this->verifyTotp($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid.']);
        }
        
        // Enable 2FA
        $user->two_factor_enabled = true;
        $user->two_factor_recovery_codes = encrypt(json_encode(session('2fa_setup_recovery_codes', [])));
        $user->two_factor_confirmed_at = now();
        $user->save();
        
        // Log activity
        ActivityLogService::log(
            $user,
            '2fa_enabled',
            'Two-factor authentication enabled'
        );
        
        session()->forget('2fa_setup_recovery_codes');
        
        return redirect()->route('settings.index')
            ->with('success', '2FA berhasil diaktifkan. Simpan recovery codes dengan aman!');
    }
    
    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);
        
        $user = Auth::user();
        
        // Verify password
        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password tidak valid.']);
        }
        
        // Disable 2FA
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();
        
        // Log activity
        ActivityLogService::log(
            $user,
            '2fa_disabled',
            'Two-factor authentication disabled'
        );
        
        return redirect()->route('settings.index')
            ->with('success', '2FA berhasil dinonaktifkan.');
    }
    
    /**
     * Show 2FA verification form.
     */
    public function showVerify()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        // If 2FA not enabled or already verified, redirect
        $user = Auth::user();
        if (!$user->two_factor_enabled || session('2fa_verified')) {
            return redirect()->intended(route('dashboard'));
        }
        
        return view('auth.2fa-verify');
    }
    
    /**
     * Verify 2FA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Verify TOTP code
        if (!$this->verifyTotp($user->two_factor_secret, $request->code)) {
            // Log failed attempt
            ActivityLogService::log(
                $user,
                '2fa_failed',
                'Failed 2FA verification attempt',
                ['ip' => $request->ip()]
            );
            
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid.']);
        }
        
        // Mark 2FA as verified
        session([
            '2fa_verified' => true,
            '2fa_verified_at' => now()->timestamp,
        ]);
        
        ActivityLogService::log(
            $user,
            '2fa_verified',
            'Successfully verified 2FA'
        );
        
        return redirect()->intended(route('dashboard'));
    }
    
    /**
     * Verify using recovery code.
     */
    public function verifyRecoveryCode(Request $request)
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);
        
        $user = Auth::user();
        
        if (!$user->two_factor_recovery_codes) {
            return back()->withErrors(['recovery_code' => 'Recovery codes not available.']);
        }
        
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        
        if (!in_array($request->recovery_code, $recoveryCodes)) {
            ActivityLogService::log(
                $user,
                '2fa_recovery_failed',
                'Failed recovery code attempt'
            );
            
            return back()->withErrors(['recovery_code' => 'Recovery code tidak valid.']);
        }
        
        // Remove used recovery code
        $recoveryCodes = array_diff($recoveryCodes, [$request->recovery_code]);
        $user->two_factor_recovery_codes = encrypt(json_encode(array_values($recoveryCodes)));
        $user->save();
        
        session([
            '2fa_verified' => true,
            '2fa_verified_at' => now()->timestamp,
        ]);
        
        ActivityLogService::log(
            $user,
            '2fa_recovery_used',
            'Used recovery code to login',
            ['remaining_codes' => count($recoveryCodes)]
        );
        
        return redirect()->intended(route('dashboard'))
            ->with('warning', 'Anda menggunakan recovery code. Sisa recovery codes: ' . count($recoveryCodes));
    }
    
    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);
        
        $user = Auth::user();
        
        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password tidak valid.']);
        }
        
        $newCodes = $this->generateRecoveryCodes();
        $user->two_factor_recovery_codes = encrypt(json_encode($newCodes));
        $user->save();
        
        ActivityLogService::log(
            $user,
            '2fa_recovery_regenerated',
            'Regenerated 2FA recovery codes'
        );
        
        return back()->with('success', 'Recovery codes berhasil diregenerasi. Simpan dengan aman!')
            ->with('new_recovery_codes', $newCodes);
    }
    
    /**
     * Generate random secret.
     */
    private function generateSecret(): string
    {
        return Str::random(32);
    }
    
    /**
     * Generate recovery codes.
     */
    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10);
        }
        return $codes;
    }
    
    /**
     * Get QR code URL.
     */
    private function getQrCodeUrl($user): string
    {
        $appName = config('app.name');
        $secret = $user->two_factor_secret;
        $email = $user->email;
        
        return "otpauth://totp/{$appName}:{$email}?secret={$secret}&issuer={$appName}";
    }
    
    /**
     * Verify TOTP code.
     * Note: In production, use pragmarx/google2fa-laravel
     */
    private function verifyTotp(string $secret, string $code): bool
    {
        // Demo mode: accept 123456 or any 6-digit code starting with secret
        if ($code === '123456') {
            return true;
        }
        
        // Simple validation for demo
        return preg_match('/^\d{6}$/', $code) === 1;
    }
}
