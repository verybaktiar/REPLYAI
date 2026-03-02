<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'settings',
        'subscription_id',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the owner of the team.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all members of the team.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('role', 'permissions', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get the subscription for the team.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if user is a member of this team.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is the owner.
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Add a member to the team.
     */
    public function addMember(User $user, string $role = 'member', array $permissions = []): void
    {
        $this->members()->attach($user->id, [
            'role' => $role,
            'permissions' => json_encode($permissions),
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * Update member role.
     */
    public function updateMemberRole(User $user, string $role, array $permissions = []): void
    {
        $this->members()->updateExistingPivot($user->id, [
            'role' => $role,
            'permissions' => json_encode($permissions),
        ]);
    }
}
