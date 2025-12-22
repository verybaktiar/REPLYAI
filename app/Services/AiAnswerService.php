<?php

namespace App\Services;

use App\Models\KbArticle;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class AiAnswerService
{
    /**
     * Minimum confidence biar AI boleh kirim ke user.
     */
    public float $minConfidence = 0.55;

    /**
     * Cari jawaban AI dari KB.
     * Return:
     * [
     *   'answer' => string,
     *   'confidence' => float (0-1),
     *   'sources' => array of ['id','title','source_url']
     * ]
     * atau null kalau tidak yakin / tidak ada KB / tidak ada api key.
     */
    public function answerFromKb(string $question): ?array
    {
        $question = trim($question);
        if ($question === '') return null;

        // ✅ ambil KB aktif yang relevan (MVP scoring sederhana)
        $articles = $this->searchRelevantArticles($question, limit: 4);
        if ($articles->isEmpty()) return null;

        $context = $this->buildContext($articles);

        // ✅ kalau belum set API key, stop di sini (fallback rule manual aja)
        $apiKey = config('services.perplexity.key');
        if (!$apiKey) return null;

        $res = $this->callPerplexity($question, $context, $apiKey);
        if (!$res) return null;

        $conf = (float)($res['confidence'] ?? 0);

        // ✅ sesuai komentar kamu: kalau gak yakin -> null
        if ($conf < $this->minConfidence) {
            return null;
        }

        return [
            'answer' => $res['answer'],
            'confidence' => $conf,
            'sources' => $articles->map(fn($a)=>[
                'id'=>$a->id,
                'title'=>$a->title,
                'source_url'=>$a->source_url,
            ])->values()->all(),
        ];
    }

    // ================== Internal helpers ==================

    protected function searchRelevantArticles(string $question, int $limit = 4)
    {
        $q = Str::lower($question);

        // scoring sederhana: banyak keyword question muncul di content/title
        $keywords = collect(preg_split('/\s+/u', $q))
            ->map(fn($k)=>trim($k))
            ->filter(fn($k)=>mb_strlen($k) >= 3)
            ->unique()
            ->values();

        $articles = KbArticle::where('is_active', true)->get();

        $scored = $articles->map(function($a) use ($keywords){
            $hay = Str::lower(($a->title ?? '')." ".$a->content." ".$a->tags);

            $score = 0;
            foreach ($keywords as $kw) {
                if (Str::contains($hay, $kw)) $score++;
            }

            return [$a, $score];
        })
        ->filter(fn($pair)=>$pair[1] > 0)
        ->sortByDesc(fn($pair)=>$pair[1])
        ->take($limit)
        ->map(fn($pair)=>$pair[0]);

        return $scored->values();
    }

    protected function buildContext($articles): string
    {
        $chunks = [];
        foreach ($articles as $a) {
            $title = $a->title ?: '(Tanpa judul)';
            $content = Str::limit(trim($a->content), 1500);
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
Jika jawaban tidak ada di konteks, bilang "Tidak ditemukan di data resmi."
Jawab singkat, jelas, ramah, bahasa Indonesia.
Kembalikan output JSON valid dengan format:

{
  "answer": "...",
  "confidence": 0.0-1.0
}

confidence tinggi jika jawaban jelas tertulis di konteks.
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
            return null;
        }
    }

    protected function safeJsonDecode(string $text): ?array
    {
        $text = trim($text);

        // cari blok json pertama kalau model nambahin teks lain
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
