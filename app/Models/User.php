<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Helpers\SubscriptionHelper;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_vip',
        'csat_enabled',
        'csat_instagram_enabled',
        'csat_whatsapp_enabled',
        'csat_message',
        'csat_delay_minutes',
        'onboarding_completed_at',
        'business_industry',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_vip' => 'boolean',
            'csat_enabled' => 'boolean',
            'csat_instagram_enabled' => 'boolean',
            'csat_whatsapp_enabled' => 'boolean',
            'csat_delay_minutes' => 'integer',
        ];
    }

    /**
     * Get the user's subscription.
     */
    public function subscription()
    {
        return $this->hasOne(\App\Models\Subscription::class);
    }

    /**
     * Get all payments for the user.
     */
    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    /**
     * Get all subscriptions for the user (including historical).
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    /**
     * Get the user's active subscription.
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    // ================================
    // Feature Access Helper Methods
    // ================================

    /**
     * Cek apakah user punya akses ke fitur tertentu.
     * VIP users bypass semua.
     */
    public function hasFeature(string $feature): bool
    {
        if ($this->is_vip) {
            return true;
        }
        return SubscriptionHelper::hasFeature($feature, $this->id);
    }

    /**
     * Cek apakah user masih bisa menggunakan fitur (limit belum habis).
     * VIP users bypass semua.
     */
    public function canUse(string $feature, int $amount = 1): bool
    {
        if ($this->is_vip) {
            return true;
        }
        return SubscriptionHelper::canUse($feature, $amount, $this->id);
    }

    /**
     * Ambil limit fitur untuk user ini.
     * VIP users mendapat null (unlimited).
     */
    public function getFeatureLimit(string $feature): ?int
    {
        if ($this->is_vip) {
            return null; // Unlimited untuk VIP
        }
        return SubscriptionHelper::getLimit($feature, $this->id);
    }

    /**
     * Ambil sisa kuota fitur.
     * VIP users mendapat null (unlimited).
     */
    public function getRemainingUsage(string $feature): ?int
    {
        if ($this->is_vip) {
            return null; // Unlimited untuk VIP
        }
        return SubscriptionHelper::getRemaining($feature, $this->id);
    }

    /**
     * Track penggunaan fitur.
     * VIP users tetap ditrack untuk analytics.
     */
    public function trackUsage(string $feature, int $amount = 1): bool
    {
        return SubscriptionHelper::use($feature, $amount, $this->id);
    }

    /**
     * Ambil paket langganan aktif.
     */
    public function getPlan()
    {
        return SubscriptionHelper::getPlan($this->id);
    }

    /**
     * Send the email verification notification.
     * Override default untuk menggunakan template custom REPLYAI
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification());
    }
}
