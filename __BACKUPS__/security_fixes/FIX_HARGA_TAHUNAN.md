# 🔧 Fix: Harga Tahunan Plan Pro

## Problem
User melaporkan bahwa ketika memilih pembelian **1 tahun** untuk plan Pro, harganya tetap **Rp 500.000** (sama dengan harga bulanan).

## Root Cause
Di database, kolom `price_yearly` untuk plan Pro di-set ke **500.000** (salah), seharusnya **5.000.000**.

```
Plan Pro (sebelum fix):
- price_monthly: Rp 500.000 ✅
- price_yearly:  Rp 500.000 ❌ (seharusnya 5.000.000)
```

## Solution

### 1. Database Fix
Jalankan seeder untuk memperbaiki harga tahunan:

```bash
php artisan db:seed --class=FixPlanYearlyPricesSeeder
```

Hasil setelah fix:
```
Plan Pro (setelah fix):
- price_monthly: Rp 500.000 (per bulan)
- price_yearly:  Rp 5.000.000 (per tahun) ✅
- Hemat: Rp 1.000.000

Plan Business:
- price_monthly: Rp 1.500.000 
- price_yearly:  Rp 15.000.000
- Hemat: Rp 3.000.000

Plan Enterprise:
- price_monthly: Rp 3.500.000
- price_yearly:  Rp 35.000.000
- Hemat: Rp 7.000.000
```

### 2. UI Updates

#### Checkout Page (`resources/views/pages/checkout/index.blade.php`)
- ✅ Tambah badge "HEMAT Rp xxx" di opsi tahunan (dinamis)
- ✅ Tambah info "(setara Rp xxx/bln)" di opsi tahunan
- ✅ Tambah info "✅ Hemat Rp xxx" di bagian total

#### Payment Page (`resources/views/pages/checkout/payment.blade.php`)
- ✅ Tambah info "🎉 Hemat" jika durasi 12 bulan
- ✅ Tambah info "(setara Rp xxx/bulan)" di durasi

## Files Created/Modified

### New Files:
```
database/seeders/FixPlanYearlyPricesSeeder.php
FIX_HARGA_TAHUNAN.md
```

### Modified Files:
```
resources/views/pages/checkout/index.blade.php
resources/views/pages/checkout/payment.blade.php
```

## Testing

### Test Checkout Flow:
1. Buka halaman pricing → Pilih Plan Pro
2. Di halaman checkout:
   - Pilih "Bulanan" → Total: Rp 500.000
   - Pilih "Tahunan" → Total: Rp 5.000.000 (dengan badge "HEMAT Rp 1.000.000")
3. Submit → Invoice created dengan harga yang benar
4. Di halaman payment: Tampil "Hemat Rp 1.000.000" dan "setara Rp 416.667/bulan"

### Verify Database:
```bash
php artisan tinker
>>> App\Models\Plan::where('slug', 'pro')->first(['price_monthly', 'price_yearly'])
```

Expected:
```
price_monthly: 500000
price_yearly:  5000000
```

## Notes
- Harga tahunan = harga bulanan × 12 − diskon
- Diskon otomatis dihitung: `savings = (monthly × 12) − yearly`
- Badge "HEMAT" hanya muncul jika ada penghematan
