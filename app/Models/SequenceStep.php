<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToUser;

/**
 * Model SequenceStep
 * 
 * Merepresentasikan satu langkah/pesan dalam sequence.
 * Setiap step memiliki delay dan konten pesan yang akan dikirim.
 */
class SequenceStep extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'sequence_id',
        'order',
        'delay_type',
        'delay_value',
        'message_content',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'delay_value' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Tipe delay yang tersedia
     */
    public const DELAY_TYPES = [
        'immediately' => 'Langsung',
        'minutes' => 'Menit',
        'hours' => 'Jam',
        'days' => 'Hari',
    ];

    /**
     * Relasi: Step milik Sequence
     */
    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }

    /**
     * Hitung delay dalam detik
     * Berguna untuk menjadwalkan pengiriman pesan
     */
    public function getDelayInSeconds(): int
    {
        return match ($this->delay_type) {
            'immediately' => 0,
            'minutes' => $this->delay_value * 60,
            'hours' => $this->delay_value * 3600,
            'days' => $this->delay_value * 86400,
            default => 0,
        };
    }

    /**
     * Get label delay yang mudah dibaca
     * Contoh: "5 Menit", "2 Jam", "1 Hari"
     */
    public function getDelayLabelAttribute(): string
    {
        if ($this->delay_type === 'immediately') {
            return 'Langsung';
        }

        $typeLabel = self::DELAY_TYPES[$this->delay_type] ?? $this->delay_type;
        return "{$this->delay_value} {$typeLabel}";
    }

    /**
     * Get step selanjutnya dalam sequence yang sama
     */
    public function getNextStep(): ?SequenceStep
    {
        return SequenceStep::where('sequence_id', $this->sequence_id)
            ->where('order', '>', $this->order)
            ->where('is_active', true)
            ->orderBy('order')
            ->first();
    }

    /**
     * Cek apakah ini step terakhir
     */
    public function isLastStep(): bool
    {
        return $this->getNextStep() === null;
    }

    /**
     * Get step sebelumnya
     */
    public function getPreviousStep(): ?SequenceStep
    {
        return SequenceStep::where('sequence_id', $this->sequence_id)
            ->where('order', '<', $this->order)
            ->where('is_active', true)
            ->orderByDesc('order')
            ->first();
    }

    /**
     * Scope: Hanya step yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
