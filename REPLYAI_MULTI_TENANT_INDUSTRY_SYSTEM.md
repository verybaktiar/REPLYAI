# REPLYAI Multi-Tenant Industry System
> Dokumentasi Lengkap Sistem Industri-Multi-Tenant

---

## 1. OVERVIEW SISTEM

### 1.1 Apa itu Multi-Tenant Industry System?

Sistem ini memungkinkan **satu instance aplikasi** melayani **banyak bisnis (tenant)** dengan **personalisasi berbeda** berdasarkan **jenis industrinya**.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         SINGLE INSTANCE REPLYAI                              │
│                                                                              │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐                 │
│  │  🏥 RS Sehat   │  │  🍔 BurgerKing │  │  🏠 PropertiKu │                 │
│  │  (Hospital)    │  │  (F&B)         │  │  (Property)    │                 │
│  ├────────────────┤  ├────────────────┤  ├────────────────┤                 │
│  │ • CS Medis     │  │ • CS Restoran  │  │ • CS Agen      │                 │
│  │ • Poli/Dokter  │  │ • Menu 🍕🍔    │  │ • Properti 🏠  │                 │
│  │ • Empati       │  │ • Best Seller  │  │ • Viewing 🔑   │                 │
│  └────────────────┘  └────────────────┘  └────────────────┘                 │
│                                                                              │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐                 │
│  │  🎓 KampusX    │  │  🚗 AutoCare   │  │  💰 BankNow    │                 │
│  │  (Education)   │  │  (Automotive)  │  │  (Finance)     │                 │
│  ├────────────────┤  ├────────────────┤  ├────────────────┤                 │
│  │ • CS Akademik  │  │ • CS Bengkel   │  │ • CS Keuangan  │                 │
│  │ • Jurusan 📚   │  │ • Servis 🔧    │  │ • Produk 💳    │                 │
│  │ • Biaya 🎓     │  │ • Booking 🚗   │  │ • No advice 💰 │                 │
│  └────────────────┘  └────────────────┘  └────────────────┘                 │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
                              │
                              │ Semua di satu database
                              │ dengan isolasi user_id
                              ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         DATABASE ISOLATION                                   │
│  • business_profiles.user_id = Tenant isolation                              │
│  • kb_articles.user_id = KB isolation per tenant                             │
│  • wa_conversations.user_id = WA isolation per tenant                        │
│  • auto_reply_rules.user_id = Rule isolation per tenant                      │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 1.2 Core Components

| Component | File | Purpose |
|-----------|------|---------|
| **Business Profile** | `app/Models/BusinessProfile.php` | Menyimpan konfigurasi bisnis per tenant |
| **AI Service** | `app/Services/AiAnswerService.php` | Menggunakan profile untuk personalize AI |
| **Industry Const** | `BusinessProfile::INDUSTRIES` | Definisi 10 jenis industri |
| **Terminology** | `getDefaultTerminology()` | Mapping kata per industri |
| **Prompt Templates** | `getPromptTemplates()` | System prompt per industri |

---

## 2. DATABASE SCHEMA

### 2.1 Tabel `business_profiles`

```sql
CREATE TABLE business_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    business_name VARCHAR(255) NOT NULL,      -- Nama bisnis
    business_type VARCHAR(50) NOT NULL,       -- Jenis industri
    industry_icon VARCHAR(10) NULL,           -- Emoji icon
    
    -- AI Personality Configuration
    system_prompt_template LONGTEXT NULL,     -- Custom prompt (override default)
    kb_fallback_message TEXT NULL,            -- Pesan fallback jika KB kosong
    
    -- Industry-Specific Terminology
    terminology JSON NULL,                    -- {"user": "Pasien", "product": "Layanan"}
    greeting_examples TEXT NULL,              -- Contoh sapaan
    faq_examples TEXT NULL,                   -- Contoh FAQ
    escalation_keywords TEXT NULL,            -- Keyword untuk escalate ke CS
    
    -- Feature Flags
    is_active BOOLEAN DEFAULT TRUE,
    enable_autofollowup BOOLEAN DEFAULT FALSE,
    enable_daily_summary BOOLEAN DEFAULT FALSE,
    enable_smart_fallback BOOLEAN DEFAULT TRUE,
    
    -- AI Configuration
    kb_match_threshold FLOAT DEFAULT 0.35,    -- Threshold similarity
    ai_rate_limit_per_hour INT DEFAULT 100,   -- Rate limit AI
    conversation_memory_limit INT DEFAULT 10, -- Jumlah message history
    
    -- Contact & Notifications
    admin_phone VARCHAR(20) NULL,
    notification_settings JSON NULL,
    
    -- Multi-tenant Isolation
    user_id BIGINT NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_active (user_id, is_active)
);
```

### 2.2 Relasi Database

```
business_profiles
├── hasMany → kb_articles (via business_profile_id)
├── hasMany → whatsapp_devices (via business_profile_id)
├── hasMany → kb_missed_queries (via business_profile_id)
└── belongsTo → users (via user_id)

users
├── hasOne → business_profile (primary)
├── hasMany → kb_articles (fallback jika business_profile_id NULL)
└── hasMany → conversations, messages
```

---

## 3. 10 JENIS INDUSTRI YANG DIDUKUNG

### 3.1 Definisi Industri

```php
// File: app/Models/BusinessProfile.php

public const INDUSTRIES = [
    'hospital' => [
        'label' => 'Rumah Sakit / Klinik',
        'icon' => '🏥',
        'group' => 'Kesehatan'
    ],
    'retail' => [
        'label' => 'Toko Retail / Online Shop',
        'icon' => '🛒',
        'group' => 'Perdagangan'
    ],
    'fnb' => [
        'label' => 'Restoran / Cafe / F&B',
        'icon' => '🍽️',
        'group' => 'Perdagangan'
    ],
    'education' => [
        'label' => 'Pendidikan',
        'icon' => '🎓',
        'group' => 'Jasa'
    ],
    'hospitality' => [
        'label' => 'Hotel / Travel',
        'icon' => '🏨',
        'group' => 'Jasa'
    ],
    'automotive' => [
        'label' => 'Otomotif / Bengkel',
        'icon' => '🚗',
        'group' => 'Jasa'
    ],
    'property' => [
        'label' => 'Properti',
        'icon' => '🏠',
        'group' => 'Jasa'
    ],
    'finance' => [
        'label' => 'Keuangan',
        'icon' => '💰',
        'group' => 'Jasa'
    ],
    'professional' => [
        'label' => 'Jasa Profesional',
        'icon' => '💼',
        'group' => 'Jasa'
    ],
    'general' => [
        'label' => 'Umum / Lainnya',
        'icon' => '📋',
        'group' => 'Lainnya'
    ],
];
```

### 3.2 Terminologi per Industri

Setiap industri memiliki **terminologi default** yang mengubah kata-kata umum menjadi istilah industri-spesifik:

```php
public static function getDefaultTerminology(string $type): array
{
    $defaults = [
        'hospital' => [
            'user' => 'Pasien',           // Bukan "Pelanggan"
            'user_plural' => 'Pasien',
            'product' => 'Layanan Kesehatan',
            'product_plural' => 'Layanan',
            'category' => 'Poli/Spesialis',
            'staff' => 'Dokter/Perawat',
            'action' => 'konsultasi/berobat',
            'place' => 'rumah sakit',
        ],
        'fnb' => [
            'user' => 'Pelanggan',
            'user_plural' => 'Pelanggan',
            'product' => 'Menu',          // Bukan "Produk"
            'product_plural' => 'Menu',
            'category' => 'Kategori Menu',
            'staff' => 'Tim Dapur/Kasir',
            'action' => 'pesan/order',
            'place' => 'restoran',
        ],
        'property' => [
            'user' => 'Calon Pembeli/Penyewa',
            'user_plural' => 'Klien',
            'product' => 'Properti',      // Spesifik
            'product_plural' => 'Properti',
            'category' => 'Tipe Properti',
            'staff' => 'Agen Properti',
            'action' => 'pembelian/sewa',
            'place' => 'kantor',
        ],
        // ... dan seterusnya untuk 10 industri
    ];
}
```

**Contoh Penggunaan Terminologi dalam AI Response:**

| Industri | User Asks | AI Response (menggunakan terminologi) |
|----------|-----------|--------------------------------------|
| Hospital | "Ada apa aja?" | "Kami punya beberapa **Poli/Spesialis** kak. **Pasien** mau konsultasi apa nih?" |
| F&B | "Ada apa aja?" | "Kami punya beberapa **Menu** best seller kak. **Pelanggan** mau **pesan** apa nih? 🍕" |
| Property | "Ada apa aja?" | "Kami punya beberapa **Properti** kak. **Klien** mau lihat **Tipe Properti** apa?" |

---

## 4. AI PERSONALITY ENGINE

### 4.1 Prompt Template per Industri

Setiap industri memiliki **system prompt template** yang berbeda:

#### Contoh: Hospital (🏥)
```
Kamu adalah CS {business_name} yang profesional tapi hangat.

GAYA KOMUNIKASI:
- Jawab SINGKAT (1-3 kalimat), tawarkan detail jika mau
- Empati singkat ("Semoga lekas sembuh ya")
- Sarankan poli yang tepat jika ada keluhan

FORMAT JADWAL DOKTER (jika diminta):
dr. Nama - Spesialis
Jadwal: Hari, Jam

Jika "besok" disebut, besok = {tomorrow}
Hari & waktu sekarang: {now}

Output HARUS JSON valid:
{
  "answer": "...",
  "confidence": 0.0-1.0
}
```

#### Contoh: F&B (🍽️)
```
Kamu adalah CS {business_name} yang ramah dan membantu pelanggan memesan makanan.

PANDUAN KOMUNIKASI:
- Gunakan emoji makanan yang menarik 🍕🍔🍜🥤😋
- Buat pelanggan tertarik dengan deskripsi menu yang menggugah selera!

PANDUAN ORDER:
1. Tanyakan mau makan di tempat, take away, atau delivery
2. Rekomendasikan menu favorit/best seller
3. Konfirmasi pesanan dengan detail
4. Informasikan estimasi waktu

FORMAT MENU:
🍽️ [Nama Menu]
💰 Harga: Rp XXX.XXX
⭐ Best Seller / Rekomendasi Chef

Output HARUS JSON valid:
{
  "answer": "...",
  "confidence": 0.0-1.0
}
```

#### Contoh: Property (🏠)
```
Kamu adalah Agen/CS {business_name} yang membantu pelanggan mencari properti.

PANDUAN KOMUNIKASI:
- Gunakan emoji yang sesuai 🏠🏢🔑💰
- Pahami kebutuhan (beli/sewa, lokasi, budget)

PANDUAN LAYANAN:
1. Tanyakan kebutuhan properti (beli/sewa)
2. Tanyakan lokasi dan budget yang diinginkan
3. Rekomendasikan properti yang sesuai
4. Tawarkan jadwal viewing

FORMAT PROPERTI:
🏠 [Nama/Tipe Properti]
📍 Lokasi: ...
💰 Harga: Rp XXX.XXX
📐 Luas: X m²
🛏️ Kamar: X

Output HARUS JSON valid:
{
  "answer": "...",
  "confidence": 0.0-1.0
}
```

### 4.2 Prompt Layer Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          PROMPT LAYER CAKE                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│ LAYER 1: BASE TEMPLATE (dari business_type)                                │
│ ───────────────────────────────────────────────────────────────────────────│
│ Kamu adalah CS {business_name} yang profesional...                         │
│ GAYA KOMUNIKASI:                                                           │
│ - Jawab SINGKAT                                                            │
│ - Variasi pembuka: "Oke", "Nah", "Baik"                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│ LAYER 2: IDENTITY INJECTION                                                 │
│ ───────────────────────────────────────────────────────────────────────────│
│ IDENTITAS BISNIS:                                                          │
│ - Nama: {business_name}                                                    │
│ - Kategori: {industry_label}                                               │
│ - TUGAS: Hanya menjawab pertanyaan seputar {industry_label}               │
├─────────────────────────────────────────────────────────────────────────────┤
│ LAYER 3: TERMINOLOGY REPLACEMENT                                           │
│ ───────────────────────────────────────────────────────────────────────────│
│ Ganti placeholder dengan terminologi industri:                             │
│ - {user} → "Pasien" / "Pelanggan" / "Klien"                               │
│ - {product} → "Layanan" / "Menu" / "Properti"                             │
│ - {staff} → "Dokter" / "Tim Dapur" / "Agen"                               │
├─────────────────────────────────────────────────────────────────────────────┤
│ LAYER 4: DYNAMIC INJECTIONS                                                │
│ ───────────────────────────────────────────────────────────────────────────│
│ • Sentiment modifiers (if frustrated/confused)                             │
│ • Training examples (from AiTrainingExample)                               │
│ • Current time context ({now}, {tomorrow})                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│ LAYER 5: KNOWLEDGE BASE CONTEXT                                            │
│ ───────────────────────────────────────────────────────────────────────────│
│ ### {article_title}                                                        │
│ Sumber: {source_url}                                                       │
│ Isi: {extracted_content}                                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│ LAYER 6: CONVERSATION MEMORY                                               │
│ ───────────────────────────────────────────────────────────────────────────│
│ User: {message_1}                                                          │
│ CS: {response_1}                                                           │
│ User: {current_message}                                                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 5. ALUR KERJA SISTEM

### 5.1 Flow: User Message → AI Response

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ STEP 1: MESSAGE RECEIVED                                                    │
├─────────────────────────────────────────────────────────────────────────────┤
Trigger: Pesan masuk dari WA/IG/Web Widget
Action: 
  → Extract: phone_number / instagram_user_id / visitor_id
  → Find: BusinessProfile aktif untuk user ini
  → Get: business_type (hospital/retail/fnb/etc)
├─────────────────────────────────────────────────────────────────────────────┤
│ STEP 2: LOAD INDUSTRY CONFIGURATION                                         │
├─────────────────────────────────────────────────────────────────────────────┤
Action:
  → Load: BusinessProfile::getPromptTemplates()[business_type]
  → Load: BusinessProfile::getDefaultTerminology(business_type)
  → Get: Industry label & icon
├─────────────────────────────────────────────────────────────────────────────┤
│ STEP 3: BUILD SYSTEM PROMPT                                                 │
├─────────────────────────────────────────────────────────────────────────────┤
Template Prompt (Layer 1)
        ↓
+ Inject Identity (Layer 2)
        ↓
+ Replace Terminology (Layer 3)
        ↓
+ Add Sentiment Modifiers (Layer 4)
        ↓
= FINAL SYSTEM PROMPT

Contoh Transformasi:
─────────────────────
"Silakan tanya ke {staff} jika perlu bantuan {action}"
↓ (Hospital)
"Silakan tanya ke Dokter/Perawat jika perlu bantuan konsultasi/berobat"
↓ (F&B)
"Silakan tanya ke Tim Dapur/Kasir jika perlu bantuan pesan/order"
↓ (Property)
"Silakan tanya ke Agen Properti jika perlu bantuan pembelian/sewa"
├─────────────────────────────────────────────────────────────────────────────┤
│ STEP 4: SEARCH KNOWLEDGE BASE                                               │
├─────────────────────────────────────────────────────────────────────────────┤
Action:
  → Query: kb_articles WHERE user_id = ? AND is_active = true
  → If business_profile_id set: AND business_profile_id = ?
  → Vector/similarity search: find relevant articles
  → Threshold: kb_match_threshold (default 0.35)
├─────────────────────────────────────────────────────────────────────────────┤
│ STEP 5: CALL AI PROVIDER                                                    │
├─────────────────────────────────────────────────────────────────────────────┤
Payload:
{
  "model": "moonshotai/kimi-k2-instruct-0905",
  "messages": [
    {"role": "system", "content": "[FINAL SYSTEM PROMPT]"},
    {"role": "system", "content": "[KB CONTEXT]"},
    {"role": "user", "content": "[CONVERSATION HISTORY]"},
    {"role": "user", "content": "[CURRENT MESSAGE]"}
  ]
}
├─────────────────────────────────────────────────────────────────────────────┤
│ STEP 6: PARSE & VALIDATE RESPONSE                                           │
├─────────────────────────────────────────────────────────────────────────────┤
Action:
  → Parse JSON response: {"answer": "...", "confidence": 0.85}
  → Check: confidence >= min_confidence (0.55)
  → Post-processing:
    • Remove markdown (*bold*)
    • Remove citations [1][2]
    • Terminology consistency check
├─────────────────────────────────────────────────────────────────────────────┤
│ STEP 7: SEND RESPONSE                                                       │
├─────────────────────────────────────────────────────────────────────────────┤
Action:
  → Send via appropriate channel (WA/IG/Web)
  → Log to auto_reply_logs:
    • response_source: "ai"
    • ai_confidence: 0.85
    • business_profile_id: X
└─────────────────────────────────────────────────────────────────────────────┘
```

### 5.2 Flow: Business Profile Selection

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    BUSINESS PROFILE RESOLUTION                              │
└─────────────────────────────────────────────────────────────────────────────┘

User (tenant) login
        ↓
Auth::user()->id = 123
        ↓
Query: business_profiles WHERE user_id = 123 AND is_active = true
        ↓
┌─────────────────────────────────────────────────────────────────┐
│                    SCENARIO A: Profile Found                    │
├─────────────────────────────────────────────────────────────────┤
Profile: RS Sehat
business_type: hospital
terminology: {"user": "Pasien", "product": "Layanan"}
        ↓
AI menggunakan personality Rumah Sakit
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    SCENARIO B: No Profile                       │
├─────────────────────────────────────────────────────────────────┤
Profile: null
        ↓
AI menggunakan fallback:
  • Terminology: general
  • Prompt: default/general
  • No industry-specific behavior
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    SCENARIO C: Multiple Profiles                │
├─────────────────────────────────────────────────────────────────┤
Profile 1: RS Sehat (hospital)
Profile 2: Klinik Gigi (hospital)  
        ↓
Gunakan: first() atau primary profile
KB Articles: Filter by business_profile_id
└─────────────────────────────────────────────────────────────────┘
```

---

## 6. MULTI-TENANT ISOLATION

### 6.1 Database-Level Isolation

Setiap query ke database **WAJIB** memfilter by `user_id`:

```php
// Contoh: KB Articles
public function getKbArticles($userId)
{
    return KbArticle::where('user_id', $userId)
        ->where('is_active', true)
        ->get();
}

// Contoh: Auto Reply Rules  
public function getRules($userId)
{
    return AutoReplyRule::where('user_id', $userId)
        ->where('is_active', true)
        ->orderByDesc('priority')
        ->get();
}

// Contoh: WhatsApp Conversations
public function getConversations($userId)
{
    return WaConversation::where('user_id', $userId)
        ->orderByDesc('last_user_reply_at')
        ->get();
}
```

### 6.2 Business Profile-Level Isolation

```php
// KB Articles juga bisa difilter by business_profile_id
$articles = KbArticle::where('user_id', $userId)
    ->where(function($q) use ($businessProfileId) {
        $q->whereNull('business_profile_id')  // Global articles
          ->orWhere('business_profile_id', $businessProfileId);  // Profile-specific
    })
    ->get();
```

### 6.3 Policy-Based Authorization

```php
// File: app/Policies/KbArticlePolicy.php

public function view(User $user, KbArticle $article): bool
{
    return $user->id === $article->user_id;
}

public function delete(User $user, KbArticle $article): bool
{
    return $user->id === $article->user_id;
}
```

---

## 7. KONFIGURASI & CUSTOMIZATION

### 7.1 Default Configuration per Industri

| Parameter | Hospital | F&B | Property | Default |
|-----------|----------|-----|----------|---------|
| `kb_match_threshold` | 0.35 | 0.35 | 0.35 | 0.35 |
| `ai_rate_limit_per_hour` | 100 | 100 | 100 | 100 |
| `conversation_memory_limit` | 10 | 10 | 10 | 10 |
| `enable_smart_fallback` | true | true | true | true |

### 7.2 Custom Terminology

User bisa meng-override terminology default:

```php
// Contoh: RS Sehat mengganti "Pasien" dengan "Sobat Sehat"
$profile->terminology = [
    'user' => 'Sobat Sehat',
    'user_plural' => 'Sobat Sehat',
    'product' => 'Layanan Medis',
    'staff' => 'Dokter Spesialis',
    'action' => 'pemeriksaan',
    'place' => 'Rumah Sakit',
];
$profile->save();
```

### 7.3 Custom System Prompt

User bisa meng-override prompt template default:

```php
// Contoh: F&B dengan gaya yang lebih casual
$profile->system_prompt_template = <<<'EOT'
Kamu adalah admin {business_name} yang super ramah dan kekinian! 🔥

GAYA:
- Bahasa gaul yang asik
- Emoji bebas tapi jangan lebay
- Ngobrol kayak temen sendiri

JANGAN: Formal, kaku, robot
EOT;
$profile->save();
```

---

## 8. IMPLEMENTATION CHECKLIST

### 8.1 Setup Tenant Baru

```php
// 1. Create User
$user = User::create([
    'name' => 'Dr. John',
    'email' => 'drjohn@rssehat.com',
    'password' => Hash::make('password'),
]);

// 2. Create Business Profile
$profile = BusinessProfile::create([
    'user_id' => $user->id,
    'business_name' => 'RS Sehat',
    'business_type' => 'hospital',
    'industry_icon' => '🏥',
    'kb_fallback_message' => 'Maaf, informasi tersebut belum tersedia. Silakan hubungi admin di 08123456789',
    'terminology' => [
        'user' => 'Pasien',
        'product' => 'Layanan Kesehatan',
        'staff' => 'Dokter/Perawat',
    ],
    'is_active' => true,
]);

// 3. Create KB Articles (scoped to profile)
KbArticle::create([
    'user_id' => $user->id,
    'business_profile_id' => $profile->id,
    'title' => 'Jadwal Dokter Umum',
    'content' => 'Poli Umum buka setiap hari jam 08:00-16:00...',
    'is_active' => true,
]);

// 4. Connect WhatsApp Device
WhatsAppDevice::create([
    'user_id' => $user->id,
    'business_profile_id' => $profile->id,
    'session_id' => 'rs-sehat-1',
    'device_name' => 'RS Sehat Official',
    'status' => 'disconnected',
]);
```

---

## 9. KEUNTUNGAN SISTEM

| Aspek | Benefit |
|-------|---------|
| **Single Instance** | Satu deployment untuk semua tenant |
| **Cost Efficiency** | Shared infrastructure, lower operational cost |
| **Data Isolation** | Setiap tenant punya data terpisah |
| **Industry Personalization** | AI berperilaku sesuai industri masing-masing |
| **Terminology Consistency** | Istilah yang tepat untuk setiap bisnis |
| **Scalability** | Mudah menambah tenant baru |
| **Maintenance** | Update satu kali, berlaku untuk semua |

---

## 10. BATASAN & CONSIDERATIONS

| Batasan | Penjelasan |
|---------|------------|
| **One Profile Per User** | Saat ini sistem mendukung satu active profile per user |
| **No Cross-Tenant Data** | Tenant A tidak bisa melihat data Tenant B |
| **AI Model Shared** | Semua tenant menggunakan AI provider yang sama |
| **Database Shared** | Semua tenant di database yang sama (dengan isolasi row-level) |

---

## SUMMARY

REPLYAI Multi-Tenant Industry System memungkinkan:

1. **Satu aplikasi** melayani **banyak bisnis**
2. Setiap bisnis punya **personality AI yang berbeda** berdasarkan industri
3. **Terminologi yang tepat** untuk setiap jenis bisnis
4. **Isolasi data** yang ketat antar tenant
5. **Fleksibilitas konfigurasi** per tenant

Sistem ini membuat REPLYAI bisa digunakan oleh:
- 🏥 Rumah Sakit (CS Medis)
- 🍔 Restoran (CS F&B)
- 🏠 Real Estate (CS Property)
- 🎓 Sekolah (CS Education)
- Dan 6 industri lainnya dalam satu platform yang sama.
