<?php

namespace App\Http\Controllers;

use App\Models\AutoReplyLog;
use Illuminate\Http\Request;
class AutoReplyLogController extends Controller
{
    public function index(Request $request)
    {
        $q = AutoReplyLog::with(['conversation', 'message', 'rule'])
            ->orderByDesc('id');

        // =========================
        // FILTERS
        // =========================

        // source: manual / ai
        if ($request->filled('source')) {
            $source = $request->string('source')->toString();
            $q->where('response_source', $source);
        }

        // status exact
        if ($request->filled('status')) {
            $q->where('status', $request->string('status')->toString());
        }

        // min confidence (untuk AI)
        if ($request->filled('min_conf')) {
            $minConf = (float) $request->input('min_conf');
            $q->whereNotNull('ai_confidence')
              ->where('ai_confidence', '>=', $minConf);
        }

        // search keyword di trigger/response
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $q->where(function($qq) use ($search) {
                $qq->where('trigger_text', 'like', "%{$search}%")
                   ->orWhere('response_text', 'like', "%{$search}%")
                   ->orWhere('error_message', 'like', "%{$search}%");
            });
        }

        // pagination (default 20, user request limit as per_page)
        $limitInput = $request->input('limit', 20);
        
        if ($limitInput === 'all') {
             $perPage = 5000; // Safe max for "all"
        } else {
             $perPage = (int) $limitInput;
             $perPage = max(5, min(5000, $perPage)); 
        }
        
        $logs = $q->paginate($perPage);

        return view('pages.logs.index', [
            'title' => 'Auto Reply Logs',
            'logs' => $logs,
            'filters' => [
                'source' => $request->input('source'),
                'status' => $request->input('status'),
                'min_conf' => $request->input('min_conf'),
                'search' => $request->input('search'),
                'limit' => $request->input('limit', 20),
            ],
        ]);
    }
}
