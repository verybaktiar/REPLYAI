# 🔧 FIX: Durasi Checkout Tidak Sesuai

## Problem
User melaporkan:
- Pilih plan Pro (harga 500rb/bulan)
- Pilih durasi **12 bulan (tahunan)**
- Total yang tampil: **Rp 500.000** (salah, seharusnya Rp 5.000.000)
- Badge: "Hemat Rp 1.000.000" (ini bener, tapi totalnya salah)

## Root Cause
Ada **2 masalah**:

### Masalah 1: Logic Existing Payment (UTAMA)
Sistem cek existing pending payment hanya berdasarkan `plan_id`, tanpa cek `duration_months`.

**Flow yang salah:**
```
1. User checkout 1 bulan → Invoice INV-001 (500rb) created
2. User checkout 12 bulan → Sistem cek: "Ada pending plan Pro?"
3. Sistem: "Ada!" → Return INV-001 (500rb) ← SALAH!
4. Seharusnya: Buat invoice baru INV-002 (5jt)
```

### Masalah 2: Alpine.js x-text
Menggunakan `x-text` untuk mengubah total, yang terkadang tidak update dengan benar.

---

## Solution

### Fix 1: Cek Existing Payment dengan Durasi
**File:** `app/Services/PaymentService.php`

```php
// SEBELUM: Cek hanya berdasarkan plan
$existingPending = $this->getExistingPendingPayment($user->id, $plan->id);

// SESUDAH: Cek berdasarkan plan DAN durasi
$existingPending = $this->getExistingPendingPayment($user->id, $plan->id, $durationMonths);
```

Method `getExistingPendingPayment()` sekarang menerima parameter `$durationMonths`:
```php
private function getExistingPendingPayment(int $userId, int $planId, int $durationMonths): ?Payment
{
    return Payment::where('user_id', $userId)
        ->where('plan_id', $planId)
        ->where('duration_months', $durationMonths)  // ← TAMBAHAN INI
        ->where('status', Payment::STATUS_PENDING)
        ->where('expires_at', '>', now())
        ->first();
}
```

### Fix 2: Tampilan Total dengan x-show
**File:** `resources/views/pages/checkout/index.blade.php`

Ganti dari:
```html
<span x-text="duration === '12' ? 'Rp 5.000.000' : 'Rp 500.000'"></span>
```

Menjadi dua div terpisah:
```html
<!-- Total Bulanan -->
<div x-show="duration === '1'">
    <span>Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
</div>

<!-- Total Tahunan -->
<div x-show="duration === '12'">
    <span>Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}</span>
</div>
```

---

## Behavior Setelah Fix

### Scenario 1: Checkout Durasi Berbeda
```
1. Checkout Plan Pro 1 bulan   → Invoice INV-001 (500rb)
2. Checkout Plan Pro 12 bulan  → Invoice INV-002 (5jt) ✅
3. Checkout Plan Pro 1 bulan lagi → Return INV-001 (500rb) ✅
```

### Scenario 2: Tampilan Checkout
```
[Pilih Tahunan]
Total Pembayaran: Rp 5.000.000 ✅
✅ Hemat Rp 1.000.000
untuk 12 bulan
```

---

## Testing

### Test Manual:
1. Buka `/checkout/pro`
2. Pilih "Bulanan" → Total: Rp 500.000
3. Pilih "Tahunan" → Total: Rp 5.000.000 ✅
4. Submit → Invoice dengan harga yang benar

### Test dengan Existing Payment:
1. Checkout 1 bulan → Invoice A
2. Checkout 12 bulan → Invoice B (baru) ✅
3. Cek database: 2 invoice pending

---

## Files Modified

```
app/Services/PaymentService.php                  ← Fix existing payment logic
app/Http/Controllers/CheckoutController.php      ← Add debug logging
app/Http/View/Composers/PendingPaymentComposer.php ← Support multiple pending
resources/views/pages/checkout/index.blade.php   ← Fix total display
FIX_DURASI_CHECKOUT.md                           ← This file
```

---

## Log Debug

Tambahkan ini di `storage/logs/laravel.log` untuk memantau:

```
[Checkout process started] 
  user_id: 1, plan_id: 3, duration: 12
  price_monthly: 500000, price_yearly: 5000000

[Payment created]
  invoice: INV-2026-00001, amount: 5000000
  duration_months: 12
```

---

## Migration Data (jika perlu)

Kalau ada data existing yang salah:

```sql
-- Cek payments dengan amount tidak sesuai durasi
SELECT invoice_number, amount, duration_months, plan_id 
FROM payments 
WHERE status = 'pending'
AND ((duration_months = 1 AND amount > 500000) 
  OR (duration_months = 12 AND amount < 1000000));
```

---

**Last Updated:** 2026-02-17
