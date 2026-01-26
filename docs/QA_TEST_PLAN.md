# 📋 Rencana Pengujian QA
## Sistem Feature Gating - ReplyAI

---

## 🌐 Lingkungan Pengujian

| Parameter | Nilai |
|-----------|-------|
| URL Aplikasi | `http://127.0.0.1:8000` |
| URL Admin Panel | `http://127.0.0.1:8000/admin` |
| Tanggal Pengujian | _____________ |
| QA Tester | _____________ |

---

## 👤 Akun Pengujian

Buat akun-akun berikut sebelum memulai pengujian:

| No | Peran | Email | Paket | Status VIP |
|----|-------|-------|-------|------------|
| 1 | User Gratis | free@test.com | Gratis | Tidak |
| 2 | User Pro | pro@test.com | Pro | Tidak |
| 3 | User VIP | vip@test.com | Bebas | Ya |
| 4 | Super Admin | (login admin) | - | - |

---

## ✅ Daftar Pengujian

### Keterangan Status:
- ⬜ = Belum diuji
- ✅ = Lulus
- ❌ = Gagal

---

## 📌 TC-01: Sidebar Menu Dinamis

**Tujuan:** Memastikan sidebar menampilkan menu sesuai paket user

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|----|----------|-------------------|----------------------|--------|
| 1.1 | User gratis melihat badge PRO | Login sebagai user gratis → Lihat sidebar | Menu Broadcast, Sequences, Web Widget tampil dengan badge **PRO** | ⬜ |
| 1.2 | User Pro melihat menu lengkap | Login sebagai user Pro → Lihat sidebar | Semua menu dapat diakses tanpa badge PRO | ⬜ |
| 1.3 | User VIP melihat badge VIP | Login sebagai user VIP → Lihat sidebar | Badge VIP tampil + semua menu dapat diakses | ⬜ |
| 1.4 | Info paket aktif ditampilkan | Login sebagai user berbayar → Lihat sidebar | Nama paket tampil di bagian bawah sidebar | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 📌 TC-02: Alur Halaman Upgrade

**Tujuan:** Memastikan user diarahkan ke halaman upgrade dengan benar

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|----|----------|-------------------|----------------------|--------|
| 2.1 | Klik menu PRO diarahkan ke upgrade | User gratis klik menu Broadcast (PRO) | Diarahkan ke `/upgrade?feature=broadcasts` | ⬜ |
| 2.2 | Halaman upgrade menampilkan info fitur | Buka `/upgrade?feature=broadcasts` | Tampil nama fitur + manfaat + tombol CTA | ⬜ |
| 2.3 | Halaman upgrade menampilkan paket saat ini | Buka `/upgrade` (sudah login) | Tampil informasi paket user saat ini | ⬜ |
| 2.4 | Tombol CTA mengarah ke pricing | Klik "Lihat Paket Tersedia" | Diarahkan ke halaman `/pricing` | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 📌 TC-03: Middleware Cek Akses Fitur

**Tujuan:** Memastikan middleware memblokir akses fitur yang tidak dimiliki

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|----|----------|-------------------|----------------------|--------|
| 3.1 | User gratis diblokir dari Broadcast | User gratis akses `/whatsapp/broadcast` | Diarahkan ke halaman `/upgrade` | ⬜ |
| 3.2 | User Pro bisa akses Broadcast | User Pro akses `/whatsapp/broadcast` | Halaman tampil dengan sukses | ⬜ |
| 3.3 | User VIP bypass semua cek | User VIP akses fitur apapun | Semua halaman dapat diakses | ⬜ |
| 3.4 | Request AJAX mengembalikan 403 | User gratis AJAX ke `/whatsapp/broadcast` | Response JSON 403 dengan `upgrade_url` | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 📌 TC-04: Helper Method di User Model

**Tujuan:** Memastikan method helper berfungsi dengan benar

Jalankan perintah berikut di `php artisan tinker`:

| No | Skenario | Perintah | Hasil yang Diharapkan | Status |
|----|----------|----------|----------------------|--------|
| 4.1 | hasFeature untuk user gratis | `User::find(ID)->hasFeature('broadcasts')` | `false` | ⬜ |
| 4.2 | hasFeature untuk user Pro | `User::find(ID)->hasFeature('broadcasts')` | `true` | ⬜ |
| 4.3 | hasFeature untuk user VIP | `User::find(ID)->hasFeature('apapun')` | `true` (selalu) | ⬜ |
| 4.4 | getFeatureLimit | `User::find(ID)->getFeatureLimit('ai_messages')` | Sesuai limit paket | ⬜ |
| 4.5 | VIP mendapat unlimited | User VIP panggil `getFeatureLimit()` | `null` (unlimited) | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 📌 TC-05: Admin - Manajemen User

**Tujuan:** Memastikan SuperAdmin bisa mengelola user dengan benar

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|----|----------|-------------------|----------------------|--------|
| 5.1 | Akses daftar user | Login admin → Buka `/admin/users` | Tampil tabel user dengan pencarian | ⬜ |
| 5.2 | Cari user | Ketik email di kolom pencarian → Submit | User terfilter sesuai pencarian | ⬜ |
| 5.3 | Filter berdasarkan paket | Pilih filter paket → Submit | Tampil hanya user dengan paket tersebut | ⬜ |
| 5.4 | Filter hanya VIP | Pilih filter VIP | Tampil hanya user VIP | ⬜ |
| 5.5 | Lihat detail user | Klik user → Lihat detail | Tampil info user + subscription | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 📌 TC-06: Admin - Toggle Status VIP

**Tujuan:** Memastikan admin bisa mengubah status VIP user

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|----|----------|-------------------|----------------------|--------|
| 6.1 | Jadikan user sebagai VIP | Admin klik tombol bintang pada user non-VIP | User menjadi VIP, pesan sukses tampil | ⬜ |
| 6.2 | Cabut status VIP | Admin klik tombol bintang pada user VIP | Status VIP dicabut, pesan tampil | ⬜ |
| 6.3 | Toggle VIP dari halaman detail | Buka detail user → Klik Toggle VIP | Status berubah + pesan sukses | ⬜ |
| 6.4 | User VIP bisa akses semua | Setelah toggle, login sebagai user | Semua fitur PRO dapat diakses | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 📌 TC-07: Admin - Assign Subscription Manual

**Tujuan:** Memastikan admin bisa memberikan subscription manual

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|----|----------|-------------------|----------------------|--------|
| 7.1 | Assign paket ke user | Pilih paket + durasi → Submit | Subscription dibuat, pesan sukses | ⬜ |
| 7.2 | Cek paket yang di-assign | Login sebagai user yang di-assign | Bisa akses fitur sesuai paket | ⬜ |
| 7.3 | Override subscription lama | Assign paket baru ke user berlangganan | Subscription lama dibatalkan, baru aktif | ⬜ |
| 7.4 | Perhitungan durasi | Assign subscription 12 bulan | `expires_at` = sekarang + 12 bulan | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 📌 TC-08: Landing Page - Harga Dinamis

**Tujuan:** Memastikan landing page menampilkan harga dari database

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|----|----------|-------------------|----------------------|--------|
| 8.1 | Harga dinamis dimuat | Buka `/landingpage/index.html` | Paket dimuat dari API | ⬜ |
| 8.2 | Fitur tampil dengan benar | Periksa kartu paket | Fitur tampil dengan tanda centang | ⬜ |
| 8.3 | Klik tombol paket | Klik "Mulai Pro" | Diarahkan ke `/pricing?plan=pro` | ⬜ |
| 8.4 | User login langsung ke checkout | Klik paket (sudah login) | Diarahkan ke `/checkout/pro` | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 📌 TC-09: Alur Checkout

**Tujuan:** Memastikan alur pembelian berjalan dengan benar

| No | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|----|----------|-------------------|----------------------|--------|
| 9.1 | Guest klik paket | Klik paket di landing → `/pricing` | Tampil halaman dengan prompt register | ⬜ |
| 9.2 | Register dengan paket dipilih | Paket tersimpan di session | Setelah register, bisa checkout | ⬜ |
| 9.3 | Halaman pricing tampil | Buka `/pricing` | Semua paket aktif ditampilkan | ⬜ |
| 9.4 | Halaman checkout tampil | Buka `/checkout/pro` | Tampil detail paket + pembayaran | ⬜ |

**Catatan Pengujian:**
```
_______________________________________________________
_______________________________________________________
```

---

## 🐛 Template Laporan Bug

Gunakan format berikut untuk melaporkan bug:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ID Bug         : BUG-XXX
Tingkat        : [ ] Kritis  [ ] Tinggi  [ ] Sedang  [ ] Rendah
Test Case      : TC-XX.X
Tanggal        : ___/___/______

Langkah Reproduksi:
1. 
2. 
3. 

Hasil yang Diharapkan:
_______________________________________________________

Hasil Aktual:
_______________________________________________________

Screenshot: (lampirkan jika ada)

Akun yang Digunakan:
Browser:

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## ✔️ Checklist Sebelum Pengujian

- [ ] Semua akun pengujian sudah dibuat
- [ ] Data paket sudah di-seed ke database
- [ ] Server lokal sudah berjalan
- [ ] Panel admin bisa diakses
- [ ] Browser developer tools siap

---

## 📊 Ringkasan Hasil

| Kategori | Total | Lulus | Gagal |
|----------|-------|-------|-------|
| TC-01: Sidebar | 4 | __ | __ |
| TC-02: Upgrade | 4 | __ | __ |
| TC-03: Middleware | 4 | __ | __ |
| TC-04: Helper | 5 | __ | __ |
| TC-05: Admin User | 5 | __ | __ |
| TC-06: Toggle VIP | 4 | __ | __ |
| TC-07: Assign Sub | 4 | __ | __ |
| TC-08: Landing | 4 | __ | __ |
| TC-09: Checkout | 4 | __ | __ |
| **TOTAL** | **38** | **__** | **__** |

---

**Tanggal Selesai:** _______________  
**Tanda Tangan QA:** _______________
