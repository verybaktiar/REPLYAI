<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        $fileSize = 0;
        $lastModified = null;

        if (File::exists($logPath)) {
            $content = File::get($logPath);
            $fileSize = File::size($logPath);
            $lastModified = date('Y-m-d H:i:s', File::lastModified($logPath));
            
            // Splitting by date pattern to get individual entries
            $logs = preg_split('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/m', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            
            // Get last 100 entries and keep order (reverse later in view/json if needed)
            $logs = array_slice($logs, -100);
        }

        if ($request->ajax() || $request->has('json')) {
            $formattedLogs = collect($logs)->map(function($log) {
                return [
                    'content' => trim($log),
                    'level' => str_contains($log, '.ERROR') || str_contains($log, '.CRITICAL') ? 'error' : 
                               (str_contains($log, '.WARNING') ? 'warning' : 'info')
                ];
            });

            return response()->json([
                'logs' => $formattedLogs,
                'metadata' => [
                    'size' => number_format($fileSize / 1024, 2) . ' KB',
                    'last_modified' => $lastModified
                ]
            ]);
        }

        return view('admin.logs.index', compact('logs'));
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }

        return back()->with('success', 'Logs cleared successfully!');
    }
}
