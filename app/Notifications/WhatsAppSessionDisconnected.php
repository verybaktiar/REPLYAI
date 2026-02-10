<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Models\WhatsAppDevice;

class WhatsAppSessionDisconnected extends Notification
{
    use Queueable;

    protected $device;

    public function __construct(WhatsAppDevice $device)
    {
        $this->device = $device;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('WhatsApp Session Terputus - ' . config('app.name'))
            ->greeting('Halo Admin,')
            ->line("Session WhatsApp untuk perangkat {$this->device->session_name} terputus.")
            ->line('Sistem telah mencoba untuk reconnect secara otomatis.')
            ->action('Cek Status', url('/whatsapp-devices'))
            ->line('Jika reconnect gagal, silakan scan QR code kembali.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'WhatsApp Session Terputus',
            'message' => "Session {$this->device->session_name} terputus. Sistem mencoba reconnect otomatis.",
            'device_id' => $this->device->id,
            'session_id' => $this->device->session_id,
            'type' => 'whatsapp_disconnected',
            'action_url' => '/whatsapp-devices',
        ];
    }
}
