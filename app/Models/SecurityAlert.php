<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecurityAlert extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'type',
        'ip_address',
        'email_attempted',
        'user_agent',
        'url',
        'country',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'notes',
    ];
    
    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];
    
    /**
     * Scope for unresolved alerts.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }
    
    /**
     * Scope for recent alerts.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
    
    /**
     * Get the admin who resolved this alert.
     */
    public function resolver()
    {
        return $this->belongsTo(AdminUser::class, 'resolved_by');
    }
    
    /**
     * Mark alert as resolved.
     */
    public function resolve(int $adminId, ?string $notes = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_by' => $adminId,
            'resolved_at' => now(),
            'notes' => $notes,
        ]);
    }
    
    /**
     * Get alert type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'unauthorized_admin_access' => 'Akses Admin Tidak Sah',
            'brute_force_attempt' => 'Percobaan Brute Force',
            'suspicious_activity' => 'Aktivitas Mencurigakan',
            'failed_2fa' => 'Gagal 2FA',
            default => $this->type,
        };
    }
}
