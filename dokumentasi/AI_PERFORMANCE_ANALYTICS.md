# AI Performance Analytics - Dokumentasi Fitur

## 📋 Overview

**AI Performance Analytics** adalah fitur dashboard analitik yang menyediakan metrik dan insight tentang performa AI chatbot ReplyAI. Fitur ini membantu admin dan user untuk memantau kualitas respons AI, mengidentifikasi knowledge gaps, dan melacak improvement dari waktu ke waktu.

**URL Access:** `/ai-performance`
**Controller:** `AiPerformanceController.php`
**View:** `pages.ai-performance.index`

---

## 🎯 Fungsi dan Tujuan

### 1. **Monitoring Kualitas AI**
- Melacak akurasi intent recognition
- Mengukur relevansi respons AI
- Monitoring confidence score distribusi

### 2. **Identifikasi Knowledge Gaps**
- Menemukan pertanyaan yang tidak terjawab
- Mengidentifikasi topik yang sering ditanyakan tapi tidak ada di KB
- Memberikan saran artikel KB yang perlu dibuat

### 3. **Tracking Improvement**
- Melihat trend performa AI dari waktu ke waktu
- Mengukur efektivitas training examples
- Monitoring learning progress AI

### 4. **Popular Intents Analysis**
- Mengetahui intent apa yang paling sering muncul
- Analisis sentimen per intent
- Trend harian untuk top intents

---

## 📊 Data Sources (Sumber Data)

Fitur ini mengambil data dari **7 tabel utama**:

### 1. **Conversations** (Instagram DM)
| Kolom | Deskripsi |
|-------|-----------|
| `ai_intent` | Intent yang terdeteksi oleh AI |
| `ai_intent_confidence` | Confidence score (0-1) |
| `ai_sentiment` | Sentimen analisis (positive/negative/neutral) |
| `status` | Status percakapan (active/resolved/closed) |

### 2. **WaConversation** (WhatsApp)
| Kolom | Deskripsi |
|-------|-----------|
| `ai_intent` | Intent yang terdeteksi oleh AI |
| `ai_intent_confidence` | Confidence score (0-1) |
| `ai_sentiment` | Sentimen analisis |
| `status` | Status percakapan |

### 3. **WebConversation** (Web Widget)
| Kolom | Deskripsi |
|-------|-----------|
| `ai_intent` | Intent yang terdeteksi oleh AI |
| `ai_intent_confidence` | Confidence score (0-1) |
| `ai_sentiment` | Sentimen analisis |
| `status` | Status percakapan |

### 4. **AutoReplyLog**
| Kolom | Deskripsi |
|-------|-----------|
| `ai_confidence` | Confidence score respons AI |
| `status` | Status pengiriman (sent/failed/error) |
| `response_source` | Sumber respons (ai/kb/manual) |
| `created_at` | Timestamp |

### 5. **KbMissedQuery**
| Kolom | Deskripsi |
|-------|-----------|
| `question` | Pertanyaan yang tidak terjawab |
| `count` | Berapa kali pertanyaan ini ditanyakan |
| `last_asked_at` | Terakhir kali ditanyakan |
| `status` | Status (pending/resolved/ignored) |

### 6. **AiTrainingExample**
| Kolom | Deskripsi |
|-------|-----------|
| `user_query` | Query dari user |
| `assistant_response` | Respons yang diharapkan |
| `rating` | Rating kualitas (1-5) |
| `is_approved` | Apakah sudah di-approve |

### 7. **Messages** / **WaMessage**
| Kolom | Deskripsi |
|-------|-----------|
| `direction` | incoming/outgoing |
| `is_from_me` | Apakah dari bot |
| `created_at` | Timestamp |

---

## 📈 Metrics dan Calculations

### 1. **Intent Accuracy**
```
Overall Accuracy = (High Confidence Responses / Total Responses) × 100

High Confidence = confidence >= 0.8 (80%)
```

**Breakdown per Intent:**
- Total count per intent
- High confidence count
- Resolution rate (resolved/total)
- Average confidence

### 2. **Response Relevance**
```
Relevance Score = (Success Rate × 0.6) + (Avg Confidence × 100 × 0.4)

Success Rate = (Successful Replies / Total Replies) × 100
```

**Components:**
- Total responses
- Successful replies (status: sent/sent_ai/success)
- Failed replies (status: failed/error)
- AI-generated replies count
- Average rating dari training examples

### 3. **Knowledge Gaps**
**Frequency Buckets:**
- Very High: count >= 20
- High: count 10-19
- Medium: count 5-9
- Low: count < 5

**Suggested Topics:**
- Extract keywords dari missed queries
- Filter common words (apa, bagaimana, cara, dll)
- Sort by frequency

### 4. **Confidence Distribution**
**Buckets:**
- Low: < 50%
- Medium: 50% - 80%
- High: > 80%

Each bucket shows:
- Count & percentage
- Success rate within that bucket

### 5. **Training Improvement**
**Trend Periods:**
- Last 7 days
- Last 14 days
- Last 30 days
- Last 90 days

**Monthly History:**
- 6 bulan terakhir
- Accuracy per bulan
- Total interactions per bulan

**Improvement Rate:**
```
Improvement = ((Current - Previous) / Previous) × 100
```

---

## 🔌 API Endpoints

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/ai-performance/intent-accuracy` | GET | Intent recognition accuracy |
| `/api/ai-performance/response-relevance` | GET | Response quality scores |
| `/api/ai-performance/knowledge-gaps` | GET | Missing KB articles |
| `/api/ai-performance/training-improvement` | GET | Learning progress over time |
| `/api/ai-performance/confidence-distribution` | GET | Confidence histogram data |
| `/api/ai-performance/popular-intents` | GET | Most common customer intents |

---

## 🎨 Dashboard Components

### Header Cards
1. **Intent Accuracy** - Overall accuracy percentage
2. **Response Relevance** - Relevance score
3. **Knowledge Gaps** - Total unanswered queries
4. **Improvement Rate** - Trend vs previous period

### Charts & Visualizations
1. **Confidence Distribution** - Bar chart 3 buckets
2. **Popular Intents** - List dengan trend
3. **Accuracy by Intent Category** - Table breakdown
4. **Top Knowledge Gaps** - Priority list
5. **Training Improvement Trend** - Line chart 6 bulan
6. **Recent AI Interactions** - Activity log

---

## 🔄 Data Flow

```
User Chat → AI Processing → Log to AutoReplyLog
     ↓
Intent Detection → Save to Conversation/WaConversation
     ↓
KB Search (if no match) → Log to KbMissedQuery
     ↓
Training Review → Save to AiTrainingExample
     ↓
Analytics Dashboard (Aggregated from all tables)
```

---

## 🛠️ Tech Stack

- **Backend:** Laravel 12, PHP 8.4
- **Database:** MySQL
- **Query:** Eloquent ORM with Collections
- **Date Handling:** Carbon
- **Frontend:** Blade, Tailwind CSS, Alpine.js
- **Charts:** (Diasumsikan menggunakan Chart.js atau similar)

---

## 📝 Contoh Query SQL (Internal)

### Intent Accuracy
```sql
SELECT 
    ai_intent,
    COUNT(*) as total,
    AVG(ai_intent_confidence) as avg_confidence,
    SUM(CASE WHEN ai_intent_confidence >= 0.8 THEN 1 ELSE 0 END) as high_confidence
FROM conversations
WHERE ai_intent IS NOT NULL
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY ai_intent;
```

### Knowledge Gaps
```sql
SELECT 
    question,
    count,
    last_asked_at
FROM kb_missed_queries
WHERE status = 'pending'
ORDER BY count DESC
LIMIT 10;
```

### Confidence Distribution
```sql
SELECT 
    CASE 
        WHEN ai_confidence < 0.5 THEN 'low'
        WHEN ai_confidence BETWEEN 0.5 AND 0.8 THEN 'medium'
        ELSE 'high'
    END as bucket,
    COUNT(*) as count
FROM auto_reply_logs
WHERE ai_confidence IS NOT NULL
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY bucket;
```

---

## 🎓 Use Cases

### 1. **Admin Monitoring**
Admin dapat melihat apakah AI performa menurun dan perlu tuning.

### 2. **Content Team**
Tim konten melihat knowledge gaps untuk membuat artikel KB baru.

### 3. **Training Team**
Melihat training examples yang perlu di-review atau di-approve.

### 4. **Business Owner**
Melihat popular intents untuk mengerti kebutuhan customer.

---

## 🚨 Limitations

1. **Data Freshness** - Real-time dengan delay beberapa menit
2. **Aggregation** - Data di-aggregate dari multiple sources (IG, WA, Web)
3. **Intent Detection** - Akurasi tergantung kualitas AI model
4. **Historical Data** - Data lama mungkin tidak konsisten jika ada schema changes

---

## 🔄 Scheduled Reports

Fitur ini terintegrasi dengan **Scheduled Reports** untuk:
- Weekly AI performance summary
- Monthly knowledge gaps report
- Trend analysis reports

---

*Generated: 23 Februari 2026*
*Versi: 1.0*
