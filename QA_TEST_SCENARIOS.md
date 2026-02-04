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
| C01 | **Buka Detail Panel (Layout 3 Kolom)** | 1. Buka Inbox.<br>2. Klik salah satu chat.<br>3. Pastikan panel kanan (kolom ke-3) muncul atau klik ikon "Info" jika tertutup. | Layout menjadi 3 kolom: List Chat | Percakapan | CRM Panel. Panel menampilkan info kontak, tabs "Notes", "Tags", "AI Insight". |
| C02 | **Tambah Catatan (Notes)** | 1. Pilih tab "Notes" di panel kanan.<br>2. Ketik catatan di input area.<br>3. Tekan Enter atau klik tombol kirim. | Catatan baru muncul di daftar list di bawahnya secara real-time. |
| C03 | **Kelola Label (Tags)** | 1. Pilih tab "Tags".<br>2. Klik badge label yang tersedia di "Available Tags".<br>3. Klik ikon X pada label di "Active Tags". | Label berpindah antara Active dan Available tanpa reload halaman. |
| C04 | **AI Insight (Generative AI)** | 1. Pilih tab "AI Insight".<br>2. Tunggu proses loading (animasi).<br>3. Cek hasil ringkasan dan saran. | **Ringkasan:** Menampilkan rangkuman percakapan terakhir (10-15 pesan).<br>**Saran:** Menampilkan 3 opsi balasan cepat yang relevan untuk agen.<br>*Note: Membutuhkan API Key Perplexity.* |

### 5. Data Integrity & Duplication - **FIXED**
**Tujuan:** Memastikan tidak ada duplikasi kontak (LID vs Phone Number) dan tools perbaikan berfungsi.

| ID | Skenario | Langkah Pengujian | Ekspektasi (Expected Result) |
|----|----------|-------------------|------------------------------|
| D01 | **Cek Duplikasi (Command)** | 1. Buka terminal.<br>2. Jalankan `php artisan wa:merge-duplicates --dry-run`. | Menampilkan jumlah percakapan LID yang terdeteksi dan kandidat merge. |
| D02 | **Eksekusi Merge** | 1. Jalankan `php artisan wa:merge-duplicates`. | Percakapan LID digabungkan ke percakapan Phone Number utama. History chat LID pindah ke kontak utama. |
| D03 | **Pencegahan Duplikasi Baru** | 1. Kirim pesan dari akun WA baru (simulasi webhook).<br>2. Cek database apakah terbuat double entry. | Sistem otomatis menormalisasi nomor HP dan mencegah pembuatan row duplikat jika kontak sudah ada. |

### 6. Auto Reply Rules
**Tujuan:** Memastikan logika balasan otomatis berjalan.

| ID | Skenario | Langkah Pengujian | Ekspektasi (Expected Result) |
|----|----------|-------------------|------------------------------|
| R01 | Buat Rule Baru | 1. Menu **Rules** > Add New.<br>2. Keyword: "hallo", Reply: "Hai, ada yang bisa dibantu?". | Rule tersimpan di database. |
| R02 | Tes Rule | 1. Kirim pesan "hallo" dari HP pelanggan. | Bot membalas otomatis "Hai, ada yang bisa dibantu?" dalam beberapa detik. |

### 7. Knowledge Base (KB)
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

1.  **CRM UI (Notes & Tags)**: ✅ **SELESAI**. Sudah ada di Inbox dengan panel samping (3-Kolom).
2.  **WhatsApp Stability**: ✅ **SELESAI**. Config `maxReconnectAttempts` sudah diubah ke `Infinity`.
3.  **Queue & Real-time**: ✅ **SELESAI**. Berjalan normal.
4.  **Duplicate Handling**: ✅ **SELESAI**. Command `wa:merge-duplicates` siap, webhook logic updated.

Sistem siap untuk pengujian QA menyeluruh (User Acceptance Testing).
