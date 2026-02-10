<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KbArticle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReimportKbUrl extends Command
{
    protected $signature = 'kb:reimport {id? : ID artikel KB yang akan di-reimport}';
    protected $description = 'Re-import konten KB dari URL dengan parser yang sudah diperbaiki';

    public function handle()
    {
        $id = $this->argument('id');
        
        if ($id) {
            $articles = KbArticle::where('id', $id)->get();
        } else {
            $articles = KbArticle::whereNotNull('source_url')->get();
        }
        
        if ($articles->isEmpty()) {
            $this->error('Tidak ada artikel KB dengan URL yang ditemukan.');
            return 1;
        }
        
        foreach ($articles as $article) {
            $this->info("Processing: {$article->title} (ID: {$article->id})");
            
            if (empty($article->source_url)) {
                $this->warn("  -> Tidak punya source_url, skip.");
                continue;
            }
            
            try {
                $res = Http::timeout(15)->get($article->source_url);
                if (!$res->ok()) {
                    $this->error("  -> Gagal fetch URL (HTTP {$res->status()})");
                    continue;
                }
                
                $html = $res->body();
                $text = $this->extractTextFromHtml($html);
                
                if (Str::length($text) < 30) {
                    $this->warn("  -> Konten terlalu sedikit");
                    continue;
                }
                
                // Update artikel
                $oldLength = strlen($article->content);
                $newLength = strlen($text);
                
                $article->update(['content' => $text]);
                
                $this->info("  -> Berhasil di-update ({$oldLength} -> {$newLength} chars)");
                
            } catch (\Exception $e) {
                $this->error("  -> Error: {$e->getMessage()}");
            }
        }
        
        $this->info('Selesai!');
        return 0;
    }
    
    private function extractTextFromHtml(string $html): string
    {
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
        
        // Decode HTML entities LENGKAP
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = html_entity_decode($text);
        
        // Fix bullet/harga separator (PRIORITAS: harga dulu, baru bullet)
        $bullets = ['•', '·', '∙', '⋅', '\xE2\x80\xA2', '\xC2\xB7', '\xE2\x88\x99', '\xE2\x8B\x85'];
        $text = str_replace($bullets, '---BULLET---', $text);
        
        // Fix harga dengan ---BULLET--- marker
        $text = preg_replace('/Rp\s*(\d+)\s*---BULLET---\s*(\d+)(?:\s*---BULLET---\s*(\d+))?/i', 'Rp $1.$2.$3', $text);
        $text = preg_replace('/(\d+)\s*---BULLET---\s*(\d+)(?:\s*---BULLET---\s*(\d+))?/', '$1.$2.$3', $text);
        
        // Sisa bullet jadi normal bullet
        $text = str_replace('---BULLET---', ' • ', $text);
        
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // blacklist
        $blacklist = ['beranda','profil','akreditasi','prestasi','mitra','youtube','linkedin','twitter','facebook','tiktok','instagram','contact us'];
        foreach ($blacklist as $bad) {
            $text = str_ireplace($bad, ' ', $text);
        }
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Smart structure
        $text = $this->smartStructurePricing($text);
        
        return trim($text);
    }
    
    private function smartStructurePricing(string $text): string
    {
        $hasPricing = preg_match('/Rp\s*[\d\.]+/', $text) || preg_match('/\d+\.\d{3}/', $text);
        
        if (!$hasPricing) {
            return $this->basicStructure($text);
        }
        
        // Format harga
        $text = preg_replace('/Rp\s+(\d+)[\.\s]*(\d{3})[\.\s]*(\d{3})/i', 'Rp $1.$2.$3', $text);
        $text = preg_replace('/Rp\s+(\d+)[\.\s]*(\d{3})/i', 'Rp $1.$2', $text);
        
        $paragraphs = preg_split('/(?=[A-Z][a-z]+\s*[:\-])/u', $text);
        if ($paragraphs === false) {
            $paragraphs = [$text];
        }
        $paragraphs = array_map('trim', $paragraphs);
        $paragraphs = array_values(array_filter($paragraphs, fn($p) => strlen($p) > 20));
        
        $structured = [];
        $currentSection = '';
        
        foreach ($paragraphs as $p) {
            if (preg_match('/^(Paket|Harga|Plan|Pro|Business|Enterprise|Starter|Basic|Premium)/i', $p)) {
                if ($currentSection) {
                    $structured[] = $currentSection;
                }
                $currentSection = $p;
            } else {
                $currentSection .= "\n" . $p;
            }
        }
        
        if ($currentSection) {
            $structured[] = $currentSection;
        }
        
        if (empty($structured)) {
            return $this->basicStructure($text);
        }
        
        $result = [];
        foreach ($structured as $section) {
            $lines = explode("\n", $section);
            $lines = array_map('trim', $lines);
            $lines = array_values(array_filter($lines));
            
            if (!empty($lines) && isset($lines[0])) {
                $result[] = '📦 ' . $lines[0];
                foreach (array_slice($lines, 1) as $line) {
                    $result[] = '   • ' . $line;
                }
                $result[] = '';
            }
        }
        
        return implode("\n", $result);
    }
    
    private function basicStructure(string $text): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/u', $text);
        if ($sentences === false) {
            return trim($text);
        }
        $sentences = array_map('trim', $sentences);
        $sentences = array_values(array_filter($sentences, fn($s) => strlen($s) > 10));
        
        $paragraphs = [];
        $current = '';
        
        foreach ($sentences as $sentence) {
            if (strlen($current) > 300) {
                $paragraphs[] = trim($current);
                $current = $sentence;
            } else {
                $current .= ' ' . $sentence;
            }
        }
        
        if ($current) {
            $paragraphs[] = trim($current);
        }
        
        $result = [];
        foreach ($paragraphs as $i => $para) {
            if ($i === 0) {
                $result[] = $para;
            } else {
                if (strlen($para) < 150) {
                    $result[] = '• ' . $para;
                } else {
                    $result[] = $para;
                }
            }
        }
        
        return implode("\n\n", $result);
    }
}
