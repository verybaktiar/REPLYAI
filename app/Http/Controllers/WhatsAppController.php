<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Models\WaMessage;
use App\Models\WhatsAppDevice;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Services\ActivityLogService;

class WhatsAppController extends Controller
{
    public function __construct(
        protected WhatsAppService $waService
    ) {}

    /**
     * WhatsApp Settings Page (Device List)
     */
    public function settings()
    {
        $devices = WhatsAppDevice::with('businessProfile')->get();
        $recentMessages = WaMessage::latest()->take(10)->get();
        $businessProfiles = BusinessProfile::orderBy('business_name')->get();

        return view('pages.whatsapp.settings', [
            'title' => 'WhatsApp Settings',
            'devices' => $devices,
            'recentMessages' => $recentMessages,
            'businessProfiles' => $businessProfiles,
        ]);
    }

    /**
     * Create a new device session
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'device_name' => 'required|string|max:255',
        ]);

        $deviceName = $request->input('device_name');
        $sessionId = Str::slug($deviceName) . '-' . Str::random(6);

        // Create DB record
        $businessProfile = BusinessProfile::first(); // filtered by global scope to current user
        
        $device = WhatsAppDevice::create([
            'session_id' => $sessionId,
            'device_name' => $deviceName,
            'status' => 'scanning', // Initial status
            'business_profile_id' => $businessProfile?->id,
            'user_id' => auth()->id(), // Explicitly set user_id
        ]);

        // Init session in Node.js
        try {
            $result = $this->waService->createSession($sessionId);
            
            ActivityLogService::logCreated($device, "Menambah perangkat WhatsApp baru: {$deviceName}");
            
            return response()->json(['success' => true, 'device' => $device, 'result' => $result]);
        } catch (\Exception $e) {
            $device->delete();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get QR code for specific device
     */
    public function qr($sessionId): JsonResponse
    {
        try {
            $qr = $this->waService->getQrCode($sessionId);
            
            if ($qr) {
                return response()->json(['success' => true, 'qr' => $qr]);
            }
            
            return response()->json(['success' => false, 'message' => 'No QR code available']);
        } catch (\Exception $e) {
            \Log::error("WhatsApp QR code error for {$sessionId}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get QR code',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get status for specific device
     */
    public function status($sessionId): JsonResponse
    {
        try {
            $status = $this->waService->getStatus($sessionId);
            
            // Update DB with latest status if we got valid response
            $device = WhatsAppDevice::where('session_id', $sessionId)->first();
            
            // Auto-reconnect if session not found in Node.js but exists in DB
            if ($device && isset($status['error']) && $status['error'] === 'Session not found') {
                \Log::info("WhatsApp session {$sessionId} not found in service. Attempting to re-initialize.");
                $this->waService->createSession($sessionId);
                return response()->json(['status' => 'initializing', 'message' => 'Session re-initialization started']);
            }

            if ($device && isset($status['status'])) {
                // Map Node.js status to DB status
                $dbStatus = match($status['status']) {
                    'waiting_qr' => 'scanning',
                    'connected' => 'connected',
                    'disconnected' => 'disconnected',
                    default => 'unknown'
                };
                
                $device->update([
                    'status' => $dbStatus,
                    'phone_number' => $status['phoneNumber'] ?? $device->phone_number,
                    'profile_name' => $status['profileName'] ?? $device->profile_name,
                ]);
            }
            
            return response()->json($status);
        } catch (\Exception $e) {
            \Log::error("WhatsApp status check error for {$sessionId}: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'error' => 'Failed to get status',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reconnect device (Re-initialize session)
     */
    public function reconnect($sessionId): JsonResponse
    {
        try {
            $device = WhatsAppDevice::where('session_id', $sessionId)->firstOrFail();
            
            // Re-create session in Node.js service
            $this->waService->createSession($sessionId);
            
            // Update status to scanning so UI shows QR code
            $device->update(['status' => 'scanning']);
            
            return response()->json([
                'success' => true, 
                'message' => 'Session re-initialized. Please scan the QR code.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Disconnect device
     */
    public function destroy($sessionId): JsonResponse
    {
        try {
            $this->waService->disconnect($sessionId);
            
            if ($device) {
                ActivityLogService::logDeleted($device, "Menghapus perangkat WhatsApp: {$device->device_name}");
                
                // We can either delete the record or just mark as disconnected
                // For now, let's delete to allow re-adding freshly
                $device->delete();
            }

            return response()->json(['success' => true, 'message' => 'Device removed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send message
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|exists:whatsapp_devices,id', // or session_id
            'phone' => 'required|string',
            'message' => 'nullable|string', 
            'file' => 'nullable|file|max:10240', 
        ]);
        
        $device = WhatsAppDevice::findOrFail($request->input('device_id'));
        $sessionId = $device->session_id;

        $phone = $request->input('phone');
        $message = $request->input('message') ?? '';
        $mediaUrl = null;
        $mediaType = null;

        // Handle File Upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('whatsapp-media', 'public');
            $mediaUrl = asset('storage/' . $path);
            
            $mime = $file->getMimeType();
            if (str_contains($mime, 'image')) {
                $mediaType = 'image';
            } elseif (str_contains($mime, 'video')) {
                $mediaType = 'video';
            } else {
                $mediaType = 'document';
            }
        }

        if (empty($message) && empty($mediaUrl)) {
            return response()->json(['success' => false, 'error' => 'Message or file is required']);
        }

        try {
            $result = $this->waService->sendMessage(
                $sessionId,
                $phone,
                $message,
                $mediaUrl,
                $mediaType
            );
            return response()->json($result);
        } catch (\Exception $e) {
             return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update device's business profile assignment
     */
    public function updateProfile(Request $request, $sessionId): JsonResponse
    {
        $request->validate([
            'business_profile_id' => 'nullable|exists:business_profiles,id',
        ]);

        $device = WhatsAppDevice::where('session_id', $sessionId)->firstOrFail();
        
        $device->update([
            'business_profile_id' => $request->input('business_profile_id'),
        ]);

        $device->load('businessProfile');

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'device' => $device,
        ]);
    }
}
