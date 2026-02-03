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

        return view('admin.system-health', compact(
            'disconnectedWa', 
            'disconnectedIg', 
            'externalHealth',
            'dbStatus',
            'cacheStatus',
            'diskUsedPercent',
            'activeUsers24h',
            'pm2Status',
            'systemPorts'
        ));
    }

    /**
     * Manage PM2 Services (start, stop, restart)
     */
    public function manageService(Request $request)
    {
        $validated = $request->validate([
            'service_name' => 'required|string',
            'action' => 'required|in:start,stop,restart',
        ]);

        $service = $validated['service_name'];
        $action = $validated['action'];

        // Secure command execution
        // We only allow specific actions and the service name is strictly typed/passed from our list
        try {
            // Force PM2_HOME and PATH for Windows web server compatibility
            $pm2Home = 'C:\Users\Administrator\.pm2';
            $npmPath = 'C:\Users\Administrator\AppData\Roaming\npm';
            putenv("PM2_HOME={$pm2Home}");
            $currentPath = getenv('PATH');
            putenv("PATH={$npmPath};{$currentPath}");

            // Find in pm2 list first to ensure it's a managed service
            $pm2List = $this->healthService->getPm2Status();
            $exists = collect($pm2List)->where('name', $service)->first();

            if (!$exists) {
                return back()->with('error', "Service {$service} tidak ditemukan di daftar PM2.");
            }

            // Execute PM2 command
            $cmd = "pm2 {$action} " . escapeshellarg($service) . " 2>&1";
            shell_exec($cmd);

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
