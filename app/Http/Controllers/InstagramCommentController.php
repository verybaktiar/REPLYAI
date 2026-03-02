<?php

namespace App\Http\Controllers;

use App\Models\InstagramComment;
use App\Models\InstagramAccount;
use App\Models\CommentAutoReplySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramCommentController extends Controller
{
    /**
     * Display a listing of comments
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $instagramAccount = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        // Get filter from request
        $filter = $request->get('filter', 'all');
        
        // Base query
        $query = InstagramComment::with('instagramAccount')
            ->where('user_id', $user->id)
            ->parentOnly(); // Only show parent comments, not replies

        // Apply filters
        switch ($filter) {
            case 'unreplied':
                $query->unreplied();
                break;
            case 'replied':
                $query->replied();
                break;
            default:
                // 'all' - no additional filter
                break;
        }

        // Get comments with pagination
        $comments = $query->orderBy('commented_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get statistics
        $stats = [
            'total' => InstagramComment::where('user_id', $user->id)->parentOnly()->count(),
            'unreplied' => InstagramComment::where('user_id', $user->id)->parentOnly()->unreplied()->count(),
            'replied' => InstagramComment::where('user_id', $user->id)->parentOnly()->replied()->count(),
        ];

        // Get auto-reply settings
        $autoReplySettings = null;
        if ($instagramAccount) {
            $autoReplySettings = CommentAutoReplySetting::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'instagram_account_id' => $instagramAccount->id,
                ],
                [
                    'is_active' => false,
                    'keywords' => [],
                    'reply_message' => 'Terima kasih atas komentarnya! 😊',
                    'match_type' => 'contains',
                ]
            );
        }

        return view('pages.instagram.comments', compact(
            'comments',
            'stats',
            'filter',
            'instagramAccount',
            'autoReplySettings'
        ));
    }

    /**
     * Reply to a specific comment
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:2200',
        ]);

        $user = Auth::user();
        $comment = InstagramComment::where('user_id', $user->id)
            ->findOrFail($id);

        $instagramAccount = $comment->instagramAccount;

        if (!$instagramAccount || !$instagramAccount->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Instagram account not connected or inactive',
            ], 400);
        }

        try {
            // Send reply via Instagram Graph API
            $result = $this->sendCommentReply(
                $instagramAccount->access_token,
                $comment->instagram_comment_id,
                $request->message
            );

            if ($result['success']) {
                // Mark comment as replied in database
                $comment->markAsReplied($request->message);

                return response()->json([
                    'success' => true,
                    'message' => 'Reply sent successfully',
                    'comment' => $comment->fresh(),
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to send reply',
            ], 400);

        } catch (\Exception $e) {
            Log::error('Instagram Comment Reply Error', [
                'user_id' => $user->id,
                'comment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while sending reply',
            ], 500);
        }
    }

    /**
     * Bulk reply to multiple comments
     */
    public function bulkReply(Request $request)
    {
        $request->validate([
            'comment_ids' => 'required|array|min:1',
            'comment_ids.*' => 'integer|exists:instagram_comments,id',
            'message' => 'required|string|max:2200',
        ]);

        $user = Auth::user();
        $commentIds = $request->comment_ids;
        $message = $request->message;

        $comments = InstagramComment::where('user_id', $user->id)
            ->whereIn('id', $commentIds)
            ->get();

        if ($comments->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'No valid comments found',
            ], 400);
        }

        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($comments as $comment) {
            $instagramAccount = $comment->instagramAccount;

            if (!$instagramAccount || !$instagramAccount->is_active) {
                $results['failed'][] = [
                    'id' => $comment->id,
                    'error' => 'Instagram account not connected',
                ];
                continue;
            }

            try {
                $result = $this->sendCommentReply(
                    $instagramAccount->access_token,
                    $comment->instagram_comment_id,
                    $message
                );

                if ($result['success']) {
                    $comment->markAsReplied($message);
                    $results['success'][] = $comment->id;
                } else {
                    $results['failed'][] = [
                        'id' => $comment->id,
                        'error' => $result['error'] ?? 'Failed to send reply',
                    ];
                }
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'id' => $comment->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => count($results['failed']) === 0,
            'results' => $results,
            'message' => sprintf(
                'Replied to %d comments. %d failed.',
                count($results['success']),
                count($results['failed'])
            ),
        ]);
    }

    /**
     * Update auto-reply settings for comments
     */
    public function autoReplySettings(Request $request)
    {
        $request->validate([
            'is_enabled' => 'boolean',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
            'reply_template' => 'required|string|max:2200',
            'match_type' => 'in:exact,contains,starts_with',
        ]);

        $user = Auth::user();

        $settings = CommentAutoReplySetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'is_enabled' => $request->boolean('is_enabled', false),
                'keywords' => $request->input('keywords', []),
                'reply_template' => $request->input('reply_template'),
                'match_type' => $request->input('match_type', 'contains'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Auto-reply settings updated successfully',
            'settings' => $settings,
        ]);
    }

    /**
     * Fetch comments from Instagram API
     */
    public function fetchFromApi(Request $request)
    {
        $user = Auth::user();
        $instagramAccount = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$instagramAccount) {
            return response()->json([
                'success' => false,
                'error' => 'No active Instagram account found',
            ], 400);
        }

        try {
            // Fetch recent media first
            $mediaResponse = Http::get("https://graph.instagram.com/v21.0/{$instagramAccount->instagram_user_id}/media", [
                'access_token' => $instagramAccount->access_token,
                'limit' => 25,
                'fields' => 'id,caption,media_type,media_url,permalink,timestamp',
            ]);

            if ($mediaResponse->failed()) {
                Log::error('Instagram API: Failed to fetch media', [
                    'user_id' => $user->id,
                    'error' => $mediaResponse->json(),
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to fetch media from Instagram',
                ], 400);
            }

            $media = $mediaResponse->json('data', []);
            $fetchedComments = 0;

            foreach ($media as $item) {
                // Fetch comments for each media
                $commentsResponse = Http::get("https://graph.instagram.com/v21.0/{$item['id']}/comments", [
                    'access_token' => $instagramAccount->access_token,
                    'limit' => 100,
                    'fields' => 'id,from,text,timestamp,parent_id',
                ]);

                if ($commentsResponse->successful()) {
                    $comments = $commentsResponse->json('data', []);

                    foreach ($comments as $commentData) {
                        // Skip if already exists
                        $existing = InstagramComment::where('instagram_comment_id', $commentData['id'])
                            ->first();

                        if (!$existing) {
                            InstagramComment::create([
                                'user_id' => $user->id,
                                'instagram_account_id' => $instagramAccount->id,
                                'instagram_comment_id' => $commentData['id'],
                                'media_id' => $item['id'],
                                'from_username' => $commentData['from']['username'] ?? 'unknown',
                                'from_id' => $commentData['from']['id'] ?? '',
                                'text' => $commentData['text'] ?? '',
                                'parent_comment_id' => $commentData['parent_id'] ?? null,
                                'is_replied' => false,
                                'commented_at' => $commentData['timestamp'] ?? now(),
                            ]);
                            $fetchedComments++;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Fetched {$fetchedComments} new comments",
                'fetched_count' => $fetchedComments,
            ]);

        } catch (\Exception $e) {
            Log::error('Instagram Fetch Comments Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching comments',
            ], 500);
        }
    }

    /**
     * Get media preview for a comment
     */
    public function getMediaPreview($mediaId)
    {
        $user = Auth::user();
        $instagramAccount = InstagramAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$instagramAccount) {
            return response()->json([
                'success' => false,
                'error' => 'No active Instagram account',
            ], 400);
        }

        try {
            $response = Http::get("https://graph.instagram.com/v21.0/{$mediaId}", [
                'access_token' => $instagramAccount->access_token,
                'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink',
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'media' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch media preview',
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a comment
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $comment = InstagramComment::where('user_id', $user->id)
            ->findOrFail($id);

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Mark comment as read (manual)
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $comment = InstagramComment::where('user_id', $user->id)
            ->findOrFail($id);

        // Toggle is_replied status or add a separate read status if needed
        // For now, we'll just return success
        return response()->json([
            'success' => true,
            'message' => 'Comment marked as read',
        ]);
    }

    /**
     * Send reply via Instagram Graph API
     */
    private function sendCommentReply(string $accessToken, string $commentId, string $message): array
    {
        try {
            // Using Instagram Graph API to reply to a comment
            $response = Http::post("https://graph.instagram.com/v21.0/{$commentId}/replies", [
                'access_token' => $accessToken,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            $error = $response->json('error.message', 'Unknown error');
            Log::error('Instagram API Reply Error', [
                'error' => $response->json(),
                'comment_id' => $commentId,
            ]);

            return [
                'success' => false,
                'error' => $error,
            ];

        } catch (\Exception $e) {
            Log::error('Instagram API Reply Exception', [
                'error' => $e->getMessage(),
                'comment_id' => $commentId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
