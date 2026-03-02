# 🎨 FIX: Midtrans UX Improvement

## Problem
1. User tidak bisa kembali ke pilihan metode pembayaran setelah membuka Snap
2. Notice/alert menggunakan `confirm()` bawaan browser yang tidak bagus
3. Tidak ada pilihan jelas untuk "Lihat Instruksi" vs "Ganti Metode"

## Solution

### 1. Custom Modal (Ganti confirm() bawaan)

**Sebelum:**
```javascript
onPending: function(result) {
    if (confirm('Pembayaran Anda pending...')) {
        // OK
    }
    // Cancel
}
```
Tampilan: Alert browser standar, tidak profesional

**Sesudah:**
```javascript
onPending: function(result) {
    showPendingModal(); // Custom modal dengan Tailwind CSS
}
```
Tampilan: Modal custom dengan:
- ✅ Icon jam (schedule) warna kuning
- ✅ Dua tombol yang jelas
- ✅ Animasi smooth (fade + scale)

### 2. Modal Payment Pending

Tampilan modal:
```
┌─────────────────────────────────────┐
│           [⏱️ Icon]                 │
│     Pembayaran Pending              │
│                                     │
│  Anda telah memilih metode...       │
│                                     │
│  [👁️ Lihat Instruksi Pembayaran]   │
│  [🔄 Ganti Metode Pembayaran]       │
│                                     │
│  Anda juga bisa menyelesaikan...    │
└─────────────────────────────────────┘
```

**Tombol "Ganti Metode Pembayaran":**
- Tutup modal
- Reset state tombol "Bayar Sekarang"
- User bisa klik "Kembali ke pilihan pembayaran"

### 3. Modal Payment Error

Tampilan modal:
```
┌─────────────────────────────────────┐
│           [⚠️ Icon]                 │
│     Pembayaran Gagal                │
│                                     │
│  Terjadi kesalahan...               │
│                                     │
│  [🔄 Coba Lagi]                     │
│  [⬅️ Kembali ke Pilihan Pembayaran] │
└─────────────────────────────────────┘
```

### 4. Tombol Kembali

Sudah ada di bawah card:
```
← Kembali ke pilihan pembayaran
```
Link ke: `{{ route('checkout.payment', $payment->invoice_number) }}`

## Files Modified

```
resources/views/pages/checkout/midtrans.blade.php  ← Complete rewrite
```

## User Flow

### Scenario 1: User Mau Lihat Instruksi
1. Klik "Bayar Sekarang"
2. Pilih metode di Snap → Pending
3. Muncul modal "Pembayaran Pending"
4. Klik "Lihat Instruksi Pembayaran"
5. Redirect ke halaman instruksi ✅

### Scenario 2: User Mau Ganti Metode
1. Klik "Bayar Sekarang"
2. Pilih metode di Snap → Pending
3. Muncul modal "Pembayaran Pending"
4. Klik "Ganti Metode Pembayaran"
5. Modal tutup, tombol "Bayar" muncul lagi
6. Klik "Kembali ke pilihan pembayaran"
7. Kembali ke halaman payment dengan semua pilihan ✅

### Scenario 3: Payment Error
1. Klik "Bayar Sekarang"
2. Error di Snap
3. Muncul modal "Pembayaran Gagal"
4. Pilihan: Coba Lagi atau Kembali ✅

## Technical Details

### Modal System
- Pure JavaScript (tanpa library tambahan)
- Tailwind CSS untuk styling
- CSS transitions untuk animasi

### Functions
```javascript
showPendingModal()     // Tampilkan modal pending
closePendingModal()    // Tutup modal pending
showErrorModal(msg)    // Tampilkan modal error
closeErrorModal()      // Tutup modal error
```

### Animations
- Backdrop: opacity 0 → 0.7
- Content: scale 0.95 → 1.0, opacity 0 → 1
- Duration: 300ms
- Easing: ease-out

## Testing

1. **Test Modal Pending:**
   - Klik "Bayar Sekarang"
   - Pilih metode pembayaran (tapi jangan bayar)
   - Modal pending muncul
   - Coba kedua tombol

2. **Test Modal Error:**
   - Klik "Bayar Sekarang"
   - Tutup Snap tanpa pilih metode (onClose)
   - atau tunggu sampai error

3. **Test Kembali:**
   - Dari modal pending, klik "Ganti Metode"
   - Klik "Kembali ke pilihan pembayaran"
   - Harus kembali ke `/payment/{invoice}`

## Notes

- Modal menggunakan `z-index: 50` untuk pastikan di atas semua
- Backdrop menggunakan `backdrop-blur` untuk efek modern
- Semua tombol menggunakan icon dari Material Symbols

---

**Last Updated:** 2026-02-17
