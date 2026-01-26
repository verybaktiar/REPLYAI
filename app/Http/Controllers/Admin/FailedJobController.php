<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class FailedJobController extends Controller
{
    public function index()
    {
        $failedJobs = DB::table('failed_jobs')->orderByDesc('failed_at')->paginate(15);
        
        return view('admin.failed-jobs.index', compact('failedJobs'));
    }

    public function retry($id)
    {
        Artisan::call('queue:retry', ['id' => $id]);
        
        return back()->with('success', "Job #{$id} has been pushed back to the queue.");
    }

    public function retryAll()
    {
        Artisan::call('queue:retry', ['id' => ['all']]);
        
        return back()->with('success', 'All failed jobs have been pushed back to the queue.');
    }

    public function destroy($id)
    {
        DB::table('failed_jobs')->where('id', $id)->delete();
        
        return back()->with('success', "Failed job #{$id} has been deleted.");
    }

    public function flush()
    {
        Artisan::call('queue:flush');
        
        return back()->with('success', 'All failed jobs have been cleared.');
    }
}
