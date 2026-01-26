<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'user_id', 'payment_id', 'amount', 'status', 
        'reason', 'admin_notes', 'processed_by', 'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function processor() { return $this->belongsTo(Admin::class, 'processed_by'); }

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
}
