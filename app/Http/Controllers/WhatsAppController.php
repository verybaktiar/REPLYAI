<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Models\WaMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WhatsAppController extends Controller
{
    public function __construct(
        protected WhatsAppService $waService
    ) {}

    /**
     * WhatsApp Settings Page
     */
    public function settings()
    {
        $session = $this->waService->getSession();
        $status = $this->waService->getStatus();
        $recentMessages = WaMessage::latest()->take(10)->get();

        return view('pages.whatsapp.settings', [
            'title' => 'WhatsApp Settings',
            'session' => $session,
            'status' => $status,
            'recentMessages' => $recentMessages,
        ]);
    }

    /**
     * Connect to WhatsApp
     */
    public function connect(): JsonResponse
    {
        $result = $this->waService->connect();
        return response()->json($result);
    }

    /**
     * Disconnect from WhatsApp
     */
    public function disconnect(): JsonResponse
    {
        $result = $this->waService->disconnect();
        return response()->json($result);
    }

    /**
     * Get connection status
     */
    public function status(): JsonResponse
    {
        $status = $this->waService->getStatus();
        return response()->json($status);
    }

    /**
     * Get QR code
     */
    public function qr(): JsonResponse
    {
        $qr = $this->waService->getQrCode();
        
        if ($qr) {
            return response()->json(['success' => true, 'qr' => $qr]);
        }
        
        return response()->json(['success' => false, 'message' => 'No QR code available']);
    }

    /**
     * Send message
     */
    /**
     * Send message
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string', // Message can be empty if sending media
            'file' => 'nullable|file|max:10240', // Max 10MB
        ]);

        $phone = $request->input('phone');
        $message = $request->input('message') ?? '';
        $mediaUrl = null;
        $mediaType = null;

        // Handle File Upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('whatsapp-media', 'public');
            $mediaUrl = asset('storage/' . $path);
            
            // Determine media type
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

        $result = $this->waService->sendMessage(
            $phone,
            $message,
            $mediaUrl,
            $mediaType
        );

        return response()->json($result);
    }

    /**
     * Toggle auto-reply
     */
    public function toggleAutoReply(Request $request): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $this->waService->toggleAutoReply($request->input('enabled'));

        return response()->json([
            'success' => true,
            'auto_reply_enabled' => $request->input('enabled')
        ]);
    }

    /**
     * Get message history
     */
    public function messages(Request $request): JsonResponse
    {
        $phone = $request->input('phone');
        
        $query = WaMessage::latest();
        
        if ($phone) {
            $query->where('phone_number', $phone);
        }
        
        $messages = $query->paginate(50);

        return response()->json($messages);
    }
}
