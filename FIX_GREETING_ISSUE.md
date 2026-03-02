# Fix: AI Masih Mengirim Greeting di Tengah Percakapan

## Problem
AI masih mengirim greeting seperti "Maaf kak, maksudnya..." bahkan setelah fix sebelumnya.

## Root Cause Analysis

### Masalah Utama: Field `is_from_me` Tidak Ada di Database!

Saat membangun conversation history, kode menggunakan `$msg->is_from_me` untuk menentukan apakah pesan berasal dari bot atau user. Namun field ini **tidak ada** di tabel `wa_messages`!

```php
// KODE LAMA (SALAH):
$role = $msg->is_from_me ? 'assistant' : 'user';  // is_from_me SELALU null!
```

Karena `is_from_me` selalu `null`/false, semua pesan (termasuk pesan dari bot) dianggap sebagai pesan user. Akibatnya:
1. History terlihat seperti: User, User, User (tidak ada Assistant)
2. AI mengira ini adalah pesan pertama (tidak ada history)
3. AI mengirim greeting karena mengira ini awal percakapan

## Fix Applied

### 1. WhatsAppWebhookController.php
```php
// FIX: Gunakan direction field yang memang ada di DB
$isFromBot = $msg->direction === 'outgoing';
$role = $isFromBot ? 'assistant' : 'user';
$content = $isFromBot ? ($msg->bot_reply ?? $msg->message) : $msg->message;
```

### 2. AiAnswerService.php  
```php
// FIX: Gunakan direction field
$sender = $msg->direction === 'outgoing' ? 'Bot/CS' : 'User';
```

### 3. Post-processing di AiAnswerService.php (Perkuat Regex)
```php
// Pattern yang lebih komprehensif
$greetingPatterns = [
    '/^(Halo|Hai|Hi|Hello|Hey)[\s,!.]+/iu',
    '/^(Selamat\s+(pagi|siang|sore|malam))[\s,!.]+/iu',
    '/^(Maaf\s+(ya\s+)?kak,?\s+maksudnya)[\s,!.:]*/iu',
    '/^(Maaf\s+kak,?\s+(ya\s+)?maksudnya)[\s,!.:]*/iu',
    '/^(Maaf,?\s+maksudnya)[\s,!.:]*/iu',
    '/^(Maksudnya,?\s+maaf)[\s,!.:]*/iu',
    '/^(Maaf\s+(ya\s+)?kak?)[\s,!.]+/iu',
    '/^(Maaf,?\s+saya\s+(tidak\s+)?(paham|mengerti))[\s,!.]+/iu',
    '/^(Saya\s+(tidak\s+)?(paham|mengerti))[\s,!.]+/iu',
    '/^(Mau\s+(yang\s+)?mana)[\s?.]+/iu',
    '/^(Mau\s+tanya\s+tentang)[\s?.]+/iu',
    '/^(Tanya\s+tentang)[\s?.]+/iu',
    '/^(Halo|Hai|Hi)[\s,!.]+\s*(kak|kakak|mba|mas)[\s,!.]+/iu',
    '/^(Halo|Hai|Hi)[\s,!.]+\s*(kak|kakak|mba|mas)?[\s,!.]+/iu',
];
```

### 4. System Prompt (Perkuat Larangan)
```
DILARANG KERAS mengatakan 'Maaf kak, maksudnya...' atau 'Maaf saya tidak paham' - ini SANGAT MENYEBALKAN!
DILARANG KERAS menanyakan 'Mau yang mana?' atau 'Tanya tentang apa?' - user sudah JELAS dari history!
```

## Testing

1. Mulai percakapan baru dengan bot
2. Bot harusnya sapa dengan greeting (ini benar)
3. Kirim pesan kedua (pertanyaan)
4. Bot harusnya LANGSUNG jawab tanpa greeting
5. Contoh BENAR: "Rp50.000 untuk produk A"
6. Contoh SALAH: "Maaf kak, maksudnya tanya harga produk A?"

## Files Modified
- `app/Http/Controllers/WhatsAppWebhookController.php` - Fix history builder
- `app/Services/AiAnswerService.php` - Fix sender detection + perkuat regex
