# 📧 Setup Email untuk ReplyAI

## Masalah: Email Verifikasi Tidak Terkirim

**Penyebab**: Konfigurasi email belum diatur, default menggunakan `log` (hanya tersimpan di file)

---

## 🔧 SOLUSI A: Mailtrap (Recommended untuk Development)

**Keuntungan**:
- ✅ Gratis
- ✅ Email masuk ke dashboard (tidak ke inbox nyata)
- ✅ Aman untuk testing
- ✅ Mudah setup

### Langkah Setup:

1. **Daftar Mailtrap**
   - Buka https://mailtrap.io/
   - Sign up gratis
   - Buat inbox baru

2. **Copy SMTP Credentials**
   
   Contoh:
   ```
   Host:     sandbox.smtp.mailtrap.io
   Port:     2525
   Username: 1234567890abcdef
   Password: 1234567890abcdef
   ```

3. **Update .env**

   Tambahkan di `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_username
   MAIL_PASSWORD=your_mailtrap_password
   MAIL_ENCRYPTION=null
   MAIL_FROM_ADDRESS=noreply@replyai.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```

4. **Clear Cache**
   ```bash
   php artisan config:clear
   ```

5. **Test**
   - Register user baru
   - Cek dashboard Mailtrap
   - Email verifikasi harus muncul di sana

---

## 🔧 SOLUSI B: Gmail SMTP (Untuk Production Simple)

**Perlu**: Gmail App Password (bukan password Gmail biasa)

### Langkah:

1. **Buat App Password**
   - Buka https://myaccount.google.com/apppasswords
   - Generate app password untuk "Mail"
   - Copy password (16 karakter)

2. **Update .env**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=yourgmail@gmail.com
   MAIL_PASSWORD=your_app_password_16chars
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=yourgmail@gmail.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```

3. **Clear Cache**
   ```bash
   php artisan config:clear
   ```

---

## 🔧 SOLUSI C: Cek Email di Log File (Debug)

Jika ingin lihat email tanpa kirim:

```bash
# Lihat log email
tail -f storage/logs/laravel.log | grep -i "verification"
```

Email akan tersimpan di log, bisa dilihat isinya (HTML/text)

---

## 🔧 SOLUSI D: Resend (Modern, Recommended untuk Production)

**Keuntungan**:
- ✅ Free tier: 3000 email/bulan
- ✅ Mudah setup
- ✅ Analytics

### Langkah:

1. **Daftar Resend**
   - https://resend.com/
   - Get API Key

2. **Update .env**
   ```env
   MAIL_MAILER=resend
   RESEND_KEY=re_xxxxxxxxxxxxxxxxxxxxxx
   MAIL_FROM_ADDRESS=onboarding@resend.dev
   MAIL_FROM_NAME="${APP_NAME}"
   ```

3. **Install package**
   ```bash
   composer require resend/resend-laravel
   ```

4. **Clear cache**
   ```bash
   php artisan config:clear
   ```

---

## 🧪 VERIFIKASI EMAIL BEKERJA

Setelah setup, test:

1. **Register user baru**
   ```
   http://localhost:8000/register
   ```

2. **Cek log**
   ```bash
   tail storage/logs/laravel.log
   ```

3. **Cek queue** (jika pakai queue)
   ```bash
   php artisan queue:work
   ```

4. **Cek inbox** (Mailtrap/Gmail)

---

## ⚠️ TROUBLESHOOTING

### "Connection refused" atau timeout
- Cek firewall
- Cek port SMTP
- Cek koneksi internet

### "Authentication failed"
- Username/password salah
- Untuk Gmail: pastikan pakai App Password, bukan password biasa

### Email tidak masuk queue
- Cek `QUEUE_CONNECTION` di .env
- Pastikan queue worker berjalan: `php artisan queue:work`

### Email di log tapi tidak dikirim
- Normal jika `MAIL_MAILER=log`
- Ganti ke `smtp` atau `resend` untuk kirim nyata

---

## ✅ CHECKLIST SETUP

- [ ] Pilih provider email (Mailtrap/Gmail/Resend)
- [ ] Daftar & copy credentials
- [ ] Update `.env` file
- [ ] `php artisan config:clear`
- [ ] Register user baru
- [ ] Verifikasi email terkirim
- [ ] Selesai!

---

**Rekomendasi**:
- **Development**: Pakai Mailtrap
- **Production**: Pakai Resend atau Postmark
