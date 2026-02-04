<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Traits\BelongsToUser;

class Tag extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = ['user_id', 'name', 'color'];

    /**
     * Get all of the conversations that are assigned this tag.
     */
    public function conversations(): MorphToMany
    {
        return $this->morphedByMany(WaConversation::class, 'taggable');
    }
}
