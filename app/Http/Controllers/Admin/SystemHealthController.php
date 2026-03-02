<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppDevice;
use App\Models\InstagramAccount;
use App\Models\User;
use App\Services\HealthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\WebWidget;
use App\Models\WebConversation;
use App\Models\WebMessage;
use Illuminate\Http\Request;

class SystemHealthController extends Controller
{
    protected $healthService;

    public function __construct(HealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    /**
     * Check authorization - only superadmin can manage services
     */
    private function checkAuthorization(): void
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role !== 'superadmin') {
            abort(403, 'Unauthorized. Only superadmin can manage system services.');
        }
    }

    public function index()
    {
        // 1. Proactive Monitoring: Disconnected Accounts
        $disconnectedWa = WhatsAppDevice::where('status', '!=', WhatsAppDevice::STATUS_CONNECTED)
            ->where('is_active', true)
            ->with(['businessProfile.user'])
            ->get();

        $disconnectedIg = InstagramAccount::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('token_expires_at')
                      ->orWhere('token_expires_at', '<=', now());
            })
            ->with('user')
            ->get();

        // 2. External API Health
        $externalHealth = $this->healthService->getExternalHealth();

        // 3. System Stats
        $dbStatus = true;
        try { DB::connection()->getPdo(); } catch (\Exception $e) { $dbStatus = false; }

        $cacheStatus = true;
        try { 
            Cache::put('health_check', 'ok', 10);
            $cacheStatus = Cache::get('health_check') === 'ok';
        } catch (\Exception $e) { $cacheStatus = false; }

        $diskTotal = disk_total_space(base_path());
        $diskFree = disk_free_space(base_path());
        $diskUsedPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100);

        $activeUsers24h = User::where('updated_at', '>=', now()->subDay())->count();

        // 4. Internal Resources (PM2 & Ports)
        $pm2Status = $this->healthService->getPm2Status();
        $systemPorts = $this->healthService->getSystemPorts();

        // 5. Check Queue Worker & WA Service Status
        $queueStatus = collect($pm2Status)->contains(function ($service) {
            return $service['name'] === 'laravel-queue' && $service['status'] === 'online';
        });

        $waServiceStatus = collect($pm2Status)->contains(function ($service) {
            return $service['name'] === 'wa-service' && $service['status'] === 'online';
        });

        return view('admin.system-health', compact(
            'disconnectedWa', 
            'disconnectedIg', 
            'externalHealth',
            'dbStatus',
            'cacheStatus',
            'diskUsedPercent',
            'activeUsers24h',
            'pm2Status',
            'systemPorts',
            'queueStatus',
            'waServiceStatus'
        ));
    }

    /**
     * Manage PM2 Services (start, stop, restart)
     */
    public function manageService(Request $request)
    {
        $this->checkAuthorization();
        $validated = $request->validate([
            'service_name' => 'required|string',
            'action' => 'required|in:start,stop,restart',
        ]);

        $service = $validated['service_name'];
        $action = $validated['action'];

        // Allowed services
        $allowedServices = ['wa-service', 'laravel-queue'];
        if (!in_array($service, $allowedServices)) {
            return back()->with('error', "Service {$service} tidak diizinkan.");
        }

        try {
            // Force PM2_HOME and PATH for Windows web server compatibility
            $pm2Home = 'C:\Users\Administrator\.pm2';
            $npmPath = 'C:\Users\Administrator\AppData\Roaming\npm';
            putenv("PM2_HOME={$pm2Home}");
            $currentPath = getenv('PATH');
            putenv("PATH={$npmPath};{$currentPath}");

            // Find in pm2 list to check if service exists
            $pm2List = $this->healthService->getPm2Status();
            $exists = collect($pm2List)->where('name', $service)->first();

            // If service doesn't exist and action is 'start', use ecosystem file
            if (!$exists && $action === 'start') {
                $ecoPath = base_path('ecosystem.config.cjs');
                if (!file_exists($ecoPath)) {
                    return back()->with('error', "Ecosystem file tidak ditemukan.");
                }
                
                // Delete any old PM2 config for this service first (fix wrong cwd issue)
                shell_exec("pm2 delete " . escapeshellarg($service) . " 2>&1");
                sleep(1);
                
                // Clear PM2 dump and save
                shell_exec("pm2 cleardump 2>&1");
                
                // Start service using ecosystem file with proper cwd
                if ($service === 'wa-service') {
                    // Start wa-service manually with correct cwd
                    $waServicePath = base_path('wa-service');
                    $cmd = "cd " . escapeshellarg($waServicePath) . " && pm2 start index.js --name " . escapeshellarg($service) . " 2>&1";
                    $output = shell_exec($cmd);
                } else {
                    // Start other services via ecosystem
                    $cmd = "pm2 start " . escapeshellarg($ecoPath) . " --only " . escapeshellarg($service) . " 2>&1";
                    $output = shell_exec($cmd);
                }
                
                // Save PM2 config
                shell_exec("pm2 save 2>&1");
                
                // Check if successfully started
                sleep(3);
                $pm2ListAfter = $this->healthService->getPm2Status();
                $started = collect($pm2ListAfter)->where('name', $service)->first();
                
                if ($started && $started['status'] === 'online') {
                    \App\Models\AdminActivityLog::log(
                        auth()->guard('admin')->user(),
                        'manage_service',
                        "Started PM2 service: {$service}",
                        ['service' => $service, 'action' => $action, 'output' => $output],
                        null
                    );
                    return back()->with('success', "Service {$service} berhasil dijalankan!");
                } else {
                    // Try to get more detailed error
                    $logs = shell_exec("pm2 logs {$service} --lines 10 2>&1");
                    return back()->with('error', "Gagal menjalankan service {$service}. Error: " . substr($output . ' ' . $logs, 0, 500));
                }
            }

            // Service exists, check if it needs reset (errored state with wrong path)
            if ($exists) {
                // If service is in errored state, delete and recreate
                if ($exists['status'] === 'errored' && $action === 'start') {
                    shell_exec("pm2 delete " . escapeshellarg($service) . " 2>&1");
                    sleep(1);
                    
                    // Start fresh
                    if ($service === 'wa-service') {
                        $waServicePath = base_path('wa-service');
                        $cmd = "cd " . escapeshellarg($waServicePath) . " && pm2 start index.js --name " . escapeshellarg($service) . " 2>&1";
                    } else {
                        $cmd = "pm2 start " . escapeshellarg(base_path('ecosystem.config.cjs')) . " --only " . escapeshellarg($service) . " 2>&1";
                    }
                    $output = shell_exec($cmd);
                    shell_exec("pm2 save 2>&1");
                    
                    sleep(2);
                    $pm2ListAfter = $this->healthService->getPm2Status();
                    $restarted = collect($pm2ListAfter)->where('name', $service)->first();
                    
                    if ($restarted && $restarted['status'] === 'online') {
                        return back()->with('success', "Service {$service} berhasil di-reset dan dijalankan ulang!");
                    } else {
                        return back()->with('error', "Gagal restart service {$service}. Output: " . substr($output, 0, 300));
                    }
                }
                
                // Normal action
                $cmd = "pm2 {$action} " . escapeshellarg($service) . " 2>&1";
                shell_exec($cmd);
            } else {
                return back()->with('error', "Service {$service} tidak ditemukan di daftar PM2.");
            }

            // Log activity
            \App\Models\AdminActivityLog::log(
                auth()->guard('admin')->user(),
                'manage_service',
                "Executing PM2 {$action} on service: {$service}",
                ['service' => $service, 'action' => $action],
                null
            );

            return back()->with('success', "Service {$service} berhasil di-{$action}!");
        } catch (\Exception $e) {
            return back()->with('error', "Gagal mengelola service: " . $e->getMessage());
        }
    }

    /**
     * Start non-PM2 system services (Redis, Laravel)
     */
    public function startSystemService(Request $request)
    {
        $this->checkAuthorization();
        $validated = $request->validate([
            'service' => 'required|in:redis,laravel',
        ]);

        $service = $validated['service'];

        try {
            if ($service === 'redis') {
                // Try to start Redis using various methods
                $output = [];
                
                // Method 1: Try redis-server directly (if in PATH)
                $cmd = 'start /B redis-server 2>&1';
                shell_exec($cmd);
                
                // Wait a moment and check if Redis is now running
                sleep(2);
                $connection = @fsockopen('127.0.0.1', 6379, $errno, $errstr, 1);
                
                if (is_resource($connection)) {
                    fclose($connection);
                    
                    \App\Models\AdminActivityLog::log(
                        auth()->guard('admin')->user(),
                        'start_service',
                        "Started Redis service",
                        ['service' => 'redis'],
                        null
                    );
                    
                    return response()->json(['success' => true, 'message' => 'Redis started successfully']);
                }
                
                return response()->json(['success' => false, 'message' => 'Failed to start Redis. Please start it manually.']);
            }
            
            if ($service === 'laravel') {
                // Start Laravel development server in background
                $projectPath = base_path();
                $cmd = 'start /B php "' . $projectPath . '\artisan" serve --port=8000 > NUL 2>&1';
                shell_exec($cmd);
                
                // Wait a moment and check if port is now open
                sleep(2);
                $connection = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 1);
                
                if (is_resource($connection)) {
                    fclose($connection);
                    
                    \App\Models\AdminActivityLog::log(
                        auth()->guard('admin')->user(),
                        'start_service',
                        "Started Laravel development server",
                        ['service' => 'laravel', 'port' => 8000],
                        null
                    );
                    
                    return response()->json(['success' => true, 'message' => 'Laravel development server started at http://127.0.0.1:8000']);
                }
                
                return response()->json(['success' => false, 'message' => 'Failed to start Laravel server. Please check if port 8000 is already in use.']);
            }
            
            return response()->json(['success' => false, 'message' => 'Unknown service']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Fetch logs for a service (AJAX)
     */
    public function viewLogs(Request $request)
    {
        $validated = $request->validate([
            'service_name' => 'required|string',
        ]);

        $logs = $this->healthService->getServiceLogs($validated['service_name']);

        return response($logs)->header('Content-Type', 'text/plain');
    }

    public function cleanupOrphans()
    {
        $waDeleted = WhatsAppDevice::whereNull('user_id')->delete();
        $igDeleted = InstagramAccount::whereNull('user_id')->delete();
        
        $widgetsDeleted = WebWidget::whereNull('user_id')->delete();
        $webConvDeleted = WebConversation::whereNull('user_id')->delete();
        $webMsgDeleted = WebMessage::whereNull('user_id')->delete();

        $totalDeleted = $waDeleted + $igDeleted + $widgetsDeleted + $webConvDeleted + $webMsgDeleted;

        return back()->with('success', "Pembersihan selesai! Total {$totalDeleted} data yatim (WA: {$waDeleted}, IG: {$igDeleted}, Web Chat: {$widgetsDeleted}) berhasil dihapus bersih.");
    }
}
