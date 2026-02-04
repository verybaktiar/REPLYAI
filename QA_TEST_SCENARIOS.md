# Skenario Uji Coba (QA Test Scenarios) - REPLYAI

Dokumen ini dirancang untuk Quality Assurance (QA) profesional guna memverifikasi fungsionalitas sistem REPLYAI secara menyeluruh, termasuk fitur yang sudah ada, fitur baru (SaaS Pro), dan fitur CRM yang baru saja diimplementasikan.

**Tanggal Dokumen:** 2026-02-04
**Lingkungan:** Development (Localhost)
**Status Update:** CRM UI & Backend Implemented, Auto-reconnect Fixed.

---

## 🛠️ Persiapan Lingkungan (Setup)

Sebelum memulai pengujian, pastikan lingkungan pengembangan berjalan dengan benar.

**Langkah:**
1.  Buka terminal/command prompt.
2.  Jalankan perintah otomatis:
    ```bash
    ./start-dev.bat
    ```
3.  Pastikan **4 jendela terminal** terbuka dan tidak ada yang error:
    -   **Laravel Server** (`php artisan serve`)
    -   **Queue Worker** (`php artisan queue:work`) -> *Kritikal untuk pengiriman pesan.*
    -   **Reverb Server** (`php artisan reverb:start`) -> *Kritikal untuk real-time chat.*
    -   **Frontend Build** (`npm run dev`)

---

## 🧪 Skenario Pengujian

### 1. Authentication & Onboarding
**Tujuan:** Memastikan user baru bisa mendaftar dan masuk ke dashboard.

| ID | Skenario | Langkah Pengujian | Ekspektasi (Expected Result) |
|----|----------|-------------------|------------------------------|
| A01 | Register Akun Baru | 1. Buka `/register`.<br>2. Isi form lengkap.<br>3. Submit. | User berhasil login dan diarahkan ke Dashboard atau Onboarding Wizard. |
| A02 | Login | 1. Buka `/login`.<br>2. Masukkan kredensial valid. | Masuk ke Dashboard (`/dashboard`). |
| A03 | Session Timeout | 1. Login.<br>2. Biarkan idle selama 120 menit (Simulasi). | Sesi berakhir dan user diminta login kembali. |

### 2. WhatsApp Connection (Device Management)
**Tujuan:** Memastikan koneksi antara REPLYAI dan WhatsApp Server (Node.js/Baileys) stabil.

| ID | Skenario | Langkah Pengujian | Ekspektasi (Expected Result) |
|----|----------|-------------------|------------------------------|
| W01 | Tambah Device Baru | 1. Buka menu **WhatsApp > Settings**.<br>2. Klik "Add Device".<br>3. Masukkan nama device. | QR Code muncul dalam < 5 detik. Status "Scanning". |
| W02 | Scan QR Code | 1. Scan QR dengan HP (Linked Devices). | Status berubah menjadi "Connected". Profil WA muncul. |
| W03 | Reconnect (Manual) | 1. Putuskan koneksi internet HP sebentar.<br>2. Klik tombol "Reconnect" di dashboard. | Sistem mencoba menyambung ulang dan status kembali "Connected". |
| W04 | **Auto Reconnect (Stabil)** | 1. Matikan service Node.js atau putuskan koneksi berkali-kali. | **FIXED:** Sistem sekarang mencoba reconnect tanpa batas (`Infinity`) sampai tersambung kembali. |

### 3. Messaging (Queue & Real-time) - **FITUR BARU**
**Tujuan:** Menguji fitur antrian pesan dan update real-time yang baru diimplementasikan.

| ID | Skenario | Langkah Pengujian | Ekspektasi (Expected Result) |
|----|----------|-------------------|------------------------------|
| M01 | Kirim Pesan (Text) | 1. Buka **WhatsApp > Inbox**.<br>2. Pilih chat.<br>3. Ketik pesan & Kirim. | Pesan tidak langsung terkirim (pending), masuk antrian, lalu terkirim setelah 1-4 detik (Human delay). |
| M02 | Kirim Pesan (Media) | 1. Upload gambar/dokumen di chat.<br>2. Kirim. | File terupload dan terkirim via antrian. |
| M03 | Terima Pesan (Real-time) | 1. Buka Inbox di browser.<br>2. Kirim pesan DARI HP pelanggan ke nomor Bot. | Pesan muncul di Inbox **seketika** tanpa refresh halaman. |
| M04 | Queue Processing | 1. Perhatikan terminal "Queue Worker" saat mengirim pesan. | Muncul log `Processing: App\Jobs\SendMessageJob` dan `Processed`. |

### 4. CRM (Notes & Tags) - **FITUR TERBARU**
**Tujuan:** Memverifikasi fungsionalitas Manajemen Hubungan Pelanggan (CRM) di Inbox.

| ID | Skenario | Langkah Pengujian | Ekspektasi (Expected Result) |
|----|----------|-------------------|------------------------------|
| C01 | **Buka Detail Panel** | 1. Buka Inbox.<br>2. Klik ikon "Detail Kontak" (Dock Left) di header chat. | Panel kanan terbuka dengan animasi slide-in. Menampilkan tab "Catatan" dan "Label". |
| C02 | **Tambah Catatan** | 1. Pilih tab "Catatan".<br>2. Ketik catatan internal.<br>3. Klik "Simpan". | Catatan muncul di list bawahnya dengan nama penulis dan waktu "Just now". |
| C03 | **Lihat Label (Tags)** | 1. Pilih tab "Label". | Menampilkan daftar label yang sudah ditempel ke kontak ini (jika ada). |
| C04 | **Pasang Label** | 1. Di tab "Label", lihat daftar "Tambah Label".<br>2. Klik salah satu label. | Label berpindah ke bagian "Active Tags" di atas. |
| C05 | **Lepas Label** | 1. Klik ikon "X" pada label di bagian "Active Tags". | Label terhapus dari kontak dan kembali ke daftar pilihan di bawah. |

### 5. Auto Reply Rules
**Tujuan:** Memastikan logika balasan otomatis berjalan.

| ID | Skenario | Langkah Pengujian | Ekspektasi (Expected Result) |
|----|----------|-------------------|------------------------------|
| R01 | Buat Rule Baru | 1. Menu **Rules** > Add New.<br>2. Keyword: "hallo", Reply: "Hai, ada yang bisa dibantu?". | Rule tersimpan di database. |
| R02 | Tes Rule | 1. Kirim pesan "hallo" dari HP pelanggan. | Bot membalas otomatis "Hai, ada yang bisa dibantu?" dalam beberapa detik. |

### 6. Knowledge Base (KB)
**Tujuan:** Memastikan artikel KB bisa dibuat untuk AI Context.

| ID | Skenario | Langkah Pengujian | Ekspektasi (Expected Result) |
|----|----------|-------------------|------------------------------|
| K01 | Tambah Artikel | 1. Menu **Knowledge Base** > Add Article.<br>2. Isi Judul & Konten. | Artikel tersimpan dan status "Active". |

---

## ⚠️ Known Issues (Isu yang Sudah Diketahui)

Daftar ini adalah fitur yang mungkin memerlukan perhatian di masa depan:

1.  **Instagram Inbox**: Fitur ini ada di menu, tapi belum diintegrasikan penuh dengan sistem Queue baru seperti WhatsApp.
    *   *Status:* Perlu pengujian terpisah dan upgrade kode.

---

## ✅ Status Pengembangan

1.  **CRM UI (Notes & Tags)**: ✅ **SELESAI**. Sudah ada di Inbox dengan panel samping.
2.  **WhatsApp Stability**: ✅ **SELESAI**. Config `maxReconnectAttempts` sudah diubah ke `Infinity`.
3.  **Queue & Real-time**: ✅ **SELESAI**. Berjalan normal.

Sistem siap untuk pengujian QA menyeluruh (User Acceptance Testing).
