<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for current admin (AJAX)
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        
        $notifications = AdminNotification::forAdmin($admin->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $unreadCount = AdminNotification::forAdmin($admin->id)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(AdminNotification $notification)
    {
        $admin = Auth::guard('admin')->user();
        
        // Ensure admin can only mark their own notifications
        if ($notification->admin_id && $notification->admin_id !== $admin->id) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $admin = Auth::guard('admin')->user();
        
        $count = AdminNotification::markAllAsRead($admin->id);

        return response()->json([
            'success' => true,
            'marked_count' => $count,
        ]);
    }

    /**
     * Get unread count (for polling)
     */
    public function unreadCount()
    {
        $admin = Auth::guard('admin')->user();
        
        $count = AdminNotification::forAdmin($admin->id)
            ->unread()
            ->count();

        $hasUrgent = AdminNotification::forAdmin($admin->id)
            ->unread()
            ->where('priority', 'urgent')
            ->exists();

        return response()->json([
            'count' => $count,
            'has_urgent' => $hasUrgent,
        ]);
    }

    /**
     * Show all notifications page
     */
    public function showAll()
    {
        $admin = Auth::guard('admin')->user();
        
        $notifications = AdminNotification::forAdmin($admin->id)
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Delete notification
     */
    public function destroy(AdminNotification $notification)
    {
        $admin = Auth::guard('admin')->user();
        
        if ($notification->admin_id && $notification->admin_id !== $admin->id) {
            abort(403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
