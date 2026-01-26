<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminActivityLog;

class AdminFeatureFlagController extends Controller
{
    /**
     * Tampilkan halaman pengelolaan feature flags
     */
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('label')->get();
        $groups = $settings->groupBy('group');

        return view('admin.settings.features', compact('groups'));
    }

    /**
     * Update nilai setting (toggle)
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                $oldValue = $setting->value;
                $setting->update(['value' => $value]);
                
                // Log jika ada perubahan
                if ($oldValue != $value) {
                    AdminActivityLog::log(
                        Auth::guard('admin')->user(),
                        'update_feature_flag',
                        "Update setting {$setting->label} dari '{$oldValue}' ke '{$value}'",
                        ['key' => $key, 'old' => $oldValue, 'new' => $value],
                        $setting
                    );
                }
            }
        }

        // Clear Cache
        SystemSetting::clearCache();

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui!');
    }

    /**
     * Update single toggle via AJAX
     */
    public function toggle(Request $request, SystemSetting $setting)
    {
        $oldValue = $setting->value;
        $newValue = $oldValue == '1' ? '0' : '1';
        
        $setting->update(['value' => $newValue]);
        SystemSetting::clearCache();

        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'toggle_feature_flag',
            "Toggle setting {$setting->label} menjadi " . ($newValue == '1' ? 'ON' : 'OFF'),
            ['key' => $setting->key, 'value' => $newValue],
            $setting
        );

        return response()->json([
            'success' => true,
            'new_value' => $newValue,
            'message' => "Setting {$setting->label} diperbarui."
        ]);
    }
}
