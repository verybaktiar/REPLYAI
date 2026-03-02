<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiSuggestionService
{
    /**
     * API Provider: 'openai' or 'claude'
     */
    protected string $provider;

    /**
     * API Key
     */
    protected ?string $apiKey;

    /**
     * Model name
     */
    protected string $model;

    /**
     * Cache duration in seconds (5 minutes)
     */
    protected int $cacheDuration = 300;

    /**
     * Maximum messages to include in context
     */
    protected int $maxContextMessages = 10;

    public function __construct()
    {
        $this->provider = config('services.ai_suggestions.provider', 'openai');
        $this->apiKey = $this->getApiKey();
        $this->model = config('services.ai_suggestions.model', $this->getDefaultModel());
    }

    /**
     * Get API key based on provider
     */
    protected function getApiKey(): ?string
    {
        return match ($this->provider) {
            'openai' => config('services.openai.key'),
            'claude' => config('services.claude.key'),
            'megallm' => config('services.megallm.key'),
            default => config('services.openai.key'),
        };
    }

    /**
     * Get default model based on provider
     */
    protected function getDefaultModel(): string
    {
        return match ($this->provider) {
            'openai' => 'gpt-4o-mini',
            'claude' => 'claude-3-haiku-20240307',
            'megallm' => 'mistral-large-3-675b-instruct-2512',
            default => 'gpt-4o-mini',
        };
    }

    /**
     * Generate reply suggestions based on conversation context
     *
     * @param mixed $conversation
     * @param mixed $lastMessage
     * @return array
     */
    public function generateReplySuggestions($conversation, $lastMessage): array
    {
        if (!$this->apiKey) {
            Log::warning('AI Suggestions: No API key configured');
            return $this->getFallbackSuggestions();
        }

        // Generate cache key
        $cacheKey = $this->getCacheKey('suggestions', $conversation->id, $lastMessage->id ?? 'latest');

        // Check cache
        if ($cached = Cache::get($cacheKey)) {
            Log::info('AI Suggestions: Returning cached suggestions', ['conversation_id' => $conversation->id]);
            return $cached;
        }

        try {
            $context = $this->buildConversationContext($conversation, $lastMessage);
            $suggestions = $this->callAiForSuggestions($context);

            // Cache the result
            Cache::put($cacheKey, $suggestions, $this->cacheDuration);

            return $suggestions;
        } catch (\Throwable $e) {
            Log::error('AI Suggestions: Failed to generate suggestions', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
            ]);

            return $this->getFallbackSuggestions();
        }
    }

    /**
     * Analyze sentiment of a message
     *
     * @param string $text
     * @return array ['sentiment' => 'positive|neutral|negative', 'score' => float, 'emotions' => array]
     */
    public function analyzeSentiment(string $text): array
    {
        if (empty($text)) {
            return ['sentiment' => 'neutral', 'score' => 0.5, 'emotions' => []];
        }

        // Use local analysis for quick results
        $localResult = $this->analyzeSentimentLocal($text);

        // If AI is not configured, return local analysis
        if (!$this->apiKey) {
            return $localResult;
        }

        // Generate cache key
        $cacheKey = $this->getCacheKey('sentiment', md5($text));

        // Check cache
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $result = $this->callAiForSentiment($text);
            Cache::put($cacheKey, $result, $this->cacheDuration);
            return $result;
        } catch (\Throwable $e) {
            Log::error('AI Sentiment Analysis: Failed', ['error' => $e->getMessage()]);
            return $localResult;
        }
    }

    /**
     * Local sentiment analysis as fallback
     */
    protected function analyzeSentimentLocal(string $text): array
    {
        $lower = Str::lower($text);

        // Positive keywords
        $positiveWords = ['bagus', 'keren', 'mantap', 'sip', 'oke', 'ok', 'baik', 'terima kasih',
            'thanks', 'makasih', 'helpful', 'membantu', 'senang', 'puas', 'wow', 'suka', 'love',
            'hebat', 'excellent', 'perfect', 'top', 'recommended', 'puas', 'senang', 'happy'];

        // Negative keywords
        $negativeWords = ['lambat', 'lama', 'kecewa', 'buruk', 'parah', 'jelek', 'gak bisa',
            'tidak bisa', 'gagal', 'error', 'rusak', 'mahal', 'ribet', 'susah', 'kesal', 'bete',
            'capek', 'males', 'bohong', 'tipu', 'penipuan', 'marah', 'kesel', 'frustasi'];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (Str::contains($lower, $word)) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (Str::contains($lower, $word)) {
                $negativeCount++;
            }
        }

        if ($positiveCount > $negativeCount) {
            $sentiment = 'positive';
            $score = min(0.9, 0.6 + ($positiveCount * 0.1));
        } elseif ($negativeCount > $positiveCount) {
            $sentiment = 'negative';
            $score = max(0.1, 0.4 - ($negativeCount * 0.1));
        } else {
            $sentiment = 'neutral';
            $score = 0.5;
        }

        return [
            'sentiment' => $sentiment,
            'score' => $score,
            'emotions' => $this->extractEmotions($lower),
            'source' => 'local',
        ];
    }

    /**
     * Extract emotions from text
     */
    protected function extractEmotions(string $text): array
    {
        $emotions = [];

        $emotionMap = [
            'happy' => ['senang', 'bahagia', 'gembira', 'hepi', 'happy', '😊', '😄'],
            'frustrated' => ['frustasi', 'kesal', 'marah', 'sebel', 'bete', 'kesel', '😤', '😠'],
            'confused' => ['bingung', 'pusing', 'ga ngerti', 'gak paham', 'confused', '😕', '🤔'],
            'satisfied' => ['puas', 'senang', 'mantap', 'recommended', '👍', '😍'],
            'urgent' => ['buruan', 'cepet', 'urgent', 'penting', 'segera', 'asap'],
        ];

        foreach ($emotionMap as $emotion => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($text, $keyword)) {
                    $emotions[] = $emotion;
                    break;
                }
            }
        }

        return array_unique($emotions);
    }

    /**
     * Summarize a conversation
     *
     * @param array $messages
     * @return array ['summary' => string, 'keyPoints' => array, 'actionItems' => array]
     */
    public function summarizeConversation(array $messages): array
    {
        if (empty($messages)) {
            return ['summary' => '', 'keyPoints' => [], 'actionItems' => []];
        }

        if (!$this->apiKey) {
            return $this->getBasicSummary($messages);
        }

        // Generate cache key based on message IDs
        $messageIds = collect($messages)->pluck('id')->implode(',');
        $cacheKey = $this->getCacheKey('summary', md5($messageIds));

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $result = $this->callAiForSummary($messages);
            Cache::put($cacheKey, $result, $this->cacheDuration);
            return $result;
        } catch (\Throwable $e) {
            Log::error('AI Summary: Failed to generate summary', ['error' => $e->getMessage()]);
            return $this->getBasicSummary($messages);
        }
    }

    /**
     * Get basic summary without AI
     */
    protected function getBasicSummary(array $messages): array
    {
        $messageCount = count($messages);
        $customerMessages = collect($messages)->where('sender_type', 'contact')->count();
        $agentMessages = collect($messages)->where('sender_type', 'agent')->count();

        return [
            'summary' => "Percakapan dengan {$messageCount} pesan ({$customerMessages} dari pelanggan, {$agentMessages} dari agen)",
            'keyPoints' => [],
            'actionItems' => [],
            'source' => 'basic',
        ];
    }

    /**
     * Detect customer intent
     *
     * @param string $text
     * @return array ['intent' => string, 'confidence' => float, 'entities' => array]
     */
    public function detectIntent(string $text): array
    {
        if (empty($text)) {
            return ['intent' => 'unknown', 'confidence' => 0, 'entities' => []];
        }

        // Local intent detection
        $localResult = $this->detectIntentLocal($text);

        if (!$this->apiKey) {
            return $localResult;
        }

        $cacheKey = $this->getCacheKey('intent', md5($text));

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $result = $this->callAiForIntent($text);
            Cache::put($cacheKey, $result, $this->cacheDuration);
            return $result;
        } catch (\Throwable $e) {
            Log::error('AI Intent Detection: Failed', ['error' => $e->getMessage()]);
            return $localResult;
        }
    }

    /**
     * Local intent detection
     */
    protected function detectIntentLocal(string $text): array
    {
        $lower = Str::lower($text);

        $intentPatterns = [
            'complaint' => ['komplain', 'keluhan', 'protes', 'kecewa', 'jelek', 'buruk', 'rusak', 'error', 'gagal'],
            'inquiry' => ['tanya', 'info', 'informasi', 'bagaimana', 'gimana', 'apa', 'berapa', 'kapan', 'dimana'],
            'purchase' => ['beli', 'order', 'pesan', 'checkout', 'bayar', 'pembelian', 'transaksi'],
            'support' => ['bantu', 'bantuan', 'help', 'support', 'cara', 'tutorial', 'panduan'],
            'feedback' => ['saran', 'feedback', 'review', 'testimoni', 'opini', 'kritik'],
            'greeting' => ['halo', 'hai', 'hi', 'hello', 'pagi', 'siang', 'sore', 'malam'],
            'urgent' => ['urgent', 'penting', 'segera', 'darurat', 'cepet', 'buruan', 'asap'],
            'cancellation' => ['batal', 'cancel', 'refund', 'kembali', 'uang kembali', 'retur'],
        ];

        $detectedIntent = 'general';
        $confidence = 0.5;

        foreach ($intentPatterns as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (Str::contains($lower, $pattern)) {
                    $detectedIntent = $intent;
                    $confidence = 0.7;
                    break 2;
                }
            }
        }

        return [
            'intent' => $detectedIntent,
            'confidence' => $confidence,
            'entities' => $this->extractEntities($text),
            'source' => 'local',
        ];
    }

    /**
     * Extract entities from text
     */
    protected function extractEntities(string $text): array
    {
        $entities = [];
        $lower = Str::lower($text);

        // Product mentions
        if (preg_match('/\b(produk|barang|item|menu)\s+(\w+)/i', $text, $matches)) {
            $entities['product'] = $matches[2];
        }

        // Price mentions
        if (preg_match('/(rp\.?|rupiah)?\s*([\d\.]+)\s*(ribu|juta|rb|jt)?/i', $text, $matches)) {
            $entities['price'] = $matches[0];
        }

        // Date/Time mentions
        if (preg_match('/(hari\s+\w+|senin|selasa|rabu|kamis|jumat|sabtu|minggu|\d{1,2}\s+\w+)/i', $text, $matches)) {
            $entities['date'] = $matches[0];
        }

        // Email
        if (preg_match('/[\w\.-]+@[\w\.-]+\.\w+/', $text, $matches)) {
            $entities['email'] = $matches[0];
        }

        // Phone
        if (preg_match('/(\+?62|0)[\d\s\-]{8,15}/', $text, $matches)) {
            $entities['phone'] = $matches[0];
        }

        return $entities;
    }

    /**
     * Build conversation context for AI
     */
    protected function buildConversationContext($conversation, $lastMessage): string
    {
        $messages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->take($this->maxContextMessages)
            ->get()
            ->reverse();

        $context = "Percakapan dengan {$conversation->display_name}:\n\n";

        foreach ($messages as $msg) {
            $sender = $msg->sender_type === 'agent' ? 'Agen' : 'Pelanggan';
            $content = $msg->content ?? '[Media]';
            $context .= "{$sender}: {$content}\n";
        }

        return $context;
    }

    /**
     * Call AI API for suggestions
     */
    protected function callAiForSuggestions(string $context): array
    {
        $prompt = $this->buildSuggestionPrompt($context);

        $response = $this->makeApiCall($prompt);

        if (!$response) {
            return $this->getFallbackSuggestions();
        }

        try {
            $content = $response['choices'][0]['message']['content'] ?? '';
            $data = json_decode($content, true);

            if (!is_array($data) || !isset($data['suggestions'])) {
                // Try to parse from text format
                $suggestions = $this->parseSuggestionsFromText($content);
            } else {
                $suggestions = $data['suggestions'];
            }

            // Ensure we have exactly 3 suggestions
            $suggestions = array_slice($suggestions, 0, 3);
            while (count($suggestions) < 3) {
                $suggestions[] = $this->getFallbackSuggestions()[count($suggestions)];
            }

            return $suggestions;
        } catch (\Throwable $e) {
            Log::error('AI Suggestions: Failed to parse response', ['error' => $e->getMessage()]);
            return $this->getFallbackSuggestions();
        }
    }

    /**
     * Build prompt for suggestion generation
     */
    protected function buildSuggestionPrompt(string $context): string
    {
        return <<<PROMPT
{$context}

Berdasarkan percakapan di atas, buatkan 3 saran balasan yang sesuai untuk agen customer service.
Balasan harus:
1. Ramah dan profesional
2. Relevan dengan konteks percakapan
3. Singkat tapi informatif (maksimal 2 kalimat)
4. Menggunakan bahasa Indonesia yang natural

Format output dalam JSON:
{
    "suggestions": [
        "Saran balasan 1",
        "Saran balasan 2",
        "Saran balasan 3"
    ]
}
PROMPT;
    }

    /**
     * Parse suggestions from text response
     */
    protected function parseSuggestionsFromText(string $text): array
    {
        $lines = explode("\n", $text);
        $suggestions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            // Look for numbered items or bullet points
            if (preg_match('/^(\d+[.\)]\s*|[\-\*]\s*)(.+)/', $line, $matches)) {
                $suggestions[] = trim($matches[2]);
            }
        }

        return $suggestions;
    }

    /**
     * Get fallback suggestions
     */
    protected function getFallbackSuggestions(): array
    {
        return [
            'Terima kasih telah menghubungi kami. Ada yang bisa saya bantu?',
            'Mohon maaf atas ketidaknyamanannya. Bisa dijelaskan lebih detail?',
            'Baik kak, saya bantu cek dulu ya. Mohon tunggu sebentar.',
        ];
    }

    /**
     * Call AI for sentiment analysis
     */
    protected function callAiForSentiment(string $text): array
    {
        $prompt = <<<PROMPT
Analisis sentimen dari teks berikut: "{$text}"

Klasifikasikan sebagai: positive, neutral, atau negative.
Berikan juga skor (0-1) dan emosi yang terdeteksi.

Format JSON:
{
    "sentiment": "positive|neutral|negative",
    "score": 0.8,
    "emotions": ["happy", "satisfied"]
}
PROMPT;

        $response = $this->makeApiCall($prompt, 0.3);

        if (!$response) {
            return $this->analyzeSentimentLocal($text);
        }

        try {
            $content = $response['choices'][0]['message']['content'] ?? '';
            $data = json_decode($content, true);

            return [
                'sentiment' => $data['sentiment'] ?? 'neutral',
                'score' => (float) ($data['score'] ?? 0.5),
                'emotions' => $data['emotions'] ?? [],
                'source' => 'ai',
            ];
        } catch (\Throwable $e) {
            return $this->analyzeSentimentLocal($text);
        }
    }

    /**
     * Call AI for summarization
     */
    protected function callAiForSummary(array $messages): array
    {
        $conversation = collect($messages)->map(function ($msg) {
            $sender = $msg['sender_type'] === 'agent' ? 'Agen' : 'Pelanggan';
            return "{$sender}: {$msg['content']}";
        })->implode("\n");

        $prompt = <<<PROMPT
Ringkas percakapan berikut:

{$conversation}

Berikan:
1. Ringkasan singkat (1-2 kalimat)
2. Poin-poin penting (maksimal 3)
3. Tindak lanjut yang diperlukan (jika ada)

Format JSON:
{
    "summary": "Ringkasan singkat",
    "keyPoints": ["Poin 1", "Poin 2"],
    "actionItems": ["Tindak lanjut 1"]
}
PROMPT;

        $response = $this->makeApiCall($prompt, 0.5);

        if (!$response) {
            return $this->getBasicSummary($messages);
        }

        try {
            $content = $response['choices'][0]['message']['content'] ?? '';
            $data = json_decode($content, true);

            return [
                'summary' => $data['summary'] ?? '',
                'keyPoints' => $data['keyPoints'] ?? [],
                'actionItems' => $data['actionItems'] ?? [],
                'source' => 'ai',
            ];
        } catch (\Throwable $e) {
            return $this->getBasicSummary($messages);
        }
    }

    /**
     * Call AI for intent detection
     */
    protected function callAiForIntent(string $text): array
    {
        $prompt = <<<PROMPT
Deteksi intent dari pesan berikut: "{$text}"

Pilih salah satu: complaint, inquiry, purchase, support, feedback, greeting, urgent, cancellation, atau general.

Format JSON:
{
    "intent": "inquiry",
    "confidence": 0.85,
    "entities": {"product": "nama produk", "price": "harga"}
}
PROMPT;

        $response = $this->makeApiCall($prompt, 0.3);

        if (!$response) {
            return $this->detectIntentLocal($text);
        }

        try {
            $content = $response['choices'][0]['message']['content'] ?? '';
            $data = json_decode($content, true);

            return [
                'intent' => $data['intent'] ?? 'general',
                'confidence' => (float) ($data['confidence'] ?? 0.5),
                'entities' => $data['entities'] ?? [],
                'source' => 'ai',
            ];
        } catch (\Throwable $e) {
            return $this->detectIntentLocal($text);
        }
    }

    /**
     * Make API call to AI provider
     */
    protected function makeApiCall(string $prompt, float $temperature = 0.7): ?array
    {
        return match ($this->provider) {
            'openai' => $this->callOpenAI($prompt, $temperature),
            'claude' => $this->callClaude($prompt, $temperature),
            'megallm' => $this->callMegaLLM($prompt, $temperature),
            default => $this->callOpenAI($prompt, $temperature),
        };
    }

    /**
     * Call OpenAI API
     */
    protected function callOpenAI(string $prompt, float $temperature): ?array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $temperature,
                'max_tokens' => 500,
            ]);

        if (!$response->successful()) {
            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Call Claude API
     */
    protected function callClaude(string $prompt, float $temperature): ?array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $temperature,
                'max_tokens' => 500,
            ]);

        if (!$response->successful()) {
            Log::error('Claude API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();

        // Convert Claude format to OpenAI format for consistency
        return [
            'choices' => [
                [
                    'message' => [
                        'content' => $data['content'][0]['text'] ?? '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Call MegaLLM API
     */
    protected function callMegaLLM(string $prompt, float $temperature): ?array
    {
        $baseUrl = rtrim(config('services.megallm.url', 'https://ai.megallm.io/v1'), '/');

        $response = Http::timeout(30)
            ->withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/chat/completions', [
                'model' => $this->model,
                'temperature' => $temperature,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (!$response->successful()) {
            Log::error('MegaLLM API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Generate cache key
     */
    protected function getCacheKey(string $type, ...$parts): string
    {
        $keyParts = array_merge(['ai_suggestions', $type], $parts);
        return implode(':', $keyParts);
    }

    /**
     * Clear cached suggestions for a conversation
     */
    public function clearCache(int $conversationId): void
    {
        // Clear all cache keys for this conversation
        $types = ['suggestions', 'sentiment', 'summary', 'intent'];
        foreach ($types as $type) {
            // We can't easily clear all variations, but this clears the main ones
            Cache::forget("ai_suggestions:{$type}:{$conversationId}");
        }
    }
}
