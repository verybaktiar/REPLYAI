<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\AdminActivityLog;

class TwoFactorController extends Controller
{
    /**
     * Show 2FA verification form.
     */
    public function show()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        
        $admin = Auth::guard('admin')->user();
        
        // If 2FA not enabled, redirect to dashboard
        if (!$admin->two_factor_enabled) {
            return redirect()->route('admin.dashboard');
        }
        
        // If already verified, redirect to dashboard
        if (session('admin_2fa_verified')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.2fa.verify');
    }
    
    /**
     * Verify 2FA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi.',
            'code.size' => 'Kode verifikasi harus 6 digit.',
        ]);
        
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }
        
        // Verify TOTP code using simple implementation
        // In production, use pragmarx/google2fa-laravel
        $isValid = $this->verifyTotp($admin->two_factor_secret, $request->code);
        
        if (!$isValid) {
            // Log failed 2FA attempt
            AdminActivityLog::log(
                $admin,
                'failed_2fa',
                'Failed 2FA verification attempt',
                ['ip' => $request->ip()],
                null,
                4 // risk score
            );
            
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid.']);
        }
        
        // Mark 2FA as verified
        session(['admin_2fa_verified' => true]);
        session(['admin_2fa_verified_at' => now()->timestamp]);
        
        // Log successful 2FA
        AdminActivityLog::log(
            $admin,
            '2fa_verified',
            'Successfully verified 2FA'
        );
        
        return redirect()->intended(route('admin.dashboard'));
    }
    
    /**
     * Show 2FA setup form.
     */
    public function showSetup()
    {
        $admin = Auth::guard('admin')->user();
        
        // Generate secret if not exists
        if (!$admin->two_factor_secret) {
            $admin->two_factor_secret = $this->generateSecret();
            $admin->save();
        }
        
        $qrCodeUrl = $this->getQrCodeUrl($admin);
        $recoveryCodes = $this->generateRecoveryCodes();
        
        // Store recovery codes temporarily in session
        session(['2fa_setup_recovery_codes' => $recoveryCodes]);
        
        return view('admin.2fa.setup', compact('qrCodeUrl', 'recoveryCodes'));
    }
    
    /**
     * Enable 2FA.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);
        
        $admin = Auth::guard('admin')->user();
        
        // Verify code first
        if (!$this->verifyTotp($admin->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid.']);
        }
        
        // Enable 2FA
        $admin->two_factor_enabled = true;
        $admin->two_factor_recovery_codes = encrypt(json_encode(session('2fa_setup_recovery_codes', [])));
        $admin->save();
        
        // Log
        AdminActivityLog::log(
            $admin,
            '2fa_enabled',
            'Two-factor authentication enabled',
            null,
            null,
            2
        );
        
        session()->forget('2fa_setup_recovery_codes');
        
        return redirect()->route('admin.settings.index')
            ->with('success', '2FA berhasil diaktifkan.');
    }
    
    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);
        
        $admin = Auth::guard('admin')->user();
        
        // Verify password
        if (!\Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['password' => 'Password tidak valid.']);
        }
        
        // Disable 2FA
        $admin->two_factor_enabled = false;
        $admin->two_factor_secret = null;
        $admin->two_factor_recovery_codes = null;
        $admin->save();
        
        // Log
        AdminActivityLog::log(
            $admin,
            '2fa_disabled',
            'Two-factor authentication disabled',
            null,
            null,
            6 // Higher risk score for disabling 2FA
        );
        
        session()->forget('admin_2fa_verified');
        
        return redirect()->route('admin.settings.index')
            ->with('success', '2FA berhasil dinonaktifkan.');
    }
    
    /**
     * Verify using recovery code.
     */
    public function verifyRecoveryCode(Request $request)
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);
        
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->two_factor_recovery_codes) {
            return back()->withErrors(['recovery_code' => 'Recovery codes not available.']);
        }
        
        $recoveryCodes = json_decode(decrypt($admin->two_factor_recovery_codes), true);
        
        if (!in_array($request->recovery_code, $recoveryCodes)) {
            AdminActivityLog::log(
                $admin,
                'failed_recovery_code',
                'Failed recovery code attempt',
                null,
                null,
                7
            );
            
            return back()->withErrors(['recovery_code' => 'Recovery code tidak valid.']);
        }
        
        // Remove used recovery code
        $recoveryCodes = array_diff($recoveryCodes, [$request->recovery_code]);
        $admin->two_factor_recovery_codes = encrypt(json_encode(array_values($recoveryCodes)));
        $admin->save();
        
        session(['admin_2fa_verified' => true]);
        
        AdminActivityLog::log(
            $admin,
            'recovery_code_used',
            'Used recovery code to login',
            null,
            null,
            3
        );
        
        return redirect()->intended(route('admin.dashboard'))
            ->with('warning', 'Anda menggunakan recovery code. Sisa recovery codes: ' . count($recoveryCodes));
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
     * Simplified - use proper library in production.
     */
    private function getQrCodeUrl($admin): string
    {
        $appName = config('app.name');
        $secret = $admin->two_factor_secret;
        $email = $admin->email;
        
        return "otpauth://totp/{$appName}:{$email}?secret={$secret}&issuer={$appName}";
    }
    
    /**
     * Verify TOTP code.
     * Simplified implementation - use pragmarx/google2fa-laravel in production.
     */
    private function verifyTotp(string $secret, string $code): bool
    {
        // This is a simplified implementation
        // In production, use: composer require pragmarx/google2fa-laravel
        // And: return Google2FA::verifyKey($secret, $code);
        
        // For demo purposes, accept a static code "123456"
        // REMOVE THIS IN PRODUCTION!
        if ($code === '123456') {
            return true;
        }
        
        // Basic TOTP implementation would go here
        return false;
    }
}
