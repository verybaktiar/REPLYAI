# Optimasi Fitur AI Automation (Keamanan & Efisiensi)

Dokumen ini menjelaskan rangkaian optimasi yang telah diterapkan pada fitur-fitur AI Automation (Auto-Follow Up, Daily Summary, dan AI Style Training) untuk memastikan sistem yang lebih aman, menjaga privasi, dan bekerja secara profesional.

## 1. Keamanan & Privasi AI Training (Self-Learning)

Fitur ini memungkinkan AI untuk belajar dari gaya bahasa yang Anda sukai, namun dengan batasan keamanan yang ketat:

- **Penyaringan Data Sensitif (PII Scrubbing)**: Sistem secara otomatis mendeteksi dan menyensor informasi pribadi seperti email, nomor telepon, nomor KTP, atau nomor rekening sebelum disimpan ke dalam contoh latihan AI.
- **Persetujuan Manual (Admin Approval)**: Jawaban yang ditandai "Bagus" di Inbox akan masuk ke status _Draft_. Admin harus menyetujui (`is_approved`) contoh tersebut sebelum AI benar-benar menggunakannya sebagai referensi gaya bahasa.
- **Aturan Customer Terverifikasi (Anti-Poisoning)**: Untuk mencegah "serangan" dari nomor asing yang sengaja memberikan contoh buruk, latihan AI hanya diizinkan untuk customer yang sudah memiliki riwayat chat minimal 7 hari.

## 2. Auto-Follow Up yang Pintar & Terkontrol (Anti-Spam)

Fitur pengingat otomatis untuk customer yang tidak merespon dalam 24 jam:

- **Batasan Pengiriman**: Maksimal hanya **2 kali follow-up** per percakapan. Jika tetap tidak ada respon, sistem akan berhenti secara otomatis.
- **Weekend Skip (Libur)**: Sistem secara otomatis melewati hari Sabtu dan Minggu untuk menjaga profesionalisme bisnis.
- **Waktu Pintar (Smart Timing Window)**: Pengingat hanya dikirim pada jam operasional (**09:00 - 21:00**). Jika batas 24 jam tercapai di jam tidur, sistem akan menunggu hingga pagi.
- **Pemeriksaan Konteks (Context-Aware)**: Sistem akan meninjau pesan terakhir customer. Jika mengandung kata penutup (seperti "terima kasih", "ok", atau "sudah transfer"), maka follow-up akan otomatis dibatalkan.
- **Kontrol Manual (Toggle)**: Di header chat WhatsApp Inbox, Anda bisa mematikan/menghidupkan auto-follow up secara manual untuk pelanggan tertentu.

## 3. Laporan Harian (Daily Summary) & Ekspor Data

Fitur untuk transparansi data dan pemantauan performa harian:

- **Laporan Gamifikasi (Emoji)**: Laporan WhatsApp kini lebih visual dengan emoji (🔥, 📊, ⭐) dan penilaian performa CS menggunakan sistem bintang.
- **Privasi pada Laporan**: Nomor telepon customer dalam laporan WhatsApp akan disensor sebagian (contoh: `0812xxxx45`).
- **Ekspor Data (Backup)**: Anda dapat mengunduh data latihan gaya bahasa AI dalam format **CSV** atau **JSON** melalui tombol ekspor di sidebar WhatsApp Inbox.

---

**Status Implementasi:** Phase 1 & 2 Selesai & Terverifikasi
**Lokasi Kode Utama:**

- `AiAnswerService.php` (Logika AI, Scrubbing, & Gamifikasi)
- `SendAutoFollowupJob.php` (Anti-Spam, Timing, & Weekend Skip)
- `WhatsAppInboxController.php` (API Latihan AI & Kontrol Follow-up)
- `AiTrainingExportController.php` (Fitur Ekspor/Backup)
