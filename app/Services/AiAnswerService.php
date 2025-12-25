<?php

namespace App\Services;

use App\Models\KbArticle;
use App\Models\AutoReplyRule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnswerService
{
    public float $minConfidence = 0.55;

    public function answerFromKb(string $question): ?array
    {
        $question = trim($question);
        if ($question === '') return null;

        // ✅ hanya ambil KB yang AKTIF
        $articles = $this->searchRelevantArticles($question, 4);
        if ($articles->isEmpty()) {
            Log::info('📚 KB candidates empty', ['question' => $question]);
            return null;
        }

        $apiKey = config('services.perplexity.key');
        if (!$apiKey) return null;

        $context = $this->buildContext($articles);

        $res = $this->callPerplexity($question, $context, $apiKey);
        if (!$res) return null;

        $conf = (float)($res['confidence'] ?? 0);
        if ($conf < $this->minConfidence) return null;

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

        // ✅ penting: is_active = 1
        $articles = KbArticle::where('is_active', 1)->get();

        $scored = $articles->map(function($a) use ($keywords){
            $hay = Str::lower(($a->title ?? '')." ".$a->content." ".$a->tags);

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
            $content = Str::limit(trim($a->content), 1800);
            $src = $a->source_url ?: '-';

            $chunks[] = "### {$title}\nSumber: {$src}\nIsi:\n{$content}";
        }

        return implode("\n\n", $chunks);
    }

    protected function callPerplexity(string $question, string $context, string $apiKey): ?array
    {
        try {
            $system = <<<SYS
Kamu adalah asisten customer service rumah sakit.
Jawab hanya berdasarkan KONTEKS yang diberikan.
Jika jawaban tidak ada di konteks, jawab: "Tidak ditemukan di data resmi."
Jawab singkat, jelas, ramah, bahasa Indonesia.
Output HARUS JSON valid:

{
  "answer": "...",
  "confidence": 0.0-1.0
}
SYS;

            $payload = [
                "model" => config('services.perplexity.model', 'sonar-pro'),
                "temperature" => 0.2,
                "messages" => [
                    ["role"=>"system","content"=>$system],
                    ["role"=>"user","content"=>"KONTEKS:\n".$context."\n\nPERTANYAAN:\n".$question]
                ]
            ];

            $http = Http::timeout(30)
                ->withToken($apiKey)
                ->acceptJson()
                ->post(config('services.perplexity.url', 'https://api.perplexity.ai').'/chat/completions', $payload);

            if (!$http->ok()) {
                Log::error('❌ Perplexity HTTP not ok', [
                    'status' => $http->status(),
                    'body' => $http->body(),
                ]);
                return null;
            }


            $text = $http->json('choices.0.message.content');
            if (!$text) return null;

            $json = $this->safeJsonDecode($text);
            if (!$json) return null;

            $answer = trim((string)($json['answer'] ?? ''));
            $confidence = (float)($json['confidence'] ?? 0);

            if ($answer === '') return null;

            return [
                'answer' => $answer,
                'confidence' => max(0, min(1, $confidence)),
            ];
        } catch (\Throwable $e) {
            Log::error('❌ AI call error', ['err' => $e->getMessage()]);
            return null;
        }
    }

    protected function safeJsonDecode(string $text): ?array
    {
        $text = trim($text);

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
     * AI pilih 1 rule terbaik dari banyak rule yang match (opsi C)
     */
    public function pickBestRuleId(string $userText, $matchedRules): ?int
    {
        $userText = trim($userText);
        if ($userText === '') return null;

        $apiKey = config('services.perplexity.key');
        if (!$apiKey) return null;

        $candidates = collect($matchedRules)->map(function ($r) {
            return [
                'id' => (int) $r->id,
                'priority' => (int) ($r->priority ?? 0),
                'match_type' => (string) ($r->match_type ?? 'contains'),
                'trigger_keyword' => (string) ($r->trigger_keyword ?? ''),
                'response_preview' => Str::limit((string) ($r->response_text ?? ''), 120),
            ];
        })->values()->all();

        try {
            $system = <<<SYS
Kamu adalah classifier intent chatbot.
Pilih 1 RULE paling sesuai dengan pesan user.

Balas HARUS JSON:
{
  "rule_id": number|null,
  "confidence": 0.0-1.0,
  "reason": "singkat"
}

Jika ragu, gunakan priority tertinggi sebagai tie-breaker.
Jika tidak ada yang cocok, rule_id=null.
SYS;

            $payload = [
                "model" => config('services.perplexity.model', 'sonar-pro'),
                "temperature" => 0.0,
                "messages" => [
                    ["role"=>"system","content"=>$system],
                    ["role"=>"user","content"=>json_encode([
                        "user_message" => $userText,
                        "candidates" => $candidates,
                    ], JSON_UNESCAPED_UNICODE)]
                ],
            ];

            $http = Http::timeout(30)
                ->withToken($apiKey)
                ->acceptJson()
                ->post(config('services.perplexity.url', 'https://api.perplexity.ai').'/chat/completions', $payload);

                if (!$http->ok()) {
                    Log::error('❌ Perplexity HTTP not ok (pickBestRuleId)', [
                        'status' => $http->status(),
                        'body' => $http->body(),
                    ]);
                    return null;
                }
                

            $text = $http->json('choices.0.message.content');
            if (!$text) return null;

            $json = $this->safeJsonDecode($text);
            if (!$json) return null;

            $ruleId = $json['rule_id'] ?? null;
            $conf = (float) ($json['confidence'] ?? 0);

            if ($ruleId === null) return null;
            if ($conf < 0.35) return null;

            return (int) $ruleId;
        } catch (\Throwable $e) {
            Log::error('❌ pickBestRuleId error', ['err' => $e->getMessage()]);
            return null;
        }
    }
}
