# 📚 Dokumentasi Sistem ReplyAI

> **Versi**: 1.0  
> **Tanggal**: 16 Februari 2026  
> **Platform**: Laravel 12 + Tailwind CSS v4 + Alpine.js

---

## 📋 Daftar Isi

1. [Gambaran Umum](#-gambaran-umum)
2. [Alur Sistem](#-alur-sistem)
3. [Fitur-Fitur Utama](#-fitur-fitur-utama)
4. [Arsitektur Teknologi](#-arsitektur-teknologi)
5. [Struktur Database](#-struktur-database)
6. [Diagram Alur](#-diagram-alur)
7. [Integrasi Channel](#-integrasi-channel)

---

## 🎯 Gambaran Umum

**ReplyAI** adalah platform **Customer Service Automation** berbasis Laravel yang dirancang untuk membantu bisnis menangani komunikasi pelanggan secara otomatis melalui berbagai channel (Instagram, WhatsApp, Web Widget).

### Konsep Dasar

| Aspek | Deskripsi |
|-------|-----------|
| **Tujuan** | Mengotomatisasi balasan customer service 24/7 |
| **Pendekatan** | Rule-based + AI Knowledge Base |
| **Target User** | UMKM, Startup, Rumah Sakit, E-commerce |
| **Model Bisnis** | Subscription-based (Basic, Pro, Enterprise) |

---

## 🔄 Alur Sistem

### Alur Utama (Main Flow)

```
┌─────────────┐     ┌──────────────────┐     ┌──────────────────────────┐
│   CHANNEL   │────▶│  WEBHOOK/API     │────▶│   LARAVEL BACKEND        │
│  (IG/WA/Web)│     │  (Node.js WA     │     │  - Proses pesan          │
└─────────────┘     │   Service)       │     │  - Cek Rule/AI           │
                    └──────────────────┘     │  - Simpan ke DB          │
                                             └──────────────────────────┘
                                                        │
                              ┌─────────────────────────┼──────────────────────┐
                              ▼                         ▼                      ▼
                    ┌─────────────────┐      ┌─────────────────┐    ┌──────────────────┐
                    │  CEK RULES      │      │  KNOWLEDGE BASE │    │  TAKEOVER/       │
                    │  (Keyword-based)│      │  (AI Parser)    │    │  HUMAN HANDOFF   │
                    └─────────────────┘      └─────────────────┘    └──────────────────┘
                              │                         │                      │
                              ▼                         ▼                      ▼
                    ┌─────────────────┐      ┌─────────────────┐    ┌──────────────────┐
                    │  Balasan Cepat  │      │  AI Response    │    │  CS Agent Reply  │
                    │  (Tepat cocok)  │      │  (Semantic)     │    │  (Bot off)       │
                    └─────────────────┘      └─────────────────┘    └──────────────────┘
                              │                         │                      │
                              └─────────────────────────┴──────────────────────┘
                                                        │
                                                        ▼
                                               ┌─────────────────┐
                                               │  KIRIM BALASAN  │
                                               │  Ke Channel     │
                                               └─────────────────┘
```

### Tahapan Proses Detail

| Tahap | Komponen | Deskripsi |
|-------|----------|-----------|
| **1. Pesan Masuk** | Channel (IG/WA/Web) | Customer mengirim pesan ke platform |
| **2. Webhook** | Node.js Service / Meta API | Platform mengirim payload ke sistem |
| **3. Simpan Pesan** | Laravel Controller | Sistem menyimpan ke tabel `conversations` & `messages` |
| **4. Cek Status** | TakeoverController | Cek apakah sedang di-takeover oleh CS |
| **5. Proses Balasan** | BotService | Cek Rules → Knowledge Base → Default Response |
| **6. Kirim Balasan** | WhatsAppService / InstagramService | Kirim balasan ke customer |
| **7. Logging** | ActivityLog | Catat aktivitas untuk analytics |

### Alur Handoff (Human Takeover)

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│   Bot Aktif     │────────▶│  CS Ambil Alih   │────────▶│  CS Membalas    │
│  (Auto-reply)   │         │  (Klik Takeover) │         │  (Manual reply) │
└─────────────────┘         └──────────────────┘         └─────────────────┘
                                                                     │
                                                                     ▼
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│   Bot Aktif     │◀────────│  Auto Handback   │◀────────│  CS Selesai     │
│  (Auto-reply)   │         │  (4 jam timeout) │         │  / Klik Handback│
└─────────────────┘         └──────────────────┘         └─────────────────┘
```

---

## ✨ Fitur-Fitur Utama

### 1. 📊 Dashboard
**Lokasi**: `/dashboard`

| Fitur | Deskripsi |
|-------|-----------|
| Statistik Cepat | Total Percakapan, Pesan Masuk, Bot Reply |
| Grafik Aktivitas | Chart harian/mingguan volume pesan |
| Status Koneksi | Indicator Online/Offline per channel |
| Feed Aktivitas | Log terbaru secara real-time |

### 2. 💬 Live Inbox (Kotak Masuk)
**Lokasi**: `/inbox` (Instagram), `/whatsapp/inbox` (WhatsApp)

#### Layout 3-Panel
```
┌─────────────────┬─────────────────────┬─────────────────┐
│  DAFTAR CHAT    │    PREVIEW CHAT     │  DETAIL KONTAK  │
│                 │                     │                 │
│  [Filter]       │  ┌─────────────┐    │  Nama: xxx      │
│  [Search]       │  │ Bubble Chat │    │  No: xxx        │
│                 │  │             │    │  Status: xxx    │
│  • Chat 1       │  │ Bot/User    │    │                 │
│  • Chat 2       │  └─────────────┘    │  [Takeover]     │
│  • Chat 3       │                     │  [Handback]     │
│                 │  [Input Message]    │                 │
└─────────────────┴─────────────────────┴─────────────────┘
```

#### Filter Status
| Status | Warna Badge | Deskripsi |
|--------|-------------|-----------|
| `All` | - | Semua percakapan |
| `Bot Handling` | 🟢 Hijau | Bot sedang aktif menjawab |
| `Needs Attention` | 🔴 Merah | Butuh bantuan manusia |
| `Agent Handling` | 🟡 Kuning | CS sedang melayani |

#### Fitur Chat
- **Handoff**: CS ambil alih → Bot berhenti menjawab
- **Handback**: Kembalikan ke bot setelah selesai
- **Auto Handback**: Otomatis setelah 4 jam tidak ada balasan CS

### 3. 🤖 Manajemen Bot (Rules)
**Lokasi**: `/rules`

#### Struktur Rule
```php
{
  "id": 1,
  "keyword": "harga",
  "response": "Silakan cek harga di: www.example.com/harga",
  "logic": "contains",  // atau "exact"
  "is_active": true
}
```

#### Tipe Logic
| Tipe | Deskripsi | Contoh |
|------|-----------|--------|
| `Contains` | Pesan mengandung keyword | "harga" → cocok dengan "berapa harga?" |
| `Exact` | Pesan persis sama | "harga" → hanya cocok "harga" |

#### UI Features
- Card-based layout (bukan tabel)
- Drag-and-drop reorder
- Quick toggle on/off
- Test rule langsung di UI

### 4. 📚 Knowledge Base (KB)
**Lokasi**: `/kb`

#### Fitur
| Fitur | Deskripsi |
|-------|-----------|
| Upload Dokumen | PDF, Word, TXT → Auto parse |
| Web Scraping | Scraping konten dari URL |
| Manual Input | Tulis artikel langsung |
| AI Parser | Ekstrak teks otomatis |

#### Alur Knowledge Base
```
Pesan Masuk → Cek Rules (tidak cocok) 
                        ↓
            Cari di Knowledge Base (Semantic Search)
                        ↓
            Cocok → AI Response berdasarkan KB
            Tidak Cocok → Default Response / AI General
```

### 5. 📱 WhatsApp Integration
**Lokasi**: `/whatsapp/settings`, `/whatsapp/inbox`

#### Multi-Device Support
```
┌─────────────────────────────────────────────┐
│           WHATSAPP DEVICES                  │
├─────────────────────────────────────────────┤
│  [+] Tambah Device Baru                     │
│                                             │
│  ┌─────────────┐  ┌─────────────┐          │
│  │ Device 1    │  │ Device 2    │          │
│  │ 🟢 Connected│  │ 🟡 Scanning │          │
│  │ 081234...   │  │ [QR Code]   │          │
│  │ [Detail]    │  │ [Cancel]    │          │
│  └─────────────┘  └─────────────┘          │
└─────────────────────────────────────────────┘
```

#### Fitur WhatsApp
| Fitur | Deskripsi |
|-------|-----------|
| QR Connect | Scan QR untuk koneksi (Baileys) |
| Multi-Session | Banyak nomor WA dalam satu akun |
| Broadcast | Kirim pesan massal dengan queue |
| Media Support | Gambar, dokumen, video |
| Status Tracking | Connected, Scanning, Disconnected |

### 6. 📷 Instagram Integration
**Lokasi**: `/instagram/settings`

#### Fitur
- OAuth Connect ke Instagram Business Account
- Auto-reply DM
- Webhook dari Meta Graph API
- Multi-account support

### 7. 👥 CRM & Data Kontak
**Lokasi**: `/contacts`

#### Data yang Disimpan
| Field | Keterangan |
|-------|------------|
| Nama Profil | Nama display customer |
| Platform | Instagram / WhatsApp / Web |
| Total Pesan | Jumlah interaksi |
| Terakhir Aktif | Timestamp terakhir |
| Tags | BPJS, VIP, New Lead, dsb |
| Notes | Catatan per kontak |

### 8. 📈 Analytics & Reports
**Lokasi**: `/analytics`, `/whatsapp/analytics`

#### Metrik yang Dilacak
- Volume pesan (hari/minggu/bulan)
- Response time (rata-rata)
- Bot resolution rate (% terselesaikan bot)
- Handoff rate (% yang perlu CS)
- CSAT Rating (Customer Satisfaction)

### 9. ⚡ Quick Replies
**Lokasi**: `/settings/quick-replies`

#### Konsep Shortcut
```
CS mengetik: /salam
Sistem mengganti: "Selamat pagi, ada yang bisa kami bantu?"

CS mengetik: /jam
Sistem mengganti: "Jam operasional kami: Senin-Jumat 08:00-17:00"
```

### 10. 🎮 Bot Simulator
**Lokasi**: `/simulator`

#### Fitur
- Test bot tanpa device/IG asli
- Debug panel: Rule yang kena, confidence score
- Real-time response testing
- Sandbox environment

### 11. 🌐 Web Chat Widget
**Lokasi**: `/web-widgets`

#### Fitur
- Generate embed code untuk website
- Customizable colors & branding
- Mobile responsive
- Real-time chat

### 12. 📬 Sequences (Drip Campaign)
**Lokasi**: `/sequences`

#### Alur Sequence
```
User Subscribe → Hari 1: Pesan 1 → Hari 3: Pesan 2 → Hari 7: Pesan 3
```

#### Fitur
- Schedule pesan berurutan
- Delay customization
- Manual enrollment
- Cancel anytime

### 13. ⏰ Pengaturan Jam Operasional
**Lokasi**: `/settings`

#### Konfigurasi
- Jam buka & tutup per hari
- Pesan khusus di luar jam kerja
- Auto-reply "Kami tutup, akan dibalas besok"

### 14. 📋 Activity Logs
**Lokasi**: `/logs`

#### Jenis Log
- Auto-reply logs
- Takeover/handback logs
- Error logs
- API usage logs

### 15. 💳 Subscription & Billing
**Lokasi**: `/subscription`, `/checkout`

#### Plans
| Plan | Fitur | Limit |
|------|-------|-------|
| Basic | Rules, Basic KB | 1000 msg/bulan |
| Pro | +AI, Multi-channel | 5000 msg/bulan |
| Enterprise | +Priority, Custom | Unlimited |

### 16. 🔐 Admin Panel (Super Admin)
**Lokasi**: `/admin`

#### Fitur Admin
- Manajemen user & subscription
- Approval pembayaran manual
- Broadcast announcement
- System health monitoring
- Feature flags management
- Impersonate user

---

## 🏗️ Arsitektur Teknologi

### Stack Teknologi

| Layer | Teknologi | Versi |
|-------|-----------|-------|
| **Backend Framework** | Laravel | 12.x |
| **PHP Version** | PHP | 8.2+ |
| **Frontend CSS** | Tailwind CSS | v4 |
| **Frontend JS** | Alpine.js | 3.x |
| **Template Engine** | Blade | - |
| **Database** | MySQL / PostgreSQL | - |
| **Queue System** | Laravel Queue | Database/Redis |
| **Build Tool** | Vite | - |
| **Testing** | Pest | - |

### Layanan Eksternal

| Layanan | Fungsi |
|---------|--------|
| **Node.js + Baileys** | WhatsApp Web API |
| **Meta Graph API** | Instagram DM |
| **OpenAI API** | AI Response |
| **Midtrans** | Payment Gateway |

### Struktur Folder

```
REPLYAI/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controller utama
│   │   ├── Middleware/       # Auth, Verified, Suspended
│   │   └── Requests/         # Form validation
│   ├── Models/               # Eloquent Models (50+ model)
│   ├── Services/             # Business logic (WhatsAppService, etc)
│   └── Traits/               # Reusable traits
├── config/
├── database/
│   ├── migrations/           # 100+ migration files
│   └── seeders/
├── resources/
│   ├── views/                # Blade templates
│   ├── css/                  # Tailwind styles
│   └── js/                   # Alpine.js components
├── routes/
│   ├── web.php               # Main routes
│   ├── admin.php             # Admin panel routes
│   └── api.php               # API routes
├── wa-service/               # Node.js WhatsApp service
└── storage/
    ├── logs/                 # Application logs
    └── app/public/           # Uploads
```

---

## 🗄️ Struktur Database

### Tabel Utama

#### Conversations (Percakapan)
```php
- id (bigint, PK)
- user_id (bigint, FK)           # Pemilik akun
- instagram_account_id (bigint)  # Link ke IG
- instagram_user_id (string)     # Meta API contact ID
- ig_username (string)
- display_name (string)
- last_message (text)
- source (enum: chatwoot, meta_direct)
- status (enum: bot_handling, agent_handling, needs_attention)
- tags (json)
- created_at, updated_at
```

#### Messages (Pesan)
```php
- id (bigint, PK)
- conversation_id (bigint, FK)
- sender_type (enum: contact, agent)
- content (text)
- source (enum: chatwoot, meta_direct)
- is_replied_by_bot (boolean)
- sent_at (timestamp)
```

#### wa_sessions (WhatsApp Sessions)
```php
- id (bigint, PK)
- session_id (string, unique)    # ID sesi Baileys
- device_name (string)
- phone_number (string)
- profile_name (string)
- status (enum: connected, disconnected, scanning)
- is_active (boolean)
```

#### auto_reply_rules (Rules Bot)
```php
- id (bigint, PK)
- user_id (bigint, FK)
- keyword (string)
- response (text)
- logic (enum: contains, exact)
- is_active (boolean)
- priority (int)
```

#### kb_articles (Knowledge Base)
```php
- id (bigint, PK)
- user_id (bigint, FK)
- title (string)
- content (text)
- source_type (enum: manual, pdf, url)
- is_active (boolean)
```

---

## 📊 Diagram Alur

### Sequence Diagram: Pesan Masuk → Balasan

```
Customer    Channel     Webhook    Laravel    BotService    Database    AI/Rule
   │           │           │           │            │            │          │
   │──Pesan───▶│           │           │            │            │          │
   │           │──Payload─▶│           │            │            │          │
   │           │           │──Request─▶│            │            │          │
   │           │           │           │──Simpan───▶│            │          │
   │           │           │           │            │◀───────────│          │
   │           │           │           │──Proses────▶│            │          │
   │           │           │           │            │──Cek Rule────────────▶│
   │           │           │           │            │◀───────────│          │
   │           │           │           │            │            │          │
   │           │           │           │            │──Jika tidak cocok────▶│
   │           │           │           │            │◀───────────│          │
   │           │           │           │            │            │          │
   │           │◀──Kirim───│◀─Response─│◀───────────│            │          │
   │◀─Balasan──│           │           │            │            │          │
```

### State Diagram: Status Percakapan

```
                    ┌─────────────┐
                    │     NEW     │
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
       ┌──────────┐  ┌──────────┐  ┌──────────┐
       │BOT_HANDLE│  │ AGENT_   │  │  NEEDS   │
       │   ING    │──▶│ HANDLING │  │ATTENTION │
       └────┬─────┘  └────┬─────┘  └────┬─────┘
            │             │             │
            │    ┌────────┘             │
            │    ▼                      │
            └───▶│      RESOLVED        │◀─────────┘
                 └──────────────────────┘
```

---

## 🔌 Integrasi Channel

### 1. WhatsApp (via Baileys)

```javascript
// wa-service/ Node.js Service
const { makeWASocket } = require('@whiskeysockets/baileys');

// Flow:
// 1. Generate QR Code → User scan
// 2. Save session → Connect to WA Web
// 3. Listen messages → Forward to Laravel webhook
// 4. Send reply → WA socket
```

#### Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/connect` | Inisiasi sesi baru |
| GET | `/status` | Cek status koneksi |
| GET | `/qr` | Ambil QR code |
| POST | `/disconnect` | Putuskan sesi |
| POST | `/send` | Kirim pesan |

### 2. Instagram (via Meta API)

```php
// Laravel Controller
// 1. OAuth ke Meta
// 2. Dapatkan access token
// 3. Setup webhook
// 4. Listen incoming DM
// 5. Reply via Graph API
```

#### Webhook Events
- `messages` - Pesan masuk
- `message_reactions` - Reaction pada pesan
- `messaging_postbacks` - Postback dari button

### 3. Web Widget

```javascript
// Embed code yang di-generate
<script>
  (function() {
    var widget = document.createElement('script');
    widget.src = 'https://replyai.com/widget.js?key=xxx';
    document.head.appendChild(widget);
  })();
</script>
```

---

## 🔧 Konfigurasi Environment

### File .env

```env
# App
APP_NAME=ReplyAI
APP_ENV=production
APP_URL=https://replyai.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=replyai
DB_USERNAME=root
DB_PASSWORD=secret

# WhatsApp Service
WHATSAPP_SERVICE_URL=http://127.0.0.1:3001

# Meta API
META_APP_ID=your_app_id
META_APP_SECRET=your_app_secret

# OpenAI
OPENAI_API_KEY=sk-xxx

# Payment (Midtrans)
MIDTRANS_SERVER_KEY=xxx
MIDTRANS_CLIENT_KEY=xxx
MIDTRANS_IS_PRODUCTION=true
```

---

## 🚀 Deployment

### Persyaratan Server
- PHP 8.2+
- MySQL 8.0+ / PostgreSQL 13+
- Node.js 18+ (untuk wa-service)
- Redis (opsional, untuk queue)

### Langkah Deploy

```bash
# 1. Clone & Install
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
php artisan migrate --force
php artisan optimize

# 4. Queue Worker (supervisor)
php artisan queue:work --sleep=3 --tries=3

# 5. WhatsApp Service
cd wa-service && npm install && npm start
```

---

## 📞 Kontak & Support

| Kanal | Link/Contact |
|-------|--------------|
| Dokumentasi Online | `/docs` (dalam aplikasi) |
| Support Ticket | `/support` |
| Admin Panel | `/admin` |

---

## 📝 Changelog

| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 1.0 | 2026-02-16 | Dokumentasi awal |

---

*Dokumen ini akan diupdate secara berkala sesuai perkembangan sistem.*
