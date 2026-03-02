<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToUser;

/**
 * Class WaBroadcast
 * 
 * Model untuk mengelola broadcast WhatsApp.
 * 
 * SECURITY NOTES:
 * - user_id TIDAK ADA di $fillable untuk mencegah mass assignment attack
 * - user_id di-set otomatis via BelongsToUser trait
 * - Selalu gunakan :where() dengan user_id untuk query
 */
class WaBroadcast extends Model
{
    use BelongsToUser;

    /**
     * The attributes that are mass assignable.
     * 
     * SECURITY: user_id sengaja DIHAPUS dari fillable untuk mencegah injection.
     * user_id akan di-set otomatis via BelongsToUser trait saat creating.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'message',
        'media_path',
        'status',
        'scheduled_at',
        'filters'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'array',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Get the targets for the broadcast.
     *
     * @return HasMany
     */
    public function targets(): HasMany
    {
        return $this->hasMany(WaBroadcastTarget::class);
    }

    /**
     * Scope untuk filter berdasarkan user (additional safety)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Boot the model.
     * Additional security: Ensure user_id is never mass assigned
     */
    protected static function boot()
    {
        parent::boot();

        // SECURITY: Force user_id dari auth jika tersedia
        static::creating(function ($model) {
            if (auth()->check() && empty($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });

        // SECURITY: Prevent user_id changes on update
        static::updating(function ($model) {
            if ($model->isDirty('user_id')) {
                // Revert user_id change
                $model->user_id = $model->getOriginal('user_id');
            }
        });
    }
}
