<?php

namespace App\Services;

use App\Models\WaMessage;
use App\Models\WhatsAppDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $serviceUrl;

    public function __construct()
    {
        $this->serviceUrl = config('services.whatsapp.url', 'http://127.0.0.1:3001');
    }

    /**
     * Create/Initialize a session
     */
    public function createSession(string $sessionId): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->serviceUrl}/connect", [
                'sessionId' => $sessionId
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'success' => false,
                'error' => 'Failed to initiate connection'
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Connect Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get connection status
     */
    public function getStatus(string $sessionId): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/status", [
                'sessionId' => $sessionId
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'status' => 'error',
                'error' => 'Failed to get status'
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error: ' . $e->getMessage());
            return [
                'status' => 'offline',
                'error' => 'WhatsApp service is not running'
            ];
        }
    }

    /**
     * Get QR code
     */
    public function getQrCode(string $sessionId): ?string
    {
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/qr", [
                'sessionId' => $sessionId
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['qr'] ?? null;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('WhatsApp QR Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Disconnect
     */
    public function disconnect(string $sessionId): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->serviceUrl}/disconnect", [
                'sessionId' => $sessionId
            ]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'success' => false,
                'error' => 'Failed to disconnect'
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Disconnect Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send message
     */
    public function sendMessage(string $sessionId, string $phoneOrJid, string $message, ?string $mediaUrl = null, ?string $mediaType = null): array
    {
        try {
            $payload = [
                'sessionId' => $sessionId,
                'phone' => $phoneOrJid,
                'message' => $message,
            ];

            if ($mediaUrl && $mediaType) {
                $payload['mediaUrl'] = $mediaUrl;
                $payload['mediaType'] = $mediaType;
            }

            $response = Http::timeout(30)->post("{$this->serviceUrl}/send", $payload);
            
            if ($response->successful()) {
                $this->logMessage($sessionId, $phoneOrJid, $message, $mediaType, 'sent');
                return ['success' => true, 'message' => 'Message sent successfully'];
            }
            
            return ['success' => false, 'error' => $response->json()['error'] ?? 'Failed to send message'];
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log message to database
     */
    protected function logMessage(string $sessionId, string $phoneOrJid, string $message, ?string $mediaType, string $status)
    {
        $isJid = str_contains($phoneOrJid, '@');
        $remoteJid = $isJid ? $phoneOrJid : $this->formatPhoneToJid($phoneOrJid);
        $phoneNumber = $isJid ? $this->extractPhoneFromJid($phoneOrJid) : $this->cleanPhoneNumber($phoneOrJid);

        WaMessage::create([
            'wa_message_id' => 'out_' . uniqid(),
            'remote_jid' => $remoteJid,
            'phone_number' => $phoneNumber,
            'direction' => 'outgoing',
            'message' => $message,
            'message_type' => $mediaType ?? 'text',
            'status' => $status,
            // 'device_id' => ... (Optional: find device by sessionId if needed)
        ]);
    }

    /**
     * Handle incoming message from webhook
     */
    public function handleIncomingMessage(array $data): WaMessage
    {
        $message = WaMessage::updateOrCreate(
            ['wa_message_id' => $data['messageId']],
            [
                'remote_jid' => $data['fromJid'],
                'phone_number' => $data['from'],
                'push_name' => $data['pushName'] ?? null,
                'direction' => 'incoming',
                'message' => $data['message'],
                'message_type' => $data['messageType'] ?? 'text',
                'status' => 'read',
                'wa_timestamp' => isset($data['timestamp']) 
                    ? \Carbon\Carbon::createFromTimestamp($data['timestamp']) 
                    : now(),
            ]
        );

        return $message;
    }

    /**
     * Update session status from webhook
     */
    public function handleStatusUpdate(array $data): void
    {
        if (!isset($data['sessionId'])) {
            return;
        }

        $device = WhatsAppDevice::where('session_id', $data['sessionId'])->first();
        if (!$device) {
            return;
        }

        $validStatuses = ['disconnected', 'waiting_qr', 'connected']; // 'connecting' not used in DB enum? 
        // My migration used: ['connected', 'disconnected', 'scanning', 'unknown']
        // Node.js sends: 'connected', 'waiting_qr', 'disconnected'
        
        $status = $data['status'] ?? 'unknown';
        
        // Map Node status to DB status
        $dbStatus = match($status) {
            'waiting_qr' => 'scanning',
            'connected' => 'connected',
            'disconnected' => 'disconnected',
            default => 'unknown'
        };

        $updateData = ['status' => $dbStatus]; // Use mapped status

        if (isset($data['phoneNumber'])) {
            $updateData['phone_number'] = $data['phoneNumber'];
        }

        if (isset($data['name'])) {
            $updateData['profile_name'] = $data['name'];
        }

        if ($dbStatus === 'connected') {
            $updateData['last_connected_at'] = now();
        }
        
        // If disconnected, maybe log reason?
        if ($dbStatus === 'disconnected' && isset($data['reason'])) {
            $updateData['last_disconnect_reason'] = $data['reason'];
        }

        $device->update($updateData);
    }

    /**
     * Toggle auto-reply setting (Legacy/Global default)
     */
    public function toggleAutoReply(bool $enabled): void
    {
        // For now, maybe just use the first device or a global setting?
        // Or keep WaSession as a "default" config holder?
        // Let's assume we maintain WaSession for global config if needed
        \App\Models\WaSession::where('session_id', 'default')->update(['auto_reply_enabled' => $enabled]);
    }

    /**
     * Check if auto-reply is enabled
     */
    public function isAutoReplyEnabled(): bool
    {
        $session = \App\Models\WaSession::where('session_id', 'default')->first();
        return $session->auto_reply_enabled ?? true;
    }

    /**
     * Helpers (Keep existing)
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);
        if (str_starts_with($cleaned, '0')) $cleaned = '62' . substr($cleaned, 1);
        return $cleaned;
    }

    protected function formatPhoneToJid(string $phone): string
    {
        return $this->cleanPhoneNumber($phone) . '@s.whatsapp.net';
    }

    protected function extractPhoneFromJid(string $jid): string
    {
        return preg_replace('/@.*$/', '', $jid);
    }
}
