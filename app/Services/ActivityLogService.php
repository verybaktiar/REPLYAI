<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log an activity
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        ?array $properties = null,
        string $severity = 'info'
    ): ActivityLog {
        $user = Auth::user();
        $isImpersonated = session()->has('impersonator_id');
        
        return ActivityLog::create([
            'user_id' => $user?->id,
            'admin_id' => $isImpersonated ? session('impersonator_id') : null,
            'action' => $action,
            'description' => $description,
            'model_type' => $subject ? get_class($subject) : null,
            'model_id' => $subject?->id,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'is_impersonated' => $isImpersonated,
            'severity' => $severity,
        ]);
    }

    /**
     * Log with success severity
     */
    public static function success(string $action, ?string $description = null, ?Model $subject = null, ?array $properties = null): ActivityLog
    {
        return self::log($action, $description, $subject, $properties, 'success');
    }

    /**
     * Log with warning severity
     */
    public static function warning(string $action, ?string $description = null, ?Model $subject = null, ?array $properties = null): ActivityLog
    {
        return self::log($action, $description, $subject, $properties, 'warning');
    }

    /**
     * Log with error severity
     */
    public static function error(string $action, ?string $description = null, ?Model $subject = null, ?array $properties = null): ActivityLog
    {
        return self::log($action, $description, $subject, $properties, 'error');
    }

    /**
     * Log user login
     */
    public static function logLogin(): void
    {
        self::success(ActivityLog::ACTION_LOGIN, 'User logged in');
    }

    /**
     * Log user logout
     */
    public static function logLogout(): void
    {
        self::log(ActivityLog::ACTION_LOGOUT, 'User logged out');
    }

    /**
     * Log model created
     */
    public static function logCreated(Model $model, ?string $description = null): void
    {
        $modelName = class_basename($model);
        $action = strtolower($modelName) . '.created';
        
        self::success($action, $description ?? "{$modelName} created", $model, [
            'attributes' => $model->getAttributes(),
        ]);
    }

    /**
     * Log model updated
     */
    public static function logUpdated(Model $model, ?string $description = null): void
    {
        $modelName = class_basename($model);
        $action = strtolower($modelName) . '.updated';
        
        self::log($action, $description ?? "{$modelName} updated", $model, [
            'old' => $model->getOriginal(),
            'new' => $model->getChanges(),
        ]);
    }

    /**
     * Log model deleted
     */
    public static function logDeleted(Model $model, ?string $description = null): void
    {
        $modelName = class_basename($model);
        $action = strtolower($modelName) . '.deleted';
        
        self::warning($action, $description ?? "{$modelName} deleted", $model, [
            'attributes' => $model->getAttributes(),
        ]);
    }

    /**
     * Log admin impersonation start
     */
    public static function logImpersonationStart(int $targetUserId): void
    {
        self::warning(ActivityLog::ACTION_IMPERSONATION_START, "Admin started impersonating user #{$targetUserId}", null, [
            'target_user_id' => $targetUserId,
        ]);
    }

    /**
     * Log admin impersonation end
     */
    public static function logImpersonationEnd(): void
    {
        self::log(ActivityLog::ACTION_IMPERSONATION_END, 'Admin stopped impersonating user');
    }
}
