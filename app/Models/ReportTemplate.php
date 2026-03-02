<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;

class ReportTemplate extends Model
{
    use BelongsToUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'metrics',
        'filters',
        'chart_type',
        'is_default',
        'is_shared',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metrics' => 'array',
        'filters' => 'array',
        'is_default' => 'boolean',
        'is_shared' => 'boolean',
    ];

    /**
     * Chart type constants.
     */
    const CHART_LINE = 'line';
    const CHART_BAR = 'bar';
    const CHART_PIE = 'pie';
    const CHART_HEATMAP = 'heatmap';
    const CHART_MIXED = 'mixed';

    /**
     * Available metrics.
     */
    const METRIC_TOTAL_CONVERSATIONS = 'total_conversations';
    const METRIC_TOTAL_MESSAGES = 'total_messages';
    const METRIC_BOT_RESOLUTION_RATE = 'bot_resolution_rate';
    const METRIC_AVG_RESPONSE_TIME = 'avg_response_time';
    const METRIC_FIRST_RESPONSE_TIME = 'first_response_time';
    const METRIC_ESCALATION_RATE = 'escalation_rate';
    const METRIC_CSAT_SCORE = 'csrf_score';
    const METRIC_SENTIMENT_POSITIVE = 'sentiment_positive';
    const METRIC_SENTIMENT_NEGATIVE = 'sentiment_negative';
    const METRIC_SENTIMENT_NEUTRAL = 'sentiment_neutral';
    const METRIC_PEAK_HOURS = 'peak_hours';
    const METRIC_AGENT_PERFORMANCE = 'agent_performance';

    /**
     * Get available chart types.
     */
    public static function getChartTypes(): array
    {
        return [
            self::CHART_LINE => 'Line Chart',
            self::CHART_BAR => 'Bar Chart',
            self::CHART_PIE => 'Pie Chart',
            self::CHART_HEATMAP => 'Heatmap',
            self::CHART_MIXED => 'Mixed Charts',
        ];
    }

    /**
     * Get available metrics.
     */
    public static function getAvailableMetrics(): array
    {
        return [
            self::METRIC_TOTAL_CONVERSATIONS => [
                'label' => 'Total Conversations',
                'category' => 'general',
                'icon' => 'chat',
            ],
            self::METRIC_TOTAL_MESSAGES => [
                'label' => 'Total Messages',
                'category' => 'general',
                'icon' => 'message',
            ],
            self::METRIC_BOT_RESOLUTION_RATE => [
                'label' => 'Bot Resolution Rate',
                'category' => 'performance',
                'icon' => 'smart_toy',
            ],
            self::METRIC_AVG_RESPONSE_TIME => [
                'label' => 'Average Response Time',
                'category' => 'performance',
                'icon' => 'timer',
            ],
            self::METRIC_FIRST_RESPONSE_TIME => [
                'label' => 'First Response Time',
                'category' => 'performance',
                'icon' => 'schedule',
            ],
            self::METRIC_ESCALATION_RATE => [
                'label' => 'Escalation Rate',
                'category' => 'performance',
                'icon' => 'trending_up',
            ],
            self::METRIC_CSAT_SCORE => [
                'label' => 'CSAT Score',
                'category' => 'quality',
                'icon' => 'star',
            ],
            self::METRIC_SENTIMENT_POSITIVE => [
                'label' => 'Positive Sentiment',
                'category' => 'sentiment',
                'icon' => 'sentiment_satisfied',
            ],
            self::METRIC_SENTIMENT_NEGATIVE => [
                'label' => 'Negative Sentiment',
                'category' => 'sentiment',
                'icon' => 'sentiment_dissatisfied',
            ],
            self::METRIC_SENTIMENT_NEUTRAL => [
                'label' => 'Neutral Sentiment',
                'category' => 'sentiment',
                'icon' => 'sentiment_neutral',
            ],
            self::METRIC_PEAK_HOURS => [
                'label' => 'Peak Hours',
                'category' => 'analytics',
                'icon' => 'access_time',
            ],
            self::METRIC_AGENT_PERFORMANCE => [
                'label' => 'Agent Performance',
                'category' => 'analytics',
                'icon' => 'people',
            ],
        ];
    }

    /**
     * Scope for default templates.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for shared templates.
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Scope for user's own templates.
     */
    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Set this template as default for the user.
     */
    public function setAsDefault(): void
    {
        // Remove default from other templates
        self::where('user_id', $this->user_id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Duplicate this template.
     */
    public function duplicate(?int $newUserId = null): self
    {
        return self::create([
            'user_id' => $newUserId ?? $this->user_id,
            'name' => $this->name . ' (Copy)',
            'description' => $this->description,
            'metrics' => $this->metrics,
            'filters' => $this->filters,
            'chart_type' => $this->chart_type,
            'is_default' => false,
            'is_shared' => false,
        ]);
    }

    /**
     * Get chart type label.
     */
    public function getChartTypeLabelAttribute(): string
    {
        return self::getChartTypes()[$this->chart_type] ?? $this->chart_type;
    }

    /**
     * Get metrics with labels.
     */
    public function getMetricsWithLabelsAttribute(): array
    {
        $availableMetrics = self::getAvailableMetrics();
        $result = [];

        foreach ($this->metrics as $metric) {
            $result[] = [
                'value' => $metric,
                'label' => $availableMetrics[$metric]['label'] ?? $metric,
                'category' => $availableMetrics[$metric]['category'] ?? 'other',
                'icon' => $availableMetrics[$metric]['icon'] ?? 'help',
            ];
        }

        return $result;
    }

    /**
     * Get filter value safely.
     */
    public function getFilter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }
}
