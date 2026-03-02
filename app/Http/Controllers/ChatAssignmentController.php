<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\ChatAssignment;
use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WebConversation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ChatAssignmentController extends Controller
{
    /**
     * Valid conversation types
     */
    private const VALID_TYPES = [
        'instagram' => Conversation::class,
        'whatsapp' => WaConversation::class,
        'web' => WebConversation::class,
    ];

    /**
     * Cache prefix for typing status
     */
    private const TYPING_CACHE_PREFIX = 'chat_typing:';

    /**
     * Cache TTL for typing status (seconds)
     */
    private const TYPING_CACHE_TTL = 10;

    /**
     * Assign conversation to an agent
     */
    public function assign(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'notes' => 'nullable|string|max:500',
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $conversation = $this->getConversation($type, $id, $user->id);
            if (!$conversation) {
                return response()->json(['error' => 'Conversation not found'], 404);
            }

            $agentId = $request->input('user_id');
            $agent = User::find($agentId);

            if (!$agent) {
                return response()->json(['error' => 'Agent not found'], 404);
            }

            // Check if already assigned
            $existingAssignment = ChatAssignment::where('conversation_type', $type)
                ->where('conversation_id', $id)
                ->where('status', ChatAssignment::STATUS_ACTIVE)
                ->first();

            if ($existingAssignment) {
                // If assigned to same agent, return error
                if ($existingAssignment->user_id === $agentId) {
                    return response()->json([
                        'error' => 'Conversation already assigned to this agent',
                        'assignment' => $existingAssignment->load('agent')
                    ], 422);
                }

                // Transfer to new agent
                return $this->doTransfer($existingAssignment, $agentId, $user->id, $request->input('notes'));
            }

            // Create new assignment
            $assignment = ChatAssignment::create([
                'user_id' => $agentId,
                'conversation_type' => $type,
                'conversation_id' => $id,
                'assigned_by' => $user->id,
                'assigned_at' => now(),
                'status' => ChatAssignment::STATUS_ACTIVE,
                'notes' => $request->input('notes'),
            ]);

            // Update conversation status if needed
            $this->updateConversationStatus($conversation, 'agent_handling');

            // Broadcast assignment event (if using websockets)
            $this->broadcastAssignment($assignment, 'assigned');

            Log::info('Chat assigned', [
                'assignment_id' => $assignment->id,
                'conversation_type' => $type,
                'conversation_id' => $id,
                'agent_id' => $agentId,
                'assigned_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversation assigned successfully',
                'assignment' => $assignment->load(['agent', 'assignedBy']),
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to assign conversation',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove assignment from conversation
     */
    public function unassign(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $conversation = $this->getConversation($type, $id, $user->id);
            if (!$conversation) {
                return response()->json(['error' => 'Conversation not found'], 404);
            }

            $assignment = ChatAssignment::where('conversation_type', $type)
                ->where('conversation_id', $id)
                ->where('status', ChatAssignment::STATUS_ACTIVE)
                ->first();

            if (!$assignment) {
                return response()->json([
                    'error' => 'No active assignment found for this conversation'
                ], 404);
            }

            // Check permission - only assigned agent can unassign
            if ($assignment->user_id !== $user->id) {
                return response()->json([
                    'error' => 'You do not have permission to unassign this conversation'
                ], 403);
            }

            $assignment->markAsResolved();

            // Update conversation status back to bot/open
            $this->updateConversationStatus($conversation, 'open');

            // Broadcast unassignment event
            $this->broadcastAssignment($assignment, 'unassigned');

            Log::info('Chat unassigned', [
                'assignment_id' => $assignment->id,
                'conversation_type' => $type,
                'conversation_id' => $id,
                'unassigned_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversation unassigned successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Error unassigning chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to unassign conversation',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transfer conversation to another agent
     */
    public function transfer(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'notes' => 'nullable|string|max:500',
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $conversation = $this->getConversation($type, $id, $user->id);
            if (!$conversation) {
                return response()->json(['error' => 'Conversation not found'], 404);
            }

            $newAgentId = $request->input('user_id');
            $newAgent = User::find($newAgentId);

            if (!$newAgent) {
                return response()->json(['error' => 'New agent not found'], 404);
            }

            $existingAssignment = ChatAssignment::where('conversation_type', $type)
                ->where('conversation_id', $id)
                ->where('status', ChatAssignment::STATUS_ACTIVE)
                ->first();

            if (!$existingAssignment) {
                // No existing assignment, just create new one
                return $this->assign($request, $type, $id);
            }

            // Check permission - only assigned agent can transfer
            if ($existingAssignment->user_id !== $user->id) {
                return response()->json([
                    'error' => 'You do not have permission to transfer this conversation'
                ], 403);
            }

            return $this->doTransfer($existingAssignment, $newAgentId, $user->id, $request->input('notes'));

        } catch (\Exception $e) {
            Log::error('Error transferring chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to transfer conversation',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of conversations assigned to current user
     */
    public function myAssignments(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $type = $request->query('type');
            $status = $request->query('status', ChatAssignment::STATUS_ACTIVE);

            $query = ChatAssignment::with(['conversation'])
                ->where('user_id', $user->id);

            if ($type) {
                $query->where('conversation_type', $type);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $assignments = $query->orderByDesc('assigned_at')
                ->paginate(20);

            // Format response with conversation details
            $formatted = $assignments->map(function ($assignment) {
                $conversation = $this->getConversation(
                    $assignment->conversation_type,
                    $assignment->conversation_id,
                    $assignment->user_id
                );

                return [
                    'id' => $assignment->id,
                    'conversation_type' => $assignment->conversation_type,
                    'conversation_id' => $assignment->conversation_id,
                    'status' => $assignment->status,
                    'assigned_at' => $assignment->assigned_at,
                    'resolved_at' => $assignment->resolved_at,
                    'notes' => $assignment->notes,
                    'conversation' => $conversation ? [
                        'id' => $conversation->id,
                        'display_name' => $conversation->display_name ?? $conversation->name ?? 'Unknown',
                        'avatar' => $conversation->avatar ?? null,
                        'last_message' => $conversation->last_message ?? null,
                        'status' => $conversation->status ?? null,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted,
                'pagination' => [
                    'current_page' => $assignments->currentPage(),
                    'last_page' => $assignments->lastPage(),
                    'per_page' => $assignments->perPage(),
                    'total' => $assignments->total(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting my assignments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to get assignments',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if another agent is typing in this conversation
     */
    public function collisionCheck(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $cacheKey = self::TYPING_CACHE_PREFIX . "{$type}:{$id}";
            $typingData = Cache::get($cacheKey, []);

            // Filter out current user and expired entries
            $othersTyping = [];
            $now = now();

            foreach ($typingData as $userId => $data) {
                if ($userId != $user->id) {
                    $expiresAt = Carbon::parse($data['expires_at']);
                    if ($expiresAt->isFuture()) {
                        $othersTyping[] = [
                            'user_id' => $userId,
                            'user_name' => $data['user_name'],
                            'avatar' => $data['avatar'] ?? null,
                            'typing_since' => $data['typing_since'],
                        ];
                    }
                }
            }

            // Get current assignment
            $assignment = ChatAssignment::where('conversation_type', $type)
                ->where('conversation_id', $id)
                ->where('status', ChatAssignment::STATUS_ACTIVE)
                ->with('agent')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'others_typing' => $othersTyping,
                    'typing_count' => count($othersTyping),
                    'assigned_to' => $assignment ? [
                        'user_id' => $assignment->agent->id,
                        'name' => $assignment->agent->name,
                        'avatar' => $assignment->agent->avatar ?? null,
                        'is_me' => $assignment->agent->id === $user->id,
                    ] : null,
                    'collision_warning' => count($othersTyping) > 0 && $assignment && $assignment->agent->id === $user->id,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking collision', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to check typing status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Broadcast typing status
     */
    public function typing(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'conversation_type' => 'required|string|in:instagram,whatsapp,web',
                'conversation_id' => 'required|integer',
                'is_typing' => 'required|boolean',
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $type = $request->input('conversation_type');
            $id = $request->input('conversation_id');
            $isTyping = $request->input('is_typing');

            $cacheKey = self::TYPING_CACHE_PREFIX . "{$type}:{$id}";
            $typingData = Cache::get($cacheKey, []);

            if ($isTyping) {
                $typingData[$user->id] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'avatar' => $user->avatar ?? null,
                    'typing_since' => now()->toIso8601String(),
                    'expires_at' => now()->addSeconds(self::TYPING_CACHE_TTL)->toIso8601String(),
                ];
            } else {
                unset($typingData[$user->id]);
            }

            // Clean up expired entries
            foreach ($typingData as $userId => $data) {
                if (Carbon::parse($data['expires_at'])->isPast()) {
                    unset($typingData[$userId]);
                }
            }

            if (empty($typingData)) {
                Cache::forget($cacheKey);
            } else {
                Cache::put($cacheKey, $typingData, self::TYPING_CACHE_TTL);
            }

            return response()->json([
                'success' => true,
                'is_typing' => $isTyping,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating typing status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to update typing status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get typing status for a conversation
     */
    public function typingStatus(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $type = $request->route('type');
            $cacheKey = self::TYPING_CACHE_PREFIX . "{$type}:{$id}";
            $typingData = Cache::get($cacheKey, []);

            $othersTyping = [];

            foreach ($typingData as $userId => $data) {
                if ($userId != $user->id) {
                    $expiresAt = Carbon::parse($data['expires_at']);
                    if ($expiresAt->isFuture()) {
                        $othersTyping[] = [
                            'user_id' => $userId,
                            'user_name' => $data['user_name'],
                            'avatar' => $data['avatar'] ?? null,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'others_typing' => $othersTyping,
                    'typing_count' => count($othersTyping),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get typing status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available agents for assignment
     */
    public function getAgents(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Get all active users in the same tenant/account
            // Assuming user_id represents the tenant/owner
            $agents = User::where('id', $user->id)
                ->orWhere(function ($query) use ($user) {
                    // Add logic here to get team members if you have team functionality
                    // For now, just return the current user
                    $query->where('id', -1); // No other users
                })
                ->select(['id', 'name', 'email', 'avatar'])
                ->get();

            // Add current assignments count for each agent
            $agentsWithCount = $agents->map(function ($agent) {
                $activeCount = ChatAssignment::where('user_id', $agent->id)
                    ->where('status', ChatAssignment::STATUS_ACTIVE)
                    ->count();

                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'avatar' => $agent->avatar ?? $this->getInitialsAvatar($agent->name),
                    'active_assignments' => $activeCount,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $agentsWithCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting agents', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get agents',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get assignment details for a conversation
     */
    public function getAssignment(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $assignment = ChatAssignment::where('conversation_type', $type)
                ->where('conversation_id', $id)
                ->where('status', ChatAssignment::STATUS_ACTIVE)
                ->with(['agent', 'assignedBy'])
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $assignment->id,
                    'agent' => [
                        'id' => $assignment->agent->id,
                        'name' => $assignment->agent->name,
                        'avatar' => $assignment->agent->avatar ?? $this->getInitialsAvatar($assignment->agent->name),
                    ],
                    'assigned_by' => [
                        'id' => $assignment->assignedBy->id,
                        'name' => $assignment->assignedBy->name,
                    ],
                    'assigned_at' => $assignment->assigned_at,
                    'notes' => $assignment->notes,
                    'is_mine' => $assignment->user_id === $user->id,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get assignment',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Private: Get conversation by type and ID
     */
    private function getConversation(string $type, int $id, int $userId): ?Model
    {
        $modelClass = self::VALID_TYPES[$type] ?? null;

        if (!$modelClass) {
            return null;
        }

        return $modelClass::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Private: Perform transfer
     */
    private function doTransfer(ChatAssignment $existingAssignment, int $newAgentId, int $transferredById, ?string $notes): JsonResponse
    {
        // Mark old assignment as transferred
        $existingAssignment->update([
            'status' => ChatAssignment::STATUS_TRANSFERRED,
            'resolved_at' => now(),
        ]);

        // Create new assignment
        $newAssignment = ChatAssignment::create([
            'user_id' => $newAgentId,
            'conversation_type' => $existingAssignment->conversation_type,
            'conversation_id' => $existingAssignment->conversation_id,
            'assigned_by' => $transferredById,
            'assigned_at' => now(),
            'status' => ChatAssignment::STATUS_ACTIVE,
            'notes' => $notes ?? 'Transferred from ' . $existingAssignment->agent->name,
        ]);

        // Broadcast transfer event
        $this->broadcastAssignment($newAssignment, 'transferred');

        Log::info('Chat transferred', [
            'old_assignment_id' => $existingAssignment->id,
            'new_assignment_id' => $newAssignment->id,
            'from_agent' => $existingAssignment->user_id,
            'to_agent' => $newAgentId,
            'transferred_by' => $transferredById,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation transferred successfully',
            'assignment' => $newAssignment->load(['agent', 'assignedBy']),
        ]);
    }

    /**
     * Private: Update conversation status
     */
    private function updateConversationStatus($conversation, string $status): void
    {
        if (method_exists($conversation, 'update')) {
            $conversation->update(['status' => $status]);
        }
    }

    /**
     * Private: Broadcast assignment event
     */
    private function broadcastAssignment(ChatAssignment $assignment, string $event): void
    {
        // Implement your broadcasting logic here
        // Example using Laravel Echo/Pusher:
        // broadcast(new ChatAssigned($assignment, $event))->toOthers();
    }

    /**
     * Private: Get initials avatar
     */
    private function getInitialsAvatar(string $name): string
    {
        $initials = collect(explode(' ', $name))
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->join('');

        // Return a data URI with colored circle and initials
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
        $color = $colors[crc32($name) % count($colors)];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">' .
            '<circle cx="20" cy="20" r="20" fill="' . $color . '"/>' .
            '<text x="50%" y="50%" dy=".1em" text-anchor="middle" fill="white" font-size="16" font-weight="bold">' .
            $initials .
            '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
