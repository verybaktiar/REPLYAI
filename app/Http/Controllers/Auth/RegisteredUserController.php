<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\NotDisposableEmail;
use App\Rules\UserPasswordPolicy;
use App\Services\Security\CaptchaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'captchaSiteKey' => app(CaptchaService::class)->getSiteKey(),
            'captchaEnabled' => CaptchaService::isEnabled(),
            'captchaProvider' => app(CaptchaService::class)->getProvider(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $captchaService = app(CaptchaService::class);

        // Build validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'lowercase', 
                'email', 
                'max:255', 
                'unique:'.User::class,
                new NotDisposableEmail(), // ✅ Block disposable emails
            ],
            'password' => ['required', 'confirmed', new UserPasswordPolicy()],
        ];

        // ✅ Add CAPTCHA validation if enabled
        if ($captchaService::isEnabled()) {
            $rules[$captchaService->getResponseFieldName()] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        // ✅ Verify CAPTCHA
        if ($captchaService::isEnabled()) {
            $captchaResponse = $request->input($captchaService->getResponseFieldName());
            
            if (!$captchaService->verify($captchaResponse, $request->ip())) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'captcha' => 'Verifikasi CAPTCHA gagal. Silakan coba lagi.',
                    ]);
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Send email verification
        event(new Registered($user));

        // SECURITY: Tidak auto-login, user harus verify email & login manual
        // Auth::login($user); // REMOVED untuk keamanan

        // Redirect ke login dengan success message
        return redirect()->route('login')
            ->with('status', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi akun, lalu login.');
    }
}
