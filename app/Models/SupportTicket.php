<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToUser;

/**
 * Model: Tiket Support
 * 
 * Model untuk tiket bantuan dari pelanggan.
 * Pelanggan bisa submit tiket saat ada masalah, admin akan dapat notifikasi.
 * 
 * Alur:
 * 1. Pelanggan submit tiket → status: open
 * 2. Admin mulai handle → status: in_progress
 * 3. Admin balas → status: waiting_customer
 * 4. Selesai → status: resolved
 * 5. Ditutup → status: closed
 */
class SupportTicket extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'category',
        'subject',
        'message',
        'attachments',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'closed_at',
        'rating',
        'feedback',
    ];

    protected $casts = [
        'attachments' => 'array',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Konstanta kategori
    const CATEGORY_BOT = 'bot_not_responding';
    const CATEGORY_WHATSAPP = 'whatsapp_issue';
    const CATEGORY_PAYMENT = 'payment';
    const CATEGORY_BUG = 'feature_bug';
    const CATEGORY_REQUEST = 'feature_request';
    const CATEGORY_OTHER = 'other';

    // Konstanta prioritas
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Konstanta status
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_WAITING = 'waiting_customer';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    // ==========================================
    // RELASI
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')
            ->orderBy('created_at', 'asc');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [self::STATUS_IN_PROGRESS, self::STATUS_WAITING]);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', [self::STATUS_RESOLVED, self::STATUS_CLOSED]);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Generate nomor tiket baru
     */
    public static function generateTicketNumber(): string
    {
        $year = date('Y');
        $lastTicket = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastTicket 
            ? (int) substr($lastTicket->ticket_number, -5) + 1 
            : 1;
        
        return sprintf('TKT-%s-%05d', $year, $nextNumber);
    }

    /**
     * Ambil label kategori dalam bahasa Indonesia
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_BOT => 'Bot Tidak Merespon',
            self::CATEGORY_WHATSAPP => 'Masalah WhatsApp',
            self::CATEGORY_PAYMENT => 'Pembayaran',
            self::CATEGORY_BUG => 'Bug/Fitur Error',
            self::CATEGORY_REQUEST => 'Permintaan Fitur',
            self::CATEGORY_OTHER => 'Lainnya',
            default => $this->category,
        };
    }

    /**
     * Ambil label prioritas
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Rendah',
            self::PRIORITY_MEDIUM => 'Sedang',
            self::PRIORITY_HIGH => 'Tinggi',
            self::PRIORITY_URGENT => 'Urgent',
            default => $this->priority,
        };
    }

    /**
     * Ambil label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'Menunggu',
            self::STATUS_IN_PROGRESS => 'Sedang Diproses',
            self::STATUS_WAITING => 'Menunggu Balasan Anda',
            self::STATUS_RESOLVED => 'Selesai',
            self::STATUS_CLOSED => 'Ditutup',
            default => $this->status,
        };
    }

    /**
     * Ambil warna badge status
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'red',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_WAITING => 'blue',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_CLOSED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Ambil warna badge prioritas
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_MEDIUM => 'blue',
            self::PRIORITY_HIGH => 'yellow',
            self::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    /**
     * Daftar kategori untuk dropdown
     */
    public static function getCategoryOptions(): array
    {
        return [
            self::CATEGORY_BOT => 'Bot tidak merespon',
            self::CATEGORY_WHATSAPP => 'Masalah WhatsApp (disconnect, dll)',
            self::CATEGORY_PAYMENT => 'Pembayaran',
            self::CATEGORY_BUG => 'Bug / Fitur tidak berfungsi',
            self::CATEGORY_REQUEST => 'Permintaan fitur baru',
            self::CATEGORY_OTHER => 'Lainnya',
        ];
    }

    /**
     * Daftar prioritas untuk dropdown
     */
    public static function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Rendah - Bisa ditangani kapan saja',
            self::PRIORITY_MEDIUM => 'Sedang - Perlu ditangani segera',
            self::PRIORITY_HIGH => 'Tinggi - Mengganggu operasional',
            self::PRIORITY_URGENT => 'Urgent - Bisnis terhenti total',
        ];
    }
}
