<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Exception;

class BackupService
{
    public function generateBackup()
    {
        $backupName = 'backup-' . now()->format('Y-m-d-His');
        $backupPath = storage_path('app/backups/' . $backupName);
        
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        try {
            // 1. Database Dump (MySQL)
            $dbConfig = config('database.connections.mysql');
            $sqlFile = $backupPath . '.sql';
            
            // Note: On Windows/Laragon, mysqldump might need full path or be in PATH
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($sqlFile)
            );
            
            exec($command, $output, $returnCode);

            // 2. Zip the SQL file and optionally storage folder
            $zipFile = $backupPath . '.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
                if (file_exists($sqlFile)) {
                    $zip->addFile($sqlFile, 'database.sql');
                }
                
                // You could also add storage/app/public if needed:
                // $this->addFolderToZip(storage_path('app/public'), $zip, 'storage');
                
                $zip->close();
            }

            // Cleanup SQL file
            if (file_exists($sqlFile)) {
                unlink($sqlFile);
            }

            return $zipFile;

        } catch (Exception $e) {
            throw new Exception('Backup failed: ' . $e->getMessage());
        }
    }

    private function addFolderToZip($folder, $zip, $zipPath)
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder));
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folder) + 1);
                $zip->addFile($filePath, $zipPath . '/' . $relativePath);
            }
        }
    }
}
