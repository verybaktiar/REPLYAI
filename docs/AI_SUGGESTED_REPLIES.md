# AI Suggested Replies Feature

Fitur AI Suggested Replies membantu agen customer service merespons lebih cepat dengan memberikan saran balasan otomatis berdasarkan konteks percakapan.

## Fitur Utama

### 1. **AI Reply Suggestions**
- Menghasilkan 3 saran balasan yang relevan dengan konteks percakapan
- Berdasarkan 10 pesan terakhir dalam percakapan
- Cache selama 5 menit untuk performa optimal
- Fallback ke template default jika AI tidak tersedia

### 2. **Sentiment Analysis**
- Mendeteksi sentimen pesan: Positive, Neutral, Negative
- Menampilkan indikator sentimen di daftar percakapan
- Membantu agen memahami mood pelanggan

### 3. **Intent Detection**
- Mendeteksi maksud pelanggan:
  - `complaint` - Keluhan
  - `inquiry` - Pertanyaan
  - `purchase` - Pembelian
  - `support` - Bantuan teknis
  - `feedback` - Umpan balik
  - `greeting` - Sapaan
  - `urgent` - Mendesak
  - `cancellation` - Pembatalan
  - `general` - Umum

### 4. **Conversation Summary**
- Meringkas percakapan panjang
- Menyoroti poin-poin penting
- Mengidentifikasi tindak lanjut yang diperlukan

## Konfigurasi

### Environment Variables

Tambahkan ke file `.env`:

```env
# --- AI Suggested Replies (for Agents) ---
AI_SUGGESTIONS_ENABLED=true
AI_SUGGESTIONS_PROVIDER=megallm
AI_SUGGESTIONS_MODEL=mistral-large-3-675b-instruct-2512
AI_SUGGESTIONS_CACHE_DURATION=300
AI_SUGGESTIONS_MAX_CONTEXT=10

# --- Claude AI (Optional) ---
CLAUDE_API_KEY=
CLAUDE_MODEL=claude-3-haiku-20240307
```

### Provider Options

1. **MegaLLM** (Recommended)
   - Provider lokal Indonesia
   - Mendukung berbagai model open-source
   - Biaya lebih terjangkau

2. **OpenAI**
   - Model: gpt-4o-mini, gpt-4o
   - API Key dari OpenAI Dashboard

3. **Claude**
   - Model: claude-3-haiku-20240307
   - API Key dari Anthropic Console

## API Endpoints

### 1. Generate Suggestions
```http
POST /api/ai/suggest-replies
Content-Type: application/json

{
    "conversation_id": 123,
    "conversation_type": "instagram" // instagram, whatsapp, web
}
```

Response:
```json
{
    "success": true,
    "data": {
        "suggestions": [
            "Saran balasan 1",
            "Saran balasan 2",
            "Saran balasan 3"
        ],
        "sentiment": {
            "sentiment": "positive",
            "score": 0.85,
            "emotions": ["happy", "satisfied"]
        },
        "intent": {
            "intent": "inquiry",
            "confidence": 0.92,
            "entities": {}
        },
        "message_id": 456
    },
    "meta": {
        "generated_at": "2026-02-16T08:30:00Z",
        "cache_expires_in": 300
    }
}
```

### 2. Analyze Sentiment
```http
POST /api/ai/analyze-sentiment
Content-Type: application/json

{
    "text": "Pesan yang ingin dianalisis",
    "conversation_id": 123, // optional
    "conversation_type": "instagram" // optional
}
```

### 3. Summarize Conversation
```http
POST /api/ai/summarize
Content-Type: application/json

{
    "conversation_id": 123,
    "conversation_type": "instagram"
}
```

Response:
```json
{
    "success": true,
    "data": {
        "summary": "Ringkasan singkat percakapan",
        "keyPoints": ["Poin 1", "Poin 2", "Poin 3"],
        "actionItems": ["Tindak lanjut 1"],
        "source": "ai"
    },
    "meta": {
        "message_count": 25,
        "generated_at": "2026-02-16T08:30:00Z"
    }
}
```

### 4. Detect Intent
```http
POST /api/ai/detect-intent
Content-Type: application/json

{
    "text": "Pesan yang ingin dideteksi intent-nya",
    "conversation_id": 123, // optional
    "conversation_type": "instagram" // optional
}
```

## Integrasi ke Chat Interface

### Menggunakan Blade Component

```blade
<x-chat.ai-suggestions 
    :conversation-id="$conversation->id"
    :conversation-type="'instagram'"
    :input-selector="'#message-input'"
    :on-insert="'onSuggestionInsert'"
/>
```

### Update Conversation List

```blade
<x-chat.chat-assigned-item 
    :name="$conversation->display_name"
    :message="$conversation->last_message"
    :time="$conversation->last_activity_at"
    :ai-sentiment="$conversation->ai_sentiment"
    :ai-intent="$conversation->ai_intent"
/>
```

### Menggunakan JavaScript/Alpine.js

```javascript
// Initialize AI Suggestions
const aiSuggestions = {
    suggestions: [],
    loading: false,
    
    async loadSuggestions(conversationId, type) {
        this.loading = true;
        
        const response = await fetch('/api/ai/suggest-replies', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                conversation_type: type
            })
        });
        
        const data = await response.json();
        this.suggestions = data.data.suggestions;
        this.loading = false;
    }
};
```

## Database Migration

Jalankan migration untuk menambahkan kolom AI sentiment:

```bash
php artisan migrate --path=database/migrations/2026_02_16_000000_add_ai_sentiment_to_conversations.php
```

Kolom yang ditambahkan:
- `ai_sentiment` - positive, neutral, negative
- `ai_sentiment_score` - nilai 0.00 - 1.00
- `ai_intent` - jenis intent yang terdeteksi
- `ai_intent_confidence` - tingkat kepercayaan intent
- `ai_analyzed_at` - timestamp analisis terakhir

## File yang Dibuat

1. **Service**: `app/Services/AiSuggestionService.php`
2. **Controller**: `app/Http/Controllers/AiReplyController.php`
3. **View Component**: `resources/views/components/chat/ai-suggestions.blade.php`
4. **Updated Component**: `resources/views/components/chat/chat-assigned-item.blade.php`
5. **Migration**: `database/migrations/2026_02_16_000000_add_ai_sentiment_to_conversations.php`
6. **Routes**: Updated `routes/api.php`
7. **Config**: Updated `config/services.php`

## Best Practices

1. **Caching**
   - Suggestions di-cache selama 5 menit untuk menghemat API calls
   - Sentiment di-cache per pesan untuk menghindari analisis ulang

2. **Fallback**
   - Jika AI service tidak tersedia, sistem akan menggunakan local analysis
   - Template fallback untuk suggestions jika API error

3. **Rate Limiting**
   - Implementasi rate limiting di server untuk mencegah abuse
   - Cache memastikan user tidak melakukan API call berlebihan

4. **Privacy**
   - Hanya user yang memiliki akses ke conversation yang bisa melihat suggestions
   - Data percakapan tidak disimpan permanen oleh AI service

## Troubleshooting

### Suggestions tidak muncul
1. Cek API key di `.env`
2. Pastikan `AI_SUGGESTIONS_ENABLED=true`
3. Cek log file untuk error

### Sentiment tidak terdeteksi
1. Pastikan migration sudah dijalankan
2. Cek apakah kolom `ai_sentiment` ada di database
3. Verifikasi API provider tersedia

### Performance lambat
1. Cek koneksi ke AI provider
2. Tingkatkan `AI_SUGGESTIONS_CACHE_DURATION`
3. Pertimbangkan menggunakan provider yang lebih cepat

## Roadmap

- [ ] Smart Reply Learning dari riwayat responses agen
- [ ] Multi-language support untuk suggestions
- [ ] Custom suggestion templates per business profile
- [ ] Analytics untuk tracking suggestion usage
- [ ] Integration dengan Knowledge Base untuk suggestions yang lebih akurat
