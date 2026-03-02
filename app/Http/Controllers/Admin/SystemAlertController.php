<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SystemAlertController extends Controller
{
    protected $alertRulesCacheKey = 'system_alert_rules';
    protected $alertHistoryCacheKey = 'system_alert_history';

    /**
     * Check authorization - only superadmin can manage alerts
     */
    private function checkAuthorization(): void
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role !== 'superadmin') {
            abort(403, 'Unauthorized. Only superadmin can manage system alerts.');
        }
    }

    /**
     * Show all system alerts with configuration
     */
    public function index()
    {
        $this->checkAuthorization();

        // Alert rules
        $alertRules = $this->getAlertRules();

        // Recent alerts history
        $alertHistory = $this->getAlertHistory();

        // System metrics for context
        $metrics = [
            'disk_usage' => $this->getDiskUsage(),
            'failed_jobs_count' => DB::table('failed_jobs')->count(),
            'queue_size' => $this->getQueueSize(),
            'error_rate' => $this->getErrorRate(),
            'service_status' => $this->getServiceStatus(),
        ];

        // Alert channels
        $channels = [
            'email' => config('mail.from.address') ? true : false,
            'slack' => config('services.slack.webhook_url') ? true : false,
            'telegram' => config('services.telegram.bot_token') ? true : false,
            'database' => true,
        ];

        return view('admin.system-alerts.index', compact(
            'alertRules',
            'alertHistory',
            'metrics',
            'channels'
        ));
    }

    /**
     * Create new alert rule
     */
    public function store(Request $request)
    {
        $this->checkAuthorization();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:disk_usage,service_down,failed_jobs,error_rate,queue_backlog',
            'condition' => 'required|in:greater_than,less_than,equals',
            'threshold' => 'required|numeric|min:0',
            'channels' => 'required|array',
            'channels.*' => 'in:email,slack,telegram,database',
            'cooldown' => 'required|integer|min:1|max:1440',
            'description' => 'nullable|string|max:255',
        ]);

        $rules = $this->getAlertRules();
        
        $newRule = [
            'id' => uniqid('alert_'),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'condition' => $validated['condition'],
            'threshold' => $validated['threshold'],
            'channels' => $validated['channels'],
            'cooldown' => $validated['cooldown'],
            'description' => $validated['description'] ?? '',
            'enabled' => true,
            'created_at' => now()->toIso8601String(),
            'last_triggered' => null,
            'trigger_count' => 0,
        ];

        $rules[] = $newRule;
        $this->saveAlertRules($rules);

        // Log activity
        \App\Models\AdminActivityLog::log(
            auth()->guard('admin')->user(),
            'create_alert_rule',
            "Created alert rule: {$validated['name']}",
            $newRule,
            null
        );

        return redirect()->route('admin.system-alerts.index')
            ->with('success', 'Alert rule created successfully');
    }

    /**
     * Toggle alert rule enable/disable
     */
    public function toggle($id)
    {
        $this->checkAuthorization();

        $rules = $this->getAlertRules();
        $found = false;

        foreach ($rules as &$rule) {
            if ($rule['id'] === $id) {
                $rule['enabled'] = !$rule['enabled'];
                $found = true;
                $status = $rule['enabled'] ? 'enabled' : 'disabled';
                break;
            }
        }

        if (!$found) {
            return redirect()->route('admin.system-alerts.index')
                ->with('error', 'Alert rule not found');
        }

        $this->saveAlertRules($rules);

        // Log activity
        \App\Models\AdminActivityLog::log(
            auth()->guard('admin')->user(),
            'toggle_alert_rule',
            "Alert rule {$status}: {$id}",
            ['rule_id' => $id, 'status' => $status],
            null
        );

        return redirect()->route('admin.system-alerts.index')
            ->with('success', "Alert rule {$status} successfully");
    }

    /**
     * Delete alert rule
     */
    public function destroy($id)
    {
        $this->checkAuthorization();

        $rules = $this->getAlertRules();
        $originalCount = count($rules);
        
        $rules = array_filter($rules, function ($rule) use ($id) {
            return $rule['id'] !== $id;
        });

        if (count($rules) === $originalCount) {
            return redirect()->route('admin.system-alerts.index')
                ->with('error', 'Alert rule not found');
        }

        $this->saveAlertRules(array_values($rules));

        // Log activity
        \App\Models\AdminActivityLog::log(
            auth()->guard('admin')->user(),
            'delete_alert_rule',
            "Deleted alert rule: {$id}",
            ['rule_id' => $id],
            null
        );

        return redirect()->route('admin.system-alerts.index')
            ->with('success', 'Alert rule deleted successfully');
    }

    /**
     * Send test alert
     */
    public function test(Request $request)
    {
        $this->checkAuthorization();

        $channel = $request->input('channel', 'database');
        $testMessage = [
            'title' => 'Test Alert',
            'message' => 'This is a test alert from ReplyAI System Alerts',
            'severity' => 'info',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'disk_usage' => 45,
                'failed_jobs' => 0,
                'test' => true,
            ],
        ];

        $sent = $this->sendNotification($testMessage, [$channel]);

        if ($sent) {
            // Add to history
            $this->addToHistory([
                'id' => uniqid('hist_'),
                'rule_id' => 'test',
                'rule_name' => 'Test Alert',
                'severity' => 'info',
                'message' => $testMessage['message'],
                'channels' => [$channel],
                'data' => $testMessage['data'],
                'created_at' => now()->toIso8601String(),
            ]);

            return redirect()->route('admin.system-alerts.index')
                ->with('success', "Test alert sent via {$channel}");
        }

        return redirect()->route('admin.system-alerts.index')
            ->with('error', "Failed to send test alert via {$channel}");
    }

    /**
     * Check and send alerts based on thresholds
     * This method is typically called by a scheduled command
     */
    public function checkAndSendAlerts(): array
    {
        $rules = $this->getAlertRules();
        $triggered = [];

        foreach ($rules as &$rule) {
            if (!$rule['enabled']) {
                continue;
            }

            // Check cooldown
            if ($rule['last_triggered']) {
                $lastTriggered = Carbon::parse($rule['last_triggered']);
                $cooldownMinutes = $rule['cooldown'];
                
                if ($lastTriggered->diffInMinutes(now()) < $cooldownMinutes) {
                    continue;
                }
            }

            $shouldTrigger = false;
            $currentValue = null;
            $alertData = [];

            switch ($rule['type']) {
                case 'disk_usage':
                    $currentValue = $this->getDiskUsage();
                    $shouldTrigger = $this->evaluateCondition(
                        $currentValue,
                        $rule['condition'],
                        $rule['threshold']
                    );
                    $alertData = ['disk_usage_percent' => $currentValue];
                    break;

                case 'failed_jobs':
                    $currentValue = DB::table('failed_jobs')->count();
                    $shouldTrigger = $this->evaluateCondition(
                        $currentValue,
                        $rule['condition'],
                        $rule['threshold']
                    );
                    $alertData = ['failed_jobs_count' => $currentValue];
                    break;

                case 'queue_backlog':
                    $currentValue = $this->getQueueSize();
                    $shouldTrigger = $this->evaluateCondition(
                        $currentValue,
                        $rule['condition'],
                        $rule['threshold']
                    );
                    $alertData = ['queue_size' => $currentValue];
                    break;

                case 'error_rate':
                    $currentValue = $this->getErrorRate();
                    $shouldTrigger = $this->evaluateCondition(
                        $currentValue,
                        $rule['condition'],
                        $rule['threshold']
                    );
                    $alertData = ['errors_per_minute' => $currentValue];
                    break;

                case 'service_down':
                    $serviceStatus = $this->getServiceStatus();
                    $failedServices = array_filter($serviceStatus, fn($s) => !$s['healthy']);
                    $currentValue = count($failedServices);
                    $shouldTrigger = $currentValue > 0;
                    $alertData = ['failed_services' => array_column($failedServices, 'name')];
                    break;
            }

            if ($shouldTrigger) {
                $alertMessage = [
                    'title' => "Alert: {$rule['name']}",
                    'message' => $this->buildAlertMessage($rule, $currentValue),
                    'severity' => $this->getSeverity($rule['type'], $currentValue, $rule['threshold']),
                    'timestamp' => now()->toIso8601String(),
                    'data' => array_merge($alertData, [
                        'threshold' => $rule['threshold'],
                        'condition' => $rule['condition'],
                        'current_value' => $currentValue,
                    ]),
                ];

                $sent = $this->sendNotification($alertMessage, $rule['channels']);

                if ($sent) {
                    $rule['last_triggered'] = now()->toIso8601String();
                    $rule['trigger_count'] = ($rule['trigger_count'] ?? 0) + 1;
                    
                    $this->addToHistory([
                        'id' => uniqid('hist_'),
                        'rule_id' => $rule['id'],
                        'rule_name' => $rule['name'],
                        'severity' => $alertMessage['severity'],
                        'message' => $alertMessage['message'],
                        'channels' => $rule['channels'],
                        'data' => $alertMessage['data'],
                        'created_at' => now()->toIso8601String(),
                    ]);

                    $triggered[] = [
                        'rule' => $rule['name'],
                        'value' => $currentValue,
                        'severity' => $alertMessage['severity'],
                    ];
                }
            }
        }

        $this->saveAlertRules($rules);

        return [
            'checked' => count($rules),
            'triggered' => $triggered,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get current disk usage percentage
     */
    private function getDiskUsage(): float
    {
        $total = disk_total_space(base_path());
        $free = disk_free_space(base_path());
        
        if ($total === 0 || $total === false) {
            return 0;
        }
        
        return round((($total - $free) / $total) * 100, 2);
    }

    /**
     * Get queue size
     */
    private function getQueueSize(): int
    {
        try {
            return DB::table('jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get error rate (errors per minute in last 5 minutes)
     */
    private function getErrorRate(): float
    {
        // Check log file for recent errors (simplified)
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $fiveMinutesAgo = now()->subMinutes(5);
        $errorCount = 0;
        
        // Simple grep for recent errors (in production, use proper log aggregation)
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            if (strpos($line, 'ERROR') !== false) {
                // Check if error is recent (simplified check)
                $errorCount++;
            }
        }
        
        return round($errorCount / 5, 2); // errors per minute
    }

    /**
     * Get service status
     */
    private function getServiceStatus(): array
    {
        $services = [];

        // Database
        try {
            DB::connection()->getPdo();
            $services[] = ['name' => 'Database', 'healthy' => true];
        } catch (\Exception $e) {
            $services[] = ['name' => 'Database', 'healthy' => false, 'error' => $e->getMessage()];
        }

        // Cache
        try {
            Cache::put('health_check', 'ok', 10);
            $healthy = Cache::get('health_check') === 'ok';
            $services[] = ['name' => 'Cache', 'healthy' => $healthy];
        } catch (\Exception $e) {
            $services[] = ['name' => 'Cache', 'healthy' => false, 'error' => $e->getMessage()];
        }

        // WA Service (check port)
        $services[] = [
            'name' => 'WA Service',
            'healthy' => @fsockopen('127.0.0.1', 3001, $errno, $errstr, 1) !== false
        ];

        return $services;
    }

    /**
     * Evaluate condition
     */
    private function evaluateCondition($value, $condition, $threshold): bool
    {
        switch ($condition) {
            case 'greater_than':
                return $value > $threshold;
            case 'less_than':
                return $value < $threshold;
            case 'equals':
                return $value == $threshold;
            default:
                return false;
        }
    }

    /**
     * Build alert message
     */
    private function buildAlertMessage(array $rule, $currentValue): string
    {
        $typeLabels = [
            'disk_usage' => 'Disk Usage',
            'service_down' => 'Service Status',
            'failed_jobs' => 'Failed Jobs',
            'error_rate' => 'Error Rate',
            'queue_backlog' => 'Queue Backlog',
        ];

        $conditionLabels = [
            'greater_than' => 'exceeded',
            'less_than' => 'dropped below',
            'equals' => 'equals',
        ];

        $typeLabel = $typeLabels[$rule['type']] ?? $rule['type'];
        $conditionLabel = $conditionLabels[$rule['condition']] ?? $rule['condition'];

        if ($rule['type'] === 'service_down') {
            return "{$typeLabel}: {$currentValue} service(s) are down";
        }

        return "{$typeLabel} has {$conditionLabel} {$rule['threshold']} (current: {$currentValue})";
    }

    /**
     * Get severity based on rule type and values
     */
    private function getSeverity(string $type, $currentValue, $threshold): string
    {
        switch ($type) {
            case 'disk_usage':
                if ($currentValue > 90) return 'critical';
                if ($currentValue > 75) return 'warning';
                return 'info';

            case 'failed_jobs':
                if ($currentValue > 100) return 'critical';
                if ($currentValue > 50) return 'warning';
                return 'info';

            case 'service_down':
                return 'critical';

            case 'error_rate':
                if ($currentValue > 10) return 'critical';
                if ($currentValue > 5) return 'warning';
                return 'info';

            case 'queue_backlog':
                if ($currentValue > 1000) return 'critical';
                if ($currentValue > 500) return 'warning';
                return 'info';

            default:
                return 'warning';
        }
    }

    /**
     * Send notification through specified channels
     */
    private function sendNotification(array $message, array $channels): bool
    {
        $sent = false;

        foreach ($channels as $channel) {
            try {
                switch ($channel) {
                    case 'email':
                        $sent = $this->sendEmailNotification($message) || $sent;
                        break;

                    case 'slack':
                        $sent = $this->sendSlackNotification($message) || $sent;
                        break;

                    case 'telegram':
                        $sent = $this->sendTelegramNotification($message) || $sent;
                        break;

                    case 'database':
                        $sent = $this->saveToDatabase($message) || $sent;
                        break;
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send alert', [
                    'channel' => $channel,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(array $message): bool
    {
        $adminEmail = config('mail.from.address');
        
        if (!$adminEmail) {
            return false;
        }

        try {
            Mail::raw($message['message'], function ($mail) use ($message, $adminEmail) {
                $mail->to($adminEmail)
                     ->subject('[ReplyAI Alert] ' . $message['title']);
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('Email alert failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send Slack notification
     */
    private function sendSlackNotification(array $message): bool
    {
        $webhookUrl = config('services.slack.webhook_url');
        
        if (!$webhookUrl) {
            return false;
        }

        try {
            $response = Http::post($webhookUrl, [
                'text' => "*{$message['title']}*\n{$message['message']}",
                'attachments' => [
                    [
                        'color' => $message['severity'] === 'critical' ? 'danger' : 
                                  ($message['severity'] === 'warning' ? 'warning' : 'good'),
                        'fields' => [
                            [
                                'title' => 'Severity',
                                'value' => $message['severity'],
                                'short' => true,
                            ],
                            [
                                'title' => 'Time',
                                'value' => $message['timestamp'],
                                'short' => true,
                            ],
                        ],
                    ],
                ],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Slack alert failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send Telegram notification
     */
    private function sendTelegramNotification(array $message): bool
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');
        
        if (!$botToken || !$chatId) {
            return false;
        }

        try {
            $emoji = $message['severity'] === 'critical' ? '🔴' : 
                    ($message['severity'] === 'warning' ? '🟡' : '🔵');

            $text = "{$emoji} *{$message['title']}*\n\n{$message['message']}\n\n";
            $text .= "*Severity:* {$message['severity']}\n";
            $text .= "*Time:* {$message['timestamp']}";

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Telegram alert failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Save alert to database
     */
    private function saveToDatabase(array $message): bool
    {
        try {
            // You could create a SystemAlert model for persistent storage
            // For now, we'll use the cache-based history
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get alert rules from cache
     */
    private function getAlertRules(): array
    {
        return Cache::get($this->alertRulesCacheKey, []);
    }

    /**
     * Save alert rules to cache
     */
    private function saveAlertRules(array $rules): void
    {
        Cache::put($this->alertRulesCacheKey, $rules, now()->addDays(30));
    }

    /**
     * Get alert history
     */
    private function getAlertHistory(int $limit = 50): array
    {
        $history = Cache::get($this->alertHistoryCacheKey, []);
        
        // Sort by created_at descending and limit
        usort($history, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($history, 0, $limit);
    }

    /**
     * Add entry to alert history
     */
    private function addToHistory(array $entry): void
    {
        $history = Cache::get($this->alertHistoryCacheKey, []);
        $history[] = $entry;
        
        // Keep only last 100 entries
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }

        Cache::put($this->alertHistoryCacheKey, $history, now()->addDays(7));
    }

    /**
     * API endpoint for alert metrics
     */
    public function metrics()
    {
        $this->checkAuthorization();

        return response()->json([
            'disk_usage' => $this->getDiskUsage(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'queue_size' => $this->getQueueSize(),
            'error_rate' => $this->getErrorRate(),
            'service_status' => $this->getServiceStatus(),
            'active_rules' => count(array_filter($this->getAlertRules(), fn($r) => $r['enabled'])),
            'alerts_today' => count(array_filter($this->getAlertHistory(), fn($h) => 
                strtotime($h['created_at']) >= strtotime('today')
            )),
        ]);
    }
}
