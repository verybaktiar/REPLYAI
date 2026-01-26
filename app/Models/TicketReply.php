<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model: Balasan Tiket
 * 
 * Model untuk menyimpan balasan/reply pada tiket support.
 * Bisa dari customer, admin, atau sistem.
 */
class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'sender_type',
        'sender_id',
        'message',
        'attachments',
        'is_read',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read' => 'boolean',
    ];

    const SENDER_CUSTOMER = 'customer';
    const SENDER_ADMIN = 'admin';
    const SENDER_SYSTEM = 'system';

    // ==========================================
    // RELASI
    // ==========================================

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function isFromCustomer(): bool
    {
        return $this->sender_type === self::SENDER_CUSTOMER;
    }

    public function isFromAdmin(): bool
    {
        return $this->sender_type === self::SENDER_ADMIN;
    }

    public function isFromSystem(): bool
    {
        return $this->sender_type === self::SENDER_SYSTEM;
    }

    public function getSenderLabelAttribute(): string
    {
        return match($this->sender_type) {
            self::SENDER_CUSTOMER => 'Anda',
            self::SENDER_ADMIN => 'Tim Support',
            self::SENDER_SYSTEM => 'Sistem',
            default => 'Unknown',
        };
    }
}
