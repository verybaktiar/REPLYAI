<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk System Settings
 */
class SystemSettingController extends Controller
{
    /**
     * Tampilkan halaman settings
     */
    public function index()
    {
        $groups = [
            'payment' => [
                'title' => 'Payment Gateway',
                'icon' => 'payments',
                'settings' => [
                    ['key' => 'midtrans_server_key', 'label' => 'Midtrans Server Key', 'type' => 'password'],
                    ['key' => 'midtrans_client_key', 'label' => 'Midtrans Client Key', 'type' => 'text'],
                    ['key' => 'midtrans_is_production', 'label' => 'Midtrans Production Mode', 'type' => 'boolean'],
                ]
            ],
            'whatsapp' => [
                'title' => 'WhatsApp API',
                'icon' => 'chat',
                'settings' => [
                    ['key' => 'fonnte_api_key', 'label' => 'Fonnte API Key', 'type' => 'password'],
                ]
            ],
            'ai' => [
                'title' => 'AI Configuration',
                'icon' => 'smart_toy',
                'settings' => [
                    ['key' => 'openai_api_key', 'label' => 'OpenAI API Key', 'type' => 'password'],
                    ['key' => 'ai_model', 'label' => 'AI Model', 'type' => 'text'],
                ]
            ],
            'system' => [
                'title' => 'System',
                'icon' => 'settings',
                'settings' => [
                    ['key' => 'maintenance_mode', 'label' => 'Maintenance Mode', 'type' => 'boolean'],
                    ['key' => 'maintenance_message', 'label' => 'Maintenance Message', 'type' => 'text'],
                ]
            ],
        ];

        // Load current values
        foreach ($groups as $groupKey => &$group) {
            foreach ($group['settings'] as &$setting) {
                $setting['value'] = SystemSetting::get($setting['key'], '');
            }
        }

        return view('admin.settings.index', compact('groups'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        $updated = [];

        foreach ($settings as $key => $value) {
            // Handle boolean
            if ($value === 'on' || $value === '1') {
                $value = '1';
            } elseif ($value === null && str_contains($key, 'mode')) {
                $value = '0';
            }

            SystemSetting::set($key, $value);
            $updated[$key] = $value;
        }

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'update_settings',
            'Update system settings',
            ['updated_keys' => array_keys($updated)]
        );

        return back()->with('success', 'Settings berhasil disimpan!');
    }
}
