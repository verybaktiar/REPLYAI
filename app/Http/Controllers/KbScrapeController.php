<?php

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbScrapeJob;
use App\Services\Scraper\WebScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class KbScrapeController extends Controller
{
    private WebScraperService $scraper;

    public function __construct(WebScraperService $scraper)
    {
        $this->scraper = $scraper;
    }

    /**
     * Start a new scrape job
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:500',
            'target_name' => 'nullable|string|max:200',
            'business_profile_id' => 'nullable|exists:business_profiles,id',
        ]);

        $user = Auth::user();
        $url = $validated['url'];

        // Rate limiting: 1 request per 5 minutes
        $rateLimitKey = 'scrape_rate_limit:' . $user->id;
        if (Cache::has($rateLimitKey)) {
            $remaining = Cache::get($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Rate limit terlampaui. Tunggu ' . $remaining . ' detik lagi.',
                'retry_after' => $remaining
            ], 429);
        }

        // Check for recent jobs
        if (KbScrapeJob::hasRecentJob($user->id, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki job scrape yang aktif. Tunggu sampai selesai.',
            ], 429);
        }

        // Validate URL (basic security checks)
        if (!$this->isValidUrl($url)) {
            return response()->json([
                'success' => false,
                'message' => 'URL tidak valid atau tidak diizinkan (localhost, IP private, dll)',
            ], 400);
        }

        // Set rate limit
        Cache::put($rateLimitKey, 300, 300); // 5 minutes

        // Create job
        $job = KbScrapeJob::create([
            'user_id' => $user->id,
            'business_profile_id' => $validated['business_profile_id'] ?? null,
            'status' => KbScrapeJob::STATUS_PENDING,
            'job_id' => KbScrapeJob::generateJobId(),
            'url' => $url,
            'target_name' => $validated['target_name'] ?? null,
            'progress_percent' => 0,
            'progress_step' => 'Menunggu antrian...',
        ]);

        // Process immediately and wait for result (synchronous)
        // Increase time limit for scraping
        set_time_limit(60);
        
        try {
            $this->scraper->processJob($job);
            $job->refresh();
            
            if ($job->status === KbScrapeJob::STATUS_COMPLETED) {
                return response()->json([
                    'success' => true,
                    'job_id' => $job->job_id,
                    'status' => $job->status,
                    'data' => $job->scraped_data,
                    'entities' => $job->extracted_entities,
                    'preview' => $this->scraper->convertToKbFormat($job->scraped_data, $job->target_name),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'job_id' => $job->job_id,
                    'status' => $job->status,
                    'message' => $job->error_message ?? 'Gagal mengekstrak data',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'job_id' => $job->job_id,
                'message' => 'Gagal mengekstrak data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get job status
     */
    public function status(string $jobId)
    {
        $job = KbScrapeJob::where('job_id', $jobId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $response = [
            'job_id' => $job->job_id,
            'status' => $job->status,
            'progress_percent' => $job->progress_percent,
            'progress_step' => $job->progress_step,
            'url' => $job->url,
            'target_name' => $job->target_name,
            'created_at' => $job->created_at,
            'started_at' => $job->started_at,
            'completed_at' => $job->completed_at,
        ];

        // Include results if completed
        if ($job->status === KbScrapeJob::STATUS_COMPLETED) {
            $response['data'] = $job->scraped_data;
            $response['entities'] = $job->extracted_entities;
            
            // Generate preview content
            if ($job->scraped_data) {
                $preview = $this->scraper->convertToKbFormat(
                    $job->scraped_data, 
                    $job->target_name
                );
                $response['preview'] = $preview;
            }
        }

        // Include error if failed
        if ($job->status === KbScrapeJob::STATUS_FAILED) {
            $response['error'] = $job->error_message;
        }

        return response()->json($response);
    }

    /**
     * Save scraped data to KB
     */
    public function saveToKb(Request $request, string $jobId)
    {
        $validated = $request->validate([
            'selected_entities' => 'nullable|array',
            'custom_title' => 'nullable|string|max:200',
        ]);

        $job = KbScrapeJob::where('job_id', $jobId)
            ->where('user_id', Auth::id())
            ->where('status', KbScrapeJob::STATUS_COMPLETED)
            ->firstOrFail();

        if (!$job->scraped_data) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data untuk disimpan',
            ], 400);
        }

        // Convert to KB format
        $kbData = $this->scraper->convertToKbFormat(
            $job->scraped_data,
            $validated['custom_title'] ?? $job->target_name
        );

        // Filter selected entities if provided
        $selectedEntities = $validated['selected_entities'] ?? [];
        if (!empty($selectedEntities)) {
            $kbData = $this->filterEntities($kbData, $selectedEntities, $job->scraped_data);
        }

        // Create KB article
        $article = KbArticle::create([
            'user_id' => Auth::id(),
            'business_profile_id' => $job->business_profile_id,
            'title' => $kbData['title'],
            'content' => $kbData['content'],
            'category' => 'website_import',
            'source_url' => $job->url,
            'is_active' => true,
            'metadata' => [
                'scraped_at' => $job->completed_at,
                'job_id' => $job->job_id,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan ke Knowledge Base',
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
            ],
        ]);
    }

    /**
     * Get user's scrape jobs
     */
    public function list()
    {
        $jobs = KbScrapeJob::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($job) => [
                'job_id' => $job->job_id,
                'url' => $job->url,
                'target_name' => $job->target_name,
                'status' => $job->status,
                'progress_percent' => $job->progress_percent,
                'created_at' => $job->created_at,
                'completed_at' => $job->completed_at,
            ]);

        return response()->json($jobs);
    }

    /**
     * Delete a scrape job
     */
    public function destroy(string $jobId)
    {
        $job = KbScrapeJob::where('job_id', $jobId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job dihapus',
        ]);
    }

    /**
     * Validate URL security
     */
    private function isValidUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);

        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return false;
        }

        $host = strtolower($parsed['host'] ?? '');
        
        if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
            return false;
        }

        if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $host)) {
            return false;
        }

        return true;
    }

    /**
     * Filter entities based on user selection
     */
    private function filterEntities(array $kbData, array $selectedEntities, array $scrapedData): array
    {
        $content = [];

        // Always include title
        $content[] = $kbData['title'];
        $content[] = '';

        // Include about only if selected
        if (!empty($scrapedData['about']) && in_array('about_section', $selectedEntities)) {
            $content[] = '📋 **Tentang Kami**';
            $content[] = $scrapedData['about'];
            $content[] = '';
        }

        // Filter pricing packages
        if (!empty($scrapedData['pricing'])) {
            $selectedPricing = array_filter($scrapedData['pricing'], function($pkg) use ($selectedEntities) {
                return in_array('pricing_' . $pkg['name'], $selectedEntities);
            });

            if (!empty($selectedPricing)) {
                $content[] = '💰 **Daftar Harga & Paket**';
                $content[] = '';

                foreach ($selectedPricing as $package) {
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
        }

        // Filter FAQ
        if (!empty($scrapedData['faq'])) {
            $selectedFaq = array_filter($scrapedData['faq'], function($faq) use ($selectedEntities) {
                return in_array('faq_' . $faq['question'], $selectedEntities);
            });

            if (!empty($selectedFaq)) {
                $content[] = '❓ **Pertanyaan Umum (FAQ)**';
                $content[] = '';

                $index = 1;
                foreach ($selectedFaq as $faq) {
                    $content[] = $index++ . ". {$faq['question']}";
                    $content[] = "   {$faq['answer']}";
                    $content[] = '';
                }
            }
        }

        $kbData['content'] = implode("\n", $content);
        return $kbData;
    }

    /**
     * Trigger background processing via async HTTP call
     */
    private function triggerBackgroundProcessing(string $jobId): void
    {
        // Use a non-blocking HTTP call to trigger processing
        $url = url('/kb/scrape/process/' . $jobId);
        
        // Use cURL with minimal timeout (fire and forget)
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT_MS => 500, // 500ms timeout (fire and forget)
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'X-Internal-Request: 1',
            ],
        ]);
        
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Process a job immediately (called via async request)
     */
    public function process(string $jobId)
    {
        // Check if this is an internal request
        if (!request()->header('X-Internal-Request')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $job = KbScrapeJob::where('job_id', $jobId)->first();
        
        if (!$job || $job->status !== KbScrapeJob::STATUS_PENDING) {
            return response()->json(['error' => 'Job not found or already processed'], 404);
        }

        // Process the job
        $this->scraper->processJob($job);

        return response()->json(['success' => true, 'status' => $job->fresh()->status]);
    }
}
