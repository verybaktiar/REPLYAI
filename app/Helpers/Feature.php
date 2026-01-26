<?php

namespace App\Helpers;

use App\Models\SystemSetting;

class Feature
{
    /**
     * Check if a feature is enabled globally
     */
    public static function enabled(string $key): bool
    {
        return SystemSetting::get($key, '1') == '1';
    }

    /**
     * Check if a feature is disabled globally
     */
    public static function disabled(string $key): bool
    {
        return !self::enabled($key);
    }

    /**
     * Get a setting value
     */
    public static function setting(string $key, $default = null)
    {
        return SystemSetting::get($key, $default);
    }
}
