<?php

namespace App\Services;

use App\Models\KbArticle;
use App\Models\BusinessProfile;
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
     * WhatsApp-specific AI answer dengan kemampuan percakapan natural
     * Handle sapaan, pertanyaan umum, dan keluhan pasien
     * 
     * @param string $question The user's question
     * @param array|null $conversationHistory Previous messages for context
     * @param BusinessProfile|null $profile Optional profile to use (overrides default)
     */
    public function answerWhatsApp(string $question, ?array $conversationHistory = [], ?BusinessProfile $profile = null): ?array
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
            $greetingResponses = [
                "Halo kak! 👋 Selamat datang. Ada yang bisa saya bantu? 😊",
                "Hai kak! 😊 Saya asisten virtual siap membantu 24 jam!",
                "Halo! Selamat datang 👋 Silakan sampaikan pertanyaan Anda, saya akan bantu jawab ya 😊",
            ];
            
            return [
                'answer' => $greetingResponses[array_rand($greetingResponses)],
                'confidence' => 0.95,
                'source' => 'greeting',
            ];
        }

        // 4) Coba cari di Knowledge Base dulu
        $articles = $this->searchRelevantArticles($question, 4);
        
        // 5) Jika KB tidak kosong, gunakan context dari KB
        $context = '';
        if (!$articles->isEmpty()) {
            $context = $this->buildContext($articles, $question);
        }

        // 6) Panggil AI dengan prompt khusus WhatsApp
        $apiKey = config('services.perplexity.key');
        if (!$apiKey) {
            Log::error('❌ Perplexity API key missing');
            
            // Fallback response jika API key tidak ada
            $fallbackProfile = $profile ?? BusinessProfile::getActive();
            $fallbackMsg = $fallbackProfile ? $fallbackProfile->kb_fallback_message : "Mohon maaf, layanan sedang gangguan.";

            return [
                'answer' => $fallbackMsg,
                'confidence' => 0.5,
                'source' => 'fallback',
            ];
        }

        $res = $this->callWhatsAppAI($question, $context, $conversationHistory, $profile);
        
        if (!$res || empty($res['answer'])) {
            // Jika AI tidak bisa jawab, berikan respons yang lebih natural
            return [
                'answer' => "Hmm, saya kurang paham dengan pertanyaan kakak 🤔 Bisa dijelaskan lebih detail?",
                'confidence' => 0.4,
                'source' => 'clarification',
            ];
        }

        return [
            'answer' => (string)($res['answer'] ?? ''),
            'confidence' => (float)($res['confidence'] ?? 0.8),
            'source' => !$articles->isEmpty() ? 'kb' : 'ai',
        ];
    }

    /**
     * Call Perplexity dengan prompt khusus WhatsApp yang lebih conversational
     * 
     * @param BusinessProfile|null $profile Optional profile to use (overrides default)
     */
    protected function callWhatsAppAI(string $question, string $context, array $history = [], ?BusinessProfile $profile = null): ?array
    {
        $apiKey = config('services.perplexity.key');
        $baseUrl = rtrim((string) config('services.perplexity.url', 'https://api.perplexity.ai'), '/');
        $model = (string) config('services.perplexity.model', 'sonar-pro');
        $timeout = (int) config('services.perplexity.timeout', 60);

        $now = now()->format('l, d F Y H:i');
        $tomorrow = now()->addDay()->format('l');

        $contextSection = $context ? "\n\nKONTEKS KNOWLEDGE BASE:\n{$context}" : "\n\n(Tidak ada data spesifik di Knowledge Base untuk pertanyaan ini)";

        // USE PROVIDED PROFILE OR FALLBACK TO ACTIVE
        $profile = $profile ?? BusinessProfile::getActive();
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
        $finalUserMsg = "Pertanyaan user:{$contextSection}\n\nPERTANYAAN:\n{$question}";

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
            "temperature" => 0.3, // Sedikit lebih kreatif untuk natural conversation
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

    protected function searchRelevantArticles(string $question, int $limit = 4)
    {
        $q = Str::lower($question);

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
            ->filter(fn($k) => !in_array($k, $stopwords, true)) // filter stopwords
            ->unique()
            ->values();

        $articles = KbArticle::where('is_active', 1)->get();

        $scored = $articles->map(function ($a) use ($keywords) {
                $hay = Str::lower(($a->title ?? '') . " " . ($a->content ?? '') . " " . ($a->tags ?? ''));

                $score = 0;
                foreach ($keywords as $kw) {
                    if (Str::contains($hay, $kw)) $score++;
                }

                return [$a, $score];
            })
            ->filter(fn($pair) => $pair[1] > 0)
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
            if (mb_strlen($fullContent) > 2000 && !empty($importantKeywords)) {
                $relevantParts = $this->extractRelevantParts($fullContent, $importantKeywords, 1500);
                $content = $relevantParts ?: Str::limit($fullContent, 1200);
            } else {
                $content = Str::limit($fullContent, 1200);
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
}
