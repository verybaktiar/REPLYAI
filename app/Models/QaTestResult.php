<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk menyimpan hasil QA Test
 */
class QaTestResult extends Model
{
    protected $fillable = [
        'scenario_id',
        'status',
        'notes',
        'tested_by',
        'tested_at',
    ];

    protected $casts = [
        'tested_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pass' => 'green',
            'fail' => 'red',
            'skip' => 'yellow',
            default => 'gray',
        };
    }
}
