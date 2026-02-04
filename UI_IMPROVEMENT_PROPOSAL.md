# Proposal Perbaikan UI & Sistem WhatsApp Inbox

Dokumen ini berisi analisis masalah (bug) yang ditemukan pada sistem saat ini dan usulan perbaikan antarmuka (UI/UX) agar lebih modern, rapi, dan profesional.

## 1. Analisis Masalah: Duplikasi Percakapan ("Kok ada doble gini?")

### 🛑 Masalah
Dalam screenshot yang dilampirkan, terlihat ada dua percakapan untuk kontak yang sama ("~Hawin Feri Baktiar~") dengan waktu yang sama ("3 hours ago").

### 🔍 Penyebab Teknis
WhatsApp memiliki dua jenis ID untuk pengguna:
1.  **Phone Number ID**: Format standar (contoh: `628123456789`).
2.  **LID (Linked Device ID)**: Format panjang (contoh: `265583866159318` seperti terlihat di panel kanan screenshot).

Saat ini, sistem menyimpan pesan dari kedua ID ini sebagai **dua entitas berbeda** di database karena kolom `phone_number` di tabel `wa_messages` menerima mentah-mentah apa yang dikirim webhook. Akibatnya:
- Pesan dari HP user masuk sebagai Chat A.
- Pesan dari Device/LID user masuk sebagai Chat B.
- Di UI Inbox, keduanya muncul terpisah padahal orangnya sama.

### ✅ Solusi Perbaikan (Backend)
Perlu diterapkan **Normalisasi Kontak**:
1.  Saat pesan masuk, cek apakah ID tersebut adalah LID (biasanya 15+ digit dan bukan format nomor telepon umum).
2.  Jika LID, cari di database apakah ada nomor HP asli yang terhubung dengan `pushName` yang sama atau `user_id` yang sama.
3.  Simpan pesan tersebut di bawah satu `phone_number` utama.
4.  Buat *Migration Script* untuk menggabungkan (merge) percakapan duplikat yang sudah terlanjur ada.

---

## 2. Usulan Desain UI Baru (UI Improvement)

User merasa UI "belum fix" atau kurang rapi. Berikut adalah usulan desain "Clean & Professional SaaS" untuk modul WhatsApp.

### A. Konsep Visual (Clean Dark/Light Mode)
Saat ini UI terlalu gelap dan padat. Kita akan mengadopsi gaya yang lebih *breathable* (lega) mirip dengan **Intercom** atau **WhatsApp Web Modern**.

| Komponen | Kondisi Sekarang | Usulan Perbaikan |
| :--- | :--- | :--- |
| **Sidebar Kiri (Menu)** | Terlalu lebar, ikon kurang jelas. | Perkecil lebar (60px), hanya ikon dengan tooltip, atau pindah ke atas (Header). |
| **List Chat (Tengah)** | Terlalu padat, badge status kecil. | **Card Style**: Beri padding lebih, avatar lebih besar, badge status (Bot/Human) lebih jelas dengan warna kontras. Tambahkan *Section Divider* ("Hari Ini", "Kemarin"). |
| **Chat Area (Kanan)** | Bubble chat standar, background gelap. | **Modern Bubbles**: Bubble dengan sudut membulat yang lebih halus, warna bubble incoming/outgoing yang lebih kontras tapi lembut (misal: Slate Blue vs White). Background pattern lebih transparan. |
| **Panel Kanan (CRM)** | Baru ditambahkan (slide-in). | **Permanent/Toggle Column**: Buat menjadi kolom ke-3 yang permanen (3-Column Layout) agar user bisa chat sambil edit notes tanpa panel menutupi chat. |

### B. Mockup Layout (Wireframe)

```
+----------------+---------------------+------------------------------------------+----------------------+
|  NAVBAR (Top)  |  SEARCH & FILTER    |  HEADER: Nama Kontak & Status            |  ACTIONS             |
+----------------+---------------------+------------------------------------------+----------------------+
|  [Logo]        |  [All] [Unread]     |                                          |  [Resolve] [Ticket]  |
|                |                     |                                          |                      |
|  (ICON MENU)   |  **Hari Ini**       |  [ Bubble Kiri: User ]                   |  **DETAIL KONTAK**   |
|  [Inbox]       |  [ Avatar ] Name    |                                          |                      |
|  [Broadcst]    |    "Pesan..."       |           [ Bubble Kanan: Bot ]          |  Nama: Hawin Feri    |
|  [Contact]     |    [Badge: Bot]     |                                          |  No: +628...         |
|  [Report]      |                     |  [ Bubble Kiri: User ]                   |                      |
|  [Setting]     |  **Kemarin**        |                                          |  **TAGS**            |
|                |  [ Avatar ] Name    |                                          |  [VIP] [Lead] [+]    |
|                |    "Ok siap..."     |                                          |                      |
|                |                     |                                          |  **NOTES**           |
|                |                     |  --------------------------------------  |  [Input Note...]     |
|                |                     |  [ Input Box: Ketik pesan... ] [Send]    |  - Note 1 (User A)   |
+----------------+---------------------+------------------------------------------+----------------------+
```

### C. Fitur UI Spesifik yang Perlu Ditambahkan
1.  **Filter Tabs di List Chat**:
    *   Tab: "Semua", "Belum Dibaca", "Perlu Balasan (Human)", "Selesai".
    *   Ini membantu CS fokus pada chat yang butuh perhatian saja.
2.  **Indikator "Sedang Mengetik" (Real-time)**:
    *   Tampilkan animasi `...` di list chat jika user sedang mengetik (memanfaatkan event websocket).
3.  **Quick Actions on Hover**:
    *   Di list chat, saat di-hover muncul tombol cepat: "Arsipkan", "Tandai Belum Baca", "Assign ke Saya".
4.  **Bulk Actions**:
    *   Checkbox di list chat untuk memilih banyak pesan sekaligus -> "Mark as Read" atau "Broadcast".

## 3. Rencana Eksekusi (Action Plan)

Jika Anda setuju, saya akan mengerjakan perbaikan ini dalam 2 tahap:

### Tahap 1: Fix Masalah Duplikasi & Data (Prioritas Tinggi)
- [ ] Buat script normalisasi untuk menggabungkan chat LID & No. HP.
- [ ] Update logic `WhatsAppInboxController` agar pintar mendeteksi duplikat.

### Tahap 2: Revamp UI (Sesuai Request)
- [ ] Redesign `inbox.blade.php` dengan layout 3-kolom yang lebih rapi.
- [ ] Perbaiki CSS untuk *spacing*, *typography*, dan warna agar tidak terlihat "double" atau membingungkan.
- [ ] Tambahkan tab filter (Unread/All/Human).

---
**Apakah Anda ingin saya mulai dengan Tahap 1 (Fix Duplikasi) terlebih dahulu?**
