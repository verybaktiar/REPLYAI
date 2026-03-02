<?php

namespace App\Http\Controllers;

use App\Models\AutoReplyLog;
use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WebConversation;
use App\Models\KbMissedQuery;
use App\Models\AiTrainingExample;
use App\Models\Message;
use App\Models\WaMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiPerformanceController extends Controller
{
    /**
     * Main AI analytics dashboard view
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Get date range filter
        $dateRange = $request->get('range', '30'); // days
        $startDate = Carbon::now()->subDays((int)$dateRange)->startOfDay();

        // Get key metrics for the dashboard
        $intentAccuracy = $this->getIntentAccuracyData($startDate);
        $responseRelevance = $this->getResponseRelevanceData($startDate);
        $knowledgeGaps = $this->getKnowledgeGapsData();
        $confidenceDistribution = $this->getConfidenceDistributionData($startDate);
        $popularIntents = $this->getPopularIntentsData($startDate);
        $trainingImprovement = $this->getTrainingImprovementData();

        // Recent AI logs
        $recentLogs = AutoReplyLog::with(['conversation'])
            ->whereNotNull('ai_confidence')
            ->latest()
            ->limit(20)
            ->get();

        return view('pages.ai-performance.index', [
            'intentAccuracy' => $intentAccuracy,
            'responseRelevance' => $responseRelevance,
            'knowledgeGaps' => $knowledgeGaps,
            'confidenceDistribution' => $confidenceDistribution,
            'popularIntents' => $popularIntents,
            'trainingImprovement' => $trainingImprovement,
            'recentLogs' => $recentLogs,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Measure intent recognition accuracy
     * Compare AI predicted intent vs actual outcome
     * Return accuracy percentage by intent category
     */
    public function getIntentAccuracy(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $dateRange = $request->get('range', '30');
        $startDate = Carbon::now()->subDays((int)$dateRange)->startOfDay();

        $data = $this->getIntentAccuracyData($startDate);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'date_range_days' => $dateRange,
            ],
        ]);
    }

    /**
     * Get intent accuracy data
     */
    private function getIntentAccuracyData(Carbon $startDate): array
    {
        // Get conversations with AI intent data
        $intents = [];
        
        // From Instagram conversations
        $igIntents = Conversation::whereNotNull('ai_intent')
            ->where('created_at', '>=', $startDate)
            ->select('ai_intent', 'ai_intent_confidence', 'status')
            ->get();
        
        foreach ($igIntents as $conv) {
            $intents[] = [
                'intent' => $conv->ai_intent,
                'confidence' => $conv->ai_intent_confidence,
                'status' => $conv->status,
            ];
        }

        // From WhatsApp conversations
        $waIntents = WaConversation::whereNotNull('ai_intent')
            ->where('created_at', '>=', $startDate)
            ->select('ai_intent', 'ai_intent_confidence', 'status')
            ->get();
        
        foreach ($waIntents as $conv) {
            $intents[] = [
                'intent' => $conv->ai_intent,
                'confidence' => $conv->ai_intent_confidence,
                'status' => $conv->status,
            ];
        }

        // From Web conversations
        $webIntents = WebConversation::whereNotNull('ai_intent')
            ->where('created_at', '>=', $startDate)
            ->select('ai_intent', 'ai_intent_confidence', 'status')
            ->get();
        
        foreach ($webIntents as $conv) {
            $intents[] = [
                'intent' => $conv->ai_intent,
                'confidence' => $conv->ai_intent_confidence,
                'status' => $conv->status,
            ];
        }

        // Calculate accuracy by intent
        $intentStats = [];
        foreach ($intents as $intent) {
            $name = $intent['intent'] ?? 'unknown';
            
            if (!isset($intentStats[$name])) {
                $intentStats[$name] = [
                    'total' => 0,
                    'high_confidence' => 0,
                    'resolved' => 0,
                    'avg_confidence' => 0,
                ];
            }
            
            $intentStats[$name]['total']++;
            $intentStats[$name]['avg_confidence'] += $intent['confidence'] ?? 0;
            
            if (($intent['confidence'] ?? 0) >= 0.8) {
                $intentStats[$name]['high_confidence']++;
            }
            
            if (in_array($intent['status'], ['resolved', 'closed'])) {
                $intentStats[$name]['resolved']++;
            }
        }

        // Calculate percentages
        $result = [];
        foreach ($intentStats as $intent => $stats) {
            $result[] = [
                'intent' => $intent,
                'total' => $stats['total'],
                'accuracy_percentage' => $stats['total'] > 0 
                    ? round(($stats['high_confidence'] / $stats['total']) * 100, 1) 
                    : 0,
                'resolution_rate' => $stats['total'] > 0 
                    ? round(($stats['resolved'] / $stats['total']) * 100, 1) 
                    : 0,
                'avg_confidence' => $stats['total'] > 0 
                    ? round($stats['avg_confidence'] / $stats['total'], 2) 
                    : 0,
            ];
        }

        // Sort by total count descending
        usort($result, fn($a, $b) => $b['total'] <=> $a['total']);

        return [
            'by_intent' => $result,
            'overall_accuracy' => count($intents) > 0 
                ? round((collect($intents)->where('confidence', '>=', 0.8)->count() / count($intents)) * 100, 1)
                : 0,
            'total_analyzed' => count($intents),
        ];
    }

    /**
     * Measure response quality
     * Track user feedback on AI responses
     * Calculate relevance score
     */
    public function getResponseRelevance(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $dateRange = $request->get('range', '30');
        $startDate = Carbon::now()->subDays((int)$dateRange)->startOfDay();

        $data = $this->getResponseRelevanceData($startDate);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'date_range_days' => $dateRange,
            ],
        ]);
    }

    /**
     * Get response relevance data
     */
    private function getResponseRelevanceData(Carbon $startDate): array
    {
        // Get training examples with ratings
        $trainingExamples = AiTrainingExample::where('created_at', '>=', $startDate)
            ->whereNotNull('rating')
            ->get();

        // Get auto reply logs with status analysis
        $logs = AutoReplyLog::where('created_at', '>=', $startDate)
            ->select('status', 'ai_confidence', 'response_source')
            ->get();

        // Calculate metrics
        $totalLogs = $logs->count();
        $successfulReplies = $logs->whereIn('status', ['sent', 'sent_ai', 'success'])->count();
        $failedReplies = $logs->whereIn('status', ['failed', 'error'])->count();
        $aiReplies = $logs->where('response_source', 'ai')->count();
        
        // Rating distribution
        $ratings = [
            'excellent' => $trainingExamples->where('rating', '>=', 4)->count(),
            'good' => $trainingExamples->whereBetween('rating', [3, 3.9])->count(),
            'average' => $trainingExamples->whereBetween('rating', [2, 2.9])->count(),
            'poor' => $trainingExamples->where('rating', '<', 2)->count(),
        ];

        $totalRated = $trainingExamples->count();
        $avgRating = $totalRated > 0 ? round($trainingExamples->avg('rating'), 2) : 0;

        // Relevance score (combination of success rate and average confidence)
        $successRate = $totalLogs > 0 ? round(($successfulReplies / $totalLogs) * 100, 1) : 0;
        $avgConfidence = $logs->whereNotNull('ai_confidence')->avg('ai_confidence') ?? 0;
        $relevanceScore = round(($successRate * 0.6) + (($avgConfidence * 100) * 0.4), 1);

        return [
            'relevance_score' => $relevanceScore,
            'success_rate' => $successRate,
            'avg_confidence' => round($avgConfidence * 100, 1),
            'total_responses' => $totalLogs,
            'successful_replies' => $successfulReplies,
            'failed_replies' => $failedReplies,
            'ai_generated_replies' => $aiReplies,
            'ratings' => [
                'distribution' => $ratings,
                'average' => $avgRating,
                'total_rated' => $totalRated,
            ],
        ];
    }

    /**
     * Identify missing KB articles
     * Query KbMissedQuery model
     * Group by frequency
     * Suggest new articles
     */
    public function getKnowledgeGaps(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $this->getKnowledgeGapsData();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get knowledge gaps data
     */
    private function getKnowledgeGapsData(): array
    {
        // Get top missed queries
        $missedQueries = KbMissedQuery::pending()
            ->orderByDesc('count')
            ->limit(50)
            ->get();

        // Group by frequency buckets
        $frequencyBuckets = [
            'very_high' => $missedQueries->where('count', '>=', 20)->values(),
            'high' => $missedQueries->whereBetween('count', [10, 19])->values(),
            'medium' => $missedQueries->whereBetween('count', [5, 9])->values(),
            'low' => $missedQueries->where('count', '<', 5)->values(),
        ];

        // Extract common keywords/patterns
        $suggestedTopics = $this->extractSuggestedTopics($missedQueries);

        return [
            'total_unanswered' => $missedQueries->sum('count'),
            'unique_queries' => $missedQueries->count(),
            'by_frequency' => [
                'very_high' => [
                    'count' => $frequencyBuckets['very_high']->count(),
                    'queries' => $frequencyBuckets['very_high']->take(5),
                ],
                'high' => [
                    'count' => $frequencyBuckets['high']->count(),
                    'queries' => $frequencyBuckets['high']->take(5),
                ],
                'medium' => [
                    'count' => $frequencyBuckets['medium']->count(),
                    'queries' => $frequencyBuckets['medium']->take(5),
                ],
                'low' => [
                    'count' => $frequencyBuckets['low']->count(),
                    'queries' => $frequencyBuckets['low']->take(5),
                ],
            ],
            'top_queries' => $missedQueries->take(10)->map(fn($q) => [
                'id' => $q->id,
                'question' => $q->question,
                'count' => $q->count,
                'last_asked_at' => $q->last_asked_at,
            ]),
            'suggested_topics' => $suggestedTopics,
        ];
    }

    /**
     * Extract suggested topics from missed queries
     */
    private function extractSuggestedTopics($queries): array
    {
        $topics = [];
        $commonWords = ['apa', 'bagaimana', 'cara', 'mau', 'ingin', 'bisa', 'tolong', 'ada', 'yang', 'dan', 'atau', 'ini', 'itu'];
        
        foreach ($queries->take(20) as $query) {
            $words = explode(' ', strtolower($query->question));
            foreach ($words as $word) {
                $word = preg_replace('/[^\w]/', '', $word);
                if (strlen($word) > 3 && !in_array($word, $commonWords)) {
                    if (!isset($topics[$word])) {
                        $topics[$word] = ['word' => $word, 'count' => 0, 'queries' => []];
                    }
                    $topics[$word]['count'] += $query->count;
                    if (count($topics[$word]['queries']) < 3) {
                        $topics[$word]['queries'][] = $query->question;
                    }
                }
            }
        }

        // Sort by count and take top 10
        usort($topics, fn($a, $b) => $b['count'] <=> $a['count']);
        
        return array_slice($topics, 0, 10);
    }

    /**
     * Track AI learning progress
     * Compare accuracy over time periods
     * Show improvement trends
     */
    public function getTrainingImprovement(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $this->getTrainingImprovementData();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get training improvement data
     */
    private function getTrainingImprovementData(): array
    {
        $periods = [7, 14, 30, 90]; // days
        $trends = [];

        foreach ($periods as $days) {
            $startDate = Carbon::now()->subDays($days)->startOfDay();
            
            // Get logs for this period
            $logs = AutoReplyLog::where('created_at', '>=', $startDate)
                ->select('ai_confidence', 'status', 'created_at')
                ->get();

            $total = $logs->count();
            $successful = $logs->whereIn('status', ['sent', 'sent_ai', 'success'])->count();
            $avgConfidence = $logs->whereNotNull('ai_confidence')->avg('ai_confidence') ?? 0;

            // Training examples added
            $trainingExamples = AiTrainingExample::where('created_at', '>=', $startDate)->count();

            $trends["last_{$days}_days"] = [
                'period_days' => $days,
                'total_interactions' => $total,
                'successful_interactions' => $successful,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 1) : 0,
                'avg_confidence' => round($avgConfidence * 100, 1),
                'training_examples_added' => $trainingExamples,
            ];
        }

        // Calculate improvement rate
        $current = $trends['last_7_days']['success_rate'] ?? 0;
        $previous = $trends['last_14_days']['success_rate'] ?? 0;
        $improvement = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0;

        // Monthly trend for chart
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $monthLogs = AutoReplyLog::whereBetween('created_at', [$monthStart, $monthEnd])->get();
            $monthTotal = $monthLogs->count();
            $monthSuccessful = $monthLogs->whereIn('status', ['sent', 'sent_ai', 'success'])->count();
            
            $monthlyTrend[] = [
                'month' => $monthStart->format('M Y'),
                'accuracy' => $monthTotal > 0 ? round(($monthSuccessful / $monthTotal) * 100, 1) : 0,
                'total' => $monthTotal,
            ];
        }

        return [
            'current_performance' => $trends['last_7_days'],
            'trends' => $trends,
            'improvement_rate' => $improvement,
            'trend_direction' => $improvement > 0 ? 'up' : ($improvement < 0 ? 'down' : 'stable'),
            'monthly_history' => $monthlyTrend,
        ];
    }

    /**
     * AI confidence histogram
     * Buckets: Low (<50%), Medium (50-80%), High (>80%)
     * Return counts and percentages
     */
    public function getConfidenceDistribution(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $dateRange = $request->get('range', '30');
        $startDate = Carbon::now()->subDays((int)$dateRange)->startOfDay();

        $data = $this->getConfidenceDistributionData($startDate);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'date_range_days' => $dateRange,
            ],
        ]);
    }

    /**
     * Get confidence distribution data
     */
    private function getConfidenceDistributionData(Carbon $startDate): array
    {
        $logs = AutoReplyLog::where('created_at', '>=', $startDate)
            ->whereNotNull('ai_confidence')
            ->select('ai_confidence', 'status')
            ->get();

        $total = $logs->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'buckets' => [
                    'low' => ['count' => 0, 'percentage' => 0, 'range' => '< 50%'],
                    'medium' => ['count' => 0, 'percentage' => 0, 'range' => '50% - 80%'],
                    'high' => ['count' => 0, 'percentage' => 0, 'range' => '> 80%'],
                ],
            ];
        }

        $low = $logs->where('ai_confidence', '<', 0.5);
        $medium = $logs->whereBetween('ai_confidence', [0.5, 0.8]);
        $high = $logs->where('ai_confidence', '>', 0.8);

        // Calculate success rate per bucket
        $getSuccessRate = function ($collection) {
            $total = $collection->count();
            if ($total === 0) return 0;
            $successful = $collection->whereIn('status', ['sent', 'sent_ai', 'success'])->count();
            return round(($successful / $total) * 100, 1);
        };

        return [
            'total' => $total,
            'buckets' => [
                'low' => [
                    'count' => $low->count(),
                    'percentage' => round(($low->count() / $total) * 100, 1),
                    'range' => '< 50%',
                    'success_rate' => $getSuccessRate($low),
                ],
                'medium' => [
                    'count' => $medium->count(),
                    'percentage' => round(($medium->count() / $total) * 100, 1),
                    'range' => '50% - 80%',
                    'success_rate' => $getSuccessRate($medium),
                ],
                'high' => [
                    'count' => $high->count(),
                    'percentage' => round(($high->count() / $total) * 100, 1),
                    'range' => '> 80%',
                    'success_rate' => $getSuccessRate($high),
                ],
            ],
        ];
    }

    /**
     * Most common customer intents
     * Group by intent type
     * Show trends
     */
    public function getPopularIntents(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $dateRange = $request->get('range', '30');
        $startDate = Carbon::now()->subDays((int)$dateRange)->startOfDay();

        $data = $this->getPopularIntentsData($startDate);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'date_range_days' => $dateRange,
            ],
        ]);
    }

    /**
     * Get popular intents data
     */
    private function getPopularIntentsData(Carbon $startDate): array
    {
        $allIntents = [];

        // Instagram
        $igIntents = Conversation::whereNotNull('ai_intent')
            ->where('created_at', '>=', $startDate)
            ->select('ai_intent', 'ai_intent_confidence', 'ai_sentiment')
            ->get();
        foreach ($igIntents as $i) {
            $allIntents[] = ['intent' => $i->ai_intent, 'confidence' => $i->ai_intent_confidence, 'sentiment' => $i->ai_sentiment];
        }

        // WhatsApp
        $waIntents = WaConversation::whereNotNull('ai_intent')
            ->where('created_at', '>=', $startDate)
            ->select('ai_intent', 'ai_intent_confidence', 'ai_sentiment')
            ->get();
        foreach ($waIntents as $i) {
            $allIntents[] = ['intent' => $i->ai_intent, 'confidence' => $i->ai_intent_confidence, 'sentiment' => $i->ai_sentiment];
        }

        // Web
        $webIntents = WebConversation::whereNotNull('ai_intent')
            ->where('created_at', '>=', $startDate)
            ->select('ai_intent', 'ai_intent_confidence', 'ai_sentiment')
            ->get();
        foreach ($webIntents as $i) {
            $allIntents[] = ['intent' => $i->ai_intent, 'confidence' => $i->ai_intent_confidence, 'sentiment' => $i->ai_sentiment];
        }

        // Group by intent
        $grouped = [];
        foreach ($allIntents as $item) {
            $intent = $item['intent'];
            if (!isset($grouped[$intent])) {
                $grouped[$intent] = [
                    'intent' => $intent,
                    'count' => 0,
                    'total_confidence' => 0,
                    'sentiments' => [],
                ];
            }
            $grouped[$intent]['count']++;
            $grouped[$intent]['total_confidence'] += $item['confidence'] ?? 0;
            if ($item['sentiment']) {
                $grouped[$intent]['sentiments'][] = $item['sentiment'];
            }
        }

        // Calculate percentages and format
        $total = count($allIntents);
        $result = [];
        foreach ($grouped as $intent => $data) {
            $sentimentCounts = array_count_values($data['sentiments']);
            arsort($sentimentCounts);
            
            $result[] = [
                'intent' => $intent,
                'count' => $data['count'],
                'percentage' => $total > 0 ? round(($data['count'] / $total) * 100, 1) : 0,
                'avg_confidence' => $data['count'] > 0 ? round($data['total_confidence'] / $data['count'], 2) : 0,
                'dominant_sentiment' => array_key_first($sentimentCounts) ?? 'neutral',
            ];
        }

        // Sort by count descending
        usort($result, fn($a, $b) => $b['count'] <=> $a['count']);

        // Daily trend for top 5 intents
        $topIntents = array_slice(array_column($result, 'intent'), 0, 5);
        $dailyTrend = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayStart = Carbon::now()->subDays($i)->startOfDay();
            $dayEnd = Carbon::now()->subDays($i)->endOfDay();
            
            $dayData = ['date' => $date];
            
            foreach ($topIntents as $intent) {
                $count = Conversation::where('ai_intent', $intent)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();
                $count += WaConversation::where('ai_intent', $intent)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();
                $count += WebConversation::where('ai_intent', $intent)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();
                    
                $dayData[$intent] = $count;
            }
            
            $dailyTrend[] = $dayData;
        }

        return [
            'total_intents_detected' => $total,
            'intents' => $result,
            'top_5' => array_slice($result, 0, 5),
            'daily_trend' => $dailyTrend,
        ];
    }
}
