# 🔧 FIX: Invoice Amount Mismatch

## Problem
Invoice dibuat dengan amount yang salah untuk durasi yang dipilih:

```
Invoice: INV-2026-00001
Durasi: 12 bulan
Amount: Rp 500.000 (SALAH!)
Expected: Rp 5.000.000
```

## Root Cause

### Invoice Lama (Sebelum Fix)
Invoice INV-2026-00001 dibuat **sebelum** fix durasi, dengan:
- duration_months: 12
- amount: 500.000 (salah - seharusnya 5.000.000)

### Setelah Fix
Ketika user checkout 12 bulan lagi:
1. Sistem cek existing payment dengan durasi 12 bulan
2. Ketemu INV-2026-00001 (durasi 12, amount 500rb)
3. Return invoice lama dengan amount salah

## Solution

### 1. Cleanup Invoice Salah
```bash
php artisan payments:cleanup-wrong-amount --force
```

Result:
- Invoice INV-2026-00001 di-cancel
- User bisa buat invoice baru dengan amount benar

### 2. Update PaymentService
Tambahkan validasi amount di `getExistingPendingPayment()`:

```php
private function getExistingPendingPayment(int $userId, int $planId, int $durationMonths, int $expectedAmount): ?Payment
{
    return Payment::where('user_id', $userId)
        ->where('plan_id', $planId)
        ->where('duration_months', $durationMonths)
        ->where('amount', $expectedAmount)  // ← TAMBAHAN INI
        ->where('status', Payment::STATUS_PENDING)
        ->where('expires_at', '>', now())
        ->first();
}
```

Ini memastikan hanya invoice dengan amount yang SESUAI yang direuse.

## Testing

### Sebelum Fix:
```
Checkout 12 bulan → Return INV-00001 (500rb) ❌
```

### Setelah Fix:
```
Checkout 12 bulan → Buat INV-00002 (5jt) ✅
Checkout 12 bulan lagi → Return INV-00002 (5jt) ✅
Checkout 1 bulan → Buat INV-00003 (500rb) ✅
```

## Commands

```bash
# Check wrong amount payments (dry run)
php artisan payments:cleanup-wrong-amount --dry-run

# Fix wrong amount payments
php artisan payments:cleanup-wrong-amount --force

# Check logs
tail -f storage/logs/laravel.log | grep "Checkout process"
```

## Files Modified

```
app/Services/PaymentService.php              ← Add amount validation
app/Console/Commands/CleanupWrongAmountPayments.php  ← New command
```

## Prevention

Sekarang sistem akan:
1. ✅ Cek existing payment berdasarkan plan + durasi + amount
2. ✅ Auto-cleanup payments dengan amount salah
3. ✅ Buat invoice baru kalau amount tidak sesuai

**Invoice dengan amount salah tidak akan direuse lagi!**
