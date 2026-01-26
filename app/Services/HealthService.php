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
}
