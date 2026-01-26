<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'user_id', 'to_email', 'subject', 'template', 
        'status', 'error_message', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public static function log($userId, $toEmail, $subject, $template = null, $status = 'sent', $error = null)
    {
        return self::create([
            'user_id' => $userId,
            'to_email' => $toEmail,
            'subject' => $subject,
            'template' => $template,
            'status' => $status,
            'error_message' => $error,
        ]);
    }
}
