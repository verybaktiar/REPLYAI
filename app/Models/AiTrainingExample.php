<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;

class AiTrainingExample extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'business_profile_id',
        'message_id',
        'user_query',
        'assistant_response',
        'rating',
        'is_approved',
    ];

    /**
     * Get the business profile that owns the training example.
     */
    public function businessProfile()
    {
        return $this->belongsTo(BusinessProfile::class);
    }
}
