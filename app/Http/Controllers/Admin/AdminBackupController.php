<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminActivityLog;
use Exception;

class AdminBackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isSuperAdmin()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_backup_access',
                'Attempted to access backups without superadmin privilege',
                ['url' => request()->fullUrl()],
                null,
                8
            );
            abort(403, 'Only Superadmin can manage backups.');
        }
    }

    public function index()
    {
        $this->checkAuthorization();
        
        $backups = [];
        $directory = storage_path('app/backups');
        
        if (file_exists($directory)) {
            $files = scandir($directory);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $path = $directory . '/' . $file;
                    $backups[] = [
                        'name' => $file,
                        'size' => number_format(filesize($path) / 1024 / 1024, 2) . ' MB',
                        'date' => date('Y-m-d H:i:s', filemtime($path)),
                    ];
                }
            }
        }
        
        // Sort by date desc
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return view('admin.backups.index', compact('backups'));
    }

    public function create()
    {
        $this->checkAuthorization();
        
        try {
            $filename = $this->backupService->generateBackup();
            
            AdminActivityLog::log(
                Auth::guard('admin')->user(),
                'create_backup',
                "Generated backup: {$filename}",
                ['filename' => $filename]
            );
            
            return back()->with('success', 'Backup berhasil digenerate!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal generate backup: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $this->checkAuthorization();
        
        $path = storage_path('app/backups/' . $filename);
        if (file_exists($path)) {
            return response()->download($path);
        }
        return back()->with('error', 'File tidak ditemukan.');
    }

    public function destroy($filename)
    {
        $this->checkAuthorization();
        
        $path = storage_path('app/backups/' . $filename);
        if (file_exists($path)) {
            unlink($path);
            
            AdminActivityLog::log(
                Auth::guard('admin')->user(),
                'delete_backup',
                "Deleted backup: {$filename}",
                ['filename' => $filename]
            );
            
            return back()->with('success', 'Backup berhasil dihapus.');
        }
        return back()->with('error', 'Gagal menghapus file.');
    }
}
