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

        return view('admin.system-health', compact(
            'disconnectedWa', 
            'disconnectedIg', 
            'externalHealth',
            'dbStatus',
            'cacheStatus',
            'diskUsedPercent',
            'activeUsers24h',
        ));
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
