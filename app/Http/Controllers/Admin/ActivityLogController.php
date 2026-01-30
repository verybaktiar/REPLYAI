<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

/**
 * Controller untuk menampilkan Activity Logs admin.
 */
class ActivityLogController extends Controller
{
    /**
     * Tampilkan daftar activity logs
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'admin'); // Default ke admin
        
        if ($type === 'user') {
            $query = \App\Models\ActivityLog::with('user')
                ->orderBy('created_at', 'desc');
            
            // Unique actions for User Activity
            $actions = \App\Models\ActivityLog::distinct()->pluck('action');
        } else {
            $query = AdminActivityLog::with('admin')
                ->orderBy('created_at', 'desc');
            
            // Unique actions for Admin Activity
            $actions = AdminActivityLog::distinct()->pluck('action');
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50);

        return view('admin.activity-logs.index', compact('logs', 'actions', 'type'));
    }
}
