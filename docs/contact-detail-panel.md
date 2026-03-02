# Contact Detail Panel Component

A comprehensive slide-in panel component for managing contact details across WhatsApp, Instagram, and Web Chat conversations.

## Features

- **Multi-platform Support**: Works with WhatsApp, Instagram, and Web Chat contacts
- **Profile Management**: View and manage contact profile information
- **Custom Fields**: Dynamic custom fields with inline editing
- **Tags System**: Add/remove tags with visual indicators
- **Notes Management**: CRUD operations for contact notes with categories
- **Activity History**: Message statistics and activity timeline
- **Actions**: Block contact, delete conversation
- **Dark Theme**: Consistent dark UI matching the application design
- **Responsive**: Works on mobile and desktop

## Installation

The component files are located at:
- **View**: `resources/views/components/chat/contact-detail-panel.blade.php`
- **Controller**: `app/Http/Controllers/ContactPanelController.php`
- **Routes**: Added to `routes/api.php`

### Database Migrations

Run the migrations to create necessary tables:

```bash
php artisan migrate
```

Tables created:
- `contact_field_values` - Stores custom field values for contacts
- `blocked_contacts` - Stores blocked contact records

## Usage

### Basic Usage

Include the component in your Blade template:

```blade
<x-chat.contact-detail-panel 
    :contact-type="'whatsapp'" 
    :contact-id="$phoneNumber"
    :show-panel="false" />
```

### Opening the Panel

Dispatch a custom event from your Alpine.js component:

```javascript
window.dispatchEvent(new CustomEvent('contact-panel-open', {
    detail: {
        type: 'whatsapp',      // 'whatsapp', 'instagram', or 'web'
        id: '1234567890'       // phone_number, instagram_user_id, or visitor_id
    }
}));
```

### Integration with WhatsApp Inbox

Add to your `whatsappInbox()` Alpine.js component:

```javascript
function whatsappInbox() {
    return {
        // ... existing code ...
        
        openContactPanel() {
            if (!this.activeChat) return;
            
            window.dispatchEvent(new CustomEvent('contact-panel-open', {
                detail: {
                    type: 'whatsapp',
                    id: this.activeChat.phone_number
                }
            }));
        }
    }
}
```

Add a button to trigger the panel:

```blade
<button @click="openContactPanel()" class="p-2 hover:bg-gray-700 rounded-lg">
    <span class="material-symbols-outlined">person</span>
</button>
```

## API Endpoints

All API endpoints require authentication.

### Get Contact Details
```
GET /api/contacts/{type}/{id}/details

Response:
{
    "success": true,
    "contact": { ... },
    "customFields": [ ... ],
    "tags": [ ... ],
    "notes": [ ... ],
    "activityStats": { ... },
    "activityLog": [ ... ]
}
```

### Add Note
```
POST /api/contacts/{type}/{id}/notes
Content-Type: application/json

{
    "content": "Note content here",
    "category": "general"  // general, follow_up, complaint, feedback, private
}
```

### Delete Note
```
DELETE /api/notes/{id}
```

### Update Tags
```
PUT /api/contacts/{type}/{id}/tags
Content-Type: application/json

{
    "tag_ids": [1, 2, 3]
}
```

### Update Custom Field
```
PUT /api/contacts/{type}/{id}/custom-fields
Content-Type: application/json

{
    "field_id": 1,
    "value": "Field value"
}
```

### Block Contact
```
POST /api/contacts/{type}/{id}/block
```

### Delete Conversation
```
DELETE /api/contacts/{type}/{id}/conversation
```

## Custom Fields

### Supported Field Types

- `text` - Single line text input
- `number` - Numeric input
- `date` - Date picker
- `email` - Email input
- `phone` - Phone number input
- `url` - URL input
- `select` - Dropdown select
- `multi_select` - Multi-select dropdown
- `textarea` - Multi-line text area
- `checkbox` - Boolean checkbox

### Creating Custom Fields

Use the `ContactCustomField` model:

```php
use App\Models\ContactCustomField;

ContactCustomField::create([
    'user_id' => auth()->id(),
    'name' => 'Company',
    'key' => 'company',
    'type' => ContactCustomField::TYPE_TEXT,
    'is_required' => false,
    'sort_order' => 1
]);
```

## Component Architecture

### Alpine.js Data Structure

```javascript
contactPanel(contactId, contactType, isOpen) {
    return {
        // State
        isOpen: false,
        isLoading: false,
        error: null,
        contact: null,
        customFields: [],
        contactTags: [],
        availableTags: [],
        notes: [],
        activityLog: [],
        activityStats: {},
        
        // UI State
        activeTab: 'info',  // 'info', 'notes', 'activity'
        editingCustomFields: false,
        showTagSelector: false,
        showBlockModal: false,
        
        // Methods
        openPanel(data),
        closePanel(),
        fetchContactDetails(),
        updateCustomField(field),
        addNote(),
        deleteNote(noteId),
        addTag(tagId),
        removeTag(tagId),
        blockContact()
    }
}
```

## Styling

The component uses Tailwind CSS with a dark theme:
- Background: `bg-gray-900`
- Surface: `bg-gray-800`
- Border: `border-gray-800`
- Primary accent: `text-blue-400`, `border-blue-500`

Material Symbols icons are used throughout the component.

## Events

### Emitted Events

- `@contact-panel-closed` - Fired when panel is closed

### Listened Events

- `@contact-panel-open.window` - Opens panel with contact details
- `@keydown.escape.window` - Closes panel on Escape key

## Security

- All API endpoints require authentication
- User ID is automatically set from authenticated user
- Contacts are scoped to the authenticated user
- CSRF protection enabled for state-changing requests

## Troubleshooting

### Panel not opening
- Check that Alpine.js is loaded
- Verify the event is being dispatched correctly
- Check browser console for JavaScript errors

### API returning 404
- Verify the contact exists for the authenticated user
- Check the contact type matches ('whatsapp', 'instagram', 'web')
- Ensure migrations have been run

### Custom fields not saving
- Check that `contact_custom_fields` table has the field definitions
- Verify the field type is supported
- Check browser network tab for API errors
