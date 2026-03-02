<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model: Admin Notification
 * 
 * Model untuk notifikasi real-time dan history untuk admin users.
 */
class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'is_read',
        'read_at',
        'priority',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Notification types
    const TYPE_PAYMENT = 'payment';
    const TYPE_SUPPORT = 'support';
    const TYPE_SECURITY = 'security';
    const TYPE_SYSTEM = 'system';
    const TYPE_USER = 'user';

    // ==========================================
    // RELASI
    // ==========================================

    public function admin()
    {
        return $this->belongsTo(AdminUser::class, 'admin_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where(function($q) use ($adminId) {
            $q->where('admin_id', $adminId)
              ->orWhereNull('admin_id'); // Global notifications
        });
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Create a new notification
     */
    public static function notify(
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?int $adminId = null,
        string $priority = self::PRIORITY_MEDIUM,
        ?array $data = null
    ): self {
        return self::create([
            'admin_id' => $adminId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'priority' => $priority,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Notify all admins or specific roles
     */
    public static function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?array $roles = null,
        ?string $actionUrl = null,
        string $priority = self::PRIORITY_MEDIUM,
        ?array $data = null
    ): void {
        $query = AdminUser::where('is_active', true);
        
        if ($roles) {
            $query->whereIn('role', $roles);
        }

        $admins = $query->get();

        foreach ($admins as $admin) {
            self::notify(
                $type,
                $title,
                $message,
                $actionUrl,
                $admin->id,
                $priority,
                $data
            );
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark all notifications as read for admin
     */
    public static function markAllAsRead(int $adminId): int
    {
        return self::forAdmin($adminId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function getIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PAYMENT => 'payments',
            self::TYPE_SUPPORT => 'support_agent',
            self::TYPE_SECURITY => 'security',
            self::TYPE_SYSTEM => 'settings',
            self::TYPE_USER => 'person',
            default => 'notifications',
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_URGENT => 'red',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_MEDIUM => 'blue',
            self::PRIORITY_LOW => 'gray',
            default => 'gray',
        };
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
