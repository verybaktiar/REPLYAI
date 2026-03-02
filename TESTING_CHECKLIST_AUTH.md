# ✅ Testing Checklist - Alur Autentikasi ReplyAI

> **Tanggal**: 16 Februari 2026  
> **Status**: Ready for Testing  
> **Tester**: [Nama Tester]

---

## 🎯 SCOPE TESTING

Fitur yang di-test:
1. ✅ Registrasi dengan CAPTCHA (Turnstile)
2. ✅ Validasi Email (block disposable)
3. ✅ Rate Limiting (10 req/menit)
4. ✅ Login & Redirect Logic
5. ✅ Email Verification Flow
6. ✅ New Device Notification
7. ✅ Suspended Account Handling

---

## 📋 TEST CASES

### TC-001: Registrasi dengan Email Valid
**Status**: ⬜ Belum Test  
**Steps**:
1. Buka `/register`
2. Isi nama: `Test User`
3. Isi email: `testuser@gmail.com`
4. Isi password: `Password123!`
5. Konfirmasi password: `Password123!`
6. Klik "Daftar Sekarang"

**Expected Result**:
- [ ] Redirect ke `/login`
- [ ] Flash message: "Registrasi berhasil! Silakan cek email..."
- [ ] User tercipta di database (email belum terverifikasi)
- [ ] Email verifikasi terkirim (cek log atau mailtrap)

**Actual Result**:  
_________________________________

---

### TC-002: Registrasi dengan Disposable Email (HARUS GAGAL)
**Status**: ⬜ Belum Test  
**Steps**:
1. Buka `/register`
2. Isi nama: `Test User`
3. Isi email: `test@tempmail.com`
4. Isi password: `Password123!`
5. Konfirmasi password: `Password123!`
6. Klik "Daftar Sekarang"

**Expected Result**:
- [ ] Tetap di halaman `/register`
- [ ] Error message: "Email menggunakan domain temporary/disposable..."
- [ ] User TIDAK tercipta di database

**Actual Result**:  
_________________________________

**Test dengan domain lain**:
- [ ] test@10minutemail.com → HARUS GAGAL
- [ ] test@guerrillamail.com → HARUS GAGAL
- [ ] test@yopmail.com → HARUS GAGAL
- [ ] test@mailinator.com → HARUS GAGAL

---

### TC-003: Registrasi - Rate Limiting (HARUS GAGAL setelah 10x)
**Status**: ⬜ Belum Test  
**Steps**:
1. Buka `/register`
2. Coba daftar 11x dalam 1 menit dengan email berbeda:
   - test1@gmail.com
   - test2@gmail.com
   - ... sampai test11@gmail.com

**Expected Result**:
- [ ] Request 1-10: Sukses (redirect ke login)
- [ ] Request 11: Gagal dengan HTTP 429
- [ ] Tampil halaman error "Terlalu Banyak Request"
- [ ] Countdown timer muncul

**Actual Result**:  
_________________________________

---

### TC-004: CAPTCHA - Submit Tanpa CAPTCHA (HARUS GAGAL)
**Status**: ⬜ Belum Test  
**Precondition**: `CAPTCHA_ENABLED=true` di .env

**Steps**:
1. Buka `/register`
2. Isi semua field valid
3. Hapus field CAPTCHA (inspect element) atau submit tanpa tunggu CAPTCHA load
4. Klik "Daftar Sekarang"

**Expected Result**:
- [ ] Error: "Verifikasi CAPTCHA gagal"
- [ ] User tidak dibuat

**Actual Result**:  
_________________________________

---

### TC-005: Login Sukses dengan Subscription Aktif
**Status**: ⬜ Belum Test  
**Precondition**: User sudah verified, punya subscription active

**Steps**:
1. Buka `/login`
2. Isi email & password valid
3. Klik "Masuk"

**Expected Result**:
- [ ] Redirect ke `/dashboard`
- [ ] Session regenerate (cek cookie berubah)
- [ ] Activity log tercatat (cek database tabel activity_logs)

**Actual Result**:  
_________________________________

---

### TC-006: Login - User Suspended (HARUS GAGAL)
**Status**: ⬜ Belum Test  
**Precondition**: User exists dengan `is_suspended=true`

**Steps**:
1. Buka `/login`
2. Isi email user yang suspended
3. Isi password yang benar
4. Klik "Masuk"

**Expected Result**:
- [ ] Redirect ke `/suspended`
- [ ] Flash message: "Akun Anda telah ditangguhkan"
- [ ] Session di-invalidate (tidak ada session)
- [ ] User ter-logout

**Actual Result**:  
_________________________________

---

### TC-007: Login - Rate Limiting (10x Gagal)
**Status**: ⬜ Belum Test  
**Steps**:
1. Buka `/login`
2. Coba login dengan password SALAH sebanyak 11x

**Expected Result**:
- [ ] Attempt 1-5: Error "Email atau password salah"
- [ ] Attempt 6-10: Error + throttling (delay semakin lama)
- [ ] Attempt 11: HTTP 429 "Too Many Requests"

**Actual Result**:  
_________________________________

---

### TC-008: Email Verification Flow
**Status**: ⬜ Belum Test  
**Precondition**: User baru register, belum verified

**Steps**:
1. Register user baru
2. Cek email (atau log di `storage/logs/laravel.log`)
3. Copy link verifikasi dari email
4. Buka link verifikasi

**Expected Result**:
- [ ] Email verified di database (`email_verified_at` terisi)
- [ ] User di-logout otomatis
- [ ] Redirect ke `/login`
- [ ] Flash message: "Email berhasil diverifikasi! Silakan login..."

**Actual Result**:  
_________________________________

---

### TC-009: New Device Login Notification
**Status**: ⬜ Belum Test  
**Precondition**: User sudah pernah login sebelumnya

**Steps**:
1. Login dari Browser Chrome (device 1)
2. Logout
3. Login dari Browser Firefox (device 2) atau mode incognito
4. Cek email user

**Expected Result**:
- [ ] Email terkirim dengan subject "🔐 Login Baru Terdeteksi..."
- [ ] Email berisi: IP Address, Browser (Firefox), OS, Lokasi
- [ ] Email berisi warning jika bukan user yang login

**Actual Result**:  
_________________________________

---

### TC-010: Redirect Logic Setelah Login
**Status**: ⬜ Belum Test  

**Test Case A - User tanpa subscription**:
- [ ] Login → Redirect ke `/pricing` dengan info "Silakan pilih paket"

**Test Case B - Subscription pending**:
- [ ] Login → Redirect ke `/subscription/pending`

**Test Case C - Subscription expired**:
- [ ] Login → Redirect ke `/pricing` dengan warning "Langganan tidak aktif"

**Test Case D - Subscription active**:
- [ ] Login → Redirect ke `/dashboard`

**Actual Result**:  
_________________________________

---

## 🔧 CARA TESTING

### Jalankan Test Otomatis
```bash
# Semua test auth
php artisan test tests/Feature/Auth/

# Test spesifik
php artisan test tests/Feature/Auth/RegistrationSecurityTest.php

# Test dengan filter
php artisan test --filter=rejects_disposable_email
php artisan test --filter=rate_limiting
```

### Test Manual di Browser
```bash
# Start server
php artisan serve

# Buka browser
http://localhost:8000/register
http://localhost:8000/login
```

### Cek Database
```sql
-- Cek user terdaftar
SELECT id, name, email, email_verified_at, is_suspended, created_at FROM users ORDER BY id DESC LIMIT 5;

-- Cek activity logs
SELECT * FROM activity_logs WHERE action LIKE '%login%' ORDER BY created_at DESC LIMIT 10;

-- Cek rate limiting (jika pakai database cache)
SELECT * FROM cache WHERE `key` LIKE '%rate_limit%';
```

---

## 📝 LOG TESTING

| TC ID | Tester | Tanggal | Hasil | Catatan |
|-------|--------|---------|-------|---------|
| TC-001 | | | ⬜ PASS / ⬜ FAIL | |
| TC-002 | | | ⬜ PASS / ⬜ FAIL | |
| TC-003 | | | ⬜ PASS / ⬜ FAIL | |
| TC-004 | | | ⬜ PASS / ⬜ FAIL | |
| TC-005 | | | ⬜ PASS / ⬜ FAIL | |
| TC-006 | | | ⬜ PASS / ⬜ FAIL | |
| TC-007 | | | ⬜ PASS / ⬜ FAIL | |
| TC-008 | | | ⬜ PASS / ⬜ FAIL | |
| TC-009 | | | ⬜ PASS / ⬜ FAIL | |
| TC-010 | | | ⬜ PASS / ⬜ FAIL | |

---

## 🐛 BUG REPORT TEMPLATE

Jika menemukan bug, isi:

**TC ID**: [Nomor test case]  
**Deskripsi Bug**:  
**Steps to Reproduce**:  
**Expected**:  
**Actual**:  
**Screenshot**:  
**Log Error** (dari `storage/logs/laravel.log`):  

---

## ✅ SIGN OFF

**Tester**: _______________________  
**Tanggal**: _______________________  
**Hasil Akhir**: 
- [ ] SEMUA TEST PASS
- [ ] ADA BUG (catatan di bawah)

**Catatan Bug**:  
_________________________________  
_________________________________

**Disetujui oleh**: _______________________  
**Tanggal Sign Off**: _______________________

---

*Selamat Testing! 🚀*
