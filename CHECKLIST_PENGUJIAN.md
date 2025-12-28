# Checklist Pengujian Sistem ReplyAI (QA Checklist)

Gunakan daftar ini untuk memastikan seluruh sistem berjalan dengan baik.

## 1. Pengujian Dasar (Smoke Test)
- [ ] Login ke Dashboard admin berhasil.
- [ ] Sidebar navigasi tampil sempurna di semua halaman.
- [ ] Berpindah antar menu tidak ada error (404/500).

## 2. Pengujian Inbox & Handoff
### Skenario A: Bot Menjawab
- [ ] **Langkah:** Kirim pesan "Halo" dari Simulator/Instagram.
- [ ] **Ekspektasi:** Bot membalas sesuai _Welcome Message_ atau _Rule_. Status di Inbox: `Bot Handling`.

### Skenario B: Takeover oleh CS
- [ ] **Langkah:** Buka percakapan di Inbox > Ketik balasan manual "Halo kak, saya bantu ya".
- [ ] **Ekspektasi:**
    - Pesan terkirim.
    - Status berubah jadi `Agent Handling`.
    - Coba chat lagi dari sisi user -> Bot **TIDAK** membalas (Silent).

### Skenario C: Handback ke Bot
- [ ] **Langkah:** Di percakapan `Agent Handling`, klik tombol "Kembalikan ke Bot".
- [ ] **Ekspektasi:** Status kembali ke `Bot Handling`. Chat user berikutnya dibalas bot.

## 3. Pengujian Rules (Auto Reply)
- [ ] **Langkah:** Buat rule baru: Keyword="promo", Response="Ada diskon 50%".
- [ ] **Langkah:** Test di Simulator ketik "info promo dong".
- [ ] **Ekspektasi:** Bot membalas "Ada diskon 50%".

## 4. Pengujian Knowledge Base
- [ ] **Langkah:** Upload file PDF berisi info produk.
- [ ] **Langkah:** Tunggu proses parsing selesai.
- [ ] **Langkah:** Test di Simulator tanya hal spesifik dari PDF tersebut (yang tidak ada di Rules).
- [ ] **Ekspektasi:** Bot menjawab menggunakan konteks dari PDF (via AI).

## 5. Pengujian Data Kontak (CRM)
- [ ] **Langkah:** Chat baru masuk dari user baru.
- [ ] **Ekspektasi:** Data user muncul di menu **Data Kontak**.
- [ ] **Langkah:** Cek kolom "Total Pesan".
- [ ] **Ekspektasi:** Angka sesuai jumlah chat, bukan "n/a".

## 6. Pengujian Quick Replies
- [ ] **Langkah:** Buat Quick Reply: Shortcut="/siap", Text="Siap laksanakan!".
- [ ] **Langkah:** Buka Inbox > Ketik "/".
- [ ] **Ekspektasi:** Muncul sugesti "/siap". Klik -> teks terisi otomatis.

## 7. Pengujian Jam Operasional
- [ ] **Langkah:** Set jam tutup sekarang (di menu Settings).
- [ ] **Langkah:** Chat dari Simulator.
- [ ] **Ekspektasi:** Balasan bot sesuai "Pesan di Luar Jam Kerja".
