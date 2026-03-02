# 📋 QA MASTER TESTING - REPLYAI (BAHASA INDONESIA)
## 387 Test Cases Lengkap - Semua Fitur

---

# 📱 BAGIAN 1: PORTAL PENGGUNA (98 Test Cases)

## MODUL 1.1: AUTENTIKASI (18 Test)

| No | Kode Test | Skenario | Hasil Diharapkan | Status |
|----|-----------|----------|------------------|--------|
| 1 | USR-AUTH-01 | Register dengan data valid | Akun dibuat, email verifikasi terkirim | ☐ |
| 2 | USR-AUTH-02 | Register dengan email sudah terdaftar | Error: Email sudah digunakan | ☐ |
| 3 | USR-AUTH-03 | Register password kurang 8 karakter | Error: Password minimal 8 karakter | ☐ |
| 4 | USR-AUTH-04 | Register format email tidak valid | Error: Format email tidak valid | ☐ |
| 5 | USR-AUTH-05 | Register nama kosong | Error: Nama wajib diisi | ☐ |
| 6 | USR-AUTH-06 | Klik link verifikasi email | Email terverifikasi, redirect login | ☐ |
| 7 | USR-AUTH-07 | Link verifikasi expired | Error: Link sudah tidak valid | ☐ |
| 8 | USR-AUTH-08 | Login credential valid | Berhasil login, masuk dashboard | ☐ |
| 9 | USR-AUTH-09 | Login password salah | Error: Password tidak cocok | ☐ |
| 10 | USR-AUTH-10 | Login email belum verifikasi | Error: Email belum diverifikasi | ☐ |
| 11 | USR-AUTH-11 | Login akun di-suspend | Error: Akun ditangguhkan | ☐ |
| 12 | USR-AUTH-12 | Forgot password request | Email reset password terkirim | ☐ |
| 13 | USR-AUTH-13 | Reset password link valid | Password berhasil diubah | ☐ |
| 14 | USR-AUTH-14 | Setup 2FA Google Authenticator | QR code muncul, 2FA aktif | ☐ |
| 15 | USR-AUTH-15 | Login 2FA kode valid | Login berhasil | ☐ |
| 16 | USR-AUTH-16 | Login 2FA kode salah | Error: Kode tidak valid | ☐ |
| 17 | USR-AUTH-17 | Logout dari dashboard | Session berakhir, redirect login | ☐ |
| 18 | USR-AUTH-18 | Akses dashboard tanpa login | Redirect ke halaman login | ☐ |

## MODUL 1.2: DASHBOARD (12 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 19 | USR-DASH-01 | Akses dashboard setelah login | Dashboard muncul dengan statistik | ☐ |
| 20 | USR-DASH-02 | Lihat total pesan hari ini | Angka total pesan terupdate | ☐ |
| 21 | USR-DASH-03 | Lihat jumlah kontak | Jumlah kontak ditampilkan | ☐ |
| 22 | USR-DASH-04 | Lihat status perangkat WA | Status Connected/Disconnected | ☐ |
| 23 | USR-DASH-05 | Lihat status akun Instagram | Status Active/Expired | ☐ |
| 24 | USR-DASH-06 | Lihat penggunaan kuota pesan | Progress bar kuota muncul | ☐ |
| 25 | USR-DASH-07 | Lihat info langganan aktif | Plan dan tanggal expired | ☐ |
| 26 | USR-DASH-08 | Klik tombol "Pesan Baru" | Redirect ke compose | ☐ |
| 27 | USR-DASH-09 | Klik "Hubungkan WhatsApp" | Redirect ke WA connection | ☐ |
| 28 | USR-DASH-10 | Klik icon notifikasi bell | Dropdown notifikasi muncul | ☐ |
| 29 | USR-DASH-11 | Klik conversation terbaru | Redirect ke inbox chat | ☐ |
| 30 | USR-DASH-12 | Refresh dashboard F5 | Data terupdate tanpa error | ☐ |

## MODUL 1.3: INBOX & CHAT (24 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 31 | USR-INBOX-01 | Akses halaman inbox | Daftar conversation muncul | ☐ |
| 32 | USR-INBOX-02 | Pilih conversation dari list | Chat terbuka dengan history | ☐ |
| 33 | USR-INBOX-03 | Kirim pesan teks baru | Pesan terkirim, status Sent | ☐ |
| 34 | USR-INBOX-04 | Kirim pesan dengan gambar | Gambar terupload dan terkirim | ☐ |
| 35 | USR-INBOX-05 | Kirim pesan dengan PDF | Dokumen terupload, bisa download | ☐ |
| 36 | USR-INBOX-06 | Gunakan Quick Reply template | Template terinsert ke input | ☐ |
| 37 | USR-INBOX-07 | Tambah internal note | Catatan tersimpan, internal only | ☐ |
| 38 | USR-INBOX-08 | Tandai conversation Starred | Icon star berubah, masuk starred | ☐ |
| 39 | USR-INBOX-09 | Arsipkan conversation | Pindah ke tab Archived | ☐ |
| 40 | USR-INBOX-10 | Hapus conversation | Confirmation, conversation terhapus | ☐ |
| 41 | USR-INBOX-11 | Search conversation by name | Hasil sesuai keyword | ☐ |
| 42 | USR-INBOX-12 | Filter by status unread/starred | List terfilter | ☐ |
| 43 | USR-INBOX-13 | Terima pesan WA masuk | Pesan muncul real-time | ☐ |
| 44 | USR-INBOX-14 | Terima pesan Instagram DM | Pesan muncul dengan label IG | ☐ |
| 45 | USR-INBOX-15 | Lihat status pesan Sent/Delivered/Read | Status terupdate | ☐ |
| 46 | USR-INBOX-16 | Mention user di internal note | User di-mention dapat notif | ☐ |
| 47 | USR-INBOX-17 | Assign chat ke team member | Chat pindah ke member | ☐ |
| 48 | USR-INBOX-18 | Gunakan AI Auto-Reply | AI generate response | ☐ |
| 49 | USR-INBOX-19 | Set reminder pada chat | Reminder tersimpan, notif muncul | ☐ |
| 50 | USR-INBOX-20 | Export chat history | File CSV/PDF terdownload | ☐ |
| 51 | USR-INBOX-21 | Blokir kontak dari chat | Kontak diblokir | ☐ |
| 52 | USR-INBOX-22 | Lihat profil kontak | Sidebar profil muncul | ☐ |
| 53 | USR-INBOX-23 | Edit info kontak | Data kontak terupdate | ☐ |
| 54 | USR-INBOX-24 | Kirim broadcast message | Pesan terkirim ke multiple contacts | ☐ |

## MODUL 1.4: WHATSAPP DEVICE (15 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 55 | USR-WA-01 | Buka halaman koneksi WhatsApp | Halaman muncul dengan tombol connect | ☐ |
| 56 | USR-WA-02 | Klik "Hubungkan Perangkat Baru" | QR code muncul | ☐ |
| 57 | USR-WA-03 | Scan QR dengan WA mobile | Perangkat terhubung, status Connecting | ☐ |
| 58 | USR-WA-04 | Tunggu koneksi established | Status Connected dengan icon hijau | ☐ |
| 59 | USR-WA-05 | Lihat detail perangkat | Nomor WA, nama, status muncul | ☐ |
| 60 | USR-WA-06 | Putuskan koneksi WhatsApp | Status Disconnected | ☐ |
| 61 | USR-WA-07 | Hapus session WhatsApp | Session terhapus, harus scan ulang | ☐ |
| 62 | USR-WA-08 | Reconnect WhatsApp disconnected | QR code muncul kembali | ☐ |
| 63 | USR-WA-09 | Lihat log koneksi | History koneksi dan error log | ☐ |
| 64 | USR-WA-10 | Ganti nama session | Nama session terupdate | ☐ |
| 65 | USR-WA-11 | Test kirim pesan via WA | Pesan terkirim dan diterima | ☐ |
| 66 | USR-WA-12 | Terima pesan masuk via WA | Pesan muncul real-time | ☐ |
| 67 | USR-WA-13 | Lihat status kesehatan koneksi | Signal strength dan latency | ☐ |
| 68 | USR-WA-14 | Setup auto-reply WA | Auto-reply tersimpan dan aktif | ☐ |
| 69 | USR-WA-15 | Batasi jam operasional WA | Auto-reply di luar jam aktif | ☐ |

## MODUL 1.5: INSTAGRAM ACCOUNT (12 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 70 | USR-IG-01 | Buka pengaturan Instagram | Halaman muncul dengan tombol connect | ☐ |
| 71 | USR-IG-02 | Klik "Hubungkan Instagram" | Redirect ke OAuth | ☐ |
| 72 | USR-IG-03 | Login Instagram dan authorize | Redirect back, akun terhubung | ☐ |
| 73 | USR-IG-04 | Lihat akun Instagram terhubung | Username, status Active muncul | ☐ |
| 74 | USR-IG-05 | Lihat daftar DM Instagram | Pesan DM dengan label IG | ☐ |
| 75 | USR-IG-06 | Balas DM Instagram | Pesan terkirim ke IG DM | ☐ |
| 76 | USR-IG-07 | Terima DM baru dari Instagram | Pesan muncul real-time | ☐ |
| 77 | USR-IG-08 | Lihat komentar Instagram | Komentar post muncul di tab | ☐ |
| 78 | USR-IG-09 | Balas komentar Instagram | Komentar reply terposting | ☐ |
| 79 | USR-IG-10 | Putuskan koneksi Instagram | Status Disconnected | ☐ |
| 80 | USR-IG-11 | Reconnect Instagram expired | OAuth ulang berhasil | ☐ |
| 81 | USR-IG-12 | Setup auto-reply Instagram DM | Auto-reply aktif | ☐ |

## MODUL 1.6: KONTAK & CRM (14 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 82 | USR-CRM-01 | Akses halaman kontak | Daftar kontak dengan pagination | ☐ |
| 83 | USR-CRM-02 | Tambah kontak baru manual | Form muncul, data tersimpan | ☐ |
| 84 | USR-CRM-03 | Import kontak dari CSV | File diupload, kontak terimport | ☐ |
| 85 | USR-CRM-04 | Export kontak ke CSV | File terdownload | ☐ |
| 86 | USR-CRM-05 | Edit data kontak | Perubahan tersimpan | ☐ |
| 87 | USR-CRM-06 | Hapus kontak | Konfirmasi, kontak terhapus | ☐ |
| 88 | USR-CRM-07 | Search kontak by nama/email | Hasil sesuai | ☐ |
| 89 | USR-CRM-08 | Filter kontak by tags | Kontak terfilter | ☐ |
| 90 | USR-CRM-09 | Tambah tag ke kontak | Tag muncul di profil | ☐ |
| 91 | USR-CRM-10 | Lihat history chat kontak | Semua conversation muncul | ☐ |
| 92 | USR-CRM-11 | Lihat detail profil kontak | Info lengkap muncul | ☐ |
| 93 | USR-CRM-12 | Tambah catatan ke kontak | Catatan tersimpan | ☐ |
| 94 | USR-CRM-13 | Merge kontak duplikat | Kontak tergabung | ☐ |
| 95 | USR-CRM-14 | Segmentasi kontak grup | Grup tersimpan, bisa broadcast | ☐ |

---

# 🔧 BAGIAN 2: PANEL ADMIN (156 Test Cases)

## MODUL 2.1: DASHBOARD ADMIN (8 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 96 | ADM-DASH-01 | Login ke panel admin | Dashboard admin muncul | ☐ |
| 97 | ADM-DASH-02 | Lihat statistik pengguna | Total users, active, new today | ☐ |
| 98 | ADM-DASH-03 | Lihat statistik pendapatan | Revenue hari ini, bulan ini | ☐ |
| 99 | ADM-DASH-04 | Lihat grafik pertumbuhan | Chart user & revenue | ☐ |
| 100 | ADM-DASH-05 | Lihat aktivitas terbaru | Activity log recent | ☐ |
| 101 | ADM-DASH-06 | Lihat tiket support pending | Jumlah tiket open | ☐ |
| 102 | ADM-DASH-07 | Lihat pembayaran pending | Jumlah payment menunggu | ☐ |
| 103 | ADM-DASH-08 | Quick action buttons | Tombol shortcut berfungsi | ☐ |

## MODUL 2.2: MANAJEMEN PENGGUNA (28 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 104 | ADM-USER-01 | Lihat daftar pengguna | List user dengan pagination | ☐ |
| 105 | ADM-USER-02 | Search user by name/email | Hasil sesuai keyword | ☐ |
| 106 | ADM-USER-03 | Filter user by status | Aktif/Suspended/Expired | ☐ |
| 107 | ADM-USER-04 | Filter user by plan | Basic/Pro/Enterprise | ☐ |
| 108 | ADM-USER-05 | View detail profil user | Info lengkap user | ☐ |
| 109 | ADM-USER-06 | Edit data user | Perubahan tersimpan | ☐ |
| 110 | ADM-USER-07 | Suspend user account | User tidak bisa login | ☐ |
| 111 | ADM-USER-08 | Unsuspend user account | User bisa login kembali | ☐ |
| 112 | ADM-USER-09 | Delete user permanently | User terhapus dari DB | ☐ |
| 113 | ADM-USER-10 | Impersonate user | Login sebagai user | ☐ |
| 114 | ADM-USER-11 | Reset password user | Email reset terkirim | ☐ |
| 115 | ADM-USER-12 | Verifikasi email user manual | Email terverifikasi | ☐ |
| 116 | ADM-USER-13 | Assign subscription ke user | Plan aktif di user | ☐ |
| 117 | ADM-USER-14 | Extend subscription user | Masa aktif bertambah | ☐ |
| 118 | ADM-USER-15 | Cancel subscription user | Subscription non-aktif | ☐ |
| 119 | ADM-USER-16 | Reset usage quota user | Quota kembali ke 0/full | ☐ |
| 120 | ADM-USER-17 | Lihat activity log user | Semua aktivitas user | ☐ |
| 121 | ADM-USER-18 | Lihat devices yang dikelola user | List WA & IG user | ☐ |
| 122 | ADM-USER-19 | Export data user ke CSV | File terdownload | ☐ |
| 123 | ADM-USER-20 | Bulk suspend multiple users | Users ter-suspend | ☐ |
| 124 | ADM-USER-21 | Bulk delete users | Users terhapus | ☐ |
| 125 | ADM-USER-22 | Lihat pembayaran history user | List payment user | ☐ |
| 126 | ADM-USER-23 | Kirim email ke user | Email terkirim | ☐ |
| 127 | ADM-USER-24 | Lihat tiket support user | List tiket dari user | ☐ |
| 128 | ADM-USER-25 | Lihat statistik usage user | Pesan terkirim, kontak, dll | ☐ |
| 129 | ADM-USER-26 | Force logout user | Session user berakhir | ☐ |
| 130 | ADM-USER-27 | Add note ke user profile | Catatan tersimpan | ☐ |
| 131 | ADM-USER-28 | Change user role | Role terupdate | ☐ |

## MODUL 2.3: PEMBAYARAN & PENDAPATAN (32 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 132 | ADM-PAY-01 | Lihat daftar pembayaran | List payment dengan filter | ☐ |
| 133 | ADM-PAY-02 | Filter payment by status | Pending/Success/Failed | ☐ |
| 134 | ADM-PAY-03 | Filter payment by method | Transfer/QRIS/CC | ☐ |
| 135 | ADM-PAY-04 | Filter payment by date range | Hasil sesuai tanggal | ☐ |
| 136 | ADM-PAY-05 | View detail pembayaran | Info lengkap payment | ☐ |
| 137 | ADM-PAY-06 | Lihat bukti pembayaran user | Gambar bukti terbuka | ☐ |
| 138 | ADM-PAY-07 | Approve pembayaran manual | Status success, subscription aktif | ☐ |
| 139 | ADM-PAY-08 | Reject pembayaran | Status failed, alasan tercatat | ☐ |
| 140 | ADM-PAY-09 | Request bukti ulang ke user | Email request terkirim | ☐ |
| 141 | ADM-PAY-10 | Generate invoice manual | Invoice tergenerate | ☐ |
| 142 | ADM-PAY-11 | Download invoice PDF | File terdownload | ☐ |
| 143 | ADM-PAY-12 | Kirim invoice ke email user | Email terkirim | ☐ |
| 144 | ADM-PAY-13 | Lihat revenue dashboard | Total revenue hari/bulan/tahun | ☐ |
| 145 | ADM-PAY-14 | Lihat grafik revenue | Chart revenue trends | ☐ |
| 146 | ADM-PAY-15 | Lihat revenue by plan | Breakdown per paket | ☐ |
| 147 | ADM-PAY-16 | Lihat refund requests | List refund pending | ☐ |
| 148 | ADM-PAY-17 | Approve refund | Dana dikembalikan, status refunded | ☐ |
| 149 | ADM-PAY-18 | Reject refund | Status rejected, alasan tercatat | ☐ |
| 150 | ADM-PAY-19 | Lihat failed payments | List failed dengan error | ☐ |
| 151 | ADM-PAY-20 | Retry failed payment | Payment diproses ulang | ☐ |
| 152 | ADM-PAY-21 | Export payment data | CSV/Excel terdownload | ☐ |
| 153 | ADM-PAY-22 | Export revenue report | PDF report terdownload | ☐ |
| 154 | ADM-PAY-23 | Lihat tax/VAT report | Pajak terhitung | ☐ |
| 155 | ADM-PAY-24 | Setup payment gateway | Midtras/Fonnte config | ☐ |
| 156 | ADM-PAY-25 | Test payment gateway | Test transaction berhasil | ☐ |
| 157 | ADM-PAY-26 | Lihat recurring payments | List subscription aktif | ☐ |
| 158 | ADM-PAY-27 | Cancel recurring payment | Auto-renewal dimatikan | ☐ |
| 159 | ADM-PAY-28 | Lihat payment logs | History transaksi | ☐ |
| 160 | ADM-PAY-29 | Search payment by invoice | Hasil sesuai nomor invoice | ☐ |
| 161 | ADM-PAY-30 | Bulk approve payments | Multiple payments approved | ☐ |
| 161 | ADM-PAY-31 | Set payment reminder | Reminder aktif | ☐ |
| 162 | ADM-PAY-32 | Lihat payment analytics | Metrik conversion, dll | ☐ |

## MODUL 2.4: TIKET DUKUNGAN (24 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 163 | ADM-TKT-01 | Lihat daftar tiket | List tiket dengan status | ☐ |
| 164 | ADM-TKT-02 | Filter tiket by status | Open/In Progress/Resolved/Closed | ☐ |
| 165 | ADM-TKT-03 | Filter tiket by priority | Urgent/High/Medium/Low | ☐ |
| 166 | ADM-TKT-04 | Filter tiket by category | Billing/Technical/Feature | ☐ |
| 167 | ADM-TKT-05 | View detail tiket | Info lengkap tiket | ☐ |
| 168 | ADM-TKT-06 | Assign tiket ke agent | Tiket masuk ke agent | ☐ |
| 169 | ADM-TKT-07 | Reply tiket | Balasan tersimpan, email ke user | ☐ |
| 170 | ADM-TKT-08 | Internal note pada tiket | Note internal, user tidak lihat | ☐ |
| 171 | ADM-TKT-09 | Change priority tiket | Priority terupdate | ☐ |
| 172 | ADM-TKT-10 | Change status tiket | Status terupdate | ☐ |
| 173 | ADM-TKT-11 | Escalate tiket | Tiket pindah ke level atas | ☐ |
| 174 | ADM-TKT-12 | Merge tiket duplikat | Tiket tergabung | ☐ |
| 175 | ADM-TKT-13 | Split tiket | Satu tiket jadi dua | ☐ |
| 176 | ADM-TKT-14 | Set SLA deadline | Deadline tercatat | ☐ |
| 177 | ADM-TKT-15 | View SLA breach warning | Warning muncul jika melewati SLA | ☐ |
| 178 | ADM-TKT-16 | Close tiket resolved | Status closed | ☐ |
| 179 | ADM-TKT-17 | Reopen closed tiket | Status open kembali | ☐ |
| 180 | ADM-TKT-18 | Delete tiket | Tiket terhapus | ☐ |
| 181 | ADM-TKT-19 | Search tiket by subject/content | Hasil sesuai | ☐ |
| 182 | ADM-TKT-20 | Export tiket data | CSV terdownload | ☐ |
| 183 | ADM-TKT-21 | View agent performance | Statistik agent | ☐ |
| 184 | ADM-TKT-22 | View customer satisfaction | Rating & feedback | ☐ |
| 185 | ADM-TKT-23 | Setup auto-reply tiket | Auto-response aktif | ☐ |
| 186 | ADM-TKT-24 | Bulk assign tiket | Multiple tiket assigned | ☐ |

## MODUL 2.5: PAKET & PROMO (18 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 187 | ADM-PLAN-01 | Lihat daftar paket | List plan aktif/non-aktif | ☐ |
| 188 | ADM-PLAN-02 | Tambah paket baru | Plan tersimpan | ☐ |
| 189 | ADM-PLAN-03 | Edit paket existing | Perubahan tersimpan | ☐ |
| 190 | ADM-PLAN-04 | Non-aktifkan paket | Plan tidak muncul di frontend | ☐ |
| 191 | ADM-PLAN-05 | Hapus paket | Plan terhapus | ☐ |
| 192 | ADM-PLAN-06 | Set harga paket | Harga terupdate | ☐ |
| 193 | ADM-PLAN-07 | Set fitur paket | Limit pesan, kontak, dll | ☐ |
| 194 | ADM-PLAN-08 | Set trial period | Trial aktif | ☐ |
| 195 | ADM-PLAN-09 | Lihat daftar promo code | List promo aktif | ☐ |
| 196 | ADM-PLAN-10 | Buat promo code baru | Promo tersimpan | ☐ |
| 197 | ADM-PLAN-11 | Edit promo code | Perubahan tersimpan | ☐ |
| 198 | ADM-PLAN-12 | Non-aktifkan promo | Promo tidak berlaku | ☐ |
| 199 | ADM-PLAN-13 | Set discount type | Percentage/Fixed amount | ☐ |
| 200 | ADM-PLAN-14 | Set promo expiration | Expired date tercatat | ☐ |
| 201 | ADM-PLAN-15 | Set usage limit | Limit penggunaan promo | ☐ |
| 202 | ADM-PLAN-16 | Lihat promo usage stats | Berapa kali digunakan | ☐ |
| 203 | ADM-PLAN-17 | Bulk generate promo codes | Multiple codes tergenerate | ☐ |
| 204 | ADM-PLAN-18 | Export promo data | CSV terdownload | ☐ |

## MODUL 2.6: LAPORAN & ANALITIK (19 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 205 | ADM-RPT-01 | Lihat user growth report | Chart pertumbuhan user | ☐ |
| 206 | ADM-RPT-02 | Lihat revenue report | Chart pendapatan | ☐ |
| 207 | ADM-RPT-03 | Lihat churn rate report | Persentase churn | ☐ |
| 208 | ADM-RPT-04 | Lihat retention report | Retention rate | ☐ |
| 209 | ADM-RPT-05 | Lihat active users report | DAU/MAU | ☐ |
| 210 | ADM-RPT-06 | Lihat message volume report | Total pesan terkirim | ☐ |
| 211 | ADM-RPT-07 | Lihat device connection report | Status perangkat | ☐ |
| 212 | ADM-RPT-08 | Lihat support ticket report | Stats tiket | ☐ |
| 213 | ADM-RPT-09 | Filter report by date range | Data sesuai range | ☐ |
| 214 | ADM-RPT-10 | Export report to PDF | PDF terdownload | ☐ |
| 215 | ADM-RPT-11 | Export report to Excel | Excel terdownload | ☐ |
| 216 | ADM-RPT-12 | Schedule automated report | Report terkirim email | ☐ |
| 217 | ADM-RPT-13 | Compare period report | Perbandingan periode | ☐ |
| 218 | ADM-RPT-14 | View real-time analytics | Data real-time | ☐ |
| 219 | ADM-RPT-15 | Custom report builder | Report custom tersimpan | ☐ |
| 220 | ADM-RPT-16 | View geographic distribution | Map user location | ☐ |
| 221 | ADM-RPT-17 | View plan distribution | Breakdown per plan | ☐ |
| 222 | ADM-RPT-18 | View feature usage stats | Fitur paling digunakan | ☐ |
| 223 | ADM-RPT-19 | Share report link | Link shareable | ☐ |

## MODUL 2.7: PENGATURAN SISTEM (35 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 224 | ADM-SET-01 | Edit nama aplikasi | Nama terupdate di frontend | ☐ |
| 225 | ADM-SET-02 | Upload logo aplikasi | Logo terupdate | ☐ |
| 226 | ADM-SET-03 | Set favicon | Favicon terupdate | ☐ |
| 227 | ADM-SET-04 | Edit email pengirim | Email default terupdate | ☐ |
| 228 | ADM-SET-05 | Konfigurasi SMTP | Email bisa terkirim | ☐ |
| 229 | ADM-SET-06 | Set timezone default | Waktu sesuai timezone | ☐ |
| 230 | ADM-SET-07 | Set currency default | Mata uang terupdate | ☐ |
| 231 | ADM-SET-08 | Set language default | Bahasa default terupdate | ☐ |
| 232 | ADM-SET-09 | Konfigurasi WhatsApp gateway | WA gateway aktif | ☐ |
| 233 | ADM-SET-10 | Konfigurasi Instagram API | IG API aktif | ☐ |
| 234 | ADM-SET-11 | Set API keys | Keys tersimpan | ☐ |
| 235 | ADM-SET-12 | Konfigurasi payment gateway | Payment bisa diproses | ☐ |
| 236 | ADM-SET-13 | Set tax/VAT rate | Pajak terhitung | ☐ |
| 237 | ADM-SET-14 | Set maintenance mode | Mode maintenance aktif | ☐ |
| 238 | ADM-SET-15 | Set registration enable/disable | Registrasi dibuka/ditutup | ☐ |
| 239 | ADM-SET-16 | Set email verification required | Verifikasi wajib/tidak | ☐ |
| 240 | ADM-SET-17 | Set terms & conditions | T&C terupdate | ☐ |
| 241 | ADM-SET-18 | Set privacy policy | Policy terupdate | ☐ |
| 242 | ADM-SET-19 | Konfigurasi SEO meta | Meta tags terupdate | ☐ |
| 243 | ADM-SET-20 | Set Google Analytics ID | Tracking aktif | ☐ |
| 244 | ADM-SET-21 | Set Facebook Pixel | Pixel aktif | ☐ |
| 245 | ADM-SET-22 | Konfigurasi backup schedule | Backup otomatis jalan | ☐ |
| 246 | ADM-SET-23 | Set retention policy | Data lama terhapus otomatis | ☐ |
| 247 | ADM-SET-24 | Set file upload max size | Limit upload berfungsi | ☐ |
| 248 | ADM-SET-25 | Set allowed file types | Filter file berfungsi | ☐ |
| 249 | ADM-SET-26 | Konfigurasi rate limiting | Rate limit aktif | ☐ |
| 250 | ADM-SET-27 | Set session timeout | Timeout berfungsi | ☐ |
| 251 | ADM-SET-28 | Set password policy | Policy password enforce | ☐ |
| 252 | ADM-SET-29 | Enable/disable 2FA globally | 2FA wajib/tidak | ☐ |
| 253 | ADM-SET-30 | Set IP whitelist | Hanya IP whitelist bisa akses | ☐ |
| 254 | ADM-SET-31 | Set custom CSS/JS | Kode custom tersimpan | ☐ |
| 255 | ADM-SET-32 | Konfigurasi webhook | Webhook terkirim | ☐ |
| 256 | ADM-SET-33 | Set notification preferences | Preferensi notif tersimpan | ☐ |
| 257 | ADM-SET-34 | Set business hours | Jam operasional tersimpan | ☐ |
| 258 | ADM-SET-35 | Reset settings to default | Setting kembali default | ☐ |

---

# 🔐 BAGIAN 3: SUPERADMIN (133 Test Cases)

## MODUL 3.1: MANAJEMEN PERANGKAT (16 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 259 | SUP-DEV-01 | Lihat semua perangkat WA | List semua device | ☐ |
| 260 | SUP-DEV-02 | Lihat semua akun IG | List semua IG account | ☐ |
| 263 | SUP-DEV-03 | Filter device by status | Connected/Disconnected | ☐ |
| 264 | SUP-DEV-04 | Filter device by user | Device milik user tertentu | ☐ |
| 265 | SUP-DEV-05 | View detail device | Info lengkap device | ☐ |
| 266 | SUP-DEV-06 | Force reconnect device | Device reconnect | ☐ |
| 267 | SUP-DEV-07 | Force disconnect device | Device disconnected | ☐ |
| 268 | SUP-DEV-08 | Delete device permanently | Device terhapus | ☐ |
| 269 | SUP-DEV-09 | Cleanup orphaned devices | Device tanpa owner terhapus | ☐ |
| 270 | SUP-DEV-10 | Bulk reconnect devices | Multiple devices reconnect | ☐ |
| 271 | SUP-DEV-11 | Bulk delete devices | Multiple devices deleted | ☐ |
| 272 | SUP-DEV-12 | View device logs | Log koneksi device | ☐ |
| 273 | SUP-DEV-13 | Monitor device health | Health status muncul | ☐ |
| 274 | SUP-DEV-14 | Set device auto-reconnect | Auto-reconnect aktif | ☐ |
| 275 | SUP-DEV-15 | View QR code untuk device | QR code muncul | ☐ |
| 276 | SUP-DEV-16 | Export device data | CSV terdownload | ☐ |

## MODUL 3.2: JADWAL & JOB (14 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 277 | SUP-SCH-01 | Lihat daftar scheduled tasks | List cron jobs | ☐ |
| 278 | SUP-SCH-02 | View queue status | Status queue worker | ☐ |
| 279 | SUP-SCH-03 | View failed jobs | List failed jobs | ☐ |
| 280 | SUP-SCH-04 | Retry failed job | Job dijalankan ulang | ☐ |
| 281 | SUP-SCH-05 | Retry all failed jobs | Semua failed job retry | ☐ |
| 282 | SUP-SCH-06 | Flush failed jobs | Failed jobs dihapus | ☐ |
| 283 | SUP-SCH-07 | Run task manually | Task dijalankan | ☐ |
| 284 | SUP-SCH-08 | View execution history | History eksekusi | ☐ |
| 285 | SUP-SCH-09 | Schedule new task | Task baru tersimpan | ☐ |
| 286 | SUP-SCH-10 | Edit scheduled task | Perubahan tersimpan | ☐ |
| 287 | SUP-SCH-11 | Delete scheduled task | Task terhapus | ☐ |
| 288 | SUP-SCH-12 | Enable/disable task | Task aktif/non-aktif | ☐ |
| 289 | SUP-SCH-13 | View queue statistics | Statistik queue | ☐ |
| 290 | SUP-SCH-14 | Clear queue | Queue dikosongkan | ☐ |

## MODUL 3.3: MONITORING API (18 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 291 | SUP-API-01 | View API usage dashboard | Dashboard API muncul | ☐ |
| 292 | SUP-API-02 | View requests per user | Breakdown per user | ☐ |
| 293 | SUP-API-03 | View requests per endpoint | Breakdown per endpoint | ☐ |
| 294 | SUP-API-04 | View response time stats | Latency stats | ☐ |
| 295 | SUP-API-05 | View error rate stats | Persentase error | ☐ |
| 296 | SUP-API-06 | View rate limit hits | Jumlah rate limit | ☐ |
| 297 | SUP-API-07 | Block user API access | User diblokir akses API | ☐ |
| 298 | SUP-API-08 | Unblock user API access | Blokir dibuka | ☐ |
| 299 | SUP-API-09 | View blocked IPs | List IP yang diblokir | ☐ |
| 300 | SUP-API-10 | Block IP address | IP diblokir | ☐ |
| 301 | SUP-API-11 | Unblock IP address | Blokir IP dibuka | ☐ |
| 302 | SUP-API-12 | View recent requests log | Log request terbaru | ☐ |
| 303 | SUP-API-13 | Filter logs by status code | 200/400/500 | ☐ |
| 304 | SUP-API-14 | Filter logs by method | GET/POST/PUT/DELETE | ☐ |
| 305 | SUP-API-15 | Export API logs | CSV terdownload | ☐ |
| 306 | SUP-API-16 | View API health status | Status API external | ☐ |
| 307 | SUP-API-17 | Test API endpoint | Response muncul | ☐ |
| 308 | SUP-API-18 | Set API rate limits | Limit tersimpan | ☐ |

## MODUL 3.4: WEBHOOK LOGS (12 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 309 | SUP-WEB-01 | View webhook logs | List webhook | ☐ |
| 310 | SUP-WEB-02 | Filter by provider | WA/IG/Midtrans/OpenAI | ☐ |
| 311 | SUP-WEB-03 | Filter by status | Success/Failed/Pending | ☐ |
| 312 | SUP-WEB-04 | View webhook detail | Payload & response | ☐ |
| 313 | SUP-WEB-05 | Retry failed webhook | Webhook dikirim ulang | ☐ |
| 314 | SUP-WEB-06 | Retry all failed webhooks | Bulk retry | ☐ |
| 315 | SUP-WEB-07 | View integration health | Status integrasi | ☐ |
| 316 | SUP-WEB-08 | Test webhook endpoint | Test berhasil | ☐ |
| 317 | SUP-WEB-09 | Configure webhook URL | URL tersimpan | ☐ |
| 318 | SUP-WEB-10 | View webhook delivery stats | Stats pengiriman | ☐ |
| 319 | SUP-WEB-11 | Export webhook logs | CSV terdownload | ☐ |
| 320 | SUP-WEB-12 | Delete old webhook logs | Logs lama terhapus | ☐ |

## MODUL 3.5: USER ANALYTICS (16 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 321 | SUP-ANL-01 | View user activity heatmap | Heatmap aktivitas | ☐ |
| 322 | SUP-ANL-02 | View login patterns | Pola login user | ☐ |
| 323 | SUP-ANL-03 | View feature usage breakdown | Fitur paling digunakan | ☐ |
| 324 | SUP-ANL-04 | View user engagement score | Score engagement | ☐ |
| 325 | SUP-ANL-05 | View top active users | List user paling aktif | ☐ |
| 326 | SUP-ANL-06 | View inactive users | List user tidak aktif | ☐ |
| 327 | SUP-ANL-07 | View user growth by day | Growth chart harian | ☐ |
| 328 | SUP-ANL-08 | View user growth by month | Growth chart bulanan | ☐ |
| 329 | SUP-ANL-09 | View retention cohort analysis | Cohort chart | ☐ |
| 330 | SUP-ANL-10 | View churn prediction | Prediksi churn | ☐ |
| 331 | SUP-ANL-11 | View lifetime value (LTV) | Nilai LTV user | ☐ |
| 332 | SUP-ANL-12 | Export user analytics | CSV/PDF terdownload | ☐ |
| 333 | SUP-ANL-13 | Filter by date range | Data sesuai range | ☐ |
| 334 | SUP-ANL-14 | Compare user segments | Perbandingan segment | ☐ |
| 335 | SUP-ANL-15 | View geographic distribution | Peta distribusi | ☐ |
| 336 | SUP-ANL-16 | View device usage stats | Mobile vs Desktop | ☐ |

## MODUL 3.6: ALERT & MONITORING (22 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 337 | SUP-ALR-01 | View system metrics | CPU, RAM, Disk usage | ☐ |
| 338 | SUP-ALR-02 | View database status | DB connection status | ☐ |
| 339 | SUP-ALR-03 | View cache status | Redis/Memcached status | ☐ |
| 340 | SUP-ALR-04 | View queue status | Queue worker status | ☐ |
| 341 | SUP-ALR-05 | View service status | WA Service, IG Service | ☐ |
| 342 | SUP-ALR-06 | View disk usage | Persentase disk terpakai | ☐ |
| 343 | SUP-ALR-07 | Configure disk usage alert | Alert aktif | ☐ |
| 344 | SUP-ALR-08 | Configure service down alert | Alert aktif | ☐ |
| 349 | SUP-ALR-09 | Configure high error rate alert | Alert aktif | ☐ |
| 350 | SUP-ALR-10 | Configure queue backlog alert | Alert aktif | ☐ |
| 351 | SUP-ALR-11 | Test alert notification | Notif test terkirim | ☐ |
| 352 | SUP-ALR-12 | View alert history | History alert | ☐ |
| 353 | SUP-ALR-13 | Enable/disable alert rule | Rule aktif/non-aktif | ☐ |
| 354 | SUP-ALR-14 | Set alert threshold | Threshold tersimpan | ☐ |
| 355 | SUP-ALR-15 | Configure email notification | Email notif aktif | ☐ |
| 356 | SUP-ALR-16 | Configure Slack notification | Slack notif aktif | ☐ |
| 357 | SUP-ALR-17 | Configure WhatsApp notification | WA notif aktif | ☐ |
| 358 | SUP-ALR-18 | View system health score | Score kesehatan sistem | ☐ |
| 359 | SUP-ALR-19 | View recent errors | Error log terbaru | ☐ |
| 360 | SUP-ALR-20 | Clear error logs | Logs terhapus | ☐ |
| 361 | SUP-ALR-21 | Export system logs | CSV terdownload | ☐ |
| 362 | SUP-ALR-22 | Auto-heal stopped services | Service restart otomatis | ☐ |

## MODUL 3.7: QUERY RUNNER (15 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 363 | SUP-QRY-01 | Akses query runner | Interface muncul | ☐ |
| 364 | SUP-QRY-02 | Execute SELECT query | Hasil query muncul | ☐ |
| 365 | SUP-QRY-03 | Execute SELECT dengan JOIN | Hasil join muncul | ☐ |
| 366 | SUP-QRY-04 | Execute SELECT dengan WHERE | Hasil terfilter | ☐ |
| 367 | SUP-QRY-05 | Execute SELECT dengan ORDER | Hasil terurut | ☐ |
| 368 | SUP-QRY-06 | Execute SELECT dengan LIMIT | Hasil terbatas | ☐ |
| 369 | SUP-QRY-07 | Attempt INSERT query | REJECTED - Hanya SELECT | ☐ |
| 370 | SUP-QRY-08 | Attempt UPDATE query | REJECTED - Hanya SELECT | ☐ |
| 371 | SUP-QRY-09 | Attempt DELETE query | REJECTED - Hanya SELECT | ☐ |
| 372 | SUP-QRY-10 | Attempt DROP query | REJECTED - Hanya SELECT | ☐ |
| 373 | SUP-QRY-11 | View table schema | Struktur tabel muncul | ☐ |
| 374 | SUP-QRY-12 | Use predefined query | Query template berjalan | ☐ |
| 375 | SUP-QRY-13 | Export query results to CSV | CSV terdownload | ☐ |
| 376 | SUP-QRY-14 | View query execution time | Waktu eksekusi muncul | ☐ |
| 377 | SUP-QRY-15 | View all database tables | List tabel muncul | ☐ |

## MODUL 3.8: MESSAGE TEMPLATES (13 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 378 | SUP-TPL-01 | View all templates | List template | ☐ |
| 379 | SUP-TPL-02 | Filter by category | Welcome/Notification/etc | ☐ |
| 380 | SUP-TPL-03 | Create new template | Template tersimpan | ☐ |
| 381 | SUP-TPL-04 | Edit template | Perubahan tersimpan | ☐ |
| 382 | SUP-TPL-05 | Delete template | Template terhapus | ☐ |
| 383 | SUP-TPL-06 | Preview template with variables | Preview muncul | ☐ |
| 384 | SUP-TPL-07 | Test variable detection | Variables terdeteksi | ☐ |
| 385 | SUP-TPL-08 | Enable/disable template | Template aktif/non-aktif | ☐ |
| 386 | SUP-TPL-09 | Clone template | Duplikat template | ☐ |
| 387 | SUP-TPL-10 | Import template | Template terimport | ☐ |
| 388 | SUP-TPL-11 | Export template | Template terexport | ☐ |
| 389 | SUP-TPL-12 | Set template category | Kategori tersimpan | ☐ |
| 390 | SUP-TPL-13 | View template usage stats | Statistik penggunaan | ☐ |

## MODUL 3.9: MAINTENANCE MODE (12 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 391 | SUP-MTN-01 | View maintenance settings | Settings muncul | ☐ |
| 392 | SUP-MTN-02 | Enable maintenance mode | Mode aktif | ☐ |
| 393 | SUP-MTN-03 | Disable maintenance mode | Mode non-aktif | ☐ |
| 394 | SUP-MTN-04 | Set custom maintenance message | Pesan tersimpan | ☐ |
| 395 | SUP-MTN-05 | Set countdown timer | Timer muncul | ☐ |
| 396 | SUP-MTN-06 | Add IP to whitelist | IP bisa akses saat maintenance | ☐ |
| 397 | SUP-MTN-07 | Remove IP from whitelist | IP diblokir | ☐ |
| 398 | SUP-MTN-08 | View current whitelisted IPs | List IP muncul | ☐ |
| 399 | SUP-MTN-09 | Preview maintenance page | Preview muncul | ☐ |
| 400 | SUP-MTN-10 | Schedule maintenance | Maintenance terjadwal | ☐ |
| 401 | SUP-MTN-11 | Cancel scheduled maintenance | Jadwal dibatalkan | ☐ |
| 402 | SUP-MTN-12 | View maintenance history | History muncul | ☐ |

## MODUL 3.10: DATA TRANSFER (15 Test)

| No | Kode | Skenario | Hasil | Status |
|----|------|----------|-------|--------|
| 403 | SUP-DAT-01 | View export/import interface | Interface muncul | ☐ |
| 404 | SUP-DAT-02 | Export users to CSV | CSV terdownload | ☐ |
| 405 | SUP-DAT-03 | Export payments to CSV | CSV terdownload | ☐ |
| 406 | SUP-DAT-04 | Export to Excel format | Excel terdownload | ☐ |
| 407 | SUP-DAT-05 | Filter export by date range | Data sesuai range | ☐ |
| 408 | SUP-DAT-06 | Download import template | Template terdownload | ☐ |
| 409 | SUP-DAT-07 | Import users from CSV | User terimport | ☐ |
| 410 | SUP-DAT-08 | Preview import data | Preview muncul | ☐ |
| 411 | SUP-DAT-09 | Validate import data | Error jika data invalid | ☐ |
| 412 | SUP-DAT-10 | Create database backup | Backup tercreate | ☐ |
| 413 | SUP-DAT-11 | Download backup file | File terdownload | ☐ |
| 414 | SUP-DAT-12 | Restore from backup | Database terestore | ☐ |
| 415 | SUP-DAT-13 | Delete old backup | Backup terhapus | ☐ |
| 416 | SUP-DAT-14 | Schedule auto-backup | Backup otomatis aktif | ☐ |
| 417 | SUP-DAT-15 | View backup history | History muncul | ☐ |

---

# 📊 RINGKASAN TOTAL

| Bagian | Jumlah Test | Progress |
|--------|-------------|----------|
| Portal Pengguna | 98 | ___% |
| Panel Admin | 156 | ___% |
| Superadmin | 133 | ___% |
| **TOTAL** | **387** | ___% |

---

# 🐛 BUG TRACKER

| ID | Modul | Tingkat | Deskripsi | Status |
|----|-------|---------|-----------|--------|
| BUG-001 | | | | Open |

---

# 📝 CATATAN TAMBAHAN

[Tulis catatan, temuan, atau saran perbaikan di sini]

---

**Petunjuk Penggunaan:**
1. Cek (☐) setiap test yang sudah dijalankan
2. Tulis hasil: ✅ Berhasil atau ❌ Gagal
3. Jika gagal, catat di Bug Tracker
4. Update progress di tabel ringkasan
5. Submit dokumen ini setelah selesai
