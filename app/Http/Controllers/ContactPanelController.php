<?php

namespace App\Http\Controllers;

use App\Models\ContactCustomField;
use App\Models\ContactNote;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Tag;
use App\Models\WaConversation;
use App\Models\WaConversationNote;
use App\Models\WaMessage;
use App\Models\WebConversation;
use App\Models\WebMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactPanelController extends Controller
{
    /**
     * Get contact details with all related data
     *
     * @param string $type Contact type: whatsapp, instagram, web
     * @param string $id Contact identifier
     * @return JsonResponse
     */
    public function getContactDetails(string $type, string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get contact based on type
            $contact = $this->getContact($type, $id, $user->id);
            
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found'
                ], 404);
            }
            
            // Get custom fields with values
            $customFields = $this->getCustomFields($type, $id, $user->id);
            
            // Get tags
            $tags = $this->getContactTags($type, $id, $user->id);
            
            // Get notes
            $notes = $this->getContactNotes($type, $id, $user->id);
            
            // Get activity stats
            $activityStats = $this->getActivityStats($type, $id, $user->id);
            
            // Get activity log
            $activityLog = $this->getActivityLog($type, $id, $user->id);
            
            return response()->json([
                'success' => true,
                'contact' => $contact,
                'customFields' => $customFields,
                'tags' => $tags,
                'notes' => $notes,
                'activityStats' => $activityStats,
                'activityLog' => $activityLog
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting contact details', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load contact details'
            ], 500);
        }
    }
    
    /**
     * Update a custom field value
     *
     * @param Request $request
     * @param string $type
     * @param string $id
     * @return JsonResponse
     */
    public function updateCustomField(Request $request, string $type, string $id): JsonResponse
    {
        $request->validate([
            'field_id' => 'required|integer',
            'value' => 'nullable|string|max:1000'
        ]);
        
        try {
            $user = Auth::user();
            
            // Verify contact exists
            $contact = $this->getContact($type, $id, $user->id);
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found'
                ], 404);
            }
            
            // Get custom field definition
            $fieldDef = ContactCustomField::where('id', $request->field_id)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$fieldDef) {
                return response()->json([
                    'success' => false,
                    'message' => 'Custom field not found'
                ], 404);
            }
            
            // Update or create field value
            DB::table('contact_field_values')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'contact_type' => $type,
                    'contact_id' => $id,
                    'field_id' => $request->field_id
                ],
                [
                    'value' => $request->value,
                    'updated_at' => now()
                ]
            );
            
            // Log activity
            $this->logActivity($type, $id, $user->id, 'field_update', [
                'field_name' => $fieldDef->name,
                'field_key' => $fieldDef->key
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating custom field', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field'
            ], 500);
        }
    }
    
    /**
     * Add a new note
     *
     * @param Request $request
     * @param string $type
     * @param string $id
     * @return JsonResponse
     */
    public function addNote(Request $request, string $type, string $id): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'category' => 'nullable|string|in:general,follow_up,complaint,feedback,private'
        ]);
        
        try {
            $user = Auth::user();
            
            // Verify contact exists
            $contact = $this->getContact($type, $id, $user->id);
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found'
                ], 404);
            }
            
            // Handle different note models based on type
            if ($type === 'whatsapp') {
                $note = WaConversationNote::create([
                    'wa_conversation_id' => $contact['conversation_id'],
                    'user_id' => $user->id,
                    'content' => $request->content,
                    'is_internal' => true
                ]);
            } else {
                $note = ContactNote::create([
                    'user_id' => $user->id,
                    'contact_type' => $type,
                    'contact_id' => $id,
                    'content' => $request->content,
                    'category' => $request->category ?? 'general',
                    'created_by' => $user->id
                ]);
            }
            
            // Load author
            $note->load('author');
            
            // Log activity
            $this->logActivity($type, $id, $user->id, 'note', [
                'note_id' => $note->id,
                'category' => $request->category ?? 'general'
            ]);
            
            return response()->json([
                'success' => true,
                'note' => [
                    'id' => $note->id,
                    'content' => $note->content,
                    'category' => $note->category ?? 'general',
                    'created_at' => $note->created_at,
                    'author' => [
                        'id' => $user->id,
                        'name' => $user->name
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error adding note', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add note'
            ], 500);
        }
    }
    
    /**
     * Delete a note
     *
     * @param int $noteId
     * @return JsonResponse
     */
    public function deleteNote(int $noteId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Try to find note in either table
            $note = ContactNote::where('id', $noteId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$note) {
                $note = WaConversationNote::where('id', $noteId)
                    ->where('user_id', $user->id)
                    ->first();
            }
            
            if (!$note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found'
                ], 404);
            }
            
            $note->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting note', [
                'error' => $e->getMessage(),
                'note_id' => $noteId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete note'
            ], 500);
        }
    }
    
    /**
     * Update contact tags
     *
     * @param Request $request
     * @param string $type
     * @param string $id
     * @return JsonResponse
     */
    public function updateTags(Request $request, string $type, string $id): JsonResponse
    {
        $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'integer|exists:tags,id'
        ]);
        
        try {
            $user = Auth::user();
            
            // Verify contact exists
            $contact = $this->getContact($type, $id, $user->id);
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found'
                ], 404);
            }
            
            // Verify tags belong to user
            $validTagIds = Tag::whereIn('id', $request->tag_ids)
                ->where('user_id', $user->id)
                ->pluck('id')
                ->toArray();
            
            // Update tags based on type
            if ($type === 'whatsapp') {
                $conversation = WaConversation::where('phone_number', $id)
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($conversation) {
                    $conversation->tags()->sync($validTagIds);
                }
            } elseif ($type === 'instagram') {
                $conversation = Conversation::where('instagram_user_id', $id)
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($conversation) {
                    $conversation->tags = $validTagIds;
                    $conversation->save();
                }
            } else {
                // For web conversations, update tags array
                $conversation = WebConversation::where('visitor_id', $id)
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($conversation) {
                    $conversation->tags = Tag::whereIn('id', $validTagIds)
                        ->pluck('name')
                        ->toArray();
                    $conversation->save();
                }
            }
            
            // Get updated tags
            $tags = Tag::whereIn('id', $validTagIds)->get(['id', 'name', 'color']);
            
            // Log activity
            $this->logActivity($type, $id, $user->id, 'tag_update', [
                'tag_count' => count($validTagIds)
            ]);
            
            return response()->json([
                'success' => true,
                'tags' => $tags,
                'message' => 'Tags updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating tags', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tags'
            ], 500);
        }
    }
    
    /**
     * Block a contact
     *
     * @param string $type
     * @param string $id
     * @return JsonResponse
     */
    public function blockContact(string $type, string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Verify contact exists
            $contact = $this->getContact($type, $id, $user->id);
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found'
                ], 404);
            }
            
            // Add to blocked contacts
            DB::table('blocked_contacts')->insert([
                'user_id' => $user->id,
                'contact_type' => $type,
                'contact_id' => $id,
                'blocked_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Log activity
            $this->logActivity($type, $id, $user->id, 'block', []);
            
            return response()->json([
                'success' => true,
                'message' => 'Contact blocked successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error blocking contact', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to block contact'
            ], 500);
        }
    }
    
    /**
     * Delete a conversation
     *
     * @param string $type
     * @param string $id
     * @return JsonResponse
     */
    public function deleteConversation(string $type, string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Delete based on type
            if ($type === 'whatsapp') {
                $conversation = WaConversation::where('phone_number', $id)
                    ->where('user_id', $user->id)
                    ->first();
                    
                if ($conversation) {
                    // Delete messages first
                    WaMessage::where('phone_number', $id)
                        ->where('user_id', $user->id)
                        ->delete();
                    // Delete conversation
                    $conversation->delete();
                }
            } elseif ($type === 'instagram') {
                $conversation = Conversation::where('instagram_user_id', $id)
                    ->where('user_id', $user->id)
                    ->first();
                    
                if ($conversation) {
                    // Delete messages
                    Message::where('conversation_id', $conversation->id)->delete();
                    // Delete conversation
                    $conversation->delete();
                }
            } else {
                $conversation = WebConversation::where('visitor_id', $id)
                    ->where('user_id', $user->id)
                    ->first();
                    
                if ($conversation) {
                    // Delete messages
                    WebMessage::where('conversation_id', $conversation->id)->delete();
                    // Delete conversation
                    $conversation->delete();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Conversation deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting conversation', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete conversation'
            ], 500);
        }
    }
    
    /**
     * Get contact based on type
     */
    private function getContact(string $type, string $id, int $userId): ?array
    {
        switch ($type) {
            case 'whatsapp':
                $conversation = WaConversation::where('phone_number', $id)
                    ->where('user_id', $userId)
                    ->first();
                
                if (!$conversation) return null;
                
                return [
                    'id' => $conversation->id,
                    'conversation_id' => $conversation->id,
                    'name' => $conversation->display_name,
                    'display_name' => $conversation->display_name,
                    'phone_number' => $conversation->phone_number,
                    'identifier' => $conversation->phone_number,
                    'status' => $conversation->status,
                    'session_status' => $conversation->session_status,
                    'assigned_cs' => $conversation->assigned_cs,
                    'avatar' => null,
                    'created_at' => $conversation->created_at,
                    'last_activity_at' => $conversation->last_user_reply_at ?? $conversation->updated_at
                ];
                
            case 'instagram':
                $conversation = Conversation::where('instagram_user_id', $id)
                    ->where('user_id', $userId)
                    ->first();
                
                if (!$conversation) return null;
                
                return [
                    'id' => $conversation->id,
                    'conversation_id' => $conversation->id,
                    'name' => $conversation->display_name,
                    'display_name' => $conversation->display_name,
                    'ig_username' => $conversation->ig_username,
                    'identifier' => $conversation->instagram_user_id,
                    'status' => $conversation->status,
                    'avatar' => $conversation->avatar,
                    'source' => $conversation->source,
                    'created_at' => $conversation->created_at,
                    'last_activity_at' => $conversation->last_activity_at
                ];
                
            case 'web':
                $conversation = WebConversation::where('visitor_id', $id)
                    ->where('user_id', $userId)
                    ->first();
                
                if (!$conversation) return null;
                
                return [
                    'id' => $conversation->id,
                    'conversation_id' => $conversation->id,
                    'name' => $conversation->visitor_name,
                    'display_name' => $conversation->display_name,
                    'visitor_email' => $conversation->visitor_email,
                    'visitor_id' => $conversation->visitor_id,
                    'identifier' => $conversation->visitor_id,
                    'visitor_ip' => $conversation->visitor_ip,
                    'status' => $conversation->status,
                    'page_url' => $conversation->page_url,
                    'created_at' => $conversation->created_at,
                    'last_activity_at' => $conversation->last_activity_at
                ];
                
            default:
                return null;
        }
    }
    
    /**
     * Get custom fields with values
     */
    private function getCustomFields(string $type, string $id, int $userId): array
    {
        $fieldDefs = ContactCustomField::where('user_id', $userId)
            ->orderBy('sort_order')
            ->get();
        
        $fieldValues = DB::table('contact_field_values')
            ->where('user_id', $userId)
            ->where('contact_type', $type)
            ->where('contact_id', $id)
            ->pluck('value', 'field_id')
            ->toArray();
        
        return $fieldDefs->map(function ($field) use ($fieldValues) {
            return [
                'id' => $field->id,
                'name' => $field->name,
                'label' => $field->name,
                'key' => $field->key,
                'type' => $field->type,
                'options' => $field->options,
                'is_required' => $field->is_required,
                'value' => $fieldValues[$field->id] ?? null
            ];
        })->toArray();
    }
    
    /**
     * Get contact tags
     */
    private function getContactTags(string $type, string $id, int $userId): array
    {
        if ($type === 'whatsapp') {
            $conversation = WaConversation::where('phone_number', $id)
                ->where('user_id', $userId)
                ->first();
            
            if ($conversation) {
                return $conversation->tags()->select('tags.id', 'tags.name', 'tags.color')->get()->toArray();
            }
        } elseif ($type === 'instagram') {
            $conversation = Conversation::where('instagram_user_id', $id)
                ->where('user_id', $userId)
                ->first();
            
            if ($conversation && $conversation->tags) {
                // Get tag details from tag IDs or names
                $tagNames = is_array($conversation->tags) ? $conversation->tags : [];
                return Tag::whereIn('name', $tagNames)
                    ->where('user_id', $userId)
                    ->select('id', 'name', 'color')
                    ->get()
                    ->toArray();
            }
        } else {
            $conversation = WebConversation::where('visitor_id', $id)
                ->where('user_id', $userId)
                ->first();
            
            if ($conversation && $conversation->tags) {
                $tagNames = is_array($conversation->tags) ? $conversation->tags : [];
                return Tag::whereIn('name', $tagNames)
                    ->where('user_id', $userId)
                    ->select('id', 'name', 'color')
                    ->get()
                    ->toArray();
            }
        }
        
        return [];
    }
    
    /**
     * Get contact notes
     */
    private function getContactNotes(string $type, string $id, int $userId): array
    {
        if ($type === 'whatsapp') {
            $conversation = WaConversation::where('phone_number', $id)
                ->where('user_id', $userId)
                ->first();
            
            if ($conversation) {
                return WaConversationNote::where('wa_conversation_id', $conversation->id)
                    ->where('user_id', $userId)
                    ->with('author:id,name')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(fn($note) => [
                        'id' => $note->id,
                        'content' => $note->content,
                        'category' => $note->is_internal ? 'private' : 'general',
                        'created_at' => $note->created_at,
                        'author' => $note->author
                    ])
                    ->toArray();
            }
        } else {
            return ContactNote::where('user_id', $userId)
                ->where('contact_type', $type)
                ->where('contact_id', $id)
                ->with('author:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }
        
        return [];
    }
    
    /**
     * Get activity statistics
     */
    private function getActivityStats(string $type, string $id, int $userId): array
    {
        $stats = [
            'messageCount' => 0,
            'conversationCount' => 1,
            'avgResponseTime' => '-'
        ];
        
        if ($type === 'whatsapp') {
            $stats['messageCount'] = WaMessage::where('phone_number', $id)
                ->where('user_id', $userId)
                ->count();
        } elseif ($type === 'instagram') {
            $conversation = Conversation::where('instagram_user_id', $id)
                ->where('user_id', $userId)
                ->first();
            
            if ($conversation) {
                $stats['messageCount'] = Message::where('conversation_id', $conversation->id)->count();
            }
        } else {
            $conversation = WebConversation::where('visitor_id', $id)
                ->where('user_id', $userId)
                ->first();
            
            if ($conversation) {
                $stats['messageCount'] = WebMessage::where('conversation_id', $conversation->id)->count();
            }
        }
        
        return $stats;
    }
    
    /**
     * Get activity log
     */
    private function getActivityLog(string $type, string $id, int $userId): array
    {
        $log = [];
        
        // Get last messages as activity
        if ($type === 'whatsapp') {
            $messages = WaMessage::where('phone_number', $id)
                ->where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get();
            
            foreach ($messages as $msg) {
                $log[] = [
                    'type' => 'message',
                    'description' => $msg->is_from_me ? 'You sent a message' : 'Received a message',
                    'timestamp' => $msg->created_at
                ];
            }
        } elseif ($type === 'instagram') {
            $conversation = Conversation::where('instagram_user_id', $id)
                ->where('user_id', $userId)
                ->first();
            
            if ($conversation) {
                $messages = Message::where('conversation_id', $conversation->id)
                    ->latest()
                    ->take(5)
                    ->get();
                
                foreach ($messages as $msg) {
                    $log[] = [
                        'type' => 'message',
                        'description' => $msg->is_from_me ? 'You sent a message' : 'Received a message',
                        'timestamp' => $msg->created_at
                    ];
                }
            }
        }
        
        // Get notes as activity
        $notes = $this->getContactNotes($type, $id, $userId);
        foreach (array_slice($notes, 0, 3) as $note) {
            $log[] = [
                'type' => 'note',
                'description' => 'Added a note: ' . substr($note['content'], 0, 50) . (strlen($note['content']) > 50 ? '...' : ''),
                'timestamp' => $note['created_at']
            ];
        }
        
        // Sort by timestamp
        usort($log, fn($a, $b) => strtotime($b['timestamp']) - strtotime($a['timestamp']));
        
        return array_slice($log, 0, 10);
    }
    
    /**
     * Log an activity
     */
    private function logActivity(string $type, string $id, int $userId, string $action, array $data): void
    {
        // This could be stored in a dedicated activity log table
        // For now, we'll just log to the system log
        Log::info('Contact panel activity', [
            'user_id' => $userId,
            'contact_type' => $type,
            'contact_id' => $id,
            'action' => $action,
            'data' => $data
        ]);
    }
}
