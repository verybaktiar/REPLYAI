<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminActivityLog;

class AdminFeatureFlagController extends Controller
{
    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isSuperAdmin()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_feature_flag_access',
                'Attempted to access feature flags without superadmin privilege',
                ['url' => request()->fullUrl()],
                null,
                8
            );
            abort(403, 'Only Superadmin can manage feature flags.');
        }
    }

    /**
     * Tampilkan halaman pengelolaan feature flags
     */
    public function index()
    {
        $this->checkAuthorization();
        
        $settings = SystemSetting::orderBy('group')->orderBy('label')->get();
        $groups = $settings->groupBy('group');

        return view('admin.settings.features', compact('groups'));
    }

    /**
     * Update nilai setting (toggle)
     */
    public function update(Request $request)
    {
        $this->checkAuthorization();
        
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
        $this->checkAuthorization();
        
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
