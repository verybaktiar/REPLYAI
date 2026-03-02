<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Http\Request;

/**
 * Class NewDeviceLoginNotification
 * 
 * Notifikasi email saat user login dari device/browser baru.
 * Security: Memberi tahu user jika ada aktivitas mencurigakan.
 */
class NewDeviceLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Request data untuk informasi login
     *
     * @var \Illuminate\Http\Request
     */
    private Request $request;

    /**
     * Waktu login
     *
     * @var \DateTime
     */
    private $loginTime;

    /**
     * Create a new notification instance.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->loginTime = now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ip = $this->request->ip();
        $userAgent = $this->request->userAgent();
        $browser = $this->getBrowser($userAgent);
        $os = $this->getOS($userAgent);
        $location = $this->getLocation($ip);

        return (new MailMessage)
            ->subject('🔐 Login Baru Terdeteksi di Akun ReplyAI Anda')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Kami mendeteksi login baru ke akun ReplyAI Anda dari device yang belum pernah digunakan sebelumnya.')
            ->line('')
            ->line('📱 **Detail Login:**')
            ->line('- **Waktu:** ' . $this->loginTime->format('d M Y H:i:s') . ' WIB')
            ->line('- **IP Address:** ' . $ip)
            ->line('- **Browser:** ' . $browser)
            ->line('- **Sistem Operasi:** ' . $os)
            ->line('- **Lokasi:** ' . $location)
            ->line('')
            ->line('Jika ini adalah Anda, tidak perlu melakukan apa pun.')
            ->line('')
            ->line('⚠️ **Jika BUKAN Anda yang login**, segera:')
            ->line('1. Ganti password Anda segera')
            ->line('2. Hubungi admin jika melihat aktivitas mencurigakan')
            ->action('Ke Dashboard', url('/dashboard'))
            ->line('')
            ->line('Terima kasih telah menggunakan ReplyAI!')
            ->salutation('Salam,\nTim Security ReplyAI');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'login_time' => $this->loginTime->toDateTimeString(),
        ];
    }

    /**
     * Parse browser dari user agent
     *
     * @param string $userAgent
     * @return string
     */
    private function getBrowser(string $userAgent): string
    {
        $browsers = [
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'Safari' => 'Safari',
            'Edge' => 'Edge',
            'Opera' => 'Opera',
            'MSIE' => 'Internet Explorer',
            'Trident' => 'Internet Explorer',
        ];

        foreach ($browsers as $key => $name) {
            if (stripos($userAgent, $key) !== false) {
                return $name;
            }
        }

        return 'Unknown Browser';
    }

    /**
     * Parse OS dari user agent
     *
     * @param string $userAgent
     * @return string
     */
    private function getOS(string $userAgent): string
    {
        $oses = [
            'Windows NT 10.0' => 'Windows 10',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.1' => 'Windows 7',
            'Mac OS X' => 'macOS',
            'Linux' => 'Linux',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
        ];

        foreach ($oses as $key => $name) {
            if (stripos($userAgent, $key) !== false) {
                return $name;
            }
        }

        return 'Unknown OS';
    }

    /**
     * Get lokasi dari IP (simplified)
     * 
     * Note: Untuk production, gunakan GeoIP service seperti MaxMind atau ipinfo.io
     *
     * @param string $ip
     * @return string
     */
    private function getLocation(string $ip): string
    {
        // Simplified - untuk production gunakan GeoIP
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }

        // Coba get info dari ip-api.com (free, no auth required)
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=city,country");
            if ($response) {
                $data = json_decode($response, true);
                if ($data && isset($data['city'], $data['country'])) {
                    return $data['city'] . ', ' . $data['country'];
                }
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return 'Unknown Location';
    }
}
