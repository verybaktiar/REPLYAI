# ReplyAI Project Feature Roadmap

Dokumen ini melacak status pengembangan fitur untuk project ReplyAI Admin.

## ✅ Completed Features (Sudah Dikerjakan)
- [x] **Dashboard UI Redesign**: Tampilan command center baru dengan statistik dan feed aktivitas.
- [x] **Live Inbox (Standardized)**: Antarmuka chat 3-panel dengan bubble chat bot/user yang jelas.
- [x] **Bot Management (Rules)**: UI berbasis Kartu untuk CRUD rules, bukan tabel kaku.
- [x] **Activity Logs**: Halaman log detail dengan filter status dan platform.
- [x] **Sidebar Standardization**: Navigasi konsisten di seluruh aplikasi (Dark Mode).
- [x] **Analytics & Reports**: Dashboard analisa kinerja bot dengan grafik traffic.

## 🚧 In Progress (Sedang Dikerjakan)
- [x] **3. CRM & Kontak (Patient Profiling)**
    - [x] Halaman database kontak/pasien.
    - [x] Detail profil dengan riwayat chat (Link ke Inbox).
    - [x] Filter berdasarkan tag (BPJS, VIP, dll).

- [ ] **4. Pengaturan Jam Operasional (Business Hours)**
    - [x] Rute & UI Placeholder.
    - [ ] Form Database & Logic.

- [x] **5. Bot Simulator (Playground)**
    - [x] Rute & UI Placeholder.
    - [x] Chat Engine Sandbox.

## 📅 Scheduled (Akan Dikerjakan Selanjutnya)
- [ ] **2. AI Knowledge Base Parser**
    - [ ] Fitur Upload Dokumen (PDF/Word/Txt).
    - [ ] UI untuk manajemen dokumen yang di-ingest AI.
    
- [ ] **4. Pengaturan Jam Operasional (Business Hours)**
    - [ ] Halaman Settings untuk atur jam buka/tutup RS.
    - [ ] Konfigurasi pesan auto-reply di luar jam kerja.

- [ ] **5. Bot Simulator (Playground)**
    - [ ] Halaman khusus / Widget untuk tes respon bot tanpa HP.
    - [ ] Debugger view untuk melihat logic AI.

## 🔮 Next Up Roadmap

| Prioritas | Fitur | Deskripsi | Status |
|---|---|---|---|
| 🔥 Tinggi | **Dashboard Analytics** | Grafik trend, top pertanyaan, response time | ✅ |
| 🔥 Tinggi | **Pengaturan Jam Operasional** | Bot kirim "Kami tutup" di luar jam kerja | ⏳ |
| 📊 Sedang | **AI KB Parser** | Upload PDF → otomatis jadi knowledge base | ✅ |
| ⚡ Sedang | **Quick Reply Template** | Template jawaban cepat untuk CS | ✅ |

## 💡 Future Ideas
- [x] **WhatsApp Integration** - Bot untuk WhatsApp menggunakan Baileys
- [ ] **Broadcast / Campaign Manager** - Kirim pesan massal (Blast)
- [ ] **Multi-Agent Support** - Beberapa CS online bersamaan

## ✅ WhatsApp Integration (Core)
- [x] Node.js Service (Baileys) - `wa-service/`
- [x] Database Migrations (wa_sessions, wa_messages)
- [x] Laravel Models (WaSession, WaMessage)
- [x] WhatsAppService & Controllers
- [x] API Routes (connect, disconnect, send, webhook)
- [x] WhatsApp Settings UI Page
- [x] Sidebar Navigation Update
- [x] Testing dengan device WhatsApp
- [x] Group Chat Filtering & Null Message Fix

## 🚀 WhatsApp Expansion (Next Priority)
- [ ] **WhatsApp Inbox** - UI Chat mirip WA Web (List chat, Bubble chat, media view)
- [ ] **Media Support** - Kirim/Terima Gambar & Dokumen
- [x] **Broadcast / Blast Message**
  - [x] Campaign Management (Database & UI)
  - [x] Background Queueing (Anti-banned delay)
  - [x] Target Filtering (Exclude Groups)
  - [x] Realtime Progress Reportminder** - Otomatis reminder H-1
- [ ] **Multi-Session** - Support banyak nomor WA
- [x] **Analytics** - Dashboard statistik pesan
- [x] **Internal Documentation** - Panduan penggunaan di dalam aplikasi
