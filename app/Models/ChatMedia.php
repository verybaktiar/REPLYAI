<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\BelongsToUser;

class ChatMedia extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'message_type',
        'message_id',
        'conversation_type',
        'conversation_id',
        'type',
        'mime_type',
        'filename',
        'url',
        'size',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Type constants
     */
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_DOCUMENT = 'document';
    const TYPE_VOICE = 'voice';
    const TYPE_STICKER = 'sticker';
    const TYPE_LOCATION = 'location';
    const TYPE_CONTACT = 'contact';

    /**
     * Get the user who owns this media
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the message (polymorphic)
     */
    public function message(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the conversation (polymorphic)
     */
    public function conversation(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for images
     */
    public function scopeImages($query)
    {
        return $query->where('type', self::TYPE_IMAGE);
    }

    /**
     * Scope for videos
     */
    public function scopeVideos($query)
    {
        return $query->where('type', self::TYPE_VIDEO);
    }

    /**
     * Scope for documents
     */
    public function scopeDocuments($query)
    {
        return $query->where('type', self::TYPE_DOCUMENT);
    }

    /**
     * Scope for audio files
     */
    public function scopeAudio($query)
    {
        return $query->whereIn('type', [self::TYPE_AUDIO, self::TYPE_VOICE]);
    }

    /**
     * Get human readable file size
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
