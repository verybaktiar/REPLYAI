<?php

namespace App\Services\Scraper;

use App\Models\KbScrapeJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class WebScraperService
{
    /**
     * Maximum execution time for scraping (seconds)
     */
    const MAX_EXECUTION_TIME = 35;

    /**
     * Path to Node.js scraper script
     */
    private string $scraperScriptPath;

    public function __construct()
    {
        $this->scraperScriptPath = base_path('app/Services/Scraper/puppeteer-scraper.js');
    }

    /**
     * Process a scrape job
     */
    public function processJob(KbScrapeJob $job): void
    {
        try {
            // Update job status
            $job->update([
                'status' => KbScrapeJob::STATUS_PROCESSING,
                'started_at' => now(),
                'progress_percent' => 5,
                'progress_step' => 'Memulai browser...'
            ]);

            // Validate URL
            if (!$this->isValidUrl($job->url)) {
                throw new \Exception('URL tidak valid atau tidak diizinkan');
            }

            // Create temporary output file
            $outputFile = storage_path('app/scraper/' . $job->job_id . '.json');
            
            // Ensure directory exists
            if (!is_dir(dirname($outputFile))) {
                mkdir(dirname($outputFile), 0755, true);
            }

            // Run Puppeteer scraper
            $result = $this->runScraper($job->url, $outputFile, $job);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Gagal mengekstrak data dari website');
            }

            // Parse extracted data
            $scrapedData = $result['data'] ?? [];
            $entities = $result['entities'] ?? [];

            // Check if we got meaningful data
            if (!$entities['hasPricing'] && !$entities['hasFeatures'] && !$entities['hasAbout']) {
                throw new \Exception('Tidak dapat menemukan data terstruktur (harga, fitur, atau deskripsi). Website mungkin menggunakan proteksi atau format yang tidak didukung.');
            }

            // Update job with results
            $job->markCompleted($scrapedData, $entities);

            // Save raw HTML for debugging (optional)
            if (!empty($result['rawHtml'])) {
                $job->update(['raw_html' => $result['rawHtml']]);
            }

            // Cleanup output file
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }

        } catch (\Exception $e) {
            Log::error('Web scraping failed', [
                'job_id' => $job->job_id,
                'url' => $job->url,
                'error' => $e->getMessage()
            ]);

            $job->markFailed($e->getMessage());

            // Cleanup on failure
            $outputFile = storage_path('app/scraper/' . $job->job_id . '.json');
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
        }
    }

    /**
     * Run the Puppeteer scraper
     */
    private function runScraper(string $url, string $outputFile, KbScrapeJob $job): array
    {
        $nodePath = $this->findNodeExecutable();
        
        if (!$nodePath) {
            throw new \Exception('Node.js tidak ditemukan di server');
        }

        $command = sprintf(
            '%s %s %s %s 2>&1',
            escapeshellarg($nodePath),
            escapeshellarg($this->scraperScriptPath),
            escapeshellarg($url),
            escapeshellarg($outputFile)
        );

        // Execute with timeout
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \Exception('Gagal menjalankan proses scraper');
        }

        // Set streams to non-blocking
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $startTime = time();
        $output = '';
        $stderr = '';

        while (true) {
            $status = proc_get_status($process);

            if (!$status['running']) {
                break;
            }

            // Check timeout
            if (time() - $startTime > self::MAX_EXECUTION_TIME) {
                proc_terminate($process, 9);
                throw new \Exception('Timeout: Scraping memakan waktu terlalu lama (>30 detik)');
            }

            // Read progress updates from stdout
            $stdout = stream_get_contents($pipes[1]);
            if ($stdout) {
                $output .= $stdout;
                
                // Parse progress updates (JSON lines)
                $lines = explode("\n", $stdout);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    $progress = json_decode($line, true);
                    if ($progress && isset($progress['step'], $progress['progress'])) {
                        $job->updateProgress($progress['progress'], $progress['step']);
                    }
                }
            }

            // Read stderr
            $err = stream_get_contents($pipes[2]);
            if ($err) {
                $stderr .= $err;
            }

            usleep(100000); // 100ms
        }

        // Get final output
        $output .= stream_get_contents($pipes[1]);
        $stderr .= stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        // Read result from output file
        if (!file_exists($outputFile)) {
            throw new \Exception('Output file tidak ditemukan: ' . $stderr);
        }

        $result = json_decode(file_get_contents($outputFile), true);

        if (!$result) {
            throw new \Exception('Gagal parsing hasil: ' . json_last_error_msg());
        }

        return $result;
    }

    /**
     * Validate URL
     */
    private function isValidUrl(string $url): bool
    {
        // Check format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);

        // Must be http or https
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return false;
        }

        // Block localhost and private IPs
        $host = strtolower($parsed['host'] ?? '');
        
        if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
            return false;
        }

        // Block private IP ranges
        if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $host)) {
            return false;
        }

        return true;
    }

    /**
     * Find Node.js executable
     */
    private function findNodeExecutable(): ?string
    {
        $possiblePaths = [
            'node',  // In PATH
            'C:\\Program Files\\nodejs\\node.exe',
            'C:\\Program Files (x86)\\nodejs\\node.exe',
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/node/bin/node',
        ];

        foreach ($possiblePaths as $path) {
            if ($path === 'node') {
                // Check if in PATH
                $output = shell_exec('where node 2>nul || which node 2>/dev/null');
                if ($output && trim($output)) {
                    return 'node';
                }
            } elseif (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Convert scraped data to KB article format
     */
    public function convertToKbFormat(array $scrapedData, ?string $customName = null): array
    {
        $content = [];

        // Title
        $title = $customName ?: ($scrapedData['businessName'] ?: 'Informasi Produk/Layanan');

        // Build content
        if ($scrapedData['tagline']) {
            $content[] = $scrapedData['tagline'];
            $content[] = '';
        }

        // About section
        if ($scrapedData['about']) {
            $content[] = '📋 **Tentang Kami**';
            $content[] = $scrapedData['about'];
            $content[] = '';
        }

        // Pricing section
        if (!empty($scrapedData['pricing'])) {
            $content[] = '💰 **Daftar Harga & Paket**';
            $content[] = '';

            foreach ($scrapedData['pricing'] as $package) {
                $content[] = "📦 {$package['name']}";
                $content[] = "   💵 {$package['price']}" . ($package['period'] ? '/' . $package['period'] : '');
                
                if (!empty($package['features'])) {
                    foreach ($package['features'] as $feature) {
                        $content[] = "   ✅ {$feature}";
                    }
                }
                $content[] = '';
            }
        }

        // Features section (if no pricing)
        if (!empty($scrapedData['features']) && empty($scrapedData['pricing'])) {
            $content[] = '✨ **Fitur Unggulan**';
            $content[] = '';
            foreach ($scrapedData['features'] as $feature) {
                $content[] = "• {$feature}";
            }
            $content[] = '';
        }

        // FAQ section
        if (!empty($scrapedData['faq'])) {
            $content[] = '❓ **Pertanyaan Umum (FAQ)**';
            $content[] = '';

            foreach ($scrapedData['faq'] as $index => $faq) {
                $content[] = ($index + 1) . ". {$faq['question']}";
                $content[] = "   {$faq['answer']}";
                $content[] = '';
            }
        }

        return [
            'title' => $title,
            'content' => implode("\n", $content),
            'source_url' => $scrapedData['url'] ?? null,
            'category' => 'website_scrape'
        ];
    }
}
