# Tier Feature Mapping - REPLYAI

## Ringkasan Fitur per Tier

### 🟢 UMKM (Starter)
**Harga:** Rp 0 - 99.000/bulan
**Target:** Warung online, UMKM, personal brand

#### Fitur:
- ✅ WhatsApp Inbox (1 device)
- ✅ Instagram DM (1 account)
- ✅ Kontak (500)
- ✅ Basis Pengetahuan (50 artikel)
- ✅ Atur Balasan (10 rules)
- ✅ Balasan Cepat (20 template)
- ✅ Statistik Dasar
- ✅ Riwayat (7 hari)
- ✅ Tugas Saya

#### Tidak ada:
- ❌ Segment
- ❌ Chat Automation
- ❌ Broadcast
- ❌ AI Analytics
- ❌ Realtime Dashboard
- ❌ Team/Agent
- ❌ Instagram Comments
- ❌ Web Widget
- ❌ API Access

---

### 🔵 BUSINESS (Growth)
**Harga:** Rp 500.000 - 1.500.000/bulan
**Target:** Bisnis menengah, tim kecil, e-commerce

#### Fitur tambahan:
- ✅ Semua fitur UMKM (limit lebih tinggi)
- ✅ Segment (grouping kontak)
- ✅ Chat Automation
- ✅ Broadcast messaging
- ✅ AI Analytics
- ✅ Realtime Dashboard
- ✅ Kualitas Chat (sentiment)
- ✅ Chat Assignment
- ✅ Agent Takeover
- ✅ Kontak (5.000)

#### Tidak ada:
- ❌ Pesan Terjadwal (Sequences)
- ❌ Instagram Comments
- ❌ Web Widget
- ❌ Perbandingan Laporan
- ❌ API Access

---

### 🟣 ENTERPRISE (Custom)
**Harga:** Rp 3.500.000+/bulan
**Target:** Perusahaan besar, multi-cabang

#### Fitur tambahan:
- ✅ Semua fitur Business
- ✅ Pesan Terjadwal (Drip campaigns)
- ✅ Instagram Comments reply
- ✅ Web Widget
- ✅ Perbandingan Laporan (WoW, MoM)
- ✅ Jadwal Laporan (email)
- ✅ Template Laporan Custom
- ✅ API Access & Webhook
- ✅ Multi-user Roles
- ✅ Audit Logs
- ✅ Custom Integration
- ✅ White-label option
- ✅ Dedicated Support
- ✅ Unlimited kontak

---

## Menu Sidebar per Tier

### UMKM (4 Menu)
1. Overview
2. Chat (WA, IG, Kontak, Tugas)
3. AI Setup (KB, Rules, Quick Reply)
4. Statistik
5. Integrasi

### BUSINESS (6 Menu)
1. Overview
2. Chat (+Segment)
3. AI Setup (+Automation)
4. Promosi (Broadcast)
5. Laporan (+AI Analytics, Realtime, Quality)
6. Team (Assignment)
7. Integrasi

### ENTERPRISE (8+ Menu)
1. Overview
2. Chat (+Segment, Comments, Web Widget)
3. AI Setup (Lengkap)
4. Promosi (+Sequences)
5. Laporan (Lengkap + Custom)
6. Team (Lengkap)
7. Advanced (API, Webhook, Audit)
8. Integrasi

---

## Limit per Tier

| Fitur | UMKM | Business | Enterprise |
|-------|------|----------|------------|
| WA Device | 1 | 3 | Unlimited |
| IG Account | 1 | 3 | Unlimited |
| Kontak | 500 | 5.000 | Unlimited |
| KB Articles | 50 | 500 | Unlimited |
| Auto Reply Rules | 10 | 50 | Unlimited |
| Quick Replies | 20 | 100 | Unlimited |
| Broadcast/month | - | 10 | Unlimited |
| Sequences | - | - | Unlimited |
| Team Members | - | 5 | Unlimited |
| API Calls/day | - | - | 10.000 |

---

## Implementasi

### 1. Database Migration
```sql
-- Update plans table
UPDATE plans SET tier = 'umkm' WHERE slug IN ('gratis', 'hemat', 'trial-pro');
UPDATE plans SET tier = 'business' WHERE slug IN ('pro', 'business');
UPDATE plans SET tier = 'enterprise' WHERE slug IN ('enterprise', 'custom');
```

### 2. Middleware Check
```php
// routes/web.php
Route::middleware(['tier:business'])->group(function () {
    // Business+ routes
});

Route::middleware(['tier:enterprise'])->group(function () {
    // Enterprise only routes
});
```

### 3. Sidebar Logic
```php
// UMKM: Basic only
// Business: Basic + Growth features  
// Enterprise: Everything
```

---

## Catatan

- UMKM tidak punya fitur "Promosi" untuk mencegah spam
- Web Widget hanya untuk Enterprise (terlalu teknis)
- Instagram Comments hanya Enterprise (jarang dipakai UMKM)
- API Access hanya Enterprise (security reason)
