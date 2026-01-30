<?php

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Services\AiAnswerService;
use App\Services\ActivityLogService;

class KbArticleController extends Controller
{
    public function index()
    {
        $articles = KbArticle::with('businessProfile')->orderByDesc('updated_at')->get();
        $businessProfiles = BusinessProfile::orderBy('business_name')->get();
        return view('pages.kb.index', compact('articles', 'businessProfiles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => ['nullable','string','max:255'],
            'content' => ['required','string'],
            'tags'    => ['nullable','string','max:255'],
            'business_profile_id' => ['nullable','exists:business_profiles,id'],
        ]);

        $article = KbArticle::create([
            'title'     => $validated['title'] ?? null,
            'content'   => $validated['content'],
            'tags'      => $validated['tags'] ?? null,
            'is_active' => true,
            'business_profile_id' => $validated['business_profile_id'] ?? null,
        ]);

        ActivityLogService::logCreated($article, "Membuat artikel KB: " . ($article->title ?? 'Tanpa Judul'));

        return back()->with('ok', 'KB article dibuat');
    }

    /**
     * Import dari URL (manual)
     * POST /kb/import-url (AJAX)
     */
    public function importUrl(Request $request)
    {
        $validated = $request->validate([
            'url'   => ['required','url'],
            'title' => ['nullable','string','max:255'],
            'tags'  => ['nullable','string','max:255'],
        ]);

        $url = $validated['url'];

        // Coba fetch dengan SSL verification, jika gagal coba tanpa SSL verification
        try {
            $res = Http::timeout(15)->get($url);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // SSL error - coba tanpa verifikasi SSL (untuk self-signed cert atau self-referential URL)
            try {
                $res = Http::timeout(15)->withoutVerifying()->get($url);
            } catch (\Illuminate\Http\Client\ConnectionException $e2) {
                // Jika masih gagal, coba dengan HTTP (bukan HTTPS)
                $httpUrl = str_replace('https://', 'http://', $url);
                try {
                    $res = Http::timeout(15)->get($httpUrl);
                } catch (\Exception $e3) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Gagal fetch URL: ' . $e->getMessage(),
                    ], 422);
                }
            }
        }
        
        if (!$res->ok()) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal fetch URL (HTTP ' . $res->status() . ')',
            ], 422);
        }

        $html = $res->body();

        /**
         * ✅ Coba deteksi endpoint DataTables AJAX
         * Kalau ketemu -> fetch JSON -> format jadi jadwal rapi
         */
        $ajaxUrl = $this->tryFindDatatableAjaxUrl($html, $url);

        if ($ajaxUrl) {
            try {
                $ajaxRes = Http::timeout(15)->get($ajaxUrl);

                if ($ajaxRes->ok()) {
                    $json = $ajaxRes->json();
                    $tableText = $this->formatDoctorScheduleFromJson($json);

                    if ($tableText && Str::length($tableText) > 50) {
                        $text = $tableText;
                    } else {
                        $text = $this->extractTextFromHtml($html);
                    }
                } else {
                    $text = $this->extractTextFromHtml($html);
                }
            } catch (\Throwable $e) {
                $text = $this->extractTextFromHtml($html);
            }
        } else {
            // fallback normal
            $text = $this->extractTextFromHtml($html);
        }

        if (Str::length($text) < 30) {
            return response()->json([
                'ok' => false,
                'message' => 'Konten terlalu sedikit / tidak terbaca',
            ], 422);
        }

        $article = KbArticle::create([
            'title'      => $validated['title'] ?? $this->guessTitleFromHtml($html) ?? $url,
            'content'    => $text,
            'source_url' => $url,
            'tags'       => $validated['tags'] ?? null,
            'is_active'  => true,
        ]);

        ActivityLogService::logCreated($article, "Import KB dari URL: {$url}");

        return response()->json([
            'ok' => true,
            'article' => $article,
        ]);
    }

    /**
     * Import File (PDF/TXT)
     * POST /kb/import-file
     */
    public function importFile(Request $request, \App\Services\KbParserService $parser)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,txt', 'max:5120'], // Max 5MB
            'tags' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        
        try {
            $text = $parser->parseFile($file->getPathname(), $file->getMimeType());
            
            if (Str::length($text) < 30) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Konten file terlalu sedikit atau kosong.',
                ], 422);
            }

            $article = KbArticle::create([
                'title'      => $file->getClientOriginalName(),
                'content'    => $text,
                'source_url' => 'File: ' . $file->getClientOriginalName(),
                'tags'       => $request->tags ?? 'file-upload',
                'is_active'  => true,
            ]);

            ActivityLogService::logCreated($article, "Import KB dari File: " . $file->getClientOriginalName());

            return response()->json([
                'ok' => true,
                'article' => $article,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(KbArticle $kb)
    {
        $kb->is_active = !$kb->is_active;
        $kb->save();

        ActivityLogService::logUpdated($kb, ($kb->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . " artikel KB: " . ($kb->title ?? 'Tanpa Judul'));

        return response()->json(['ok' => true, 'is_active' => $kb->is_active]);
    }

    public function destroy(KbArticle $kb)
    {
        ActivityLogService::logDeleted($kb, "Menghapus artikel KB: " . ($kb->title ?? 'Tanpa Judul'));
        $kb->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Update KB article's profile assignment (AJAX)
     */
    public function updateProfile(Request $request, KbArticle $kb)
    {
        $request->validate([
            'business_profile_id' => ['nullable', 'exists:business_profiles,id'],
        ]);

        $kb->update([
            'business_profile_id' => $request->business_profile_id,
        ]);

        return response()->json([
            'ok' => true,
            'article' => $kb->fresh()->load('businessProfile'),
        ]);
    }

    // ================= helpers =================

    private function extractTextFromHtml(string $html): string
    {
        // ====== 0) Coba parse tabel jadwal dokter dulu (kalau ada di HTML statik) ======
        $tableText = $this->tryExtractDoctorScheduleTable($html);
        if ($tableText && Str::length($tableText) > 50) {
            return $tableText;
        }

        // ====== 1) extractor normal ======
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', ' ', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', ' ', $html);

        $html = preg_replace('/<nav\b[^>]*>(.*?)<\/nav>/is', ' ', $html);
        $html = preg_replace('/<footer\b[^>]*>(.*?)<\/footer>/is', ' ', $html);
        $html = preg_replace('/<header\b[^>]*>(.*?)<\/header>/is', ' ', $html);
        $html = preg_replace('/<aside\b[^>]*>(.*?)<\/aside>/is', ' ', $html);

        $main = null;

        if (preg_match('/<main\b[^>]*>(.*?)<\/main>/is', $html, $m)) {
            $main = $m[1];
        } elseif (preg_match('/<article\b[^>]*>(.*?)<\/article>/is', $html, $m)) {
            $main = $m[1];
        } elseif (preg_match('/<section\b[^>]*class="[^"]*(content|container|page-content|entry-content)[^"]*"[^>]*>(.*?)<\/section>/is', $html, $m)) {
            $main = $m[2];
        }

        $targetHtml = $main ?? $html;

        $text = strip_tags($targetHtml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // blacklist noise
        $blacklist = [
            'beranda','profil','akreditasi','prestasi','mitra',
            'youtube','linkedin','twitter','facebook','tiktok','instagram',
            'contact us','promo','minimarket','parking','linen','laundry',
        ];
        foreach ($blacklist as $bad) {
            $text = str_ireplace($bad, ' ', $text);
        }
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // delimiter layanan
        $delims = [
            'Rawat Jalan','Rawat Inap','Layanan Unggulan','Fasilitas Umum','Fasilitas Penunjang',
            'Poliklinik Eksekutif','Poliklinik Reguler','Kamar Kelas I','Kamar Kelas II','Kamar Kelas III',
            'Kamar VIP','Suite Room','IGD 24 Jam','Instalasi Bedah Sentral','Instalasi Farmasi',
            'Instalasi Laboratorium','Instalasi Radiologi','Rehabilitasi Medik',
        ];
        foreach ($delims as $d) {
            $text = preg_replace('/\b' . preg_quote($d, '/') . '\b/u', "\n" . $d, $text);
        }

        // split + dedup
        $parts = preg_split('/[\.\n\r•\-]+/u', $text);
        $parts = array_map(fn($p) => trim($p), $parts);
        $parts = array_filter($parts);

        $unique = [];
        $seen = [];
        foreach ($parts as $p) {
            $key = mb_strtolower($p);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $unique[] = $p;
        }

        return trim("• " . implode("\n• ", $unique));
    }

    private function guessTitleFromHtml(string $html): ?string
    {
        if (preg_match('/<title>(.*?)<\/title>/is', $html, $m)) {
            return trim(html_entity_decode($m[1]));
        }
        return null;
    }

    /**
     * Coba parse tabel HTML statik (kalau ada).
     * Kalau jadwal dokter di-load JS -> biasanya kosong, fallback akan jalan.
     */
    private function tryExtractDoctorScheduleTable(string $html): ?string
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();

        if (!$dom->loadHTML($html)) {
            return null;
        }

        $xpath = new \DOMXPath($dom);

        $tables = $xpath->query('//table');
        $lines = [];

        if ($tables && $tables->length > 0) {
            foreach ($tables as $table) {
                $rows = $xpath->query('.//tr', $table);
                if (!$rows || $rows->length < 2) continue;

                for ($i = 0; $i < $rows->length; $i++) {
                    $cells = $xpath->query('.//th|.//td', $rows->item($i));
                    if (!$cells || $cells->length < 2) continue;

                    $cols = [];
                    foreach ($cells as $c) {
                        $val = trim(preg_replace('/\s+/', ' ', $c->textContent));
                        if ($val !== '') $cols[] = $val;
                    }

                    if (count($cols) < 2) continue;

                    $lines[] = "• " . implode(" | ", $cols);
                }

                if (count($lines) > 20) break;
            }
        }

        if (count($lines) >= 5) {
            return implode("\n", $lines);
        }

        // fallback regex kalau table kosong (JS ajax)
        $plain = strip_tags($html);
        $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/', ' ', $plain);

        $chunks = preg_split('/\b(dr\.|drg\.|prof\.)\s*/i', $plain);
        if (!$chunks || count($chunks) < 3) return null;

        $fallback = [];
        foreach ($chunks as $c) {
            $c = trim($c);
            if ($c === '') continue;

            $short = Str::limit($c, 180);

            if (!preg_match('/[A-Za-z].*\bSp\.|\bdr\b/i', $short)) continue;

            $fallback[] = "• dr. " . $short;
            if (count($fallback) >= 30) break;
        }

        if (count($fallback) >= 5) {
            return implode("\n", $fallback);
        }

        return null;
    }

    /**
     * ✅ Deteksi URL AJAX DataTables dari HTML
     */
    private function tryFindDatatableAjaxUrl(string $html, string $baseUrl): ?string
    {
        // 1) cek langsung di HTML (punyamu yg lama)
        $direct = $this->tryFindAjaxUrlInText($html, $baseUrl);
        if ($direct) return $direct;
    
        // 2) kalau gak ada, scan file JS eksternal
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        if (!$dom->loadHTML($html)) return null;
    
        $xpath = new \DOMXPath($dom);
        $scripts = $xpath->query('//script[@src]');
    
        if (!$scripts || $scripts->length === 0) return null;
    
        /** @var \DOMElement $sc */
        foreach ($scripts as $sc) {
            if (!$sc instanceof \DOMElement) {
                continue;
            }
    
            $src = $sc->getAttribute('src');
            if (!$src) continue;
    
            $srcUrl = $this->normalizeUrl($src, $baseUrl);
    
            try {
                $jsRes = Http::timeout(10)->get($srcUrl);
                if (!$jsRes->ok()) continue;
    
                $js = $jsRes->body();
    
                $found = $this->tryFindAjaxUrlInText($js, $baseUrl);
                if ($found) return $found;
    
            } catch (\Throwable $e) {
                continue;
            }
        }
    
        return null;
    }
    
    /**
     * Cari pola ajax url di string apapun (HTML / JS)
     */
    private function tryFindAjaxUrlInText(string $text, string $baseUrl): ?string
    {
        // pola umum DataTables
        if (preg_match('/ajax\s*:\s*{[^}]*url\s*:\s*["\']([^"\']+)["\']/is', $text, $m)) {
            return $this->normalizeUrl($m[1], $baseUrl);
        }
    
        if (preg_match('/ajax\s*:\s*["\']([^"\']+)["\']/is', $text, $m)) {
            return $this->normalizeUrl($m[1], $baseUrl);
        }
    
        // kadang pakai data-url attribute
        if (preg_match('/data-url\s*=\s*["\']([^"\']+)["\']/is', $text, $m)) {
            return $this->normalizeUrl($m[1], $baseUrl);
        }
    
        // kadang pakai fetch("...") / $.get("...")
        if (preg_match('/fetch\(\s*["\']([^"\']+)["\']/is', $text, $m)) {
            return $this->normalizeUrl($m[1], $baseUrl);
        }
    
        if (preg_match('/\$\.(get|post)\(\s*["\']([^"\']+)["\']/is', $text, $m)) {
            return $this->normalizeUrl($m[2], $baseUrl);
        }
    
        return null;
    }
    

    private function normalizeUrl(string $url, string $baseUrl): string
    {
        if (Str::startsWith($url, 'http')) return $url;

        $base = rtrim($baseUrl, '/');
        return $base . $url;
    }

    /**
     * ✅ Format JSON DataTables jadwal dokter jadi bullet rapi
     */
    private function formatDoctorScheduleFromJson($json): ?string
    {
        if (!$json) return null;
    
        $rows = $json['data'] ?? null;
        if (!$rows || !is_array($rows)) return null;
    
        $hariMap = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
    
        $out = [];
        foreach ($rows as $r) {
    
            // ✅ DataTables kadang return array kolom, kadang string panjang
            if (is_string($r)) {
                $cols = array_map('trim', explode('|', $r));
            } elseif (is_array($r)) {
                $cols = array_values($r);
    
                // kalau cuma 1 kolom tapi ada "|" → pecah lagi
                if (count($cols) === 1 && is_string($cols[0]) && str_contains($cols[0], '|')) {
                    $cols = array_map('trim', explode('|', $cols[0]));
                }
            } else {
                continue;
            }
    
            // bersihin entity &amp; dll
            $cols = array_map(function($c){
                $c = strip_tags((string)$c);
                $c = html_entity_decode($c, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return trim(preg_replace('/\s+/', ' ', $c));
            }, $cols);
    
            $nama = $cols[0] ?? '';
            $poli = $cols[1] ?? '';
    
            if ($nama === '' && $poli === '') continue;
    
            // ===== ambil jam dari kolom hari saja =====
            $jadwalHari = [];
            for ($h = 0; $h < 7; $h++) {
                $idx = 2 + $h;
                $cell = $cols[$idx] ?? '-';
                if ($cell === '' || $cell === '-') continue;
    
                $cellLower = Str::lower($cell);
    
                // normalisasi libur
                if (str_contains($cellLower, 'libur')) {
                    $jadwalHari[] = $hariMap[$h] . ': Libur';
                    continue;
                }
    
                // ✅ extract cuma jam (support "08:00-12:00" atau "08.00 - 12.00")
                if (preg_match('/(\d{1,2}[:\.]\d{2}\s*-\s*\d{1,2}[:\.]\d{2})/u', $cell, $m)) {
                    $jam = str_replace('.', ':', $m[1]);
                    $jadwalHari[] = $hariMap[$h] . ': ' . $jam;
                    continue;
                }
    
                // kalau gak ketemu jam, skip biar gak spam teks
                continue;
            }
    
            $jadwalStr = $jadwalHari ? implode(', ', $jadwalHari) : '-';
    
            // rapihin poli biar gak kepanjangan
            $poli = Str::limit($poli, 80);
    
            $out[] = "• {$nama} — {$poli} ({$jadwalStr})";
    
            if (count($out) >= 400) break;
        }
    
        return empty($out) ? null : implode("\n", $out);
    }

        /**
     * Test AI Answer dari KB (AJAX)
     * POST /kb/test-ai
     */
    public function testAi(Request $request, AiAnswerService $ai)
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3'],
        ]);

        $q = $validated['question'];

        $res = $ai->answerFromKb($q);

        return response()->json([
            'ok' => true,
            'result' => $res, // bisa null kalau AI tidak yakin
        ]);
    }

    
}
