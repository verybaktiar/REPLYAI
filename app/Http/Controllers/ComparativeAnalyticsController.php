<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\WaMessage;
use App\Models\WaConversation;
use App\Models\AutoReplyLog;
use App\Models\Conversation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * Comparative Analytics Controller
 * 
 * Provides comparison analytics between different time periods
 * including week-over-week, month-over-month, and year-over-year analysis.
 */
class ComparativeAnalyticsController extends Controller
{
    /**
     * Display the comparative analytics view
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.reports.comparative.index');
    }

    /**
     * Compare metrics between two custom time periods
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function comparePeriods(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate request parameters
        $validated = $request->validate([
            'period1_start' => 'required|date',
            'period1_end' => 'required|date|after_or_equal:period1_start',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date|after_or_equal:period2_start',
        ]);

        $period1Start = Carbon::parse($validated['period1_start'])->startOfDay();
        $period1End = Carbon::parse($validated['period1_end'])->endOfDay();
        $period2Start = Carbon::parse($validated['period2_start'])->startOfDay();
        $period2End = Carbon::parse($validated['period2_end'])->endOfDay();

        // Get metrics for both periods
        $period1Metrics = $this->calculateMetrics($period1Start, $period1End);
        $period2Metrics = $this->calculateMetrics($period2Start, $period2End);

        // Calculate changes
        $changes = $this->calculateChanges($period1Metrics, $period2Metrics);

        return response()->json([
            'period1' => [
                'start_date' => $period1Start->format('Y-m-d'),
                'end_date' => $period1End->format('Y-m-d'),
                'metrics' => $period1Metrics,
            ],
            'period2' => [
                'start_date' => $period2Start->format('Y-m-d'),
                'end_date' => $period2End->format('Y-m-d'),
                'metrics' => $period2Metrics,
            ],
            'changes' => $changes,
        ]);
    }

    /**
     * Get week-over-week comparison (last 7 days vs previous 7 days)
     * 
     * @return JsonResponse
     */
    public function getWeekOverWeek(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Current week (last 7 days)
        $currentWeekStart = Carbon::now()->subDays(6)->startOfDay();
        $currentWeekEnd = Carbon::now()->endOfDay();

        // Previous week (7-14 days ago)
        $previousWeekStart = Carbon::now()->subDays(13)->startOfDay();
        $previousWeekEnd = Carbon::now()->subDays(7)->endOfDay();

        $currentWeekMetrics = $this->calculateMetrics($currentWeekStart, $currentWeekEnd);
        $previousWeekMetrics = $this->calculateMetrics($previousWeekStart, $previousWeekEnd);
        $changes = $this->calculateChanges($previousWeekMetrics, $currentWeekMetrics);

        return response()->json([
            'current_week' => [
                'start_date' => $currentWeekStart->format('Y-m-d'),
                'end_date' => $currentWeekEnd->format('Y-m-d'),
                'metrics' => $currentWeekMetrics,
            ],
            'previous_week' => [
                'start_date' => $previousWeekStart->format('Y-m-d'),
                'end_date' => $previousWeekEnd->format('Y-m-d'),
                'metrics' => $previousWeekMetrics,
            ],
            'changes' => $changes,
            'trend' => $this->determineTrend($changes),
        ]);
    }

    /**
     * Get month-over-month comparison (this month vs last month)
     * 
     * @return JsonResponse
     */
    public function getMonthOverMonth(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Current month
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        // Previous month
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $currentMonthMetrics = $this->calculateMetrics($currentMonthStart, $currentMonthEnd);
        $previousMonthMetrics = $this->calculateMetrics($previousMonthStart, $previousMonthEnd);
        $changes = $this->calculateChanges($previousMonthMetrics, $currentMonthMetrics);

        // Get daily breakdown for trend visualization
        $dailyBreakdown = $this->getDailyBreakdown($currentMonthStart, $currentMonthEnd);

        return response()->json([
            'current_month' => [
                'start_date' => $currentMonthStart->format('Y-m-d'),
                'end_date' => $currentMonthEnd->format('Y-m-d'),
                'metrics' => $currentMonthMetrics,
            ],
            'previous_month' => [
                'start_date' => $previousMonthStart->format('Y-m-d'),
                'end_date' => $previousMonthEnd->format('Y-m-d'),
                'metrics' => $previousMonthMetrics,
            ],
            'changes' => $changes,
            'trend' => $this->determineTrend($changes),
            'daily_breakdown' => $dailyBreakdown,
        ]);
    }

    /**
     * Get year-over-year comparison (this year vs last year)
     * 
     * @return JsonResponse
     */
    public function getYearOverYear(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Current year
        $currentYearStart = Carbon::now()->startOfYear();
        $currentYearEnd = Carbon::now()->endOfYear();

        // Previous year
        $previousYearStart = Carbon::now()->subYear()->startOfYear();
        $previousYearEnd = Carbon::now()->subYear()->endOfYear();

        $currentYearMetrics = $this->calculateMetrics($currentYearStart, $currentYearEnd);
        $previousYearMetrics = $this->calculateMetrics($previousYearStart, $previousYearEnd);
        $changes = $this->calculateChanges($previousYearMetrics, $currentYearMetrics);

        // Get monthly breakdown
        $monthlyBreakdown = $this->getMonthlyBreakdown($currentYearStart, $currentYearEnd);

        return response()->json([
            'current_year' => [
                'start_date' => $currentYearStart->format('Y-m-d'),
                'end_date' => $currentYearEnd->format('Y-m-d'),
                'metrics' => $currentYearMetrics,
            ],
            'previous_year' => [
                'start_date' => $previousYearStart->format('Y-m-d'),
                'end_date' => $previousYearEnd->format('Y-m-d'),
                'metrics' => $previousYearMetrics,
            ],
            'changes' => $changes,
            'trend' => $this->determineTrend($changes),
            'monthly_breakdown' => $monthlyBreakdown,
        ]);
    }

    /**
     * Get benchmark data comparing user performance against industry standards
     * 
     * @return JsonResponse
     */
    public function getBenchmarkData(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get last 30 days metrics for comparison
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $userMetrics = $this->calculateMetrics($startDate, $endDate);

        // Industry benchmarks (mock data)
        $benchmarks = [
            'total_conversations' => [
                'user_value' => $userMetrics['total_conversations'],
                'industry_avg' => 500,
                'industry_top' => 2000,
                'percentile' => $this->calculatePercentile($userMetrics['total_conversations'], 500, 2000),
            ],
            'bot_resolution_rate' => [
                'user_value' => $userMetrics['bot_resolution_rate'],
                'industry_avg' => 65.0,
                'industry_top' => 85.0,
                'percentile' => $this->calculatePercentile($userMetrics['bot_resolution_rate'], 65.0, 85.0),
            ],
            'avg_response_time' => [
                'user_value' => $userMetrics['avg_response_time'],
                'industry_avg' => 45.0,
                'industry_top' => 15.0,
                'percentile' => $this->calculateInversePercentile($userMetrics['avg_response_time'], 45.0, 15.0),
            ],
            'csat_score' => [
                'user_value' => $userMetrics['csat_score'],
                'industry_avg' => 3.8,
                'industry_top' => 4.5,
                'percentile' => $this->calculatePercentile($userMetrics['csat_score'], 3.8, 4.5),
            ],
            'handoff_rate' => [
                'user_value' => $userMetrics['handoff_rate'],
                'industry_avg' => 25.0,
                'industry_top' => 10.0,
                'percentile' => $this->calculateInversePercentile($userMetrics['handoff_rate'], 25.0, 10.0),
            ],
            'message_volume' => [
                'user_value' => $userMetrics['message_volume'],
                'industry_avg' => 1500,
                'industry_top' => 5000,
                'percentile' => $this->calculatePercentile($userMetrics['message_volume'], 1500, 5000),
            ],
        ];

        // Overall performance score
        $overallScore = collect($benchmarks)->avg('percentile');

        return response()->json([
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => 30,
            ],
            'benchmarks' => $benchmarks,
            'overall_score' => round($overallScore, 1),
            'performance_rating' => $this->getPerformanceRating($overallScore),
        ]);
    }

    /**
     * Get trend analysis with moving averages and forecasting
     * 
     * @return JsonResponse
     */
    public function getTrendAnalysis(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get last 30 days of daily data
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(29)->startOfDay();

        $dailyData = $this->getDailyMetrics($startDate, $endDate);

        // Calculate moving averages
        $movingAverages = $this->calculateMovingAverages($dailyData);

        // Detect trends
        $trends = $this->detectTrends($dailyData);

        // Generate forecast
        $forecast = $this->generateForecast($dailyData);

        return response()->json([
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'daily_data' => $dailyData,
            'moving_averages' => $movingAverages,
            'trends' => $trends,
            'forecast' => $forecast,
        ]);
    }

    /**
     * Calculate all metrics for a given time period
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function calculateMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $userId = auth()->id();

        // Total conversations (WhatsApp + Instagram)
        $waConversations = WaConversation::whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $igConversationIds = Conversation::whereBetween('created_at', [$startDate, $endDate])
            ->pluck('id');
        $totalConversations = $waConversations + $igConversationIds->count();

        // Message volume
        $waMessages = WaMessage::whereBetween('created_at', [$startDate, $endDate])
            ->where('user_id', $userId);
        $waIncoming = (clone $waMessages)->where('direction', 'incoming')->count();
        $waOutgoing = (clone $waMessages)->where('direction', 'outgoing')->count();

        $igMessages = 0;
        if ($igConversationIds->isNotEmpty()) {
            $igMessages = \App\Models\Message::whereIn('conversation_id', $igConversationIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
        }

        $messageVolume = $waIncoming + $waOutgoing + $igMessages;

        // Bot resolution rate
        $waBotResolved = (clone $waMessages)
            ->where('direction', 'incoming')
            ->whereNotNull('bot_reply')
            ->where('bot_reply', '!=', '')
            ->count();

        $igBotResolved = 0;
        if ($igConversationIds->isNotEmpty()) {
            $igBotResolved = AutoReplyLog::whereIn('conversation_id', $igConversationIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['sent', 'sent_ai', 'success'])
                ->count();
        }

        $totalIncoming = $waIncoming + $igMessages;
        $botResolutionRate = $totalIncoming > 0 
            ? round((($waBotResolved + $igBotResolved) / $totalIncoming) * 100, 1) 
            : 0;

        // Handoff rate
        $handoffCount = WaConversation::where('status', WaConversation::STATUS_AGENT_HANDLING)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $handoffRate = $totalConversations > 0 
            ? round(($handoffCount / $totalConversations) * 100, 1) 
            : 0;

        // Average response time
        $avgResponseTime = $this->calculateAvgResponseTime($startDate, $endDate);

        // CSAT score (mock calculation - in real scenario would come from feedback table)
        $csatScore = $this->calculateCsatScore($startDate, $endDate);

        return [
            'total_conversations' => $totalConversations,
            'message_volume' => $messageVolume,
            'incoming_messages' => $waIncoming + $igMessages,
            'outgoing_messages' => $waOutgoing,
            'bot_resolution_rate' => $botResolutionRate,
            'handoff_rate' => $handoffRate,
            'avg_response_time' => $avgResponseTime,
            'csat_score' => $csatScore,
        ];
    }

    /**
     * Calculate percentage changes between two sets of metrics
     * 
     * @param array $baseline
     * @param array $current
     * @return array
     */
    private function calculateChanges(array $baseline, array $current): array
    {
        $changes = [];
        $metrics = [
            'total_conversations',
            'message_volume',
            'incoming_messages',
            'outgoing_messages',
            'bot_resolution_rate',
            'handoff_rate',
            'avg_response_time',
            'csat_score',
        ];

        foreach ($metrics as $metric) {
            $baselineValue = $baseline[$metric] ?? 0;
            $currentValue = $current[$metric] ?? 0;

            if ($baselineValue > 0) {
                $percentageChange = round((($currentValue - $baselineValue) / $baselineValue) * 100, 1);
            } elseif ($currentValue > 0) {
                $percentageChange = 100;
            } else {
                $percentageChange = 0;
            }

            $changes[$metric] = [
                'absolute' => $currentValue - $baselineValue,
                'percentage' => $percentageChange,
                'direction' => $percentageChange > 0 ? 'up' : ($percentageChange < 0 ? 'down' : 'stable'),
            ];
        }

        return $changes;
    }

    /**
     * Calculate average response time for a period
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculateAvgResponseTime(Carbon $startDate, Carbon $endDate): float
    {
        $userId = auth()->id();

        $messages = WaMessage::whereBetween('created_at', [$startDate, $endDate])
            ->where('user_id', $userId)
            ->orderBy('phone_number')
            ->orderBy('created_at')
            ->get(['phone_number', 'direction', 'created_at']);

        $responseTimes = [];
        $lastIncoming = [];

        foreach ($messages as $msg) {
            if ($msg->direction === 'incoming') {
                $lastIncoming[$msg->phone_number] = $msg->created_at;
            } elseif ($msg->direction === 'outgoing' && isset($lastIncoming[$msg->phone_number])) {
                $diff = abs($msg->created_at->diffInSeconds($lastIncoming[$msg->phone_number]));
                if ($diff < 300) { // Only count if response within 5 minutes
                    $responseTimes[] = $diff;
                }
                unset($lastIncoming[$msg->phone_number]);
            }
        }

        return count($responseTimes) > 0 
            ? round(array_sum($responseTimes) / count($responseTimes), 1) 
            : 0;
    }

    /**
     * Calculate CSAT score
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculateCsatScore(Carbon $startDate, Carbon $endDate): float
    {
        // Mock CSAT calculation - in real implementation, would query feedback/satisfaction table
        // For now, estimate based on bot resolution rate
        $userId = auth()->id();
        
        $totalMessages = WaMessage::whereBetween('created_at', [$startDate, $endDate])
            ->where('user_id', $userId)
            ->where('direction', 'incoming')
            ->count();

        if ($totalMessages === 0) {
            return 0;
        }

        $resolved = WaMessage::whereBetween('created_at', [$startDate, $endDate])
            ->where('user_id', $userId)
            ->where('direction', 'incoming')
            ->whereNotNull('bot_reply')
            ->where('bot_reply', '!=', '')
            ->count();

        // Estimate CSAT based on resolution rate (0-5 scale)
        $resolutionRate = $resolved / $totalMessages;
        $estimatedCsat = min(5, max(1, 2.5 + ($resolutionRate * 2.5)));

        return round($estimatedCsat, 1);
    }

    /**
     * Determine overall trend based on changes
     * 
     * @param array $changes
     * @return array
     */
    private function determineTrend(array $changes): array
    {
        $positiveMetrics = ['total_conversations', 'message_volume', 'bot_resolution_rate', 'csat_score'];
        $negativeMetrics = ['handoff_rate', 'avg_response_time'];

        $positiveChanges = 0;
        $negativeChanges = 0;

        foreach ($positiveMetrics as $metric) {
            if (isset($changes[$metric])) {
                if ($changes[$metric]['direction'] === 'up') {
                    $positiveChanges++;
                } elseif ($changes[$metric]['direction'] === 'down') {
                    $negativeChanges++;
                }
            }
        }

        foreach ($negativeMetrics as $metric) {
            if (isset($changes[$metric])) {
                if ($changes[$metric]['direction'] === 'down') {
                    $positiveChanges++;
                } elseif ($changes[$metric]['direction'] === 'up') {
                    $negativeChanges++;
                }
            }
        }

        $totalChanges = $positiveChanges + $negativeChanges;
        if ($totalChanges === 0) {
            return ['direction' => 'stable', 'strength' => 'neutral'];
        }

        $positiveRatio = $positiveChanges / $totalChanges;

        if ($positiveRatio >= 0.7) {
            return ['direction' => 'upward', 'strength' => 'strong'];
        } elseif ($positiveRatio >= 0.5) {
            return ['direction' => 'upward', 'strength' => 'moderate'];
        } elseif ($positiveRatio >= 0.3) {
            return ['direction' => 'downward', 'strength' => 'moderate'];
        } else {
            return ['direction' => 'downward', 'strength' => 'strong'];
        }
    }

    /**
     * Get daily breakdown of metrics
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getDailyBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $breakdown = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();
            $metrics = $this->calculateMetrics($dayStart, $dayEnd);

            $breakdown[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('D'),
                'metrics' => $metrics,
            ];
        }

        return $breakdown;
    }

    /**
     * Get monthly breakdown of metrics
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getMonthlyBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $breakdown = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            $metrics = $this->calculateMetrics($monthStart, $monthEnd);

            $breakdown[] = [
                'month' => $current->format('Y-m'),
                'month_name' => $current->format('F'),
                'metrics' => $metrics,
            ];

            $current->addMonth();
        }

        return $breakdown;
    }

    /**
     * Get daily metrics for trend analysis
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getDailyMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $data = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $userId = auth()->id();

            // Get key metrics for the day
            $conversations = WaConversation::whereBetween('created_at', [$dayStart, $dayEnd])->count();
            $messages = WaMessage::whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('user_id', $userId)
                ->count();
            $incoming = WaMessage::whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('user_id', $userId)
                ->where('direction', 'incoming')
                ->count();
            $botResolved = WaMessage::whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('user_id', $userId)
                ->where('direction', 'incoming')
                ->whereNotNull('bot_reply')
                ->where('bot_reply', '!=', '')
                ->count();

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'conversations' => $conversations,
                'messages' => $messages,
                'incoming' => $incoming,
                'bot_resolved' => $botResolved,
                'resolution_rate' => $incoming > 0 ? round(($botResolved / $incoming) * 100, 1) : 0,
            ];
        }

        return $data;
    }

    /**
     * Calculate moving averages
     * 
     * @param array $dailyData
     * @return array
     */
    private function calculateMovingAverages(array $dailyData): array
    {
        $ma3 = [];
        $ma7 = [];

        for ($i = 0; $i < count($dailyData); $i++) {
            // 3-day moving average
            if ($i >= 2) {
                $sum = $dailyData[$i]['conversations'] + $dailyData[$i - 1]['conversations'] + $dailyData[$i - 2]['conversations'];
                $ma3[] = [
                    'date' => $dailyData[$i]['date'],
                    'value' => round($sum / 3, 1),
                ];
            }

            // 7-day moving average
            if ($i >= 6) {
                $sum = 0;
                for ($j = 0; $j < 7; $j++) {
                    $sum += $dailyData[$i - $j]['conversations'];
                }
                $ma7[] = [
                    'date' => $dailyData[$i]['date'],
                    'value' => round($sum / 7, 1),
                ];
            }
        }

        return [
            'ma3' => $ma3,
            'ma7' => $ma7,
        ];
    }

    /**
     * Detect trends in data
     * 
     * @param array $dailyData
     * @return array
     */
    private function detectTrends(array $dailyData): array
    {
        if (count($dailyData) < 7) {
            return ['status' => 'insufficient_data'];
        }

        // Get first and second half averages
        $half = (int) ceil(count($dailyData) / 2);
        $firstHalf = array_slice($dailyData, 0, $half);
        $secondHalf = array_slice($dailyData, $half);

        $firstAvg = collect($firstHalf)->avg('conversations');
        $secondAvg = collect($secondHalf)->avg('conversations');

        $change = $firstAvg > 0 ? (($secondAvg - $firstAvg) / $firstAvg) * 100 : 0;

        return [
            'status' => 'analyzed',
            'direction' => $change > 5 ? 'upward' : ($change < -5 ? 'downward' : 'stable'),
            'change_percentage' => round($change, 1),
            'first_half_avg' => round($firstAvg, 1),
            'second_half_avg' => round($secondAvg, 1),
        ];
    }

    /**
     * Generate simple linear forecast
     * 
     * @param array $dailyData
     * @param int $days
     * @return array
     */
    private function generateForecast(array $dailyData, int $days = 7): array
    {
        if (count($dailyData) < 3) {
            return ['status' => 'insufficient_data'];
        }

        // Simple linear regression
        $n = count($dailyData);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($dailyData as $i => $data) {
            $x = $i;
            $y = $data['conversations'];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if ($denominator == 0) {
            $slope = 0;
        } else {
            $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        }
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        // Generate forecast
        $forecast = [];
        $lastDate = Carbon::parse($dailyData[count($dailyData) - 1]['date']);

        for ($i = 1; $i <= $days; $i++) {
            $x = $n + $i - 1;
            $predictedValue = max(0, round($slope * $x + $intercept, 1));

            $forecast[] = [
                'date' => $lastDate->copy()->addDays($i)->format('Y-m-d'),
                'predicted_conversations' => $predictedValue,
                'confidence' => 'low', // Simple forecast has lower confidence
            ];
        }

        return [
            'status' => 'generated',
            'method' => 'linear_regression',
            'slope' => round($slope, 2),
            'forecast' => $forecast,
        ];
    }

    /**
     * Calculate percentile for benchmark comparison
     * 
     * @param float $value
     * @param float $avg
     * @param float $top
     * @return float
     */
    private function calculatePercentile(float $value, float $avg, float $top): float
    {
        if ($value <= 0) {
            return 0;
        }
        if ($value >= $top) {
            return 100;
        }
        if ($value <= $avg) {
            return round(($value / $avg) * 50, 1);
        }
        return round(50 + (($value - $avg) / ($top - $avg)) * 50, 1);
    }

    /**
     * Calculate inverse percentile (for metrics where lower is better)
     * 
     * @param float $value
     * @param float $avg
     * @param float $top
     * @return float
     */
    private function calculateInversePercentile(float $value, float $avg, float $top): float
    {
        if ($value <= $top) {
            return 100;
        }
        if ($value >= $avg) {
            return max(0, round((1 - (($value - $avg) / ($avg - $top))) * 50, 1));
        }
        return round(50 + ((1 - ($value / $avg)) * 50), 1);
    }

    /**
     * Get performance rating based on overall score
     * 
     * @param float $score
     * @return string
     */
    private function getPerformanceRating(float $score): string
    {
        if ($score >= 90) {
            return 'exceptional';
        } elseif ($score >= 75) {
            return 'excellent';
        } elseif ($score >= 60) {
            return 'good';
        } elseif ($score >= 40) {
            return 'average';
        } elseif ($score >= 25) {
            return 'below_average';
        }
        return 'needs_improvement';
    }
}
