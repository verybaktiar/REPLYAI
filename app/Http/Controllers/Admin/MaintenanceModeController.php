<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Controller untuk Maintenance Mode Management
 */
class MaintenanceModeController extends Controller
{
    /**
     * Tampilkan halaman maintenance mode settings
     */
    public function index()
    {
        $settings = [
            'enabled' => (bool) SystemSetting::get('maintenance_mode_enabled', false),
            'message' => SystemSetting::get('maintenance_mode_message', 'We are currently performing maintenance. Please check back soon.'),
            'countdown_enabled' => (bool) SystemSetting::get('maintenance_countdown_enabled', false),
            'countdown_end' => SystemSetting::get('maintenance_countdown_end', null),
            'allowed_ips' => json_decode(SystemSetting::get('maintenance_allowed_ips', '[]'), true) ?: [],
        ];

        return view('admin.maintenance.index', compact('settings'));
    }

    /**
     * Enable maintenance mode
     */
    public function enable(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'countdown_enabled' => 'boolean',
            'countdown_end' => 'nullable|date|after:now',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
        ]);

        // Save settings
        SystemSetting::set('maintenance_mode_enabled', '1', [
            'group' => 'maintenance',
            'type' => 'boolean',
            'label' => 'Maintenance Mode Enabled',
        ]);

        SystemSetting::set('maintenance_mode_message', $validated['message'], [
            'group' => 'maintenance',
            'type' => 'text',
            'label' => 'Maintenance Message',
        ]);

        SystemSetting::set('maintenance_countdown_enabled', $validated['countdown_enabled'] ? '1' : '0', [
            'group' => 'maintenance',
            'type' => 'boolean',
            'label' => 'Countdown Enabled',
        ]);

        if (!empty($validated['countdown_end'])) {
            SystemSetting::set('maintenance_countdown_end', $validated['countdown_end'], [
                'group' => 'maintenance',
                'type' => 'datetime',
                'label' => 'Countdown End Time',
            ]);
        }

        // Save allowed IPs
        $existingIps = json_decode(SystemSetting::get('maintenance_allowed_ips', '[]'), true) ?: [];
        if (!empty($validated['allowed_ips'])) {
            $existingIps = array_unique(array_merge($existingIps, $validated['allowed_ips']));
        }
        
        SystemSetting::set('maintenance_allowed_ips', json_encode($existingIps), [
            'group' => 'maintenance',
            'type' => 'json',
            'label' => 'Allowed IPs',
        ]);

        // Clear cache
        Cache::forget('maintenance_mode_enabled');
        Cache::forget('maintenance_mode_settings');

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'maintenance_enable',
            'Enabled maintenance mode',
            [
                'message' => $validated['message'],
                'countdown_enabled' => $validated['countdown_enabled'] ?? false,
                'countdown_end' => $validated['countdown_end'] ?? null,
            ]
        );

        return back()->with('success', 'Maintenance mode enabled successfully!');
    }

    /**
     * Disable maintenance mode
     */
    public function disable()
    {
        SystemSetting::set('maintenance_mode_enabled', '0', [
            'group' => 'maintenance',
            'type' => 'boolean',
            'label' => 'Maintenance Mode Enabled',
        ]);

        // Clear countdown
        SystemSetting::set('maintenance_countdown_enabled', '0');

        // Clear cache
        Cache::forget('maintenance_mode_enabled');
        Cache::forget('maintenance_mode_settings');

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'maintenance_disable',
            'Disabled maintenance mode'
        );

        return back()->with('success', 'Maintenance mode disabled successfully!');
    }

    /**
     * Add IP to whitelist
     */
    public function whitelistIp(Request $request)
    {
        $validated = $request->validate([
            'ip' => 'required|ip',
            'description' => 'nullable|string|max:255',
        ]);

        $allowedIps = json_decode(SystemSetting::get('maintenance_allowed_ips', '[]'), true) ?: [];
        
        // Check if IP already exists
        $exists = false;
        foreach ($allowedIps as $item) {
            if (is_array($item) && $item['ip'] === $validated['ip']) {
                $exists = true;
                break;
            } elseif ($item === $validated['ip']) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            return back()->with('error', 'IP address is already whitelisted!');
        }

        // Add new IP with metadata
        $allowedIps[] = [
            'ip' => $validated['ip'],
            'description' => $validated['description'] ?? '',
            'added_by' => Auth::guard('admin')->user()->name,
            'added_at' => now()->toIso8601String(),
        ];

        SystemSetting::set('maintenance_allowed_ips', json_encode($allowedIps), [
            'group' => 'maintenance',
            'type' => 'json',
            'label' => 'Allowed IPs',
        ]);

        // Clear cache
        Cache::forget('maintenance_mode_settings');

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'maintenance_whitelist_add',
            'Added IP to maintenance whitelist',
            ['ip' => $validated['ip']]
        );

        return back()->with('success', "IP {$validated['ip']} has been whitelisted!");
    }

    /**
     * Remove IP from whitelist
     */
    public function removeWhitelistIp(Request $request, $ip)
    {
        $allowedIps = json_decode(SystemSetting::get('maintenance_allowed_ips', '[]'), true) ?: [];
        
        // Filter out the IP to remove
        $filteredIps = array_values(array_filter($allowedIps, function ($item) use ($ip) {
            if (is_array($item)) {
                return $item['ip'] !== $ip;
            }
            return $item !== $ip;
        }));

        SystemSetting::set('maintenance_allowed_ips', json_encode($filteredIps), [
            'group' => 'maintenance',
            'type' => 'json',
            'label' => 'Allowed IPs',
        ]);

        // Clear cache
        Cache::forget('maintenance_mode_settings');

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'maintenance_whitelist_remove',
            'Removed IP from maintenance whitelist',
            ['ip' => $ip]
        );

        return back()->with('success', "IP {$ip} has been removed from whitelist!");
    }

    /**
     * Get current maintenance settings (for API)
     */
    public function getSettings()
    {
        return response()->json([
            'enabled' => (bool) SystemSetting::get('maintenance_mode_enabled', false),
            'message' => SystemSetting::get('maintenance_mode_message', 'We are currently performing maintenance. Please check back soon.'),
            'countdown_enabled' => (bool) SystemSetting::get('maintenance_countdown_enabled', false),
            'countdown_end' => SystemSetting::get('maintenance_countdown_end', null),
        ]);
    }
}
