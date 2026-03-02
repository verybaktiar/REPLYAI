# 📧 Setup Gmail SMTP untuk ReplyAI

## ✅ Status: Konfigurasi Ditambahkan

File `.env` sudah diupdate dengan template Gmail SMTP.

---

## 🔧 LANGKAH-LANGKAH

### 1. Edit .env File

Buka file `.env` dan ganti:

```env
# GANTI INI:
MAIL_USERNAME=yourgmail@gmail.com
MAIL_PASSWORD=your_app_password_here
MAIL_FROM_ADDRESS=yourgmail@gmail.com

# MENJADI (contoh):
MAIL_USERNAME=admin.replyai@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop  ← 16 karakter dari App Password
MAIL_FROM_ADDRESS=admin.replyai@gmail.com
```

---

### 2. Cara Buat Gmail App Password

**⚠️ Penting**: Password Gmail biasa tidak akan berfungsi!

#### Langkah:
1. Buka https://myaccount.google.com/apppasswords
2. Login dengan Gmail yang sama
3. Klik "Select app" → pilih **"Mail"**
4. Klik "Select device" → pilih **"Other (Custom name)"**
5. Ketik nama: `ReplyAI`
6. Klik **"Generate"**
7. **Copy** password 16 karakter yang muncul
   - Contoh: `abcd efgh ijkl mnop`
   - Contoh: `xmjh uwnr kqoq pqdm`
8. Paste ke `MAIL_PASSWORD` di `.env`

#### Screenshot Proses:
```
Google Account → Security → 2-Step Verification → App passwords
                                                    ↓
                                            Select app: Mail
                                            Select device: Other
                                            Name: ReplyAI
                                                    ↓
                                            [Generate]
                                                    ↓
                                    Your app password: xxxx xxxx xxxx xxxx
                                    (Copy this!)
```

---

### 3. Clear Cache

```bash
php artisan config:clear
```

---

### 4. Test Email

#### Test via Tinker:
```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

Mail::raw('Test email dari ReplyAI', function (Message $message) {
    $message->to('email-kamu@gmail.com')
            ->subject('Test SMTP Gmail');
});

echo "Email terkirim!";
```

#### Atau test via Register:
1. Buka http://localhost:8000/register
2. Daftar dengan email baru
3. Cek inbox email tersebut
4. Email verifikasi harus masuk!

---

## 🧪 VERIFIKASI

### Cek Config:
```bash
php artisan tinker --execute="echo config('mail.mailers.smtp.host');"
# Output: smtp.gmail.com

php artisan tinker --execute="echo config('mail.mailers.smtp.username');"
# Output: yourgmail@gmail.com
```

### Cek Log jika gagal:
```bash
tail -f storage/logs/laravel.log | grep -i "mail\|smtp\|error"
```

---

## ⚠️ TROUBLESHOOTING

### Error: "Username and Password not accepted"
**Solusi**:
- Pastikan pakai **App Password**, bukan password Gmail biasa
- Pastikan 2-Step Verification aktif di Gmail
- Cek tidak ada spasi di awal/akhir password

### Error: "Less secure app access"
**Solusi**: Tidak perlu! App Password sudah bypass ini.

### Error: "Connection timeout"
**Solusi**:
- Cek koneksi internet
- Cek firewall tidak block port 587
- Cek tidak pakai VPN/proxy

### Email tidak masuk inbox
**Cek**:
- Folder Spam/Promotions di Gmail
- Log: `storage/logs/laravel.log`
- Pastikan `MAIL_MAILER=smtp` (bukan `log`)

---

## 📋 CHECKLIST

- [ ] Buat App Password di Google Account
- [ ] Copy password 16 karakter
- [ ] Update `MAIL_USERNAME` di `.env`
- [ ] Update `MAIL_PASSWORD` di `.env` (App Password)
- [ ] Update `MAIL_FROM_ADDRESS` di `.env`
- [ ] Jalankan `php artisan config:clear`
- [ ] Test kirim email
- [ ] Verifikasi email masuk ke inbox

---

## 🎉 CONTOH .env LENGKAP

```env
# ... config lainnya ...

# CAPTCHA
CAPTCHA_ENABLED=true
CAPTCHA_PROVIDER=turnstile
CAPTCHA_SITE_KEY=0x4AAAAAACd0FkljBcwk8WWp
CAPTCHA_SECRET=0x4AAAAAACd0FoGNm1aI9i_MutTGwV9Zh4c

# EMAIL - Gmail SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=admin.replyai@gmail.com      ← GANTI
MAIL_PASSWORD=xmjhuwnrkqoqpqdm              ← GANTI (App Password)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=admin.replyai@gmail.com    ← GANTI
MAIL_FROM_NAME="ReplyAI"

# ... config lainnya ...
```

---

## 💡 TIPS

1. **Jangan share App Password** - Sama sensitifnya dengan password utama
2. **Simpan backup** - App Password hanya ditampilkan sekali saat dibuat
3. **Revoke jika bocor** - Bisa hapus di https://myaccount.google.com/apppasswords
4. **Bisa buat multiple** - Satu Gmail bisa punya banyak App Password untuk apps berbeda

---

**Butuh bantuan?** Cek log error di `storage/logs/laravel.log`
