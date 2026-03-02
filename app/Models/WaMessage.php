<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\BelongsToUser;

/**
 * Class WaMessage
 * 
 * Model untuk menyimpan pesan WhatsApp.
 * 
 * SECURITY NOTES:
 * - user_id TIDAK ADA di $fillable
 * - user_id di-set otomatis saat creating
 * - direction hanya bisa 'incoming' atau 'outgoing'
 */
class WaMessage extends Model
{
    use BelongsToUser;

    /**
     * The attributes that are mass assignable.
     * 
     * SECURITY: user_id dihapus dari fillable
     *
     * @var array<string>
     */
    protected $fillable = [
        'session_id',
        'wa_message_id',
        'remote_jid',
        'phone_number',
        'push_name',
        'direction',
        'message',
        'message_type',
        'status',
        'bot_reply',
        'metadata',
        'wa_timestamp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'wa_timestamp' => 'datetime',
    ];

    /**
     * Valid direction values
     *
     * @var array<string>
     */
    public const VALID_DIRECTIONS = ['incoming', 'outgoing', 'inbound', 'outbound'];

    /**
     * Get the device that received/sent this message.
     *
     * @return BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(WhatsAppDevice::class, 'session_id', 'session_id');
    }

    /**
     * Get the conversation for this message.
     *
     * @return BelongsTo
     */
    public function waConversation(): BelongsTo
    {
        return $this->belongsTo(WaConversation::class, 'phone_number', 'phone_number');
    }

    /**
     * Get media attachments for this message
     */
    public function media()
    {
        return $this->morphMany(ChatMedia::class, 'message');
    }

    /**
     * Scope for incoming messages
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    /**
     * Scope for outgoing messages
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    /**
     * Scope for messages from a specific phone number
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $phone
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromPhone($query, string $phone)
    {
        return $query->where('phone_number', $phone);
    }

    /**
     * Get formatted phone number (with +)
     *
     * @return string
     */
    public function getFormattedPhoneAttribute(): string
    {
        return '+' . $this->phone_number;
    }

    /**
     * Check if message is from user (incoming)
     *
     * @return bool
     */
    public function isFromUser(): bool
    {
        return $this->direction === 'incoming' || $this->direction === 'inbound';
    }

    /**
     * Check if message is from bot (outgoing)
     *
     * @return bool
     */
    public function isFromBot(): bool
    {
        return $this->direction === 'outgoing' || $this->direction === 'outbound';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // SECURITY: Validate direction values
        static::creating(function ($model) {
            // Normalize direction values
            if (in_array($model->direction, ['inbound', 'incoming'])) {
                $model->direction = 'incoming';
            } elseif (in_array($model->direction, ['outbound', 'outgoing'])) {
                $model->direction = 'outgoing';
            }

            // SECURITY: Set user_id from device owner jika belum di-set
            if (empty($model->user_id) && !empty($model->session_id)) {
                $device = WhatsAppDevice::where('session_id', $model->session_id)->first();
                if ($device) {
                    $model->user_id = $device->user_id;
                }
            }
        });

        // SECURITY: Prevent user_id changes
        static::updating(function ($model) {
            if ($model->isDirty('user_id')) {
                $model->user_id = $model->getOriginal('user_id');
            }
        });
    }
}
