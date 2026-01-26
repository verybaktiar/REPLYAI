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
        $query = AdminActivityLog::with('admin')
            ->orderBy('created_at', 'desc');

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by admin
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
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

        // Get unique actions for filter
        $actions = AdminActivityLog::distinct()->pluck('action');

        return view('admin.activity-logs.index', compact('logs', 'actions'));
    }
}
