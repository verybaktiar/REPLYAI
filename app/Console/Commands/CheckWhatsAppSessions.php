<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppDevice;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WhatsAppSessionDisconnected;

class CheckWhatsAppSessions extends Command
{
    protected $signature = 'whatsapp:check-sessions';
    protected $description = 'Check WhatsApp session status and reconnect if needed';

    public function handle(WhatsAppService $waService)
    {
        $this->info('Checking WhatsApp sessions...');
        
        $devices = WhatsAppDevice::where('status', 'connected')->get();
        
        foreach ($devices as $device) {
            try {
                $status = $waService->getStatus($device->session_id);
                
                if (!isset($status['connected']) || !$status['connected']) {
                    $this->warn("Session {$device->session_id} is disconnected. Attempting reconnect...");
                    
                    // Update status
                    $device->update(['status' => 'disconnected']);
                    
                    // Send notification to admin
                    if ($device->user) {
                        $device->user->notify(new WhatsAppSessionDisconnected($device));
                    }
                    
                    // Auto reconnect
                    $reconnectResult = $waService->createSession($device->session_id);
                    
                    if ($reconnectResult['success'] ?? false) {
                        $this->info("Successfully reconnected session {$device->session_id}");
                        Log::info('WhatsApp Auto-Reconnect Success', ['session_id' => $device->session_id]);
                    } else {
                        $this->error("Failed to reconnect session {$device->session_id}");
                        Log::error('WhatsApp Auto-Reconnect Failed', [
                            'session_id' => $device->session_id,
                            'error' => $reconnectResult['error'] ?? 'Unknown'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error checking session {$device->session_id}: " . $e->getMessage());
                Log::error('WhatsApp Session Check Error', [
                    'session_id' => $device->session_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info('Session check completed.');
        return 0;
    }
}
