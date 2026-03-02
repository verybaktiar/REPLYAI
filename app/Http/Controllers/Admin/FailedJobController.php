<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminActivityLog;

class FailedJobController extends Controller
{
    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isSuperAdmin()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_failed_jobs_access',
                'Attempted to access failed jobs without superadmin privilege',
                ['url' => request()->fullUrl()],
                null,
                7
            );
            abort(403, 'Only Superadmin can manage failed jobs.');
        }
    }

    public function index()
    {
        $this->checkAuthorization();
        
        $failedJobs = DB::table('failed_jobs')->orderByDesc('failed_at')->paginate(15);
        
        return view('admin.failed-jobs.index', compact('failedJobs'));
    }

    public function retry($id)
    {
        $this->checkAuthorization();
        
        Artisan::call('queue:retry', ['id' => $id]);
        
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'retry_failed_job',
            "Retried failed job #{$id}",
            ['job_id' => $id]
        );
        
        return back()->with('success', "Job #{$id} has been pushed back to the queue.");
    }

    public function retryAll()
    {
        $this->checkAuthorization();
        
        $count = DB::table('failed_jobs')->count();
        Artisan::call('queue:retry', ['id' => ['all']]);
        
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'retry_all_failed_jobs',
            "Retried all {$count} failed jobs",
            ['count' => $count]
        );
        
        return back()->with('success', "{$count} failed jobs have been pushed back to the queue.");
    }

    public function destroy($id)
    {
        $this->checkAuthorization();
        
        $job = DB::table('failed_jobs')->where('id', $id)->first();
        DB::table('failed_jobs')->where('id', $id)->delete();
        
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'delete_failed_job',
            "Deleted failed job #{$id}",
            ['job_id' => $id, 'queue' => $job->queue ?? 'unknown']
        );
        
        return back()->with('success', "Failed job #{$id} has been deleted.");
    }

    public function flush()
    {
        $this->checkAuthorization();
        
        $count = DB::table('failed_jobs')->count();
        Artisan::call('queue:flush');
        
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'flush_failed_jobs',
            "Flushed {$count} failed jobs",
            ['count' => $count]
        );
        
        return back()->with('success', "{$count} failed jobs have been cleared.");
    }
}
