# REPLYAI Chatbot UMKM - Technical Blueprint
> Multi-Channel AI Chatbot System for Indonesian MSMEs

---

## 1. CORE ARCHITECTURE

### 1.1 System Overview
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              CLIENT LAYER                                    │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │  WhatsApp   │  │  Instagram  │  │   Web Chat  │  │  FB/TikTok (Future) │ │
│  │  (Baileys)  │  │  (Meta API) │  │   (Widget)  │  │                     │ │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘  └─────────────────────┘ │
└─────────┼────────────────┼────────────────┼──────────────────────────────────┘
          │                │                │
          ▼                ▼                ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           GATEWAY LAYER                                      │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                    Webhook Router (Laravel API)                       │   │
│  │  • /api/whatsapp/webhook/message    (wa-service → Laravel)           │   │
│  │  • /api/whatsapp/webhook/status     (Connection status)              │   │
│  │  • /api/instagram/webhook           (Meta → Laravel)                 │   │
│  │  • /api/web/chat                    (Web Widget → Laravel)           │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          PROCESSING ENGINE                                   │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────────────┐  │
│  │  AutoReplyEngine │→ │  AiAnswerService │→ │  Multi-Provider AI Gateway  │  │
│  │  (Rule-based)   │  │  (LLM/NLP)      │  │  • MegaLLM (Primary)        │  │
│  │  • Exact match  │  │  • Prompt eng.  │  │  • SumoPod (Failover)       │  │
│  │  • Contains     │  │  • Context mgmt │  │  • OpenAI/Claude (Backup)   │  │
│  │  • Regex        │  │  • Sentiment    │  │                             │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                            DATA LAYER                                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐ │
│  │  MySQL       │  │  wa-service  │  │  Redis       │  │  External APIs   │ │
│  │  (Primary DB)│  │  (Node.js)   │  │  (Cache)     │  │  • Meta Graph    │ │
│  │  Multi-tenant│  │  Baileys     │  │  • Sessions  │  │  • Midtrans      │ │
│  │  Isolation   │  │  QR/Session  │  │  • Rate Lim  │  │  • Xendit        │ │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 1.2 Platform Integration Details

#### WhatsApp (wa-service - Node.js)
```javascript
// Architecture: Separate Node.js microservice
Library: @whiskeysockets/baileys v6.7.9
Port: 3001
Protocol: WebSocket (Baileys) + HTTP REST API

// Message Flow:
WA Server → Baileys → wa-service → Webhook → Laravel
                    ↓
              Session Store (./sessions/{sessionId}/)

// Key Endpoints (wa-service):
POST /connect              → Create/connect session
POST /send                 → Send message (text/media)
GET  /qr?sessionId={id}    → Get QR code for scanning
POST /disconnect           → Logout session

// Security:
- X-WA-Service-Key header authentication
- Group messages ignored (endsWith '@g.us')
- Own messages ignored (fromMe check)
- Auto-reconnect with 5s interval, unlimited retries
```

#### Instagram (Meta Graph API v21.0)
```php
// Webhook Verification:
GET  /api/instagram/webhook?hub_verify_token={token}
→ Validates against config('services.instagram.webhook_verify_token')

// Incoming Events:
POST /api/instagram/webhook
→ object: "instagram"
→ entry[].messaging[].message.text

// Outgoing API:
POST https://graph.instagram.com/v21.0/{igUserId}/messages
Headers: Authorization: Bearer {access_token}
Body: {
  "recipient": {"id": "{recipient_id}"},
  "message": {"text": "{message}"}
}

// OAuth Scopes Required:
instagram_business_basic
instagram_business_manage_messages
instagram_business_manage_comments

// Multi-tenancy: 
recipient.id in webhook maps to instagram_accounts.instagram_user_id
```

#### Web Chat Widget
```javascript
// API Key Authentication
Endpoint: GET /api/web/widget/{api_key}
→ Returns widget configuration

// Conversation Init
POST /api/web/chat
Body: {
  "api_key": "...",
  "visitor_id": "uuid",
  "message": "..."
}

// Long Polling
GET /api/web/poll?visitor_id={id}
→ Returns new messages since last poll
```

---

## 2. LOGIC FLOW (Trigger → Action)

### 2.1 Message Processing Pipeline

```
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 1: INGESTION                                                          │
├────────────────────────────────────────────────────────────────────────────┤
Trigger:  Webhook received (WA/IG/Web)
Action:   → Parse sender info
          → Extract message content + metadata
          → Deduplication check (instagram_message_id / wa_message_id)
          → Route to platform handler
├────────────────────────────────────────────────────────────────────────────┤
│ STEP 2: CONVERSATION RESOLUTION                                            │
├────────────────────────────────────────────────────────────────────────────┤
Trigger:  Valid message received
Action:   → Find/create conversation by:
             • WA: phone_number
             • IG: instagram_user_id
             • Web: visitor_id
          → Update last_activity_at
          → Check conversation status (bot_active / agent_handling)
├────────────────────────────────────────────────────────────────────────────┤
│ STEP 3: AGENT HANDOFF CHECK                                                │
├────────────────────────────────────────────────────────────────────────────┤
Trigger:  Conversation resolved
Action:   IF status == 'agent_handling' OR 'escalated':
            → Silently log message
            → Notify assigned CS (if takeover_timeout not expired)
          ELSE:
            → Continue to Step 4
├────────────────────────────────────────────────────────────────────────────┤
│ STEP 4: COMMAND PARSING                                                    │
├────────────────────────────────────────────────────────────────────────────┤
Trigger:  Message text available
Action:   IF text MATCHES ['menu', 'bantuan', 'help']:
            → Return static menu template
            → Log source: "menu"
          ELSE IF text MATCHES rule trigger:
            → Return rule.response_text
            → Log source: "manual", rule_id: X
          ELSE:
            → Continue to Step 5
├────────────────────────────────────────────────────────────────────────────┤
│ STEP 5: AI PROCESSING                                                      │
├────────────────────────────────────────────────────────────────────────────┤
Trigger:  No rule/command match
Action:   → Check AI cooldown (1 minute per conversation)
          → IF in cooldown:
              → Queue for later / skip
          → Build context:
              • System prompt (industry-specific)
              • Knowledge base articles (filtered by user_id)
              • Conversation history (last N messages)
              • Sentiment analysis result
          → Call AI Provider
          → Parse JSON response {answer, confidence}
          → IF confidence < 0.55:
              → Escalate to CS / fallback message
          → Log source: "ai", confidence: X
├────────────────────────────────────────────────────────────────────────────┤
│ STEP 6: RESPONSE DISPATCH                                                  │
├────────────────────────────────────────────────────────────────────────────┤
Trigger:  Response ready
Action:   → Post-processing:
             • Remove markdown (*bold* → plain)
             • Strip citations [1], [2]
             • PII scrubbing (emails, phones)
          → Send via appropriate channel:
             • WA: POST wa-service:3001/send
             • IG: POST graph.instagram.com/.../messages
             • Web: Push to poll queue
          → Save message to database
          → Log to auto_reply_logs
└────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Rule Matching Engine (Detailed)

```php
// Trigger → Action Format

// Example 1: Simple Keyword
Trigger: "harga"
Match Type: "contains"
Action: "Harga mulai dari Rp50rb/bulan kak. Mau tahu detailnya?"
Priority: 5

// Example 2: Multiple Keywords (OR logic)
Trigger: "harga|price|biaya|tarif|cost"
Match Type: "contains"
Action: "Ini daftar harga lengkapnya kak: ..."
Priority: 10

// Example 3: Exact Match
Trigger: "cancel"
Match Type: "exact"
Action: "Baik kak, untuk cancel order silahkan..."
Priority: 8

// Example 4: Regex Pattern
Trigger: "order\s+#?\d+"
Match Type: "regex"
Action: "Untuk cek status order, bisa kakak kirim nomor ordernya ya"
Priority: 7

// Priority Resolution:
// 1. Sort by priority DESC
// 2. If tie: newer rule wins (created_at DESC)
// 3. First match in sorted list is used
// 4. Rules ALWAYS take precedence over AI
```

### 2.3 Sentiment-Based Flow Branching

```
┌─────────────────┐
│ Incoming Message│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Sentiment Detect│
└────────┬────────┘
         │
    ┌────┼────┬─────────┐
    │    │    │         │
    ▼    ▼    ▼         ▼
┌─────┐┌───┐┌─────┐  ┌──────┐
│Angry││Sad││Happy│  │Neutral│
└──┬──┘└─┬─┘└──┬──┘  └──┬───┘
   │     │     │        │
   ▼     ▼     ▼        ▼
┌─────────────────────────────────────────┐
│Angry → Inject empathy prompt:          │
│        "User is frustrated. Show empathy│
│         first, offer quick solution"    │
├─────────────────────────────────────────┤
│Sad   → Inject help prompt:             │
│        "User seems confused. Use        │
│         simpler language with examples" │
├─────────────────────────────────────────┤
│Happy → Standard friendly response      │
├─────────────────────────────────────────┤
│Neutral → Standard processing           │
└─────────────────────────────────────────┘
```

---

## 3. NLP & PERSONALITY ENGINE

### 3.1 Prompt Architecture (Layered)

```
┌──────────────────────────────────────────────────────────────────────────┐
│                    PROMPT LAYER CAKE                                      │
├──────────────────────────────────────────────────────────────────────────┤
│ LAYER 1: BASE TEMPLATE (BusinessProfile::getPromptTemplates)             │
├──────────────────────────────────────────────────────────────────────────┤
Kamu adalah CS {business_name} yang profesional tapi hangat.
Kamu chat via WhatsApp, jadi gunakan bahasa santai dan natural seperti manusia.

GAYA KOMUNIKASI:
- Jawab SINGKAT (1-3 kalimat), tawarkan detail jika mau
- Variasi pembuka: "Oke", "Nah", "Oh iya", "Baik"
- Emoji maksimal 1 per pesan, TIDAK WAJIB
- Sapaan: "Kak" atau sesuai nama
- JANGAN dump semua info sekaligus

ATURAN KERAS (ANTI-HALUSINASI):
1. HANYA jawab dari KONTEKS KNOWLEDGE BASE
2. JANGAN PERNAH MENGARANG JAWABAN
3. JIKA INFO TIDAK ADA: Arahkan ke Admin
4. DILARANG beralih ke topik lain

Output HARUS JSON valid:
{"answer": "...", "confidence": 0.0-1.0}
├──────────────────────────────────────────────────────────────────────────┤
│ LAYER 2: INDUSTRY PERSONALITY (10 types)                                │
├──────────────────────────────────────────────────────────────────────────┤
Hospital      → Empathy focus, medical terms, poli recommendations
Retail        → Persuasive, sales techniques, "Stock terbatas kak!"
F&B           → Appetizing 🍕🍔, "Pasti enak kak!"
Education     → Formal 📚🎓, program details, "Silakan daftar di..."
Hospitality   → 5-star service 🏨✈️, reservation flow
Automotive    → Professional 🚗🔧, service booking
Property      → Persuasive 🏠🔑, viewing offers
Finance       → Trustworthy 💰💳, NO investment advice
Professional  → Business-like 💼📋, consultation booking
General       → Neutral, balanced approach
├──────────────────────────────────────────────────────────────────────────┤
│ LAYER 3: DYNAMIC INJECTIONS                                             │
├──────────────────────────────────────────────────────────────────────────┤
• Sentiment modifiers (if angry/sad detected)
• Context history (last N messages)
• Training examples (approved user feedback)
• Terminology glossary (business-specific terms)
├──────────────────────────────────────────────────────────────────────────┤
│ LAYER 4: KNOWLEDGE BASE CONTEXT                                         │
├──────────────────────────────────────────────────────────────────────────┤
### {article_title}
Sumber: {source_url}
Isi: {extracted_content}

(Only top 3-5 most relevant articles included)
├──────────────────────────────────────────────────────────────────────────┤
│ LAYER 5: CONVERSATION MEMORY                                            │
├──────────────────────────────────────────────────────────────────────────┤
User: {message_1}
CS: {response_1}
User: {message_2}
CS: {response_2}
...
(Current message)
└──────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Anti-Hallucination Mechanisms

```php
// 1. STRICT KB ISOLATION
$query->where('user_id', $userId)  // Multi-tenant filter
      ->where('is_active', true);

// 2. PROMO VALIDATION
if (Str::contains($message, ['promo', 'diskon', 'sale'])) {
    $hasPromoInKb = $articles->contains(function($a) {
        return Str::contains($a->content, ['promo', 'diskon']);
    });
    
    if (!$hasPromoInKb) {
        return "Mohon maaf kak, saat ini belum ada promo aktif...";
    }
}

// 3. CHALLENGE DETECTION (User correcting bot)
$challengeWords = ['tadi', 'katanya', 'bilang', 'sebelumnya', 'kok', 'loh'];
if (Str::contains($message, $challengeWords)) {
    return "Mohon maaf sekali kak 🙏 Sepertinya saya salah...";
}

// 4. GREETING STRIPPING (Prevent repetitive greetings)
if (hasConversationHistory()) {
    $greetingPatterns = [
        '/^(Halo|Hai|Hi|Hello)[\s,!.]+/iu',
        '/^(Maaf\s+(ya\s+)?kak,?\s+maksudnya)[\s,!.:]*/iu',
    ];
    $response = preg_replace($greetingPatterns, '', $response);
}

// 5. CONFIDENCE THRESHOLD
if ($aiConfidence < 0.55) {
    return $fallbackMessage;  // Arahkan ke CS
}
```

### 3.3 Sentiment Detection (Local)

```php
// No API call - pure local keyword matching
protected function detectSentiment(string $text): array
{
    $negative = ['lambat', 'lama', 'kecewa', 'buruk', 'gagal', 'error', 
                 'rusak', 'jelek', 'nyesel', 'penipuan', 'tidak bagus'];
    $confused = ['bingung', 'gimana', 'ga ngerti', 'kurang jelas', 
                 'gatau', 'gak paham', 'susah'];
    $positive = ['bagus', 'keren', 'mantap', 'sip', 'terima kasih', 
                 'oke', 'good', 'nice'];
    $agreement = ['boleh', 'iya', 'ya', 'mau', 'oke', 'setuju', 'gas'];
    
    $textLower = strtolower($text);
    
    foreach ($negative as $word) {
        if (str_contains($textLower, $word)) {
            return ['sentiment' => 'frustrated', 'keywords' => [$word]];
        }
    }
    // ... similar for confused, positive
    
    return ['sentiment' => 'neutral', 'keywords' => []];
}
```

---

## 4. DATABASE & INTEGRATION

### 4.1 Core Schema (Multi-Tenant)

```sql
-- CONVERSATION ISOLATION (Critical for SaaS)
-- Every table has user_id FK for tenant isolation

-- WhatsApp Conversations
CREATE TABLE wa_conversations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(20) UNIQUE NOT NULL,  -- 6281234567890
    display_name VARCHAR(255),
    status ENUM('bot_active', 'agent_handling', 'idle') DEFAULT 'bot_active',
    session_status ENUM('active', 'followup_sent', 'closed') DEFAULT 'active',
    assigned_cs VARCHAR(100),
    takeover_at TIMESTAMP NULL,
    last_cs_reply_at TIMESTAMP NULL,
    last_user_reply_at TIMESTAMP NULL,
    user_id BIGINT NOT NULL,  -- TENANT ISOLATION
    INDEX idx_user_phone (user_id, phone_number),
    INDEX idx_status (status)
);

-- WhatsApp Messages
CREATE TABLE wa_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    wa_message_id VARCHAR(100) UNIQUE NOT NULL,  -- WhatsApp's message ID
    phone_number VARCHAR(20) NOT NULL,
    direction ENUM('incoming', 'outgoing'),
    message TEXT,
    message_type ENUM('text', 'image', 'video', 'audio', 'document'),
    status ENUM('pending', 'sent', 'delivered', 'read', 'failed'),
    bot_reply TEXT,
    session_id VARCHAR(100),  -- FK to whatsapp_devices
    user_id BIGINT NOT NULL,
    INDEX idx_phone_direction (phone_number, direction),
    INDEX idx_created (created_at)
);

-- Auto-Reply Rules
CREATE TABLE auto_reply_rules (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    trigger_keyword VARCHAR(500),  -- "harga|price|biaya"
    match_type ENUM('exact', 'contains', 'regex') DEFAULT 'contains',
    response_text TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    priority INT DEFAULT 0,
    user_id BIGINT NOT NULL,
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_priority (priority DESC)
);

-- Knowledge Base Articles
CREATE TABLE kb_articles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    content TEXT NOT NULL,  -- Scraped/entered content
    source_url VARCHAR(500),
    tags VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    business_profile_id BIGINT NULL,
    user_id BIGINT NOT NULL,  -- STRICT ISOLATION
    INDEX idx_user_active (user_id, is_active)
);

-- Auto-Reply Logs (Analytics)
CREATE TABLE auto_reply_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT,
    message_id BIGINT,
    rule_id BIGINT NULL,
    trigger_text TEXT,
    response_text TEXT,
    response_source ENUM('ai', 'rule', 'menu', 'fallback'),
    ai_confidence FLOAT,
    ai_sources JSON,
    user_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_source (user_id, response_source),
    INDEX idx_created (created_at)
);
```

### 4.2 Third-Party Integrations

```php
// AI PROVIDERS (Failover Chain)
$providers = [
    'megallm' => [
        'endpoint' => 'https://ai.megallm.io/v1/chat/completions',
        'model'    => 'moonshotai/kimi-k2-instruct-0905',
        'timeout'  => 30,
    ],
    'sumopod' => [
        'endpoint' => 'https://ai.sumopod.com/v1/chat/completions',
        'model'    => 'kimi-k2-5-260127-free',
        'timeout'  => 30,
    ],
];

// Meta Graph API (Instagram)
'instagram' => [
    'app_id'     => env('INSTAGRAM_APP_ID'),
    'app_secret' => env('INSTAGRAM_APP_SECRET'),
    'webhook_verify_token' => env('WEBHOOK_VERIFY_TOKEN'),
    'graph_api_base' => 'https://graph.instagram.com/v21.0/',
];

// Payment Gateways
'midtrans' => [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
];

// Webhook Endpoints
Route::post('/api/midtrans/notification', [MidtransWebhookController::class, 'handle']);
```

### 4.3 Data Flow Security

```
┌─────────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                           │
├─────────────────────────────────────────────────────────────┤
│ 1. USER ISOLATION                                           │
│    - Every query: ->where('user_id', auth()->id())          │
│    - Policy-based authorization on all models               │
│    - Global scope auto-applied in models                    │
├─────────────────────────────────────────────────────────────┤
│ 2. WEBHOOK AUTHENTICATION                                   │
│    - wa-service: X-WA-Service-Key header                    │
│    - Instagram: hub_verify_token signature                  │
│    - CSRF tokens on all forms                               │
├─────────────────────────────────────────────────────────────┤
│ 3. API RATE LIMITING                                        │
│    - Per-user: 100 AI requests/hour                         │
│    - Global: Throttle on non-webhook routes                 │
│    - Webhook routes: Exempt from throttling                 │
├─────────────────────────────────────────────────────────────┤
│ 4. DATA SANITIZATION                                        │
│    - HTML stripping from user inputs                        │
│    - SQL injection protection (parameterized queries)       │
│    - XSS protection (Blade escaping)                        │
└─────────────────────────────────────────────────────────────┘
```

---

## 5. UNIQUE EDGE - MAIN FUNCTIONS

### 5.1 Tier-Based Feature Access (UMKM/Business/Enterprise)

```php
// Core Differentiator: Plan tier system
// File: app/Http/Middleware/CheckTier.php

class CheckTier
{
    public function handle($request, Closure $next, string $requiredTier): Response
    {
        $tiers = ['umkm' => 1, 'business' => 2, 'enterprise' => 3];
        $userTier = $user?->getPlanTier() ?? 'umkm';
        
        if ($tiers[$userTier] < $tiers[$requiredTier]) {
            return redirect()->route('dashboard')
                ->with('error', 'Fitur ini memerlukan paket ' . ucfirst($requiredTier));
        }
        return $next($request);
    }
}

// Applied to routes:
Route::middleware(['tier:business'])->group(function () {
    Route::get('/reports/realtime', [RealtimeDashboardController::class, 'index']);
    Route::resource('whatsapp/broadcast', WhatsAppBroadcastController::class);
});

Route::middleware(['tier:enterprise'])->group(function () {
    Route::resource('sequences', SequenceController::class);
    Route::get('/instagram/comments', [InstagramCommentController::class, 'index']);
});
```

### 5.2 Session Takeover & Handback System

```php
// File: app/Models/WaConversation.php

class WaConversation extends Model
{
    // KEY FEATURE: Human agent can "take over" conversation from bot
    
    public function takeover(string $csName): void
    {
        $this->update([
            'status' => self::STATUS_AGENT_HANDLING,
            'assigned_cs' => $csName,
            'takeover_at' => now(),
            'last_cs_reply_at' => now(),
        ]);
    }
    
    public function handback(): void
    {
        $this->update([
            'status' => self::STATUS_BOT_ACTIVE,
            'assigned_cs' => null,
            'takeover_at' => null,
            'last_cs_reply_at' => null,
        ]);
    }
    
    // AUTO-HANDBACK: If agent idle for X minutes
    public function getRemainingMinutesAttribute(): ?int
    {
        if (!$this->last_cs_reply_at) return null;
        
        $session = WaSession::where('user_id', $this->user_id)->first();
        $timeout = $session?->takeover_timeout_minutes ?? 60;
        $elapsed = now()->diffInMinutes($this->last_cs_reply_at);
        
        return max(0, $timeout - $elapsed);
    }
}
```

### 5.3 Smart Knowledge Base Import (Web Scraping)

```php
// File: app/Http/Controllers/KbScrapeController.php

class KbScrapeController extends Controller
{
    // UNIQUE: Auto-extract info from websites using Puppeteer
    
    public function start(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'url'],
            'name' => ['nullable', 'string'],
        ]);
        
        // SECURITY: SSRF Protection
        if (!SsrfProtectionService::isUrlSafe($validated['url'])) {
            return response()->json([
                'ok' => false,
                'message' => 'URL tidak aman (private IP/local)',
            ], 400);
        }
        
        // Queue scraping job
        $jobId = Str::uuid();
        ProcessKbScrape::dispatch([
            'job_id' => $jobId,
            'url' => $validated['url'],
            'user_id' => auth()->id(),
        ]);
        
        return response()->json([
            'ok' => true,
            'job_id' => $jobId,
            'message' => 'Scraping dimulai (estimasi 10-30 detik)',
        ]);
    }
}

// Extracts: Prices, features, FAQs, contact info
// Supports: E-commerce, restaurant menus, property listings
```

### 5.4 AI Training System (Learning from CS)

```php
// File: app/Models/AiTrainingExample.php

class AiTrainingExample extends Model
{
    // KEY FEATURE: Bot learns from human CS responses
    
    protected $fillable = [
        'user_id',
        'business_profile_id',
        'user_query',           // What customer asked
        'assistant_response',   // How CS replied
        'rating',               // 1-5 stars
        'is_approved',          // Approved by admin
    ];
}

// Usage in AI prompt (AiAnswerService.php):
$trainingExamples = AiTrainingExample::where('business_profile_id', $profile?->id)
    ->where('is_approved', true)
    ->latest()
    ->take(5)
    ->get();

if ($trainingExamples->isNotEmpty()) {
    $systemPrompt .= "\n\nGAYA BAHASA YANG DISUKAI:";
    foreach ($trainingExamples as $ex) {
        $systemPrompt .= "\nUser: {$ex->user_query}\nCS: {$ex->assistant_response}";
    }
    $systemPrompt .= "\n\nGunakan gaya bahasa, nada, dan keramahan yang sama.";
}
```

### 5.5 Multi-Provider AI Failover

```php
// File: app/Services/AiProviderService.php

class AiProviderService
{
    // UNIQUE: Automatic failover between AI providers
    
    protected array $providers = [
        'megallm' => [
            'endpoint' => 'https://ai.megallm.io/v1/chat/completions',
            'healthy' => true,
            'fail_count' => 0,
        ],
        'sumopod' => [
            'endpoint' => 'https://ai.sumopod.com/v1/chat/completions',
            'healthy' => true,
            'fail_count' => 0,
        ],
    ];
    
    public function chat(array $messages, string $model, int $timeout = 30): array
    {
        // Try primary first
        foreach ($this->providers as $name => $config) {
            if (!$config['healthy']) continue;
            
            try {
                $response = Http::timeout($timeout)
                    ->withHeaders(['Authorization' => 'Bearer ' . $config['key']])
                    ->post($config['endpoint'], [
                        'model' => $model,
                        'messages' => $messages,
                    ]);
                
                if ($response->successful()) {
                    $this->resetFailCount($name);
                    return $response->json();
                }
            } catch (Exception $e) {
                $this->incrementFailCount($name);
                if ($this->providers[$name]['fail_count'] >= 3) {
                    $this->providers[$name]['healthy'] = false;
                    // Alert admin: Provider X is down
                }
            }
        }
        
        throw new Exception('All AI providers failed');
    }
}
```

### 5.6 Quick Reply Shortcuts

```php
// File: app/Models/QuickReply.php

class QuickReply extends Model
{
    // KEY FEATURE: Type "/shortcut" in chat for instant template
    
    protected $fillable = ['shortcut', 'message', 'category', 'is_active'];
    
    // Usage in chat:
    // User types: "/harga"
    // System expands to: "Harga mulai dari Rp50rb/bulan kak. Mau lihat detailnya?"
}

// Auto-expansion in message sending:
public function expandShortcuts(string $text): string
{
    $shortcuts = QuickReply::where('is_active', true)->get();
    
    foreach ($shortcuts as $qr) {
        $pattern = '/\/' . preg_quote($qr->shortcut, '/') . '\b/i';
        $text = preg_replace($pattern, $qr->message, $text);
    }
    
    return $text;
}
```

---

## 6. DEPLOYMENT NOTES

### 6.1 Required Services
```
┌─────────────────────────────────────────────────────────────────┐
│ SERVICE              │ PORT  │ PURPOSE                          │
├─────────────────────────────────────────────────────────────────┤
│ Laravel (PHP-FPM)    │ 9000  │ Main application                 │
│ Nginx                │ 80/443│ Web server / reverse proxy       │
│ MySQL                │ 3306  │ Primary database                 │
│ Redis                │ 6379  │ Cache / Sessions / Queue         │
│ wa-service (Node)    │ 3001  │ WhatsApp Baileys gateway         │
│ Supervisor           │ -     │ Queue worker management          │
│ PM2                  │ -     │ wa-service process manager       │
└─────────────────────────────────────────────────────────────────┘
```

### 6.2 Critical Environment Variables
```env
# Database
DB_DATABASE=replyai
DB_USERNAME=
DB_PASSWORD=

# WhatsApp Service
WA_SERVICE_KEY=replyai-wa-secret
LARAVEL_WEBHOOK_URL=http://127.0.0.1:8000/api/whatsapp/webhook

# AI Providers
MEGALLM_API_KEY=
SUMOPOD_API_KEY=

# Instagram/Meta
INSTAGRAM_APP_ID=
INSTAGRAM_APP_SECRET=
WEBHOOK_VERIFY_TOKEN=
META_APP_SECRET=

# Midtrans Payment
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

---

## 7. SUMMARY

REPLYAI is a **multi-tenant, multi-channel AI chatbot platform** specifically designed for Indonesian MSMEs with these key differentiators:

1. **Unified Inbox** - WhatsApp, Instagram, Web Widget in one dashboard
2. **Hybrid AI** - Rule-based priority + AI fallback with confidence threshold
3. **Local Context** - Industry-specific personas (10 types) with Indonesian language nuances
4. **Anti-Hallucination** - Strict KB isolation + promo validation + challenge detection
5. **Human Handoff** - Session takeover with auto-timeout and handback
6. **Auto-Learning** - AI improves from approved CS responses
7. **Smart Import** - Automatic website scraping for KB population
8. **Tiered Access** - UMKM/Business/Enterprise feature gating

**License**: Private - For AI Assistant training purposes only.
