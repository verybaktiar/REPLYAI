# Media Gallery Component Documentation

## Overview
The Media Gallery component provides a comprehensive interface for viewing, managing, and downloading media files (images, videos, documents, audio) from chat conversations.

## Features
- **Grid/List Views**: Images and videos in a masonry grid, documents in a list view
- **Filter Tabs**: All, Images, Videos, Documents, Audio
- **Search**: Filter by filename
- **Preview Modal**: Click to preview images and videos
- **Download**: Download individual files
- **Delete**: Remove media with confirmation
- **Keyboard Navigation**: Arrow keys for preview navigation, Escape to close

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── ChatMediaController.php      # Controller for media operations
├── Models/
│   └── ChatMedia.php                    # Existing model for media records
├── Services/
│   └── WhatsAppService.php              # Updated to save media on webhook
├── Http/
│   └── Controllers/
│       └── InstagramWebhookController.php # Updated to save media on webhook

resources/
└── views/
    └── components/
        └── chat/
            └── media-gallery.blade.php  # Alpine.js component

routes/
├── web.php                              # Chat media gallery route
└── api.php                              # API routes for media operations
```

## Routes

### Web Routes
```
GET /chat/{type}/{conversationId}/media   # List media for conversation
```

### API Routes
```
GET    /api/chat-media/{id}               # Preview media
GET    /api/chat-media/{id}/download      # Download media
DELETE /api/chat-media/{id}               # Delete media
```

## Usage

### Basic Usage
```blade
@include('components.chat.media-gallery', [
    'conversationType' => 'instagram',    // or 'whatsapp'
    'conversationId' => $conversationId,
    'triggerLabel' => 'Media Gallery',
    'triggerIcon' => 'photo_library'
])
```

### In Inbox Views
The component has been integrated into the Instagram inbox view (`resources/views/pages/inbox/index.blade.php`).

For WhatsApp inbox, add similar code to the WhatsApp inbox view:
```blade
@include('components.chat.media-gallery', [
    'conversationType' => 'whatsapp',
    'conversationId' => $conversationId,
    'triggerLabel' => 'Media',
    'triggerIcon' => 'photo_library'
])
```

## How Media is Saved

### WhatsApp
When a message with media is received via webhook, the `WhatsAppService::storeMediaIfPresent()` method automatically creates a `ChatMedia` record.

### Instagram
When a message with attachments is received, the `InstagramWebhookController::processAttachments()` method creates `ChatMedia` records for each attachment.

### Manual Creation
You can also manually create media records:
```php
use App\Http\Controllers\ChatMediaController;

ChatMediaController::storeFromWebhook(
    'instagram',                    // conversation type
    $conversationId,                // conversation ID
    $messageId,                     // message ID
    Message::class,                 // message model class
    [
        'url' => 'https://example.com/image.jpg',
        'mime_type' => 'image/jpeg',
        'filename' => 'image.jpg',
        'size' => 102400,
    ],
    $userId                         // user ID
);
```

## Media Types
The following media types are supported:
- `image` - JPG, PNG, GIF, WEBP
- `video` - MP4, MOV, AVI
- `audio` - MP3, OGG, WAV
- `voice` - OGA, WEBM
- `document` - PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT

## Styling
The component uses Tailwind CSS with the following custom styles:
- Dark theme matching the application design
- Masonry grid layout for images
- Hover effects with gradient overlays
- Modal overlay with backdrop blur
- Responsive design (mobile-friendly)

## Security
- All media routes require authentication
- Users can only access media belonging to their own conversations
- Media deletion is restricted to the owner

## Keyboard Shortcuts
- `Escape` - Close gallery or preview modal
- `←` / `→` - Navigate between media in preview
- `Ctrl+K` - Focus search input (when gallery is open)

## Future Enhancements
- Bulk select and delete
- Media upload from gallery
- Share media links
- Media compression settings
- Cloud storage integration (S3, etc.)
