<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Traits\BelongsToUser;

/**
 * Model SequenceEnrollment
 * 
 * Merepresentasikan pendaftaran seorang user/kontak dalam sequence.
 * Tracking progress dan jadwal pengiriman pesan.
 */
class SequenceEnrollment extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'sequence_id',
        'contact_identifier',
        'contact_name',
        'platform',
        'current_step_id',
        'status',
        'enrolled_at',
        'completed_at',
        'next_run_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * Status yang tersedia
     */
    public const STATUSES = [
        'active' => 'Aktif',
        'completed' => 'Selesai',
        'paused' => 'Dijeda',
        'cancelled' => 'Dibatalkan',
    ];

    /**
     * Relasi: Enrollment milik Sequence
     */
    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }

    /**
     * Relasi: Step saat ini
     */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(SequenceStep::class, 'current_step_id');
    }

    /**
     * Scope: Enrollment yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Enrollment yang siap diproses (waktunya sudah tiba)
     */
    public function scopeReadyToProcess($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now());
    }

    /**
     * Pindah ke step selanjutnya
     * Return false jika tidak ada step selanjutnya (sequence selesai)
     */
    public function advanceToNextStep(): bool
    {
        $currentStep = $this->currentStep;
        
        if (!$currentStep) {
            // Jika belum ada current step, ambil step pertama
            $nextStep = $this->sequence->getFirstStep();
        } else {
            // Ambil step selanjutnya
            $nextStep = $currentStep->getNextStep();
        }

        if (!$nextStep) {
            // Tidak ada step selanjutnya = sequence selesai
            $this->markAsCompleted();
            return false;
        }

        // Set step selanjutnya dan jadwalkan
        $this->current_step_id = $nextStep->id;
        $this->next_run_at = now()->addSeconds($nextStep->getDelayInSeconds());
        $this->save();

        return true;
    }

    /**
     * Tandai sebagai selesai
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'next_run_at' => null,
        ]);

        // Update statistik sequence
        $this->sequence->incrementCompleted();
    }

    /**
     * Jeda sequence untuk user ini
     */
    public function pause(): void
    {
        $this->update([
            'status' => 'paused',
            'next_run_at' => null,
        ]);
    }

    /**
     * Lanjutkan sequence yang dijeda
     */
    public function resume(): void
    {
        if ($this->status !== 'paused') {
            return;
        }

        $currentStep = $this->currentStep;
        $delay = $currentStep ? $currentStep->getDelayInSeconds() : 0;

        $this->update([
            'status' => 'active',
            'next_run_at' => now()->addSeconds($delay),
        ]);
    }

    /**
     * Batalkan sequence untuk user ini
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'next_run_at' => null,
        ]);
    }

    /**
     * Get label status
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get nomor step saat ini (1-indexed)
     */
    public function getCurrentStepNumberAttribute(): int
    {
        if (!$this->current_step_id) {
            return 0;
        }

        return $this->sequence->steps()
            ->where('order', '<=', $this->currentStep->order)
            ->count();
    }

    /**
     * Get total steps dalam sequence
     */
    public function getTotalStepsAttribute(): int
    {
        return $this->sequence->steps()->count();
    }

    /**
     * Get progress dalam persen
     */
    public function getProgressPercentAttribute(): int
    {
        $total = $this->total_steps;
        if ($total === 0) {
            return 0;
        }

        if ($this->status === 'completed') {
            return 100;
        }

        return (int) round(($this->current_step_number / $total) * 100);
    }
}
