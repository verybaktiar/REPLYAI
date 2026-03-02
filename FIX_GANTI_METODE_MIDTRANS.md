# 🔄 FIX: Ganti Metode Pembayaran Midtrans

## Problem
User sudah pilih Virtual Account (VA) di Midtrans, kemudian mau ganti ke QRIS. Tapi ketika buka Snap lagi, langsung ke VA (tidak bisa pilih metode lain).

## Root Cause
Midtrans Snap menggunakan **token** yang sama. Token tersebut mengingat pilihan metode pembayaran terakhir.

```
Token ABC123 → User pilih VA → Token ingat VA
Token ABC123 → Dibuka lagi → Langsung ke VA (tidak bisa ganti)
```

## Solution

### 1. Force Create New Token
Tambahkan parameter `forceNew` di `MidtransService::createSnapTransaction()`:

```php
public function createSnapTransaction(Payment $payment, bool $forceNew = false): array
{
    if (!$forceNew) {
        // Reuse existing token (default behavior)
    } else {
        // Always create new token
        Log::info('Force creating new Midtrans Snap token');
    }
    // ...
}
```

### 2. URL Parameter `?new=1`
Controller cek parameter `new`:

```php
$forceNew = $request->has('new');
$snapData = $this->midtransService->createSnapTransaction($payment, $forceNew);
```

### 3. UI Update
Tiga pilihan di modal pending:

```
┌─────────────────────────────────────────┐
│      [⏱️] Pembayaran Pending            │
│                                         │
│  Anda telah memilih metode...           │
│                                         │
│  [ℹ️] Jika ingin mengganti metode       │
│       pembayaran, klik "Pilih Metode    │
│       Lain" untuk membuat sesi baru     │
│                                         │
│  [👁️ Lihat Instruksi Pembayaran]       │
│  [🔄 Pilih Metode Lain] ← Buat token baru
│  [⬅️ Tutup & Kembali]                   │
└─────────────────────────────────────────┘
```

## User Flow

### Scenario: Ganti dari VA ke QRIS

**Sebelum Fix:**
```
1. Klik Bayar → Pilih VA → Pending
2. Tutup Snap
3. Klik Bayar lagi → Langsung ke VA ❌
4. Tidak bisa pilih QRIS
```

**Sesudah Fix:**
```
1. Klik Bayar → Pilih VA → Pending
2. Tutup Snap atau klik "Pilih Metode Lain"
3. Klik "🔄 Ganti Metode Pembayaran" (link dengan ?new=1)
4. Sistem buat token baru
5. Snap terbuka → Bisa pilih metode baru (QRIS) ✅
```

## Technical Details

### URL Pattern
```
/midtrans/INV-001          → Use existing token (if valid)
/midtrans/INV-001?new=1    → Force create new token
```

### Token Lifecycle
```
Token Lama (VA):
  - Created: 14:00
  - Method: VA Mandiri
  - Status: Pending
  
Token Baru (QRIS):
  - Created: 14:05 (after clicking "Pilih Metode Lain")
  - Method: Fresh selection
  - Status: Waiting for selection
```

## Files Modified

```
app/Services/MidtransService.php              ← Add forceNew parameter
app/Http/Controllers/CheckoutController.php   ← Handle ?new=1 parameter
resources/views/pages/checkout/midtrans.blade.php  ← Add "Pilih Metode Lain" button
```

## UI Changes

### Modal Payment Pending
Tambah tombol "Pilih Metode Lain":
```html
<a href="/midtrans/INV-001?new=1" class="...">
    <span class="material-symbols-outlined">sync</span>
    Pilih Metode Lain
</a>
```

### Info Box
```
ℹ️ Jika ingin mengganti metode pembayaran (contoh: dari VA ke QRIS), 
   klik "Pilih Metode Lain" untuk membuat sesi baru.
```

### Link di Bawah
```
🔄 Ganti Metode Pembayaran  ← Selalu ada untuk force new token
```

## Testing

### Test 1: Ganti Metode
1. Checkout → Midtrans → Pilih VA → Pending
2. Klik "Pilih Metode Lain"
3. Snap terbuka dengan token baru
4. Pilih QRIS → Berhasil ✅

### Test 2: Lanjut Metode yang Sama
1. Checkout → Midtrans → Pilih VA → Pending
2. Klik "Lihat Instruksi Pembayaran"
3. Kembali ke halaman payment
4. Klik Bayar lagi (tanpa ?new=1)
5. Langsung ke VA (token lama) ✅

### Test 3: Force New dari Link
1. Buka `/midtrans/INV-001?new=1`
2. Selalu buat token baru
3. Bisa pilih metode apapun ✅

## Notes

- Token lama tetap valid di Midtrans
- Payment yang sudah pending di token lama tetap ada
- User bisa bayar via metode mana pun yang tersedia
- Tidak ada duplikasi invoice

---

**Last Updated:** 2026-02-17
