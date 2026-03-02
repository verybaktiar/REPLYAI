# 🔒 Security Fixes: Payment Vulnerabilities

**Status:** ✅ COMPLETED  
**Date:** 2026-02-16  
**Scope:** Payment System (Checkout, Midtrans, PaymentService)

---

## ✅ Fixed Vulnerabilities

### 🔴 CRITICAL-001: Price Manipulation Fix

**Problem:**  
Harga di-calculate di frontend dan dikirim ke backend. Attacker bisa modify price via browser dev tools.

**Fix:**
- ✅ Price SELALU di-calculate ulang di backend via `PaymentService::calculatePrice()`
- ✅ Database query untuk dapat harga terbaru: `Plan::find($plan->id)`
- ✅ Validation: Harga harus > 0, Plan harus aktif

**Code:**
```php
// PaymentService::calculatePrice() - Tidak trust frontend
private function calculatePrice(Plan $plan, int $durationMonths): int
{
    $freshPlan = Plan::find($plan->id); // Fresh from DB
    
    $price = match($durationMonths) {
        1 => $freshPlan->price_monthly,
        12 => $freshPlan->price_yearly,
        default => throw new Exception('Durasi tidak valid'),
    };
    
    if ($price <= 0) {
        throw new Exception('Harga plan tidak valid.');
    }
    
    return $price;
}
```

---

### 🔴 CRITICAL-002: Multiple Pending Payments Fix

**Problem:**  
User bisa membuat unlimited invoice pending untuk plan yang sama.

**Fix:**
- ✅ Cek existing pending payment sebelum create baru
- ✅ Double-check dengan database lock (`lockForUpdate()`)
- ✅ Update expires_at untuk existing payment

**Code:**
```php
// PaymentService::createPayment()
$existingPending = $this->getExistingPendingPayment($user->id, $plan->id);

if ($existingPending) {
    // Return existing dengan updated expires_at
    $existingPending->update(['expires_at' => now()->addHours(24)]);
    return $existingPending;
}
```

---

### 🔴 CRITICAL-003: Midtrans Callback Bypass Fix

**Problem:**  
URL `/checkout/midtrans/finish` bisa dipanggil langsung dengan parameter palsu tanpa verify ke Midtrans API.

**Fix:**
- ✅ SELALU verify ke Midtrans API via `getTransactionStatus()`
- ✅ Tidak trust URL parameters dari client
- ✅ Status check: settlement, capture, pending, cancel, deny, expire

**Code:**
```php
// CheckoutController::midtransFinish()
$status = $this->midtransService->getTransactionStatus($invoiceNumber);

if (in_array($status->transaction_status, ['settlement', 'capture'])) {
    // Process payment
} elseif ($status->transaction_status === 'pending') {
    // Redirect to waiting page
} elseif (in_array($status->transaction_status, ['cancel', 'deny', 'expire'])) {
    // Mark as failed
}
```

---

### 🔴 CRITICAL-004: Secure File Upload Fix

**Problem:**  
Filename predictable (invoice number) dan tidak ada validasi ketat.

**Fix:**
- ✅ Random filename: 40 karakter random + extension
- ✅ Validasi: hanya jpg, jpeg, png
- ✅ Size limit: 5MB
- ✅ Store di storage/app/public/payment-proofs

**Code:**
```php
// PaymentService::uploadProof()
$extension = $file->getClientOriginalExtension();
$filename = \Illuminate\Support\Str::random(40) . '.' . $extension;
$path = $file->storeAs('payment-proofs', $filename, 'public');
```

---

### 🔴 CRITICAL-005: Webhook Signature Verification

**Problem:**  
Webhook dari Midtrans tidak diverifikasi signature-nya.

**Fix:**
- ✅ SHA512 signature verification
- ✅ Hash comparison dengan `hash_equals()`
- ✅ Verify order_id, status_code, gross_amount, serverKey

**Code:**
```php
// MidtransWebhookController::handleNotification()
$expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

if (!hash_equals($expectedSignature, $signatureKey)) {
    return response()->json(['status' => 'error'], 403);
}
```

---

### 🔴 CRITICAL-006: Webhook Idempotency

**Problem:**  
Webhook bisa diproses multiple times menyebabkan double subscription.

**Fix:**
- ✅ Distributed lock dengan Cache::lock()
- ✅ Double-check status after lock
- ✅ Return 'already_processed' jika sudah diproses

**Code:**
```php
$lockKey = 'midtrans_webhook_' . $notification['transaction_id'];

return Cache::lock($lockKey, 60)->block(5, function () use ($payment) {
    $payment->refresh();
    
    if ($payment->status === 'paid') {
        return response()->json(['status' => 'already_processed']);
    }
    // Process...
});
```

---

### 🔴 CRITICAL-007: Amount Verification

**Problem:**  
Gross amount dari webhook tidak dicocokkan dengan payment di database.

**Fix:**
- ✅ Compare received amount dengan expected amount
- ✅ Return 400 jika mismatch

**Code:**
```php
$expectedAmount = (int) $payment->total;
$receivedAmount = (int) $grossAmount;

if ($receivedAmount !== $expectedAmount) {
    Log::error('Midtrans: Amount mismatch!', [
        'expected' => $expectedAmount,
        'received' => $receivedAmount,
    ]);
    return response()->json(['status' => 'error', 'message' => 'Amount mismatch'], 400);
}
```

---

## 📧 Email Notifications

**FIX-008:** Implementasi email notification untuk pembayaran

**Files:**
- `app/Mail/PaymentSuccessMail.php`
- `app/Mail/PaymentRejectedMail.php`
- `resources/views/emails/payment/success.blade.php`
- `resources/views/emails/payment/rejected.blade.php`

**Features:**
- Email dikirim via queue (async)
- EmailLog untuk tracking
- Markdown template

---

## 🧹 Expired Payment Cleanup

**FIX-009:** Auto-cleanup expired pending payments

**Command:**
```bash
# Manual cleanup
php artisan payments:cleanup-expired

# Force (tanpa konfirmasi)
php artisan payments:cleanup-expired --force
```

**Recommended Scheduler:**
```php
// app/Console/Kernel.php
$schedule->command('payments:cleanup-expired --force')->hourly();
```

---

## 🔍 Testing Checklist

### Price Manipulation Test
- [ ] Inspect element, ubah harga di hidden input → Harus tetap pakai harga database
- [ ] Ganti duration di request → Harus re-calculate
- [ ] Plan tidak aktif → Harus error

### Multiple Pending Payments Test
- [ ] Buat invoice pending → Sukses
- [ ] Buat invoice lagi untuk plan yang sama → Return existing invoice
- [ ] Cek expires_at diperbarui

### Midtrans Callback Test
- [ ] Akses `/checkout/midtrans/finish?transaction_status=settlement` tanpa bayar → Harus check API dulu
- [ ] Parameter palsu → Tidak update status
- [ ] Bayar beneran → Status updated

### File Upload Test
- [ ] Upload file besar (>5MB) → Error
- [ ] Upload non-image → Error
- [ ] Upload valid image → Filename random, tersimpan

### Webhook Test
- [ ] Kirim webhook tanpa signature → 403
- [ ] Kirim webhook dengan signature salah → 403
- [ ] Kirim webhook dengan amount salah → 400
- [ ] Kirim webhook valid → Processed
- [ ] Kirim webhook ulang → already_processed

---

## 📁 Files Modified

```
app/Services/PaymentService.php                  (Complete rewrite)
app/Http/Controllers/CheckoutController.php      (Security fixes)
app/Http/Controllers/Api/MidtransWebhookController.php (Signature + Idempotency)
app/Services/MidtransService.php                 (Minor updates)
app/Mail/PaymentSuccessMail.php                  (NEW)
app/Mail/PaymentRejectedMail.php                 (NEW)
app/Console/Commands/CleanupExpiredPayments.php  (NEW)
resources/views/emails/payment/success.blade.php (NEW)
resources/views/emails/payment/rejected.blade.php (NEW)
```

---

## 🔐 Security Headers

Tambahkan di `.htaccess` atau middleware:

```apache
# Prevent direct access to payment proofs
<FilesMatch "^payment-proofs/">
    Order deny,allow
    Deny from all
</FilesMatch>
```

---

## 📊 Monitoring

**Logs to watch:**
- `Midtrans: Amount mismatch!` → Possible tampering attempt
- `Midtrans: Invalid signature` → Fake webhook attempt
- `Payment created with validated price` → Audit trail

**Alerts:**
- Multiple failed signature verifications
- Amount mismatches
- Cleanup command results

---

## ✅ Deployment Checklist

- [ ] Backup database
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Clear config: `php artisan config:clear`
- [ ] Test semua payment flows
- [ ] Update Midtrans Dashboard (Notification URL)
- [ ] Test webhook dengan Postman/simulator
- [ ] Monitor logs untuk 24 jam pertama

---

**Last Updated:** 2026-02-16  
**By:** Security Fix Bot
