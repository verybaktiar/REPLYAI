<?php

namespace App\Services;

use App\Models\KbArticle;
use App\Models\BusinessProfile;
use App\Models\AiTrainingExample;
use App\Models\KbMissedQuery;
use App\Models\AiTrainingSuggestion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

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

    public function answerFromKb(string $question, ?BusinessProfile $profile = null): ?array
    {
        $question = trim($question);
        if ($question === '') return null;
        
        $profile = $profile ?? BusinessProfile::getActive();

        $articles = $this->searchRelevantArticles($question, 4, $profile?->id, $profile?->user_id, $profile);
        if ($articles->isEmpty()) {
            Log::info('📚 KB candidates empty', ['question' => $question]);
            return null;
        }

        $apiKey = config('services.megallm.key') ?? config('services.perplexity.key');
        if (!$apiKey) {
            Log::error('❌ AI API key missing (MegaLLM/Perplexity)');
            return null;
        }

        $context = $this->buildContext($articles, $question);

        $res = $this->callMegaLLM($question, $context);
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
        
        $historyCount = count($conversationHistory);
        Log::info('🤖 AI AnswerWhatsApp START', [
            'question' => $question,
            'user_id' => $userId,
            'profile_id' => $profile?->id,
            'history_count' => $historyCount,
            'has_history' => $historyCount > 0,
        ]);
        
        // RATE LIMITING: Check if user has exceeded AI request limit
        $rateLimitKey = 'ai_rate_limit:' . ($userId ?? 'guest');
        $rateLimitMax = $profile?->ai_rate_limit_per_hour ?? 100;
        $currentCount = (int) Cache::get($rateLimitKey, 0);
        
        Log::debug('🤖 Rate limit check', [
            'key' => $rateLimitKey,
            'current' => $currentCount,
            'max' => $rateLimitMax,
        ]);
        
        if ($currentCount >= $rateLimitMax) {
            Log::warning('🚫 AI Rate limit exceeded', [
                'user_id' => $userId,
                'count' => $currentCount,
                'limit' => $rateLimitMax,
            ]);
            return [
                'answer' => "Mohon maaf, layanan AI sementara tidak tersedia karena batas penggunaan tercapai. Silakan hubungi Admin kami ya.",
                'confidence' => 0.5,
                'source' => 'rate_limited',
            ];
        }
        
        // Increment rate limit counter
        Cache::put($rateLimitKey, $currentCount + 1, now()->addHour());

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
            $businessName = $profile?->business_name ?? 'Replai';
            
            // DYNAMIC GREETING: Cek apakah ada promo aktif di KB
            $profileId = $profile?->id;
            $searchUserId = $userId ?? $profile?->user_id;
            
            Log::debug('🎭 GREETING CHECK', [
                'profileId' => $profileId,
                'searchUserId' => $searchUserId,
                'businessName' => $businessName,
                'industry' => $industry,
            ]);
            
            $hasPromo = $this->hasActivePromo($profileId, $searchUserId);
            
            // Cek apakah ada konten produk/menu di KB
            $hasProductData = $this->hasProductContent($profileId, $searchUserId);
            
            Log::debug('🎭 GREETING DECISION', [
                'hasProductData' => $hasProductData,
                'hasPromo' => $hasPromo,
            ]);

            // SAFE greetings - tidak menjanjikan apa yang tidak ada di KB
            if ($hasProductData) {
                // Jika ada data produk, boleh tawarkan menu/best seller
                $greetingResponses = [
                    "Halo kak! Ada yang bisa kami bantu seputar {$businessName}? 😊",
                    "Hai kak! Selamat datang. Ada yang bisa dibantu hari ini? ✨",
                    "Halo! Selamat datang di {$businessName}. Silakan tanya-tanya ya kak. 🙏",
                    "Hai kak, salam kenal! Ada yang bisa kami bantu? 😊"
                ];
            } else {
                // Jika TIDAK ada data produk, greeting netral tanpa janji
                $greetingResponses = [
                    "Halo kak! Selamat datang di {$businessName}. Ada yang bisa kami bantu? 😊",
                    "Hai kak! Selamat datang. Ada yang bisa dibantu hari ini? ✨",
                    "Halo! Selamat datang di {$businessName}. Silakan tanya-tanya ya kak. 🙏",
                    "Hai kak, salam kenal! Ada yang bisa kami bantu? 😊"
                ];
            }

            // Only add promo greetings IF promo exists in KB
            if ($hasPromo) {
                $greetingResponses[] = "Halo! Selamat datang di {$businessName}. Mau info produk atau promo terbaru?";
                $greetingResponses[] = "Hai kak! Jangan lewatkan promo spesial hari ini ya. Mau info lengkapnya? 🎁";
            }
            
            $selectedGreeting = $greetingResponses[array_rand($greetingResponses)];
            
            Log::info('🎭 GREETING SENT', [
                'greeting' => $selectedGreeting,
                'hasProductData' => $hasProductData,
                'hasPromo' => $hasPromo,
            ]);
            
            return [
                'answer' => $selectedGreeting,
                'confidence' => 0.95,
                'source' => 'greeting',
            ];
        }

        // 4) Coba cari di Knowledge Base dulu (filter by profile if provided)
        $profileId = $profile?->id;
        // Prioritize passed userId, then profile's userId
        $searchUserId = $userId ?? $profile?->user_id;
        
        $articles = $this->searchRelevantArticles($question, 4, $profileId, $searchUserId, $profile);
        
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
                        return [
                            'answer' => "Mohon maaf sekali kak 🙏 Sepertinya saya salah memberikan informasi sebelumnya. \n\nSetelah saya cek ulang sistem, saat ini MEMANG BELUM ADA promo aktif. Mohon abaikan info yang salah tadi ya kak. \n\nSebagai gantinya, mau saya rekomendasikan menu {$industry} yang paling worth it? 😊",
                            'confidence' => 1.0,
                            'source' => 'system_hallucination_correction'
                        ];
                    }

                    Log::warning('🛑 BLOCKED PROMO HALLUCINATION', ['query' => $question]);
                    
                    return [
                        'answer' => "Mohon maaf kak, saat ini belum ada promo aktif. 🙏\n\nTapi kami punya menu {$industry} favorit yang best seller lho! Mau lihat menunya? 😊",
                        'confidence' => 1.0,
                        'source' => 'system_promo_block'
                    ];
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
                 $businessName = $profile?->business_name ?? 'kami';
                 
                 // Extract what user is asking about
                 $topic = '';
                 if (Str::contains($lower, ['poli', 'dokter', 'spesialis'])) {
                     $topic = 'poli/spesialis';
                 } elseif (Str::contains($lower, ['jadwal', 'buka', 'tutup', 'jam'])) {
                     $topic = 'jadwal operasional';
                 } elseif (Str::contains($lower, ['harga', 'biaya', 'tarif'])) {
                     $topic = 'harga dan biaya';
                 } elseif (Str::contains($lower, ['lokasi', 'alamat'])) {
                     $topic = 'lokasi';
                 }
                 
                 if ($topic) {
                     return [
                         'answer' => "Mohon maaf kak, informasi tentang {$topic} belum tersedia di sistem kami. 😊\n\nSilakan hubungi Admin {$businessName} untuk info lengkapnya ya. Terima kasih!",
                         'confidence' => 1.0,
                         'source' => 'system_no_data'
                     ];
                 }
                 
                 return [
                     'answer' => "Mohon maaf kak, untuk info detailnya bisa langsung hubungi Admin {$businessName} ya. Kami siap membantu! 😊",
                     'confidence' => 1.0,
                     'source' => 'system_no_data'
                 ];
             }
        }

        // 6) Panggil AI dengan prompt khusus WhatsApp
        $apiKey = config('services.megallm.key') ?? config('services.perplexity.key');
        if (!$apiKey) {
            Log::error('❌ AI API key missing (MegaLLM/Perplexity)');
            
            // Fallback response jika API key tidak ada
            $fallbackProfile = $profile ?? BusinessProfile::getActive();
            $fallbackMsg = $fallbackProfile ? $fallbackProfile->kb_fallback_message : "Mohon maaf, layanan sedang gangguan.";

            return [
                'answer' => $fallbackMsg,
                'confidence' => 0.5,
                'source' => 'fallback',
                'image_url' => $imagePath,
            ];
        }

        // 7) Deteksi sentimen user
        $sentiment = $this->detectSentiment($question);
        
        $res = $this->callMegaLLMWhatsApp($question, $context, $conversationHistory, $profile, $sentiment);
        
        if (!$res || empty($res['answer'])) {
            // Track missed query
            $this->trackMissedQuery($question, $userId, $profile?->id);
            
            // Smart Fallback dengan suggestions jika enabled
            if ($profile?->enable_smart_fallback) {
                $fallback = $this->getSmartFallback($question, $profile, $userId);
                if ($fallback) {
                    return $fallback;
                }
            }
            
            // Jika AI tidak bisa jawab, berikan respons yang lebih natural
            return [
                'answer' => "Hmm, bisa dijelaskan lebih detail kak?",
                'confidence' => 0.4,
                'source' => 'clarification',
            ];
        }
        
        $confidence = (float)($res['confidence'] ?? 0.8);
        
        // Track missed query jika confidence terlalu rendah
        if ($confidence < ($profile?->kb_match_threshold ?? 0.35)) {
            $this->trackMissedQuery($question, $userId, $profile?->id);
            
            // Smart Fallback dengan suggestions jika enabled
            if ($profile?->enable_smart_fallback) {
                $fallback = $this->getSmartFallback($question, $profile, $userId);
                if ($fallback) {
                    return $fallback;
                }
            }
        }

        return [
            'answer' => (string)($res['answer'] ?? ''),
            'confidence' => $confidence,
            'source' => !$articles->isEmpty() ? 'kb' : 'ai',
            'sentiment' => $sentiment['sentiment'] ?? 'neutral',
            'image_url' => $imagePath,
        ];
    }

    /**
     * Call MegaLLM dengan prompt khusus WhatsApp yang lebih conversational
     * 
     * @param BusinessProfile|null $profile Optional profile to use (overrides default)
     * @param array $sentiment Hasil deteksi sentimen dari detectSentiment()
     */
    protected function callMegaLLMWhatsApp(string $question, string $context, array $history = [], ?BusinessProfile $profile = null, array $sentiment = []): ?array
    {
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
            // Fallback terminology for when no profile
            $terminology = [
                'user' => 'Pelanggan',
                'user_plural' => 'Pelanggan',
                'product' => 'Produk',
                'product_plural' => 'Produk',
                'category' => 'Kategori',
                'staff' => 'Staff',
                'action' => 'pemesanan',
                'place' => 'tempat',
            ];
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
            
            // Get effective terminology for this business type
            $terminology = $profile->getEffectiveTerminology();
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
        $historyCount = count($history);
        if (!empty($history)) {
            $systemPrompt .= "\n\n🚫 ATURAN GREETING - WAJIB DIPATUHI (SANGAT PENTING - LANGGAR = ERROR):";
            $systemPrompt .= "\n- INI ADALAH LANJUTAN PERCAKAPAN (sudah {$historyCount} pesan sebelumnya).";
            $systemPrompt .= "\n- DILARANG KERAS menggunakan kata sapaan seperti 'Halo', 'Hai', 'Hi', 'Selamat' di awal jawaban.";
            $systemPrompt .= "\n- DILARANG KERAS mengatakan 'Maaf kak, maksudnya...' atau 'Maaf saya tidak paham' - ini SANGAT MENYEBALKAN!";
            $systemPrompt .= "\n- DILARANG KERAS menanyakan 'Mau yang mana?' atau 'Tanya tentang apa?' - user sudah JELAS dari history!";
            $systemPrompt .= "\n- LANGSUNG JAWAB PERTANYAAN, tanpa basa-basi, tanpa sapaan, tanpa permintaan maaf.";
            $systemPrompt .= "\n- Jawaban HARUS dimulai langsung dengan informasi yang diminta.";
            $systemPrompt .= "\n\nContoh SALAH (AKAN MENYEBABKAN ERROR):";
            $systemPrompt .= "\n❌ 'Halo kak! Untuk harga {$terminology['product']}...'";
            $systemPrompt .= "\n❌ 'Maaf kak, maksudnya tanya tentang {$profile->business_name}?' - JANGAN PERNAH!";
            $systemPrompt .= "\n❌ 'Maaf kak, saya tidak paham' - JANGAN PERNAH!";
            $systemPrompt .= "\n❌ 'Mau yang mana?' - JANGAN PERNAH!";
            $systemPrompt .= "\n❌ 'Bisa dijelaskan lagi?' - JANGAN PERNAH!";
            $systemPrompt .= "\n\nContoh BENAR (HARUS SEPERTI INI):";
            $systemPrompt .= "\n✅ 'Untuk {$terminology['product']} A harga Rp50.000...' - LANGSUNG INFO!";
            $systemPrompt .= "\n✅ '{$terminology['staff']} kami tersedia hari Senin jam 09:00...' - LANGSUNG INFO!";
            $systemPrompt .= "\n✅ 'Rp75.000 sudah termasuk pajak.' - LANGSUNG INFO!";
        } else {
            $systemPrompt .= "\n\nATURAN GREETING:";
            $systemPrompt .= "\n- Karena ini awal percakapan (pesan pertama), SAPA user dengan ramah (Halo/Hai).";
        }

        // Tambahkan instruksi konteks percakapan
        $systemPrompt .= "\n\nPENTING - KONTEKS PERCAKAPAN:
- SELALU perhatikan history chat sebelumnya
- Jika user hanya bilang 'boleh', 'oke', 'iya', 'mau' dll, itu berarti SETUJU dengan tawaranmu sebelumnya
- JANGAN tanya ulang 'ada apa?' jika user sudah setuju - langsung lanjutkan ke aksi berikutnya
- Contoh: Jika kamu tawarkan {$terminology['action']} dan user bilang 'boleh', LANGSUNG arahkan ke cara {$terminology['action']}";

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
        $memoryLimit = $profile?->conversation_memory_limit ?? 10;
        $rawHistory = array_slice($history, -$memoryLimit); // Ambil N terakhir sesuai konfigurasi

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

        // Gunakan AiProviderService dengan failover
        $providerService = app(AiProviderService::class);
        $result = $providerService->chatCompletion($messages, null);

        if (!$result['success']) {
            Log::error('❌ All AI providers failed for WhatsApp', ['error' => $result['error']]);
            return null;
        }

        Log::info('✅ AI Provider success for WhatsApp', [
            'provider' => $result['provider'], 
            'model' => $result['model']
        ]);

        $text = $result['answer'];
        if (!$text) return null;

        $json = $this->safeJsonDecode((string)$text);
        if (!$json) return null;

            $answer = trim((string)($json['answer'] ?? ''));
            if ($answer === '') return null;

            // Bersihkan citation markers
            $answer = preg_replace('/\[\d+\]/', '', $answer);
            $answer = trim($answer);

            // POST-PROCESSING: Hapus greeting jika ada history (fallback jika AI tidak patuh)
            $historyCount = count($history);
            Log::debug('🧹 Post-processing check', [
                'history_count' => $historyCount,
                'original_answer' => $answer,
            ]);
            
            if ($historyCount > 0) {
                $originalAnswer = $answer;
                
                // Hapus greeting patterns di awal jawaban - MORE COMPREHENSIVE
                $greetingPatterns = [
                    // Basic greetings
                    '/^(Halo|Hai|Hi|Hello|Hey)[\s,!.]+/iu',
                    '/^(Selamat\s+(pagi|siang|sore|malam))[\s,!.]+/iu',
                    
                    // "Maaf kak..." variations (the most annoying one!)
                    '/^(Maaf\s+(ya\s+)?kak,?\s+maksudnya)[\s,!.:]*/iu',
                    '/^(Maaf\s+kak,?\s+(ya\s+)?maksudnya)[\s,!.:]*/iu',
                    '/^(Maaf,?\s+maksudnya)[\s,!.:]*/iu',
                    '/^(Maksudnya,?\s+maaf)[\s,!.:]*/iu',
                    '/^(Maaf\s+(ya\s+)?kak?)[\s,!.]+/iu',
                    
                    // "Saya tidak paham" variations
                    '/^(Maaf,?\s+saya\s+(tidak\s+)?(paham|mengerti))[\s,!.]+/iu',
                    '/^(Saya\s+(tidak\s+)?(paham|mengerti))[\s,!.]+/iu',
                    
                    // "Mau yang mana" variations
                    '/^(Mau\s+(yang\s+)?mana)[\s?.]+/iu',
                    '/^(Mau\s+tanya\s+tentang)[\s?.]+/iu',
                    '/^(Tanya\s+tentang)[\s?.]+/iu',
                    
                    // Greeting + question combos
                    '/^(Halo|Hai|Hi)[\s,!.]+\s*(kak|kakak|mba|mas)[\s,!.]+/iu',
                    '/^(Halo|Hai|Hi)[\s,!.]+\s*(kak|kakak|mba|mas)?[\s,!.]+/iu',
                ];
                
                $iteration = 0;
                $maxIterations = 3; // Prevent infinite loops
                do {
                    $changed = false;
                    foreach ($greetingPatterns as $pattern) {
                        $newAnswer = preg_replace($pattern, '', $answer);
                        if ($newAnswer !== $answer) {
                            $answer = $newAnswer;
                            $changed = true;
                            Log::debug('🧹 Pattern matched and removed', [
                                'pattern' => $pattern,
                                'current_answer' => $answer,
                            ]);
                        }
                    }
                    $iteration++;
                } while ($changed && $iteration < $maxIterations);
                
                $answer = trim($answer);
                
                // Also remove leading emoji that might be left after greeting removal
                $answer = preg_replace('/^[\s\p{P}\p{So}]+/u', '', $answer);
                $answer = trim($answer);
                
                // Capitalize first letter
                if (!empty($answer)) {
                    $answer = ucfirst($answer);
                }
                
                if ($originalAnswer !== $answer) {
                    Log::info('🧹 Greeting removed by post-processing', [
                        'original' => $originalAnswer,
                        'cleaned' => $answer,
                        'history_count' => $historyCount,
                    ]);
                }
            }

        return [
            'answer' => $answer,
            'confidence' => max(0, min(1, (float)($json['confidence'] ?? 0.7))),
        ];
    }

    protected function searchRelevantArticles(string $question, int $limit = 4, ?int $profileId = null, ?int $userId = null, ?BusinessProfile $profile = null)
    {
        $q = Str::lower($question);
        
        // Get configurable threshold from profile or use default
        $threshold = $profile?->kb_match_threshold ?? 0.35;

        // 1. EXPAND SYNONYMS / INTENTS
        // Map common intents to keywords that might appear in KB articles
        // NOTE: This is a comprehensive default list for multi-tenant SaaS.
        // TODO: Make this configurable per business_profile for better accuracy.
        $synonymMap = [
            // === PRODUK & JUAL BELI (All Industries) ===
            'jualan'  => ['produk', 'menu', 'katalog', 'harga', 'layanan', 'daftar', 'item', 'barang'],
            'jual'    => ['produk', 'menu', 'katalog', 'harga', 'layanan', 'tersedia', 'stok'],
            'beli'    => ['cara pesan', 'order', 'pembayaran', 'cara order', 'cara beli'],
            'order'   => ['cara pesan', 'pembayaran', 'rekening', 'checkout', 'pesan'],
            'pesan'   => ['cara pesan', 'pembayaran', 'booking', 'order', 'pemesanan'],
            'info'    => ['produk', 'menu', 'layanan', 'tentang', 'profil', 'detail', 'keterangan'],
            'detail'  => ['info', 'keterangan', 'spesifikasi', 'deskripsi'],
            
            // === HARGA & PEMBAYARAN ===
            'biaya'   => ['harga', 'tarif', 'price', 'ongkir', 'cost', 'bayar'],
            'murah'   => ['harga', 'promo', 'diskon', 'hemat', 'economy'],
            'mahal'   => ['harga', 'premium', 'kualitas', 'eksklusif', 'luxury'],
            'promo'   => ['diskon', 'sale', 'potongan', 'bonus', 'free', 'gratis', 'cashback', 'voucher'],
            'diskon'  => ['promo', 'sale', 'potongan', 'hemat', 'murah'],
            'bayar'   => ['pembayaran', 'payment', 'transfer', 'qris', 'cash', 'tunai', 'kartu', 'debit', 'kredit'],
            
            // === PAKET & SUBSCRIPTION (SaaS, Gym, etc) ===
            'paket'   => ['plan', 'harga', 'layanan', 'produk', 'subscription', 'berlangganan', 'paket'],
            'plan'    => ['paket', 'harga', 'layanan', 'subscription', 'berlangganan'],
            
            // === LOKASI & OPERASIONAL ===
            'lokasi'  => ['alamat', 'map', 'tempat', 'cabang', 'kota', 'lokasi', 'where'],
            'dimana'  => ['alamat', 'map', 'tempat', 'cabang', 'lokasi'],
            'buka'    => ['jam', 'jadwal', 'operasional', 'jam buka', 'waktu'],
            'tutup'   => ['jam', 'jadwal', 'operasional', 'jam tutup', 'waktu'],
            'jam'     => ['jadwal', 'operasional', 'waktu', 'buka', 'tutup'],
            
            // === F&B (Restoran, Kafe, Catering) ===
            'menu'    => ['makanan', 'minuman', 'daftar', 'katalog', 'harga'],
            'makanan' => ['menu', 'masakan', 'hidangan', 'food'],
            'minuman' => ['menu', 'drink', 'beverage', 'es', 'kopi', 'tea'],
            'catering'=> ['pesan', 'paket', 'nasi box', 'prasmanan'],
            
            // === KESEHATAN (Klinik, Rumah Sakit, Apotek) ===
            'dokter'  => ['dokter', 'poli', 'spesialis', 'jadwal dokter', 'praktek'],
            'poli'    => ['dokter', 'spesialis', 'klinik', 'layanan'],
            'obat'    => ['apotek', 'resep', 'farmasi', 'medication'],
            'janji'   => ['booking', 'reservasi', 'janji temu', 'appointment'],
            
            // === E-COMMERCE & RETAIL ===
            'stok'    => ['tersedia', 'ready', 'habis', 'available', 'inventory'],
            'ukuran'  => ['size', 's', 'm', 'l', 'xl', 'dimensi', 'besar', 'kecil'],
            'warna'   => ['color', 'putih', 'hitam', 'merah', 'biru', 'varian'],
            'pengiriman'=> ['kirim', 'delivery', 'expedisi', 'jne', 'jnt', 'sicepat', 'ongkir'],
            
            // === JASA & SERVIS ===
            'servis'  => ['perbaikan', 'repair', 'service', 'garansi', 'maintenance'],
            'garansi' => ['warranty', 'claim', 'service', 'perbaikan gratis'],
            
            // === PENDIDIKAN & KURSUS ===
            'kursus'  => ['kelas', 'course', 'pelatihan', 'training', 'materi'],
            'kelas'   => ['kursus', 'jadwal', 'materi', 'pelajaran', 'lesson'],
            'ujian'   => ['test', 'evaluasi', 'assessment', 'sertifikasi'],
            
            // === REAL ESTATE & PROPERTY ===
            'properti'=> ['rumah', 'apartemen', 'ruko', 'tanah', 'property'],
            'sewa'    => ['rent', 'kontrak', 'lease', 'bulanan', 'tahunan'],
            'jual'    => ['beli', 'property', 'rumah', 'investasi'],
            
            // === FASILITAS & LAINNYA ===
            'wifi'    => ['fasilitas', 'internet', 'koneksi', 'password'],
            'parkir'  => ['fasilitas', 'lokasi', 'tempat', 'mobil', 'motor'],
            'booking' => ['reservasi', 'pesan tempat', 'meja', 'acara', 'meeting'],
            'reservasi'=> ['booking', 'pesan tempat', 'meja', 'janji'],
            'member'  => ['loyalty', 'poin', 'reward', 'daftar', 'membership'],
            'cara'    => ['tutorial', 'panduan', 'guide', 'how to', 'langkah', 'step'],
            'syarat'  => ['ketentuan', 'terms', 'rules', 'peraturan', 's&k'],
            'faq'     => ['pertanyaan', 'question', 'help', 'bantuan', 'cara'],
        ];

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

        $scoredPairs = $articles->map(function ($a) use ($keywords) {
                $hay = Str::lower(($a->title ?? '') . " " . ($a->content ?? '') . " " . ($a->tags ?? ''));

                $matchedCount = 0;
                $totalKeywords = $keywords->count();

                if ($totalKeywords === 0) return ['article' => $a, 'score' => 0];

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

                return ['article' => $a, 'score' => $score];
            })
            ->filter(fn($item) => $item['score'] >= $threshold)
            ->sortByDesc(fn($item) => $item['score'])
            ->take($limit);

        $result = $scoredPairs->map(fn($item) => $item['article'])->values();
        
        // LOGGING: Track KB search results
        Log::debug('🔍 KB SEARCH RESULT', [
            'question' => $question,
            'profile_id' => $profileId,
            'user_id' => $userId,
            'threshold' => $threshold,
            'keywords' => $keywords->toArray(),
            'total_articles_checked' => $articles->count(),
            'articles_found' => $result->count(),
            'articles' => $scoredPairs->map(fn($item) => [
                'id' => $item['article']->id,
                'title' => $item['article']->title,
                'score' => $item['score'],
            ])->toArray(),
        ]);

        return $result;
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

    /**
     * Call AI Provider dengan failover otomatis (MegaLLM -> SumoPod)
     */
    protected function callMegaLLM(string $question, string $context): ?array
    {
        $now = now()->format('l, d F Y H:i');
        $tomorrow = now()->addDay()->format('l');
        
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

        $messages = [
            ["role" => "system", "content" => $system],
            ["role" => "user", "content" => "KONTEKS:\n".$context."\n\nPERTANYAAN:\n".$question],
        ];

        // Gunakan AiProviderService dengan failover
        $providerService = app(AiProviderService::class);
        $result = $providerService->chatCompletion($messages, null);

        if (!$result['success']) {
            Log::error('❌ All AI providers failed for KB call', ['error' => $result['error']]);
            return null;
        }

        Log::info('✅ AI Provider success for KB', ['provider' => $result['provider'], 'model' => $result['model']]);

        $text = $result['answer'];
        $json = $this->safeJsonDecode((string)$text);
        
        if (!$json) {
            Log::error('❌ AI response not JSON', ['text' => $text]);
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
    }

    /**
     * Legacy: Call Perplexity API (fallback)
     * @deprecated Use callMegaLLM instead
     */
    protected function callPerplexity(string $question, string $context): ?array
    {
        // Delegate ke MegaLLM
        return $this->callMegaLLM($question, $context);
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

        $apiKey = config('services.megallm.key') ?? config('services.perplexity.key');
        if (!$apiKey) return null;
        
        $baseUrl = rtrim((string) config('services.megallm.url', 'https://ai.megallm.io/v1'), '/');
        $model = (string) config('services.megallm.model', 'mistral-large-3-675b-instruct-2512');

        $historyText = "";
        foreach (array_slice($history, -10) as $msg) {
            $role = $msg['role'] === 'assistant' ? 'Bot' : 'User';
            $historyText .= "{$role}: {$msg['content']}\n";
        }

        $payload = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => "Berikan ringkasan 1 kalimat singkat (maksimal 15 kata) tentang inti pembicaraan/masalah user ini dalam Bahasa Indonesia. Langsung berikan ringkasannya saja tanpa kata pengantar."],
                ["role" => "user", "content" => "RIWAYAT CHAT:\n" . $historyText],
            ],
            "temperature" => 0.1
        ];

        try {
            $http = Http::timeout(20)->withToken($apiKey)->post($baseUrl . '/chat/completions', $payload);
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

        $apiKey = config('services.megallm.key') ?? config('services.perplexity.key');
        if (!$apiKey) return [];
        
        $baseUrl = rtrim((string) config('services.megallm.url', 'https://ai.megallm.io/v1'), '/');
        $model = (string) config('services.megallm.model', 'mistral-large-3-675b-instruct-2512');

        $profile = $profile ?? BusinessProfile::getActive();
        $businessContext = $profile ? "Nama Bisnis: {$profile->business_name}. " : "";
        
        $historyText = "";
        foreach (array_slice($history, -6) as $msg) {
            $role = $msg['role'] === 'assistant' ? 'Bot' : 'User';
            $historyText .= "{$role}: {$msg['content']}\n";
        }

        $payload = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => "Kamu adalah asisten ahli untuk Customer Service. {$businessContext}Berdasarkan riwayat chat, berikan 3 pilihan balasan singkat (Quick Replies) yang paling relevan untuk dikirim Admin ke User. Gunakan Bahasa Indonesia yang sopan dan ramah. Output HARUS JSON format: {\"suggestions\": [\"Opsi 1\", \"Opsi 2\", \"Opsi 3\"]}"],
                ["role" => "user", "content" => "RIWAYAT CHAT:\n" . $historyText],
            ],
            "temperature" => 0.5
        ];

        try {
            $http = Http::timeout(20)->withToken($apiKey)->post($baseUrl . '/chat/completions', $payload);
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

        $apiKey = config('services.megallm.key') ?? config('services.perplexity.key');
        if (!$apiKey) return null;
        
        $baseUrl = rtrim((string) config('services.megallm.url', 'https://ai.megallm.io/v1'), '/');
        $model = (string) config('services.megallm.model', 'mistral-large-3-675b-instruct-2512');

        $businessName = $profile?->business_name ?? 'Bisnis';
        
        // Prepare message data for AI (limit to avoid token overflow)
        $messageDump = "";
        foreach ($messages->take(100) as $msg) {
            // FIX: Use direction field instead of is_from_me (which doesn't exist in DB)
            $sender = $msg->direction === 'outgoing' ? 'Bot/CS' : 'User';
            $text = $msg->message ?: $msg->bot_reply;
            $messageDump .= "[{$msg->created_at->format('H:i')}] {$sender}: {$text}\n";
        }

        $payload = [
            "model" => $model,
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
            $http = Http::timeout(60)->withToken($apiKey)->post($baseUrl . '/chat/completions', $payload);
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

    /**
     * Check if product/menu/content exists in KB
     * Used for adaptive greeting - tidak menjanjikan jika tidak ada datanya
     * NOTE: Comprehensive keywords for multi-tenant SaaS (various industries)
     */
    protected function hasProductContent(?int $profileId, ?int $userId): bool
    {
        // Cek apakah ada artikel aktif yang mengandung kata-kata terkait produk/layanan
        // Keywords mencakup berbagai industri: F&B, Retail, Jasa, Kesehatan, Pendidikan, dll
        $productKeywords = [
            // Umum
            'produk', 'product', 'layanan', 'service', 'jual', 'jualan', 'harga', 'price',
            // F&B
            'menu', 'makanan', 'minuman', 'katalog', 'item',
            // Kesehatan
            'dokter', 'poli', 'obat', 'treatment', 'perawatan',
            // Jasa
            'servis', 'repair', 'kursus', 'kelas', 'course',
            // Real Estate
            'properti', 'rumah', 'apartemen', 'sewa',
            // Detail
            'katalog', 'daftar', 'pilihan', 'varian'
        ];
        
        $query = KbArticle::where('is_active', 1);
        
        // Build OR query for product keywords
        $query->where(function($q) use ($productKeywords) {
            foreach ($productKeywords as $keyword) {
                $q->orWhere('title', 'like', "%{$keyword}%")
                  ->orWhere('content', 'like', "%{$keyword}%")
                  ->orWhere('tags', 'like', "%{$keyword}%");
            }
        });

        // Multi-tenant filter - sama dengan hasActivePromo
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

    /**
     * Track missed query untuk analytics
     * Catat pertanyaan yang tidak terjawab oleh AI/KB
     */
    protected function trackMissedQuery(string $question, ?int $userId, ?int $profileId): void
    {
        if (!$userId) return;
        
        try {
            $missed = KbMissedQuery::firstOrCreate(
                [
                    'user_id' => $userId,
                    'business_profile_id' => $profileId,
                    'question' => $question,
                ],
                ['status' => 'pending']
            );
            
            $missed->incrementCount();
            
            Log::info('📊 Missed query tracked', [
                'question' => $question,
                'user_id' => $userId,
                'profile_id' => $profileId,
                'count' => $missed->count,
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Failed to track missed query', ['err' => $e->getMessage()]);
        }
    }

    /**
     * Smart Fallback dengan suggestions
     * Jika tidak ketemu di KB, suggest artikel yang mirip
     */
    protected function getSmartFallback(string $question, ?BusinessProfile $profile, ?int $userId): ?array
    {
        if (!$profile || !$userId) return null;
        
        try {
            // Cari artikel yang mirip (tanpa threshold tinggi)
            $articles = KbArticle::where('is_active', 1)
                ->where('user_id', $userId)
                ->where(function ($q) use ($profile) {
                    $q->whereNull('business_profile_id')
                      ->orWhere('business_profile_id', $profile->id);
                })
                ->limit(5)
                ->get();
            
            if ($articles->isEmpty()) return null;
            
            // Hitung similarity sederhana berdasarkan keyword
            $questionLower = Str::lower($question);
            $questionWords = collect(preg_split('/\s+/u', $questionLower))
                ->filter(fn($w) => mb_strlen($w) >= 3)
                ->values();
            
            $suggestions = $articles->map(function ($article) use ($questionWords, $questionLower) {
                $titleLower = Str::lower($article->title);
                $content = Str::lower($article->title . ' ' . $article->content . ' ' . $article->tags);
                $matchCount = 0;
                $titleMatch = false;
                
                foreach ($questionWords as $word) {
                    if (Str::contains($content, $word)) {
                        $matchCount++;
                        // Extra weight if word matches in title
                        if (Str::contains($titleLower, $word)) {
                            $matchCount += 2;
                            $titleMatch = true;
                        }
                    }
                }
                
                // Calculate score with bonus for title match
                $score = $questionWords->count() > 0 
                    ? $matchCount / ($questionWords->count() * 3) 
                    : 0;
                
                return [
                    'article' => $article,
                    'score' => $score,
                    'titleMatch' => $titleMatch,
                ];
            })
            ->filter(fn($item) => $item['score'] > 0.3) // Increased to 30% minimum match
            ->sortByDesc('score')
            ->take(3);
            
            // Only use smart fallback if we have high confidence suggestions
            $highConfidenceSuggestions = $suggestions->filter(fn($item) => $item['score'] > 0.5 || $item['titleMatch']);
            
            if ($highConfidenceSuggestions->isEmpty()) return null;
            
            // Build fallback response dengan suggestions
            $businessName = $profile->business_name ?? 'Kami';
            $fallbackMsg = "Mohon maaf kak, saya tidak menemukan info spesifik tentang pertanyaan tersebut.\n\n";
            
            // Only suggest if we have good matches
            if ($highConfidenceSuggestions->count() > 0) {
                $fallbackMsg .= "Mungkin yang Anda cari:\n";
                
                foreach ($highConfidenceSuggestions as $i => $item) {
                    $num = $i + 1;
                    $title = $item['article']->title;
                    $fallbackMsg .= "{$num}. {$title}\n";
                }
                
                $fallbackMsg .= "\n";
            }
            
            $fallbackMsg .= "Atau bisa langsung hubungi Admin {$businessName} ya. 😊";
            
            Log::info('💡 Smart fallback triggered', [
                'question' => $question,
                'suggestions' => $highConfidenceSuggestions->pluck('article.title')->toArray(),
                'scores' => $highConfidenceSuggestions->pluck('score')->toArray(),
            ]);
            
            return [
                'answer' => $fallbackMsg,
                'confidence' => 0.6,
                'source' => 'smart_fallback',
                'suggestions' => $highConfidenceSuggestions->pluck('article.id')->toArray(),
            ];
            
        } catch (\Throwable $e) {
            Log::error('❌ Smart fallback error', ['err' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create training suggestion dari chat CS manusia
     * Dipanggil ketika CS menjawab pertanyaan yang AI tidak bisa jawab
     */
    public function createTrainingSuggestion(int $userId, int $conversationId, string $question, string $csAnswer, ?int $profileId = null): ?AiTrainingSuggestion
    {
        try {
            $suggestion = AiTrainingSuggestion::create([
                'user_id' => $userId,
                'business_profile_id' => $profileId,
                'conversation_id' => $conversationId,
                'question' => $question,
                'cs_answer' => $csAnswer,
                'status' => 'pending',
            ]);
            
            Log::info('🎓 Training suggestion created', [
                'suggestion_id' => $suggestion->id,
                'user_id' => $userId,
                'question' => $question,
            ]);
            
            return $suggestion;
            
        } catch (\Throwable $e) {
            Log::error('❌ Failed to create training suggestion', ['err' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get top missed queries untuk dashboard analytics
     */
    public function getTopMissedQueries(int $userId, ?int $profileId = null, int $limit = 10): \Illuminate\Support\Collection
    {
        $query = KbMissedQuery::where('user_id', $userId)
            ->where('status', 'pending')
            ->orderByDesc('count');
            
        if ($profileId) {
            $query->where('business_profile_id', $profileId);
        }
        
        return $query->limit($limit)->get();
    }
}
