<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ScheduleMonitorController extends Controller
{
    private function checkAuthorization(): void
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role !== 'superadmin') {
            abort(403, 'Unauthorized.');
        }
    }

    public function index()
    {
        $this->checkAuthorization();

        $tasks = $this->getScheduledTasks();
        $executionHistory = $this->getExecutionHistory();
        $pendingJobs = $this->getPendingJobs();
        
        return view('admin.schedule.index', compact('tasks', 'executionHistory', 'pendingJobs'));
    }

    private function getScheduledTasks(): array
    {
        // Get tasks from app/Console/Kernel.php schedule
        // This is a simplified version - in production you might want to parse the Kernel file
        return [
            [
                'name' => 'process:webhooks',
                'description' => 'Process pending webhooks',
                'frequency' => 'Every minute',
                'last_run' => Cache::get('schedule_last_run_process_webhooks'),
                'status' => 'active',
                'command' => 'webhooks:process',
            ],
            [
                'name' => 'queue:retry-failed',
                'description' => 'Auto-retry failed jobs',
                'frequency' => 'Every 5 minutes',
                'last_run' => Cache::get('schedule_last_run_retry_failed'),
                'status' => 'active',
                'command' => 'queue:retry-failed',
            ],
            [
                'name' => 'cleanup:old-data',
                'description' => 'Clean up old logs and temp data',
                'frequency' => 'Daily at 02:00',
                'last_run' => Cache::get('schedule_last_run_cleanup'),
                'status' => 'active',
                'command' => 'cleanup:old-data',
            ],
            [
                'name' => 'instagram:refresh-tokens',
                'description' => 'Refresh expired Instagram tokens',
                'frequency' => 'Daily at 01:00',
                'last_run' => Cache::get('schedule_last_run_ig_refresh'),
                'status' => 'active',
                'command' => 'instagram:refresh-tokens',
            ],
            [
                'name' => 'reports:daily',
                'description' => 'Generate daily reports',
                'frequency' => 'Daily at 23:55',
                'last_run' => Cache::get('schedule_last_run_daily_reports'),
                'status' => 'active',
                'command' => 'reports:generate daily',
            ],
            [
                'name' => 'health:auto-heal',
                'description' => 'Auto-heal stopped services',
                'frequency' => 'Every 10 minutes',
                'last_run' => Cache::get('schedule_last_run_auto_heal'),
                'status' => 'active',
                'command' => 'system:auto-heal',
            ],
        ];
    }

    private function getExecutionHistory(): array
    {
        // Get from database or cache
        $history = Cache::get('schedule_execution_history', []);
        
        // Limit to last 50 entries
        return array_slice($history, -50);
    }

    private function getPendingJobs(): array
    {
        $jobs = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as count'), DB::raw('max(available_at) as oldest'))
            ->groupBy('queue')
            ->get();

        return $jobs->map(function($job) {
            return [
                'queue' => $job->queue,
                'count' => $job->count,
                'oldest' => $job->oldest ? date('Y-m-d H:i:s', $job->oldest) : null,
            ];
        })->toArray();
    }

    public function runTask(Request $request)
    {
        $this->checkAuthorization();

        $task = $request->input('task');
        $allowedTasks = [
            'process:webhooks' => 'webhooks:process',
            'queue:retry-failed' => 'queue:retry-failed',
            'cleanup:old-data' => 'cleanup:old-data',
            'instagram:refresh-tokens' => 'instagram:refresh-tokens',
            'reports:daily' => 'reports:generate daily',
            'health:auto-heal' => 'system:auto-heal',
        ];

        if (!isset($allowedTasks[$task])) {
            return back()->with('error', 'Invalid task.');
        }

        try {
            $command = $allowedTasks[$task];
            $output = shell_exec('cd ' . base_path() . ' && php artisan ' . $command . ' 2>&1');
            
            // Update last run time
            Cache::put('schedule_last_run_' . str_replace(':', '_', $task), now()->toDateTimeString(), 86400);
            
            // Add to history
            $history = Cache::get('schedule_execution_history', []);
            $history[] = [
                'task' => $task,
                'run_at' => now()->toDateTimeString(),
                'output' => substr($output, 0, 500),
                'status' => 'success',
            ];
            Cache::put('schedule_execution_history', $history, 86400 * 7);

            \App\Models\AdminActivityLog::log(
                auth()->guard('admin')->user(),
                'run_scheduled_task',
                "Manually ran scheduled task: {$task}",
                ['task' => $task, 'output' => substr($output, 0, 200)],
                null
            );

            return back()->with('success', "Task '{$task}' executed successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getQueueStatus()
    {
        $this->checkAuthorization();

        $queues = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as count'))
            ->groupBy('queue')
            ->pluck('count', 'queue');

        $failed = DB::table('failed_jobs')->count();

        return response()->json([
            'queues' => $queues,
            'failed' => $failed,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
