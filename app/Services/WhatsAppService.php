<?php

namespace App\Services;

use App\Models\WaSession;
use App\Models\WaMessage;
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
     * Get connection status from Node.js service
     */
    public function getStatus(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/status");
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Sync status to database
                $this->syncSessionStatus($data);
                
                return $data;
            }
            
            return [
                'status' => 'error',
                'error' => 'Failed to get status from WhatsApp service'
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
     * Initiate connection to WhatsApp
     */
    public function connect(): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->serviceUrl}/connect");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return [
                'success' => false,
                'error' => 'Failed to initiate connection'
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Connect Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Disconnect from WhatsApp
     */
    public function disconnect(): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->serviceUrl}/disconnect");
            
            if ($response->successful()) {
                // Update session in database
                WaSession::where('session_id', 'default')->update([
                    'status' => 'disconnected',
                    'phone_number' => null,
                    'name' => null,
                ]);
                
                return $response->json();
            }
            
            return [
                'success' => false,
                'error' => 'Failed to disconnect'
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Disconnect Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get QR code for authentication
     */
    public function getQrCode(): ?string
    {
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/qr");
            
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
     * Send message via WhatsApp
     * Supports both phone number and JID format
     */
    public function sendMessage(string $phoneOrJid, string $message, ?string $mediaUrl = null, ?string $mediaType = null): array
    {
        try {
            $payload = [
                'phone' => $phoneOrJid, // Can be phone number or JID (including @lid format)
                'message' => $message,
            ];

            if ($mediaUrl && $mediaType) {
                $payload['mediaUrl'] = $mediaUrl;
                $payload['mediaType'] = $mediaType;
            }

            $response = Http::timeout(30)->post("{$this->serviceUrl}/send", $payload);
            
            if ($response->successful()) {
                // Determine remote_jid and phone_number based on input format
                $isJid = str_contains($phoneOrJid, '@');
                $remoteJid = $isJid ? $phoneOrJid : $this->formatPhoneToJid($phoneOrJid);
                $phoneNumber = $isJid ? $this->extractPhoneFromJid($phoneOrJid) : $this->cleanPhoneNumber($phoneOrJid);
                
                // Log outgoing message
                WaMessage::create([
                    'wa_message_id' => 'out_' . uniqid(),
                    'remote_jid' => $remoteJid,
                    'phone_number' => $phoneNumber,
                    'direction' => 'outgoing',
                    'message' => $message,
                    'message_type' => $mediaType ?? 'text',
                    'status' => 'sent',
                ]);

                return [
                    'success' => true,
                    'message' => 'Message sent successfully'
                ];
            }
            
            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Failed to send message'
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Send Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract phone number from JID (handles both @s.whatsapp.net and @lid formats)
     */
    protected function extractPhoneFromJid(string $jid): string
    {
        // Remove @lid or @s.whatsapp.net suffix
        return preg_replace('/@.*$/', '', $jid);
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
                'status' => 'read', // Use 'read' for incoming messages (we've read it)
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
        $this->syncSessionStatus($data);
    }

    /**
     * Sync session status to database
     */
    protected function syncSessionStatus(array $data): void
    {
        $session = WaSession::getDefault();
        
        // Validate status against allowed ENUM values
        $validStatuses = ['disconnected', 'waiting_qr', 'connecting', 'connected'];
        $status = $data['status'] ?? 'disconnected';
        
        if (!in_array($status, $validStatuses)) {
            Log::warning("Invalid WhatsApp status received: {$status}");
            $status = 'disconnected'; // Default to disconnected for invalid values
        }
        
        $updateData = ['status' => $status];
        
        if (isset($data['phoneNumber'])) {
            $updateData['phone_number'] = $data['phoneNumber'];
        }
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        
        if ($status === 'connected') {
            $updateData['last_connected_at'] = now();
        }
        
        $session->update($updateData);
    }

    /**
     * Get session from database
     */
    public function getSession(): WaSession
    {
        return WaSession::getDefault();
    }

    /**
     * Toggle auto-reply setting
     */
    public function toggleAutoReply(bool $enabled): void
    {
        WaSession::getDefault()->update(['auto_reply_enabled' => $enabled]);
    }

    /**
     * Check if auto-reply is enabled
     */
    public function isAutoReplyEnabled(): bool
    {
        return WaSession::getDefault()->auto_reply_enabled ?? true;
    }

    /**
     * Clean phone number (remove non-numeric characters)
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);
        
        // Handle Indonesian numbers
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }
        
        return $cleaned;
    }

    /**
     * Format phone number to WhatsApp JID
     */
    protected function formatPhoneToJid(string $phone): string
    {
        return $this->cleanPhoneNumber($phone) . '@s.whatsapp.net';
    }
}
