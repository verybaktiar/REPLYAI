# 🔐 Panduan Setup CAPTCHA & Security Features

> **Dokumen ini menjelaskan cara mengaktifkan CAPTCHA dan fitur keamanan baru.**

---

## 📋 Fitur Security yang Sudah Diimplementasikan

### ✅ 1. CAPTCHA (Anti-Bot Protection)
- **Lokasi**: Halaman registrasi (`/register`)
- **Provider**: hCaptcha (recommended), reCAPTCHA v3, atau Cloudflare Turnstile
- **Status**: Gratis hingga 1 juta request/bulan

### ✅ 2. Disposable Email Blocker
- **Lokasi**: Validasi registrasi
- **Fungsi**: Memblokir 300+ domain temporary email
- **Contoh yang diblokir**: tempmail.com, 10minutemail.com, yopmail.com, dll

### ✅ 3. Global Rate Limiting
- **Lokasi**: Login & Register endpoints
- **Limit**: 10 request per menit per IP
- **Fungsi**: Mencegah brute force & credential stuffing

### ✅ 4. New Device Login Notification
- **Lokasi**: Setelah login berhasil
- **Fungsi**: Kirim email jika login dari device/browser baru
- **Info**: IP Address, Browser, OS, Lokasi

---

## 🚀 Langkah Setup CAPTCHA

### Pilihan 1: hCaptcha (Recommended ⭐)

**Keuntungan**:
- Privacy-focused (tidak tracking user)
- Gratis 1 juta request/bulan
- Mudah setup

**Cara Daftar**:
1. Kunjungi https://www.hcaptcha.com/
2. Sign up gratis
3. Add new site
4. Copy **Site Key** dan **Secret Key**

**Konfigurasi .env**:
```env
CAPTCHA_ENABLED=true
CAPTCHA_PROVIDER=hcaptcha
CAPTCHA_SITE_KEY=10000000-aaaa-bbbb-cccc-000000000001
CAPTCHA_SECRET=0x0000000000000000000000000000000000000000
```

---

### Pilihan 2: Google reCAPTCHA v3

**Keuntungan**:
- Paling populer
- Invisible (tidak perlu klik)

**Cara Daftar**:
1. Kunjungi https://www.google.com/recaptcha/admin
2. Sign in dengan Google account
3. Create new site
4. Pilih reCAPTCHA v3
5. Copy **Site Key** dan **Secret Key**

**Konfigurasi .env**:
```env
CAPTCHA_ENABLED=true
CAPTCHA_PROVIDER=recaptcha
CAPTCHA_SITE_KEY=6Lxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
CAPTCHA_SECRET=6Lxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

### Pilihan 3: Cloudflare Turnstile (Terbaru 🔥)

**Keuntungan**:
- **Gratis unlimited!**
- Privacy-focused
- Tidak ada CAPTCHA challenge (invisible)
- Integrasi baik dengan Cloudflare

**Cara Daftar**:
1. Kunjungi https://dash.cloudflare.com/
2. Login atau signup
3. Pergi ke **Turnstile**
4. Add widget
5. Copy **Site Key** dan **Secret Key**

**Konfigurasi .env**:
```env
CAPTCHA_ENABLED=true
CAPTCHA_PROVIDER=turnstile
CAPTCHA_SITE_KEY=0x4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
CAPTCHA_SECRET=0x4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## 🧪 Testing

### Test Disposable Email Blocker
```bash
# Coba registrasi dengan email temporary (harus gagal)
curl -X POST http://localhost:8000/register \
  -d "name=Test&email=test@tempmail.com&password=password123&password_confirmation=password123"

# Coba dengan email valid (harus sukses)
curl -X POST http://localhost:8000/register \
  -d "name=Test&email=test@gmail.com&password=Password123!&password_confirmation=Password123!"
```

### Test Rate Limiting
```bash
# Jalankan 15x dalam 1 menit (request ke-11+ harus gagal dengan 429)
for i in {1..15}; do
  curl -X POST http://localhost:8000/login \
    -d "email=test@gmail.com&password=wrongpassword"
done
```

### Test CAPTCHA
1. Buka halaman registrasi
2. CAPTCHA widget harus muncul
3. Coba submit tanpa mengisi CAPTCHA → Harus error
4. Isi CAPTCHA dengan benar → Harus sukses

---

## 📊 Monitoring

### Check Logs
```bash
# Lihat log CAPTCHA failures
tail -f storage/logs/laravel.log | grep "CAPTCHA"

# Lihat log rate limiting
tail -f storage/logs/laravel.log | grep "rate"

# Lihat log new device login
tail -f storage/logs/laravel.log | grep "NewDevice"
```

---

## 🎯 Rekomendasi untuk Production

### 1. Aktifkan CAPTCHA
```env
CAPTCHA_ENABLED=true
CAPTCHA_PROVIDER=hcaptcha  # atau turnstile
```

### 2. Pastikan Queue Running (untuk email notifikasi)
```bash
php artisan queue:work --daemon
```

### 3. Monitor Rate Limit Hits
```bash
# Cek berapa banyak request yang di-block
php artisan cache:clear  # Reset jika perlu
```

### 4. Update Daftar Disposable Email (opsional)
Tambahkan domain baru ke `app/Rules/NotDisposableEmail.php` jika menemukan provider baru.

---

## 🔧 Troubleshooting

### CAPTCHA Tidak Muncul
**Penyebab**: 
- `CAPTCHA_ENABLED=false`
- Site key salah
- JavaScript tidak load

**Solusi**:
```bash
# Cek config
cat .env | grep CAPTCHA

# Clear cache
php artisan config:clear
php artisan view:clear
```

### Rate Limit Terlalu Strict
**Solusi**: Edit `routes/auth.php`
```php
// Default: 10 request per menit
Route::middleware(['guest', 'rate_limit:10,1'])

// Lebih longgar: 30 request per menit  
Route::middleware(['guest', 'rate_limit:30,1'])
```

### Email Notifikasi Tidak Dikirim
**Cek**:
```bash
# Cek queue
curl artisan queue:monitor

# Cek failed jobs
php artisan queue:failed

# Cek mail config
cat .env | grep MAIL
```

---

## 📞 Support

Jika ada masalah:
1. Cek log: `storage/logs/laravel.log`
2. Test config: `php artisan tinker` → `config('services.captcha')`
3. Clear all cache: `php artisan optimize:clear`

---

**Status**: ✅ Semua fitur security siap digunakan!

Silakan pilih provider CAPTCHA dan update `.env` file Anda.
