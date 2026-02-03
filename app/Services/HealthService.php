<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HealthService
{
    /**
     * Check OpenAI API Status
     */
    public function checkOpenAI(): array
    {
        return Cache::remember('health_openai', 300, function () {
            try {
                $response = Http::timeout(3)->get('https://status.openai.com/api/v2/status.json');
                if ($response->successful()) {
                    $status = $response->json()['status']['indicator'];
                    return [
                        'name' => 'OpenAI',
                        'status' => $status === 'none' ? 'online' : 'degraded',
                        'message' => $response->json()['status']['description'],
                    ];
                }
            } catch (\Exception $e) {}

            return ['name' => 'OpenAI', 'status' => 'unknown', 'message' => 'Status check failed'];
        });
    }

    /**
     * Check Meta (Instagram/WA Cloud) Status
     */
    public function checkMeta(): array
    {
        return Cache::remember('health_meta', 300, function () {
            // Meta doesn't have a simple public JSON status, we can try a basic ping to graph
            try {
                $response = Http::timeout(3)->get('https://graph.facebook.com/v18.0/debug_token');
                // Even without credentials, if it returns 400 (not 5xx), the API is likely up
                if ($response->status() < 500) {
                    return ['name' => 'Meta API', 'status' => 'online', 'message' => 'Graph API responsive'];
                }
            } catch (\Exception $e) {}

            return ['name' => 'Meta API', 'status' => 'offline', 'message' => 'Service unreachable'];
        });
    }

    /**
     * Check Fonnte API (WA Local/Unofficial)
     */
    public function checkFonnte(): array
    {
        return Cache::remember('health_fonnte', 300, function () {
            try {
                $response = Http::timeout(3)->get('https://api.fonnte.com');
                if ($response->status() < 500) {
                    return ['name' => 'Fonnte', 'status' => 'online', 'message' => 'Main gateway online'];
                }
            } catch (\Exception $e) {}

            return ['name' => 'Fonnte', 'status' => 'offline', 'message' => 'Service unreachable'];
        });
    }

    /**
     * Get all external health stats
     */
    public function getExternalHealth(): array
    {
        return [
            $this->checkOpenAI(),
            $this->checkMeta(),
            $this->checkFonnte(),
        ];
    }

    /**
     * Get real-time PM2 status
     */
    public function getPm2Status(): array
    {
        try {
            // Set PM2_HOME and PATH for Windows web server compatibility
            // Since PM2 processes are per-user on Windows, we force it to use Administrator's session
            $pm2Home = 'C:\Users\Administrator\.pm2';
            $npmPath = 'C:\Users\Administrator\AppData\Roaming\npm';
            
            putenv("PM2_HOME={$pm2Home}");
            $currentPath = getenv('PATH');
            putenv("PATH={$npmPath};{$currentPath}");

            // Using shell_exec to get PM2 process list in JSON format
            // We use 'pm2.cmd' explicitly for Windows stability
            $output = shell_exec('pm2 jlist 2>&1');
            
            if ($output) {
                // Clean up PM2 startup logs/noise from output
                // We look for the start of the JSON array: either "[{" (list with items) or "[]" (empty list)
                $jsonStart = strpos($output, '[{');
                if ($jsonStart === false) {
                    $jsonStart = strpos($output, '[]');
                }

                if ($jsonStart !== false) {
                    $output = substr($output, $jsonStart);
                }

                $processes = json_decode($output, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Log::error("PM2 JSON Decode Error: " . json_last_error_msg() . " | Output: " . substr($output, 0, 500));
                    return [];
                }

                return array_map(function ($proc) {
                    return [
                        'name' => $proc['name'] ?? 'unknown',
                        'pm_id' => $proc['pm_id'] ?? '?',
                        'status' => $proc['pm2_env']['status'] ?? 'unknown',
                        'mode' => $proc['pm2_env']['exec_mode'] ?? 'fork',
                        'uptime' => $proc['pm2_env']['pm_uptime'] ?? 0,
                        'cpu' => $proc['monit']['cpu'] ?? 0,
                        'memory' => round(($proc['monit']['memory'] ?? 0) / 1024 / 1024, 1) . 'MB',
                        'restart_count' => $proc['pm2_env']['restart_time'] ?? 0
                    ];
                }, $processes);
            }
        } catch (\Exception $e) {
            \Log::error("PM2 Status Check Failed: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Check if system ports are LISTENING
     */
    public function getSystemPorts(): array
    {
        $ports = [
            ['name' => 'Laravel (APP)', 'port' => 8000, 'host' => '127.0.0.1'],
            ['name' => 'WA Service (Node)', 'port' => 3001, 'host' => '127.0.0.1'],
            ['name' => 'Database (MySQL)', 'port' => 3306, 'host' => '127.0.0.1'],
            ['name' => 'Redis', 'port' => 6379, 'host' => '127.0.0.1'],
        ];

        $results = [];
        foreach ($ports as $p) {
            $connection = @fsockopen($p['host'], $p['port'], $errno, $errstr, 0.5);
            if (is_resource($connection)) {
                $results[] = array_merge($p, ['status' => 'online']);
                fclose($connection);
            } else {
                $results[] = array_merge($p, ['status' => 'offline']);
            }
        }

        return $results;
    }

    /**
     * Get recent logs for a PM2 service
     */
    public function getServiceLogs(string $serviceName, int $lines = 50): string
    {
        try {
            $pm2Home = 'C:\Users\Administrator\.pm2';
            $npmPath = 'C:\Users\Administrator\AppData\Roaming\npm';
            putenv("PM2_HOME={$pm2Home}");
            $currentPath = getenv('PATH');
            putenv("PATH={$npmPath};{$currentPath}");

            // Execute pm2 logs command
            $cmd = "pm2 logs " . escapeshellarg($serviceName) . " --lines {$lines} --nostream 2>&1";
            $output = shell_exec($cmd);

            return $output ?: "No logs found for service: {$serviceName}";
        } catch (\Exception $e) {
            return "Failed to fetch logs: " . $e->getMessage();
        }
    }

    /**
     * Auto-monitor and heal services
     */
    public function monitorAndHeal(): array
    {
        $status = $this->getPm2Status();
        $healed = [];

        foreach ($status as $proc) {
            if ($proc['status'] !== 'online') {
                \Log::warning("SystemHealth: Service {$proc['name']} is {$proc['status']}. Attempting auto-restart.");
                
                $pm2Home = 'C:\Users\Administrator\.pm2';
                $npmPath = 'C:\Users\Administrator\AppData\Roaming\npm';
                putenv("PM2_HOME={$pm2Home}");
                $currentPath = getenv('PATH');
                putenv("PATH={$npmPath};{$currentPath}");

                shell_exec("pm2 restart " . escapeshellarg($proc['name']) . " 2>&1");
                $healed[] = $proc['name'];
            }
        }

        if (!empty($healed)) {
            $message = "⚠️ *REPLYAI SYSTEM ALERT*\n\nTerdeteksi servis mati dan telah di-restart otomatis:\n- " . implode("\n- ", $healed) . "\n\nSilakan cek Dashboard untuk detail.";
            $this->sendAdminAlert($message);
        }

        return $healed;
    }

    /**
     * Send alert to all superadmins
     */
    public function sendAdminAlert(string $message): void
    {
        try {
            // Find an active/connected device to send from
            $device = \App\Models\WhatsAppDevice::where('status', 'connected')->first();
            if (!$device) return;

            $superAdmins = \App\Models\AdminUser::where('role', 'superadmin')->where('is_active', true)->get();

            foreach ($superAdmins as $admin) {
                if ($admin->phone) {
                    $waService = app(WhatsAppService::class);
                    $waService->sendMessage($device->session_id, $admin->phone, $message);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send admin alert: " . $e->getMessage());
        }
    }
}
