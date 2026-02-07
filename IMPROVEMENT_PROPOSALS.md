# Rekomendasi Perbaikan & Peningkatan Sistem (ReplyAI)

Dokumen ini berisi saran perbaikan teknis dan fungsional untuk meningkatkan fleksibilitas, stabilitas, dan skalabilitas sistem AI WhatsApp, khususnya agar lebih aman untuk penggunaan Multi-Tenant (banyak user/bisnis berbeda).

**Catatan:** Kode belum diubah. Menunggu persetujuan (ACC) Anda untuk implementasi.

---

## 1. Dynamic Social Media & Contact Info (Prioritas Tinggi)
**Masalah:** Saat ini fallback message menggunakan `{$profile->business_name}` untuk merujuk ke sosial media. Namun, nama bisnis seringkali berbeda dengan username Instagram atau link website.
**Saran Perbaikan:**
- Tambahkan kolom baru di tabel `business_profiles`:
  - `instagram_handle` (contoh: `@kedaikopisusah`)
  - `website_url` (contoh: `www.kopisusah.com`)
  - `address_map_url` (Link Google Maps)
- Update `AiAnswerService` untuk menyuntikkan data ini ke dalam System Prompt jika tersedia.
- **Benefit:** Jawaban AI lebih akurat saat mengarahkan user ("Cek IG kami di @kedaikopisusah" vs "Cek IG kami di Kedai Kopi Susah").

## 2. Configurable Synonym Map per Industry
**Masalah:** Daftar sinonim (`$synonymMap`) saat ini di-hardcode di dalam `AiAnswerService.php` dan bercampur untuk semua industri (F&B, Kesehatan, Retail).
**Saran Perbaikan:**
- Pindahkan mapping sinonim ke file config (`config/ai_synonyms.php`) atau method di model `BusinessProfile`.
- Pisahkan sinonim berdasarkan `business_type`.
  - Contoh: Keyword "Poli" hanya relevan untuk `hospital`, keyword "Menu" untuk `fnb`.
- **Benefit:** Mengurangi "noise" pencarian KB dan mencegah AI bingung konteks antar industri.

## 3. Localization & Language Support (Dukungan Bahasa)
**Masalah:** `stopwords` dan logic greeting saat ini sangat spesifik Bahasa Indonesia.
**Saran Perbaikan:**
- Abstraksi string hardcoded ke file `lang/id/ai.php`.
- Izinkan `BusinessProfile` memiliki setting `primary_language`.
- **Benefit:** Sistem siap jika nanti ada klien yang ingin bot berbahasa Inggris atau daerah.

## 4. Rate Limiting & Cost Control
**Masalah:** Belum ada pembatasan berapa kali AI dipanggil per tenant. Jika ada user iseng spam chat, biaya API Perplexity bisa membengkak.
**Saran Perbaikan:**
- Tambahkan middleware atau check di `WhatsAppWebhookController` untuk limit:
  - Max pesan AI per user per hari.
  - Max pesan AI per tenant per bulan.
- **Benefit:** Mencegah kebocoran biaya operasional (cost safety).

## 5. Enhanced Logging & Debugging
**Masalah:** Saat ini kita log error dan warning, tapi belum ada log terstruktur untuk "AI Reasoning" (kenapa AI memilih jawaban A vs B).
**Saran Perbaikan:**
- Buat tabel `ai_logs` yang menyimpan:
  - Input User
  - Context yang ditemukan (ID artikel KB)
  - Prompt final yang dikirim
  - Jawaban AI
  - Token usage / Latency
- **Benefit:** Memudahkan debugging jika ada komplain "Kenapa AI jawab begini?" dan monitoring kualitas jawaban.

## 6. Strict Mode Toggle
**Masalah:** Tingkat "kreativitas" AI saat ini diatur global (`temperature=0.1`).
**Saran Perbaikan:**
- Tambahkan opsi `strict_mode` (boolean) di `BusinessProfile`.
- Jika `true`: Hanya jawab jika confidence score KB sangat tinggi (>0.85). Jika tidak, langsung fallback.
- Jika `false`: Lebih luwes/conversational (seperti sekarang).
- **Benefit:** Memberikan kontrol lebih kepada pemilik bisnis yang paranoid terhadap halusinasi.

---

### Langkah Selanjutnya?
Jika Anda setuju dengan salah satu atau beberapa poin di atas, silakan beri instruksi "ACC Poin 1 dan 4" (misalnya), dan saya akan mulai mengimplementasikannya secara bertahap.
