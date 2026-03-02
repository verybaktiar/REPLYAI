<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;
use Carbon\Carbon;

class ScheduledReport extends Model
{
    use BelongsToUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'report_type',
        'frequency',
        'day_of_week',
        'day_of_month',
        'send_time',
        'email_to',
        'format',
        'filters',
        'is_active',
        'last_sent_at',
        'next_send_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'array',
        'is_active' => 'boolean',
        'day_of_month' => 'integer',
        'last_sent_at' => 'datetime',
        'next_send_at' => 'datetime',
    ];

    /**
     * Report type constants.
     */
    const TYPE_ANALYTICS = 'analytics';
    const TYPE_AI_PERFORMANCE = 'ai_performance';
    const TYPE_CSAT = 'csat';
    const TYPE_CONVERSATION_QUALITY = 'conversation_quality';

    /**
     * Frequency constants.
     */
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';

    /**
     * Format constants.
     */
    const FORMAT_PDF = 'pdf';
    const FORMAT_EXCEL = 'excel';
    const FORMAT_CSV = 'csv';

    /**
     * Get available report types.
     */
    public static function getReportTypes(): array
    {
        return [
            self::TYPE_ANALYTICS => 'Analytics Report',
            self::TYPE_AI_PERFORMANCE => 'AI Performance Report',
            self::TYPE_CSAT => 'Customer Satisfaction Report',
            self::TYPE_CONVERSATION_QUALITY => 'Conversation Quality Report',
        ];
    }

    /**
     * Get available frequencies.
     */
    public static function getFrequencies(): array
    {
        return [
            self::FREQUENCY_DAILY => 'Daily',
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_MONTHLY => 'Monthly',
        ];
    }

    /**
     * Get available formats.
     */
    public static function getFormats(): array
    {
        return [
            self::FORMAT_PDF => 'PDF',
            self::FORMAT_EXCEL => 'Excel',
            self::FORMAT_CSV => 'CSV',
        ];
    }

    /**
     * Get available days of week.
     */
    public static function getDaysOfWeek(): array
    {
        return [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
    }

    /**
     * Scope for active reports.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for reports due to be sent.
     */
    public function scopeDue($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('next_send_at')
            ->where('next_send_at', '<=', now());
    }

    /**
     * Scope by report type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Check if report is due to be sent.
     */
    public function isDue(): bool
    {
        return $this->is_active 
            && $this->next_send_at 
            && $this->next_send_at->isPast();
    }

    /**
     * Mark report as sent and update next send time.
     */
    public function markAsSent(): void
    {
        $this->update([
            'last_sent_at' => now(),
            'next_send_at' => $this->calculateNextSendAt(),
        ]);
    }

    /**
     * Calculate the next send at datetime based on frequency.
     */
    protected function calculateNextSendAt(): ?Carbon
    {
        $now = now();
        $sendTime = $this->send_time ?? '09:00';
        [$hour, $minute] = explode(':', $sendTime);

        switch ($this->frequency) {
            case self::FREQUENCY_DAILY:
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                if ($next->lessThanOrEqualTo($now)) {
                    $next->addDay();
                }
                return $next;

            case self::FREQUENCY_WEEKLY:
                $daysMap = [
                    'sunday' => 0,
                    'monday' => 1,
                    'tuesday' => 2,
                    'wednesday' => 3,
                    'thursday' => 4,
                    'friday' => 5,
                    'saturday' => 6,
                ];
                $targetDay = $daysMap[strtolower($this->day_of_week ?? 'monday')] ?? 1;
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                $currentDay = $next->dayOfWeek;

                if ($currentDay === $targetDay && $next->greaterThan($now)) {
                    return $next;
                }

                $daysUntil = ($targetDay - $currentDay + 7) % 7;
                if ($daysUntil === 0) {
                    $daysUntil = 7;
                }

                return $next->addDays($daysUntil);

            case self::FREQUENCY_MONTHLY:
                $dayOfMonth = $this->day_of_month ?? 1;
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                $targetDay = min($dayOfMonth, $next->daysInMonth);

                if ($next->day === $targetDay && $next->greaterThan($now)) {
                    return $next;
                }

                if ($next->day < $targetDay) {
                    $next->setDay($targetDay);
                } else {
                    $next->addMonth()->setDay(min($dayOfMonth, $next->daysInMonth));
                }

                return $next;

            default:
                return null;
        }
    }

    /**
     * Get human-readable frequency label.
     */
    public function getFrequencyLabelAttribute(): string
    {
        return self::getFrequencies()[$this->frequency] ?? $this->frequency;
    }

    /**
     * Get human-readable report type label.
     */
    public function getReportTypeLabelAttribute(): string
    {
        return self::getReportTypes()[$this->report_type] ?? $this->report_type;
    }

    /**
     * Get human-readable format label.
     */
    public function getFormatLabelAttribute(): string
    {
        return self::getFormats()[$this->format] ?? $this->format;
    }
}
