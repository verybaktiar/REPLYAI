# Panduan Sistem Langganan ReplyAI

## 🎯 Untuk Pelanggan (User)

### Melihat Paket & Harga
1. Buka halaman `/pricing`
2. Lihat perbandingan fitur antar paket
3. Klik **"Pilih Paket"** untuk upgrade

### Proses Checkout
1. Pilih durasi (Bulanan/Tahunan)
2. Masukkan kode promo (jika ada)
3. Klik **"Lanjutkan Pembayaran"**
4. Transfer ke rekening yang ditampilkan
5. Upload bukti transfer
6. Tunggu verifikasi (maks 1x24 jam)

### Mengelola Langganan
- **Dashboard**: `/subscription` - Lihat status & penggunaan
- **Upgrade**: Klik "Upgrade Sekarang" di dashboard
- **Riwayat**: Lihat semua transaksi pembayaran

### Butuh Bantuan?
1. Buka `/support`
2. Klik **"Buat Tiket Baru"**
3. Pilih kategori & jelaskan masalah
4. Tunggu balasan dari tim support

---

## 🔐 Untuk Super Admin

### Login Admin
- URL: `/superadmin/login` (perlu diimplementasikan)
- Email: `admin@replyai.com`
- Password: `Admin123!` ⚠️ **GANTI SETELAH LOGIN**

### Fitur Admin (Foundation Ready)
- Approve pembayaran manual
- Kelola tenant/subscriber
- Lihat revenue & statistik
- Handle tiket support

### Cron Jobs Penting
Pastikan scheduler berjalan dengan:
```bash
php artisan schedule:run
```

Atau di crontab:
```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📋 Daftar Routes

### Pricing & Checkout
| Route | Method | Fungsi |
|-------|--------|--------|
| `/pricing` | GET | Halaman harga |
| `/checkout/{paket}` | GET | Form checkout |
| `/checkout/{paket}` | POST | Proses checkout |
| `/checkout/payment/{invoice}` | GET | Detail pembayaran |
| `/checkout/payment/{id}/upload` | POST | Upload bukti |

### Subscription
| Route | Method | Fungsi |
|-------|--------|--------|
| `/subscription` | GET | Dashboard langganan |
| `/subscription/upgrade` | GET | Pilihan upgrade |
| `/subscription/cancel` | POST | Batalkan langganan |

### Support
| Route | Method | Fungsi |
|-------|--------|--------|
| `/support` | GET | Daftar tiket |
| `/support/create` | GET | Form tiket baru |
| `/support` | POST | Submit tiket |
| `/support/{id}` | GET | Detail tiket |
| `/support/{id}/reply` | POST | Balas tiket |

---

## 🗄️ Database

### Tabel Utama
- `plans` - Daftar paket langganan
- `subscriptions` - Langganan user
- `payments` - Riwayat pembayaran
- `usage_records` - Penggunaan fitur
- `promo_codes` - Kode diskon
- `support_tickets` - Tiket bantuan
- `admin_users` - Admin panel

### Paket Default
| Paket | Harga | AI Messages | Kontak |
|-------|-------|-------------|--------|
| Gratis | Rp 0 | 100/bulan | 100 |
| Hemat | Rp 99.000 | 500/bulan | 500 |
| Pro | Rp 249.000 | 2.000/bulan | Unlimited |
| Enterprise | Rp 500.000 | Unlimited | Unlimited |

---

## ⚙️ Commands

```bash
# Cek langganan expired (dijalankan scheduler)
php artisan subscription:check-expired --send-reminders

# Reset penggunaan bulanan (dijalankan scheduler)
php artisan usage:reset-monthly

# Seed paket default
php artisan db:seed --class=PlanSeeder

# Seed admin default
php artisan db:seed --class=AdminSeeder
```

---

## 🔧 Environment Variables

Tambahkan di `.env`:
```env
# Bank Info untuk Manual Transfer
BANK_BCA_NUMBER=1234567890
BANK_BCA_NAME="PT ReplyAI Indonesia"
BANK_MANDIRI_NUMBER=0987654321
BANK_MANDIRI_NAME="PT ReplyAI Indonesia"
```

---

*Dokumentasi dibuat: 17 Januari 2026*
