# Chat Assignment System Integration Guide

## Overview
This document describes how to integrate the Chat Assignment System into your existing chat interfaces.

## Components

### 1. Assignment Panel Component
The assignment panel provides a dropdown UI for assigning/unassigning conversations.

```blade
<x-chat.assignment-panel 
    conversationType="instagram" 
    :conversationId="$conversation->id"
    :currentAssignment="$currentAssignment"
/>
```

### 2. Chat Item with Assignment Indicator
Shows agent assignment status in the conversation list.

```blade
<x-chat.chat-assigned-item 
    :name="$conversation->display_name"
    :message="$conversation->last_message"
    :time="$conversation->last_activity_at"
    :assignedAgent="$assignedAgent"
    :assignmentType="$assignmentType" {{-- 'me', 'other', or null --}}
    channel="instagram"
/>
```

## Integration Example

### In your Inbox View (e.g., resources/views/pages/inbox/index.blade.php)

```blade
{{-- Add this to your chat header --}}
<div class="flex items-center gap-4">
    {{-- Existing header content --}}
    
    {{-- Assignment Panel --}}
    @php
        $currentAssignment = $selectedConversation ? \App\Models\ChatAssignment::where('conversation_type', 'instagram')
            ->where('conversation_id', $selectedConversation->id)
            ->where('status', 'active')
            ->with('agent')
            ->first() : null;
        
        $assignmentData = $currentAssignment ? [
            'agent' => [
                'id' => $currentAssignment->agent->id,
                'name' => $currentAssignment->agent->name,
                'avatar' => $currentAssignment->agent->avatar ?? null,
            ]
        ] : null;
    @endphp
    
    <x-chat.assignment-panel 
        conversationType="instagram"
        :conversationId="$selectedConversation?->id"
        :currentAssignment="$assignmentData"
    />
</div>

{{-- Add this to your conversation list item --}}
@foreach($conversations as $conversation)
    @php
        $assignment = \App\Models\ChatAssignment::where('conversation_type', 'instagram')
            ->where('conversation_id', $conversation->id)
            ->where('status', 'active')
            ->with('agent')
            ->first();
        
        $assignedAgent = $assignment ? [
            'name' => $assignment->agent->name,
            'avatar' => $assignment->agent->avatar ?? null,
        ] : null;
        
        $assignmentType = $assignment ? ($assignment->agent->id === auth()->id() ? 'me' : 'other') : null;
    @endphp
    
    <x-chat.chat-assigned-item 
        :name="$conversation->display_name"
        :message="$conversation->last_message"
        :time="\Carbon\Carbon::parse($conversation->last_activity_at)->diffForHumans()"
        :active="$selectedId == $conversation->id"
        :unread="true"
        :assignedAgent="$assignedAgent"
        :assignmentType="$assignmentType"
        channel="instagram"
        onclick="window.location.href='{{ route('inbox', ['conversation_id' => $conversation->id]) }}'"
    />
@endforeach
```

## API Endpoints

### Assign Conversation
```
POST /api/chat/{type}/{id}/assign
Body: { "user_id": 123, "notes": "Optional note" }
```

### Unassign Conversation
```
DELETE /api/chat/{type}/{id}/assign
```

### Transfer Conversation
```
POST /api/chat/{type}/{id}/transfer
Body: { "user_id": 456, "notes": "Transfer reason" }
```

### Get My Assignments
```
GET /api/my-assignments?type=instagram&status=active&page=1
```

### Get Assignment Details
```
GET /api/chat/{type}/{id}/assignment
```

### Collision Check (Who's Typing)
```
GET /api/chat/{type}/{id}/collision-check
```

### Broadcast Typing Status
```
POST /api/chat/typing
Body: { "conversation_type": "instagram", "conversation_id": 123, "is_typing": true }
```

### Get Typing Status
```
GET /api/chat/{type}/{id}/typing-status
```

## Events

The assignment panel dispatches custom events:

- `conversation-assigned` - When a conversation is assigned
- `conversation-unassigned` - When a conversation is unassigned
- `show-toast` - For toast notifications

Listen to these events:

```javascript
document.addEventListener('conversation-assigned', (e) => {
    console.log('Assigned:', e.detail.assignment);
    // Refresh conversation list, etc.
});
```

## Styling

The components use Tailwind CSS with a dark theme. Key classes:
- Background: `bg-gray-900`, `bg-gray-950`
- Borders: `border-gray-800`
- Text: `text-gray-300`, `text-gray-400`
- Accents: `text-blue-500`, `bg-blue-600/20`
- Status badges: 
  - Active: `bg-green-500/20 text-green-400`
  - Resolved: `bg-gray-500/20 text-gray-400`
  - Transferred: `bg-amber-500/20 text-amber-400`

## Features

1. **Assignment Dropdown**: Shows list of available agents with search
2. **Current Assignment Display**: Shows assigned agent avatar and name
3. **Unassign Option**: Removes assignment from conversation
4. **Transfer**: Reassign to another agent
5. **Collision Detection**: Warns when multiple agents are typing
6. **Typing Indicators**: Shows who's currently typing
7. **Assignment Count**: Shows number of active assignments per agent
8. **Status Badges**: Visual indicators for assignment status

## Database

The system uses the existing `chat_assignments` table with the following fields:
- `user_id` - Assigned agent
- `conversation_type` - instagram, whatsapp, or web
- `conversation_id` - Conversation ID
- `assigned_by` - User who made the assignment
- `assigned_at` - Assignment timestamp
- `resolved_at` - Resolution timestamp (if resolved)
- `status` - active, resolved, or transferred
- `notes` - Optional notes
