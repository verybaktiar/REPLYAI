# Panduan Deployment & Sinkronisasi Kode: ReplyAI

Dokumen ini berfungsi sebagai **SOP (Standard Operating Procedure)** agar Anda atau AI di masa depan bisa melakukan setup ulang dengan benar dan melakukan update kode tanpa mengirim file secara manual.

---

## Bagian 1: Sinkronisasi Kode & Database (Manual)

Sesuai preferensi Anda, sinkronisasi dilakukan secara manual tanpa sistem sinkronisasi otomatis:

1.  **Kode**: Salin folder proyek `REPLYAI` dari laptop ke PC Server (misal lewat USB atau LAN) ke folder `C:\laragon\www\`.
2.  **Database**:
    - Di Laptop: Export database `replyai` lewat phpMyAdmin menjadi file `.sql`.
    - Di PC Server: Import file `.sql` tersebut ke database baru di Laragon.

---

## Bagian 2: Langkah Setup Awal (PC Server Windows)

### 1. Persiapan Lingkungan

- Instal **Laragon** (Apache/Nginx, PHP 8.2, MySQL).
- Instal **Git** untuk OS Windows.
- Instal **Node.js** (untuk menjalankan service WhatsApp).

### 2. Persiapan Folder Proyek

- Letakkan folder proyek di `C:\laragon\www\REPLYAI`.
- Buka terminal di folder tersebut untuk instalasi dependensi (hanya 1x):

```bash
composer install
npm install
npm run build
```

### 3. Konfigurasi Produksi (.env)

Buat file `.env` di PC Server, sesuaikan bagian ini:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://replai.my.id

DB_DATABASE=replyai_prod
DB_USERNAME=root
DB_PASSWORD=your_password

# Jalankan: php artisan key:generate
```

### 4. Setup Jaringan (Cloudflare Tunnel)

1.  Download `cloudflared.exe` di PC Server.
2.  Buka terminal sebagai Admin:
    ```bash
    cloudflared tunnel login
    cloudflared tunnel create replyai-tunnel
    cloudflared tunnel route dns replyai-tunnel replai.my.id
    cloudflared tunnel run --url http://localhost:80 replyai-tunnel
    ```

---

## Bagian 3: Cara Melakukan Update (Manual)

Setiap ada perubahan fitur di laptop:

1.  Salin file-file yang berubah ke USB/LAN.
2.  Timpa (overwrite) file yang ada di PC Server.
3.  Impor ulang database jika ada perubahan struktur tabel.
4.  Jalankan `php artisan optimize` di PC Server untuk me-refresh sistem.

---

## Bagian 4: Menjalankan Background Services

Pastikan dua hal ini selalu jalan di PC Server (bisa dimasukkan ke Startup Windows):

1.  **WA Service**: Di folder service WA, jalankan `npm start`.
2.  **Queue Worker**: Buka terminal di folder project, jalankan `php artisan queue:work` (agar pengiriman pesan massal/broadcast jalan di background).

---

## Bagian 5: Otomatisasi & Persistence (Auto-Start)

Agar server Anda tetap berjalan meskipun PC mati dan nyala lagi, ikuti langkah ini:

### 1. Laragon (Web & Database)

Buka Laragon -> Klik Ikon Gear (Preferences) -> Centang:

- **Run Laragon at Windows startup**
- **Start all services automatically**
  Ini akan memastikan Apache/Nginx dan MySQL langsung nyala tanpa Anda klik apa-apa.

### 2. Layanan Background (WA & Queue)

Buat file baru di Notepad, simpan dengan nama `start_projects.bat` di folder Startup Windows (`Win + R`, ketik `shell:startup`):

```batch
@echo off
:: Beri waktu 10 detik agar Laragon/Internet siap
timeout /t 10

:: Jalankan Service WhatsApp
cd /d "C:\laragon\www\REPLYAI\wa-service"
start "WA_SERVICE" npm start
:: Jalankan Laravel Queue (Bot)
cd /d "C:\laragon\www\REPLYAI"
start "LARAVEL_QUEUE" php artisan queue:work --tries=3
```

### 3. Cloudflare Tunnel sebagai Service

Jalankan Command Prompt sebagai Administrator:

```bash
cloudflared service install
```

Ini akan membuat Cloudflare Tunnel berjalan sebagai Windows Service (selalu aktif di latar belakang tanpa ada jendela terminal yang terbuka).

---

## Penting: Perbedaan Perintah di Server (Produksi)

| Perihal       | Di Laptop (Coding)  | Di PC Server (Online)                                          |
| :------------ | :------------------ | :------------------------------------------------------------- |
| **Akses Web** | `php artisan serve` | **DILARANG**. Cukup akses via domain (Laragon sudah handel).   |
| **Frontend**  | `npm run dev`       | **DILARANG**. Jalankan `npm run build` (hanya 1x saat update). |
| **Log Fitur** | `APP_DEBUG=true`    | `APP_DEBUG=false` (agar error tidak terlihat orang lain).      |

---

## Rencana Verifikasi

- [ ] Restart PC Server, pastikan domain `replai.my.id` langsung bisa dibuka.
- [ ] Pastikan bot WhatsApp langsung merespon tanpa harus buka terminal manual.
- [ ] Pastikan fitur Broadcast Laravel berjalan secara otomatis.
