# 🔐 Analisis Alur Autentikasi - ReplyAI

> **Tanggal Analisis**: 16 Februari 2026  
> **Analis**: Senior Laravel Developer  
> **Status**: ✅ Flow Utama Baik, Ada Beberapa Rekomendasi Improvement

---

## 📋 DAFTAR ISI

1. [Ringkasan Alur](#-ringkasan-alur)
2. [Flow Registrasi](#-flow-registrasi-detail)
3. [Flow Login](#-flow-login-detail)
4. [Flow Email Verification](#-flow-email-verification)
5. [Flow Logout](#-flow-logout)
6. [Security Analysis](#-security-analysis)
7. [Identified Issues](#-identified-issues)
8. [Rekomendasi Improvement](#-rekomendasi-improvement)

---

## 🎯 RINGKASAN ALUR

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         ALUR AUTENTIKASI REPLYAI                            │
└─────────────────────────────────────────────────────────────────────────────┘

REGISTRASI:
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  Landing │────▶│  Form    │────▶│ Validate │────▶│  Create  │────▶│  Login   │
│  Page    │     │ Register │     │   Input  │     │   User   │     │  Page    │
└──────────┘     └──────────┘     └──────────┘     └──────────┘     └──────────┘
                                                         │
                                                         ▼
                                                  ┌──────────┐
                                                  │  Send    │
                                                  │  Email   │
                                                  │  Verify  │
                                                  └──────────┘

LOGIN:
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  Login   │────▶│ Validate │────▶│  Check   │────▶│  Check   │────▶│ Redirect │
│  Page    │     │  Creds   │     │Suspended │     │   Plan   │     │   To     │
└──────────┘     └──────────┘     └──────────┘     └──────────┘     └──────────┘
                                              │                    (Dashboard/
                                              │                    Pricing/
                                              ▼                    Pending)
                                        ┌──────────┐
                                        │  Logout  │
                                        │ Redirect │
                                        └──────────┘
```

---

## 📝 FLOW REGISTRASI (DETAIL)

### Step-by-Step Flow

| Step | Endpoint | Controller | Deskripsi |
|------|----------|------------|-----------|
| 1 | `GET /register` | `RegisteredUserController@create` | Tampilkan form registrasi |
| 2 | `POST /register` | `RegisteredUserController@store` | Proses registrasi |

### Validasi Input
```php
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);
```

### ✅ Bagus
- ✅ Validasi input lengkap (required, email format, unique, password confirmation)
- ✅ Password menggunakan `Rules\Password::defaults()` (min 8 chars)
- ✅ Email di-convert ke lowercase untuk konsistensi
- ✅ **TIDAK auto-login setelah registrasi** (security best practice)
- ✅ Event `Registered` dipanggil untuk kirim email verifikasi
- ✅ Redirect ke login dengan success message

### ⚠️ Catatan
- Tidak ada CAPTCHA/reCAPTCHA (vulnerable ke bot spam)
- Tidak ada rate limiting pada registrasi
- Tidak ada validasi email domain (bisa pakai disposable email)

---

## 🔑 FLOW LOGIN (DETAIL)

### Step-by-Step Flow

| Step | Endpoint | Controller | Deskripsi |
|------|----------|------------|-----------|
| 1 | `GET /login` | `AuthenticatedSessionController@create` | Tampilkan form login |
| 2 | `POST /login` | `AuthenticatedSessionController@store` | Proses autentikasi |

### Validasi Input
```php
$request->validate([
    'email' => ['required', 'string', 'email'],
    'password' => ['required', 'string'],
]);
```

### Redirect Logic Setelah Login
```
┌─────────────────┐
│  User Login     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     ┌─────────────────┐
│ is_suspended?   │────▶│  YES: Logout    │
│                 │     │  → /suspended   │
└────────┬────────┘     └─────────────────┘
         │ NO
         ▼
┌─────────────────┐     ┌─────────────────┐
│ selected_plan   │────▶│  YES: Checkout  │
│ in session?     │     │  → /checkout    │
└────────┬────────┘     └─────────────────┘
         │ NO
         ▼
┌─────────────────┐     ┌─────────────────┐
│ No subscription │────▶│  → /pricing     │
└────────┬────────┘     └─────────────────┘
         │ Has subscription
         ▼
┌─────────────────┐     ┌─────────────────┐
│ Status pending? │────▶│  → /pending     │
└────────┬────────┘     └─────────────────┘
         │ Active/Trial
         ▼
┌─────────────────┐
│  → /dashboard   │
└─────────────────┘
```

### ✅ Bagus
- ✅ Rate limiting: 5 attempts per email/IP (Lockout)
- ✅ Session regeneration setelah login (security)
- ✅ Activity log untuk setiap login
- ✅ Suspended account check dengan logout & invalidate session
- ✅ Redirect cerdas berdasarkan subscription status
- ✅ Remember me functionality tersedia

### ⚠️ Catatan
- Rate limiting hanya per email, tidak ada global rate limit per IP
- Tidak ada 2FA/MFA
- Tidak ada notifikasi login dari device baru

---

## 📧 FLOW EMAIL VERIFICATION

### Step-by-Step

| Step | Endpoint | Controller | Deskripsi |
|------|----------|------------|-----------|
| 1 | Event `Registered` | `SendEmailVerificationNotification` | Kirim email verifikasi otomatis |
| 2 | `GET /verify-email/{id}/{hash}` | `VerifyEmailController` | Verifikasi email via signed URL |
| 3 | `POST /email/verification-notification` | `EmailVerificationNotificationController@store` | Resend email (throttled) |

### ⚠️ CRITICAL ISSUE: Strict Verification Flow

**Current Behavior**:
```php
// VerifyEmailController
public function __invoke(EmailVerificationRequest $request): RedirectResponse
{
    if ($request->user()->hasVerifiedEmail()) {
        Auth::guard('web')->logout();  // ← Logout!
        return redirect()->route('login')->with('status', '...');
    }
    
    // Mark as verified
    if ($request->user()->markEmailAsVerified()) {
        event(new Verified($request->user()));
    }
    
    // SECURITY: Logout user setelah verifikasi
    Auth::guard('web')->logout();  // ← Logout lagi!
    
    return redirect()->route('login')->with('status', '...');
}
```

**Masalah**:
1. ✅ User HARUS login lagi setelah verifikasi (extra strict)
2. ⚠️ UX kurang baik, user harus login 2x (registrasi → verify → login)
3. ✅ Tapi ini memang security best practice untuk high-security app

---

## 🚪 FLOW LOGOUT

### Endpoint
- `POST /logout` (recommended)
- `GET /logout` (available untuk testing/convenience)

### Proses Logout
```php
ActivityLogService::logLogout();
Auth::guard('web')->logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
return redirect('/');
```

### ✅ Bagus
- ✅ Session invalidate (session tidak bisa dipakai lagi)
- ✅ CSRF token regeneration
- ✅ Activity log tercatat
- ✅ Redirect ke landing page

---

## 🔒 SECURITY ANALYSIS

### Middleware Stack untuk Protected Routes
```php
Route::middleware(['auth', 'verified', 'suspended'])->group(function () {
    // Protected routes...
});
```

| Middleware | Fungsi | Status |
|------------|--------|--------|
| `auth` | Pastikan user terautentikasi | ✅ OK |
| `verified` | Pastikan email sudah diverifikasi | ✅ OK |
| `suspended` | Cek & block suspended accounts | ✅ OK |

### Suspended Account Check (Double Layer)
1. **Login Time**: `AuthenticatedSessionController@store` cek `is_suspended`
2. **Request Time**: `CheckSuspendedAccount` middleware cek setiap request

### Password Security
- ✅ Hashing: `Hash::make()` (default bcrypt)
- ✅ Password rules: min 8 chars, mixed case, numbers, symbols
- ✅ Password confirmation field wajib diisi

---

## ⚠️ IDENTIFIED ISSUES

### Issue #1: Missing CAPTCHA
**Risk**: Bot spam registration  
**Severity**: 🟡 MEDIUM  
**Fix**: Tambah reCAPTCHA v3 atau hCaptcha

### Issue #2: No Email Domain Validation
**Risk**: Disposable email abuse  
**Severity**: 🟡 MEDIUM  
**Fix**: Validasi domain email (block tempmail, 10minutemail, dll)

### Issue #3: Rate Limiting Gap
**Risk**: Credential stuffing attack  
**Severity**: 🟠 HIGH  
**Fix**: Global rate limit per IP (selain per email)

### Issue #4: Missing 2FA/MFA
**Risk**: Account takeover jika password leak  
**Severity**: 🟡 MEDIUM  
**Fix**: Implement 2FA (TOTP/SMS) untuk plan Enterprise

### Issue #5: No Login Notification
**Risk**: User tidak tahu kalau ada login dari device lain  
**Severity**: 🟡 MEDIUM  
**Fix**: Kirim email notifikasi saat login dari device/IP baru

### Issue #6: Session Timeout Tidak Jelas
**Risk**: Session bisa jalan lama tanpa aktifitas  
**Severity**: 🟢 LOW  
**Fix**: Config session lifetime & idle timeout

---

## 💡 REKOMENDASI IMPROVEMENT

### 1. Tambah CAPTCHA (High Priority)
```php
// RegisteredUserController@store
$request->validate([
    'g-recaptcha-response' => ['required', new Recaptcha()],
    // ... existing rules
]);
```

### 2. Email Domain Validation
```php
// Custom validation rule
$request->validate([
    'email' => ['required', 'email', new NotDisposableEmail()],
]);
```

### 3. Global Rate Limiting
```php
// routes/web.php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('register', [RegisteredUserController::class, 'store']);
});
```

### 4. Login Notification Email
```php
// Dalam AuthenticatedSessionController@store
if ($this->isNewDevice($user, $request)) {
    $user->notify(new NewDeviceLoginNotification($request));
}
```

### 5. Session Security Config
```php
// config/session.php
'lifetime' => 120,           // 2 hours
'expire_on_close' => false,
'same_site' => 'lax',
'secure' => true,            // HTTPS only
'http_only' => true,         // No JS access
```

---

## 📊 FLOW DIAGRAM LENGKAP

```
╔═══════════════════════════════════════════════════════════════════════════════╗
║                          REGISTRATION FLOW                                    ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  ┌──────────────┐                                                            ║
║  │  /register   │                                                            ║
║  │     GET      │                                                            ║
║  └──────┬───────┘                                                            ║
║         │                                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  Middleware:                       │                                      ║
║  │  - guest (sudah login di-redirect) │                                      ║
║  │  - global_feature:enable_registration │                                   ║
║  └────────────────────────────────────┘                                      ║
║         │                                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  Show: auth/register.blade.php     │                                      ║
║  │  - Name field                      │                                      ║
║  │  - Email field                     │                                      ║
║  │  - Password field                  │                                      ║
║  │  - Password confirmation           │                                      ║
║  └────────────────────────────────────┘                                      ║
║         │                                                                     ║
║         │ POST /register                                                      ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  Validation:                       │                                      ║
║  │  - name: required, max:255         │                                      ║
║  │  - email: required, email, unique  │                                      ║
║  │  - password: required, confirmed,  │                                      ║
║  │           Password::defaults()     │                                      ║
║  └────────────────────────────────────┘                                      ║
║         │                                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  User::create([                    │                                      ║
║  │    'name' => $request->name,       │                                      ║
║  │    'email' => $request->email,     │                                      ║
║  │    'password' => Hash::make(...)   │                                      ║
║  │  ])                                │                                      ║
║  └────────────────────────────────────┘                                      ║
║         │                                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  event(new Registered($user))      │                                      ║
║  │  → Send verification email         │                                      ║
║  └────────────────────────────────────┘                                      ║
║         │                                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  Redirect: /login                  │                                      ║
║  │  Message: "Registrasi berhasil!    │                                      ║
║  │           Silakan cek email..."    │                                      ║
║  └────────────────────────────────────┘                                      ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════════════════╗
║                            LOGIN FLOW                                         ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  ┌──────────────┐                                                            ║
║  │    /login    │                                                            ║
║  │     GET      │                                                            ║
║  └──────┬───────┘                                                            ║
║         │                                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  Middleware: guest                 │                                      ║
║  │  (Sudah login → redirect)          │                                      ║
║  └────────────────────────────────────┘                                      ║
║         │                                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  Show: auth/login.blade.php        │                                      ║
║  └────────────────────────────────────┘                                      ║
║         │                                                                     ║
║         │ POST /login                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  LoginRequest:                     │                                      ║
║  │  - email: required, email          │                                      ║
║  │  - password: required              │                                      ║
║  │  - Rate limit: 5 attempts          │                                      ║
║  └────────────────────────────────────┘                                      ║
║         │                                                                     ║
║         ▼                                                                     ║
║  ┌────────────────────────────────────┐                                      ║
║  │  Auth::attempt([email, password])  │                                      ║
║  │  Success?                          │                                      ║
║  └────────┬─────────────────────┬─────┘                                      ║
║           │                     │                                             ║
║        NO │                     │ YES                                         ║
║           ▼                     ▼                                             ║
║  ┌────────────────┐    ┌──────────────────────────┐                        ║
║  │ RateLimiter::hit│    │ session->regenerate()    │                        ║
║  │ Return: failed │    │ ActivityLogService::log  │                        ║
║  └────────────────┘    └───────────┬──────────────┘                        ║
║                                    │                                         ║
║                                    ▼                                         ║
║                     ┌──────────────────────────────┐                       ║
║                     │ Check: is_suspended?         │                       ║
║                     └──────────────┬───────────────┘                       ║
║                                    │                                         ║
║                          YES ┌─────┴─────┐ NO                               ║
║                              ▼           ▼                                   ║
║                     ┌────────────────┐ ┌──────────────────────────┐         ║
║                     │ Logout         │ │ Check subscription       │         ║
║                     │ → /suspended   │ │ status                   │         ║
║                     └────────────────┘ └──────────┬───────────────┘         ║
║                                                   │                          ║
║                              ┌────────────────────┼────────────────────┐    ║
║                              │                    │                    │    ║
║                              ▼                    ▼                    ▼    ║
║                        ┌──────────┐      ┌──────────┐      ┌──────────────┐ ║
║                        │ selected │      │ No sub   │      │ Active/      │ ║
║                        │ plan?    │      │          │      │ Trial/Pending│ ║
║                        └────┬─────┘      └────┬─────┘      └──────┬───────┘ ║
║                             │                 │                   │         ║
║                             ▼                 ▼                   ▼         ║
║                        ┌──────────┐    ┌──────────┐       ┌──────────┐     ║
║                        │/checkout │    │/pricing  │       │/dashboard│     ║
║                        └──────────┘    └──────────┘       │ or       │     ║
║                                                           │/pending  │     ║
║                                                           └──────────┘     ║
║                                                                            ║
╚════════════════════════════════════════════════════════════════════════════╝
```

---

## ✅ VERDIKSI

| Aspek | Status | Catatan |
|-------|--------|---------|
| **Security** | ✅ GOOD | Suspended check, rate limiting, session security |
| **UX** | ⚠️ FAIR | Strict email verification (must login 2x) |
| **Validation** | ✅ GOOD | Input validation lengkap |
| **Redirect Logic** | ✅ GOOD | Smart redirect berdasarkan subscription |
| **Anti-Bot** | ❌ NEEDS WORK | No CAPTCHA, no email domain validation |
| **Monitoring** | ✅ GOOD | Activity log untuk login/logout |

### Overall Rating: 7.5/10
**Rekomendasi**: Implement improvement #1 (CAPTCHA) dan #3 (Global rate limit) segera untuk meningkatkan keamanan.

---

*Dokumen ini dibuat untuk review alur autentikasi sistem ReplyAI.*
