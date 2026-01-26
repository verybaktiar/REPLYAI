<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class AdminBackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
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
        try {
            $this->backupService->generateBackup();
            return back()->with('success', 'Backup berhasil digenerate!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal generate backup: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        if (file_exists($path)) {
            return response()->download($path);
        }
        return back()->with('error', 'File tidak ditemukan.');
    }

    public function destroy($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        if (file_exists($path)) {
            unlink($path);
            return back()->with('success', 'Backup berhasil dihapus.');
        }
        return back()->with('error', 'Gagal menghapus file.');
    }
}
