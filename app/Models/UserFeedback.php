<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFeedback extends Model
{
    protected $table = 'user_feedback';

    protected $fillable = [
        'user_id', 'type', 'title', 'description', 
        'status', 'votes', 'admin_response', 'reviewed_by'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(Admin::class, 'reviewed_by'); }

    public function scopeNew($q) { return $q->where('status', 'new'); }
    public function scopePlanned($q) { return $q->where('status', 'planned'); }
    
    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'bug' => 'red',
            'feature' => 'blue',
            'improvement' => 'green',
            default => 'slate',
        };
    }
}
