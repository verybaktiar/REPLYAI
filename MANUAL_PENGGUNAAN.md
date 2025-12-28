# Manual Penggunaan Sistem ReplyAI

Dokumen ini berisi penjelasan lengkap mengenai fitur, fungsi, dan cara penggunaan sistem ReplyAI.

## Daftar Isi
1. [Dashboard](#1-dashboard)
2. [Kotak Masuk (Inbox)](#2-kotak-masuk-inbox)
3. [Data Kontak (CRM)](#3-data-kontak-crm)
4. [Analisis & Laporan](#4-analisis--laporan)
5. [Manajemen Bot (Rules)](#5-manajemen-bot-rules)
6. [Knowledge Base (KB)](#6-knowledge-base-kb)
7. [Quick Replies](#7-quick-replies)
8. [Simulator](#8-simulator)
9. [Settings (Operational Hours)](#9-settings-operational-hours)
10. [Log Aktivitas](#10-log-aktivitas)

---

## 1. Dashboard
**Fungsi:** Halaman utama yang memberikan gambaran ringkas kinerja sistem.
**Isi:**
- Statistik cepat (Total Percakapan, Pesan Masuk, Bot Reply).
- Grafik aktivitas harian.
- Status koneksi ke platform (Instagram/WhatsApp).

---

## 2. Kotak Masuk (Inbox)
**Fungsi:** Pusat pengelolaan pesan masuk dari pelanggan.
**Fitur Utama:**
- **Daftar Percakapan:** Melihat semua chat yang masuk.
- **Filter Status:**
    - `All`: Semua pesan.
    - `Bot Handling`: Pesan yang sedang ditangani bot.
    - `Needs Attention` (Escalated): Pesan yang butuh bantuan manusia (bot menyerah/user minta CS).
    - `Agent Handling`: Pesan yang sedang dihandle oleh CS.
- **Chat Interface:** Halaman untuk membalas pesan secara manual.
- **Handoff (Ambil Alih):**
    - Saat CS membalas pesan, status otomatis berubah jadi `Agent Handling`.
    - Bot akan berhenti membalas otomatis di percakapan ini.
- **Kembalikan ke Bot (Handback):**
    - Tombol "Kembalikan ke Bot" untuk mengaktifkan kembali auto-reply setelah CS selesai melayani.
    - Jika CS tidak membalas selama 4 jam, sistem otomatis mengembalikan ke bot.

**Cara Penggunaan:**
1. Masuk ke menu **Kotak Masuk**.
2. Klik salah satu percakapan.
3. Ketik pesan untuk mengambil alih (Bot otomatis mati).
4. Klik **Kembalikan ke Bot** jika sudah selesai.

---

## 3. Data Kontak (CRM)
**Fungsi:** Menyimpan database pelanggan yang berinteraksi.
**Isi:**
- Nama Profil, Platform (IG/WA), Total Pesan, Terakhir Aktif.
- Tagging otomatis (misal: "new_lead", "support").
- Tombol "Lihat Chat" untuk langsung menuju histori percakapan di Inbox.

---

## 4. Analisis & Laporan
**Fungsi:** Melihat metrik performa bot dan agen.
**Fitur:**
- Grafik volume pesan per hari/minggu.
- Statistik sentimen (Positif/Negatif) jika ada modul sentimen (opsional).
- Waktu respon rata-rata.

---

## 5. Manajemen Bot (Rules)
**Fungsi:** Mengatur aturan balasan otomatis berbasis kata kunci (Rule-based).
**Cara Penggunaan:**
1. Klik **Tambah Rule Baru**.
2. **Keyword**: Masukkan kata kunci (misal: "harga", "biaya").
3. **Response**: Masukkan teks balasan bot.
4. **Logic**: Pilih `Contains` (mengandung kata) atau `Exact` (persis sama).
5. Klik **Simpan**.

---

## 6. Knowledge Base (KB)
**Fungsi:** "Otak" AI untuk menjawab pertanyaan kompleks tanpa rule baku.
**Fitur:**
- **Upload Dokumen**: Upload file PDF/Doc berisi info produk (brosur, SOP, FAQ).
- **Parser**: Sistem otomatis membaca teks dari file.
- **Input Manual**: Menulis artikel pengetahuan langsung.
**Alur:**
- Bot akan mencari jawaban di KB jika tidak ada _Rule_ yang cocok.

---

## 7. Quick Replies
**Fungsi:** Template pesan cepat untuk CS (shortcut).
**Fitur:**
- Membuat shortcut (misal: `/salam` -> "Selamat pagi, ada yang bisa kami bantu?").
- Digunakan di halaman **Inbox** oleh CS.

---

## 8. Simulator
**Fungsi:** Menguji respon bot tanpa harus chat dari akun Instagram asli.
**Cara Penggunaan:**
1. Buka menu **Simulator**.
2. Di kolom chat, ketik pesan tes (misal: "Halo").
3. Lihat balasan bot.
4. Panel kanan menampilkan debug log (Rule mana yang kena, confidence score AI, dll).

---

## 9. Settings (Operational Hours)
**Fungsi:** Mengatur jam kerja bot dan jam kerja CS.
**Fitur:**
- **Jam Operasional**: Tentukan jam buka/tutup bisnis.
- **Pesan di Luar Jam Kerja**: Menentukan balasan khusus jika customer chat tengah malam.

---

## 10. Log Aktivitas
**Fungsi:** Catatan teknis semua aktivitas sistem.
**Isi:** Info error, peringatan, atau log penting untuk developer debugging.
