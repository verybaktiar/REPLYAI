<?php

namespace App\Services;

use App\Models\KbArticle;
use App\Models\BusinessProfile;
use App\Models\AiTrainingExample;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnswerService
{
    public float $minConfidence;

    public function __construct()
    {
        $this->minConfidence = (float) config('ai.min_confidence', env('AI_MIN_CONFIDENCE', 0.55));
    }

    /**
     * Deteksi sentimen user berdasarkan kata-kata
     * @return array ['sentiment' => 'positive|negative|frustrated|confused|neutral', 'keywords' => [...]]
     */
    protected function detectSentiment(string $text): array
    {
        $lower = Str::lower($text);
        
        // Kata-kata negatif/frustasi
        $negativeWords = ['lambat', 'lama', 'kecewa', 'buruk', 'parah', 'jelek', 'gak bisa', 
                          'tidak bisa', 'gagal', 'error', 'rusak', 'mahal', 'ribet', 'susah',
                          'kesal', 'bete', 'capek', 'males', 'bohong', 'tipu', 'penipuan'];
        
        // Kata-kata bingung
        $confusedWords = ['bingung', 'gimana', 'bagaimana', 'ga ngerti', 'gak paham', 
                          'kurang jelas', 'maksudnya', 'artinya', 'apa itu', 'apa maksud'];
        
        // Kata-kata positif
        $positiveWords = ['bagus', 'keren', 'mantap', 'sip', 'oke', 'ok', 'baik', 'terima kasih',
                          'thanks', 'makasih', 'helpful', 'membantu', 'senang', 'puas', 'wow'];
        
        // Kata-kata persetujuan
        $agreementWords = ['boleh', 'iya', 'ya', 'mau', 'oke', 'ok', 'sip', 'baik', 'setuju', 
                           'lanjut', 'gas', 'yuk', 'ayok', 'deal', 'jadi'];
        
        $foundKeywords = [];
        $sentiment = 'neutral';
        
        // Check negative/frustrated first (highest priority)
        foreach ($negativeWords as $word) {
            if (Str::contains($lower, $word)) {
                $foundKeywords[] = $word;
                $sentiment = 'frustrated';
            }
        }
        
        // Check confused
        if ($sentiment === 'neutral') {
            foreach ($confusedWords as $word) {
                if (Str::contains($lower, $word)) {
                    $foundKeywords[] = $word;
                    $sentiment = 'confused';
                }
            }
        }
        
        // Check positive
        if ($sentiment === 'neutral') {
            foreach ($positiveWords as $word) {
                if (Str::contains($lower, $word)) {
                    $foundKeywords[] = $word;
                    $sentiment = 'positive';
                }
            }
        }
        
        // Check agreement (untuk konteks handling)
        $isAgreement = false;
        foreach ($agreementWords as $word) {
            if (Str::contains($lower, $word) && mb_strlen($text) < 20) {
                $isAgreement = true;
                break;
            }
        }
        
        return [
            'sentiment' => $sentiment,
            'keywords' => $foundKeywords,
            'is_agreement' => $isAgreement,
        ];
    }

    public function answerFromKb(string $question): ?array
    {
        $question = trim($question);
        if ($question === '') return null;

        $articles = $this->searchRelevantArticles($question, 4);
        if ($articles->isEmpty()) {
            Log::info('📚 KB candidates empty', ['question' => $question]);
            return null;
        }

        $apiKey = config('services.perplexity.key');
        if (!$apiKey) {
            Log::error('❌ Perplexity API key missing');
            return null;
        }

        $context = $this->buildContext($articles, $question);

        $res = $this->callPerplexity($question, $context);
        if (!$res) return null;

        $conf = (float)($res['confidence'] ?? 0);
        if ($conf < $this->minConfidence) {
            Log::info('📉 AI confidence low', ['conf' => $conf, 'min' => $this->minConfidence]);
            return null;
        }

        return [
            'answer' => (string)($res['answer'] ?? ''),
            'confidence' => $conf,
            'sources' => $articles->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'source_url' => $a->source_url,
            ])->values()->all(),
        ];
    }

    /**
     * Jawab pertanyaan dengan konteks (untuk Web Widget)
     */
    public function answerWithContext(string $question, array $history = []): string
    {
        // Re-use logic from answerWhatsApp but return string directly
        $result = $this->answerWhatsApp($question, $history);
        
        if (!$result || empty($result['answer'])) {
            return "Maaf, saya tidak mengerti. Bisa diulangi dengan kalimat lain?";
        }

        return $result['answer'];
    }

    /**
     * Helper to format response and log interaction
     */
    private function formatResponse(string $answer, float $confidence, string $source, ?BusinessProfile $profile, ?int $userId, string $question, array $contextIds = [], ?string $sentiment = null, ?string $imageUrl = null): array
    {
        // Log AI Interaction
        if ($profile) {
             \App\Models\AiLog::create([
                 'business_profile_id' => $profile->id,
                 'user_id' => $userId,
                 'user_message' => $question,
                 'answer' => $answer,
                 'confidence' => $confidence,
                 'source' => $source,
                 'context_used' => $contextIds,
                 'input_tokens' => 0,
                 'output_tokens' => 0,
             ]);
        }

        return [
            'answer' => $answer,
            'confidence' => $confidence,
            'source' => $source,
            'sentiment' => $sentiment ?? 'neutral',
            'image_url' => $imageUrl,
        ];
    }

    /**
     * WhatsApp-specific AI answer dengan kemampuan percakapan natural
     * Handle sapaan, pertanyaan umum, dan keluhan pasien
     * 
     * @param string $question The user's question
     * @param array|null $conversationHistory Previous messages for context
     * @param BusinessProfile|null $profile Optional profile to use (overrides default)
     * @param int|null $userId Optional user ID (context) for multi-tenant KB search
     */
    public function answerWhatsApp(string $question, ?array $conversationHistory = [], ?BusinessProfile $profile = null, ?int $userId = null): ?array
    {
        $question = trim($question);
        if ($question === '') return null;

        $lower = Str::lower($question);
        
        // 1) Deteksi sapaan/greeting
        $greetings = ['halo', 'hai', 'hi', 'hello', 'hey', 'pagi', 'siang', 'sore', 'malam', 
                      'assalamualaikum', 'assalamu', 'permisi', 'selamat'];
        $isGreeting = false;
        foreach ($greetings as $g) {
            if (Str::contains($lower, $g) && mb_strlen($question) < 30) {
                $isGreeting = true;
                break;
            }
        }

        // 2) Deteksi sapaan sederhana tanpa pertanyaan substantif
        $isSimpleGreeting = $isGreeting && !Str::contains($lower, ['?', 'jadwal', 'dokter', 'poli', 
            'harga', 'biaya', 'jam', 'buka', 'tutup', 'daftar', 'booking', 'sakit', 'keluhan']);

        // 3) Jika sapaan sederhana, balas ramah tanpa perlu KB
        if ($isSimpleGreeting) {
            $industry = $profile?->getIndustryLabel() ?? 'kami';
            
            // DYNAMIC GREETING: Cek apakah ada promo aktif di KB
            $profileId = $profile?->id;
            $searchUserId = $userId ?? $profile?->user_id;
            $hasPromo = $this->hasActivePromo($profileId, $searchUserId);

            // Default greetings (SAFE - No Promo Promise)
            $greetingResponses = [
                "Halo kak! Mau tanya tentang menu {$industry} atau rekomendasi best seller? ☕",
                "Hai kak! Ada yang bisa kami bantu seputar {$industry}? 😊",
                "Hai kak, salam kenal! Mau cari apa hari ini? Kami siap bantu. ✨",
                "Selamat datang di {$profile?->business_name}! Silakan tanya-tanya menu atau jam buka ya kak. 🙏"
            ];

            // Only add promo greetings IF promo exists in KB
            if ($hasPromo) {
                $greetingResponses[] = "Halo! Selamat datang di {$profile?->business_name}. Mau info produk atau promo terbaru?";
                $greetingResponses[] = "Hai kak! Jangan lewatkan promo spesial hari ini ya. Mau info lengkapnya? 🎁";
            }
            
            return $this->formatResponse(
                $greetingResponses[array_rand($greetingResponses)],
                0.95,
                'greeting',
                $profile,
                $userId,
                $question
            );
        }

        // 4) Coba cari di Knowledge Base dulu (filter by profile if provided)
        $profileId = $profile?->id;
        // Prioritize passed userId, then profile's userId
        $searchUserId = $userId ?? $profile?->user_id;
        
        $articles = $this->searchRelevantArticles($question, 4, $profileId, $searchUserId);
        
        $imagePath = null;
        if (!$articles->isEmpty()) {
            $topArticle = $articles->first();
            if ($topArticle && $topArticle->image_path) {
                // Gunakan URL absolut dan pastikan skema (https/http) sesuai dengan APP_URL
                $baseUrl = config('app.url');
                $imagePath = $baseUrl . '/storage/' . $topArticle->image_path;
                
                // Pastikan jika APP_URL https, maka imagePath juga dipaksa https agar tidak SSL error
                if (str_starts_with($baseUrl, 'https')) {
                    $imagePath = str_replace('http://', 'https://', $imagePath);
                }
            }
        }

        // 5) Jika KB tidak kosong, gunakan context dari KB
        $context = '';
        if (!$articles->isEmpty()) {
            $context = $this->buildContext($articles, $question);

            // --- STRICT PROMO VALIDATION & HALLUCINATION CORRECTION ---
            // Mencegah halusinasi promo fiktif (Beli 1 Gratis 1, dsb)
            $promoKeywords = ['promo', 'diskon', 'discount', 'sale', 'potongan', 'cashback', 'gratis', 'bonus', 'free', 'hadiah', 'voucher'];
            $isPromoQuery = Str::contains($lower, $promoKeywords);
            
            if ($isPromoQuery) {
                $hasPromoInKb = false;
                foreach ($articles as $article) {
                    if (Str::contains(Str::lower($article->title . ' ' . $article->content), $promoKeywords)) {
                        $hasPromoInKb = true;
                        break;
                    }
                }
                
                if (!$hasPromoInKb) {
                    // Check for HALLUCINATION CHALLENGE (User complaining about previous false info)
                    // Keywords: "tadi", "katanya", "bilang", "sebelumnya", "kok", "loh"
                    $challengeKeywords = ['tadi', 'katanya', 'bilang', 'sebelumnya', 'kok', 'loh', 'kemarin', 'bapak', 'ibu', 'mbak', 'mas', 'bohong'];
                    $isChallenge = Str::contains($lower, $challengeKeywords);

                    $industry = $profile?->getIndustryLabel() ?? 'kami';
                    
                    if ($isChallenge) {
                        Log::warning('⚠️ HALLUCINATION CHALLENGE DETECTED', ['query' => $question]);
                        $msg = __('ai.hallucination_correction', ['industry' => $industry]);
                        return $this->formatResponse($msg, 1.0, 'system_hallucination_correction', $profile, $userId, $question);
                    }

                    Log::warning('🛑 BLOCKED PROMO HALLUCINATION', ['query' => $question]);
                    
                    $msg = __('ai.promo_block', ['industry' => $industry]);
                    return $this->formatResponse($msg, 1.0, 'system_promo_block', $profile, $userId, $question);
                }
            }
            // ------------------------------------

        } else {
             // ANTI-HALLUCINATION: If KB is empty, check if question is specific (price, schedule, etc)
             // If specific and no KB -> Return standard "I don't know" immediately without calling AI
             // This saves cost and prevents hallucination
             $specificKeywords = ['harga', 'biaya', 'tarif', 'bayar', 'jadwal', 'buka', 'tutup', 'dokter', 'poli', 'lokasi', 'alamat', 'syarat', 'cara'];
             $isSpecific = Str::contains($lower, $specificKeywords);
             
             if ($isSpecific) {
                 $industry = $profile?->getIndustryLabel() ?? 'bisnis';
                 $msg = __('ai.specific_no_data', ['industry' => $industry]);
                 return $this->formatResponse($msg, 1.0, 'system_no_data', $profile, $userId, $question);
             }
        }

        // 6) Panggil AI dengan prompt khusus WhatsApp
        $apiKey = config('services.perplexity.key');
        if (!$apiKey) {
            Log::error('❌ Perplexity API key missing');
            
            // Fallback response jika API key tidak ada
            $fallbackProfile = $profile ?? BusinessProfile::getActive();
            $fallbackMsg = $fallbackProfile ? $fallbackProfile->kb_fallback_message : "Mohon maaf, layanan sedang gangguan.";

            return $this->formatResponse($fallbackMsg, 0.5, 'fallback', $profile, $userId, $question, [], null, $imagePath);
        }

        // 7) Deteksi sentimen user
        $sentiment = $this->detectSentiment($question);
        
        $res = $this->callWhatsAppAI($question, $context, $conversationHistory, $profile, $sentiment);
        
        if (!$res || empty($res['answer'])) {
            // Jika AI tidak bisa jawab, berikan respons yang lebih natural
            return $this->formatResponse(__('ai.clarification'), 0.4, 'clarification', $profile, $userId, $question);
        }
        
        return $this->formatResponse(
            (string)($res['answer'] ?? ''),
            (float)($res['confidence'] ?? 0.8),
            !$articles->isEmpty() ? 'kb' : 'ai',
            $profile,
            $userId,
            $question,
            $articles->pluck('id')->toArray(),
            $sentiment['sentiment'] ?? 'neutral',
            $imagePath
        );
    }

    /**
     * Call Perplexity dengan prompt khusus WhatsApp yang lebih conversational
     * 
     * @param BusinessProfile|null $profile Optional profile to use (overrides default)
     * @param array $sentiment Hasil deteksi sentimen dari detectSentiment()
     */
    protected function callWhatsAppAI(string $question, string $context, array $history = [], ?BusinessProfile $profile = null, array $sentiment = []): ?array
    {
        $apiKey = config('services.perplexity.key');
        $baseUrl = rtrim((string) config('services.perplexity.url', 'https://api.perplexity.ai'), '/');
        $model = (string) config('services.perplexity.model', 'sonar-pro');
        $timeout = (int) config('services.perplexity.timeout', 60);

        $now = now()->format('l, d F Y H:i');
        $tomorrow = now()->addDay()->format('l');

        $contextSection = $context ? "\n\nKONTEKS KNOWLEDGE BASE:\n{$context}" : "\n\n(Tidak ada data spesifik di Knowledge Base untuk pertanyaan ini)";

        // USE PROVIDED PROFILE OR FALLBACK TO ACTIVE
        // Only fallback to getActive() if we have an authenticated user context (Dashboard/Test)
        // In Webhook (unauthenticated), we rely strictly on the provided $profile
        if (!$profile && \Illuminate\Support\Facades\Auth::check()) {
            $profile = BusinessProfile::getActive();
        }
        
        if (!$profile) {
            Log::warning('⚠️ No active BusinessProfile found, using fallback.');
            $systemPrompt = "Kamu adalah asisten virtual yang membantu. Jawab pertanyaan user dengan sopan.";
        } else {
            // Replace placeholders in the stored template
            $systemPrompt = str_replace(
                ['{business_name}', '{today}', '{now}', '{tomorrow}'], 
                [$profile->business_name, $now, $now, $tomorrow], 
                $profile->system_prompt_template
            );
            
            // INJECT BUSINESS CATEGORY & IDENTITY
            $industryLabel = $profile->getIndustryLabel();
            $systemPrompt .= "\n\nIDENTITAS BISNIS:\n- Nama: {$profile->business_name}\n- Kategori: {$industryLabel}\n- TUGAS: Hanya menjawab pertanyaan seputar {$industryLabel} di {$profile->business_name}.";
        }

        // 8) ADD AI TRAINING EXAMPLES (STYLE LEARNING)
        // Only use approved examples for safety
        $trainingExamples = AiTrainingExample::where('business_profile_id', $profile?->id)
            ->where('is_approved', true)
            ->latest()
            ->take(5)
            ->get();
        
        if ($trainingExamples->isNotEmpty()) {
            $systemPrompt .= "\n\nGAYA BAHASA YANG DISUKAI (Pelajari contoh di bawah ini):";
            foreach ($trainingExamples as $ex) {
                $systemPrompt .= "\nUser: {$ex->user_query}\nCS: {$ex->assistant_response}";
            }
            $systemPrompt .= "\n\nGunakan gaya bahasa, nada, dan keramahan yang sama seperti contoh CS di atas.";
        }

        $systemPrompt .= "\n\nATURAN KERAS (ANTI-HALUSINASI & ISOLASI TENANT):
1. KAMU ADALAH 'CLOSED SYSTEM AI'. Kamu HANYA boleh menjawab berdasarkan teks di bagian 'KONTEKS KNOWLEDGE BASE' di bawah.
2. JANGAN PERNAH MENGARANG JAWABAN. Jika informasi tidak ada di Konteks, JANGAN gunakan pengetahuan umum.
3. JIKA INFO TIDAK ADA:
   - JANGAN bilang 'Saya tidak tahu' atau 'Info tidak tersedia' saja.
   - Jawab: 'Mohon maaf kak, untuk info detailnya bisa langsung hubungi Admin kami via WA ya, atau cek sosial media {$profile->business_name} untuk update terbaru. Ada yang lain yang bisa saya bantu? 😊'
   - Tetap ramah dan tawarkan bantuan lain.
4. VALIDASI KATEGORI: Jika user bertanya tentang produk/topik di luar kategori '{$industryLabel}' (misal toko kopi ditanya skincare), JANGAN JAWAB. Alihkan kembali ke topik bisnis ini.
5. OUT-OF-SCOPE / TOXIC: Jika user berkata kasar atau aneh, tetap ramah: 'Maaf kak kalau ada yang kurang berkenan 😊 Bisa dijelaskan kendalanya? Kami siap bantu~'
6. DILARANG beralih ke topik lain selain bisnis {$profile->business_name}.
7. VARIASI STYLE: Jangan gunakan kalimat yang kaku. Gunakan bahasa yang natural, ramah, dan sesekali gunakan emoji yang relevan.
8. FORMAT OUTPUT WAJIB JSON.
Format: {\"answer\": \"Jawaban kamu disini\", \"confidence\": 0.9}

ATURAN KHUSUS PROMO & DISKON (SANGAT PENTING):
- HANYA sebutkan promo jika TERTULIS JELAS di Konteks.
- JANGAN PERNAH mengarang promo 'Beli 1 Gratis 1' atau diskon apapun jika tidak ada datanya.
- Jika user tanya promo dan TIDAK ADA di konteks, jawab: 'Mohon maaf kak, saat ini belum ada promo aktif. Tapi harga reguler kami sangat terjangkau kok! 😊'
- DILARANG MENJANJIKAN bonus/hadiah yang tidak ada di KB.

ATURAN KONSISTENSI & KEJUJURAN:
- JANGAN BOHONG atau ngeles jika user menangkap basah kesalahanmu.
- Jika user bilang 'katanya tadi ada promo', dan nyatanya TIDAK ADA di KB:
  1. AKUI KESALAHAN: 'Maaf kak, saya salah info sebelumnya.'
  2. KOREKSI: 'Yang benar belum ada promo saat ini.'
  3. JANGAN DENIAL: Jangan bilang 'Saya tidak pernah bilang begitu'.";

        // ATURAN GREETING (CONTEXT AWARE)
        // Jika ada history (percakapan berlanjut), JANGAN greeting berlebihan.
        if (!empty($history)) {
            $systemPrompt .= "\n\nATURAN GREETING (PENTING):";
            $systemPrompt .= "\n- INI ADALAH LANJUTAN PERCAKAPAN. JANGAN gunakan kata sapaan seperti 'Halo', 'Hai', 'Selamat pagi' lagi.";
            $systemPrompt .= "\n- Langsung jawab ke inti pertanyaan user.";
            $systemPrompt .= "\n- Contoh Salah: 'Halo kak! Untuk harga kopi...'";
            $systemPrompt .= "\n- Contoh Benar: 'Untuk harga kopi susu Rp18.000 kak...'";
        } else {
            $systemPrompt .= "\n\nATURAN GREETING:";
            $systemPrompt .= "\n- Karena ini awal percakapan, SAPA user dengan ramah (Halo/Hai).";
        }

        // Tambahkan instruksi konteks percakapan
        $systemPrompt .= "\n\nPENTING - KONTEKS PERCAKAPAN:
- SELALU perhatikan history chat sebelumnya
- Jika user hanya bilang 'boleh', 'oke', 'iya', 'mau' dll, itu berarti SETUJU dengan tawaranmu sebelumnya
- JANGAN tanya ulang 'ada apa?' jika user sudah setuju - langsung lanjutkan ke aksi berikutnya
- Contoh: Jika kamu tawarkan demo dan user bilang 'boleh', LANGSUNG arahkan ke cara daftar demo";

        // Tambahkan instruksi berdasarkan sentimen
        $sentimentType = $sentiment['sentiment'] ?? 'neutral';
        if ($sentimentType === 'frustrated') {
            $systemPrompt .= "\n\n⚠️ SENTIMEN NEGATIF TERDETEKSI:
- User terlihat frustasi/kecewa
- Tunjukkan empati terlebih dahulu ('Mohon maaf atas ketidaknyamanannya')
- Tawarkan solusi dengan cepat
- Jika tidak bisa bantu, tawarkan escalate ke CS manusia";
        } elseif ($sentimentType === 'confused') {
            $systemPrompt .= "\n\n❓ USER TERLIHAT BINGUNG:
- Jelaskan dengan bahasa yang lebih sederhana
- Gunakan contoh konkrit jika perlu
- Tanyakan bagian mana yang kurang jelas";
        } elseif ($sentimentType === 'positive') {
            $systemPrompt .= "\n\n😊 SENTIMEN POSITIF TERDETEKSI:
- User terlihat senang/puas
- Respons dengan antusias tapi tetap profesional
- Bisa tawarkan produk/layanan tambahan jika relevan";
        }

        $messages = [
            ["role" => "system", "content" => $systemPrompt],
        ];

        // Tambah history jika ada (untuk konteks percakapan)
        // Normalisasi history agar strictly alternating User-Assistant-User
        $cleanHistory = [];
        $rawHistory = array_slice($history, -4); // Ambil 4 terakhir

        foreach ($rawHistory as $h) {
            // Validasi dasar
            if (empty($h['content']) || !is_string($h['content']) || trim($h['content']) === '') {
                continue;
            }
            // Fallback role
            if (!in_array($h['role'], ['user', 'assistant'])) {
                $h['role'] = 'user';
            }

            // Jika kosong, langsung tambah
            if (empty($cleanHistory)) {
                $cleanHistory[] = $h;
                continue;
            }

            // Cek elemen terakhir
            $lastIdx = count($cleanHistory) - 1;
            $lastRole = $cleanHistory[$lastIdx]['role'];

            if ($lastRole === $h['role']) {
                // MERGE jika role sama (User -> User  =>  User (gabungan))
                $cleanHistory[$lastIdx]['content'] .= "\n\n" . $h['content'];
            } else {
                // Append jika beda
                $cleanHistory[] = $h;
            }
        }

        // Tambahkan ke message list utama
        foreach ($cleanHistory as $h) {
            $messages[] = $h;
        }

        // Pastikan pesan terakhir di history BUKAN user (karena kita akan append pesan user baru)
        // Jika terakhir adalah User, merge dengan pesan baru
        $finalUserMsg = "Pertanyaan user:{$contextSection}\n\nPERTANYAAN:\n{$question}\n\n(PENTING: Jawab hanya berdasarkan konteks di atas. Jika tidak ada info, jawab tidak tahu. Format JSON wajib.)";

        if (!empty($messages)) {
            $lastIdx = count($messages) - 1;
            if ($messages[$lastIdx]['role'] === 'user') {
                // Merge dengan pesan terakhir history
                $messages[$lastIdx]['content'] .= "\n\n-----------------\n" . $finalUserMsg;
            } else {
                // Append baru (karena terakhir adalah assistant)
                $messages[] = [
                    "role" => "user",
                    "content" => $finalUserMsg
                ];
            }
        } else {
            // History kosong, langsung tambah
            $messages[] = [
                "role" => "user", 
                "content" => $finalUserMsg
            ];
        }

        $payload = [
            "model" => $model,
            "temperature" => 0.1, // SANGAT RENDAH agar jawaban konsisten dan tidak kreatif/mengarang
            "messages" => $messages,
        ];

        try {
            Log::info('🤖 Perplexity Payload:', $payload); // DEBUG PAYLOAD

            $http = Http::timeout($timeout)
                ->withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->post($baseUrl . '/chat/completions', $payload);

            if (!$http->ok()) {
                Log::error('❌ WhatsApp AI HTTP error', [
                    'status' => $http->status(),
                    'body' => $http->body(), // CAPTURE ERROR BODY
                ]);
                return null;
            }

            $text = $http->json('choices.0.message.content');
            if (!$text) return null;

            $json = $this->safeJsonDecode((string)$text);
            if (!$json) return null;

            $answer = trim((string)($json['answer'] ?? ''));
            if ($answer === '') return null;

            // Bersihkan citation markers
            $answer = preg_replace('/\[\d+\]/', '', $answer);
            $answer = trim($answer);

            return [
                'answer' => $answer,
                'confidence' => max(0, min(1, (float)($json['confidence'] ?? 0.7))),
            ];
        } catch (\Throwable $e) {
            Log::error('❌ WhatsApp AI error', ['err' => $e->getMessage()]);
            return null;
        }
    }

    protected function searchRelevantArticles(string $question, int $limit = 4, ?int $profileId = null, ?int $userId = null)
    {
        $q = Str::lower($question);
        
        // 0. LOAD BUSINESS PROFILE TYPE IF AVAILABLE
        $businessType = 'common';
        if ($profileId) {
            $profile = BusinessProfile::find($profileId);
            $businessType = $profile ? $profile->business_type : 'common';
        }

        // 1. EXPAND SYNONYMS / INTENTS (CONFIGURABLE)
        $synonymsCommon = config('ai_synonyms.common', []);
        $synonymsIndustry = config("ai_synonyms.{$businessType}", config('ai_synonyms.general', []));
        
        $synonymMap = array_merge($synonymsCommon, $synonymsIndustry);

        // Stopwords Indonesia - kata yang tidak penting untuk pencarian
        $stopwords = [
            'halo', 'hai', 'hi', 'kak', 'aku', 'saya', 'mau', 'ingin', 'tanya', 
            'bertanya', 'minta', 'tolong', 'dong', 'yah', 'ya', 'nih', 'yang',
            'ini', 'itu', 'ada', 'tidak', 'bisa', 'boleh', 'gimana', 'bagaimana',
            'kapan', 'berapa', 'dimana', 'apakah', 'apaan', 'apa', 'kenapa',
            'mengapa', 'siapa', 'dengan', 'untuk', 'dari', 'dan', 'atau',
        ];

        $keywords = collect(preg_split('/\s+/u', $q))
            ->map(fn($k) => trim($k))
            ->filter(fn($k) => mb_strlen($k) >= 3)
            ->filter(fn($k) => !in_array($k, $stopwords, true)); // Keep collection to allow expansion

        // Expand keywords with synonyms
        $expandedKeywords = collect($keywords->all());
        foreach ($keywords as $k) {
            if (isset($synonymMap[$k])) {
                $expandedKeywords = $expandedKeywords->merge($synonymMap[$k]);
            }
        }
        
        $keywords = $expandedKeywords->unique()->values();

        // Build query with profile filter
        // Include articles that: match profile OR have no profile (global)
        $query = KbArticle::where('is_active', 1);

        // IMPORTANT: Multi-tenant filtering
        if ($userId) {
             // If we know the user, STRICTLY filter by user
             $query->withoutGlobalScopes()->where('user_id', $userId);
        } elseif (!\Illuminate\Support\Facades\Auth::check()) {
             // If no user context (webhook) and no userId provided -> RETURN EMPTY to prevent data leak
             Log::warning('⚠️ searchRelevantArticles called without UserID in webhook context. Returning empty.');
             return collect([]);
        }

        if ($profileId) {
            $query->where(function ($q) use ($profileId) {
                $q->whereNull('business_profile_id')
                  ->orWhere('business_profile_id', $profileId);
            });
        }
        $articles = $query->get();

        $scored = $articles->map(function ($a) use ($keywords) {
                $hay = Str::lower(($a->title ?? '') . " " . ($a->content ?? '') . " " . ($a->tags ?? ''));

                $matchedCount = 0;
                $totalKeywords = $keywords->count();

                if ($totalKeywords === 0) return [$a, 0];

                foreach ($keywords as $kw) {
                    if (Str::contains($hay, $kw)) $matchedCount++;
                }

                // Calculate Match Percentage (Similarity Score)
                // Bonus weight for title match
                $titleMatch = Str::contains(Str::lower($a->title), $keywords->first()) ? 0.3 : 0; // Increased bonus
                
                // If expanded keywords are many, we should not require ALL to match.
                // Adjusted formula: (matches / (totalKeywords * 0.8)) + titleMatch
                // This makes it lenient for synonym-heavy searches
                $denominator = max(1, $totalKeywords * 0.7); 
                $score = ($matchedCount / $denominator) + $titleMatch;

                return [$a, $score];
            })
            ->filter(fn($pair) => $pair[1] >= 0.35) // LOWERED THRESHOLD: 0.35 to catch expanded synonyms
            ->sortByDesc(fn($pair) => $pair[1])
            ->take($limit)
            ->map(fn($pair) => $pair[0]);

        return $scored->values();
    }

    protected function buildContext($articles, string $question = ''): string
    {
        $chunks = [];
        $questionLower = Str::lower($question);
        
        // Extract important keywords from question
        $importantKeywords = collect(preg_split('/\s+/u', $questionLower))
            ->filter(fn($k) => mb_strlen($k) >= 4)
            ->filter(fn($k) => !in_array($k, ['halo', 'kak', 'aku', 'mau', 'tanya', 'buka', 'jam', 'berapa', 'besok'], true))
            ->values()
            ->all();
        
        foreach ($articles as $a) {
            $title = $a->title ?: '(Tanpa judul)';
            $fullContent = trim((string)$a->content);
            $src = $a->source_url ?: '-';
            
            // Bersihkan konten yang menempel (misal: 16:00DALAM -> 16:00 DALAM)
            $fullContent = preg_replace('/(\d{2}:\d{2})([a-zA-Z])/', '$1 $2', $fullContent);
            $fullContent = preg_replace('/([a-zA-Z])(\d{2}:\d{2})/', '$1 $2', $fullContent);
            
            // Jika konten panjang dan ada keyword penting, cari bagian relevan
            if (mb_strlen($fullContent) > 3000 && !empty($importantKeywords)) {
                $relevantParts = $this->extractRelevantParts($fullContent, $importantKeywords, 2500);
                $content = $relevantParts ?: Str::limit($fullContent, 3000);
            } else {
                $content = Str::limit($fullContent, 3000);
            }

            $chunks[] = "### {$title}\nSumber: {$src}\nIsi:\n{$content}";
        }

        return implode("\n\n", $chunks);
    }
    
    /**
     * Ekstrak bagian konten yang mengandung keyword
     */
    protected function extractRelevantParts(string $content, array $keywords, int $maxLength): ?string
    {
        $contentLower = Str::lower($content);
        $parts = [];
        $usedRanges = [];
        
        foreach ($keywords as $keyword) {
            $pos = mb_strpos($contentLower, $keyword);
            if ($pos !== false) {
                // Ambil konteks 300 karakter sebelum dan sesudah
                $start = max(0, $pos - 300);
                $end = min(mb_strlen($content), $pos + 300);
                
                // Cek overlap dengan range yang sudah ada
                $overlap = false;
                foreach ($usedRanges as $range) {
                    if ($start <= $range[1] && $end >= $range[0]) {
                        $overlap = true;
                        break;
                    }
                }
                
                if (!$overlap) {
                    // Potong di batas baris untuk konteks lebih baik
                    $excerpt = mb_substr($content, $start, $end - $start);
                    
                    // Trim ke batas baris
                    if ($start > 0) {
                        $nlPos = mb_strpos($excerpt, "\n");
                        if ($nlPos !== false && $nlPos < 50) {
                            $excerpt = mb_substr($excerpt, $nlPos + 1);
                        }
                    }
                    
                    $parts[] = "..." . trim($excerpt) . "...";
                    $usedRanges[] = [$start, $end];
                }
            }
        }
        
        if (empty($parts)) {
            return null;
        }
        
        $result = implode("\n\n", $parts);
        
        // Batasi total panjang
        if (mb_strlen($result) > $maxLength) {
            $result = mb_substr($result, 0, $maxLength) . '...';
        }
        
        return $result;
    }

    protected function callPerplexity(string $question, string $context): ?array
    {
        $apiKey = config('services.perplexity.key');
        $baseUrl = rtrim((string) config('services.perplexity.url', 'https://api.perplexity.ai'), '/');
        $model = (string) config('services.perplexity.model', 'sonar-pro');
        $timeout = (int) config('services.perplexity.timeout', 60);

        $now = now()->format('l, d F Y H:i');
        $tomorrow = now()->addDay()->format('l');

        // Note: For answerFromKb (Legacy/Web), we might want to use the profile too, 
        // but for now let's keep it robust by using a generic or profile-based prompt if needed.
        // For simplicity in this refactor, we'll keep the existing prompt structure for web widget
        // but arguably we should unify it.
        // Let's stick to the existing web-widget prompt to not break that specific flow, 
        // as the user focused on the business logic which seems more relevant to the conversational bot.
        // Actually, let's use the profile here too for consistency!
        
        $profile = BusinessProfile::getActive();
        if ($profile) {
             $system = str_replace(
                ['{business_name}', '{today}', '{now}', '{tomorrow}'], 
                [$profile->business_name, $now, $now, $tomorrow], 
                $profile->system_prompt_template
            );
        } else {
             $system = <<<SYS
Kamu adalah asisten customer service yang ramah & membantu.
Jawab berdasarkan KONTEKS KB yang diberikan.
Output HARUS JSON valid: { "answer": "...", "confidence": 0.0-1.0 }
SYS;
        }

        $payload = [
            "model" => $model,
            "temperature" => 0.2,
            "messages" => [
                ["role" => "system", "content" => $system],
                ["role" => "user", "content" => "KONTEKS:\n".$context."\n\nPERTANYAAN:\n".$question],
            ],
        ];

        // retry sederhana (timeout/429/5xx)
        $tries = 3;
        for ($i = 1; $i <= $tries; $i++) {
            try {
                $http = Http::timeout($timeout)
                    ->withToken($apiKey)
                    ->acceptJson()
                    ->asJson()
                    ->post($baseUrl . '/chat/completions', $payload);

                if (!$http->ok()) {
                    Log::error('❌ Perplexity HTTP not ok', [
                        'try' => $i,
                        'status' => $http->status(),
                        'body' => $http->body(),
                    ]);

                    // kalau 429/5xx → coba lagi
                    if (in_array($http->status(), [429, 500, 502, 503, 504], true) && $i < $tries) {
                        usleep(400000 * $i); // 0.4s, 0.8s, 1.2s
                        continue;
                    }

                    return null;
                }

                $text = $http->json('choices.0.message.content');
                if (!$text) {
                    Log::error('❌ Perplexity empty response content', ['raw' => $http->json()]);
                    return null;
                }

                $json = $this->safeJsonDecode((string)$text);
                if (!$json) {
                    Log::error('❌ Perplexity response not JSON', ['text' => $text]);
                    return null;
                }

                $answer = trim((string)($json['answer'] ?? ''));
                $confidence = (float)($json['confidence'] ?? 0);

                if ($answer === '') return null;

                // 🧹 Bersihkan citation markers seperti [1], [2], [10]
                $answer = preg_replace('/\[\d+\]/', '', $answer);
                $answer = trim($answer);

                return [
                    'answer' => $answer,
                    'confidence' => max(0, min(1, $confidence)),
                ];
            } catch (\Throwable $e) {
                Log::error('❌ Perplexity call error', ['try' => $i, 'err' => $e->getMessage()]);
                if ($i < $tries) {
                    usleep(500000 * $i);
                    continue;
                }
                return null;
            }
        }

        return null;
    }

    protected function safeJsonDecode(string $text): ?array
    {
        $text = trim($text);

        // kalau AI nambah teks lain, ambil blok {...}
        if (!Str::startsWith($text, '{')) {
            if (preg_match('/\{.*\}/s', $text, $m)) {
                $text = $m[0];
            }
        }

        $json = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) return null;

        return $json;
    }

    /**
     * Generate a brief summary of the conversation
     */
    public function generateSummary(array $history): ?string
    {
        if (empty($history)) return null;

        $apiKey = config('services.perplexity.key');
        if (!$apiKey) return null;

        $historyText = "";
        foreach (array_slice($history, -10) as $msg) {
            $role = $msg['role'] === 'assistant' ? 'Bot' : 'User';
            $historyText .= "{$role}: {$msg['content']}\n";
        }

        $payload = [
            "model" => config('services.perplexity.model', 'sonar-pro'),
            "messages" => [
                ["role" => "system", "content" => "Berikan ringkasan 1 kalimat singkat (maksimal 15 kata) tentang inti pembicaraan/masalah user ini dalam Bahasa Indonesia. Langsung berikan ringkasannya saja tanpa kata pengantar."],
                ["role" => "user", "content" => "RIWAYAT CHAT:\n" . $historyText],
            ],
            "temperature" => 0.1
        ];

        try {
            $http = Http::timeout(20)->withToken($apiKey)->post(rtrim((string)config('services.perplexity.url', 'https://api.perplexity.ai'), '/') . '/chat/completions', $payload);
            if ($http->ok()) {
                return trim((string)$http->json('choices.0.message.content'));
            }
        } catch (\Exception $e) {
            Log::error('❌ AI Summary Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Generate smart quick reply suggestions for agent
     */
    public function generateSuggestions(array $history, ?BusinessProfile $profile = null): array
    {
        if (empty($history)) return [];

        $apiKey = config('services.perplexity.key');
        if (!$apiKey) return [];

        $profile = $profile ?? BusinessProfile::getActive();
        $businessContext = $profile ? "Nama Bisnis: {$profile->business_name}. " : "";
        
        $historyText = "";
        foreach (array_slice($history, -6) as $msg) {
            $role = $msg['role'] === 'assistant' ? 'Bot' : 'User';
            $historyText .= "{$role}: {$msg['content']}\n";
        }

        $payload = [
            "model" => config('services.perplexity.model', 'sonar-pro'),
            "messages" => [
                ["role" => "system", "content" => "Kamu adalah asisten ahli untuk Customer Service. {$businessContext}Berdasarkan riwayat chat, berikan 3 pilihan balasan singkat (Quick Replies) yang paling relevan untuk dikirim Admin ke User. Gunakan Bahasa Indonesia yang sopan dan ramah. Output HARUS JSON format: {\"suggestions\": [\"Opsi 1\", \"Opsi 2\", \"Opsi 3\"]}"],
                ["role" => "user", "content" => "RIWAYAT CHAT:\n" . $historyText],
            ],
            "temperature" => 0.5
        ];

        try {
            $http = Http::timeout(20)->withToken($apiKey)->post(rtrim((string)config('services.perplexity.url', 'https://api.perplexity.ai'), '/') . '/chat/completions', $payload);
            if ($http->ok()) {
                $content = $http->json('choices.0.message.content');
                $json = $this->safeJsonDecode($content);
                return $json['suggestions'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('❌ AI Suggestions Error: ' . $e->getMessage());
        }

        return [];
    }
    /**
     * Generate a comprehensive daily summary for the admin
     */
    public function generateDailySummary($messages, ?BusinessProfile $profile = null): ?string
    {
        if ($messages->isEmpty()) return "Tidak ada aktivitas chat hari ini.";

        $apiKey = config('services.perplexity.key');
        if (!$apiKey) return null;

        $businessName = $profile?->business_name ?? 'Bisnis';
        
        // Prepare message data for AI (limit to avoid token overflow)
        $messageDump = "";
        foreach ($messages->take(100) as $msg) {
            $sender = $msg->is_from_me ? 'Bot/CS' : 'User';
            $text = $msg->message ?: $msg->bot_reply;
            $messageDump .= "[{$msg->created_at->format('H:i')}] {$sender}: {$text}\n";
        }

        $payload = [
            "model" => config('services.perplexity.model', 'sonar-pro'),
            "messages" => [
                ["role" => "system", "content" => "Berikan ringkasan aktivitas chat hari ini dalam Bahasa Indonesia dengan gaya visual yang menarik (Gunakan emoji).
                
ATURAN KETAT:
- Maksimal 5 poin (baris).
- Poin 1: 📊 Statistik (Chat total, % AI vs % CS).
- Poin 2: 📝 Topik Utama.
- Poin 3: 🔥 Hot Leads (Sebutkan nomor HP disensor: 0812xxxx45).
- Poin 4: ⚠️ Komplain/Urgent (Jika ada).
- Poin 5: 💡 Rekomendasi singkat.
- Gunakan emoji bintang (⭐) untuk memberikan rating performa CS hari ini (1-5 bintang).
- JANGAN pakai kata pengantar. Langsung ke poin."],
                ["role" => "user", "content" => "LOG PESAN HARI INI:\n" . $messageDump],
            ],
            "temperature" => 0.2
        ];

        try {
            $http = Http::timeout(60)->withToken($apiKey)->post(rtrim((string)config('services.perplexity.url', 'https://api.perplexity.ai'), '/') . '/chat/completions', $payload);
            if ($http->ok()) {
                return trim((string)$http->json('choices.0.message.content'));
            }
        } catch (\Exception $e) {
            Log::error('❌ Daily Summary Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Scrub Personal Identifiable Information (PII) from text
     */
    public function scrubPII(string $text): string
    {
        // Mask Emails
        $text = preg_replace('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', '[EMAIL]', $text);
        
        // Mask Phone Numbers (matches various formats like 0812-3456-7890, +62..., etc.)
        // We look for sequences of 10-15 digits with common separators
        $text = preg_replace('/(\+?62|08)[0-9 \-]{8,15}/', '[NOMOR_HP]', $text);
        
        // Mask specific sensitive keywords (optional, can be expanded)
        $sensitiveKeywords = ['KTP', 'rekening', 'password', 'sandi', 'alamat'];
        foreach ($sensitiveKeywords as $kw) {
            $text = preg_replace('/' . preg_quote($kw, '/') . '\s*[:=]\s*\S+/i', $kw . ': [SENSITIF]', $text);
        }

        return $text;
    }

    /**
     * Check if active promo exists in KB
     */
    protected function hasActivePromo(?int $profileId, ?int $userId): bool
    {
        // Cek cepat apakah ada artikel aktif yang judulnya mengandung "promo" atau "diskon"
        $query = KbArticle::where('is_active', 1)
            ->where(function($q) {
                $q->where('title', 'like', '%promo%')
                  ->orWhere('title', 'like', '%diskon%')
                  ->orWhere('tags', 'like', '%promo%');
            });

        // Multi-tenant filter
        if ($userId) {
             $query->withoutGlobalScopes()->where('user_id', $userId);
        } elseif (!\Illuminate\Support\Facades\Auth::check()) {
             // Safety: if we don't know the user, be conservative and return false
             return false; 
        }

        if ($profileId) {
            $query->where(function ($q) use ($profileId) {
                $q->whereNull('business_profile_id')
                  ->orWhere('business_profile_id', $profileId);
            });
        }
        
        return $query->exists();
    }
}
