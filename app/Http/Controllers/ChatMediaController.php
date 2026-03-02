<?php

namespace App\Http\Controllers;

use App\Models\ChatMedia;
use App\Models\Conversation;
use App\Models\WaConversation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatMediaController extends Controller
{
    /**
     * Get media list for a conversation
     */
    public function index(string $type, int $conversationId): JsonResponse
    {
        $user = auth()->user();
        
        // Validate conversation type
        $validTypes = ['instagram', 'whatsapp', 'web'];
        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid conversation type'], 400);
        }

        // Get conversation based on type
        $conversation = $this->getConversation($type, $conversationId, $user->id);
        
        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        // Build query
        $query = ChatMedia::where('user_id', $user->id)
            ->where('conversation_type', $this->getConversationModel($type))
            ->where('conversation_id', $conversationId);

        // Apply filters
        if ($filter = request('filter')) {
            switch ($filter) {
                case 'images':
                    $query->images();
                    break;
                case 'videos':
                    $query->videos();
                    break;
                case 'documents':
                    $query->documents();
                    break;
                case 'audio':
                    $query->audio();
                    break;
            }
        }

        // Apply search
        if ($search = request('search')) {
            $query->where('filename', 'like', "%{$search}%");
        }

        // Paginate
        $perPage = request('per_page', 30);
        $media = $query->orderByDesc('created_at')->paginate($perPage);

        // Transform response
        $media->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'mime_type' => $item->mime_type,
                'filename' => $item->filename,
                'url' => $item->url,
                'size' => $item->size,
                'human_readable_size' => $item->human_readable_size,
                'metadata' => $item->metadata,
                'created_at' => $item->created_at->toISOString(),
                'created_at_formatted' => $item->created_at->format('d M Y, H:i'),
                'message_id' => $item->message_id,
                'message_type' => $item->message_type,
            ];
        });

        return response()->json($media);
    }

    /**
     * Show/preview media
     */
    public function show(int $id): StreamedResponse|JsonResponse
    {
        $user = auth()->user();
        
        $media = ChatMedia::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$media) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        // Check if external URL
        if (str_starts_with($media->url, 'http')) {
            return response()->json([
                'url' => $media->url,
                'type' => $media->type,
                'filename' => $media->filename,
            ]);
        }

        // Local file
        $path = str_replace('/storage/', '', $media->url);
        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->stream(function () use ($fullPath) {
            readfile($fullPath);
        }, 200, [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->filename . '"',
            'Content-Length' => filesize($fullPath),
        ]);
    }

    /**
     * Download media
     */
    public function download(int $id): StreamedResponse|JsonResponse
    {
        $user = auth()->user();
        
        $media = ChatMedia::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$media) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        // Check if external URL - redirect
        if (str_starts_with($media->url, 'http')) {
            return redirect()->away($media->url);
        }

        // Local file
        $path = str_replace('/storage/', '', $media->url);
        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->stream(function () use ($fullPath) {
            readfile($fullPath);
        }, 200, [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $media->filename . '"',
            'Content-Length' => filesize($fullPath),
        ]);
    }

    /**
     * Delete media
     */
    public function destroy(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $media = ChatMedia::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$media) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        try {
            // Delete local file if exists
            if (!str_starts_with($media->url, 'http')) {
                $path = str_replace('/storage/', '', $media->url);
                Storage::disk('public')->delete($path);
            }

            $media->delete();

            return response()->json(['success' => true, 'message' => 'Media deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete media'], 500);
        }
    }

    /**
     * Get conversation by type and ID
     */
    protected function getConversation(string $type, int $id, int $userId): ?\Illuminate\Database\Eloquent\Model
    {
        return match($type) {
            'instagram' => Conversation::where('id', $id)->where('user_id', $userId)->first(),
            'whatsapp' => WaConversation::where('id', $id)->where('user_id', $userId)->first(),
            default => null,
        };
    }

    /**
     * Get conversation model class name
     */
    protected function getConversationModel(string $type): string
    {
        return match($type) {
            'instagram' => Conversation::class,
            'whatsapp' => WaConversation::class,
            default => '',
        };
    }

    /**
     * Store media from webhook data
     * Called when processing incoming messages with media
     */
    public static function storeFromWebhook(
        string $conversationType,
        int $conversationId,
        int $messageId,
        string $messageType,
        array $mediaData,
        int $userId
    ): ?ChatMedia {
        try {
            // Determine media type from mime type or file extension
            $mimeType = $mediaData['mime_type'] ?? 'application/octet-stream';
            $filename = $mediaData['filename'] ?? 'unknown';
            $url = $mediaData['url'] ?? $mediaData['link'] ?? null;
            $size = $mediaData['size'] ?? null;

            if (!$url) {
                Log::warning('Media URL not found in webhook data', $mediaData);
                return null;
            }

            $type = self::determineMediaType($mimeType, $filename);

            $metadata = [
                'original_url' => $url,
                'width' => $mediaData['width'] ?? null,
                'height' => $mediaData['height'] ?? null,
                'duration' => $mediaData['duration'] ?? null,
            ];

            return ChatMedia::create([
                'user_id' => $userId,
                'message_type' => $messageType,
                'message_id' => $messageId,
                'conversation_type' => self::getConversationClass($conversationType),
                'conversation_id' => $conversationId,
                'type' => $type,
                'mime_type' => $mimeType,
                'filename' => $filename,
                'url' => $url,
                'size' => $size,
                'metadata' => array_filter($metadata),
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing media from webhook: ' . $e->getMessage(), [
                'conversation_type' => $conversationType,
                'mediaData' => $mediaData,
            ]);
            return null;
        }
    }

    /**
     * Determine media type from mime type or filename
     */
    protected static function determineMediaType(string $mimeType, string $filename): string
    {
        $mimeMap = [
            'image' => ChatMedia::TYPE_IMAGE,
            'video' => ChatMedia::TYPE_VIDEO,
            'audio' => ChatMedia::TYPE_AUDIO,
            'application/pdf' => ChatMedia::TYPE_DOCUMENT,
            'application/msword' => ChatMedia::TYPE_DOCUMENT,
            'application/vnd.openxmlformats-officedocument' => ChatMedia::TYPE_DOCUMENT,
        ];

        foreach ($mimeMap as $prefix => $type) {
            if (str_starts_with($mimeType, $prefix)) {
                return $type;
            }
        }

        // Fallback to extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $extensionMap = [
            'jpg' => ChatMedia::TYPE_IMAGE,
            'jpeg' => ChatMedia::TYPE_IMAGE,
            'png' => ChatMedia::TYPE_IMAGE,
            'gif' => ChatMedia::TYPE_IMAGE,
            'webp' => ChatMedia::TYPE_IMAGE,
            'mp4' => ChatMedia::TYPE_VIDEO,
            'mov' => ChatMedia::TYPE_VIDEO,
            'avi' => ChatMedia::TYPE_VIDEO,
            'mp3' => ChatMedia::TYPE_AUDIO,
            'ogg' => ChatMedia::TYPE_AUDIO,
            'wav' => ChatMedia::TYPE_AUDIO,
            'oga' => ChatMedia::TYPE_VOICE,
            'pdf' => ChatMedia::TYPE_DOCUMENT,
            'doc' => ChatMedia::TYPE_DOCUMENT,
            'docx' => ChatMedia::TYPE_DOCUMENT,
            'xls' => ChatMedia::TYPE_DOCUMENT,
            'xlsx' => ChatMedia::TYPE_DOCUMENT,
            'ppt' => ChatMedia::TYPE_DOCUMENT,
            'pptx' => ChatMedia::TYPE_DOCUMENT,
            'txt' => ChatMedia::TYPE_DOCUMENT,
            'webm' => ChatMedia::TYPE_VOICE,
        ];

        return $extensionMap[$extension] ?? ChatMedia::TYPE_DOCUMENT;
    }

    /**
     * Get conversation model class
     */
    protected static function getConversationClass(string $type): string
    {
        return match($type) {
            'instagram' => Conversation::class,
            'whatsapp' => WaConversation::class,
            default => Conversation::class,
        };
    }
}
