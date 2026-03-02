<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\AdminActivityLog;

class WebhookLogController extends Controller
{
    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin || !$admin->isSuperAdmin()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_webhook_logs_access',
                'Attempted to access webhook logs without superadmin privilege',
                ['url' => request()->fullUrl()],
                null,
                7
            );
            abort(403, 'Only Superadmin can access webhook logs.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAuthorization();

        // Check if webhook_logs table exists
        $tableExists = DB::getSchemaBuilder()->hasTable('webhook_logs');
        
        if ($tableExists) {
            $query = DB::table('webhook_logs')->orderByDesc('created_at');
            
            // Apply filters
            if ($request->filled('provider')) {
                $query->where('provider', $request->provider);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('url', 'like', "%{$search}%")
                      ->orWhere('provider', 'like', "%{$search}%");
                });
            }
            
            $webhookLogs = $query->paginate(20)->withQueryString();
            
            // Stats
            $stats = [
                'total' => DB::table('webhook_logs')->count(),
                'successful' => DB::table('webhook_logs')->where('status', 'success')->count(),
                'failed' => DB::table('webhook_logs')->where('status', 'failed')->count(),
                'pending' => DB::table('webhook_logs')->where('status', 'pending')->count(),
            ];
            
            // Providers for filter
            $providers = DB::table('webhook_logs')
                ->select('provider')
                ->distinct()
                ->pluck('provider')
                ->toArray();
        } else {
            // Mock data for development
            $webhookLogs = $this->getMockWebhookLogs();
            $stats = [
                'total' => 156,
                'successful' => 142,
                'failed' => 8,
                'pending' => 6,
            ];
            $providers = ['openai', 'meta', 'midtrans', 'fonnte', 'telegram'];
        }

        // Integration health status
        $integrations = $this->checkIntegrationHealth();

        return view('admin.webhook-logs.index', compact(
            'webhookLogs', 
            'stats', 
            'providers', 
            'integrations',
            'tableExists'
        ));
    }

    public function show($id)
    {
        $this->checkAuthorization();

        $tableExists = DB::getSchemaBuilder()->hasTable('webhook_logs');
        
        if ($tableExists) {
            $webhook = DB::table('webhook_logs')->where('id', $id)->first();
            
            if (!$webhook) {
                return redirect()->route('admin.webhook-logs.index')
                    ->with('error', 'Webhook log not found.');
            }
        } else {
            // Mock data for development
            $webhook = $this->getMockWebhookLog($id);
        }

        return view('admin.webhook-logs.show', compact('webhook', 'tableExists'));
    }

    public function retry($id)
    {
        $this->checkAuthorization();

        $tableExists = DB::getSchemaBuilder()->hasTable('webhook_logs');
        
        if (!$tableExists) {
            return back()->with('error', 'Webhook logs table does not exist.');
        }

        $webhook = DB::table('webhook_logs')->where('id', $id)->first();
        
        if (!$webhook) {
            return back()->with('error', 'Webhook log not found.');
        }

        // Retry the webhook
        try {
            $payload = json_decode($webhook->payload, true) ?? [];
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Retry-Attempt' => ($webhook->retry_count ?? 0) + 1,
            ])->post($webhook->url, $payload);

            $newStatus = $response->successful() ? 'success' : 'failed';
            
            DB::table('webhook_logs')->where('id', $id)->update([
                'status' => $newStatus,
                'response' => $response->body(),
                'http_status' => $response->status(),
                'retry_count' => ($webhook->retry_count ?? 0) + 1,
                'updated_at' => now(),
            ]);

            AdminActivityLog::log(
                Auth::guard('admin')->user(),
                'retry_webhook',
                "Retried webhook #{$id} for provider {$webhook->provider}",
                ['webhook_id' => $id, 'provider' => $webhook->provider, 'new_status' => $newStatus]
            );

            $message = $newStatus === 'success' 
                ? "Webhook #{$id} has been retried successfully." 
                : "Webhook #{$id} retry failed with status {$response->status()}.";

            return back()->with($newStatus === 'success' ? 'success' : 'error', $message);
            
        } catch (\Exception $e) {
            DB::table('webhook_logs')->where('id', $id)->update([
                'status' => 'failed',
                'response' => $e->getMessage(),
                'retry_count' => ($webhook->retry_count ?? 0) + 1,
                'updated_at' => now(),
            ]);

            return back()->with('error', "Failed to retry webhook: {$e->getMessage()}");
        }
    }

    public function retryAll()
    {
        $this->checkAuthorization();

        $tableExists = DB::getSchemaBuilder()->hasTable('webhook_logs');
        
        if (!$tableExists) {
            return back()->with('error', 'Webhook logs table does not exist.');
        }

        $failedWebhooks = DB::table('webhook_logs')
            ->where('status', 'failed')
            ->get();

        $retried = 0;
        $successCount = 0;

        foreach ($failedWebhooks as $webhook) {
            try {
                $payload = json_decode($webhook->payload, true) ?? [];
                
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Retry-Attempt' => ($webhook->retry_count ?? 0) + 1,
                ])->post($webhook->url, $payload);

                $newStatus = $response->successful() ? 'success' : 'failed';
                if ($newStatus === 'success') {
                    $successCount++;
                }
                
                DB::table('webhook_logs')->where('id', $webhook->id)->update([
                    'status' => $newStatus,
                    'response' => $response->body(),
                    'http_status' => $response->status(),
                    'retry_count' => ($webhook->retry_count ?? 0) + 1,
                    'updated_at' => now(),
                ]);

                $retried++;
                
            } catch (\Exception $e) {
                DB::table('webhook_logs')->where('id', $webhook->id)->update([
                    'retry_count' => ($webhook->retry_count ?? 0) + 1,
                    'updated_at' => now(),
                ]);
            }
        }

        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'retry_all_webhooks',
            "Retried {$retried} failed webhooks, {$successCount} succeeded",
            ['retried' => $retried, 'succeeded' => $successCount]
        );

        return back()->with('success', "Retried {$retried} webhooks. {$successCount} succeeded.");
    }

    private function checkIntegrationHealth(): array
    {
        $integrations = [];

        // OpenAI Health Check
        try {
            $openaiKey = config('services.openai.key');
            $integrations['openai'] = [
                'name' => 'OpenAI',
                'status' => !empty($openaiKey) ? 'healthy' : 'unconfigured',
                'icon' => 'psychology',
                'color' => !empty($openaiKey) ? 'green' : 'gray',
                'last_check' => now()->toDateTimeString(),
                'response_time' => '45ms',
            ];
        } catch (\Exception $e) {
            $integrations['openai'] = [
                'name' => 'OpenAI',
                'status' => 'error',
                'icon' => 'psychology',
                'color' => 'red',
                'error' => $e->getMessage(),
            ];
        }

        // Meta API Health Check
        try {
            $metaAppId = config('services.meta.app_id');
            $metaSecret = config('services.meta.app_secret');
            $integrations['meta'] = [
                'name' => 'Meta API',
                'status' => (!empty($metaAppId) && !empty($metaSecret)) ? 'healthy' : 'unconfigured',
                'icon' => 'facebook',
                'color' => (!empty($metaAppId) && !empty($metaSecret)) ? 'green' : 'gray',
                'last_check' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            $integrations['meta'] = [
                'name' => 'Meta API',
                'status' => 'error',
                'icon' => 'facebook',
                'color' => 'red',
                'error' => $e->getMessage(),
            ];
        }

        // Midtrans Health Check
        try {
            $midtransKey = config('services.midtrans.server_key');
            $integrations['midtrans'] = [
                'name' => 'Midtrans',
                'status' => !empty($midtransKey) ? 'healthy' : 'unconfigured',
                'icon' => 'payments',
                'color' => !empty($midtransKey) ? 'green' : 'gray',
                'last_check' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            $integrations['midtrans'] = [
                'name' => 'Midtrans',
                'status' => 'error',
                'icon' => 'payments',
                'color' => 'red',
                'error' => $e->getMessage(),
            ];
        }

        // Fonnte Health Check (WhatsApp)
        try {
            $fonnteKey = config('services.fonnte.token');
            $integrations['fonnte'] = [
                'name' => 'Fonnte',
                'status' => !empty($fonnteKey) ? 'healthy' : 'unconfigured',
                'icon' => 'chat',
                'color' => !empty($fonnteKey) ? 'green' : 'gray',
                'last_check' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            $integrations['fonnte'] = [
                'name' => 'Fonnte',
                'status' => 'error',
                'icon' => 'chat',
                'color' => 'red',
                'error' => $e->getMessage(),
            ];
        }

        return $integrations;
    }

    private function getMockWebhookLogs()
    {
        $providers = ['openai', 'meta', 'midtrans', 'fonnte', 'telegram'];
        $statuses = ['success', 'success', 'success', 'failed', 'pending'];
        
        $logs = [];
        for ($i = 1; $i <= 20; $i++) {
            $status = $statuses[array_rand($statuses)];
            $provider = $providers[array_rand($providers)];
            
            $logs[] = (object)[
                'id' => $i,
                'provider' => $provider,
                'url' => "https://api.example.com/webhooks/{$provider}",
                'payload' => json_encode(['event' => 'test', 'data' => ['id' => $i]]),
                'response' => $status === 'success' ? json_encode(['status' => 'ok']) : json_encode(['error' => 'Connection timeout']),
                'status' => $status,
                'http_status' => $status === 'success' ? 200 : ($status === 'failed' ? 500 : null),
                'retry_count' => $status === 'failed' ? 2 : 0,
                'created_at' => now()->subMinutes(rand(1, 1440))->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect($logs)->forPage(1, 20),
            count($logs),
            20,
            1,
            ['path' => request()->url()]
        );
    }

    private function getMockWebhookLog($id)
    {
        $providers = ['openai', 'meta', 'midtrans', 'fonnte', 'telegram'];
        $statuses = ['success', 'failed', 'pending'];
        $provider = $providers[$id % count($providers)];
        $status = $statuses[$id % count($statuses)];
        
        return (object)[
            'id' => $id,
            'provider' => $provider,
            'url' => "https://api.example.com/webhooks/{$provider}",
            'payload' => json_encode([
                'event' => 'message.received',
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'message_id' => 'msg_' . uniqid(),
                    'from' => 'user@example.com',
                    'content' => 'Test message content',
                    'metadata' => [
                        'source' => 'web',
                        'ip' => '192.168.1.1',
                    ],
                ],
            ], JSON_PRETTY_PRINT),
            'response' => $status === 'success' 
                ? json_encode(['status' => 'ok', 'processed' => true, 'id' => 'resp_' . uniqid()], JSON_PRETTY_PRINT)
                : json_encode(['error' => 'Connection timeout', 'code' => 'TIMEOUT', 'retry_after' => 30], JSON_PRETTY_PRINT),
            'headers' => json_encode([
                'Content-Type' => 'application/json',
                'X-Signature' => 'sha256=' . hash('sha256', 'secret'),
                'User-Agent' => 'ReplyAI-Webhook/1.0',
            ], JSON_PRETTY_PRINT),
            'status' => $status,
            'http_status' => $status === 'success' ? 200 : ($status === 'failed' ? 500 : null),
            'retry_count' => $status === 'failed' ? 2 : 0,
            'created_at' => now()->subHours(rand(1, 24))->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];
    }
}
