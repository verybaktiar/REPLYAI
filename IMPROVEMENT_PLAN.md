# Rencana Perbaikan Sistem SaaS ReplyAI

Dokumen ini berisi analisis dan rencana perbaikan untuk meningkatkan stabilitas, performa, dan kemudahan pengelolaan (maintainability) sistem ReplyAI.

## 1. Infrastruktur & Stabilitas Backend

### A. Manajemen Process "WA Service" (Node.js)
**Status Saat Ini:** `wa-service` dijalankan manual via terminal (`npm start`). Jika server restart atau crash, service mati.
**Rencana Perbaikan:**
- [ ] Implementasi **PM2** (Process Manager) untuk menjalankan `wa-service`.
- [ ] Konfigurasi *auto-restart* jika crash.
- [ ] Konfigurasi *startup script* agar jalan otomatis saat boot Windows/Linux.

### B. Antrian (Queue) Laravel
**Status Saat Ini:** Kemungkinan dijalankan manual (`php artisan queue:work`) atau sync.
**Rencana Perbaikan:**
- [ ] Pastikan Queue Worker berjalan sebagai *service* (bisa pakai Supervisor di Linux atau NSSM di Windows).
- [ ] Pisahkan antrian untuk tugas berat (misal: Broadcast WhatsApp) ke queue khusus `high-priority` dan `default`.

## 2. Frontend & Performa (Critical)

### A. Migrasi dari CDN ke Build Process (Vite)
**Status Saat Ini:** Menggunakan Tailwind CSS via CDN (`<script src="https://cdn.tailwindcss.com">`).
**Masalah:**
- Performa lambat (browser harus compile CSS tiap reload).
- Tidak ada *cache busting* yang efisien.
- Ketergantungan pada internet eksternal untuk aset dasar.
- FOUC (Flash of Unstyled Content) kadang terjadi.
**Rencana Perbaikan:**
- [ ] Install Tailwind CSS via NPM.
- [ ] Konfigurasi **Vite** untuk compile aset CSS & JS.
- [ ] Ubah layout Blade untuk memuat `app.css` dan `app.js` hasil build (`@vite(['resources/css/app.css', 'resources/js/app.js'])`).

### B. Optimasi Struktur Blade & JavaScript
**Status Saat Ini:** Banyak logika JavaScript inline di dalam file `.blade.php` (contoh: di `inbox/index.blade.php`).
**Rencana Perbaikan:**
- [ ] Ekstrak logika JS yang kompleks ke file terpisah di `resources/js/`.
- [ ] Gunakan **Alpine.js Components** untuk logika UI yang berulang (seperti Dropdown, Modal, Sidebar) agar kode Blade lebih bersih.

## 3. Struktur Kode & Kualitas

### A. Refactoring "Fat Views"
**Status Saat Ini:** File `index.blade.php` di inbox sangat panjang dan mencampur logika layout, style, dan script.
**Rencana Perbaikan:**
- [ ] Pecah komponen UI menjadi Blade Components (misal: `<x-inbox.conversation-list>`, `<x-inbox.chat-area>`).
- [ ] Standarisasi penggunaan Layout. Saat ini ada `dark.blade.php`, pastikan konsisten penggunaannya.

### B. Type Safety (PHP)
**Status Saat Ini:** Helper dan Controller mungkin belum ketat tipe datanya.
**Rencana Perbaikan:**
- [ ] Tambahkan *Type Hinting* pada argument fungsi dan return types di Controller dan Service utama (terutama yang menangani pembayaran dan WA).

## 4. Keamanan (Security)

### A. Komunikasi Service-to-Service
**Status Saat Ini:** Laravel memanggil WA Service via HTTP (curl/Guzzle).
**Rencana Perbaikan:**
- [ ] Pastikan endpoint WA Service (`/send-message`, `/sessions`) dilindungi token internal sederhana (API Key) agar tidak bisa ditembak sembarangan jika port terbuka ke publik.

### B. Validasi Input
**Status Saat Ini:** Input dari user (terutama di fitur Broadcast) perlu validasi ketat.
**Rencana Perbaikan:**
- [ ] Review Request Validation untuk semua form input (Store/Update requests).

## 5. Monitoring & Logging

### A. Sentralisasi Log
**Status Saat Ini:** Log Laravel (`storage/logs`) dan Log Node.js terpisah.
**Rencana Perbaikan:**
- [ ] Buat dashboard admin sederhana di Laravel untuk melihat status "Health Check" dari WA Service (Up/Down) dan melihat 50 baris log terakhirnya.

---

## Langkah Eksekusi Prioritas (Tahap 1)
Jika rencana ini disetujui, kita akan mulai dengan **Poin 2.A (Migrasi ke Vite)** dan **Poin 1.A (PM2 untuk WA Service)** karena ini memberikan dampak performa dan stabilitas paling instan.
