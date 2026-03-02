<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppDevice;
use App\Models\InstagramAccount;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeviceManagementController extends Controller
{
    private function checkAuthorization(): void
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role !== 'superadmin') {
            abort(403, 'Unauthorized. Only superadmin can manage devices.');
        }
    }

    public function index()
    {
        $this->checkAuthorization();

        // WhatsApp Devices with user info
        $waDevices = WhatsAppDevice::with(['businessProfile.user'])
            ->orderBy('status')
            ->orderByDesc('created_at')
            ->get();

        // Instagram Accounts with user info
        $igAccounts = InstagramAccount::with('user')
            ->orderBy('is_active', 'desc')
            ->orderByDesc('created_at')
            ->get();

        // Statistics
        $stats = [
            'wa_connected' => $waDevices->where('status', WhatsAppDevice::STATUS_CONNECTED)->count(),
            'wa_connecting' => $waDevices->where('status', WhatsAppDevice::STATUS_CONNECTING)->count(),
            'wa_disconnected' => $waDevices->where('status', WhatsAppDevice::STATUS_DISCONNECTED)->count(),
            'ig_active' => $igAccounts->where('is_active', true)->where('token_expires_at', '>', now())->count(),
            'ig_expired' => $igAccounts->where('is_active', true)->where(function($q) {
                $q->whereNull('token_expires_at')->orWhere('token_expires_at', '<=', now());
            })->count(),
            'orphaned_wa' => $waDevices->whereNull('user_id')->count(),
            'orphaned_ig' => $igAccounts->whereNull('user_id')->count(),
        ];

        return view('admin.devices.index', compact('waDevices', 'igAccounts', 'stats'));
    }

    public function waReconnect(Request $request, $sessionId)
    {
        $this->checkAuthorization();

        try {
            $device = WhatsAppDevice::where('session_id', $sessionId)->firstOrFail();
            
            // Call WA service to reconnect
            $waServiceUrl = config('services.whatsapp.service_url', 'http://127.0.0.1:3001');
            $response = Http::timeout(10)->post("{$waServiceUrl}/reconnect", [
                'session_id' => $sessionId
            ]);

            if ($response->successful()) {
                $device->update(['status' => WhatsAppDevice::STATUS_CONNECTING]);
                
                \App\Models\AdminActivityLog::log(
                    auth()->guard('admin')->user(),
                    'wa_reconnect',
                    "Initiated WA reconnect for session: {$sessionId}",
                    ['session_id' => $sessionId, 'device_id' => $device->id],
                    null
                );

                return back()->with('success', 'Reconnect initiated. Device status updated to connecting.');
            }

            return back()->with('error', 'Failed to initiate reconnect. WA service returned: ' . $response->body());
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function waDisconnect(Request $request, $sessionId)
    {
        $this->checkAuthorization();

        try {
            $device = WhatsAppDevice::where('session_id', $sessionId)->firstOrFail();
            
            // Call WA service to disconnect
            $waServiceUrl = config('services.whatsapp.service_url', 'http://127.0.0.1:3001');
            $response = Http::timeout(10)->post("{$waServiceUrl}/disconnect", [
                'session_id' => $sessionId
            ]);

            $device->update(['status' => WhatsAppDevice::STATUS_DISCONNECTED]);
            
            \App\Models\AdminActivityLog::log(
                auth()->guard('admin')->user(),
                'wa_disconnect',
                "Disconnected WA session: {$sessionId}",
                ['session_id' => $sessionId, 'device_id' => $device->id],
                null
            );

            return back()->with('success', 'Device disconnected successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function waDestroy($sessionId)
    {
        $this->checkAuthorization();

        try {
            $device = WhatsAppDevice::where('session_id', $sessionId)->firstOrFail();
            
            // Try to logout from WA service first
            try {
                $waServiceUrl = config('services.whatsapp.service_url', 'http://127.0.0.1:3001');
                Http::timeout(5)->post("{$waServiceUrl}/logout", [
                    'session_id' => $sessionId
                ]);
            } catch (\Exception $e) {
                // Ignore error, continue with deletion
            }

            $device->delete();
            
            \App\Models\AdminActivityLog::log(
                auth()->guard('admin')->user(),
                'wa_delete',
                "Deleted WA device: {$sessionId}",
                ['session_id' => $sessionId],
                null
            );

            return back()->with('success', 'WhatsApp device deleted permanently.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function igReconnect($id)
    {
        $this->checkAuthorization();

        try {
            $account = InstagramAccount::findOrFail($id);
            
            // Redirect to Instagram OAuth
            return redirect()->route('instagram.connect', ['account_id' => $id]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function igDisconnect($id)
    {
        $this->checkAuthorization();

        try {
            $account = InstagramAccount::findOrFail($id);
            
            $account->update([
                'access_token' => null,
                'token_expires_at' => null,
                'is_active' => false,
            ]);
            
            \App\Models\AdminActivityLog::log(
                auth()->guard('admin')->user(),
                'ig_disconnect',
                "Disconnected IG account: {$account->username}",
                ['account_id' => $id, 'username' => $account->username],
                null
            );

            return back()->with('success', 'Instagram account disconnected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function igDestroy($id)
    {
        $this->checkAuthorization();

        try {
            $account = InstagramAccount::findOrFail($id);
            $username = $account->username;
            
            $account->delete();
            
            \App\Models\AdminActivityLog::log(
                auth()->guard('admin')->user(),
                'ig_delete',
                "Deleted IG account: {$username}",
                ['account_id' => $id, 'username' => $username],
                null
            );

            return back()->with('success', 'Instagram account deleted permanently.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function waGetQrCode(Request $request, $sessionId)
    {
        $this->checkAuthorization();

        try {
            $waServiceUrl = config('services.whatsapp.service_url', 'http://127.0.0.1:3001');
            $response = Http::timeout(10)->get("{$waServiceUrl}/qr/{$sessionId}");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'Failed to get QR code'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cleanupOrphaned()
    {
        $this->checkAuthorization();

        try {
            $waDeleted = WhatsAppDevice::whereNull('user_id')->delete();
            $igDeleted = InstagramAccount::whereNull('user_id')->delete();
            $total = $waDeleted + $igDeleted;

            if ($total > 0) {
                \App\Models\AdminActivityLog::log(
                    auth()->guard('admin')->user(),
                    'cleanup_orphaned_devices',
                    "Cleaned up {$total} orphaned devices (WA: {$waDeleted}, IG: {$igDeleted})",
                    ['wa_deleted' => $waDeleted, 'ig_deleted' => $igDeleted],
                    null
                );
                return back()->with('success', "Cleaned up {$total} orphaned devices.");
            }

            return back()->with('info', 'No orphaned devices found.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request)
    {
        $this->checkAuthorization();

        $validated = $request->validate([
            'action' => 'required|in:reconnect,disconnect,delete',
            'devices' => 'required|array',
            'devices.*' => 'string',
            'type' => 'required|in:wa,ig',
        ]);

        $action = $validated['action'];
        $devices = $validated['devices'];
        $type = $validated['type'];
        $success = 0;
        $failed = 0;

        foreach ($devices as $deviceId) {
            try {
                if ($type === 'wa') {
                    $device = WhatsAppDevice::where('session_id', $deviceId)->first();
                    if (!$device) continue;

                    switch ($action) {
                        case 'disconnect':
                            $this->waDisconnect(new Request(), $deviceId);
                            $success++;
                            break;
                        case 'delete':
                            $this->waDestroy($deviceId);
                            $success++;
                            break;
                        case 'reconnect':
                            $this->waReconnect(new Request(), $deviceId);
                            $success++;
                            break;
                    }
                } else {
                    $account = InstagramAccount::find($deviceId);
                    if (!$account) continue;

                    switch ($action) {
                        case 'disconnect':
                            $this->igDisconnect($deviceId);
                            $success++;
                            break;
                        case 'delete':
                            $this->igDestroy($deviceId);
                            $success++;
                            break;
                        case 'reconnect':
                            // For IG, redirect to OAuth
                            $success++;
                            break;
                    }
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return back()->with('success', "Bulk action completed. Success: {$success}, Failed: {$failed}");
    }
}
