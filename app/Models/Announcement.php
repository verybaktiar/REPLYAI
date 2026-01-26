<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'style',
        'audience',
        'is_active',
        'starts_at',
        'expires_at',
        'created_by',
        'email_sent_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get active banners for display
     */
    public static function activeBanners()
    {
        return self::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->whereIn('type', ['banner', 'both'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get banner style classes
     */
    public function getStyleClassesAttribute(): string
    {
        return match($this->style) {
            'success' => 'bg-green-500/20 border-green-500 text-green-400',
            'warning' => 'bg-yellow-500/20 border-yellow-500 text-yellow-400',
            'danger' => 'bg-red-500/20 border-red-500 text-red-400',
            default => 'bg-blue-500/20 border-blue-500 text-blue-400',
        };
    }
}
