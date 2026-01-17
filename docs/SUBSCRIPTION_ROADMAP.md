# 🎯 Roadmap: Sistem Langganan Bulanan ReplyAI

## 📋 Ringkasan
Membangun sistem langganan (subscription) untuk ReplyAI yang mirip dengan **Botpenguin** dan **Cekat AI**, dimana:
- Pengguna memilih paket langganan (Hemat, Pro, Enterprise)
- Fitur dibatasi berdasarkan paket yang dibeli
- Langganan otomatis expired setelah masa berlaku habis
- Pengguna harus perpanjang untuk membuka fitur kembali

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Plans Table   │────▶│ Subscriptions   │────▶│  Feature Gates  │
│  (Paket-paket)  │     │  (Langganan)    │     │ (Pembatas Fitur)│
└─────────────────┘     └─────────────────┘     └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Plan Features  │     │   Payments      │     │  Usage Tracking │
│ (Fitur per Plan)│     │  (Pembayaran)   │     │ (Tracking Limit)│
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

---

## 📦 Ide Paket Langganan

| Fitur | 🆓 Gratis | 💰 Hemat (99k/bln) | 🚀 Pro (249k/bln) | 🏢 Enterprise |
|-------|-----------|-------------------|-------------------|---------------|
| WhatsApp Device | 1 | 2 | 5 | Unlimited |
| Kontak Tersimpan | 100 | 500 | 2.000 | Unlimited |
| Pesan AI/bulan | 50 | 500 | 2.000 | Unlimited |
| Broadcast/bulan | ❌ | 500 | 5.000 | Unlimited |
| Knowledge Base (MB) | 5 MB | 50 MB | 200 MB | Unlimited |
| Sequences | ❌ | 3 | 10 | Unlimited |
| Quick Replies | 5 | 20 | 50 | Unlimited |
| Web Widget | ❌ | 1 | 5 | Unlimited |
| Team Members | 1 | 2 | 5 | Unlimited |
| Analytics | Basic | Full | Full + Export | Custom |
| Support | Community | Email | Priority | Dedicated |

> **Catatan**: Angka-angka ini bisa disesuaikan nanti

---

## 💰 Rekomendasi Harga & Fitur Detail

### Analisis Pasar Indonesia
Berdasarkan kompetitor di Indonesia:
- **Botpenguin**: $5-$50/bulan (Rp 80k - 800k)
- **Cekat AI**: Rp 149k - 499k/bulan
- **Kata.ai**: Enterprise pricing
- **Qontak**: Rp 200k - 1jt/bulan

### 📦 Rekomendasi Paket ReplyAI

---

### 🆓 GRATIS (Starter)
**Harga: Rp 0/bulan**

Untuk: UMKM yang baru mulai, ingin coba-coba

| Fitur | Limit |
|-------|-------|
| WhatsApp Device | 1 device |
| Kontak Tersimpan | 100 kontak |
| Pesan AI/bulan | 50 pesan |
| Knowledge Base | 5 MB (1-2 dokumen) |
| Quick Replies | 5 template |
| Inbox | ✅ Unlimited |
| Test Bot (Simulator) | ✅ |
| Analytics | Basic (7 hari) |
| Broadcast | ❌ |
| Sequences | ❌ |
| Web Widget | ❌ |
| Team Member | 1 orang |
| Support | Dokumentasi |

**Tujuan**: Lead generation, biarkan user merasakan value produk

---

### 💰 HEMAT (Starter Plus)
**Harga: Rp 99.000/bulan** atau **Rp 990.000/tahun** (hemat 2 bulan)

Untuk: UMKM aktif, toko online, reseller

| Fitur | Limit |
|-------|-------|
| WhatsApp Device | 2 device |
| Kontak Tersimpan | 1.000 kontak |
| Pesan AI/bulan | 500 pesan |
| Knowledge Base | 50 MB (~10 dokumen) |
| Quick Replies | 20 template |
| Inbox | ✅ Unlimited |
| Test Bot (Simulator) | ✅ |
| Analytics | Full (30 hari) |
| **Broadcast** | ✅ 500 pesan/bulan |
| **Sequences** | ✅ 3 sequence aktif |
| **Web Widget** | ❌ |
| Team Member | 2 orang |
| Support | Email (response 24 jam) |

**Value Proposition**: "Semua fitur penting untuk bisnis online"

---

### 🚀 PRO (Business)
**Harga: Rp 249.000/bulan** atau **Rp 2.490.000/tahun** (hemat 2 bulan)

Untuk: Bisnis menengah, klinik, toko multi-cabang

| Fitur | Limit |
|-------|-------|
| WhatsApp Device | 5 device |
| Kontak Tersimpan | 5.000 kontak |
| Pesan AI/bulan | 2.000 pesan |
| Knowledge Base | 200 MB (~50 dokumen) |
| Quick Replies | 50 template |
| Inbox | ✅ Unlimited |
| Test Bot (Simulator) | ✅ |
| Analytics | Full + Export CSV |
| **Broadcast** | ✅ 5.000 pesan/bulan |
| **Sequences** | ✅ 10 sequence aktif |
| **Web Widget** | ✅ 3 widget |
| Team Member | 5 orang |
| Support | Priority Email (response 12 jam) |
| **Bonus** | Remove "Powered by ReplyAI" |

**Value Proposition**: "Untuk bisnis yang serius scale up"

---

### 🏢 ENTERPRISE (Custom)
**Harga: Mulai Rp 500.000/bulan** (nego berdasarkan kebutuhan)

Untuk: Rumah sakit, perusahaan besar, multi-branch

| Fitur | Limit |
|-------|-------|
| WhatsApp Device | Unlimited |
| Kontak Tersimpan | Unlimited |
| Pesan AI/bulan | Unlimited |
| Knowledge Base | Unlimited |
| Quick Replies | Unlimited |
| Broadcast | Unlimited |
| Sequences | Unlimited |
| Web Widget | Unlimited |
| Team Member | Unlimited |
| Analytics | Custom Dashboard |
| Support | Dedicated Account Manager |
| **Bonus** | On-premise option, Custom Integration, SLA |

**Value Proposition**: "Solusi lengkap untuk enterprise"

---

### 📊 Perbandingan Ringkas

```
┌─────────────────────────────────────────────────────────────────┐
│                    PERBANDINGAN PAKET                           │
├──────────────┬────────┬─────────┬─────────┬─────────────────────┤
│ Fitur        │ Gratis │ Hemat   │ Pro     │ Enterprise          │
│              │ Rp 0   │ Rp 99k  │ Rp 249k │ Custom              │
├──────────────┼────────┼─────────┼─────────┼─────────────────────┤
│ WA Device    │ 1      │ 2       │ 5       │ Unlimited           │
│ Kontak       │ 100    │ 1.000   │ 5.000   │ Unlimited           │
│ Pesan AI     │ 50/bln │ 500/bln │ 2.000   │ Unlimited           │
│ Broadcast    │ ❌      │ 500     │ 5.000   │ Unlimited           │
│ Sequences    │ ❌      │ 3       │ 10      │ Unlimited           │
│ Web Widget   │ ❌      │ ❌       │ 3       │ Unlimited           │
│ Team         │ 1      │ 2       │ 5       │ Unlimited           │
│ Analytics    │ Basic  │ Full    │ Export  │ Custom              │
└──────────────┴────────┴─────────┴─────────┴─────────────────────┘
```

---

### 💡 Strategi Pricing

1. **Trial 7 Hari Pro** 
   - User baru dapat trial Paket Pro selama 7 hari
   - Setelah trial, downgrade ke Gratis atau bayar

2. **Diskon Tahunan**
   - Bayar 12 bulan, dapat 10 bulan (hemat 2 bulan)
   - Meningkatkan retention

3. **Early Bird / Promo Launch**
   - 50% off untuk 100 subscriber pertama
   - Lifetime discount untuk early adopters

4. **Referral Program**
   - Ajak teman, dapat 1 bulan gratis
   - Yang diajak dapat 20% diskon bulan pertama

---

## 🔐 Fitur yang Perlu Di-Gate (Dibatasi)

### 1. **Limit Kuantitas (Hard Limits)**
Fitur yang dibatasi berdasarkan jumlah:
- [ ] Jumlah WhatsApp device yang bisa dihubungkan
- [ ] Jumlah kontak yang bisa disimpan
- [ ] Jumlah pesan AI per bulan (AI quota)
- [ ] Jumlah broadcast per bulan
- [ ] Ukuran knowledge base (MB)
- [ ] Jumlah sequence yang bisa dibuat
- [ ] Jumlah quick replies
- [ ] Jumlah web widget
- [ ] Jumlah team member

### 2. **Akses Fitur (Feature Access)**
Fitur yang on/off berdasarkan paket:
- [ ] Akses ke Broadcast (Gratis = tidak bisa)
- [ ] Akses ke Sequences (Gratis = tidak bisa)
- [ ] Akses ke Web Widget (Gratis = tidak bisa)
- [ ] Akses ke Analytics Export
- [ ] Akses ke API
- [ ] Custom branding (hilangkan "Powered by ReplyAI")

---

## 💾 Database Schema (Draft)

### Tabel: `plans`
```sql
- id
- name (Gratis, Hemat, Pro, Enterprise)
- slug (gratis, hemat, pro, enterprise)
- description
- price_monthly (dalam rupiah)
- price_yearly (diskon tahunan)
- features (JSON) -- limit semua fitur
- is_active
- sort_order
- created_at, updated_at
```

### Tabel: `subscriptions`
```sql
- id
- user_id (tenant/workspace)
- plan_id
- status (trial, active, past_due, canceled, expired)
- starts_at
- expires_at
- trial_ends_at (jika ada trial)
- canceled_at
- payment_method
- created_at, updated_at
```

### Tabel: `payments`
```sql
- id
- user_id
- subscription_id
- amount
- payment_method (midtrans, xendit, manual_transfer)
- payment_reference (ID dari gateway)
- status (pending, paid, failed, refunded)
- paid_at
- invoice_url
- created_at, updated_at
```

### Tabel: `usage_records`
```sql
- id
- user_id
- feature_key (ai_messages, broadcasts, contacts, etc)
- used_count
- period_start
- period_end
- created_at, updated_at
```

---

## 🔄 Alur Subscription

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  User Daftar │────▶│ Trial 7 Hari │────▶│ Pilih Paket  │
│   (Gratis)   │     │  (Opsional)  │     │  & Bayar     │
└──────────────┘     └──────────────┘     └──────────────┘
                                                 │
                     ┌───────────────────────────┘
                     ▼
              ┌──────────────┐
              │ Subscription │
              │    Active    │
              └──────────────┘
                     │
        ┌────────────┼────────────┐
        ▼            ▼            ▼
  ┌──────────┐ ┌──────────┐ ┌──────────┐
  │ Perpanjang│ │  Expired │ │  Cancel  │
  │  Otomatis │ │ (Kunci)  │ │  Manual  │
  └──────────┘ └──────────┘ └──────────┘
                     │
                     ▼
              ┌──────────────┐
              │ Grace Period │
              │   (3 hari)   │
              └──────────────┘
                     │
                     ▼
              ┌──────────────┐
              │   Locked     │
              │ (Mode Read)  │
              └──────────────┘
```

---

## 🚫 Behavior Saat Expired

### Option A: Hard Lock (Seperti Cekat AI)
- Semua fitur terkunci
- User hanya bisa lihat dashboard (read-only)
- Popup muncul mengarahkan ke halaman perpanjang
- Data tetap tersimpan (tidak dihapus)

### Option B: Soft Lock (Seperti Botpenguin)
- Fitur premium terkunci
- Fitur gratis tetap bisa dipakai
- Downgrade otomatis ke paket Gratis
- Data di atas limit tetap tersimpan tapi tidak bisa diakses

### Option C: Grace Period + Lock
- 3 hari grace period dengan peringatan
- Setelah grace period, hard lock
- Data aman selama 30 hari setelah expired

> **Pertanyaan untuk Anda**: Mau pakai option mana?

---

## 💳 Opsi Payment Gateway (Indonesia)

| Gateway | Kelebihan | Kekurangan |
|---------|-----------|------------|
| **Midtrans** | Populer, banyak metode | Setup agak ribet |
| **Xendit** | API bagus, recurring | Fee lebih tinggi |
| **Tripay** | Murah, mudah | Kurang fitur |
| **Mayar** | Simple, subscription ready | Baru |
| **Manual Transfer** | Gratis | Manual verifikasi |

> **Pertanyaan**: Mau mulai dengan gateway apa? Atau manual transfer dulu?

---

## 📱 UI yang Perlu Dibuat

### 1. **Halaman Pricing (Public)**
- Tampilkan semua paket
- Comparison table
- CTA "Mulai Gratis" / "Langganan Sekarang"

### 2. **Halaman Checkout**
- Pilih durasi (bulanan/tahunan)
- Input kode promo (opsional)
- Pilih metode pembayaran
- Konfirmasi & bayar

### 3. **Halaman Subscription (Dashboard)**
- Status langganan saat ini
- Tanggal expired
- Usage meter (berapa % quota terpakai)
- Tombol upgrade/perpanjang

### 4. **Modal Upgrade Prompt**
- Muncul saat user mau akses fitur premium
- "Fitur ini hanya tersedia di paket Pro"
- Tombol "Upgrade Sekarang"

### 5. **Banner Warning**
- Muncul 7 hari sebelum expired
- "Langganan Anda akan berakhir dalam X hari"

---

## 🗓️ Roadmap Implementasi

### Phase 1: Foundation (1-2 minggu)
- [ ] Buat tabel database (plans, subscriptions, payments, usage)
- [ ] Buat model & relationship
- [ ] Buat seeder untuk plans
- [ ] Buat middleware `CheckSubscription`
- [ ] Buat helper `hasFeature()`, `canUseFeature()`, `getRemainingQuota()`

### Phase 2: Core Logic (1-2 minggu)
- [ ] Service class untuk subscription management
- [ ] Logic upgrade/downgrade
- [ ] Logic expiry & grace period
- [ ] Cron job untuk cek expired subscriptions
- [ ] Usage tracking service

### Phase 3: Payment (1-2 minggu)
- [ ] Integrasi payment gateway (atau manual transfer dulu)
- [ ] Webhook handler untuk payment notification
- [ ] Invoice generation
- [ ] Email notification (payment success, almost expired, expired)

### Phase 4: UI (1-2 minggu)
- [ ] Halaman Pricing
- [ ] Halaman Checkout
- [ ] Halaman Subscription Management
- [ ] Upgrade prompts di seluruh app
- [ ] Usage dashboard

### Phase 5: Polish (1 minggu)
- [ ] Testing semua flow
- [ ] Fix edge cases
- [ ] Admin panel untuk manage subscriptions
- [ ] Dokumentasi

---

## 🛡️ Super Admin Panel (Untuk Anda sebagai Pemilik)

### Konsep Arsitektur Multi-Tenant

```
┌─────────────────────────────────────────────────────────────────┐
│                     SUPER ADMIN (Anda)                          │
│   (admin.replyai.com atau replyai.com/superadmin)              │
├─────────────────────────────────────────────────────────────────┤
│  • Lihat semua tenant/subscriber                                │
│  • Monitor revenue & payment                                    │
│  • Manage plans & pricing                                       │
│  • Approve manual transfers                                     │
│  • Send announcements                                           │
│  • Suspend/activate accounts                                    │
└─────────────────────────────────────────────────────────────────┘
                              │
       ┌──────────────────────┼──────────────────────┐
       ▼                      ▼                      ▼
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│   Tenant A      │  │   Tenant B      │  │   Tenant C      │
│ (Subscriber 1)  │  │ (Subscriber 2)  │  │ (Subscriber 3)  │
│                 │  │                 │  │                 │
│ - Dashboard     │  │ - Dashboard     │  │ - Dashboard     │
│ - Inbox         │  │ - Inbox         │  │ - Inbox         │
│ - Bot           │  │ - Bot           │  │ - Bot           │
│ - dll           │  │ - dll           │  │ - dll           │
└─────────────────┘  └─────────────────┘  └─────────────────┘
```

---

### 📊 Dashboard Super Admin

**URL**: `/superadmin` atau subdomain `admin.replyai.com`

#### 1. Overview Dashboard
```
┌─────────────────────────────────────────────────────────────┐
│  SUPER ADMIN DASHBOARD                                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  📈 STATISTIK HARI INI                                      │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐           │
│  │ Total   │ │ Active  │ │ Revenue │ │ New     │           │
│  │ Tenants │ │ Subs    │ │ Bulan   │ │ Signup  │           │
│  │   156   │ │   89    │ │ 12.5jt  │ │   12    │           │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘           │
│                                                             │
│  📋 PENDING ACTIONS                                         │
│  ┌───────────────────────────────────────────────────┐     │
│  │ 🔔 5 pembayaran manual menunggu approval          │     │
│  │ 🔔 3 subscription akan expired dalam 3 hari       │     │
│  │ 🔔 2 support ticket belum dijawab                 │     │
│  └───────────────────────────────────────────────────┘     │
│                                                             │
│  📊 GRAFIK REVENUE (30 hari terakhir)                       │
│  [===================================]                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

#### 2. Tenant Management
Halaman untuk melihat dan manage semua subscriber:

| Kolom | Deskripsi |
|-------|-----------|
| ID | Tenant ID |
| Nama Bisnis | Nama usaha subscriber |
| Email | Email pemilik |
| Plan | Paket langganan (Gratis/Hemat/Pro) |
| Status | Active, Expired, Suspended |
| Expires At | Tanggal kadaluarsa |
| Revenue | Total revenue dari tenant ini |
| Actions | Lihat Detail, Suspend, Login As |

**Fitur:**
- 🔍 Search by name/email
- 🏷️ Filter by plan, status
- 📅 Filter by signup date
- 📊 Export to CSV

#### 3. Revenue & Payments
Pantau semua pemasukan:

```
┌─────────────────────────────────────────────────────────────┐
│  REVENUE DASHBOARD                                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  💰 Total Revenue                                           │
│  ┌───────────┐ ┌───────────┐ ┌───────────┐                 │
│  │  Hari Ini │ │  Bulan Ini │ │  Total    │                 │
│  │  Rp 450k  │ │  Rp 12.5jt │ │  Rp 87jt  │                 │
│  └───────────┘ └───────────┘ └───────────┘                 │
│                                                             │
│  📋 RIWAYAT PEMBAYARAN                                      │
│  ┌─────┬──────────┬─────────┬──────────┬────────┬───────┐  │
│  │ ID  │ Tenant   │ Amount  │ Method   │ Status │ Date  │  │
│  ├─────┼──────────┼─────────┼──────────┼────────┼───────┤  │
│  │ 123 │ Toko ABC │ 99.000  │ Transfer │ ✅ Paid │ 17/01 │  │
│  │ 122 │ Klinik X │ 249.000 │ Midtrans │ ✅ Paid │ 16/01 │  │
│  │ 121 │ RS Mitra │ 500.000 │ Transfer │ ⏳ Pend│ 16/01 │  │
│  └─────┴──────────┴─────────┴──────────┴────────┴───────┘  │
│                                                             │
│  📎 PENDING APPROVAL (Manual Transfer)                      │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Toko XYZ uploaded bukti transfer Rp 99.000          │   │
│  │ [Preview] [✅ Approve] [❌ Reject]                   │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

#### 4. Plan Management
Kelola paket langganan:

- ➕ Tambah plan baru
- ✏️ Edit harga & fitur
- 🔄 Aktifkan/nonaktifkan plan
- 📊 Lihat berapa subscriber per plan

#### 5. Promo & Coupon
Kelola kode promo:

| Kolom | Deskripsi |
|-------|-----------|
| Code | Kode promo (LAUNCH50, HEMAT20) |
| Discount | Persentase atau nominal |
| Usage | Berapa kali sudah dipakai |
| Limit | Batas penggunaan |
| Expires | Tanggal kadaluarsa |

#### 6. Announcements
Kirim pengumuman ke semua atau selected tenant:

- 📢 Broadcast email ke semua user
- 🎯 Target specific plan (contoh: hanya ke user Gratis untuk promo upgrade)
- 📅 Schedule announcement

#### 7. Login As (Impersonate)
Fitur penting untuk troubleshooting:

- Bisa "masuk" ke akun tenant untuk debug
- Melihat apa yang mereka lihat
- Tanpa perlu minta password mereka

#### 8. System Settings
Pengaturan global:

- 🔧 Default trial period
- 🔧 Grace period duration
- 🔧 Payment gateway config
- 🔧 Email templates
- 🔧 Global announcement bar

---

### 🗂️ Database Tambahan untuk Super Admin

```sql
-- Tabel untuk role super admin
CREATE TABLE admin_users (
    id,
    name,
    email,
    password,
    role (superadmin, support, finance),
    created_at
);

-- Tabel untuk activity log
CREATE TABLE admin_activity_logs (
    id,
    admin_id,
    action (approve_payment, suspend_tenant, login_as, etc),
    details (JSON),
    created_at
);

-- Tabel untuk announcements
CREATE TABLE announcements (
    id,
    title,
    message,
    target_plans (JSON array atau 'all'),
    is_active,
    starts_at,
    ends_at,
    created_at
);

-- Tabel untuk promo codes
CREATE TABLE promo_codes (
    id,
    code,
    discount_type (percent, fixed),
    discount_value,
    usage_count,
    usage_limit,
    valid_from,
    valid_until,
    applicable_plans (JSON array),
    is_active,
    created_at
);
```

---

### 🔐 Keamanan Super Admin

1. **Separate Login**
   - URL berbeda dari user login
   - `/superadmin/login`

2. **2FA (Two-Factor Authentication)**
   - Wajib untuk keamanan
   - Google Authenticator / Email OTP

3. **IP Whitelist**
   - Hanya IP tertentu yang bisa akses
   - Contoh: hanya dari kantor Anda

4. **Activity Logging**
   - Semua aksi admin tercatat
   - Siapa melakukan apa dan kapan

5. **Role-Based Access**
   - Superadmin: Full access
   - Support: Hanya lihat tenant, tidak bisa edit payment
   - Finance: Hanya akses revenue & payment

---

### 📱 Mobile Monitoring (Opsional)

Untuk monitoring cepat via HP:

- Telegram Bot notifikasi:
  - 🔔 New signup
  - 💰 Payment received
  - ⚠️ Subscription expired
  - 🆘 Support ticket baru

---

## 🆘 Sistem Support & Helpdesk

### Cara Agar Anda Tahu Pelanggan Ada Trouble

Ada beberapa level untuk mendeteksi dan menangani masalah pelanggan:

```
┌─────────────────────────────────────────────────────────────────┐
│                    ALUR SUPPORT                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  PELANGGAN TROUBLE                                              │
│       │                                                         │
│       ▼                                                         │
│  ┌─────────────────────────────────────────────┐               │
│  │ 1. Baca Dokumentasi/FAQ dulu               │  (Self-help)   │
│  └─────────────────────────────────────────────┘               │
│       │ Tidak ketemu solusi                                     │
│       ▼                                                         │
│  ┌─────────────────────────────────────────────┐               │
│  │ 2. Kirim Support Ticket                    │  (In-app)      │
│  └─────────────────────────────────────────────┘               │
│       │                                                         │
│       ▼                                                         │
│  ┌─────────────────────────────────────────────┐               │
│  │ 3. Anda Dapat Notifikasi                   │  (Email/TG)    │
│  └─────────────────────────────────────────────┘               │
│       │                                                         │
│       ▼                                                         │
│  ┌─────────────────────────────────────────────┐               │
│  │ 4. Balas Ticket / Login As untuk Debug     │  (Super Admin) │
│  └─────────────────────────────────────────────┘               │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### 🎫 1. Sistem Ticket Support (Di Dalam App)

Pelanggan bisa submit ticket dari dashboard mereka:

#### UI Pelanggan (Tombol "Bantuan" / Floating Button)
```
┌─────────────────────────────────────────────────┐
│  🆘 Butuh Bantuan?                              │
├─────────────────────────────────────────────────┤
│                                                 │
│  Kategori:                                      │
│  ┌─────────────────────────────────────────┐   │
│  │ ▼ Pilih Kategori                        │   │
│  │   - Bot tidak merespon                  │   │
│  │   - WhatsApp disconnect                 │   │
│  │   - Pembayaran                          │   │
│  │   - Fitur tidak berfungsi               │   │
│  │   - Pertanyaan lainnya                  │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  Jelaskan masalah Anda:                         │
│  ┌─────────────────────────────────────────┐   │
│  │                                         │   │
│  │                                         │   │
│  │                                         │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  📎 Lampirkan Screenshot (opsional)             │
│                                                 │
│  [        Kirim Ticket        ]                 │
│                                                 │
└─────────────────────────────────────────────────┘
```

#### Database Ticket
```sql
CREATE TABLE support_tickets (
    id,
    tenant_id,
    ticket_number (TKT-2024-001),
    category,
    subject,
    message,
    attachments (JSON - URLs gambar),
    priority (low, medium, high, urgent),
    status (open, in_progress, waiting_customer, resolved, closed),
    assigned_to (admin_id yang handle),
    resolved_at,
    created_at,
    updated_at
);

CREATE TABLE ticket_replies (
    id,
    ticket_id,
    sender_type (customer, admin),
    sender_id,
    message,
    attachments,
    created_at
);
```

#### UI Super Admin - Ticket Management
```
┌─────────────────────────────────────────────────────────────┐
│  🎫 SUPPORT TICKETS                          [+ Filter ▼]   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ⏳ OPEN (5)  │  🔄 IN PROGRESS (3)  │  ✅ RESOLVED (124)    │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ 🔴 TKT-2024-156  │ URGENT                          │   │
│  │ Toko ABC         │ WhatsApp disconnect terus       │   │
│  │ Pro Plan         │ 10 menit yang lalu               │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ 🟡 TKT-2024-155  │ MEDIUM                          │   │
│  │ Klinik XYZ       │ Bot tidak merespon keyword      │   │
│  │ Hemat Plan       │ 1 jam yang lalu                  │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

### 📲 2. Multi-Channel Notifikasi ke Anda

Ketika ada ticket baru atau masalah, Anda dapat notifikasi via:

#### A. Email Notification
```
Subject: 🔴 [URGENT] New Support Ticket from Toko ABC

Hai Admin,

Ada ticket baru yang memerlukan perhatian:

Ticket: TKT-2024-156
Tenant: Toko ABC (Pro Plan)  
Email: tokoabc@gmail.com
Kategori: WhatsApp Disconnect
Priority: URGENT

Pesan:
"WhatsApp saya disconnect terus sejak tadi pagi. 
Sudah coba scan ulang QR tapi gagal terus."

[Lihat Ticket] [Login As User]

---
ReplyAI Support System
```

#### B. Telegram Bot Notification (Recommended!)
Buat bot Telegram yang kirim notif ke HP Anda:

```
🚨 NEW TICKET - URGENT

📋 TKT-2024-156
👤 Toko ABC (Pro)
📧 tokoabc@gmail.com

❓ Kategori: WA Disconnect
📝 WhatsApp saya disconnect terus sejak tadi pagi...

[Lihat Detail] [Login As]

⏰ 17 Jan 2024, 10:25 WIB
```

**Setup Telegram Bot:**
1. Buat bot via @BotFather
2. Simpan token di `.env`
3. Setiap ada ticket baru → kirim ke chat Anda

#### C. WhatsApp Notification (ke nomor Anda sendiri)
Bisa kirim notif ke WA pribadi Anda menggunakan Fonnte/WA Gateway yang sama.

---

### 🚨 3. Auto-Detection Error (Proactive Monitoring)

Selain menunggu pelanggan lapor, sistem bisa **otomatis deteksi** masalah:

#### A. Health Check Monitoring
Cron job yang cek setiap 5 menit:

```php
// Cek semua WA device yang harusnya connected tapi disconnect
$problematicDevices = WhatsappDevice::where('status', 'connected')
    ->where('last_seen_at', '<', now()->subMinutes(10))
    ->get();

// Kirim alert ke admin
if ($problematicDevices->count() > 0) {
    // Notif ke Telegram/Email
    Alert::send("⚠️ {$problematicDevices->count()} device mungkin disconnect!");
}
```

#### B. Error Rate Monitoring
Deteksi jika error rate tinggi:

```php
// Cek error rate dalam 1 jam terakhir
$totalRequests = Log::where('created_at', '>', now()->subHour())->count();
$errorRequests = Log::where('created_at', '>', now()->subHour())
    ->where('status', 'failed')->count();

$errorRate = ($errorRequests / $totalRequests) * 100;

if ($errorRate > 10) { // Lebih dari 10% error
    Alert::send("🔴 Error rate tinggi: {$errorRate}%");
}
```

#### C. Dashboard Health Status
Di Super Admin Dashboard, tampilkan:

```
┌─────────────────────────────────────────────┐
│  🏥 SYSTEM HEALTH                           │
├─────────────────────────────────────────────┤
│  ✅ API Response Time: 120ms (Normal)       │
│  ✅ WA Gateway: Connected                   │
│  ⚠️ Error Rate: 3.2% (Warning)              │
│  ✅ Queue Jobs: 12 pending (Normal)         │
│  ❌ Tenant "Toko ABC": WA Disconnected      │
└─────────────────────────────────────────────┘
```

---

### 💬 4. Live Chat (Opsional - Advanced)

Jika ingin lebih responsif, bisa tambah live chat:

**Opsi A: Crisp / Tawk.to / Intercom**
- Gratis atau murah
- Widget floating di dashboard user
- Anda bisa balas dari HP

**Opsi B: Build Sendiri**
- Lebih kompleks tapi terintegrasi
- Bisa pakai WebSocket

---

### 📊 5. Knowledge Base / FAQ

Sebelum user submit ticket, arahkan ke FAQ dulu:

```
┌─────────────────────────────────────────────┐
│  📚 Pusat Bantuan                           │
├─────────────────────────────────────────────┤
│                                             │
│  🔍 Cari solusi...                          │
│                                             │
│  📌 ARTIKEL POPULER                         │
│  ├─ Cara scan QR WhatsApp                   │
│  ├─ Bot tidak merespon, apa yang harus...   │
│  ├─ Cara upgrade paket                      │
│  └─ Cara membuat broadcast                  │
│                                             │
│  📂 KATEGORI                                │
│  ├─ 🔌 Koneksi WhatsApp                     │
│  ├─ 🤖 Pengaturan Bot                       │
│  ├─ 💰 Pembayaran & Billing                 │
│  └─ 📱 Fitur Lainnya                        │
│                                             │
│  ❓ Tidak menemukan jawaban?                │
│  [    Hubungi Support    ]                  │
│                                             │
└─────────────────────────────────────────────┘
```

---

### 📈 6. SLA & Response Time Target

Tentukan target response time berdasarkan plan:

| Priority | Gratis | Hemat | Pro | Enterprise |
|----------|--------|-------|-----|------------|
| 🔴 Urgent | 48 jam | 24 jam | 4 jam | 1 jam |
| 🟡 High | 72 jam | 48 jam | 12 jam | 4 jam |
| 🟢 Normal | Best effort | 72 jam | 24 jam | 8 jam |

---

### 📋 Ringkasan Fitur Support

| Fitur | Prioritas | Deskripsi |
|-------|-----------|-----------|
| 🎫 Ticket System | ⭐⭐⭐ WAJIB | Form submit masalah + tracking |
| 📧 Email Notif | ⭐⭐⭐ WAJIB | Notif ke email admin |
| 📲 Telegram Notif | ⭐⭐⭐ RECOMMENDED | Real-time notif ke HP |
| 🚨 Auto-detection | ⭐⭐ BAGUS | Deteksi masalah otomatis |
| 📚 Knowledge Base | ⭐⭐ BAGUS | FAQ untuk self-help |
| 💬 Live Chat | ⭐ OPSIONAL | Untuk support premium |

---

## ✅ Keputusan Final

Berdasarkan diskusi, berikut keputusan yang sudah ditetapkan:

| No | Pertanyaan | Keputusan |
|----|------------|-----------|
| 1 | Paket | 4 tier: Gratis, Hemat (99k), Pro (249k), Enterprise (500k+) |
| 2 | Trial Period | ✅ 7 hari trial Pro |
| 3 | Saat Expired | Grace Period 3 hari, lalu Hard Lock (data aman 30 hari) |
| 4 | Payment Gateway | Manual Transfer dulu, lalu Midtrans/Xendit |
| 5 | Team/Multi-user | ✅ Ya, sesuai limit per paket |
| 6 | Promo Code | ✅ Ya, untuk launch & referral |

---

## 🗓️ Timeline Implementasi Final

### 📅 Phase 1: Foundation (Minggu 1-2)

**Database & Core Models**
- [ ] Migration: `plans` table
- [ ] Migration: `subscriptions` table  
- [ ] Migration: `payments` table
- [ ] Migration: `usage_records` table
- [ ] Migration: `promo_codes` table
- [ ] Migration: `support_tickets` table
- [ ] Migration: `admin_users` table
- [ ] Model: Plan, Subscription, Payment, UsageRecord
- [ ] Seeder: Default plans (Gratis, Hemat, Pro, Enterprise)
- [ ] Helper functions: `hasFeature()`, `canUse()`, `getQuota()`

**Middleware & Gates**
- [ ] Middleware: `CheckSubscription`
- [ ] Middleware: `CheckFeatureAccess`
- [ ] Middleware: `TrackUsage`

---

### 📅 Phase 2: Subscription Logic (Minggu 3-4)

**Subscription Service**
- [ ] `SubscriptionService::create()`
- [ ] `SubscriptionService::upgrade()`
- [ ] `SubscriptionService::downgrade()`
- [ ] `SubscriptionService::cancel()`
- [ ] `SubscriptionService::renew()`

**Expiry & Lock Logic**
- [ ] Cron: Check expiring subscriptions (daily)
- [ ] Cron: Send reminder email (7 days, 3 days, 1 day before)
- [ ] Cron: Lock expired accounts (after grace period)
- [ ] Grace period logic (3 days)

**Usage Tracking**
- [ ] Track AI messages per month
- [ ] Track broadcast count per month
- [ ] Track contact count
- [ ] Track KB storage size
- [ ] Reset monthly counters

---

### 📅 Phase 3: Super Admin Panel (Minggu 5-6)

**Admin Authentication**
- [ ] Route: `/superadmin/login`
- [ ] Admin guard & middleware
- [ ] 2FA setup (optional tapi recommended)

**Admin Dashboard**
- [ ] Overview: Total tenants, revenue, new signups
- [ ] Tenant list with search & filter
- [ ] Tenant detail page
- [ ] Revenue & payment history
- [ ] Plan management CRUD

**Admin Actions**
- [ ] Suspend/activate tenant
- [ ] Login As (impersonate)
- [ ] Manual subscription activation
- [ ] Approve/reject manual transfers

---

### 📅 Phase 4: Payment System (Minggu 7-8)

**Manual Transfer (MVP)**
- [ ] Checkout page with bank details
- [ ] Upload bukti transfer form
- [ ] Admin approval queue
- [ ] Email notification on approval

**Payment Gateway (Later)**
- [ ] Midtrans/Xendit integration
- [ ] Webhook handler
- [ ] Auto-activation on payment success
- [ ] Invoice generation

**Promo Codes**
- [ ] Promo code CRUD (admin)
- [ ] Apply promo code on checkout
- [ ] Track usage

---

### 📅 Phase 5: Support System (Minggu 9-10)

**Ticket System**
- [ ] Submit ticket form (user)
- [ ] Ticket list & detail (user)
- [ ] Ticket management (admin)
- [ ] Reply system (both sides)
- [ ] Close/resolve ticket

**Notifications**
- [ ] Email notification on new ticket
- [ ] Telegram bot notification
- [ ] Email notification on reply

**Health Monitoring**
- [ ] Cron: Check WA device health
- [ ] Cron: Check error rate
- [ ] Alert on anomaly

---

### 📅 Phase 6: User-Facing UI (Minggu 11-12)

**Pricing & Checkout**
- [ ] Public pricing page
- [ ] Checkout flow
- [ ] Payment confirmation page

**Subscription Management**
- [ ] Current plan display
- [ ] Usage meters (quota terpakai)
- [ ] Upgrade/downgrade flow
- [ ] Billing history

**Feature Gates UI**
- [ ] Upgrade prompt modals
- [ ] Expired account overlay
- [ ] Warning banners (almost expired)

---

### 📅 Phase 7: Polish & Launch (Minggu 13-14)

**Testing**
- [ ] Test all subscription flows
- [ ] Test payment flows
- [ ] Test expiry & lock
- [ ] Test all feature gates

**Documentation**
- [ ] User guide: Cara berlangganan
- [ ] User guide: Cara upgrade
- [ ] Admin guide: Manage subscribers

**Launch Prep**
- [ ] Early bird promo setup
- [ ] Announcement to existing users
- [ ] Go live!

---

## 📊 Ringkasan Sistem

```
┌─────────────────────────────────────────────────────────────────────┐
│                    REPLYAI SUBSCRIPTION SYSTEM                      │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐               │
│  │    USER     │   │   SYSTEM    │   │ SUPER ADMIN │               │
│  └──────┬──────┘   └──────┬──────┘   └──────┬──────┘               │
│         │                 │                 │                       │
│         ▼                 ▼                 ▼                       │
│  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐               │
│  │ • Dashboard │   │ • Cron Jobs │   │ • Dashboard │               │
│  │ • Upgrade   │   │ • Gates     │   │ • Tenants   │               │
│  │ • Checkout  │   │ • Tracking  │   │ • Revenue   │               │
│  │ • Support   │   │ • Alerts    │   │ • Tickets   │               │
│  └─────────────┘   └─────────────┘   └─────────────┘               │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │                      DATABASE                               │   │
│  ├──────────┬──────────┬──────────┬──────────┬────────────────┤   │
│  │  plans   │  subs    │ payments │  usage   │ support_tickets│   │
│  └──────────┴──────────┴──────────┴──────────┴────────────────┘   │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📦 Deliverables (Yang Akan Dibuat)

### Backend
| File/Folder | Deskripsi |
|-------------|-----------|
| `app/Models/Plan.php` | Model paket langganan |
| `app/Models/Subscription.php` | Model subscription tenant |
| `app/Models/Payment.php` | Model pembayaran |
| `app/Models/UsageRecord.php` | Model tracking penggunaan |
| `app/Models/SupportTicket.php` | Model ticket support |
| `app/Services/SubscriptionService.php` | Business logic subscription |
| `app/Services/UsageTrackingService.php` | Tracking quota/limit |
| `app/Http/Middleware/CheckSubscription.php` | Middleware cek langganan |
| `app/Http/Controllers/SuperAdmin/*` | Controllers super admin |
| `database/migrations/*` | Semua migrations |
| `database/seeders/PlanSeeder.php` | Seeder paket default |

### Frontend (Views)
| File/Folder | Deskripsi |
|-------------|-----------|
| `resources/views/superadmin/*` | Dashboard super admin |
| `resources/views/pages/pricing/*` | Halaman harga public |
| `resources/views/pages/checkout/*` | Flow checkout |
| `resources/views/pages/subscription/*` | Manage subscription |
| `resources/views/pages/support/*` | Ticket support |
| `resources/views/components/upgrade-modal.blade.php` | Modal upgrade |
| `resources/views/components/quota-meter.blade.php` | Meter usage |

### Routes
| Route | Deskripsi |
|-------|-----------|
| `/pricing` | Halaman pricing public |
| `/checkout/{plan}` | Checkout flow |
| `/subscription` | Manage subscription |
| `/support` | Ticket support |
| `/superadmin/*` | Semua route super admin |

---

## 📝 Referensi

- [Botpenguin Pricing](https://botpenguin.com/pricing)
- [Cekat AI Pricing](https://cekat.ai/pricing)
- [Laravel Cashier](https://laravel.com/docs/cashier) - untuk inspirasi arsitektur

---

## 🚀 Next Steps

1. **Review dokumen ini** - pastikan semua sudah sesuai
2. **Mulai Phase 1** - buat migrations & models
3. **Iterasi** - kita bisa adjust sambil jalan

---

*Dokumen finalized: 17 Januari 2026*
*Estimasi total: 12-14 minggu untuk full implementation*

