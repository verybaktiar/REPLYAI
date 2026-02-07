<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;

class AiLog extends Model
{
    protected $fillable = [
        'business_profile_id',
        'user_id',
        'user_message',
        'answer',
        'confidence',
        'source',
        'context_used',
        'input_tokens',
        'output_tokens',
    ];

    protected $casts = [
        'context_used' => 'array',
        'confidence' => 'float',
    ];

    public function businessProfile()
    {
        return $this->belongsTo(BusinessProfile::class);
    }
}
