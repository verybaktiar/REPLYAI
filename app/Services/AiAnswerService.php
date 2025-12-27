<?php

namespace App\Services;

use App\Models\KbArticle;
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

        $context = $this->buildContext($articles);

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

    protected function searchRelevantArticles(string $question, int $limit = 4)
    {
        $q = Str::lower($question);

        $keywords = collect(preg_split('/\s+/u', $q))
            ->map(fn($k) => trim($k))
            ->filter(fn($k) => mb_strlen($k) >= 3)
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

    protected function buildContext($articles): string
    {
        $chunks = [];
        foreach ($articles as $a) {
            $title = $a->title ?: '(Tanpa judul)';
            $content = Str::limit(trim((string)$a->content), 1800);
            $src = $a->source_url ?: '-';

            $chunks[] = "### {$title}\nSumber: {$src}\nIsi:\n{$content}";
        }

        return implode("\n\n", $chunks);
    }

    protected function callPerplexity(string $question, string $context): ?array
    {
        $apiKey = config('services.perplexity.key');
        $baseUrl = rtrim((string) config('services.perplexity.url', 'https://api.perplexity.ai'), '/');
        $model = (string) config('services.perplexity.model', 'sonar-pro');
        $timeout = (int) config('services.perplexity.timeout', 45);

        $now = now()->format('l, d F Y H:i');

        $system = <<<SYS
Kamu adalah asisten customer service rumah sakit.
Jawab HANYA berdasarkan KONTEKS yang diberikan.
Jika jawaban tidak ada di konteks, jawab: "Tidak ditemukan di data resmi."
Jawab singkat, jelas, ramah, bahasa Indonesia.
Hari ini: {$now}
Output HARUS JSON valid saja:

{
  "answer": "...",
  "confidence": 0.0-1.0
}
SYS;

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
