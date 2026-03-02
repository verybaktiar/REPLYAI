# 🔍 AUDIT ALUR PEMBAYARAN & SUBSCRIPTION - ReplyAI

> **Auditor**: Senior Laravel Developer  
> **Tanggal**: 16 Februari 2026  
> **Scope**: Pricing → Checkout → Payment → Subscription Activation  
> **Status**: ⚠️ ADA TEMUAN KRITIS

---

## 📋 DAFTAR ISI

1. [Ringkasan Alur](#-ringkasan-alur)
2. [Flow Diagram](#-flow-diagram-lengkap)
3. [Temuan Kritis](#-temuan-kritis)
4. [Bug & Kekurangan](#-bug--kekurangan)
5. [Rekomendasi Perbaikan](#-rekomendasi-perbaikan)
6. [Security Analysis](#-security-analysis)

---

## 🎯 RINGKASAN ALUR

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ALUR PEMBAYARAN REPLYAI                                  │
└─────────────────────────────────────────────────────────────────────────────┘

PRICING PAGE
     │
     ▼
┌─────────────────────────┐
│ User pilih paket        │
│ Klik "Pilih Paket"      │
└──────────┬──────────────┘
           │
           ▼
CHECKOUT PAGE (/checkout/{plan})
     │
     ├── Pilih durasi (1/12 bulan)
     ├── Pilih metode (manual/midtrans)
     └── Klik "Checkout"
           │
           ▼
INVOICE CREATED
     │
     ├── Pembayaran Manual ──▶ Upload Bukti Transfer
     │                           └── Tunggu verifikasi admin
     │
     └── Midtrans ──▶ Snap Token
                      └── Popup pembayaran
                           │
                           ├── Success ──▶ Webhook ──▶ Subscription Active
                           └── Pending ──▶ Webhook ──▶ Waiting Payment
```

---

## 🔄 FLOW DIAGRAM LENGKAP

```
╔═══════════════════════════════════════════════════════════════════════════════╗
║                           1. PRICING PAGE                                     ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  URL: /pricing                                                                ║
║  Controller: Closure di web.php                                               ║
║                                                                               ║
║  Logika:                                                                      ║
║  ┌─────────────────────────────────────────────────────────────────────────┐ ║
║  │ Jika user login + pilih plan    ──▶ Redirect /checkout/{plan}          │ ║
║  │ Jika user login + tidak pilih   ──▶ Tampil pricing + cek pending      │ ║
║  │ Jika guest + pilih plan         ──▶ Save session ──▶ /register        │ ║
║  │ Jika guest + tidak pilih        ──▶ Tampil pricing                    │ ║
║  └─────────────────────────────────────────────────────────────────────────┘ ║
║                                                                               ║
║  ⚠️ TEMUAN:                                                                   ║
║  - Pricing page bisa diakses oleh user yang sudah punya subscription aktif   ║
║  - Tidak ada redirect ke dashboard jika sudah aktif                          ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════════════════╗
║                        2. CHECKOUT PAGE                                       ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  URL: /checkout/{plan}                                                        ║
║  Controller: CheckoutController@checkout                                      ║
║                                                                               ║
║  Validasi:                                                                    ║
║  - Plan harus aktif (is_active = true)                                       ║
║  - Plan gratis redirect ke pricing                                           ║
║                                                                               ║
║  ⚠️ TEMUAN:                                                                   ║
║  - Tidak ada cek apakah user sudah punya subscription pending/active         ║
║  - Bisa membuat multiple pending payments untuk plan yang sama               ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════════════════╗
║                     3. PROSES CHECKOUT                                        ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  URL: POST /checkout/{plan}                                                   ║
║  Controller: CheckoutController@processCheckout                               ║
║  Service: PaymentService@createPayment                                        ║
║                                                                               ║
║  Input Validation:                                                            ║
║  - duration: required, in:1,12                                               ║
║  - promo_code: nullable, string, max:50                                      ║
║  - payment_method: nullable, in:manual,midtrans                              ║
║                                                                               ║
║  Proses:                                                                      ║
║  ┌─────────────────────────────────────────────────────────────────────────┐ ║
║  │ 1. Hitung harga (monthly/yearly)                                        │ ║
║  │ 2. Validasi & apply promo code                                          │ ║
║  │ 3. Lock invoice generation (atomic)                                     │ ║
║  │ 4. Generate invoice number                                              │ ║
║  │ 5. Create payment record (status: pending)                              │ ║
║  │ 6. Set expires_at (+24 jam)                                             │ ║
║  └─────────────────────────────────────────────────────────────────────────┘ ║
║                                                                               ║
║  ⚠️ TEMUAN KRITIS:                                                            ║
║  - Harga paket tidak di-calculate ulang di backend (client-side only)        ║
║  - Potensi manipulation harga via request tampering                          ║
║  - Promo code tidak dicek kepemilikan user (bisa pakai kode orang lain)      ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════════════════╗
║                     4. PEMBAYARAN MANUAL                                      ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  URL: /payment/{invoiceNumber}                                                ║
║  Controller: CheckoutController@payment                                       ║
║                                                                               ║
║  Proses Upload Bukti:                                                         ║
║  POST /payment/{payment}/upload-proof                                         ║
║                                                                               ║
║  Validasi:                                                                    ║
║  - proof: required, image, max:5MB                                            ║
║  - Cek kepemilikan payment (user_id === auth()->id())                        ║
║  - Cek status payment (harus pending)                                        ║
║                                                                               ║
║  ⚠️ TEMUAN:                                                                   ║
║  - File upload tidak di-rename (nama asli bisa expose informasi)             ║
║  - Tidak ada validasi image dimensions/size ratio                            ║
║  - Path storage bisa di-guess (predictable filename)                         ║
║  - Tidak ada notifikasi real-time ke admin (hanya log)                       ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════════════════╗
║                     5. PEMBAYARAN MIDTRANS                                    ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  URL: /payment/{invoiceNumber}/midtrans                                       ║
║  Controller: CheckoutController@payWithMidtrans                               ║
║  Service: MidtransService@createSnapTransaction                               ║
║                                                                               ║
║  Flow:                                                                        ║
║  ┌─────────────────────────────────────────────────────────────────────────┐ ║
║  │ 1. Cek payment status (harus pending)                                   │ ║
║  │ 2. Cek existing snap token (< 23 jam)                                   │ ║
║  │ 3. Buat order_id dengan timestamp suffix                                │ ║
║  │ 4. Generate Snap token                                                  │ ║
║  │ 5. Simpan token ke metadata                                             │ ║
║  │ 6. Tampilkan popup Midtrans                                             │ ║
║  └─────────────────────────────────────────────────────────────────────────┘ ║
║                                                                               ║
║  ⚠️ TEMUAN KRITIS:                                                            ║
║  - Order ID pakai timestamp suffix (bisa dibypass dengan replay attack)      ║
║  - Tidak ada validasi amount di Midtrans callback (trust client data)        ║
║  - Signature verification ada tapi tidak strict                              ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════════════════╗
║                     6. MIDTRANS CALLBACK                                      ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  URL: GET /checkout/midtrans/finish                                           ║
║  Controller: CheckoutController@midtransFinish                                ║
║                                                                               ║
║  Proses:                                                                      ║
║  ┌─────────────────────────────────────────────────────────────────────────┐ ║
║  │ 1. Extract invoice dari order_id (hapus timestamp suffix)               │ ║
║  │ 2. Cek status dari URL parameter                                        │ ║
║  │ 3. Jika settlement/capture → update payment → activate subscription     │ ║
║  │ 4. Fallback: Cek status ke Midtrans API                                 │ ║
║  └─────────────────────────────────────────────────────────────────────────┘ ║
║                                                                               ║
║  ⚠️ TEMUAN KRITIS:                                                            ║
║  - Callback URL bisa diakses langsung tanpa session (bypass)                 ║
║  - Tidak ada cek apakah callback berasal dari Midtrans (referer check)       ║
║  - Status dari URL parameter di-trust tanpa verify ke Midtrans API           │ ║
║  - Race condition possible (multiple callback)                               │ ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════════════════╗
║                     7. MIDTRANS WEBHOOK                                      ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  URL: POST /api/midtrans/notification                                         ║
║  Controller: MidtransWebhookController@handleNotification                     ║
║                                                                               ║
║  Security:                                                                    ║
║  - Signature verification dengan SHA512                                       ║
║  - Server key sebagai secret                                                  ║
║                                                                               ║
║  Proses:                                                                      ║
║  ┌─────────────────────────────────────────────────────────────────────────┐ ║
║  │ 1. Verify signature                                                     │ ║
║  │ 2. Extract order_id                                                     │ ║
║  │ 3. Find payment by invoice_number                                       │ ║
║  │ 4. Handle status: settlement/capture/pending/cancel/deny/expire         │ ║
║  │ 5. Activate subscription jika sukses                                    │ ║
║  └─────────────────────────────────────────────────────────────────────────┘ ║
║                                                                               ║
║  ⚠️ TEMUAN:                                                                   ║
║  - Webhook tidak idempotent (bisa process payment yang sudah paid)           ║
║  - Tidak ada retry mechanism untuk failed webhook                            ║
║  - Tidak ada logging detail untuk debugging                                  ║
║  - Email notifikasi sukses tidak diimplementasi (TODO)                       │ ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════════════════════════╗
║                     8. AKTIVASI SUBSCRIPTION                                  ║
╠═══════════════════════════════════════════════════════════════════════════════╣
║                                                                               ║
║  Service: PaymentService@activateSubscription                                 ║
║  Service: SubscriptionService@upgrade/renew                                   ║
║                                                                               ║
║  Proses:                                                                      ║
║  ┌─────────────────────────────────────────────────────────────────────────┐ ║
║  │ 1. Cek existing subscription                                            │ ║
║  │ 2. Jika same plan → RENEW (extend expiry)                               │ ║
║  │ 3. Jika different plan → UPGRADE (create new)                           │ ║
║  │ 4. Calculate dates (starts_at, expires_at)                              │ ║
║  │ 5. Save subscription                                                    │ ║
║  └─────────────────────────────────────────────────────────────────────────┘ ║
║                                                                               ║
║  ⚠️ TEMUAN:                                                                   ║
║  - Tidak ada grace period handling yang proper                               │ ║
║  - Downgrade plan tidak di-handle (bisa downgrade otomatis)                  │ ║
║  - Prorated calculation tidak ada (bayar penuh meski upgrade di tengah)      │ ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝
```

---

## 🚨 TEMUAN KRITIS

### [CRITICAL-001] Harga Bisa Dimanipulasi (Price Manipulation)

**Lokasi**: `CheckoutController@processCheckout`  
**Risk**: HIGH - Financial Loss

**Masalah**:
Harga tidak di-calculate ulang di backend. Frontend kirim harga, backend hanya trust input.

```php
// Saat ini (VULNERABLE):
$payment = $this->paymentService->createPayment(
    $user,
    $plan,
    $duration,
    $promoCode
);

// Di PaymentService:
$amount = $durationMonths === 12 
    ? $plan->price_yearly   // ✅ Ambil dari DB
    : $plan->price_monthly; // ✅ Ambil dari DB

// Tapi tidak ada validasi:
// - Apakah harga sudah benar?
// - Apakah promo valid untuk plan ini?
```

**Attack Scenario**:
1. Attacker intercept request
2. Ubah durasi/harga di request
3. Backend create payment dengan harga salah
4. Loss revenue

**Fix**:
```php
// Recalculate harga di backend
$expectedAmount = $duration === 12 
    ? $plan->price_yearly 
    : $plan->price_monthly;

// Validate promo code belongs to user or is public
if ($promoCode) {
    $promo = PromoCode::where('code', $promoCode)
        ->valid()
        ->forPlan($plan->id)  // Cek apakah promo untuk plan ini
        ->first();
}
```

---

### [CRITICAL-002] Multiple Pending Payments

**Lokasi**: `PaymentService@createPayment`  
**Risk**: MEDIUM - User Confusion, Admin Overload

**Masalah**:
User bisa membuat unlimited pending payments untuk plan yang sama.

```php
// Tidak ada cek existing pending payment
$payment = Payment::create([...]);
```

**Impact**:
- User bingung mana payment yang valid
- Admin kewalahan verifikasi
- Database penuh dengan garbage

**Fix**:
```php
// Cek existing pending payment
$existingPending = Payment::where('user_id', $user->id)
    ->where('plan_id', $plan->id)
    ->where('status', 'pending')
    ->where('expires_at', '>', now())
    ->first();

if ($existingPending) {
    return redirect()->route('checkout.payment', $existingPending->invoice_number)
        ->with('info', 'Anda sudah memiliki invoice pending. Silakan selesaikan pembayaran.');
}
```

---

### [CRITICAL-003] Midtrans Callback Bypass

**Lokasi**: `CheckoutController@midtransFinish`  
**Risk**: HIGH - Unauthorized Access

**Masalah**:
Callback URL bisa diakses langsung tanpa session Midtrans.

```php
public function midtransFinish(Request $request)
{
    $transactionStatus = $request->get('transaction_status');
    
    if (in_array($transactionStatus, ['settlement', 'capture'])) {
        // Langsung update tanpa verify ke Midtrans API!
        $payment->update(['status' => 'paid']);
    }
}
```

**Attack Scenario**:
1. Attacker buat payment dengan status pending
2. Attacker akses langsung: `/checkout/midtrans/finish?invoice=INV-XXX&transaction_status=settlement`
3. Payment langsung jadi PAID tanpa bayar!

**Fix**:
```php
// Selalu verify ke Midtrans API
$status = $this->midtransService->getTransactionStatus($invoiceNumber);

if (!in_array($status->transaction_status, ['settlement', 'capture'])) {
    abort(403, 'Payment not verified');
}

// Then update
$payment->update(['status' => 'paid']);
```

---

### [HIGH-001] File Upload Security Issues

**Lokasi**: `CheckoutController@uploadProof`  
**Risk**: MEDIUM

**Masalah**:
1. Filename tidak di-randomize (predictable)
2. Tidak ada image validation (dimensions, ratio)
3. Path bisa di-guess

```php
$path = $request->file('proof')->store('payment-proofs', 'public');
// Result: payment-proofs/original-filename.jpg
```

**Fix**:
```php
$filename = Str::random(40) . '.' . $file->extension();
$path = $file->storeAs('payment-proofs', $filename, 'public');

// Validate image
$request->validate([
    'proof' => 'required|image|mimes:jpg,jpeg,png|max:5120|dimensions:min_width=100,min_height=100',
]);
```

---

### [HIGH-002] Webhook Not Idempotent

**Lokasi**: `MidtransWebhookController@handleNotification`  
**Risk**: MEDIUM - Race Condition

**Masalah**:
Webhook bisa diproses multiple times, subscription bisa di-extend multiple times.

```php
private function markAsPaid(Payment $payment, $notification): void
{
    // Cek sudah paid, tapi tidak ada locking!
    if ($payment->status === Payment::STATUS_PAID) {
        return;
    }
    // ... process payment
}
```

**Fix**:
```php
// Gunakan database locking
DB::transaction(function () use ($payment) {
    $payment = Payment::lockForUpdate()->find($payment->id);
    
    if ($payment->status === Payment::STATUS_PAID) {
        return; // Already processed
    }
    
    // Process payment...
}, 5); // 5 retries
```

---

### [MEDIUM-001] Missing Notifications

**Lokasi**: `PaymentService`, `MidtransWebhookController`  
**Risk**: LOW - UX Issue

**Masalah**:
- Email notifikasi sukses: TODO (belum diimplementasi)
- Email notifikasi ke admin: TODO (belum diimplementasi)
- Real-time notification: Tidak ada

---

### [MEDIUM-002] No Expired Payment Cleanup

**Lokasi**: `Payment` model  
**Risk**: LOW - Database Bloat

**Masalah**:
Payments dengan status pending dan expired tidak di-cleanup otomatis.

**Fix**:
```php
// Tambah scheduled command
// app/Console/Commands/CleanupExpiredPayments.php

public function handle()
{
    Payment::where('status', 'pending')
        ->where('expires_at', '<', now())
        ->update(['status' => 'expired']);
}
```

---

## 🔒 SECURITY ANALYSIS

| Aspek | Status | Catatan |
|-------|--------|---------|
| **Price Validation** | 🔴 FAIL | Harga tidak di-validate ulang |
| **File Upload** | 🟡 WARN | Predictable filename |
| **Webhook Security** | 🟡 WARN | Signature ok, tapi not idempotent |
| **Callback Security** | 🔴 FAIL | Bisa di-bypass |
| **SQL Injection** | 🟢 PASS | Query menggunakan Eloquent |
| **XSS Protection** | 🟢 PASS | Blade escape output |
| **CSRF Protection** | 🟢 PASS | Form menggunakan @csrf |

---

## 💡 REKOMENDASI PERBAIKAN (Priority Order)

### Priority 1: CRITICAL (Fix Segera!)
1. ✅ Validate & recalculate price di backend
2. ✅ Prevent multiple pending payments
3. ✅ Fix Midtrans callback bypass (verify ke API)

### Priority 2: HIGH (Fix Dalam 1 Minggu)
4. ✅ Secure file upload (random filename, image validation)
5. ✅ Make webhook idempotent (database locking)
6. ✅ Add CSRF token check untuk callback

### Priority 3: MEDIUM (Fix Dalam 1 Bulan)
7. ✅ Implement email notifications
8. ✅ Add scheduled task untuk cleanup expired payments
9. ✅ Add audit logging untuk semua payment actions

---

## 📊 RINGKASAN

| Kategori | Jumlah | Status |
|----------|--------|--------|
| 🔴 Critical | 3 | Perlu fix segera |
| 🟠 High | 2 | Perlu fix minggu ini |
| 🟡 Medium | 2 | Perlu fix bulan ini |
| 🟢 Low | 0 | Optional |

**Overall Security**: 6/10 (⚠️ BUTUH PERBAIKAN)

**Rekomendasi**: **JANGAN DEPLOY KE PRODUCTION** sebelum critical issues di-fix!

---

*Audit selesai. Dokumen ini berisi temuan lengkap untuk perbaikan alur pembayaran.*
