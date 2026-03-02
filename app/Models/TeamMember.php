<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamMember extends Pivot
{
    protected $table = 'team_members';

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'joined_at' => 'datetime',
    ];

    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_AGENT = 'agent';
    const ROLE_VIEWER = 'viewer';

    /**
     * Available permissions.
     */
    const AVAILABLE_PERMISSIONS = [
        'view_conversations',
        'reply_conversations',
        'assign_conversations',
        'manage_contacts',
        'manage_segments',
        'manage_broadcasts',
        'manage_sequences',
        'view_analytics',
        'manage_settings',
        'manage_team',
        'manage_billing',
        'manage_knowledge_base',
        'manage_automations',
    ];

    /**
     * Get the team.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if member has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Owner has all permissions
        if ($this->role === self::ROLE_OWNER) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Check if member has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if member is at least a certain role level.
     */
    public function isAtLeast(string $role): bool
    {
        $hierarchy = [
            self::ROLE_OWNER => 5,
            self::ROLE_ADMIN => 4,
            self::ROLE_MANAGER => 3,
            self::ROLE_AGENT => 2,
            self::ROLE_VIEWER => 1,
        ];

        return ($hierarchy[$this->role] ?? 0) >= ($hierarchy[$role] ?? 0);
    }

    /**
     * Get role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            self::ROLE_OWNER => 'Owner',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_AGENT => 'Agent',
            self::ROLE_VIEWER => 'Viewer',
            default => $this->role,
        };
    }

    /**
     * Get default permissions for a role.
     */
    public static function getDefaultPermissions(string $role): array
    {
        return match($role) {
            self::ROLE_ADMIN => [
                'view_conversations',
                'reply_conversations',
                'assign_conversations',
                'manage_contacts',
                'manage_segments',
                'manage_broadcasts',
                'manage_sequences',
                'view_analytics',
                'manage_settings',
                'manage_team',
                'manage_knowledge_base',
                'manage_automations',
            ],
            self::ROLE_MANAGER => [
                'view_conversations',
                'reply_conversations',
                'assign_conversations',
                'manage_contacts',
                'manage_segments',
                'view_analytics',
                'manage_knowledge_base',
            ],
            self::ROLE_AGENT => [
                'view_conversations',
                'reply_conversations',
                'manage_contacts',
            ],
            self::ROLE_VIEWER => [
                'view_conversations',
                'view_analytics',
            ],
            default => [],
        };
    }
}
