<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WebConversation;
use App\Models\Message;
use App\Models\WaMessage;
use App\Models\CsatRating;
use App\Models\AutoReplyLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConversationQualityController extends Controller
{
    /**
     * Display the conversation quality page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('range', 30);
        $since = now()->subDays($days);
        
        // Get sentiment analysis
        $sentiment = $this->getSentimentAnalysisData($user, $since);
        $totalSentiment = array_sum($sentiment['distribution']);
        
        $sentimentPositive = $totalSentiment > 0 ? round(($sentiment['distribution']['positive'] / $totalSentiment) * 100, 1) : 0;
        $sentimentNeutral = $totalSentiment > 0 ? round(($sentiment['distribution']['neutral'] / $totalSentiment) * 100, 1) : 0;
        $sentimentNegative = $totalSentiment > 0 ? round(($sentiment['distribution']['negative'] / $totalSentiment) * 100, 1) : 0;
        
        // Get quality score (weighted calculation)
        $qualityScore = $this->calculateQualityScore($user, $since);
        
        // Get bot handled percentage
        $botHandled = $this->getBotHandledPercentage($user, $since);
        
        return view('pages.reports.quality.index', compact(
            'qualityScore',
            'sentimentPositive',
            'sentimentNeutral',
            'sentimentNegative',
            'botHandled',
            'days'
        ));
    }
    
    /**
     * Get sentiment analysis data
     */
    private function getSentimentAnalysisData($user, $since): array
    {
        $result = [
            'distribution' => ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'unknown' => 0],
        ];
        
        // WhatsApp sentiment
        $waSentiment = \App\Models\WaConversation::where('user_id', $user->id)
            ->whereNotNull('ai_sentiment')
            ->where('ai_analyzed_at', '>=', $since)
            ->select('ai_sentiment', \DB::raw('COUNT(*) as count'))
            ->groupBy('ai_sentiment')
            ->pluck('count', 'ai_sentiment')
            ->toArray();
        
        foreach ($waSentiment as $sentiment => $count) {
            $result['distribution'][$sentiment] += $count;
        }
        
        // Instagram sentiment
        $igSentiment = \App\Models\Conversation::where('user_id', $user->id)
            ->whereNotNull('ai_sentiment')
            ->where('ai_analyzed_at', '>=', $since)
            ->select('ai_sentiment', \DB::raw('COUNT(*) as count'))
            ->groupBy('ai_sentiment')
            ->pluck('count', 'ai_sentiment')
            ->toArray();
        
        foreach ($igSentiment as $sentiment => $count) {
            $result['distribution'][$sentiment] += $count;
        }
        
        return $result;
    }
    
    /**
     * Calculate overall quality score
     */
    private function calculateQualityScore($user, $since): int
    {
        // Simple weighted scoring based on sentiment and response time
        $sentiment = $this->getSentimentAnalysisData($user, $since);
        $total = array_sum($sentiment['distribution']);
        
        if ($total == 0) return 0;
        
        $positive = $sentiment['distribution']['positive'] / $total;
        $neutral = $sentiment['distribution']['neutral'] / $total;
        $negative = $sentiment['distribution']['negative'] / $total;
        
        // Weighted: positive 100, neutral 70, negative 30
        $score = ($positive * 100) + ($neutral * 70) + ($negative * 30);
        
        return min(100, max(0, round($score)));
    }
    
    /**
     * Get bot handled percentage
     */
    private function getBotHandledPercentage($user, $since): float
    {
        // Ambil semua phone_number dari conversation user
        $phoneNumbers = \App\Models\WaConversation::where('user_id', $user->id)
            ->pluck('phone_number');
        
        if ($phoneNumbers->isEmpty()) {
            return 0;
        }
        
        // Hitung dari wa_messages yang ada bot_reply
        $totalMessages = \App\Models\WaMessage::whereIn('phone_number', $phoneNumbers)
            ->where('direction', 'incoming')
            ->where('created_at', '>=', $since)
            ->count();
        
        $botHandled = \App\Models\WaMessage::whereIn('phone_number', $phoneNumbers)
            ->where('direction', 'incoming')
            ->where('created_at', '>=', $since)
            ->whereNotNull('bot_reply')
            ->count();
        
        return $totalMessages > 0 ? round(($botHandled / $totalMessages) * 100, 1) : 0;
    }

    /**
     * Get sentiment analysis distribution.
     */
    public function getSentimentAnalysis(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);
        $platform = $request->input('platform', 'all');
        $since = now()->subDays($days);

        $cacheKey = "sentiment_analysis_{$user->id}_{$platform}_{$days}";

        $analysis = Cache::remember($cacheKey, 300, function () use ($user, $platform, $since) {
            $result = [
                'distribution' => [
                    'positive' => 0,
                    'neutral' => 0,
                    'negative' => 0,
                    'unknown' => 0,
                ],
                'by_platform' => [
                    'whatsapp' => ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'unknown' => 0],
                    'instagram' => ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'unknown' => 0],
                    'web' => ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'unknown' => 0],
                ],
                'trend' => [],
            ];

            // WhatsApp sentiment
            if ($platform === 'all' || $platform === 'whatsapp') {
                $waSentiment = WaConversation::where('user_id', $user->id)
                    ->whereNotNull('ai_sentiment')
                    ->where('ai_analyzed_at', '>=', $since)
                    ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                    ->groupBy('ai_sentiment')
                    ->pluck('count', 'ai_sentiment')
                    ->toArray();

                foreach ($waSentiment as $sentiment => $count) {
                    $result['distribution'][$sentiment] += $count;
                    $result['by_platform']['whatsapp'][$sentiment] = $count;
                }

                // Calculate unknown
                $waTotal = WaConversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->count();
                $waAnalyzed = array_sum($waSentiment);
                $result['by_platform']['whatsapp']['unknown'] = max(0, $waTotal - $waAnalyzed);
                $result['distribution']['unknown'] += $result['by_platform']['whatsapp']['unknown'];
            }

            // Instagram sentiment
            if ($platform === 'all' || $platform === 'instagram') {
                $igSentiment = Conversation::where('user_id', $user->id)
                    ->whereNotNull('ai_sentiment')
                    ->where('ai_analyzed_at', '>=', $since)
                    ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                    ->groupBy('ai_sentiment')
                    ->pluck('count', 'ai_sentiment')
                    ->toArray();

                foreach ($igSentiment as $sentiment => $count) {
                    $result['distribution'][$sentiment] += $count;
                    $result['by_platform']['instagram'][$sentiment] = $count;
                }

                // Calculate unknown
                $igTotal = Conversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->count();
                $igAnalyzed = array_sum($igSentiment);
                $result['by_platform']['instagram']['unknown'] = max(0, $igTotal - $igAnalyzed);
                $result['distribution']['unknown'] += $result['by_platform']['instagram']['unknown'];
            }

            // Web sentiment
            if ($platform === 'all' || $platform === 'web') {
                $webSentiment = WebConversation::where('user_id', $user->id)
                    ->whereNotNull('ai_sentiment')
                    ->where('ai_analyzed_at', '>=', $since)
                    ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                    ->groupBy('ai_sentiment')
                    ->pluck('count', 'ai_sentiment')
                    ->toArray();

                foreach ($webSentiment as $sentiment => $count) {
                    $result['distribution'][$sentiment] += $count;
                    $result['by_platform']['web'][$sentiment] = $count;
                }

                // Calculate unknown
                $webTotal = WebConversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->count();
                $webAnalyzed = array_sum($webSentiment);
                $result['by_platform']['web']['unknown'] = max(0, $webTotal - $webAnalyzed);
                $result['distribution']['unknown'] += $result['by_platform']['web']['unknown'];
            }

            // Calculate percentages
            $total = array_sum($result['distribution']);
            $result['percentages'] = $total > 0 ? [
                'positive' => round(($result['distribution']['positive'] / $total) * 100, 1),
                'neutral' => round(($result['distribution']['neutral'] / $total) * 100, 1),
                'negative' => round(($result['distribution']['negative'] / $total) * 100, 1),
                'unknown' => round(($result['distribution']['unknown'] / $total) * 100, 1),
            ] : [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'unknown' => 0,
            ];

            // Daily trend
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dayData = [
                    'date' => $date->format('Y-m-d'),
                    'positive' => 0,
                    'neutral' => 0,
                    'negative' => 0,
                ];

                if ($platform === 'all' || $platform === 'whatsapp') {
                    $waDaily = WaConversation::where('user_id', $user->id)
                        ->whereNotNull('ai_sentiment')
                        ->whereDate('ai_analyzed_at', $date)
                        ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                        ->groupBy('ai_sentiment')
                        ->pluck('count', 'ai_sentiment')
                        ->toArray();

                    $dayData['positive'] += $waDaily['positive'] ?? 0;
                    $dayData['neutral'] += $waDaily['neutral'] ?? 0;
                    $dayData['negative'] += $waDaily['negative'] ?? 0;
                }

                if ($platform === 'all' || $platform === 'instagram') {
                    $igDaily = Conversation::where('user_id', $user->id)
                        ->whereNotNull('ai_sentiment')
                        ->whereDate('ai_analyzed_at', $date)
                        ->select('ai_sentiment', DB::raw('COUNT(*) as count'))
                        ->groupBy('ai_sentiment')
                        ->pluck('count', 'ai_sentiment')
                        ->toArray();

                    $dayData['positive'] += $igDaily['positive'] ?? 0;
                    $dayData['neutral'] += $igDaily['neutral'] ?? 0;
                    $dayData['negative'] += $igDaily['negative'] ?? 0;
                }

                $result['trend'][] = $dayData;
            }

            // Sentiment score (weighted average)
            $analyzedTotal = $result['distribution']['positive'] + 
                            $result['distribution']['neutral'] + 
                            $result['distribution']['negative'];

            if ($analyzedTotal > 0) {
                $result['score'] = round(
                    (($result['distribution']['positive'] * 100) +
                     ($result['distribution']['neutral'] * 50) +
                     ($result['distribution']['negative'] * 0)) / $analyzedTotal,
                    1
                );
            } else {
                $result['score'] = 0;
            }

            return $result;
        });

        return response()->json([
            'ok' => true,
            'analysis' => $analysis,
            'period' => [
                'days' => $days,
                'from' => $since->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get response time metrics (FRT, ART, Resolution Time).
     */
    public function getResponseTimeMetrics(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);
        $platform = $request->input('platform', 'all');
        $since = now()->subDays($days);

        $cacheKey = "response_time_metrics_{$user->id}_{$platform}_{$days}";

        $metrics = Cache::remember($cacheKey, 300, function () use ($user, $platform, $since) {
            $result = [
                'first_response_time' => [
                    'avg_seconds' => 0,
                    'avg_formatted' => '0s',
                    'min_seconds' => 0,
                    'max_seconds' => 0,
                    'by_platform' => [],
                ],
                'average_response_time' => [
                    'avg_seconds' => 0,
                    'avg_formatted' => '0s',
                    'by_platform' => [],
                ],
                'resolution_time' => [
                    'avg_seconds' => 0,
                    'avg_formatted' => '0s',
                    'by_platform' => [],
                ],
            ];

            // WhatsApp metrics
            if ($platform === 'all' || $platform === 'whatsapp') {
                // First Response Time - time between first incoming and first bot reply
                $waFrt = WaMessage::where('user_id', $user->id)
                    ->where('direction', 'incoming')
                    ->whereNotNull('bot_reply')
                    ->where('created_at', '>=', $since)
                    ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_frt')
                    ->selectRaw('MIN(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as min_frt')
                    ->selectRaw('MAX(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as max_frt')
                    ->first();

                $result['first_response_time']['by_platform']['whatsapp'] = [
                    'avg_seconds' => round($waFrt->avg_frt ?? 0, 1),
                    'avg_formatted' => $this->formatDuration($waFrt->avg_frt ?? 0),
                    'min_seconds' => round($waFrt->min_frt ?? 0, 1),
                    'max_seconds' => round($waFrt->max_frt ?? 0, 1),
                ];

                // Average Response Time - all bot replies
                $waArt = WaMessage::where('user_id', $user->id)
                    ->where('direction', 'incoming')
                    ->whereNotNull('bot_reply')
                    ->where('created_at', '>=', $since)
                    ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_art')
                    ->value('avg_art');

                $result['average_response_time']['by_platform']['whatsapp'] = [
                    'avg_seconds' => round($waArt ?? 0, 1),
                    'avg_formatted' => $this->formatDuration($waArt ?? 0),
                ];

                // Resolution Time - time to close conversation
                $waResolution = WaConversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->whereNotNull('last_user_reply_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, last_user_reply_at)) as avg_resolution')
                    ->value('avg_resolution');

                $result['resolution_time']['by_platform']['whatsapp'] = [
                    'avg_seconds' => round($waResolution ?? 0, 1),
                    'avg_formatted' => $this->formatDuration($waResolution ?? 0),
                ];
            }

            // Instagram metrics
            if ($platform === 'all' || $platform === 'instagram') {
                $conversationIds = Conversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->pluck('id');

                // First Response Time from AutoReplyLog
                $igFrt = AutoReplyLog::whereIn('conversation_id', $conversationIds)
                    ->where('created_at', '>=', $since)
                    ->whereIn('status', ['sent', 'sent_ai', 'success'])
                    ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_frt')
                    ->first();

                $result['first_response_time']['by_platform']['instagram'] = [
                    'avg_seconds' => round($igFrt->avg_frt ?? 0, 1),
                    'avg_formatted' => $this->formatDuration($igFrt->avg_frt ?? 0),
                    'min_seconds' => 0,
                    'max_seconds' => 0,
                ];

                $result['average_response_time']['by_platform']['instagram'] = [
                    'avg_seconds' => round($igFrt->avg_frt ?? 0, 1),
                    'avg_formatted' => $this->formatDuration($igFrt->avg_frt ?? 0),
                ];

                // Resolution time
                $igConversations = Conversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->where('status', 'resolved')
                    ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_resolution')
                    ->value('avg_resolution');

                $result['resolution_time']['by_platform']['instagram'] = [
                    'avg_seconds' => round($igConversations ?? 0, 1),
                    'avg_formatted' => $this->formatDuration($igConversations ?? 0),
                ];
            }

            // Calculate overall averages
            $platforms = array_keys($result['first_response_time']['by_platform']);
            $platformCount = count($platforms);

            if ($platformCount > 0) {
                $totalFrt = array_sum(array_column($result['first_response_time']['by_platform'], 'avg_seconds'));
                $totalArt = array_sum(array_column($result['average_response_time']['by_platform'], 'avg_seconds'));
                $totalResolution = array_sum(array_column($result['resolution_time']['by_platform'], 'avg_seconds'));

                $result['first_response_time']['avg_seconds'] = round($totalFrt / $platformCount, 1);
                $result['first_response_time']['avg_formatted'] = $this->formatDuration($totalFrt / $platformCount);

                $result['average_response_time']['avg_seconds'] = round($totalArt / $platformCount, 1);
                $result['average_response_time']['avg_formatted'] = $this->formatDuration($totalArt / $platformCount);

                $result['resolution_time']['avg_seconds'] = round($totalResolution / $platformCount, 1);
                $result['resolution_time']['avg_formatted'] = $this->formatDuration($totalResolution / $platformCount);
            }

            // Hourly distribution for heatmap
            $result['hourly_distribution'] = $this->getHourlyDistribution($user->id, $platform, $since);

            return $result;
        });

        return response()->json([
            'ok' => true,
            'metrics' => $metrics,
            'period' => [
                'days' => $days,
                'from' => $since->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get escalation rate (bot to human handoff).
     */
    public function getEscalationRate(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);
        $platform = $request->input('platform', 'all');
        $since = now()->subDays($days);

        $cacheKey = "escalation_rate_{$user->id}_{$platform}_{$days}";

        $data = Cache::remember($cacheKey, 300, function () use ($user, $platform, $since) {
            $result = [
                'total_conversations' => 0,
                'bot_handled' => 0,
                'escalated' => 0,
                'escalation_rate' => 0,
                'bot_resolution_rate' => 0,
                'by_platform' => [],
                'top_escalation_reasons' => [],
                'trend' => [],
            ];

            // WhatsApp escalation data
            if ($platform === 'all' || $platform === 'whatsapp') {
                $waTotal = WaConversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->count();

                $waBotHandled = WaConversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->where('status', WaConversation::STATUS_BOT_ACTIVE)
                    ->count();

                $waEscalated = WaConversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->where('status', WaConversation::STATUS_AGENT_HANDLING)
                    ->count();

                $result['by_platform']['whatsapp'] = [
                    'total' => $waTotal,
                    'bot_handled' => $waBotHandled,
                    'escalated' => $waEscalated,
                    'escalation_rate' => $waTotal > 0 ? round(($waEscalated / $waTotal) * 100, 1) : 0,
                    'bot_resolution_rate' => $waTotal > 0 ? round(($waBotHandled / $waTotal) * 100, 1) : 0,
                ];

                $result['total_conversations'] += $waTotal;
                $result['bot_handled'] += $waBotHandled;
                $result['escalated'] += $waEscalated;
            }

            // Instagram escalation data
            if ($platform === 'all' || $platform === 'instagram') {
                $igTotal = Conversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->count();

                $igBotHandled = Conversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->where('status', 'bot_handling')
                    ->count();

                $igEscalated = Conversation::where('user_id', $user->id)
                    ->where('created_at', '>=', $since)
                    ->where('status', 'agent_handling')
                    ->count();

                $result['by_platform']['instagram'] = [
                    'total' => $igTotal,
                    'bot_handled' => $igBotHandled,
                    'escalated' => $igEscalated,
                    'escalation_rate' => $igTotal > 0 ? round(($igEscalated / $igTotal) * 100, 1) : 0,
                    'bot_resolution_rate' => $igTotal > 0 ? round(($igBotHandled / $igTotal) * 100, 1) : 0,
                ];

                $result['total_conversations'] += $igTotal;
                $result['bot_handled'] += $igBotHandled;
                $result['escalated'] += $igEscalated;
            }

            // Calculate overall rates
            if ($result['total_conversations'] > 0) {
                $result['escalation_rate'] = round(
                    ($result['escalated'] / $result['total_conversations']) * 100,
                    1
                );
                $result['bot_resolution_rate'] = round(
                    ($result['bot_handled'] / $result['total_conversations']) * 100,
                    1
                );
            }

            // Daily trend
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dayData = [
                    'date' => $date->format('Y-m-d'),
                    'total' => 0,
                    'escalated' => 0,
                    'rate' => 0,
                ];

                if ($platform === 'all' || $platform === 'whatsapp') {
                    $waDaily = WaConversation::where('user_id', $user->id)
                        ->whereDate('created_at', $date)
                        ->count();

                    $waDailyEscalated = WaConversation::where('user_id', $user->id)
                        ->whereDate('created_at', $date)
                        ->where('status', WaConversation::STATUS_AGENT_HANDLING)
                        ->count();

                    $dayData['total'] += $waDaily;
                    $dayData['escalated'] += $waDailyEscalated;
                }

                if ($platform === 'all' || $platform === 'instagram') {
                    $igDaily = Conversation::where('user_id', $user->id)
                        ->whereDate('created_at', $date)
                        ->count();

                    $igDailyEscalated = Conversation::where('user_id', $user->id)
                        ->whereDate('created_at', $date)
                        ->where('status', 'agent_handling')
                        ->count();

                    $dayData['total'] += $igDaily;
                    $dayData['escalated'] += $igDailyEscalated;
                }

                $dayData['rate'] = $dayData['total'] > 0 
                    ? round(($dayData['escalated'] / $dayData['total']) * 100, 1) 
                    : 0;

                $result['trend'][] = $dayData;
            }

            return $result;
        });

        return response()->json([
            'ok' => true,
            'data' => $data,
            'period' => [
                'days' => $days,
                'from' => $since->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get conversation quality scores.
     */
    public function getConversationScores(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);
        $platform = $request->input('platform', 'all');
        $since = now()->subDays($days);

        $cacheKey = "conversation_scores_{$user->id}_{$platform}_{$days}";

        $scores = Cache::remember($cacheKey, 300, function () use ($user, $platform, $since) {
            $result = [
                'overall_score' => 0,
                'csat_score' => 0,
                'response_quality' => 0,
                'resolution_quality' => 0,
                'sentiment_quality' => 0,
                'by_platform' => [],
                'components' => [],
            ];

            // CSAT Score calculation
            $csatData = CsatRating::where('user_id', $user->id)
                ->whereNotNull('rating')
                ->where('created_at', '>=', $since);

            if ($platform !== 'all') {
                $csatData->where('platform', $platform);
            }

            $avgCsat = $csatData->avg('rating') ?? 0;
            $csatCount = $csatData->count();

            // Normalize CSAT to 0-100 scale
            $result['csat_score'] = round(($avgCsat / 5) * 100, 1);

            // Response quality based on response times
            $responseMetrics = $this->getResponseTimeMetrics($request)->getData()->metrics;
            $frtSeconds = $responseMetrics->first_response_time->avg_seconds ?? 0;

            // Score: <30s = 100, 30-60s = 80, 60-120s = 60, 120-300s = 40, >300s = 20
            if ($frtSeconds < 30) {
                $responseScore = 100;
            } elseif ($frtSeconds < 60) {
                $responseScore = 80;
            } elseif ($frtSeconds < 120) {
                $responseScore = 60;
            } elseif ($frtSeconds < 300) {
                $responseScore = 40;
            } else {
                $responseScore = 20;
            }
            $result['response_quality'] = $responseScore;

            // Resolution quality based on bot resolution rate
            $escalationData = $this->getEscalationRate($request)->getData()->data;
            $botResolutionRate = $escalationData->bot_resolution_rate ?? 0;
            $result['resolution_quality'] = round($botResolutionRate, 1);

            // Sentiment quality (positive sentiment percentage)
            $sentimentData = $this->getSentimentAnalysis($request)->getData()->analysis;
            $result['sentiment_quality'] = $sentimentData->percentages->positive ?? 0;

            // Calculate overall score (weighted average)
            $result['overall_score'] = round(
                ($result['csat_score'] * 0.3) +
                ($result['response_quality'] * 0.25) +
                ($result['resolution_quality'] * 0.25) +
                ($result['sentiment_quality'] * 0.2),
                1
            );

            // Score components
            $result['components'] = [
                [
                    'name' => 'Customer Satisfaction',
                    'key' => 'csat',
                    'score' => $result['csat_score'],
                    'weight' => 30,
                    'details' => [
                        'avg_rating' => round($avgCsat, 1),
                        'total_responses' => $csatCount,
                    ],
                ],
                [
                    'name' => 'Response Time',
                    'key' => 'response',
                    'score' => $result['response_quality'],
                    'weight' => 25,
                    'details' => [
                        'avg_frt_seconds' => round($frtSeconds, 1),
                        'avg_frt_formatted' => $responseMetrics->first_response_time->avg_formatted ?? '0s',
                    ],
                ],
                [
                    'name' => 'Resolution Rate',
                    'key' => 'resolution',
                    'score' => $result['resolution_quality'],
                    'weight' => 25,
                    'details' => [
                        'bot_resolution_rate' => $botResolutionRate . '%',
                        'escalation_rate' => ($escalationData->escalation_rate ?? 0) . '%',
                    ],
                ],
                [
                    'name' => 'Positive Sentiment',
                    'key' => 'sentiment',
                    'score' => $result['sentiment_quality'],
                    'weight' => 20,
                    'details' => [
                        'positive_percentage' => ($sentimentData->percentages->positive ?? 0) . '%',
                        'sentiment_score' => $sentimentData->score ?? 0,
                    ],
                ],
            ];

            // Platform breakdown
            if ($platform === 'all' || $platform === 'whatsapp') {
                $result['by_platform']['whatsapp'] = $this->calculatePlatformScore($user->id, 'whatsapp', $since);
            }
            if ($platform === 'all' || $platform === 'instagram') {
                $result['by_platform']['instagram'] = $this->calculatePlatformScore($user->id, 'instagram', $since);
            }

            return $result;
        });

        return response()->json([
            'ok' => true,
            'scores' => $scores,
            'rating' => $this->getScoreRating($scores['overall_score'] ?? 0),
            'period' => [
                'days' => $days,
                'from' => $since->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get quality overview (summary of all metrics).
     */
    public function getOverview(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);

        $sentiment = $this->getSentimentAnalysis($request)->getData()->analysis;
        $responseMetrics = $this->getResponseTimeMetrics($request)->getData()->metrics;
        $escalationData = $this->getEscalationRate($request)->getData()->data;
        $scores = $this->getConversationScores($request)->getData()->scores;

        return response()->json([
            'ok' => true,
            'overview' => [
                'quality_score' => $scores->overall_score ?? 0,
                'sentiment_score' => $sentiment->score ?? 0,
                'response_time' => $responseMetrics->first_response_time->avg_formatted ?? '0s',
                'bot_resolution_rate' => $escalationData->bot_resolution_rate ?? 0,
                'escalation_rate' => $escalationData->escalation_rate ?? 0,
            ],
            'period' => [
                'days' => $days,
            ],
        ]);
    }

    /**
     * Format duration in seconds to human readable string.
     */
    protected function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . 's';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . 'm';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = round(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    /**
     * Get hourly distribution for heatmap.
     */
    protected function getHourlyDistribution(int $userId, string $platform, Carbon $since): array
    {
        $distribution = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $distribution[$hour] = [
                'hour' => sprintf('%02d:00', $hour),
                'count' => 0,
                'avg_response_time' => 0,
            ];
        }

        if ($platform === 'all' || $platform === 'whatsapp') {
            $waHourly = WaMessage::where('user_id', $userId)
                ->where('direction', 'incoming')
                ->whereNotNull('bot_reply')
                ->where('created_at', '>=', $since)
                ->selectRaw('HOUR(created_at) as hour')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_response')
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->get();

            foreach ($waHourly as $row) {
                $distribution[$row->hour]['count'] += $row->count;
                $distribution[$row->hour]['avg_response_time'] = round($row->avg_response, 1);
            }
        }

        return $distribution;
    }

    /**
     * Calculate platform-specific quality score.
     */
    protected function calculatePlatformScore(int $userId, string $platform, Carbon $since): array
    {
        if ($platform === 'whatsapp') {
            $avgCsat = CsatRating::where('user_id', $userId)
                ->where('platform', 'whatsapp')
                ->whereNotNull('rating')
                ->where('created_at', '>=', $since)
                ->avg('rating') ?? 0;

            $frt = WaMessage::where('user_id', $userId)
                ->where('direction', 'incoming')
                ->whereNotNull('bot_reply')
                ->where('created_at', '>=', $since)
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_frt')
                ->value('avg_frt') ?? 0;

            $total = WaConversation::where('user_id', $userId)
                ->where('created_at', '>=', $since)
                ->count();

            $botHandled = WaConversation::where('user_id', $userId)
                ->where('created_at', '>=', $since)
                ->where('status', WaConversation::STATUS_BOT_ACTIVE)
                ->count();

            $positiveSentiment = WaConversation::where('user_id', $userId)
                ->where('ai_sentiment', 'positive')
                ->where('ai_analyzed_at', '>=', $since)
                ->count();

            $totalAnalyzed = WaConversation::where('user_id', $userId)
                ->whereNotNull('ai_sentiment')
                ->where('ai_analyzed_at', '>=', $since)
                ->count();
        } else {
            $avgCsat = CsatRating::where('user_id', $userId)
                ->where('platform', 'instagram')
                ->whereNotNull('rating')
                ->where('created_at', '>=', $since)
                ->avg('rating') ?? 0;

            $conversationIds = Conversation::where('user_id', $userId)
                ->where('created_at', '>=', $since)
                ->pluck('id');

            $frt = AutoReplyLog::whereIn('conversation_id', $conversationIds)
                ->whereIn('status', ['sent', 'sent_ai', 'success'])
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_frt')
                ->value('avg_frt') ?? 0;

            $total = Conversation::where('user_id', $userId)
                ->where('created_at', '>=', $since)
                ->count();

            $botHandled = Conversation::where('user_id', $userId)
                ->where('created_at', '>=', $since)
                ->where('status', 'bot_handling')
                ->count();

            $positiveSentiment = Conversation::where('user_id', $userId)
                ->where('ai_sentiment', 'positive')
                ->where('ai_analyzed_at', '>=', $since)
                ->count();

            $totalAnalyzed = Conversation::where('user_id', $userId)
                ->whereNotNull('ai_sentiment')
                ->where('ai_analyzed_at', '>=', $since)
                ->count();
        }

        // Calculate individual scores
        $csatScore = round(($avgCsat / 5) * 100, 1);

        if ($frt < 30) {
            $responseScore = 100;
        } elseif ($frt < 60) {
            $responseScore = 80;
        } elseif ($frt < 120) {
            $responseScore = 60;
        } elseif ($frt < 300) {
            $responseScore = 40;
        } else {
            $responseScore = 20;
        }

        $resolutionScore = $total > 0 ? round(($botHandled / $total) * 100, 1) : 0;
        $sentimentScore = $totalAnalyzed > 0 ? round(($positiveSentiment / $totalAnalyzed) * 100, 1) : 0;

        $overallScore = round(
            ($csatScore * 0.3) +
            ($responseScore * 0.25) +
            ($resolutionScore * 0.25) +
            ($sentimentScore * 0.2),
            1
        );

        return [
            'score' => $overallScore,
            'rating' => $this->getScoreRating($overallScore),
            'components' => [
                'csat' => $csatScore,
                'response' => $responseScore,
                'resolution' => $resolutionScore,
                'sentiment' => $sentimentScore,
            ],
        ];
    }

    /**
     * Get rating label for score.
     */
    protected function getScoreRating(float $score): string
    {
        if ($score >= 90) {
            return 'excellent';
        } elseif ($score >= 75) {
            return 'good';
        } elseif ($score >= 60) {
            return 'average';
        } elseif ($score >= 40) {
            return 'below_average';
        } else {
            return 'poor';
        }
    }
}
